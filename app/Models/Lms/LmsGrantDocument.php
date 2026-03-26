<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsGrantDocument extends Model
{
    protected $table = 'lms_grant_documents';

    protected $fillable = [
        'lms_grant_id',
        'file_path',
        'original_name',
        'position',
    ];

    public function grant(): BelongsTo
    {
        return $this->belongsTo(LmsGrant::class, 'lms_grant_id');
    }
}
