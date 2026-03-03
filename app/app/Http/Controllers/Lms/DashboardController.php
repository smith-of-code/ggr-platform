<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsAssignment;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGamificationPoint;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsStageProgress;
use App\Models\Lms\LmsTrajectoryEnrollment;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $user = auth()->user();
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        $courseEnrollments = LmsCourseEnrollment::whereHas('course', fn($q) => $q->where('lms_event_id', $event->id))
            ->where('user_id', $user->id)
            ->with(['course.stages'])
            ->get();

        $courseProgress = $courseEnrollments->map(function ($enrollment) use ($user) {
            $stages = $enrollment->course->stages;
            $completedCount = LmsStageProgress::whereIn('lms_course_stage_id', $stages->pluck('id'))
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->count();
            $total = $stages->count();
            $progress = $total > 0 ? round(($completedCount / $total) * 100) : 0;
            return [
                'course' => $enrollment->course->only(['id', 'slug', 'title', 'image']),
                'enrollment' => $enrollment->only(['id', 'status', 'completed_at']),
                'progress' => $progress,
            ];
        });

        $activeTrajectories = LmsTrajectoryEnrollment::whereHas('trajectory', fn($q) => $q->where('lms_event_id', $event->id))
            ->where('user_id', $user->id)
            ->with('trajectory')
            ->get()
            ->map(fn($e) => $e->trajectory->only(['id', 'title', 'description']));

        $upcomingAssignments = LmsAssignment::where('lms_event_id', $event->id)
            ->where('is_active', true)
            ->whereNotNull('deadline')
            ->where('deadline', '>', now())
            ->orderBy('deadline')
            ->limit(10)
            ->get(['id', 'title', 'deadline']);

        $recentPoints = LmsGamificationPoint::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'points', 'reason', 'created_at']);

        return Inertia::render('Lms/Dashboard', [
            'event' => $event->only(['id', 'slug', 'title']),
            'profile' => $profile,
            'courseProgress' => $courseProgress,
            'activeTrajectories' => $activeTrajectories,
            'upcomingAssignments' => $upcomingAssignments,
            'recentPoints' => $recentPoints,
        ]);
    }
}
