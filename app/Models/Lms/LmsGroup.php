<?php

namespace App\Models\Lms;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LmsGroup extends Model
{
    protected $table = 'lms_groups';

    protected $fillable = [
        'lms_event_id',
        'title',
        'city_id',
        'is_city_group',
        'linked_cities',
        'curator_id',
    ];

    protected function casts(): array
    {
        return [
            'linked_cities' => 'array',
            'is_city_group' => 'boolean',
        ];
    }

    /** @return BelongsTo<LmsEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(LmsEvent::class, 'lms_event_id');
    }

    /** @return BelongsTo<User, $this> */
    public function curator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'curator_id');
    }

    /** @return BelongsTo<City, $this> */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /** @return BelongsToMany<User> */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lms_group_members')
            ->withPivot('is_gamification_inactive')
            ->withTimestamps();
    }
}
