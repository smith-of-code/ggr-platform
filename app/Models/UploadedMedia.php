<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadedMedia extends Model
{
    protected $table = 'uploaded_media';

    protected $fillable = [
        'filename',
        'original_name',
        'path',
        'url',
        'disk',
        'mime_type',
        'size',
        'collection',
        'entity_type',
        'entity_id',
    ];
}
