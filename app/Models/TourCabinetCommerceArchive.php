<?php

namespace App\Models;

use App\Models\Lms\LmsFormSubmission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourCabinetCommerceArchive extends Model
{
    public const STATUS_SENT = 'sent';

    protected $table = 'tour_cabinet_commerce_archives';

    protected $fillable = [
        'user_id',
        'city_id',
        'tour_id',
        'lms_form_submission_id',
        'submitted_at',
        'status',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function lmsFormSubmission(): BelongsTo
    {
        return $this->belongsTo(LmsFormSubmission::class);
    }
}
