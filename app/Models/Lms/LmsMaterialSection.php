<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LmsMaterialSection extends Model
{
    protected $table = 'lms_material_sections';

    protected $fillable = [
        'lms_event_id',
        'title',
        'content',
        'in_menu',
        'position',
    ];

    protected $casts = [
        'in_menu' => 'boolean',
    ];

    /** @return BelongsTo<LmsEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(LmsEvent::class, 'lms_event_id');
    }

    /** @return BelongsToMany<LmsGroup> */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(LmsGroup::class, 'lms_material_access');
    }
}
