<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsCourseStage;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsStageProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StageController extends Controller
{
    public function show(LmsEvent $event, LmsCourse $course, LmsCourseStage $stage): Response
    {
        if ($course->lms_event_id !== $event->id || $stage->lms_course_id !== $course->id) {
            abort(404);
        }
        $user = auth()->user();
        $stage->load(['test', 'assignment', 'video']);
        $progress = LmsStageProgress::where('lms_course_stage_id', $stage->id)
            ->where('user_id', $user->id)
            ->first();

        $allStages = $course->stages()->orderBy('position')->get(['id', 'title', 'type', 'position']);

        return Inertia::render('Lms/Courses/Stage', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'course' => $course->only(['id', 'slug', 'title']),
            'stage' => $stage->only([
                'id', 'title', 'description', 'type', 'content',
                'scorm_package', 'lms_test_id', 'lms_assignment_id', 'lms_video_id',
            ]),
            'stages' => $allStages,
            'linkedTest' => $stage->test?->only(['id', 'title']),
            'linkedAssignment' => $stage->assignment?->only(['id', 'title']),
            'linkedVideo' => $stage->video?->only(['id', 'title', 'url', 'source', 'duration_seconds']),
            'progress' => $progress?->only(['status', 'scorm_data', 'score', 'watched_seconds', 'completed_at']),
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
}
