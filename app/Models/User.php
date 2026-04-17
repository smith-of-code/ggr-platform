<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'first_name',
        'patronymic',
        'gender',
        'birth_date',
        'email',
        'is_admin',
        'is_tour_cabinet_user',
        'phone',
        'avatar_path',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_tour_cabinet_user' => 'boolean',
            'birth_date' => 'date',
        ];
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /** @return HasMany<TourCabinetSupportTicket, $this> */
    public function tourCabinetSupportTickets(): HasMany
    {
        return $this->hasMany(TourCabinetSupportTicket::class);
    }

    /**
     * Цифры телефона для сопоставления при входе: РФ 8xxxxxxxxxx → 7xxxxxxxxxx, 10 цифр с 9 → 7…
     */
    public static function normalizePhoneDigitsForLogin(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '' || strlen($digits) < 10) {
            return null;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '8')) {
            $digits = '7'.substr($digits, 1);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
            $digits = '7'.$digits;
        }

        return $digits;
    }

    /**
     * Один пользователь с уникальным нормализованным телефоном; при коллизии вход по телефону отклоняется.
     */
    public static function findSingleUserByLoginPhone(string $raw): ?self
    {
        $want = self::normalizePhoneDigitsForLogin($raw);
        if ($want === null) {
            return null;
        }

        $matched = self::query()
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get()
            ->filter(fn (self $user) => self::normalizePhoneDigitsForLogin($user->phone) === $want)
            ->values();

        return $matched->count() === 1 ? $matched->first() : null;
    }
}
