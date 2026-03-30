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
        return count($this->getMissingFields()) === 0;
    }

    public function getMissingFields(): array
    {
        $missing = [];
        $user = $this->user;

        if (! $user || ! $user->last_name) {
            $missing[] = 'Фамилия';
        }
        if (! $user || ! $user->first_name) {
            $missing[] = 'Имя';
        }
        if (! $user || ! $user->email) {
            $missing[] = 'Email';
        }
        if (! $this->phone) {
            $missing[] = 'Телефон';
        }
        if (! $this->city) {
            $missing[] = 'Город';
        }
        if (! $this->organization) {
            $missing[] = 'Организация';
        }
        if (! $this->position) {
            $missing[] = 'Должность';
        }

        $docLabels = [
            LmsProfileDocument::TYPE_ENROLLMENT_APPLICATION => 'Заявление на зачисление',
            LmsProfileDocument::TYPE_SNILS => 'СНИЛС',
            LmsProfileDocument::TYPE_DIPLOMA => 'Диплом',
            LmsProfileDocument::TYPE_PERSONAL_DATA_CONSENT => 'Согласие на обработку ПД',
        ];

        $uploadedTypes = $this->documents()->pluck('type')->toArray();

        foreach ($docLabels as $type => $label) {
            if (! in_array($type, $uploadedTypes)) {
                $missing[] = $label . ' (документ)';
            }
        }

        return $missing;
    }
}
