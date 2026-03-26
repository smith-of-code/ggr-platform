<?php

namespace App\Models\Lms;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'city_id',
        'avatar',
        'organization',
        'project_description',
        'preferred_channel',
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

    public function cityRelation(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LmsProfileDocument::class);
    }

    public function generateInviteToken(): string
    {
        do {
            $token = Str::random(48);
        } while (static::where('invite_token', $token)->exists());

        $this->update(['invite_token' => $token]);

        return $token;
    }

    public function isProfileComplete(): bool
    {
        $user = $this->user;

        if (! $user?->last_name || ! $user?->first_name || ! $user?->email) {
            return false;
        }

        if (! $this->phone || ! $this->city_id || ! $this->organization || ! $this->position) {
            return false;
        }

        $requiredDocs = [
            LmsProfileDocument::TYPE_ENROLLMENT_APPLICATION,
            LmsProfileDocument::TYPE_SNILS,
            LmsProfileDocument::TYPE_DIPLOMA,
            LmsProfileDocument::TYPE_PERSONAL_DATA_CONSENT,
        ];

        $uploadedTypes = $this->documents()->pluck('type')->toArray();

        foreach ($requiredDocs as $type) {
            if (! in_array($type, $uploadedTypes)) {
                return false;
            }
        }

        return true;
    }
}
