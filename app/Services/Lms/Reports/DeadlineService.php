<?php

namespace App\Services\Lms\Reports;

use App\Models\Lms\LmsAssignment;
use App\Models\Lms\LmsAssignmentSubmission;
use App\Models\Lms\LmsEvent;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class DeadlineService
{
    public const STATUS_ON_TIME = 'on_time';

    public const STATUS_LATE = 'late';

    public const STATUS_OVERDUE = 'overdue';

    /**
     * Резолв дедлайна по цепочке:
     * assignment.deadline → event.default_assignment_deadline → config('lms.reports.default_deadline').
     * Возвращает Carbon в UTC. Если задание не передано (например, для теста) —
     * берём дедлайн события или фолбэк из конфига.
     */
    public function resolveDeadline(?LmsAssignment $assignment, LmsEvent $event): Carbon
    {
        $value = $assignment?->deadline
            ?? $event->default_assignment_deadline
            ?? config('lms.reports.default_deadline');

        return $this->toUtc($value);
    }

    /**
     * Момент принятия submission — created_at последней review с decision='approve'.
     * Возвращает null, если ни одной approve-review нет (включая случай «не сдавал совсем»).
     *
     * Если коллекция reviews уже загружена через eager-loading — используем её,
     * чтобы избежать N+1 при массовом расчёте отчёта. Иначе делаем точечный запрос.
     */
    public function resolveApprovedAt(LmsAssignmentSubmission $submission): ?Carbon
    {
        if ($submission->relationLoaded('reviews')) {
            $review = $submission->reviews
                ->where('decision', 'approve')
                ->sortByDesc('created_at')
                ->first();
        } else {
            $review = $submission->reviews()
                ->where('decision', 'approve')
                ->orderByDesc('created_at')
                ->first();
        }

        if (! $review) {
            return null;
        }

        return $this->toUtc($review->created_at);
    }

    /**
     * Классификация подачи относительно дедлайна:
     * - on_time: одобрено до дедлайна (включительно)
     * - late: одобрено после дедлайна
     * - overdue: одобрения нет (в т.ч. «не сдавал совсем»)
     */
    public function classify(Carbon $deadline, ?Carbon $approvedAt): string
    {
        if ($approvedAt === null) {
            return self::STATUS_OVERDUE;
        }

        return $approvedAt->lessThanOrEqualTo($deadline)
            ? self::STATUS_ON_TIME
            : self::STATUS_LATE;
    }

    /**
     * Длительность задержки в днях (>=0). Без одобрения и для on_time — 0.
     * Для late считаем как разницу полных дней между approved_at и deadline (округление вверх).
     */
    public function delayDays(Carbon $deadline, ?Carbon $approvedAt): int
    {
        if ($approvedAt === null || $approvedAt->lessThanOrEqualTo($deadline)) {
            return 0;
        }

        return (int) ceil($deadline->diffInSeconds($approvedAt) / 86400);
    }

    private function toUtc(mixed $value): Carbon
    {
        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value)->utc();
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->utc();
        }

        return Carbon::parse((string) $value)->utc();
    }
}
