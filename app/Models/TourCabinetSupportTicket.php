<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourCabinetSupportTicket extends Model
{
    public const CATEGORY_CONTEST = 'contest';

    public const CATEGORY_TOUR_APPLICATION = 'tour_application';

    public const CATEGORY_TECHNICAL = 'technical';

    public const CATEGORY_OTHER = 'other';

    public const STATUS_OPEN = 'open';

    public const STATUS_PENDING_USER = 'pending_user';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_CLOSED = 'closed';

    /** @var list<string> */
    public const CATEGORIES = [
        self::CATEGORY_CONTEST,
        self::CATEGORY_TOUR_APPLICATION,
        self::CATEGORY_TECHNICAL,
        self::CATEGORY_OTHER,
    ];

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_PENDING_USER,
        self::STATUS_RESOLVED,
        self::STATUS_CLOSED,
    ];

    protected $fillable = [
        'user_id',
        'subject',
        'category',
        'status',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<TourCabinetSupportMessage, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(TourCabinetSupportMessage::class, 'ticket_id')->orderBy('created_at');
    }

    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            self::CATEGORY_CONTEST => 'Конкурс',
            self::CATEGORY_TOUR_APPLICATION => 'Заявка на тур',
            self::CATEGORY_TECHNICAL => 'Техника / доступ',
            self::CATEGORY_OTHER => 'Другое',
            default => $category,
        };
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_OPEN => 'Открыт',
            self::STATUS_PENDING_USER => 'Ожидаем ответа',
            self::STATUS_RESOLVED => 'Решён',
            self::STATUS_CLOSED => 'Закрыт',
            default => $status,
        };
    }
}
