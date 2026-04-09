<?php

namespace Tests\Feature\Lms;

use App\Mail\LmsProfileDocumentAnnulledMail;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsProfileDocument;
use App\Models\Lms\LmsProfileDocumentReplaceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileDocumentReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: LmsEvent, 1: User, 2: User, 3: LmsProfileDocument}
     */
    private function fixtures(): array
    {
        $event = LmsEvent::create([
            'title' => 'Test Event',
            'slug' => 'ev-'.uniqid(),
            'is_active' => true,
        ]);

        $admin = User::factory()->create();
        $participant = User::factory()->create();

        LmsProfile::create([
            'user_id' => $admin->id,
            'lms_event_id' => $event->id,
            'role' => 'admin',
        ]);

        $participantProfile = LmsProfile::create([
            'user_id' => $participant->id,
            'lms_event_id' => $event->id,
            'role' => 'participant',
        ]);

        $doc = LmsProfileDocument::create([
            'lms_profile_id' => $participantProfile->id,
            'type' => LmsProfileDocument::TYPE_SNILS,
            'file_path' => 'profile-documents/test.pdf',
            'original_name' => 'test.pdf',
            'status' => LmsProfileDocument::STATUS_PENDING_REVIEW,
        ]);

        return [$event, $admin, $participant, $doc];
    }

    public function test_admin_can_approve_document(): void
    {
        [$event, $admin, $participant, $doc] = $this->fixtures();

        $this->actingAs($admin)
            ->post(route('lms.admin.users.documents.approve', [$event->slug, $participant->id, $doc->id]))
            ->assertRedirect();

        $this->assertSame(LmsProfileDocument::STATUS_APPROVED, $doc->fresh()->status);
    }

    public function test_admin_annul_deletes_file_and_sends_mail(): void
    {
        Mail::fake();
        Storage::fake(config('filesystems.upload_disk'));

        [$event, $admin, $participant, $doc] = $this->fixtures();
        $path = $doc->file_path;
        Storage::disk(config('filesystems.upload_disk'))->put($path, 'binary');

        $this->actingAs($admin)
            ->post(route('lms.admin.users.documents.annul', [$event->slug, $participant->id, $doc->id]), [
                'comment' => 'Нужен цветной скан',
            ])
            ->assertRedirect();

        Storage::disk(config('filesystems.upload_disk'))->assertMissing($path);

        $fresh = $doc->fresh();
        $this->assertSame(LmsProfileDocument::STATUS_ANNULLED, $fresh->status);
        $this->assertSame('', $fresh->file_path);
        Mail::assertSent(LmsProfileDocumentAnnulledMail::class);
    }

    public function test_annul_requires_comment(): void
    {
        [$event, $admin, $participant, $doc] = $this->fixtures();

        $this->actingAs($admin)
            ->post(route('lms.admin.users.documents.annul', [$event->slug, $participant->id, $doc->id]), [
                'comment' => '',
            ])
            ->assertSessionHasErrors('comment');
    }

    public function test_participant_cannot_upload_over_approved_document(): void
    {
        Storage::fake(config('filesystems.upload_disk'));
        [$event, , $participant, $doc] = $this->fixtures();

        Storage::disk(config('filesystems.upload_disk'))->put($doc->file_path, 'x');
        $doc->update([
            'status' => LmsProfileDocument::STATUS_APPROVED,
        ]);

        $file = UploadedFile::fake()->create('new.pdf', 100, 'application/pdf');

        $this->actingAs($participant)
            ->post(route('lms.profile.documents.upload', [$event->slug]), [
                'type' => LmsProfileDocument::TYPE_SNILS,
                'file' => $file,
            ])
            ->assertSessionHasErrors('file');
    }

    public function test_participant_can_submit_replace_request_for_approved_document(): void
    {
        [$event, , $participant, $doc] = $this->fixtures();
        $doc->update(['status' => LmsProfileDocument::STATUS_APPROVED]);

        $this->actingAs($participant)
            ->post(route('lms.profile.document-replace-requests.store', [$event->slug]), [
                'type' => LmsProfileDocument::TYPE_SNILS,
                'user_comment' => 'Нужна правка',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lms_profile_document_replace_requests', [
            'lms_profile_id' => $doc->lms_profile_id,
            'type' => LmsProfileDocument::TYPE_SNILS,
            'status' => LmsProfileDocumentReplaceRequest::STATUS_PENDING,
        ]);
    }

    public function test_admin_approve_replace_request_unlocks_upload(): void
    {
        Storage::fake(config('filesystems.upload_disk'));
        [$event, $admin, $participant, $doc] = $this->fixtures();
        Storage::disk(config('filesystems.upload_disk'))->put($doc->file_path, 'x');
        $doc->update(['status' => LmsProfileDocument::STATUS_APPROVED]);

        $req = LmsProfileDocumentReplaceRequest::create([
            'lms_profile_id' => $doc->lms_profile_id,
            'type' => $doc->type,
            'user_comment' => 'опечатка',
            'status' => LmsProfileDocumentReplaceRequest::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->post(route('lms.admin.users.document-replace-requests.approve', [$event->slug, $participant->id, $req->id]))
            ->assertRedirect();

        $this->assertSame(LmsProfileDocumentReplaceRequest::STATUS_APPROVED, $req->fresh()->status);
        $freshDoc = $doc->fresh();
        $this->assertSame(LmsProfileDocument::STATUS_PENDING_REVIEW, $freshDoc->status);
        $this->assertSame('', $freshDoc->file_path);

        $file = UploadedFile::fake()->create('new.pdf', 100, 'application/pdf');
        $this->actingAs($participant)
            ->post(route('lms.profile.documents.upload', [$event->slug]), [
                'type' => LmsProfileDocument::TYPE_SNILS,
                'file' => $file,
            ])
            ->assertRedirect();
    }
}
