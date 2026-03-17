<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LmsRole extends Model
{
    protected $table = 'lms_roles';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'lms_event_id',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(LmsEvent::class, 'lms_event_id');
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(LmsProfile::class, 'lms_role_id');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(LmsCourse::class, 'lms_course_role_access', 'lms_role_id', 'lms_course_id');
    }
}
