<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsAssignmentSubmission extends Model
{
    protected $table = 'lms_assignment_submissions';

    protected $fillable = [
        'lms_assignment_id',
        'user_id',
        'text_content',
        'link',
        'files',
        'status',
    ];

    protected $casts = [
        'files' => 'array',
    ];

    /** @return BelongsTo<LmsAssignment, $this> */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(LmsAssignment::class, 'lms_assignment_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<LmsAssignmentReview> */
    public function reviews(): HasMany
    {
        return $this->hasMany(LmsAssignmentReview::class, 'lms_assignment_submission_id');
    }

    /** @return HasMany<LmsAssignmentComment> */
    public function comments(): HasMany
    {
        return $this->hasMany(LmsAssignmentComment::class, 'lms_assignment_submission_id')->orderBy('created_at');
    }
}
