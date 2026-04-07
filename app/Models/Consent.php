<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consent extends Model
{
    const TYPE_REGISTRATION = 'registration';
    const TYPE_LMS_REGISTRATION = 'lms_registration';
    const TYPE_LMS_INVITE = 'lms_invite';
    const TYPE_LMS_ACTIVATE = 'lms_activate';
    const TYPE_APPLICATION = 'application';
    const TYPE_CONTACT_FORM = 'contact_form';
    const TYPE_LMS_FORM = 'lms_form';

    protected $fillable = [
        'event_type',
        'datetime',
        'ip_address',
        'user_agent',
        'page_url',
        'policy_version',
        'checkbox_value',
        'user_id',
        'email',
        'phone',
        'session_id',
        'additional_data',
    ];

    protected function casts(): array
    {
        return [
            'datetime' => 'datetime',
            'checkbox_value' => 'boolean',
            'additional_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
