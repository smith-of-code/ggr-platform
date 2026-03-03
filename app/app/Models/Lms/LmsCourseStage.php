<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsCourseStage extends Model
{
    protected $table = 'lms_course_stages';

    protected $fillable = [
        'lms_course_id',
        'title',
        'description',
        'type',
        'content',
        'scorm_package',
        'lms_test_id',
        'lms_assignment_id',
        'lms_video_id',
        'is_locked',
        'position',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    /** @return BelongsTo<LmsCourse, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LmsCourse::class, 'lms_course_id');
    }

    /** @return BelongsTo<LmsTest, $this> */
    public function test(): BelongsTo
    {
        return $this->belongsTo(LmsTest::class, 'lms_test_id');
    }

    /** @return BelongsTo<LmsAssignment, $this> */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(LmsAssignment::class, 'lms_assignment_id');
    }

    /** @return BelongsTo<LmsVideo, $this> */
    public function video(): BelongsTo
    {
        return $this->belongsTo(LmsVideo::class, 'lms_video_id');
    }

    /** @return HasMany<LmsStageProgress> */
    public function progress(): HasMany
    {
        return $this->hasMany(LmsStageProgress::class, 'lms_course_stage_id');
    }
}
