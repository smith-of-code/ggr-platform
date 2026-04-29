<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourCabinetCommerceCityForm extends Model
{
    protected $table = 'tour_cabinet_commerce_city_forms';

    protected $fillable = [
        'city_id',
        'lms_form_slug',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
