<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'image',
        'project_key',
        'sub_directions_title',
        'sub_directions_description',
        'sub_directions',
        'target_audiences',
        'target_audience_note',
        'free_participation_steps',
        'free_participation_details',
        'paid_participation_steps',
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
    ];

    public function featuredTours()
    {
        $ids = $this->featured_tour_ids ?? [];

        if (empty($ids)) {
            return Tour::query()->whereRaw('1 = 0');
        }

        return Tour::whereIn('id', $ids)->where('is_active', true)->orderBy('position');
    }
}
