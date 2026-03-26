<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vacancy extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'city_id',
        'company',
        'employment_type',
        'salary',
        'description',
        'requirements',
        'conditions',
        'responsibilities',
        'contact_email',
        'contact_phone',
        'image',
        'is_published',
        'published_at',
        'position',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public const EMPLOYMENT_TYPES = [
        'full_time' => 'Полная занятость',
        'part_time' => 'Частичная занятость',
        'remote' => 'Удалённо',
        'internship' => 'Стажировка',
        'contract' => 'Договор подряда',
    ];
}
