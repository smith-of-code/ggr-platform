<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsGrantEnrollmentComment extends Model
{
    protected $table = 'lms_grant_enrollment_comments';

    protected $fillable = [
        'lms_grant_enrollment_id',
        'admin_id',
        'status',
        'comment',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(LmsGrantEnrollment::class, 'lms_grant_enrollment_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
