<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsGrantEnrollment extends Model
{
    protected $table = 'lms_grant_enrollments';

    protected $fillable = [
        'lms_grant_id',
        'user_id',
    ];

    public function grant(): BelongsTo
    {
        return $this->belongsTo(LmsGrant::class, 'lms_grant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adminComments(): HasMany
    {
        return $this->hasMany(LmsGrantEnrollmentComment::class, 'lms_grant_enrollment_id')
            ->orderByDesc('created_at');
    }
}
