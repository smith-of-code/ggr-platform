<?php

namespace App\Models;

use App\Models\Lms\LmsProfileDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourCabinetDocument extends Model
{
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
            LmsProfileDocument::TYPE_SNILS,
            LmsProfileDocument::TYPE_DIPLOMA,
            LmsProfileDocument::TYPE_NAME_CHANGE_CERTIFICATE,
        ];
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
