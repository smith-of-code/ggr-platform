<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourReaction extends Model
{
    public const EMOJIS = [
        'love' => '❤️',
        'wow' => '😮',
        'fire' => '🔥',
        'cool' => '😎',
        'star' => '⭐',
    ];

    protected $fillable = [
        'tour_id',
        'user_id',
        'ip_address',
        'emoji',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
