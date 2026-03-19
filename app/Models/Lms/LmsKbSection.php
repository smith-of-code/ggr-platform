<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsKbSection extends Model
{
    protected $table = 'lms_kb_sections';

    protected $fillable = [
        'lms_event_id',
        'parent_id',
        'title',
        'description',
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

    /** @return BelongsTo<LmsKbSection, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(LmsKbSection::class, 'parent_id');
    }

    /** @return HasMany<LmsKbSection> */
    public function children(): HasMany
    {
        return $this->hasMany(LmsKbSection::class, 'parent_id');
    }

    /** @return HasMany<LmsKbItem> */
    public function items(): HasMany
    {
        return $this->hasMany(LmsKbItem::class, 'lms_kb_section_id');
    }

    /** @return BelongsToMany<LmsGroup> */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(LmsGroup::class, 'lms_kb_access');
    }
}
