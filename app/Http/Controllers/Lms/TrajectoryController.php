<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsStageProgress;
use App\Models\Lms\LmsTrajectory;
use App\Models\Lms\LmsTrajectoryEnrollment;
use App\Models\Lms\LmsTrajectoryStep;
use App\Models\Lms\LmsProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrajectoryController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $user = auth()->user();
        $trajectories = LmsTrajectory::where('lms_event_id', $event->id)
            ->where('is_active', true)
            ->with('steps.course.stages')
            ->get();

        $enrollmentIds = LmsTrajectoryEnrollment::whereIn('lms_trajectory_id', $trajectories->pluck('id'))
            ->where('user_id', $user->id)
            ->pluck('lms_trajectory_id');

        $trajectoriesWithStatus = $trajectories->map(function ($trajectory) use ($enrollmentIds, $user) {
            $enrollment = LmsTrajectoryEnrollment::where('lms_trajectory_id', $trajectory->id)
                ->where('user_id', $user->id)
                ->first();
            $stepsCount = $trajectory->steps->count();
            $completedSteps = 0;
            foreach ($trajectory->steps as $step) {
                $course = $step->course;
                if (!$course) continue;
                $stageIds = $course->stages->pluck('id');
                if ($stageIds->isEmpty()) continue;
                $completed = LmsStageProgress::whereIn('lms_course_stage_id', $stageIds)
                    ->where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->count();
                if ($completed >= $stageIds->count()) {
                    $completedSteps++;
                }
            }
            return [
                'trajectory' => $trajectory->only(['id', 'title', 'description']),
                'enrolled' => $enrollmentIds->contains($trajectory->id),
                'enrollment' => $enrollment?->only(['id', 'status', 'completed_at']),
                'steps_count' => $stepsCount,
                'completed_steps' => $completedSteps,
            ];
        });

        $profile = LmsProfile::where('lms_event_id', $event->id)->where('user_id', $user->id)->first();

        return Inertia::render('Lms/Trajectories/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'trajectories' => $trajectoriesWithStatus,
        ]);
    }

    public function show(LmsEvent $event, LmsTrajectory $trajectory): Response
    {
        if ($trajectory->lms_event_id !== $event->id) {
            abort(404);
        }
        $user = auth()->user();
        $trajectory->load('steps.course.stages');

        $enrollment = LmsTrajectoryEnrollment::where('lms_trajectory_id', $trajectory->id)
            ->where('user_id', $user->id)
            ->first();

        $stepsWithProgress = $trajectory->steps->sortBy('position')->map(function ($step) use ($user) {
            $course = $step->course;
            $totalStages = $course?->stages->count() ?? 0;
            $completedStages = $totalStages > 0
                ? LmsStageProgress::whereIn('lms_course_stage_id', $course->stages->pluck('id'))
                    ->where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->count()
                : 0;
            $progress = $totalStages > 0 ? round(($completedStages / $totalStages) * 100) : 0;
            return [
                'step' => $step->only(['id', 'lms_course_id', 'is_locked', 'opens_at', 'position']),
                'course' => $course?->only(['id', 'slug', 'title']),
                'progress' => $progress,
            ];
        })->values();

        $profile = LmsProfile::where('lms_event_id', $event->id)->where('user_id', $user->id)->first();

        return Inertia::render('Lms/Trajectories/Show', [
            'event' => $event->only(['id', 'slug', 'title']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'trajectory' => $trajectory->only(['id', 'title', 'description']),
            'enrollment' => $enrollment?->only(['id', 'status', 'completed_at']),
            'steps' => $stepsWithProgress,
        ]);
    }

    public function enroll(LmsEvent $event, LmsTrajectory $trajectory): RedirectResponse
    {
        if ($trajectory->lms_event_id !== $event->id) {
            abort(404);
        }
        $user = auth()->user();

        $enrollment = LmsTrajectoryEnrollment::firstOrCreate(
            [
                'lms_trajectory_id' => $trajectory->id,
                'user_id' => $user->id,
            ],
            ['status' => 'enrolled']
        );

        $unlockedCourses = $trajectory->steps()
            ->where('is_locked', false)
            ->where(function ($q) {
                $q->whereNull('opens_at')->orWhere('opens_at', '<=', now());
            })
            ->pluck('lms_course_id')
            ->unique();

        foreach ($unlockedCourses as $courseId) {
            LmsCourseEnrollment::firstOrCreate(
                ['lms_course_id' => $courseId, 'user_id' => $user->id],
                ['status' => 'enrolled']
            );
        }

        return redirect()->back();
    }
}
