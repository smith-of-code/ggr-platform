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
        'stage2_submitted_at',
        'stage3_text',
        'stage3_video_url',
        'stage3_attachment_path',
        'stage3_attachment_original_name',
    ];

    protected function casts(): array
    {
        return [
            'selected_city_ids' => 'array',
            'stage2_submitted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
