<?php

namespace App\Services;

use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGamificationPoint;
use App\Models\Lms\LmsGamificationRule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    public function awardPoints(LmsEvent $event, User $user, string $action, ?string $reason = null): void
    {
        $rules = LmsGamificationRule::where('lms_event_id', $event->id)
            ->where('action', $action)
            ->where('is_active', true)
            ->where('is_auto', true)
            ->get();

        foreach ($rules as $rule) {
            if ($rule->max_times !== null) {
                $count = LmsGamificationPoint::where('lms_event_id', $event->id)
                    ->where('user_id', $user->id)
                    ->where('lms_gamification_rule_id', $rule->id)
                    ->count();

                if ($count >= $rule->max_times) {
                    continue;
                }
            }

            LmsGamificationPoint::create([
                'lms_event_id' => $event->id,
                'user_id' => $user->id,
                'lms_gamification_rule_id' => $rule->id,
                'points' => $rule->points,
                'reason' => $reason ?? $rule->title,
            ]);
        }
    }

    public function getLeaderboard(LmsEvent $event, int $limit = 50): array
    {
        return DB::table('lms_gamification_points')
            ->select('users.id', 'users.name', DB::raw('SUM(lms_gamification_points.points) as total_points'))
            ->join('users', 'users.id', '=', 'lms_gamification_points.user_id')
            ->where('lms_gamification_points.lms_event_id', $event->id)
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getUserPoints(LmsEvent $event, User $user): int
    {
        return (int) LmsGamificationPoint::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->sum('points');
    }

    public function getUserRank(LmsEvent $event, User $user): int
    {
        $userTotal = $this->getUserPoints($event, $user);

        return DB::table('lms_gamification_points')
            ->select('user_id', DB::raw('SUM(points) as total'))
            ->where('lms_event_id', $event->id)
            ->groupBy('user_id')
            ->having(DB::raw('SUM(points)'), '>', $userTotal)
            ->count() + 1;
    }

    public static array $defaultActions = [
        'course_complete' => 'Завершение курса',
        'stage_complete' => 'Прохождение этапа',
        'test_pass' => 'Успешное прохождение теста',
        'assignment_approved' => 'Одобрение задания',
        'trajectory_complete' => 'Завершение траектории',
        'login_daily' => 'Ежедневный вход',
    ];
}
