<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LmsVideo extends Model
{
    protected $table = 'lms_videos';

    protected $fillable = [
        'lms_event_id',
        'title',
        'description',
        'source',
        'url',
        'file_path',
        'thumbnail',
        'duration_seconds',
        'is_recording',
        'is_active',
        'visible_to_all',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'is_recording' => 'boolean',
        'is_active' => 'boolean',
        'visible_to_all' => 'boolean',
    ];

    /** @return BelongsTo<LmsEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(LmsEvent::class, 'lms_event_id');
    }

    /** @return BelongsToMany<LmsCourse> */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(LmsCourse::class, 'lms_video_course_access', 'lms_video_id', 'lms_course_id');
    }
}
