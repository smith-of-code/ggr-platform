<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourCabinetContestArchive extends Model
{
    public const STATUS_SENT = 'sent';

    protected $table = 'tour_cabinet_contest_archives';

    protected $fillable = [
        'user_id',
        'direction_id',
        'submitted_at',
        'status',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }
}
