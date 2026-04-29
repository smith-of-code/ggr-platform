<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsAssignmentSubmissionRead extends Model
{
    protected $table = 'lms_assignment_submission_reads';

    protected $fillable = [
        'lms_assignment_submission_id',
        'user_id',
        'last_read_at',
    ];

    protected function casts(): array
    {
        return [
            'last_read_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<LmsAssignmentSubmission, $this> */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(LmsAssignmentSubmission::class, 'lms_assignment_submission_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
