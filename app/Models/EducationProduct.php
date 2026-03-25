<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationProduct extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'image',
        'duration',
        'format',
        'target_audience',
        'price_info',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
