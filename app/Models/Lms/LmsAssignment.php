<?php

namespace App\Models\Lms;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsAssignment extends Model
{
    protected $table = 'lms_assignments';

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return Carbon::instance($date)->utc()->format('Y-m-d\TH:i:s\Z');
    }

    protected $fillable = [
        'lms_event_id',
        'title',
        'description',
        'template_file',
        'template_file_name',
        'completion_mode',
        'deadline',
        'is_active',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<LmsEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(LmsEvent::class, 'lms_event_id');
    }

    /** @return HasMany<LmsAssignmentSubmission> */
    public function submissions(): HasMany
    {
        return $this->hasMany(LmsAssignmentSubmission::class, 'lms_assignment_id');
    }

    /** @return HasMany<LmsAssignmentTask> */
    public function tasks(): HasMany
    {
        return $this->hasMany(LmsAssignmentTask::class, 'lms_assignment_id')->orderBy('position');
    }
}
