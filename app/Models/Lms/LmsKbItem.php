<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsKbItem extends Model
{
    protected $table = 'lms_kb_items';

    protected $fillable = [
        'lms_kb_section_id',
        'title',
        'type',
        'content',
        'url',
        'file_path',
        'position',
    ];

    /** @return BelongsTo<LmsKbSection, $this> */
    public function section(): BelongsTo
    {
        return $this->belongsTo(LmsKbSection::class, 'lms_kb_section_id');
    }
}
