<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsTest;
use App\Models\Lms\LmsTestAttempt;
use App\Models\Lms\LmsTestAnswer;
use App\Models\Lms\LmsTestQuestion;
use App\Models\Lms\LmsTestResponse;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TestController extends Controller
{
    public function index(Request $request, LmsEvent $event): Response
    {
        $query = LmsTest::where('lms_event_id', $event->id)
            ->where('is_active', true)
            ->where('in_menu', true);

        if ($search = $request->get('search')) {
            $query->where('title', 'ilike', '%' . $search . '%');
        }

        $tests = $query->paginate(12)->withQueryString();

        return Inertia::render('Lms/Tests/Index', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'tests' => $tests,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(LmsEvent $event, LmsTest $test): Response
    {
        if ($test->lms_event_id !== $event->id) {
            abort(404);
        }
        $user = auth()->user();
        $attempts = LmsTestAttempt::where('lms_test_id', $test->id)
            ->where('user_id', $user->id)
            ->orderByDesc('started_at')
            ->get(['id', 'score', 'max_score', 'percentage', 'passed', 'started_at', 'finished_at']);

        return Inertia::render('Lms/Tests/Show', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'test' => $test->only([
                'id', 'title', 'description',
                'time_limit_minutes', 'shuffle_questions', 'shuffle_answers',
                'passing_score', 'max_attempts',
            ]),
            'attempts' => $attempts,
        ]);
    }

    public function start(LmsEvent $event, LmsTest $test): RedirectResponse
    {
        if ($test->lms_event_id !== $event->id) {
            abort(404);
        }
        $user = auth()->user();

        $attemptsCount = LmsTestAttempt::where('lms_test_id', $test->id)
            ->where('user_id', $user->id)
            ->count();

        if ($test->max_attempts && $attemptsCount >= $test->max_attempts) {
            return redirect()->back()->withErrors(['max_attempts' => 'Max attempts reached']);
        }

        $attempt = LmsTestAttempt::create([
            'lms_test_id' => $test->id,
            'user_id' => $user->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        return redirect()->route('lms.tests.take', [
            'event' => $event->slug,
            'test' => $test->id,
            'attempt' => $attempt->id,
        ]);
    }

    public function take(LmsEvent $event, LmsTest $test, LmsTestAttempt $attempt): Response|RedirectResponse
    {
        if ($attempt->user_id !== auth()->id() || $attempt->lms_test_id !== $test->id) {
            abort(403);
        }

        if ($attempt->status !== 'in_progress') {
            return redirect()->route('lms.tests.result', [
                'event' => $event->slug,
                'test' => $test->id,
                'attempt' => $attempt->id,
            ]);
        }

        $test->load('questions.answers');

        $questions = $test->questions->map(function ($q) use ($test) {
            $data = [
                'id' => $q->id,
                'text' => $q->question ?? $q->text ?? '',
                'type' => $q->type,
                'image' => $q->image ?? null,
            ];

            if (in_array($q->type, ['single', 'multiple'])) {
                $answers = $q->answers->map(fn ($a) => ['id' => $a->id, 'text' => $a->answer ?? $a->text ?? '']);
                $data['answers'] = $test->shuffle_answers ? $answers->shuffle()->values() : $answers;
            }

            return $data;
        });

        if ($test->shuffle_questions) {
            $questions = $questions->shuffle()->values();
        }

        return Inertia::render('Lms/Tests/Take', [
            'event' => $event,
            'user' => auth()->user(),
            'profile' => LmsProfile::where('user_id', auth()->id())->where('lms_event_id', $event->id)->first(),
            'test' => $test->only(['id', 'title', 'description', 'time_limit_minutes', 'shuffle_questions', 'shuffle_answers']),
            'attempt' => $attempt->only(['id', 'status', 'started_at']),
            'questions' => $questions,
        ]);
    }

    public function submit(Request $request, LmsEvent $event, LmsTest $test, LmsTestAttempt $attempt): RedirectResponse
    {
        if ($test->lms_event_id !== $event->id || $attempt->lms_test_id !== $test->id) {
            abort(404);
        }
        $user = auth()->user();
        if ($attempt->user_id !== $user->id || $attempt->finished_at) {
            abort(403);
        }

        $rawResponses = $request->validate(['responses' => ['required', 'array']])['responses'];

        // Normalize: frontend sends { questionId: value }, backend needs keyed by question_id
        $responses = collect($rawResponses)->mapWithKeys(function ($value, $key) {
            if (is_array($value) && isset($value['question_id'])) {
                return [$value['question_id'] => $value];
            }
            // Flat format: { questionId: answerId | [answerIds] | "text" }
            return [(int) $key => [
                'question_id' => (int) $key,
                'selected_answer_ids' => is_array($value) ? $value : (is_int($value) || is_numeric($value) ? [(int) $value] : []),
                'text_answer' => is_string($value) && !is_numeric($value) ? $value : null,
            ]];
        });

        $questions = $test->questions()->with('answers')->get();
        $totalScore = 0;
        $maxScore = 0;

        foreach ($questions as $question) {
            $maxScore += $question->points;
            $responseData = $responses->get($question->id);
            $isCorrect = false;
            $pointsEarned = 0;

            if ($responseData) {
                if ($question->type === 'text') {
                    $textAnswer = $responseData['text_answer'] ?? '';
                    // Text answers could be graded manually; for auto we'll leave 0
                    LmsTestResponse::create([
                        'lms_test_attempt_id' => $attempt->id,
                        'lms_test_question_id' => $question->id,
                        'text_answer' => $textAnswer,
                        'is_correct' => false,
                        'points_earned' => 0,
                    ]);
                } else {
                    $selectedIds = $responseData['selected_answer_ids'] ?? [];
                    if (is_array($selectedIds)) {
                        $correctIds = $question->answers->where('is_correct', true)->pluck('id')->sort()->values()->toArray();
                        $selectedSorted = collect($selectedIds)->sort()->values()->toArray();
                        $isCorrect = $correctIds === $selectedSorted;
                        $pointsEarned = $isCorrect ? $question->points : 0;
                    }
                    LmsTestResponse::create([
                        'lms_test_attempt_id' => $attempt->id,
                        'lms_test_question_id' => $question->id,
                        'selected_answer_ids' => $selectedIds,
                        'is_correct' => $isCorrect,
                        'points_earned' => $pointsEarned,
                    ]);
                }
            }
            $totalScore += $pointsEarned;
        }

        $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100) : 0;
        $passed = $percentage >= $test->passing_score;

        $attempt->update([
            'score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'passed' => $passed,
            'finished_at' => now(),
        ]);

        return redirect()->route('lms.tests.result', [$event, $test, $attempt]);
    }

    public function result(LmsEvent $event, LmsTest $test, LmsTestAttempt $attempt): Response
    {
        if ($test->lms_event_id !== $event->id || $attempt->lms_test_id !== $test->id) {
            abort(404);
        }
        $user = auth()->user();
        if ($attempt->user_id !== $user->id) {
            abort(403);
        }

        return Inertia::render('Lms/Tests/Result', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'test' => $test->only(['id', 'title', 'passing_score', 'show_correct_answers']),
            'attempt' => $attempt->only(['id', 'score', 'max_score', 'percentage', 'passed', 'started_at', 'finished_at']),
        ]);
    }
}
