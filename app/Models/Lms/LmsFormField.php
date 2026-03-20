<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsFormField extends Model
{
    protected $table = 'lms_form_fields';

    protected $fillable = [
        'lms_form_id', 'key', 'label', 'type',
        'required', 'placeholder', 'options', 'position',
    ];

    protected $casts = [
        'required' => 'boolean',
        'options' => 'array',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(LmsForm::class, 'lms_form_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(LmsFormResponse::class, 'lms_form_field_id');
    }
}
