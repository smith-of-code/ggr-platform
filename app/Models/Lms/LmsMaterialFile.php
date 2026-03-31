<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsMaterialFile extends Model
{
    protected $table = 'lms_material_files';

    protected $fillable = [
        'lms_material_section_id',
        'title',
        'file_path',
        'file_name',
        'file_size',
        'position',
    ];

    /** @return BelongsTo<LmsMaterialSection, $this> */
    public function section(): BelongsTo
    {
        return $this->belongsTo(LmsMaterialSection::class, 'lms_material_section_id');
    }
}
