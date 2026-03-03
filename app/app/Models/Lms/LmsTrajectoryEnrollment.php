<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsTrajectoryEnrollment extends Model
{
    protected $table = 'lms_trajectory_enrollments';

    protected $fillable = [
        'lms_trajectory_id',
        'user_id',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /** @return BelongsTo<LmsTrajectory, $this> */
    public function trajectory(): BelongsTo
    {
        return $this->belongsTo(LmsTrajectory::class, 'lms_trajectory_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
