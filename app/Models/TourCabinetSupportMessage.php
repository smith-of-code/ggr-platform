<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourCabinetSupportMessage extends Model
{
    public const AUTHOR_USER = 'user';

    public const AUTHOR_ADMIN = 'admin';

    protected $fillable = [
        'ticket_id',
        'author_type',
        'author_user_id',
        'body',
    ];

    /** @return BelongsTo<TourCabinetSupportTicket, $this> */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(TourCabinetSupportTicket::class, 'ticket_id');
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /** @return HasMany<TourCabinetSupportAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(TourCabinetSupportAttachment::class, 'message_id');
    }

    public function isFromAdmin(): bool
    {
        return $this->author_type === self::AUTHOR_ADMIN;
    }
}
