<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsCourseStage;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsStageBlock;
use App\Models\Lms\LmsStageProgress;
use App\Models\Lms\LmsTest;
use App\Models\Lms\LmsTestAttempt;
use App\Models\Lms\LmsTestAnswer;
use App\Models\Lms\LmsTestQuestion;
use App\Models\Lms\LmsTestResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TestController extends Controller
{
    public function index(Request $request, LmsEvent $event): Response
    {
        $user = auth()->user();

        $enrolledCourseIds = LmsCourseEnrollment::where('user_id', $user->id)
            ->whereHas('course', fn($q) => $q->where('lms_event_id', $event->id))
            ->pluck('lms_course_id');

        $directStageTestIds = DB::table('lms_course_stages')
            ->whereIn('lms_course_id', $enrolledCourseIds)
            ->whereNotNull('lms_test_id')
            ->distinct()
            ->pluck('lms_test_id');

        $blockTestIds = DB::table('lms_stage_blocks')
            ->join('lms_course_stages', 'lms_course_stages.id', '=', 'lms_stage_blocks.lms_course_stage_id')
            ->whereIn('lms_course_stages.lms_course_id', $enrolledCourseIds)
            ->whereNotNull('lms_stage_blocks.lms_test_id')
            ->distinct()
            ->pluck('lms_stage_blocks.lms_test_id');

        $availableTestIds = $directStageTestIds->merge($blockTestIds)->unique()->values();

        $query = LmsTest::where('lms_event_id', $event->id)
            ->where('is_active', true)
            ->whereIn('id', $availableTestIds);

        if ($search = $request->get('search')) {
            $query->where('title', 'ilike', '%' . $search . '%');
        }

        $tests = $query->paginate(12)->withQueryString();
        $testIds = collect($tests->items())->pluck('id');

        $attempts = LmsTestAttempt::whereIn('lms_test_id', $testIds)
            ->where('user_id', $user->id)
            ->get()
            ->groupBy('lms_test_id');

        $tests->getCollection()->transform(function ($test) use ($attempts) {
            $testAttempts = $attempts->get($test->id, collect());
            $test->attempt_count = $testAttempts->count();
            $bestAttempt = $testAttempts->where('passed', true)->sortByDesc('percentage')->first()
                ?? $testAttempts->sortByDesc('percentage')->first();
            $test->best_score = $bestAttempt?->percentage;
            return $test;
        });

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

        $questionsCount = $test->questions()->count();

        return Inertia::render('Lms/Tests/Show', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'test' => array_merge(
                $test->only([
                    'id', 'title', 'description',
                    'time_limit_minutes', 'shuffle_questions', 'shuffle_answers',
                    'passing_score', 'max_attempts',
                ]),
                ['questions_count' => $questionsCount]
            ),
            'attempts' => $attempts,
        ]);
    }

    public function start(Request $request, LmsEvent $event, LmsTest $test): RedirectResponse
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

        if ($returnUrl = $request->input('return_url')) {
            return redirect($returnUrl);
        }

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
            'status' => 'completed',
            'score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'passed' => $passed,
            'finished_at' => now(),
        ]);

        if ($passed) {
            $this->markLinkedStagesCompleted($test, $user);
        }

        if ($returnUrl = $request->input('return_url')) {
            return redirect($returnUrl);
        }

        return redirect()->route('lms.tests.result', [$event, $test, $attempt]);
    }

    private function markLinkedStagesCompleted(LmsTest $test, $user): void
    {
        $stageIds = collect();

        // Legacy: test linked directly to stage
        $directStageIds = LmsCourseStage::where('lms_test_id', $test->id)->pluck('id');
        $stageIds = $stageIds->merge($directStageIds);

        // Multi-block: test linked via stage_blocks
        $blockStageIds = LmsStageBlock::where('lms_test_id', $test->id)->pluck('lms_course_stage_id');
        $stageIds = $stageIds->merge($blockStageIds);

        $stageIds = $stageIds->unique();

        foreach ($stageIds as $stageId) {
            LmsStageProgress::updateOrCreate(
                ['lms_course_stage_id' => $stageId, 'user_id' => $user->id],
                ['status' => 'completed', 'completed_at' => now()]
            );

            $stage = LmsCourseStage::find($stageId);
            if ($stage) {
                $this->checkCourseCompletion($stage->lms_course_id, $user);
            }
        }
    }

    private function checkCourseCompletion(int $courseId, $user): void
    {
        $totalStages = LmsCourseStage::where('lms_course_id', $courseId)->count();
        $completedStages = LmsStageProgress::whereIn(
            'lms_course_stage_id',
            LmsCourseStage::where('lms_course_id', $courseId)->pluck('id')
        )
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        if ($completedStages >= $totalStages) {
            LmsCourseEnrollment::where('lms_course_id', $courseId)
                ->where('user_id', $user->id)
                ->update(['status' => 'completed', 'completed_at' => now()]);
        }
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

        $questionsCount = $test->questions()->count();

        return Inertia::render('Lms/Tests/Result', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'test' => array_merge(
                $test->only(['id', 'title', 'passing_score', 'show_correct_answers']),
                ['questions_count' => $questionsCount]
            ),
            'attempt' => $attempt->only(['id', 'score', 'max_score', 'percentage', 'passed', 'started_at', 'finished_at']),
        ]);
    }
}
