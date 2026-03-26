<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsTrajectory extends Model
{
    protected $table = 'lms_trajectories';

    protected $fillable = [
        'lms_event_id',
        'title',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<LmsEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(LmsEvent::class, 'lms_event_id');
    }

    /** @return HasMany<LmsTrajectoryStep> */
    public function steps(): HasMany
    {
        return $this->hasMany(LmsTrajectoryStep::class, 'lms_trajectory_id');
    }

    /** @return HasMany<LmsTrajectoryEnrollment> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(LmsTrajectoryEnrollment::class, 'lms_trajectory_id');
    }

    /** @return HasMany<LmsTrajectoryBlock> */
    public function blocks(): HasMany
    {
        return $this->hasMany(LmsTrajectoryBlock::class, 'lms_trajectory_id')->orderBy('position');
    }
}
