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
            ->where('for_city_ranking_only', false)
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw('SUM(points) as total_points'))
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->limit(50)
            ->get();

        $users = \App\Models\User::whereIn('id', $userPoints->pluck('user_id'))->get()->keyBy('id');
        $userLeaderboard = $userPoints->map(fn ($row) => [
            'user' => $users->get($row->user_id)?->only(['id', 'name']),
            'total_points' => $row->total_points,
        ]);

        $gamification = app(GamificationService::class);
        $cityLeaderboard = $gamification->getCityLeaderboardAggregates($event);

        $user = auth()->user();
        $profile = \App\Models\Lms\LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->with('lmsRole:id,name,slug')
            ->first();
        $userLeaderboardData = $userLeaderboard->values()->all();
        $userRank = null;
        $userPointsTotal = null;
        foreach ($userLeaderboardData as $i => $row) {
            if (($row['user']['id'] ?? null) === $user->id) {
                $userRank = $i + 1;
                $userPointsTotal = $row['total_points'];
                break;
            }
        }
        if ($userPointsTotal === null) {
            $userPointsTotal = $gamification->getUserPoints($event, $user);
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
            'userPoints' => $userPointsTotal,
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
            ->where('for_city_ranking_only', false)
            ->orderByDesc('created_at')
            ->get(['id', 'points', 'reason', 'lms_gamification_rule_id', 'created_at']);

        $totalPoints = $points->sum('points');

        $profile = \App\Models\Lms\LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->with('lmsRole:id,name,slug')
            ->first();

        return Inertia::render('Lms/Gamification/MyPoints', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'points' => $points,
            'totalPoints' => $totalPoints,
        ]);
    }
}
