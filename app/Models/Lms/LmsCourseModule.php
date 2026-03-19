<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsCourseModule extends Model
{
    protected $table = 'lms_course_modules';

    protected $fillable = [
        'lms_course_id',
        'title',
        'description',
        'position',
        'available_from',
        'available_to',
        'unlock_type',
    ];

    protected $casts = [
        'available_from' => 'datetime',
        'available_to' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(LmsCourse::class, 'lms_course_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(LmsCourseStage::class, 'lms_course_module_id')->orderBy('position');
    }

    public function isAvailable(): bool
    {
        if ($this->unlock_type === 'manual') return true;
        if ($this->available_from && now()->lt($this->available_from)) return false;
        if ($this->available_to && now()->gt($this->available_to)) return false;
        return true;
    }
}
