<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsFormSubmission extends Model
{
    protected $table = 'lms_form_submissions';

    protected $fillable = [
        'lms_form_id', 'user_id', 'ip_address', 'user_agent', 'user_created',
    ];

    protected $casts = [
        'user_created' => 'boolean',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(LmsForm::class, 'lms_form_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(LmsFormResponse::class, 'lms_form_submission_id');
    }
}
