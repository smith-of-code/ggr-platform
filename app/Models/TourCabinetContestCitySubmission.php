<?php

namespace App\Models;

use App\Models\Lms\LmsFormSubmission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourCabinetContestCitySubmission extends Model
{
    protected $table = 'tour_cabinet_contest_city_submissions';

    protected $fillable = [
        'user_id',
        'city_id',
        'lms_form_submission_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(LmsFormSubmission::class, 'lms_form_submission_id');
    }
}
