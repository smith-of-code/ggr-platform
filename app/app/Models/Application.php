<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $fillable = [
        'type',
        'name',
        'email',
        'phone',
        'data',
        'tour_id',
        'tour_departure_id',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function tourDeparture(): BelongsTo
    {
        return $this->belongsTo(TourDeparture::class);
    }

    public const TYPES = [
        'tour' => 'Заявка на тур',
        'research' => 'Заявка на исследование',
        'program_info' => 'Узнать подробнее о программе',
    ];
}
