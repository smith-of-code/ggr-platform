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

    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class, 'city_tour');
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favorable');
    }

    public function researches(): HasMany
    {
        return $this->hasMany(Research::class);
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
