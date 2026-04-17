<?php

namespace App\Services;

use App\Models\TourCabinetSupportAttachment;
use App\Models\TourCabinetSupportMessage;
use App\Models\TourCabinetSupportTicket;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TourCabinetSupportMessageService
{
    public const MAX_ATTACHMENTS = 5;

    public const MAX_FILE_KB = 5120;

    /** @return list<string> */
    public static function allowedMimeTypes(): array
    {
        return [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
    }

    /**
     * @param  list<UploadedFile>  $files
     */
    public function attachFiles(TourCabinetSupportMessage $message, array $files): void
    {
        $disk = config('filesystems.upload_disk', 'public');
        $dir = 'tour-cabinet/support/'.$message->ticket_id.'/'.$message->id;

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $stored = $file->store($dir, $disk);
            TourCabinetSupportAttachment::query()->create([
                'message_id' => $message->id,
                'disk' => $disk,
                'path' => $stored,
                'original_filename' => Str::limit($file->getClientOriginalName(), 200, ''),
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'size_bytes' => $file->getSize(),
            ]);
        }
    }

    /**
     * @param  list<UploadedFile>  $files
     */
    public function createUserMessage(TourCabinetSupportTicket $ticket, User $author, string $body, array $files): TourCabinetSupportMessage
    {
        return DB::transaction(function () use ($ticket, $author, $body, $files) {
            $message = TourCabinetSupportMessage::query()->create([
                'ticket_id' => $ticket->id,
                'author_type' => TourCabinetSupportMessage::AUTHOR_USER,
                'author_user_id' => $author->id,
                'body' => $body,
            ]);

            $this->attachFiles($message, $files);

            $ticket->update([
                'last_message_at' => now(),
                'status' => TourCabinetSupportTicket::STATUS_OPEN,
            ]);

            return $message->fresh(['attachments']);
        });
    }

    /**
     * @param  list<UploadedFile>  $files
     */
    public function createAdminMessage(TourCabinetSupportTicket $ticket, User $admin, string $body, array $files): TourCabinetSupportMessage
    {
        return DB::transaction(function () use ($ticket, $admin, $body, $files) {
            $message = TourCabinetSupportMessage::query()->create([
                'ticket_id' => $ticket->id,
                'author_type' => TourCabinetSupportMessage::AUTHOR_ADMIN,
                'author_user_id' => $admin->id,
                'body' => $body,
            ]);

            $this->attachFiles($message, $files);

            $ticket->update([
                'last_message_at' => now(),
                'status' => TourCabinetSupportTicket::STATUS_PENDING_USER,
            ]);

            return $message->fresh(['attachments']);
        });
    }

    public function deleteMessageAttachments(TourCabinetSupportMessage $message): void
    {
        foreach ($message->attachments as $attachment) {
            $attachment->deleteStoredFile();
            $attachment->delete();
        }
    }

    /**
     * @param  list<UploadedFile>  $files
     */
    public function createTicketWithFirstMessage(
        User $owner,
        string $subject,
        string $category,
        string $body,
        array $files,
    ): TourCabinetSupportTicket {
        return DB::transaction(function () use ($owner, $subject, $category, $body, $files) {
            $ticket = TourCabinetSupportTicket::query()->create([
                'user_id' => $owner->id,
                'subject' => $subject,
                'category' => $category,
                'status' => TourCabinetSupportTicket::STATUS_OPEN,
                'last_message_at' => now(),
            ]);

            $message = TourCabinetSupportMessage::query()->create([
                'ticket_id' => $ticket->id,
                'author_type' => TourCabinetSupportMessage::AUTHOR_USER,
                'author_user_id' => $owner->id,
                'body' => $body,
            ]);

            $this->attachFiles($message, $files);

            return $ticket->fresh(['messages.attachments']);
        });
    }
}
