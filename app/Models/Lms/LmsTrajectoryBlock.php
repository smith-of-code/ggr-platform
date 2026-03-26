<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsTrajectoryBlock extends Model
{
    protected $table = 'lms_trajectory_blocks';

    protected $fillable = [
        'lms_trajectory_id',
        'type',
        'title',
        'description',
        'date_label',
        'date_start',
        'date_end',
        'lms_assignment_id',
        'position',
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
    ];

    public function trajectory(): BelongsTo
    {
        return $this->belongsTo(LmsTrajectory::class, 'lms_trajectory_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(LmsAssignment::class, 'lms_assignment_id');
    }
}
