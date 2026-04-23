<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class TourCabinetContestDirectionSetting extends Model
{
    protected $table = 'tour_cabinet_contest_direction_settings';

    protected $fillable = [
        'project_key',
        'max_contest_stages',
    ];

    /**
     * Сколько этапов конкурса (1–3) доступно участнику для направления. По умолчанию 3, если записи нет.
     */
    public static function maxContestStagesForProjectKey(?string $projectKey): int
    {
        if ($projectKey === null || $projectKey === '' || ! Schema::hasTable((new self)->getTable())) {
            return 3;
        }

        $row = self::query()->where('project_key', $projectKey)->first();
        if ($row === null) {
            return 3;
        }

        return min(3, max(1, (int) $row->max_contest_stages));
    }
}
