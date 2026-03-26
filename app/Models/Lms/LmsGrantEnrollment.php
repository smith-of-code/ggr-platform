<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
