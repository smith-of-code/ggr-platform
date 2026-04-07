<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGamificationPoint;
use App\Services\GamificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GamificationController extends Controller
{
    public function leaderboard(LmsEvent $event): Response|RedirectResponse
    {
        if (!app(GamificationService::class)->isGamificationEnabled($event, auth()->user())) {
            return redirect()->route('lms.dashboard', $event)->with('error', 'Геймификация пока недоступна');
        }

        $adminUserIds = \App\Models\Lms\LmsProfile::where('lms_event_id', $event->id)
            ->where('role', 'admin')
            ->pluck('user_id');

        $userPoints = DB::table('lms_gamification_points')
            ->where('lms_event_id', $event->id)
            ->whereNotIn('user_id', $adminUserIds)
            ->select('user_id', DB::raw('SUM(points) as total_points'))
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->limit(50)
            ->get();

        $users = \App\Models\User::whereIn('id', $userPoints->pluck('user_id'))->get()->keyBy('id');
        $userLeaderboard = $userPoints->map(fn($row) => [
            'user' => $users->get($row->user_id)?->only(['id', 'name']),
            'total_points' => $row->total_points,
        ]);

        $cityLeaderboard = DB::table('lms_profiles')
            ->leftJoin('lms_gamification_points', function ($join) use ($event) {
                $join->on('lms_profiles.user_id', '=', 'lms_gamification_points.user_id')
                     ->where('lms_gamification_points.lms_event_id', '=', $event->id);
            })
            ->where('lms_profiles.lms_event_id', $event->id)
            ->where('lms_profiles.role', '!=', 'admin')
            ->whereNotNull('lms_profiles.city')
            ->where('lms_profiles.city', '!=', '')
            ->select(
                'lms_profiles.city',
                DB::raw('COUNT(DISTINCT lms_profiles.user_id) as members_count'),
                DB::raw('COALESCE(SUM(lms_gamification_points.points), 0) as total_points'),
                DB::raw('ROUND(COALESCE(SUM(lms_gamification_points.points), 0)::numeric / GREATEST(COUNT(DISTINCT lms_profiles.user_id), 1), 1) as avg_points')
            )
            ->groupBy('lms_profiles.city')
            ->orderByDesc('avg_points')
            ->get();

        $user = auth()->user();
        $profile = \App\Models\Lms\LmsProfile::where('lms_event_id', $event->id)->where('user_id', $user->id)->first();
        $userLeaderboardData = $userLeaderboard->values()->all();
        $userRank = null;
        $userPoints = null;
        foreach ($userLeaderboardData as $i => $row) {
            if (($row['user']['id'] ?? null) === $user->id) {
                $userRank = $i + 1;
                $userPoints = $row['total_points'];
                break;
            }
        }
        if ($userPoints === null) {
            $userPoints = DB::table('lms_gamification_points')
                ->where('lms_event_id', $event->id)
                ->where('user_id', $user->id)
                ->sum('points');
        }

        $userCityName = $profile?->city;
        $userCityRank = null;
        $userCityAvg = null;
        if ($userCityName) {
            foreach ($cityLeaderboard as $i => $row) {
                if ($row->city === $userCityName) {
                    $userCityRank = $i + 1;
                    $userCityAvg = $row->avg_points;
                    break;
                }
            }
        }

        return Inertia::render('Lms/Gamification/Leaderboard', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'userLeaderboard' => $userLeaderboardData,
            'cityLeaderboard' => $cityLeaderboard,
            'userRank' => $userRank,
            'userPoints' => $userPoints,
            'userCityRank' => $userCityRank,
            'userCityName' => $userCityName,
            'userCityAvg' => $userCityAvg,
        ]);
    }

    public function myPoints(LmsEvent $event): Response|RedirectResponse
    {
        if (!app(GamificationService::class)->isGamificationEnabled($event, auth()->user())) {
            return redirect()->route('lms.dashboard', $event)->with('error', 'Геймификация пока недоступна');
        }

        $user = auth()->user();
        $points = LmsGamificationPoint::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get(['id', 'points', 'reason', 'lms_gamification_rule_id', 'created_at']);

        $totalPoints = $points->sum('points');

        $profile = \App\Models\Lms\LmsProfile::where('lms_event_id', $event->id)->where('user_id', $user->id)->first();

        return Inertia::render('Lms/Gamification/MyPoints', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'points' => $points,
            'totalPoints' => $totalPoints,
        ]);
    }
}
