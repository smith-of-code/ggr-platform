<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsTestQuestion extends Model
{
    protected $table = 'lms_test_questions';

    protected $fillable = [
        'lms_test_id',
        'question',
        'type',
        'points',
        'position',
    ];

    /** @return BelongsTo<LmsTest, $this> */
    public function test(): BelongsTo
    {
        return $this->belongsTo(LmsTest::class, 'lms_test_id');
    }

    /** @return HasMany<LmsTestAnswer> */
    public function answers(): HasMany
    {
        return $this->hasMany(LmsTestAnswer::class, 'lms_test_question_id');
    }
}
