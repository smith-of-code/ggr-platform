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
        'for_city_ranking_only',
        'city_name',
        'lms_group_id',
        'lms_gamification_rule_id',
        'source_type',
        'source_id',
        'points',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'for_city_ranking_only' => 'boolean',
        ];
    }

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

    /** @return BelongsTo<LmsGroup, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(LmsGroup::class, 'lms_group_id');
    }

    /** @return BelongsTo<LmsGamificationRule, $this> */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(LmsGamificationRule::class, 'lms_gamification_rule_id');
    }
}
