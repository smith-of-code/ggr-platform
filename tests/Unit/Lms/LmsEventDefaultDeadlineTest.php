<?php

namespace Tests\Unit\Lms;

use App\Models\Lms\LmsEvent;
use Carbon\Carbon;
use Tests\TestCase;

class LmsEventDefaultDeadlineTest extends TestCase
{
    public function test_default_assignment_deadline_is_in_fillable(): void
    {
        $event = new LmsEvent;
        $this->assertContains('default_assignment_deadline', $event->getFillable());
    }

    public function test_default_assignment_deadline_is_cast_to_datetime(): void
    {
        $event = new LmsEvent;
        $casts = $event->getCasts();
        $this->assertArrayHasKey('default_assignment_deadline', $casts);
        $this->assertSame('datetime', $casts['default_assignment_deadline']);
    }

    public function test_serialize_date_outputs_utc_iso8601_with_z(): void
    {
        $event = new LmsEvent;
        $event->forceFill([
            'default_assignment_deadline' => Carbon::parse('2026-06-20 23:59:59', 'UTC'),
        ]);

        $serialized = json_decode($event->toJson(), true);

        $this->assertSame('2026-06-20T23:59:59Z', $serialized['default_assignment_deadline']);
    }

    public function test_default_assignment_deadline_can_be_null(): void
    {
        $event = new LmsEvent;
        $event->forceFill(['default_assignment_deadline' => null]);

        $this->assertNull($event->default_assignment_deadline);
    }
}
