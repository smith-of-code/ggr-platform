<?php

namespace App\Services;

use App\Models\TourCabinetDirectionCity;

/**
 * Определяет slug формы Этапа 1 конкурса для конкретной строки `tour_cabinet_direction_cities`.
 *
 * Иерархия (сверху вниз):
 *   1. `row->lms_form_slug` (если непустой) — индивидуальная привязка формы к городу.
 *   2. Глобальный slug из `settings` (группа `tour_cabinet`):
 *        - `contest_stage1_form_slug_more_data`, если `row->needs_more_data === true`,
 *        - иначе `contest_stage1_form_slug_standard`.
 *   3. `null` — у города нет формы (UI ЛК показывает «Заполнено» автоматически).
 */
final class TourCabinetContestStage1FormResolver
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    /**
     * Резолв slug формы для одной строки `tour_cabinet_direction_cities`.
     */
    public function resolveForRow(TourCabinetDirectionCity $row): ?string
    {
        $perRow = is_string($row->lms_form_slug) ? trim($row->lms_form_slug) : '';
        if ($perRow !== '') {
            return $perRow;
        }

        $global = $row->needs_more_data
            ? $this->settings->getTourCabinetContestStage1FormSlugMoreData()
            : $this->settings->getTourCabinetContestStage1FormSlugStandard();
        $global = is_string($global) ? trim($global) : '';

        return $global !== '' ? $global : null;
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
            ->get(['id', 'direction_id', 'city_id', 'needs_more_data', 'lms_form_slug']);

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
