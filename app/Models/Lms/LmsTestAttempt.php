<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsTestAttempt extends Model
{
    protected $table = 'lms_test_attempts';

    protected $fillable = [
        'lms_test_id',
        'user_id',
        'status',
        'score',
        'max_score',
        'percentage',
        'passed',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /** @return BelongsTo<LmsTest, $this> */
    public function test(): BelongsTo
    {
        return $this->belongsTo(LmsTest::class, 'lms_test_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<LmsTestResponse> */
    public function responses(): HasMany
    {
        return $this->hasMany(LmsTestResponse::class, 'lms_test_attempt_id');
    }
}
