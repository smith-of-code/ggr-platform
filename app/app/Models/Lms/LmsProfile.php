<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LmsProfile extends Model
{
    protected $table = 'lms_profiles';

    protected $fillable = [
        'user_id',
        'lms_event_id',
        'role',
        'lms_role_id',
        'status',
        'invite_token',
        'invited_at',
        'activated_at',
        'position',
        'phone',
        'city',
        'avatar',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'activated_at' => 'datetime',
        ];
    }

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

    public function generateInviteToken(): string
    {
        do {
            $token = Str::random(48);
        } while (static::where('invite_token', $token)->exists());

        $this->update(['invite_token' => $token]);

        return $token;
    }
}
