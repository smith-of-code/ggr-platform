<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class TourCabinetContestStage3Config extends Model
{
    public const FORMAT_VIDEO_LINK = 'video_link';

    public const FORMAT_FILE_UPLOAD = 'file_upload';

    protected $table = 'tour_cabinet_contest_stage3_configs';

    protected $fillable = [
        'direction_id',
        'title',
        'task_body',
        'response_format',
    ];

    public static function forDirection(?int $directionId): ?self
    {
        if ($directionId === null || $directionId === 0) {
            return null;
        }

        if (! Schema::hasTable((new self)->getTable())) {
            return null;
        }

        return self::query()->where('direction_id', $directionId)->first();
    }

    public function direction(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    public function usesFileUpload(): bool
    {
        return $this->response_format === self::FORMAT_FILE_UPLOAD;
    }

    public function usesVideoLink(): bool
    {
        return $this->response_format === self::FORMAT_VIDEO_LINK;
    }
}
