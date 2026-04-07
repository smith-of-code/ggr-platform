<?php

namespace App\Observers;

use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsStageProgress;
use App\Models\Lms\LmsTestAttempt;
use App\Models\Lms\LmsAssignmentReview;
use App\Models\Lms\LmsTrajectoryEnrollment;
use App\Models\User;
use App\Services\GamificationService;

class LmsProgressObserver
{
    public function __construct(
        private GamificationService $gamification
    ) {}

    public function stageCompleted(LmsStageProgress $progress): void
    {
        if ($progress->status !== 'completed') {
            return;
        }

        $stage = $progress->stage()->with('course.event')->first();
        if (!$stage?->course?->event || !$stage->course->unlocks_gamification) {
            return;
        }

        $this->gamification->awardPoints(
            $stage->course->event,
            User::find($progress->user_id),
            'stage_complete',
            "Этап: {$stage->title}"
        );
    }

    public function courseCompleted(LmsCourseEnrollment $enrollment): void
    {
        if ($enrollment->status !== 'completed') {
            return;
        }

        $course = $enrollment->course()->with('event')->first();
        if (!$course?->event || !$course->unlocks_gamification) {
            return;
        }

        $this->gamification->awardPoints(
            $course->event,
            User::find($enrollment->user_id),
            'course_complete',
            "Курс: {$course->title}"
        );
    }

    public function testPassed(LmsTestAttempt $attempt): void
    {
        if (!$attempt->passed) {
            return;
        }

        $test = $attempt->test()->with('event')->first();
        if (!$test?->event) {
            return;
        }

        if (!$this->isLinkedToGamificationCourse($test->id, null)) {
            return;
        }

        $this->gamification->awardPoints(
            $test->event,
            User::find($attempt->user_id),
            'test_pass',
            "Тест: {$test->title} ({$attempt->percentage}%)"
        );
    }

    public function assignmentApproved(LmsAssignmentReview $review): void
    {
        if ($review->decision !== 'approve') {
            return;
        }

        $submission = $review->submission()
            ->with('assignment.event')
            ->first();

        if (!$submission?->assignment?->event) {
            return;
        }

        if (!$this->isLinkedToGamificationCourse(null, $submission->assignment->id)) {
            return;
        }

        $this->gamification->awardPoints(
            $submission->assignment->event,
            User::find($submission->user_id),
            'assignment_approved',
            "Задание: {$submission->assignment->title}"
        );
    }

    private function isLinkedToGamificationCourse(?int $testId, ?int $assignmentId): bool
    {
        $column = $testId ? 'lms_test_id' : 'lms_assignment_id';
        $value = $testId ?? $assignmentId;

        $viaStage = \App\Models\Lms\LmsCourseStage::where($column, $value)
            ->whereHas('course', fn ($q) => $q->where('unlocks_gamification', true))
            ->exists();

        if ($viaStage) {
            return true;
        }

        return \App\Models\Lms\LmsStageBlock::where($column, $value)
            ->whereHas('stage.course', fn ($q) => $q->where('unlocks_gamification', true))
            ->exists();
    }

    public function trajectoryCompleted(LmsTrajectoryEnrollment $enrollment): void
    {
        if ($enrollment->status !== 'completed') {
            return;
        }

        $trajectory = $enrollment->trajectory()->first();
        if (!$trajectory) {
            return;
        }

        $event = LmsEvent::find($trajectory->lms_event_id);
        if (!$event) {
            return;
        }

        $this->gamification->awardPoints(
            $event,
            User::find($enrollment->user_id),
            'trajectory_complete',
            "Траектория: {$trajectory->title}"
        );
    }
}
