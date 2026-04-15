<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourCabinetDirectionCity extends Model
{
    protected $table = 'tour_cabinet_direction_cities';

    protected $fillable = [
        'project_key',
        'city_id',
        'needs_more_data',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'needs_more_data' => 'boolean',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
