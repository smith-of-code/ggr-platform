<?php

namespace App\Models;

use App\Models\Lms\LmsFormSubmission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourCabinetCommerceProgress extends Model
{
    protected $table = 'tour_cabinet_commerce_progress';

    protected $fillable = [
        'user_id',
        'current_stage',
        'city_id',
        'tour_id',
        'lms_form_submission_id',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'current_stage' => 'integer',
            'completed_at' => 'datetime',
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
