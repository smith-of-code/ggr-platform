<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsTest extends Model
{
    protected $table = 'lms_tests';

    protected $fillable = [
        'lms_event_id',
        'title',
        'description',
        'time_limit_minutes',
        'shuffle_questions',
        'shuffle_answers',
        'show_correct_answers',
        'passing_score',
        'max_attempts',
        'in_menu',
        'is_active',
    ];

    protected $casts = [
        'shuffle_questions' => 'boolean',
        'shuffle_answers' => 'boolean',
        'show_correct_answers' => 'boolean',
        'in_menu' => 'boolean',
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<LmsEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(LmsEvent::class, 'lms_event_id');
    }

    /** @return HasMany<LmsTestQuestion> */
    public function questions(): HasMany
    {
        return $this->hasMany(LmsTestQuestion::class, 'lms_test_id');
    }

    /** @return HasMany<LmsTestAttempt> */
    public function attempts(): HasMany
    {
        return $this->hasMany(LmsTestAttempt::class, 'lms_test_id');
    }
}
