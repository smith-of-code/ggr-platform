<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsAssignment;
use App\Models\Lms\LmsAssignmentSubmission;
use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsCourseStage;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsStageProgress;
use App\Models\Lms\LmsTest;
use App\Models\Lms\LmsTestAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StageController extends Controller
{
    private function ensureSequentialAccess(LmsCourse $course, LmsCourseStage $stage): void
    {
        if (!$course->sequential) {
            return;
        }

        $previousStage = $course->stages()
            ->where('position', '<', $stage->position)
            ->orderByDesc('position')
            ->first();

        if (!$previousStage) {
            return;
        }

        $previousCompleted = LmsStageProgress::where('lms_course_stage_id', $previousStage->id)
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->exists();

        if (!$previousCompleted) {
            abort(403, 'Необходимо сначала завершить предыдущий этап.');
        }
    }

    public function show(LmsEvent $event, LmsCourse $course, LmsCourseStage $stage): Response
    {
        if ($course->lms_event_id !== $event->id || $stage->lms_course_id !== $course->id) {
            abort(404);
        }
        $this->ensureSequentialAccess($course, $stage);
        $user = auth()->user();
        $stage->load(['test', 'assignment', 'video', 'blocks.test', 'blocks.assignment', 'blocks.video']);
        $progress = LmsStageProgress::where('lms_course_stage_id', $stage->id)
            ->where('user_id', $user->id)
            ->first();

        $allStages = $course->stages()->orderBy('position')->get(['id', 'title', 'type', 'position']);

        $blocks = $stage->blocks->map(function ($block) {
            return [
                'id' => $block->id,
                'type' => $block->type,
                'content' => $block->content,
                'scorm_package' => $block->scorm_package,
                'lms_test_id' => $block->lms_test_id,
                'lms_assignment_id' => $block->lms_assignment_id,
                'lms_video_id' => $block->lms_video_id,
                'position' => $block->position,
                'scheduled_at' => $block->scheduled_at?->format('Y-m-d\TH:i:s'),
                'scheduled_ends_at' => $block->scheduled_ends_at?->format('Y-m-d\TH:i:s'),
                'test' => $block->test?->only(['id', 'title']),
                'assignment' => $block->assignment?->only(['id', 'title']),
                'video' => $block->video?->only(['id', 'title', 'url', 'source', 'duration_seconds']),
            ];
        });

        $inlineTestData = $this->loadInlineTestData($stage, $blocks, $user, $event);
        $inlineAssignmentData = $this->loadInlineAssignmentData($stage, $blocks, $user, $event);

        return Inertia::render('Lms/Courses/Stage', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'course' => $course->only(['id', 'slug', 'title']),
            'stage' => $stage->only([
                'id', 'title', 'description', 'type', 'content',
                'scorm_package', 'lms_test_id', 'lms_assignment_id', 'lms_video_id',
            ]),
            'blocks' => $blocks,
            'stages' => $allStages,
            'linkedTest' => $stage->test?->only(['id', 'title']),
            'linkedAssignment' => $stage->assignment?->only(['id', 'title']),
            'linkedVideo' => $stage->video?->only(['id', 'title', 'url', 'source', 'duration_seconds']),
            'progress' => $progress?->only(['status', 'scorm_data', 'score', 'watched_seconds', 'completed_at']),
            'inlineTest' => $inlineTestData,
            'inlineAssignment' => $inlineAssignmentData,
        ]);
    }

    public function complete(
        Request $request,
        LmsEvent $event,
        LmsCourse $course,
        LmsCourseStage $stage
    ): RedirectResponse {
        if ($course->lms_event_id !== $event->id || $stage->lms_course_id !== $course->id) {
            abort(404);
        }
        $this->ensureSequentialAccess($course, $stage);
        $user = auth()->user();

        if ($stage->type === 'video' && $stage->video?->duration_seconds) {
            $progress = LmsStageProgress::where('lms_course_stage_id', $stage->id)
                ->where('user_id', $user->id)
                ->first();

            $watched = $progress?->watched_seconds ?? 0;
            $required = (int) ($stage->video->duration_seconds * 0.9);

            if ($watched < $required) {
                return redirect()->back()->withErrors([
                    'video' => 'Необходимо просмотреть видео полностью перед завершением этапа.',
                ]);
            }
        }

        LmsStageProgress::updateOrCreate(
            [
                'lms_course_stage_id' => $stage->id,
                'user_id' => $user->id,
            ],
            [
                'status' => 'completed',
                'completed_at' => now(),
            ]
        );

        $totalStages = $course->stages()->count();
        $completedCount = LmsStageProgress::whereIn('lms_course_stage_id', $course->stages()->pluck('id'))
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        if ($completedCount >= $totalStages) {
            LmsCourseEnrollment::where('lms_course_id', $course->id)
                ->where('user_id', $user->id)
                ->update(['status' => 'completed', 'completed_at' => now()]);
        }

        return redirect()->back();
    }

    public function heartbeat(
        Request $request,
        LmsEvent $event,
        LmsCourse $course,
        LmsCourseStage $stage
    ): JsonResponse {
        if ($course->lms_event_id !== $event->id || $stage->lms_course_id !== $course->id) {
            abort(404);
        }

        $data = $request->validate([
            'watched_seconds' => ['required', 'integer', 'min:0'],
        ]);

        $user = auth()->user();

        $progress = LmsStageProgress::firstOrCreate(
            [
                'lms_course_stage_id' => $stage->id,
                'user_id' => $user->id,
            ],
            ['status' => 'in_progress']
        );

        if ($data['watched_seconds'] > $progress->watched_seconds) {
            $progress->update(['watched_seconds' => $data['watched_seconds']]);
        }

        return response()->json([
            'watched_seconds' => $progress->watched_seconds,
        ]);
    }

    public function scormData(
        Request $request,
        LmsEvent $event,
        LmsCourse $course,
        LmsCourseStage $stage
    ): JsonResponse {
        if ($course->lms_event_id !== $event->id || $stage->lms_course_id !== $course->id) {
            abort(404);
        }
        $user = auth()->user();
        $data = $request->validate(['data' => ['required', 'array']]);

        LmsStageProgress::updateOrCreate(
            [
                'lms_course_stage_id' => $stage->id,
                'user_id' => $user->id,
            ],
            ['scorm_data' => $data['data']]
        );

        return response()->json(['success' => true]);
    }

    private function loadInlineTestData($stage, $blocks, $user, LmsEvent $event): ?array
    {
        $testId = $stage->lms_test_id;
        if (!$testId) {
            $testBlock = $blocks->firstWhere('type', 'test');
            $testId = $testBlock['lms_test_id'] ?? null;
        }
        if (!$testId) {
            return null;
        }

        $test = LmsTest::find($testId);
        if (!$test) {
            return null;
        }

        $questionsCount = $test->questions()->count();
        $attempts = LmsTestAttempt::where('lms_test_id', $test->id)
            ->where('user_id', $user->id)
            ->orderByDesc('started_at')
            ->get(['id', 'score', 'max_score', 'percentage', 'passed', 'started_at', 'finished_at', 'status']);

        $activeAttempt = $attempts->firstWhere('status', 'in_progress');
        $latestCompleted = $attempts->first(fn ($a) => $a->status === 'completed' || $a->finished_at);

        $questions = null;
        $attemptData = null;

        if ($activeAttempt) {
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
            $attemptData = $activeAttempt->only(['id', 'status', 'started_at']);
        }

        return [
            'test' => array_merge(
                $test->only(['id', 'title', 'description', 'time_limit_minutes', 'passing_score', 'max_attempts']),
                ['questions_count' => $questionsCount]
            ),
            'attempts' => $attempts,
            'activeAttempt' => $attemptData,
            'questions' => $questions,
            'latestResult' => $latestCompleted?->only(['id', 'score', 'max_score', 'percentage', 'passed']),
        ];
    }

    private function loadInlineAssignmentData($stage, $blocks, $user, LmsEvent $event): ?array
    {
        $assignmentId = $stage->lms_assignment_id;
        if (!$assignmentId) {
            $assignmentBlock = $blocks->firstWhere('type', 'assignment');
            $assignmentId = $assignmentBlock['lms_assignment_id'] ?? null;
        }
        if (!$assignmentId) {
            return null;
        }

        $assignment = LmsAssignment::with('tasks')->find($assignmentId);
        if (!$assignment) {
            return null;
        }

        $submission = LmsAssignmentSubmission::where('lms_assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->with(['reviews.reviewer:id,name', 'comments.user:id,name', 'answers.task'])
            ->first();

        return [
            'assignment' => array_merge(
                $assignment->only(['id', 'title', 'description', 'template_file', 'template_file_name', 'deadline', 'completion_mode']),
                ['tasks' => $assignment->tasks]
            ),
            'submission' => $submission,
        ];
    }
}
