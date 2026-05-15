<?php

namespace App\Services;

use App\Models\TourCabinetContestArchive;
use App\Models\TourCabinetContestProgress;
use App\Models\User;
use App\Services\Admin\TourCabinetClientContestDataService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Сервис архивации конкурсной заявки: после завершения этапа 3 собирает иммутабельный
 * snapshot всех данных конкурса (направление, выбор городов, ответы этапов 1–3) и
 * сохраняет в `tour_cabinet_contest_archives`. Прогресс помечается `archived_at = now()` —
 * с этого момента блок «Конкурс» в ЛК заблокирован (см. фичу tour-cabinet-archives).
 */
class TourCabinetContestArchiveService
{
    public function __construct(
        private readonly TourCabinetClientContestDataService $contestData,
    ) {}

    /**
     * Идемпотентно: если `archived_at` уже выставлен — возвращает существующий архив
     * (последний по `submitted_at`), не создавая дубль. Все ошибки логируются и не валят
     * вызывающий процесс (сохранение этапа 3 первично).
     */
    public function archiveProgress(TourCabinetContestProgress $progress, User $user): ?TourCabinetContestArchive
    {
        if ($progress->archived_at !== null) {
            return TourCabinetContestArchive::query()
                ->where('user_id', $progress->user_id)
                ->orderByDesc('submitted_at')
                ->orderByDesc('id')
                ->first();
        }

        try {
            $payload = $this->buildSnapshotPayload($progress, $user);

            return DB::transaction(function () use ($progress, $payload): TourCabinetContestArchive {
                $now = now();

                $archive = TourCabinetContestArchive::query()->create([
                    'user_id' => $progress->user_id,
                    'direction_id' => $progress->direction_id,
                    'submitted_at' => $now,
                    'status' => TourCabinetContestArchive::STATUS_SENT,
                    'payload' => $payload,
                ]);

                $progress->forceFill(['archived_at' => $now])->save();

                return $archive;
            });
        } catch (\Throwable $e) {
            Log::warning('tour_cabinet_contest_archive_failed: '.$e->getMessage(), [
                'user_id' => $progress->user_id,
                'progress_id' => $progress->id,
            ]);

            return null;
        }
    }

    /**
     * Снапшот формируется поверх existing `TourCabinetClientContestDataService` —
     * единого источника UI-payload'а конкурса в админ-карточке клиента; добавляем
     * метаданные, специфичные для архивной записи.
     *
     * @return array<string, mixed>
     */
    private function buildSnapshotPayload(TourCabinetContestProgress $progress, User $user): array
    {
        $base = $this->contestData->contestPayloadForUser($user);

        return [
            'progress' => $base['progress'] ?? null,
            'stage1_city_forms' => $base['stage1_city_forms'] ?? [],
            'stage2_qa' => $base['stage2_qa'] ?? [],
            'stage3' => $base['stage3'] ?? null,
            'meta' => [
                'progress_id' => $progress->id,
                'completion_notified_at' => $progress->completion_notified_at?->toIso8601String(),
                'stage3_attachment_path' => $progress->stage3_attachment_path,
                'stage3_attachment_original_name' => $progress->stage3_attachment_original_name,
            ],
        ];
    }
}
