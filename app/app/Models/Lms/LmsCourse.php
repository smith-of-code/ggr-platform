<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsCourse extends Model
{
    protected $table = 'lms_courses';

    protected $fillable = [
        'lms_event_id',
        'title',
        'slug',
        'description',
        'image',
        'sequential',
        'is_active',
        'position',
    ];

    protected $casts = [
        'sequential' => 'boolean',
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<LmsEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(LmsEvent::class, 'lms_event_id');
    }

    /** @return HasMany<LmsCourseStage> */
    public function stages(): HasMany
    {
        return $this->hasMany(LmsCourseStage::class, 'lms_course_id');
    }

    /** @return HasMany<LmsCourseEnrollment> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(LmsCourseEnrollment::class, 'lms_course_id');
    }
}
