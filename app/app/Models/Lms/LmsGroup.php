<?php

namespace App\Models\Lms;

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
        'curator_id',
    ];

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

    /** @return BelongsToMany<User> */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lms_group_members');
    }
}
