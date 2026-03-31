<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsSubmissionAnswer extends Model
{
    protected $table = 'lms_submission_answers';

    protected $fillable = [
        'lms_assignment_submission_id',
        'lms_assignment_task_id',
        'text_content',
        'link',
        'files',
    ];

    protected $casts = [
        'files' => 'array',
    ];

    /** @return BelongsTo<LmsAssignmentSubmission, $this> */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(LmsAssignmentSubmission::class, 'lms_assignment_submission_id');
    }

    /** @return BelongsTo<LmsAssignmentTask, $this> */
    public function task(): BelongsTo
    {
        return $this->belongsTo(LmsAssignmentTask::class, 'lms_assignment_task_id');
    }
}
