<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class TourCabinetContestDirectionSetting extends Model
{
    protected $table = 'tour_cabinet_contest_direction_settings';

    protected $fillable = [
        'direction_id',
        'max_contest_stages',
    ];

    /**
     * Сколько этапов конкурса (1–3) доступно участнику для направления. По умолчанию 3, если записи нет.
     */
    public static function maxContestStagesForDirection(?int $directionId): int
    {
        if ($directionId === null || $directionId === 0 || ! Schema::hasTable((new self)->getTable())) {
            return 3;
        }

        $row = self::query()->where('direction_id', $directionId)->first();
        if ($row === null) {
            return 3;
        }

        return min(3, max(1, (int) $row->max_contest_stages));
    }

    public function direction(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }
}
