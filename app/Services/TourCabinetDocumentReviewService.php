<?php

namespace App\Services;

use App\Jobs\SendMailJob;
use App\Mail\TourCabinetDocumentAnnulledMail;
use App\Models\TourCabinetDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class TourCabinetDocumentReviewService
{
    public function approve(TourCabinetDocument $document): void
    {
        if (! $document->hasFile()) {
            throw new InvalidArgumentException('Нет файла для подтверждения.');
        }

        if ($document->status === TourCabinetDocument::STATUS_APPROVED) {
            return;
        }

        $document->update([
            'status' => TourCabinetDocument::STATUS_APPROVED,
            'reviewed_at' => now(),
            'admin_comment' => null,
        ]);
    }

    public function annul(TourCabinetDocument $document, string $comment): void
    {
        $comment = trim($comment);
        if ($comment === '') {
            throw new InvalidArgumentException('Укажите комментарий для участника (что исправить в документе).');
        }

        if (! $document->hasFile()) {
            throw new InvalidArgumentException('Нет файла для отклонения.');
        }

        $document->loadMissing('user');
        $recipient = $document->user;

        $disk = config('filesystems.upload_disk', 'public');

        DB::transaction(function () use ($document, $comment, $disk) {
            try {
                Storage::disk($disk)->delete($document->file_path);
            } catch (\Throwable $e) {
                Log::warning('tour_cabinet_document_annul_delete_failed', [
                    'document_id' => $document->id,
                    'path' => $document->file_path,
                    'message' => $e->getMessage(),
                ]);
            }

            $document->update([
                'file_path' => '',
                'original_name' => '',
                'status' => TourCabinetDocument::STATUS_ANNULLED,
                'admin_comment' => $comment,
                'reviewed_at' => now(),
            ]);
        });

        if ($recipient && $recipient->email) {
            SendMailJob::dispatch(
                $recipient->email,
                new TourCabinetDocumentAnnulledMail($recipient, $document->fresh(), $comment)
            );
        }
    }
}
