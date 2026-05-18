<?php

namespace App\Jobs\Lms;

use App\Models\Lms\LmsEvent;
use App\Services\Lms\LmsParticipantDocumentsArchiveService;
use App\Services\Lms\LmsParticipantDocumentsExportStatusStore;
use App\Services\Lms\LmsProfileListFilterService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class BuildLmsParticipantDocumentsArchiveJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public int $tries = 1;

    public int $maxExceptions = 1;

    public bool $failOnTimeout = true;

    public int $uniqueFor = 900;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public int $lmsEventId,
        public int $requestedByUserId,
        public array $filters,
    ) {
        $this->onQueue('lms-exports');
    }

    public function uniqueId(): string
    {
        return $this->lmsEventId.':'.$this->requestedByUserId;
    }

    public function handle(
        LmsParticipantDocumentsArchiveService $archiveService,
        LmsProfileListFilterService $filterService,
        LmsParticipantDocumentsExportStatusStore $statusStore,
    ): void {
        $statusStore->putProcessing($this->lmsEventId, $this->requestedByUserId);

        try {
            $event = LmsEvent::query()->findOrFail($this->lmsEventId);

            $query = $event->profiles()
                ->with(['user:id,name,last_name,first_name,patronymic,email', 'documents']);

            $filterService->apply($query, $this->filters, $event);

            $profiles = $query
                ->whereHas('documents', fn ($q) => $q->whereNotNull('file_path')->where('file_path', '!=', ''))
                ->orderBy('id')
                ->get();

            $result = $archiveService->buildAndStore($profiles, $event);

            $statusStore->putCompleted(
                $this->lmsEventId,
                $this->requestedByUserId,
                $result['url'],
                $result['files_count'],
                $result['participants_count'],
            );
        } catch (RuntimeException $e) {
            $statusStore->putFailed($this->lmsEventId, $this->requestedByUserId, $e->getMessage());
        } catch (Throwable $e) {
            Log::error('LMS participant documents archive job failed', [
                'lms_event_id' => $this->lmsEventId,
                'user_id' => $this->requestedByUserId,
                'message' => $e->getMessage(),
                'exception' => $e::class,
            ]);

            $statusStore->putFailed(
                $this->lmsEventId,
                $this->requestedByUserId,
                'Не удалось собрать архив документов. Обратитесь к администратору.',
            );
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('LMS participant documents archive job failed (failed hook)', [
            'lms_event_id' => $this->lmsEventId,
            'user_id' => $this->requestedByUserId,
            'message' => $exception?->getMessage(),
            'exception' => $exception ? $exception::class : null,
        ]);

        app(LmsParticipantDocumentsExportStatusStore::class)
            ->putFailed(
                $this->lmsEventId,
                $this->requestedByUserId,
                $exception?->getMessage() ?: 'Не удалось собрать архив документов.',
            );
    }
}
