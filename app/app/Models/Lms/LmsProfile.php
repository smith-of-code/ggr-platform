<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsProfile extends Model
{
    protected $table = 'lms_profiles';

    protected $fillable = [
        'user_id',
        'lms_event_id',
        'role',
        'lms_role_id',
        'position',
        'phone',
        'city',
        'avatar',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(LmsEvent::class, 'lms_event_id');
    }

    public function lmsRole(): BelongsTo
    {
        return $this->belongsTo(LmsRole::class, 'lms_role_id');
    }
}
