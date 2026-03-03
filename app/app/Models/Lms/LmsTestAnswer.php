<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsTestAnswer extends Model
{
    protected $table = 'lms_test_answers';

    protected $fillable = [
        'lms_test_question_id',
        'answer',
        'is_correct',
        'position',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /** @return BelongsTo<LmsTestQuestion, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(LmsTestQuestion::class, 'lms_test_question_id');
    }
}
