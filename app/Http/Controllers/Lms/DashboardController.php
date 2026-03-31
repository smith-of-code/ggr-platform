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
use App\Services\GamificationService;
use Illuminate\Support\Facades\DB;
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
            ->whereNotIn('status', ['pending', 'rejected'])
            ->with(['course.stages'])
            ->get();

        $courses = $courseEnrollments->map(function ($enrollment) use ($user) {
            $stages = $enrollment->course->stages;
            $completedCount = LmsStageProgress::whereIn('lms_course_stage_id', $stages->pluck('id'))
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->count();
            $total = $stages->count();
            $progress = $total > 0 ? round(($completedCount / $total) * 100) : 0;
            return [
                'id' => $enrollment->course->id,
                'slug' => $enrollment->course->slug,
                'title' => $enrollment->course->title,
                'image' => $enrollment->course->image,
                'description' => $enrollment->course->description,
                'progress_percent' => $progress,
                'status' => $enrollment->status,
                'completed_at' => $enrollment->completed_at,
            ];
        })->values();

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

        $totalPoints = LmsGamificationPoint::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->sum('points');

        $userRank = app(GamificationService::class)->getUserRank($event, $user);

        $cityRank = null;
        $cityName = $profile?->city;
        if ($cityName) {
            $cityAvgs = DB::table('lms_profiles')
                ->leftJoin('lms_gamification_points', function ($join) use ($event) {
                    $join->on('lms_profiles.user_id', '=', 'lms_gamification_points.user_id')
                         ->where('lms_gamification_points.lms_event_id', '=', $event->id);
                })
                ->where('lms_profiles.lms_event_id', $event->id)
                ->whereNotNull('lms_profiles.city')
                ->where('lms_profiles.city', '!=', '')
                ->select(
                    'lms_profiles.city',
                    DB::raw('ROUND(COALESCE(SUM(lms_gamification_points.points), 0)::numeric / GREATEST(COUNT(DISTINCT lms_profiles.user_id), 1), 1) as avg_points')
                )
                ->groupBy('lms_profiles.city')
                ->orderByDesc('avg_points')
                ->pluck('avg_points', 'city');

            $rank = 1;
            foreach ($cityAvgs as $city => $avg) {
                if ($city === $cityName) {
                    $cityRank = $rank;
                    break;
                }
                $rank++;
            }
        }

        return Inertia::render('Lms/Dashboard', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'user' => $user->only(['id', 'name', 'last_name', 'first_name', 'patronymic', 'email']),
            'profile' => $profile,
            'isProfileComplete' => $profile?->isProfileComplete() ?? false,
            'courses' => $courses,
            'trajectories' => $activeTrajectories,
            'upcomingAssignments' => $upcomingAssignments,
            'recentPoints' => $recentPoints,
            'totalPoints' => (int) $totalPoints,
            'userRank' => $userRank,
            'cityRank' => $cityRank,
            'cityName' => $cityName,
        ]);
    }
}
