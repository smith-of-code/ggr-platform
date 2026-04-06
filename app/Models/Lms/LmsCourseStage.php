<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsCourseStage extends Model
{
    protected $table = 'lms_course_stages';

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d\TH:i:s');
    }

    protected $fillable = [
        'lms_course_id',
        'lms_course_module_id',
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
        'available_from',
        'duration_minutes',
        'source_stage_id',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'available_from' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    /** @return BelongsTo<LmsCourse, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LmsCourse::class, 'lms_course_id');
    }

    /** @return BelongsTo<LmsCourseModule, $this> */
    public function module(): BelongsTo
    {
        return $this->belongsTo(LmsCourseModule::class, 'lms_course_module_id');
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

    public function sourceStage(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_stage_id');
    }

    /** @return HasMany<LmsStageBlock> */
    public function blocks(): HasMany
    {
        return $this->hasMany(LmsStageBlock::class, 'lms_course_stage_id')->orderBy('position');
    }

    /** @return HasMany<LmsStageProgress> */
    public function progress(): HasMany
    {
        return $this->hasMany(LmsStageProgress::class, 'lms_course_stage_id');
    }
}
