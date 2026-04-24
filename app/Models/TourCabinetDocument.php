<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourCabinetDocument extends Model
{
    public const TYPE_PASSPORT_SPREAD = 'passport_spread';

    public const TYPE_PASSPORT_REGISTRATION = 'passport_registration';

    public const TYPE_SNILS = 'snils';

    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_ANNULLED = 'annulled';

    protected $fillable = [
        'user_id',
        'type',
        'file_path',
        'original_name',
        'status',
        'admin_comment',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    /** @return list<string> */
    public static function allowedTypes(): array
    {
        return [
            self::TYPE_PASSPORT_SPREAD,
            self::TYPE_PASSPORT_REGISTRATION,
            self::TYPE_SNILS,
        ];
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_PASSPORT_SPREAD => 'Паспорт: разворот с 1–2 страницей',
            self::TYPE_PASSPORT_REGISTRATION => 'Паспорт: страница с пропиской',
            self::TYPE_SNILS => 'СНИЛС',
            default => $type,
        };
    }

    public function hasFile(): bool
    {
        return $this->file_path !== null && $this->file_path !== '';
    }

    public function isLockedForParticipant(): bool
    {
        return $this->status === self::STATUS_APPROVED && $this->hasFile();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
