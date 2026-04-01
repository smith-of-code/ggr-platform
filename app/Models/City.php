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
        'energy_cities_block',
        'block_visibility',
    ];

    protected $casts = [
        'infrastructure' => 'array',
        'is_active' => 'boolean',
        'attractions' => 'array',
        'social_objects' => 'array',
        'gallery' => 'array',
        'facts' => 'array',
        'energy_cities_block' => 'array',
        'block_visibility' => 'array',
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

    public function getEnergyCitiesBlockAttribute($value): array
    {
        $block = is_string($value) ? json_decode($value, true) : $value;

        return [
            'video_url' => $block['video_url'] ?? null,
            'video_title' => $block['video_title'] ?? null,
            'video_subtitle' => $block['video_subtitle'] ?? null,
            'description' => $block['description'] ?? null,
            'button_text' => $block['button_text'] ?? null,
            'button_url' => $block['button_url'] ?? null,
        ];
    }

    public function getBlockVisibilityAttribute($value): array
    {
        $data = is_string($value) ? json_decode($value, true) : $value;

        $defaults = [
            'facts' => true,
            'infrastructure' => true,
            'video' => true,
            'attractions' => true,
            'social_objects' => true,
            'energy_cities_block' => true,
        ];

        if (! is_array($data)) {
            return $defaults;
        }

        return array_merge($defaults, array_intersect_key($data, $defaults));
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
