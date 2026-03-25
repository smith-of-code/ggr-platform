<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recipe extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'image',
        'city_id',
        'cooking_time',
        'difficulty',
        'servings',
        'ingredients',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'ingredients' => 'array',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
