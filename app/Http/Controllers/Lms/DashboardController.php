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
            ->with(['lmsRole:id,name,slug', 'cityRelation:id,name'])
            ->first();

        if ($profile && in_array($profile->status, ['imported', 'invited'])) {
            $profile->update([
                'status' => 'active',
                'activated_at' => $profile->activated_at ?? now(),
            ]);
        }

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
        $enrolledCourseIds = $courseEnrollments->pluck('course.id');
        $availableAssignmentIds = DB::table('lms_course_stages')
            ->whereIn('lms_course_id', $enrolledCourseIds)
            ->whereNotNull('lms_assignment_id')
            ->pluck('lms_assignment_id')
            ->merge(
                DB::table('lms_stage_blocks')
                    ->join('lms_course_stages', 'lms_course_stages.id', '=', 'lms_stage_blocks.lms_course_stage_id')
                    ->whereIn('lms_course_stages.lms_course_id', $enrolledCourseIds)
                    ->whereNotNull('lms_stage_blocks.lms_assignment_id')
                    ->pluck('lms_stage_blocks.lms_assignment_id')
            )
            ->unique()
            ->values();

        $activeTrajectories = LmsTrajectoryEnrollment::whereHas('trajectory', fn($q) => $q->where('lms_event_id', $event->id))
            ->where('user_id', $user->id)
            ->with('trajectory')
            ->get()
            ->map(fn($e) => $e->trajectory->only(['id', 'title', 'description']));

        $upcomingAssignments = LmsAssignment::where('lms_event_id', $event->id)
            ->where('is_active', true)
            ->whereIn('id', $availableAssignmentIds)
            ->whereNotNull('deadline')
            ->where('deadline', '>', now())
            ->orderBy('deadline')
            ->limit(10)
            ->get(['id', 'title', 'deadline']);

        $recentPoints = LmsGamificationPoint::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->where('for_city_ranking_only', false)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'points', 'reason', 'created_at']);

        $totalPoints = LmsGamificationPoint::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->where('for_city_ranking_only', false)
            ->sum('points');

        $userRank = app(GamificationService::class)->getUserRank($event, $user);

        $cityRank = null;
        $cityName = $profile ? ($profile->cityRelation?->name ?? $profile->city) : null;
        if ($cityName) {
            $rank = 1;
            foreach (app(GamificationService::class)->getCityLeaderboardAggregates($event) as $row) {
                if ($row->city === $cityName) {
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
            'isProfileComplete' => $profile ? $profile->isProfileComplete() : false,
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
