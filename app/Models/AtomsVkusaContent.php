<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtomsVkusaContent extends Model
{
    protected $table = 'atoms_vkusa_content';

    protected $fillable = [
        'hero_title',
        'hero_description',
        'hero_image',
        'competition_stages',
        'participation_conditions',
        'selection_criteria',
        'results_year',
        'results_content',
        'results_gallery',
        'results_videos',
        'results_cases',
        'why_important_content',
        'why_important_stats',
        'map_cities',
        'application_form_title',
        'application_form_text',
        'partners',
        'reviews',
        'tourism_help_content',
        'tourism_help_items',
    ];

    protected $casts = [
        'competition_stages' => 'array',
        'participation_conditions' => 'array',
        'selection_criteria' => 'array',
        'results_gallery' => 'array',
        'results_videos' => 'array',
        'results_cases' => 'array',
        'why_important_stats' => 'array',
        'map_cities' => 'array',
        'partners' => 'array',
        'reviews' => 'array',
        'tourism_help_items' => 'array',
    ];

    public static function content(): self
    {
        return static::firstOrCreate([]);
    }
}
