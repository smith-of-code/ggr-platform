<?php

namespace Tests\Feature\Lms;

use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGroup;
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
     *     groupA: LmsGroup,
     *     groupB: LmsGroup,
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

        $groupA = LmsGroup::create([
            'lms_event_id' => $event->id,
            'title' => 'Program A',
        ]);
        $groupB = LmsGroup::create([
            'lms_event_id' => $event->id,
            'title' => 'Program B',
        ]);

        $groupA->members()->attach($participant->id);

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
        $vA->groups()->attach($groupA->id);

        $vB = LmsVideo::create([
            'lms_event_id' => $event->id,
            'title' => 'Video B',
            'source' => 'link',
            'url' => 'https://www.youtube.com/watch?v=b1',
            'is_active' => true,
            'visible_to_all' => false,
        ]);
        $vB->groups()->attach($groupB->id);

        return compact('event', 'admin', 'participant', 'groupA', 'groupB', 'vAll', 'vA', 'vB');
    }

    public function test_admin_store_requires_program_when_not_visible_to_all(): void
    {
        extract($this->fixtures());

        $this->actingAs($admin)
            ->post(route('lms.admin.videos.store', $event->slug), [
                'title' => 'No programs',
                'url' => 'https://www.youtube.com/watch?v=x',
                'visible_to_all' => false,
                'group_ids' => [],
                'is_active' => true,
            ])
            ->assertSessionHasErrors('group_ids');
    }

    public function test_admin_store_visible_to_all_creates_without_group_links(): void
    {
        extract($this->fixtures());

        $this->actingAs($admin)
            ->post(route('lms.admin.videos.store', $event->slug), [
                'title' => 'Everyone',
                'url' => 'https://www.youtube.com/watch?v=y',
                'visible_to_all' => true,
                'group_ids' => [],
                'is_active' => true,
            ])
            ->assertRedirect();

        $video = LmsVideo::where('title', 'Everyone')->first();
        $this->assertNotNull($video);
        $this->assertTrue($video->visible_to_all);
        $this->assertSame(0, $video->groups()->count());
    }

    public function test_participant_default_index_excludes_other_program_only_videos(): void
    {
        extract($this->fixtures());

        $this->actingAs($participant)
            ->get(route('lms.videos.index', $event->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Lms/Videos/Index')
                ->has('videos.data', 2));
    }

    public function test_participant_index_filtered_by_program_b_shows_b_without_membership(): void
    {
        extract($this->fixtures());

        $url = route('lms.videos.index', $event->slug).'?lms_group_id='.$groupB->id;

        $this->actingAs($participant)
            ->get($url)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Lms/Videos/Index')
                ->has('videos.data', 1)
                ->where('videos.data.0.id', $vB->id));
    }

    public function test_participant_can_show_video_of_program_without_membership(): void
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
