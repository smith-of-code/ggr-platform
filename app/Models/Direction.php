<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Direction extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'image',
        'hero_bg_color_from',
        'hero_bg_color_via',
        'hero_bg_color_to',
        'hero_text_color',
        'hero_bg_image',
        'hero_bg_color_enabled',
        'sub_directions_title',
        'sub_directions_description',
        'sub_directions',
        'target_audiences',
        'target_audience_note',
        'free_participation_steps',
        'free_participation_details',
        'paid_participation_steps',
        'paid_form_slug',
        'featured_tour_ids',
        'is_active',
        'position',
    ];

    protected $casts = [
        'sub_directions' => 'array',
        'target_audiences' => 'array',
        'free_participation_steps' => 'array',
        'free_participation_details' => 'array',
        'paid_participation_steps' => 'array',
        'featured_tour_ids' => 'array',
        'is_active' => 'boolean',
        'hero_bg_color_enabled' => 'boolean',
    ];

    public function tours(): HasMany
    {
        return $this->hasMany(Tour::class);
    }

    public function featuredTours()
    {
        $ids = $this->featured_tour_ids ?? [];

        if (empty($ids)) {
            return Tour::query()->whereRaw('1 = 0');
        }

        return Tour::whereIn('id', $ids)->where('is_active', true)->orderBy('position');
    }

    /**
     * [id => title] только активных направлений (для фильтров каталога, ЛК).
     *
     * @return array<int, string>
     */
    public static function activeProjectMap(): array
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
    }

    /**
     * [id => title] всех направлений (для валидации, админки).
     *
     * @return array<int, string>
     */
    public static function allProjectMap(): array
    {
        return static::query()
            ->orderBy('position')
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
    }

    /**
     * [{key: id, label: title}] активных направлений — для Inertia props.
     *
     * @return list<array{key: int, label: string}>
     */
    public static function projectList(): array
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (self $d) => ['key' => $d->id, 'label' => $d->title])
            ->values()
            ->all();
    }
}
