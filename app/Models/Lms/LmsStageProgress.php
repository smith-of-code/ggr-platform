<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsStageProgress extends Model
{
    protected $table = 'lms_stage_progress';

    protected $fillable = [
        'lms_course_stage_id',
        'user_id',
        'status',
        'scorm_data',
        'score',
        'watched_seconds',
        'completed_at',
    ];

    protected $casts = [
        'scorm_data' => 'array',
        'watched_seconds' => 'integer',
        'completed_at' => 'datetime',
    ];

    /** @return BelongsTo<LmsCourseStage, $this> */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(LmsCourseStage::class, 'lms_course_stage_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
