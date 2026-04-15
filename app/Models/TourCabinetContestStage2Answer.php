<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourCabinetContestStage2Answer extends Model
{
    protected $table = 'tour_cabinet_contest_stage2_answers';

    protected $fillable = [
        'user_id',
        'question_id',
        'answer_text',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(TourCabinetContestStage2Question::class, 'question_id');
    }
}
