<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsTest;
use App\Models\Lms\LmsTestAttempt;
use App\Models\Lms\LmsTestAnswer;
use App\Models\Lms\LmsTestQuestion;
use App\Models\Lms\LmsTestResponse;
use App\Models\Lms\LmsEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TestController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $tests = LmsTest::where('lms_event_id', $event->id)
            ->where('is_active', true)
            ->where('in_menu', true)
            ->get(['id', 'title', 'description', 'time_limit_minutes', 'passing_score', 'max_attempts']);

        return Inertia::render('Lms/Tests/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'tests' => $tests,
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
            'event' => $event->only(['id', 'slug', 'title']),
            'test' => $test->only([
                'id', 'title', 'description',
                'time_limit_minutes', 'shuffle_questions', 'shuffle_answers',
                'passing_score', 'max_attempts',
            ]),
            'attempts' => $attempts,
        ]);
    }

    public function start(LmsEvent $event, LmsTest $test): JsonResponse
    {
        if ($test->lms_event_id !== $event->id) {
            abort(404);
        }
        $user = auth()->user();

        $attemptsCount = LmsTestAttempt::where('lms_test_id', $test->id)
            ->where('user_id', $user->id)
            ->count();

        if ($test->max_attempts && $attemptsCount >= $test->max_attempts) {
            return response()->json(['error' => 'Max attempts reached'], 403);
        }

        $attempt = LmsTestAttempt::create([
            'lms_test_id' => $test->id,
            'user_id' => $user->id,
            'started_at' => now(),
        ]);

        $questions = $test->questions()->orderBy('position')->get();

        if ($test->shuffle_questions) {
            $questions = $questions->shuffle();
        }

        $questionsWithAnswers = $questions->map(function ($question) use ($test) {
            $answers = $question->answers()->orderBy('position')->get();
            if ($test->shuffle_answers) {
                $answers = $answers->shuffle();
            }
            return [
                'id' => $question->id,
                'question' => $question->question,
                'type' => $question->type,
                'points' => $question->points,
                'answers' => $answers->map(fn($a) => [
                    'id' => $a->id,
                    'answer' => $a->answer,
                    'position' => $a->position,
                ])->values(),
            ];
        });

        return response()->json([
            'attempt_id' => $attempt->id,
            'questions' => $questionsWithAnswers,
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

        $responses = $request->validate(['responses' => ['required', 'array']])['responses'];
        $responses = collect($responses)->keyBy('question_id');

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
            'event' => $event->only(['id', 'slug', 'title']),
            'test' => $test->only(['id', 'title', 'passing_score', 'show_correct_answers']),
            'attempt' => $attempt->only(['id', 'score', 'max_score', 'percentage', 'passed', 'started_at', 'finished_at']),
        ]);
    }
}
