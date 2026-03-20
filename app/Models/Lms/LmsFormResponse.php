<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsFormResponse extends Model
{
    protected $table = 'lms_form_responses';

    protected $fillable = [
        'lms_form_submission_id', 'lms_form_field_id', 'value',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(LmsFormSubmission::class, 'lms_form_submission_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(LmsFormField::class, 'lms_form_field_id');
    }
}
