<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsTrajectoryStep extends Model
{
    protected $table = 'lms_trajectory_steps';

    protected $fillable = [
        'lms_trajectory_id',
        'lms_course_id',
        'is_locked',
        'opens_at',
        'position',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'opens_at' => 'datetime',
    ];

    /** @return BelongsTo<LmsTrajectory, $this> */
    public function trajectory(): BelongsTo
    {
        return $this->belongsTo(LmsTrajectory::class, 'lms_trajectory_id');
    }

    /** @return BelongsTo<LmsCourse, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LmsCourse::class, 'lms_course_id');
    }
}
