<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsForm extends Model
{
    protected $table = 'lms_forms';

    protected $fillable = [
        'lms_event_id', 'title', 'description', 'slug',
        'is_active', 'is_anonymous', 'allow_embed', 'create_users',
        'require_consent', 'consent_document_url',
        'fio_field_key', 'email_field_key', 'phone_field_key', 'position_field_key',
        'thank_you_message',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_anonymous' => 'boolean',
        'allow_embed' => 'boolean',
        'create_users' => 'boolean',
        'require_consent' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(LmsEvent::class, 'lms_event_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(LmsFormField::class, 'lms_form_id')->orderBy('position');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(LmsFormSubmission::class, 'lms_form_id');
    }
}
