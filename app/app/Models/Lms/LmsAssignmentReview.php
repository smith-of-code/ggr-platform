<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsAssignmentReview extends Model
{
    protected $table = 'lms_assignment_reviews';

    protected $fillable = [
        'lms_assignment_submission_id',
        'reviewer_id',
        'comment',
        'files',
        'decision',
    ];

    protected $casts = [
        'files' => 'array',
    ];

    /** @return BelongsTo<LmsAssignmentSubmission, $this> */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(LmsAssignmentSubmission::class, 'lms_assignment_submission_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
