<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourCabinetContestProgress extends Model
{
    protected $table = 'tour_cabinet_contest_progress';

    protected $fillable = [
        'user_id',
        'project_key',
        'selected_city_ids',
        'current_stage',
        'stage3_text',
        'stage3_video_url',
    ];

    protected function casts(): array
    {
        return [
            'selected_city_ids' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
