<?php

namespace Tests\Unit\Lms\Reports;

use App\Models\Lms\LmsAssignment;
use App\Models\Lms\LmsAssignmentReview;
use App\Models\Lms\LmsAssignmentSubmission;
use App\Models\Lms\LmsEvent;
use App\Services\Lms\Reports\DeadlineService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class DeadlineServiceTest extends TestCase
{
    private DeadlineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DeadlineService;
        config()->set('lms.reports.default_deadline', '2026-06-20T23:59:59Z');
    }

    public function test_resolve_deadline_uses_assignment_deadline_first(): void
    {
        $event = new LmsEvent;
        $event->forceFill(['default_assignment_deadline' => Carbon::parse('2026-05-01 00:00:00', 'UTC')]);

        $assignment = new LmsAssignment;
        $assignment->forceFill(['deadline' => Carbon::parse('2026-04-15 12:00:00', 'UTC')]);

        $deadline = $this->service->resolveDeadline($assignment, $event);

        $this->assertSame('2026-04-15T12:00:00+00:00', $deadline->toIso8601String());
    }

    public function test_resolve_deadline_falls_back_to_event_default(): void
    {
        $event = new LmsEvent;
        $event->forceFill(['default_assignment_deadline' => Carbon::parse('2026-05-01 00:00:00', 'UTC')]);

        $assignment = new LmsAssignment;

        $deadline = $this->service->resolveDeadline($assignment, $event);

        $this->assertSame('2026-05-01T00:00:00+00:00', $deadline->toIso8601String());
    }

    public function test_resolve_deadline_falls_back_to_config_when_event_empty(): void
    {
        $event = new LmsEvent;
        $assignment = new LmsAssignment;

        $deadline = $this->service->resolveDeadline($assignment, $event);

        $this->assertSame('2026-06-20T23:59:59+00:00', $deadline->toIso8601String());
    }

    public function test_resolve_deadline_accepts_null_assignment_for_tests(): void
    {
        $event = new LmsEvent;
        $event->forceFill(['default_assignment_deadline' => Carbon::parse('2026-05-10 09:00:00', 'UTC')]);

        $deadline = $this->service->resolveDeadline(null, $event);

        $this->assertSame('2026-05-10T09:00:00+00:00', $deadline->toIso8601String());
    }

    public function test_resolve_approved_at_returns_latest_approve_review(): void
    {
        $reviewOld = new LmsAssignmentReview;
        $reviewOld->forceFill([
            'decision' => 'approve',
            'created_at' => Carbon::parse('2026-04-01 10:00:00', 'UTC'),
        ]);
        $reviewReject = new LmsAssignmentReview;
        $reviewReject->forceFill([
            'decision' => 'reject',
            'created_at' => Carbon::parse('2026-04-05 10:00:00', 'UTC'),
        ]);
        $reviewNew = new LmsAssignmentReview;
        $reviewNew->forceFill([
            'decision' => 'approve',
            'created_at' => Carbon::parse('2026-04-10 10:00:00', 'UTC'),
        ]);

        $submission = new LmsAssignmentSubmission;
        $submission->setRelation('reviews', new Collection([$reviewOld, $reviewReject, $reviewNew]));

        $approvedAt = $this->service->resolveApprovedAt($submission);

        $this->assertNotNull($approvedAt);
        $this->assertSame('2026-04-10T10:00:00+00:00', $approvedAt->toIso8601String());
    }

    public function test_resolve_approved_at_returns_null_when_no_approve_review(): void
    {
        $reviewReject = new LmsAssignmentReview;
        $reviewReject->forceFill([
            'decision' => 'reject',
            'created_at' => Carbon::parse('2026-04-05 10:00:00', 'UTC'),
        ]);

        $submission = new LmsAssignmentSubmission;
        $submission->setRelation('reviews', new Collection([$reviewReject]));

        $this->assertNull($this->service->resolveApprovedAt($submission));
    }

    public function test_resolve_approved_at_returns_null_when_no_reviews_at_all(): void
    {
        $submission = new LmsAssignmentSubmission;
        $submission->setRelation('reviews', new Collection);

        $this->assertNull($this->service->resolveApprovedAt($submission));
    }

    public function test_classify_returns_overdue_when_no_approval(): void
    {
        $deadline = Carbon::parse('2026-05-01 00:00:00', 'UTC');

        $this->assertSame(DeadlineService::STATUS_OVERDUE, $this->service->classify($deadline, null));
    }

    public function test_classify_returns_on_time_when_approved_before_deadline(): void
    {
        $deadline = Carbon::parse('2026-05-01 00:00:00', 'UTC');
        $approved = Carbon::parse('2026-04-30 23:59:00', 'UTC');

        $this->assertSame(DeadlineService::STATUS_ON_TIME, $this->service->classify($deadline, $approved));
    }

    public function test_classify_returns_on_time_when_approved_exactly_at_deadline(): void
    {
        $deadline = Carbon::parse('2026-05-01 00:00:00', 'UTC');
        $approved = Carbon::parse('2026-05-01 00:00:00', 'UTC');

        $this->assertSame(DeadlineService::STATUS_ON_TIME, $this->service->classify($deadline, $approved));
    }

    public function test_classify_returns_late_when_approved_after_deadline(): void
    {
        $deadline = Carbon::parse('2026-05-01 00:00:00', 'UTC');
        $approved = Carbon::parse('2026-05-01 00:00:01', 'UTC');

        $this->assertSame(DeadlineService::STATUS_LATE, $this->service->classify($deadline, $approved));
    }

    public function test_delay_days_zero_when_on_time_or_no_approval(): void
    {
        $deadline = Carbon::parse('2026-05-01 00:00:00', 'UTC');

        $this->assertSame(0, $this->service->delayDays($deadline, null));
        $this->assertSame(0, $this->service->delayDays($deadline, Carbon::parse('2026-04-30 12:00:00', 'UTC')));
        $this->assertSame(0, $this->service->delayDays($deadline, Carbon::parse('2026-05-01 00:00:00', 'UTC')));
    }

    public function test_delay_days_rounds_up_for_partial_day_late(): void
    {
        $deadline = Carbon::parse('2026-05-01 00:00:00', 'UTC');

        $this->assertSame(1, $this->service->delayDays($deadline, Carbon::parse('2026-05-01 00:00:01', 'UTC')));
        $this->assertSame(1, $this->service->delayDays($deadline, Carbon::parse('2026-05-01 23:00:00', 'UTC')));
        $this->assertSame(2, $this->service->delayDays($deadline, Carbon::parse('2026-05-02 00:00:01', 'UTC')));
        $this->assertSame(7, $this->service->delayDays($deadline, Carbon::parse('2026-05-08 00:00:00', 'UTC')));
    }
}
