<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsCourseEnrollment extends Model
{
    protected $table = 'lms_course_enrollments';

    protected $fillable = [
        'lms_course_id',
        'user_id',
        'status',
        'completed_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /** @return BelongsTo<LmsCourse, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LmsCourse::class, 'lms_course_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
