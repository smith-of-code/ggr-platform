<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsCourseStage;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsStageProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $user = auth()->user();
        $courses = LmsCourse::where('lms_event_id', $event->id)
            ->where('is_active', true)
            ->orderBy('position')
            ->with('stages')
            ->get();

        $enrollmentIds = LmsCourseEnrollment::whereIn('lms_course_id', $courses->pluck('id'))
            ->where('user_id', $user->id)
            ->pluck('lms_course_id');

        $coursesWithStatus = $courses->map(function ($course) use ($user, $enrollmentIds) {
            $enrollment = LmsCourseEnrollment::where('lms_course_id', $course->id)
                ->where('user_id', $user->id)
                ->first();
            $completedStages = LmsStageProgress::whereIn('lms_course_stage_id', $course->stages->pluck('id'))
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->count();
            $progress = $course->stages->count() > 0
                ? round(($completedStages / $course->stages->count()) * 100)
                : 0;
            return [
                'course' => $course->only(['id', 'slug', 'title', 'description', 'image']),
                'enrolled' => $enrollmentIds->contains($course->id),
                'enrollment' => $enrollment?->only(['id', 'status', 'completed_at']),
                'progress' => $progress,
            ];
        });

        return Inertia::render('Lms/Courses/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'courses' => $coursesWithStatus,
        ]);
    }

    public function show(LmsEvent $event, LmsCourse $course): Response
    {
        if ($course->lms_event_id !== $event->id) {
            abort(404);
        }
        $user = auth()->user();
        $course->load('stages');

        $enrollment = LmsCourseEnrollment::where('lms_course_id', $course->id)
            ->where('user_id', $user->id)
            ->first();

        $stageProgress = LmsStageProgress::whereIn('lms_course_stage_id', $course->stages->pluck('id'))
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('lms_course_stage_id');

        $stagesWithProgress = $course->stages->sortBy('position')->map(function ($stage) use ($stageProgress, $user) {
            $progress = $stageProgress->get($stage->id);
            return [
                'stage' => $stage->only(['id', 'title', 'description', 'type', 'position', 'is_locked']),
                'progress' => $progress?->only(['status', 'completed_at', 'score']),
            ];
        })->values();

        return Inertia::render('Lms/Courses/Show', [
            'event' => $event->only(['id', 'slug', 'title']),
            'course' => $course->only(['id', 'slug', 'title', 'description', 'image']),
            'enrollment' => $enrollment?->only(['id', 'status', 'completed_at']),
            'stages' => $stagesWithProgress,
        ]);
    }

    public function enroll(LmsEvent $event, LmsCourse $course): RedirectResponse
    {
        if ($course->lms_event_id !== $event->id) {
            abort(404);
        }
        $user = auth()->user();

        LmsCourseEnrollment::firstOrCreate(
            [
                'lms_course_id' => $course->id,
                'user_id' => $user->id,
            ],
            ['status' => 'enrolled']
        );

        return redirect()->back();
    }
}
