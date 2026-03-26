<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsProfileDocument extends Model
{
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
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(LmsProfile::class, 'lms_profile_id');
    }
}
