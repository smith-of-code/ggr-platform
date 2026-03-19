<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class City extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'infrastructure',
        'image',
        'position',
        'is_active',
    ];

    protected $casts = [
        'infrastructure' => 'array',
        'is_active' => 'boolean',
    ];

    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class, 'city_tour');
    }
}
