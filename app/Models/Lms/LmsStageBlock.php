<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsStageBlock extends Model
{
    protected $table = 'lms_stage_blocks';

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d\TH:i:s');
    }

    protected $fillable = [
        'lms_course_stage_id',
        'type',
        'content',
        'scorm_package',
        'lms_test_id',
        'lms_assignment_id',
        'lms_video_id',
        'position',
        'scheduled_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(LmsCourseStage::class, 'lms_course_stage_id');
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(LmsTest::class, 'lms_test_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(LmsAssignment::class, 'lms_assignment_id');
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(LmsVideo::class, 'lms_video_id');
    }
}
