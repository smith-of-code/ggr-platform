<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsGamificationPoint extends Model
{
    protected $table = 'lms_gamification_points';

    protected $fillable = [
        'lms_event_id',
        'user_id',
        'lms_gamification_rule_id',
        'points',
        'reason',
    ];

    /** @return BelongsTo<LmsEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(LmsEvent::class, 'lms_event_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<LmsGamificationRule, $this> */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(LmsGamificationRule::class, 'lms_gamification_rule_id');
    }
}
