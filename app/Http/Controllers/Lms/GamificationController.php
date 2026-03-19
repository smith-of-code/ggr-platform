<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGamificationPoint;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GamificationController extends Controller
{
    public function leaderboard(LmsEvent $event): Response
    {
        $userPoints = DB::table('lms_gamification_points')
            ->where('lms_event_id', $event->id)
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

        $groupPoints = DB::table('lms_gamification_points')
            ->join('lms_group_members', 'lms_gamification_points.user_id', '=', 'lms_group_members.user_id')
            ->where('lms_gamification_points.lms_event_id', $event->id)
            ->select('lms_group_members.lms_group_id', DB::raw('SUM(lms_gamification_points.points) as total_points'))
            ->groupBy('lms_group_members.lms_group_id')
            ->orderByDesc('total_points')
            ->limit(20)
            ->get();

        $groupIds = $groupPoints->pluck('lms_group_id')->unique()->filter();
        $groups = $groupIds->isNotEmpty()
            ? \App\Models\Lms\LmsGroup::whereIn('id', $groupIds)->get()->keyBy('id')
            : collect();

        $groupLeaderboard = $groupPoints->map(function ($row) use ($groups) {
            return [
                'group' => $groups->get($row->lms_group_id)?->only(['id', 'title']),
                'total_points' => $row->total_points,
            ];
        });

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

        return Inertia::render('Lms/Gamification/Leaderboard', [
            'event' => $event->only(['id', 'slug', 'title']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'userLeaderboard' => $userLeaderboardData,
            'groupLeaderboard' => $groupLeaderboard,
            'userRank' => $userRank,
            'userPoints' => $userPoints,
        ]);
    }

    public function myPoints(LmsEvent $event): Response
    {
        $user = auth()->user();
        $points = LmsGamificationPoint::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get(['id', 'points', 'reason', 'lms_gamification_rule_id', 'created_at']);

        $totalPoints = $points->sum('points');

        $profile = \App\Models\Lms\LmsProfile::where('lms_event_id', $event->id)->where('user_id', $user->id)->first();

        return Inertia::render('Lms/Gamification/MyPoints', [
            'event' => $event->only(['id', 'slug', 'title']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'points' => $points,
            'totalPoints' => $totalPoints,
        ]);
    }
}
