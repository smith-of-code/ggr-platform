<?php

namespace App\Services\Lms;

use App\Jobs\SendMailJob;
use App\Mail\LmsProfileDocumentAnnulledMail;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfileDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class LmsProfileDocumentReviewService
{
    public function approve(LmsProfileDocument $document): void
    {
        if (! $document->hasFile()) {
            throw new InvalidArgumentException('Нет файла для подтверждения.');
        }

        if ($document->status === LmsProfileDocument::STATUS_APPROVED) {
            return;
        }

        $document->update([
            'status' => LmsProfileDocument::STATUS_APPROVED,
            'reviewed_at' => now(),
            'admin_comment' => null,
        ]);
    }

    public function annul(LmsProfileDocument $document, string $comment, LmsEvent $event): void
    {
        $comment = trim($comment);
        if ($comment === '') {
            throw new InvalidArgumentException('Комментарий обязателен.');
        }

        if (! $document->hasFile()) {
            throw new InvalidArgumentException('Нет файла для аннулирования.');
        }

        $disk = config('filesystems.upload_disk');
        $document->loadMissing('profile.user');
        $recipient = $document->profile?->user;

        DB::transaction(function () use ($document, $comment, $disk) {
            try {
                Storage::disk($disk)->delete($document->file_path);
            } catch (\Throwable $e) {
                Log::warning('lms_profile_document_annul_delete_failed', [
                    'document_id' => $document->id,
                    'path' => $document->file_path,
                    'message' => $e->getMessage(),
                ]);
            }

            $document->update([
                'file_path' => '',
                'original_name' => '',
                'status' => LmsProfileDocument::STATUS_ANNULLED,
                'admin_comment' => $comment,
                'reviewed_at' => now(),
            ]);
        });

        if ($recipient && $recipient->email) {
            SendMailJob::dispatch(
                $recipient->email,
                new LmsProfileDocumentAnnulledMail($recipient, $event, $document->fresh(), $comment)
            );
        }
    }
}
