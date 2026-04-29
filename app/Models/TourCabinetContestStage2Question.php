<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourCabinetContestStage2Question extends Model
{
    protected $table = 'tour_cabinet_contest_stage2_questions';

    protected $fillable = [
        'body',
        'sort_order',
        'is_active',
        'direction_id',
        'min_length',
        'max_length',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'min_length' => 'integer',
            'max_length' => 'integer',
        ];
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TourCabinetContestStage2Answer::class, 'question_id');
    }
}
