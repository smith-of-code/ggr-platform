<?php

namespace App\Services;

use App\Models\TourCabinetDirectionCity;

/**
 * Определяет slug формы Этапа 1 конкурса для конкретной строки `tour_cabinet_direction_cities`.
 *
 * Источник единственный — колонка `lms_form_slug`:
 *   - непустая строка → индивидуальная привязка формы к городу;
 *   - `null` / пустая строка → у города нет формы (UI ЛК показывает «Заполнено» автоматически).
 *
 * Глобальный fallback на settings/config удалён 2026-04-29 (фича `contest-city-forms`,
 * update «drop-fallback»: пустой `lms_form_slug` всегда означает автозавершение).
 */
final class TourCabinetContestStage1FormResolver
{
    /**
     * Резолв slug формы для одной строки `tour_cabinet_direction_cities`.
     */
    public function resolveForRow(TourCabinetDirectionCity $row): ?string
    {
        $perRow = is_string($row->lms_form_slug) ? trim($row->lms_form_slug) : '';

        return $perRow !== '' ? $perRow : null;
    }

    /**
     * Резолв пачки городов одного направления одним SQL-запросом.
     *
     * @param  list<int>  $cityIds
     * @return array<int, ?string>  city_id => resolved slug (`null`, если формы нет)
     */
    public function resolveBatchForDirection(int $directionId, array $cityIds): array
    {
        $cityIds = array_values(array_unique(array_map('intval', $cityIds)));
        if ($cityIds === []) {
            return [];
        }

        $rows = TourCabinetDirectionCity::query()
            ->where('direction_id', $directionId)
            ->whereIn('city_id', $cityIds)
            ->get(['id', 'direction_id', 'city_id', 'lms_form_slug']);

        $result = [];
        foreach ($cityIds as $id) {
            $result[$id] = null;
        }
        foreach ($rows as $row) {
            $result[(int) $row->city_id] = $this->resolveForRow($row);
        }

        return $result;
    }
}
