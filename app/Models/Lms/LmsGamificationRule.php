<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsGamificationRule extends Model
{
    protected $table = 'lms_gamification_rules';

    protected $fillable = [
        'lms_event_id',
        'title',
        'action',
        'points',
        'max_times',
        'is_auto',
        'is_active',
    ];

    protected $casts = [
        'is_auto' => 'boolean',
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<LmsEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(LmsEvent::class, 'lms_event_id');
    }

    /** @return HasMany<LmsGamificationPoint> */
    public function pointEntries(): HasMany
    {
        return $this->hasMany(LmsGamificationPoint::class, 'lms_gamification_rule_id');
    }
}
