<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsProfileDocument extends Model
{
    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_ANNULLED = 'annulled';

    public const TYPE_ENROLLMENT_APPLICATION = 'enrollment_application';
    public const TYPE_SNILS = 'snils';
    public const TYPE_DIPLOMA = 'diploma';
    public const TYPE_PERSONAL_DATA_CONSENT = 'personal_data_consent';
    public const TYPE_NAME_CHANGE_CERTIFICATE = 'name_change_certificate';

    public const TYPES = [
        self::TYPE_ENROLLMENT_APPLICATION,
        self::TYPE_SNILS,
        self::TYPE_DIPLOMA,
        self::TYPE_PERSONAL_DATA_CONSENT,
        self::TYPE_NAME_CHANGE_CERTIFICATE,
    ];

    public const TYPES_WITH_TEMPLATE = [
        self::TYPE_ENROLLMENT_APPLICATION,
        self::TYPE_PERSONAL_DATA_CONSENT,
    ];

    protected $table = 'lms_profile_documents';

    protected $fillable = [
        'lms_profile_id',
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

    public function hasFile(): bool
    {
        return $this->file_path !== null && $this->file_path !== '';
    }

    public function isLockedForParticipant(): bool
    {
        return $this->status === self::STATUS_APPROVED && $this->hasFile();
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(LmsProfile::class, 'lms_profile_id');
    }
}
