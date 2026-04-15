<?php

namespace Tests\Feature\Lms;

use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsCourseModule;
use App\Models\Lms\LmsCourseStage;
use App\Models\Lms\LmsEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseCurriculumOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_stages_in_curriculum_order_respects_modules_not_global_position(): void
    {
        $event = LmsEvent::create([
            'title' => 'E',
            'slug' => 'e-'.uniqid(),
            'is_active' => true,
        ]);
        $course = LmsCourse::create([
            'lms_event_id' => $event->id,
            'title' => 'C',
            'slug' => 'c-'.uniqid(),
            'sequential' => false,
            'is_active' => true,
            'position' => 1,
        ]);
        $m1 = LmsCourseModule::create([
            'lms_course_id' => $course->id,
            'title' => 'M1',
            'position' => 1,
            'unlock_type' => 'manual',
        ]);
        $m2 = LmsCourseModule::create([
            'lms_course_id' => $course->id,
            'title' => 'M2',
            'position' => 2,
            'unlock_type' => 'manual',
        ]);
        $s1 = LmsCourseStage::create([
            'lms_course_id' => $course->id,
            'lms_course_module_id' => $m1->id,
            'title' => 'M1 first',
            'type' => 'content',
            'position' => 1,
        ]);
        $s2 = LmsCourseStage::create([
            'lms_course_id' => $course->id,
            'lms_course_module_id' => $m2->id,
            'title' => 'M2 first',
            'type' => 'content',
            'position' => 1,
        ]);
        $course->refresh();
        $ordered = $course->stagesInCurriculumOrder()->pluck('id')->all();

        $this->assertSame([$s1->id, $s2->id], $ordered);
    }
}
