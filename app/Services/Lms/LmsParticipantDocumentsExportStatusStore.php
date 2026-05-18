<?php

namespace App\Services\Lms;

use Illuminate\Support\Facades\Cache;

class LmsParticipantDocumentsExportStatusStore
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    private const TTL_SECONDS = 90000;

    public function get(int $eventId, int $userId): ?array
    {
        $value = Cache::get($this->key($eventId, $userId));

        return is_array($value) ? $value : null;
    }

    public function putQueued(int $eventId, int $userId): void
    {
        $this->put($eventId, $userId, [
            'status' => self::STATUS_QUEUED,
            'message' => 'Архив поставлен в очередь на сборку.',
        ]);
    }

    public function putProcessing(int $eventId, int $userId): void
    {
        $this->put($eventId, $userId, [
            'status' => self::STATUS_PROCESSING,
            'message' => 'Собираем архив документов…',
        ]);
    }

    public function putCompleted(int $eventId, int $userId, string $url, int $filesCount, int $participantsCount): void
    {
        $this->put($eventId, $userId, [
            'status' => self::STATUS_COMPLETED,
            'message' => sprintf(
                'Архив готов: %d файлов от %d участников. Ссылка действует 24 часа.',
                $filesCount,
                $participantsCount,
            ),
            'url' => $url,
            'files_count' => $filesCount,
            'participants_count' => $participantsCount,
            'completed_at' => now()->toIso8601String(),
        ]);
    }

    public function putFailed(int $eventId, int $userId, string $message): void
    {
        $this->put($eventId, $userId, [
            'status' => self::STATUS_FAILED,
            'message' => $message,
        ]);
    }

    public function isInProgress(?array $status): bool
    {
        if ($status === null) {
            return false;
        }

        return in_array($status['status'] ?? null, [self::STATUS_QUEUED, self::STATUS_PROCESSING], true);
    }

    private function put(int $eventId, int $userId, array $payload): void
    {
        Cache::put($this->key($eventId, $userId), $payload, self::TTL_SECONDS);
    }

    private function key(int $eventId, int $userId): string
    {
        return "lms:participant-docs-export:{$eventId}:{$userId}";
    }
}
