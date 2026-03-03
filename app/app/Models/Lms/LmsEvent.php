<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsEvent extends Model
{
    protected $table = 'lms_events';

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'title',
        'slug',
        'description',
        'auth_method',
        'sso_provider_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** @return HasMany<LmsProfile> */
    public function profiles(): HasMany
    {
        return $this->hasMany(LmsProfile::class, 'lms_event_id');
    }

    /** @return HasMany<LmsGroup> */
    public function groups(): HasMany
    {
        return $this->hasMany(LmsGroup::class, 'lms_event_id');
    }

    /** @return HasMany<LmsCourse> */
    public function courses(): HasMany
    {
        return $this->hasMany(LmsCourse::class, 'lms_event_id');
    }

    /** @return HasMany<LmsKbSection> */
    public function kbSections(): HasMany
    {
        return $this->hasMany(LmsKbSection::class, 'lms_event_id');
    }

    /** @return HasMany<LmsTest> */
    public function tests(): HasMany
    {
        return $this->hasMany(LmsTest::class, 'lms_event_id');
    }

    /** @return HasMany<LmsAssignment> */
    public function assignments(): HasMany
    {
        return $this->hasMany(LmsAssignment::class, 'lms_event_id');
    }

    /** @return HasMany<LmsTrajectory> */
    public function trajectories(): HasMany
    {
        return $this->hasMany(LmsTrajectory::class, 'lms_event_id');
    }

    /** @return HasMany<LmsVideo> */
    public function videos(): HasMany
    {
        return $this->hasMany(LmsVideo::class, 'lms_event_id');
    }

    /** @return HasMany<LmsMaterialSection> */
    public function materialSections(): HasMany
    {
        return $this->hasMany(LmsMaterialSection::class, 'lms_event_id');
    }

    /** @return HasMany<LmsGamificationRule> */
    public function gamificationRules(): HasMany
    {
        return $this->hasMany(LmsGamificationRule::class, 'lms_event_id');
    }
}
