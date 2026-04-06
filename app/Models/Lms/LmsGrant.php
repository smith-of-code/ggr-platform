<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsGrant extends Model
{
    protected $table = 'lms_grants';

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d\TH:i:s');
    }

    public const TYPE_GRANT = 'grant';
    public const TYPE_SUBSIDY = 'subsidy';
    public const TYPE_CREDIT = 'credit';

    public const TYPES = [
        self::TYPE_GRANT => 'Грант',
        self::TYPE_SUBSIDY => 'Субсидия',
        self::TYPE_CREDIT => 'Кредит',
    ];

    protected $fillable = [
        'lms_event_id',
        'title',
        'type',
        'city',
        'description',
        'application_start',
        'application_end',
        'is_active',
        'position',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'city' => 'array',
        'application_start' => 'datetime',
        'application_end' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(LmsEvent::class, 'lms_event_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LmsGrantDocument::class, 'lms_grant_id')->orderBy('position');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(LmsGrantEnrollment::class, 'lms_grant_id');
    }
}
