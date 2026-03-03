<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsTestResponse extends Model
{
    protected $table = 'lms_test_responses';

    protected $fillable = [
        'lms_test_attempt_id',
        'lms_test_question_id',
        'selected_answer_ids',
        'text_answer',
        'is_correct',
        'points_earned',
    ];

    protected $casts = [
        'selected_answer_ids' => 'array',
        'is_correct' => 'boolean',
    ];

    /** @return BelongsTo<LmsTestAttempt, $this> */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(LmsTestAttempt::class, 'lms_test_attempt_id');
    }

    /** @return BelongsTo<LmsTestQuestion, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(LmsTestQuestion::class, 'lms_test_question_id');
    }
}
