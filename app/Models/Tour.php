<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Tour extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'start_city',
        'duration',
        'project',
        'participation_type',
        'season',
        'for_children',
        'for_foreigners',
        'closed_city',
        'group_size',
        'min_age',
        'price_from',
        'program_pdf',
        'memo_pdf',
        'departure_info',
        'accommodation_info',
        'conditions',
        'cost_info',
        'application_info',
        'bchp_participant',
        'is_featured',
        'image',
        'gallery',
        'videos',
        'position',
        'is_active',
        'target_audience',
        'organizer_info',
        'reactions_count',
    ];

    protected $casts = [
        'for_children' => 'boolean',
        'for_foreigners' => 'boolean',
        'closed_city' => 'boolean',
        'bchp_participant' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'price_from' => 'decimal:2',
        'reactions_count' => 'array',
        'gallery' => 'array',
        'videos' => 'array',
    ];

    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class, 'city_tour');
    }

    public function departures(): HasMany
    {
        return $this->hasMany(TourDeparture::class)->orderBy('start_date');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('order');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(TourReaction::class);
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favorable');
    }

    public const PROJECTS = [
        'start_atomgrad' => 'Старт в Атомград',
        'atoms_vkusa' => 'Атомы вкуса',
        'llr' => 'Лучшие люди Росатома',
    ];

    public const SEASONS = [
        'winter' => 'Зима',
        'spring' => 'Весна',
        'summer' => 'Лето',
        'autumn' => 'Осень',
    ];

    public const PARTICIPATION_TYPES = [
        'contest' => 'Конкурс',
        'paid' => 'За свой счёт',
    ];
}
