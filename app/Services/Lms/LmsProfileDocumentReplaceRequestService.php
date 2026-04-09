<?php

namespace App\Services\Lms;

use App\Models\Lms\LmsProfileDocument;
use App\Models\Lms\LmsProfileDocumentReplaceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class LmsProfileDocumentReplaceRequestService
{
    public function approve(LmsProfileDocumentReplaceRequest $request): void
    {
        if (! $request->isPending()) {
            throw new InvalidArgumentException('Заявка уже обработана.');
        }

        $document = LmsProfileDocument::where('lms_profile_id', $request->lms_profile_id)
            ->where('type', $request->type)
            ->first();

        if (! $document || ! $document->isLockedForParticipant()) {
            throw new InvalidArgumentException('Документ не найден или не подтверждён.');
        }

        $disk = config('filesystems.upload_disk');

        DB::transaction(function () use ($request, $document, $disk) {
            try {
                if ($document->hasFile()) {
                    Storage::disk($disk)->delete($document->file_path);
                }
            } catch (\Throwable $e) {
                Log::warning('lms_replace_request_approve_delete_failed', [
                    'document_id' => $document->id,
                    'path' => $document->file_path,
                    'message' => $e->getMessage(),
                ]);
            }

            $document->update([
                'file_path' => '',
                'original_name' => '',
                'status' => LmsProfileDocument::STATUS_PENDING_REVIEW,
                'admin_comment' => null,
                'reviewed_at' => null,
            ]);

            $request->update([
                'status' => LmsProfileDocumentReplaceRequest::STATUS_APPROVED,
                'admin_comment' => null,
                'reviewed_at' => now(),
            ]);
        });
    }

    public function reject(LmsProfileDocumentReplaceRequest $request, ?string $adminComment): void
    {
        if (! $request->isPending()) {
            throw new InvalidArgumentException('Заявка уже обработана.');
        }

        $request->update([
            'status' => LmsProfileDocumentReplaceRequest::STATUS_REJECTED,
            'admin_comment' => $adminComment ? trim($adminComment) : null,
            'reviewed_at' => now(),
        ]);
    }
}
