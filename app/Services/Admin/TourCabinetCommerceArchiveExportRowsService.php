<?php

namespace App\Services\Admin;

use App\Models\TourCabinetCommerceArchive;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

/**
 * Обогащает строку CSV-экспорта (`TourCabinetTourUsersController::export`) колонками
 * архивных коммерческих заявок пользователя. Колонки следуют наименованиям из spec'а
 * `admin-tour-users-commerce-archives` и заголовкам в `csvColumnTitleRu`.
 */
final class TourCabinetCommerceArchiveExportRowsService
{
    /**
     * Лимит широких колонок согласован с лимитом `app_N_*` для заявок на туры
     * в `TourCabinetClientContestDataService::buildExportRow`.
     */
    private const MAX_WIDE_COLUMNS = 10;

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function appendRowsForUser(array $row, User $user, ?int $cityFilterId): array
    {
        if (! Schema::hasTable('tour_cabinet_commerce_archives')) {
            $row['commerce_archives_count'] = 0;
            $row['commerce_archives_summary'] = '';

            return $row;
        }

        $archives = $user->tourCabinetCommerceArchives()
            ->with(['city:id,name', 'tour:id,title'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get();

        if ($cityFilterId !== null) {
            $archives = $archives->where('city_id', $cityFilterId)->values();
        }

        $row['commerce_archives_count'] = $archives->count();
        $row['commerce_archives_summary'] = $archives
            ->take(self::MAX_WIDE_COLUMNS)
            ->values()
            ->map(function (TourCabinetCommerceArchive $a, int $idx) {
                $cityName = $this->cityName($a);
                $tourTitle = $this->tourTitle($a);
                $date = $a->submitted_at?->format('d.m.Y') ?? '';

                return '#'.($idx + 1).' '.$cityName.' · '.$tourTitle.($date !== '' ? ' ['.$date.']' : '');
            })
            ->implode('; ');

        foreach ($archives->take(self::MAX_WIDE_COLUMNS)->values() as $i => $archive) {
            $n = $i + 1;
            $row["commerce_arch_{$n}_id"] = (string) $archive->id;
            $row["commerce_arch_{$n}_city"] = $this->cityName($archive);
            $row["commerce_arch_{$n}_tour"] = $this->tourTitle($archive);
            $row["commerce_arch_{$n}_submitted_at"] = $archive->submitted_at?->format('Y-m-d H:i:s') ?? '';
            $row["commerce_arch_{$n}_status"] = (string) ($archive->status ?? '');

            $responses = data_get($archive->payload, 'lms_form.responses', []);
            $row["commerce_arch_{$n}_lms_responses"] = is_array($responses) && $responses !== []
                ? (string) json_encode($responses, JSON_UNESCAPED_UNICODE)
                : '';
        }

        return $row;
    }

    private function cityName(TourCabinetCommerceArchive $a): string
    {
        $name = $a->city?->name ?? data_get($a->payload, 'city.name');

        return is_string($name) && $name !== '' ? $name : '';
    }

    private function tourTitle(TourCabinetCommerceArchive $a): string
    {
        $title = $a->tour?->title ?? data_get($a->payload, 'tour.title');

        return is_string($title) && $title !== '' ? $title : '';
    }
}
