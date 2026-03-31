<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'requires_approval',
        'is_mandatory',
        'unlocks_gamification',
        'position',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'sequential' => 'boolean',
        'is_active' => 'boolean',
        'requires_approval' => 'boolean',
        'is_mandatory' => 'boolean',
        'unlocks_gamification' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

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

    public function modules(): HasMany
    {
        return $this->hasMany(LmsCourseModule::class, 'lms_course_id')->orderBy('position');
    }

    public function roleAccess(): BelongsToMany
    {
        return $this->belongsToMany(LmsRole::class, 'lms_course_role_access', 'lms_course_id', 'lms_role_id');
    }
}
