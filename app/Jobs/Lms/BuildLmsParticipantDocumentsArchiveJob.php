<?php

namespace App\Jobs\Lms;

use App\Models\Lms\LmsEvent;
use App\Services\Lms\LmsParticipantDocumentsArchiveService;
use App\Services\Lms\LmsParticipantDocumentsExportStatusStore;
use App\Services\Lms\LmsProfileListFilterService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;
use Throwable;

class BuildLmsParticipantDocumentsArchiveJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public int $tries = 1;

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

    public function handle(
        LmsParticipantDocumentsArchiveService $archiveService,
        LmsProfileListFilterService $filterService,
        LmsParticipantDocumentsExportStatusStore $statusStore,
    ): void {
        $statusStore->putProcessing($this->lmsEventId, $this->requestedByUserId);

        $event = LmsEvent::query()->findOrFail($this->lmsEventId);

        $query = $event->profiles()
            ->with(['user:id,name,last_name,first_name,patronymic,email', 'documents']);

        $filterService->apply($query, $this->filters, $event);

        $profiles = $query
            ->whereHas('documents', fn ($q) => $q->whereNotNull('file_path')->where('file_path', '!=', ''))
            ->orderBy('id')
            ->get();

        try {
            $result = $archiveService->buildAndStore($profiles, $event);
        } catch (RuntimeException $e) {
            $statusStore->putFailed($this->lmsEventId, $this->requestedByUserId, $e->getMessage());

            return;
        }

        $statusStore->putCompleted(
            $this->lmsEventId,
            $this->requestedByUserId,
            $result['url'],
            $result['files_count'],
            $result['participants_count'],
        );
    }

    public function failed(?Throwable $exception): void
    {
        $message = $exception?->getMessage() ?: 'Не удалось собрать архив документов.';

        app(LmsParticipantDocumentsExportStatusStore::class)
            ->putFailed($this->lmsEventId, $this->requestedByUserId, $message);
    }
}
