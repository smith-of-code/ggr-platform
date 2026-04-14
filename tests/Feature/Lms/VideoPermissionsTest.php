<?php

namespace Tests\Feature\Lms;

use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class VideoPermissionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{
     *     event: LmsEvent,
     *     admin: User,
     *     participant: User,
     *     courseA: LmsCourse,
     *     courseB: LmsCourse,
     *     vAll: LmsVideo,
     *     vA: LmsVideo,
     *     vB: LmsVideo
     * }
     */
    private function fixtures(): array
    {
        $event = LmsEvent::create([
            'title' => 'Video Perm Event',
            'slug' => 'vp-'.uniqid(),
            'is_active' => true,
        ]);

        $admin = User::factory()->create();
        $participant = User::factory()->create();

        LmsProfile::create([
            'user_id' => $admin->id,
            'lms_event_id' => $event->id,
            'role' => 'admin',
        ]);

        LmsProfile::create([
            'user_id' => $participant->id,
            'lms_event_id' => $event->id,
            'role' => 'participant',
        ]);

        $suffix = uniqid();
        $courseA = LmsCourse::create([
            'lms_event_id' => $event->id,
            'title' => 'Course A',
            'slug' => 'course-a-'.$suffix,
            'sequential' => false,
            'is_active' => true,
            'position' => 1,
        ]);
        $courseB = LmsCourse::create([
            'lms_event_id' => $event->id,
            'title' => 'Course B',
            'slug' => 'course-b-'.$suffix,
            'sequential' => false,
            'is_active' => true,
            'position' => 2,
        ]);

        LmsCourseEnrollment::create([
            'lms_course_id' => $courseA->id,
            'user_id' => $participant->id,
            'status' => 'enrolled',
        ]);

        $vAll = LmsVideo::create([
            'lms_event_id' => $event->id,
            'title' => 'Video All',
            'source' => 'link',
            'url' => 'https://www.youtube.com/watch?v=all1',
            'is_active' => true,
            'visible_to_all' => true,
        ]);

        $vA = LmsVideo::create([
            'lms_event_id' => $event->id,
            'title' => 'Video A',
            'source' => 'link',
            'url' => 'https://www.youtube.com/watch?v=a1',
            'is_active' => true,
            'visible_to_all' => false,
        ]);
        $vA->courses()->attach($courseA->id);

        $vB = LmsVideo::create([
            'lms_event_id' => $event->id,
            'title' => 'Video B',
            'source' => 'link',
            'url' => 'https://www.youtube.com/watch?v=b1',
            'is_active' => true,
            'visible_to_all' => false,
        ]);
        $vB->courses()->attach($courseB->id);

        return compact('event', 'admin', 'participant', 'courseA', 'courseB', 'vAll', 'vA', 'vB');
    }

    public function test_admin_store_requires_course_when_not_visible_to_all(): void
    {
        extract($this->fixtures());

        $this->actingAs($admin)
            ->post(route('lms.admin.videos.store', $event->slug), [
                'title' => 'No courses',
                'url' => 'https://www.youtube.com/watch?v=x',
                'visible_to_all' => false,
                'course_ids' => [],
                'is_active' => true,
            ])
            ->assertSessionHasErrors('course_ids');
    }

    public function test_admin_store_visible_to_all_creates_without_course_links(): void
    {
        extract($this->fixtures());

        $this->actingAs($admin)
            ->post(route('lms.admin.videos.store', $event->slug), [
                'title' => 'Everyone',
                'url' => 'https://www.youtube.com/watch?v=y',
                'visible_to_all' => true,
                'course_ids' => [],
                'is_active' => true,
            ])
            ->assertRedirect();

        $video = LmsVideo::where('title', 'Everyone')->first();
        $this->assertNotNull($video);
        $this->assertTrue($video->visible_to_all);
        $this->assertSame(0, $video->courses()->count());
    }

    public function test_participant_default_index_excludes_other_course_only_videos(): void
    {
        extract($this->fixtures());

        $this->actingAs($participant)
            ->get(route('lms.videos.index', $event->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Lms/Videos/Index')
                ->has('videos.data', 2));
    }

    public function test_participant_index_filtered_by_course_b_shows_b_without_enrollment(): void
    {
        extract($this->fixtures());

        $url = route('lms.videos.index', $event->slug).'?lms_course_id='.$courseB->id;

        $this->actingAs($participant)
            ->get($url)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Lms/Videos/Index')
                ->has('videos.data', 1)
                ->where('videos.data.0.id', $vB->id));
    }

    public function test_participant_can_show_video_of_course_without_enrollment(): void
    {
        extract($this->fixtures());

        $this->actingAs($participant)
            ->get(route('lms.videos.show', [$event->slug, $vB->id]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Lms/Videos/Show')
                ->where('video.id', $vB->id));
    }

    public function test_participant_cannot_show_inactive_video(): void
    {
        extract($this->fixtures());
        $vB->update(['is_active' => false]);

        $this->actingAs($participant)
            ->get(route('lms.videos.show', [$event->slug, $vB->id]))
            ->assertNotFound();
    }
}
