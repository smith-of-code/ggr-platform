<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class City extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'infrastructure',
        'image',
        'coat_of_arms',
        'position',
        'is_active',
        'region',
        'population',
        'founded_year',
        'population_year',
        'timezone',
        'lat',
        'lng',
        'attractions',
        'social_objects',
        'gallery',
        'video_url',
        'facts',
    ];

    protected $casts = [
        'infrastructure' => 'array',
        'is_active' => 'boolean',
        'attractions' => 'array',
        'social_objects' => 'array',
        'gallery' => 'array',
        'facts' => 'array',
    ];

    /**
     * Normalize legacy string[] facts into object[] format.
     * Accessor receives raw DB value (JSON string), not the cast result.
     */
    public function getFactsAttribute($value): array
    {
        $facts = is_string($value) ? json_decode($value, true) : $value;

        if (! is_array($facts)) {
            return [];
        }

        return array_map(function ($fact) {
            if (is_string($fact)) {
                return ['title' => $fact, 'url' => null, 'description' => null];
            }
            if (is_array($fact)) {
                return [
                    'title' => $fact['title'] ?? '',
                    'url' => $fact['url'] ?? null,
                    'description' => $fact['description'] ?? null,
                ];
            }
            return ['title' => '', 'url' => null, 'description' => null];
        }, $facts);
    }

    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class, 'city_tour');
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favorable');
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    public function vacancies(): HasMany
    {
        return $this->hasMany(Vacancy::class);
    }
}
