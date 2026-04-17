<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TourCabinetSupportAttachment extends Model
{
    protected $fillable = [
        'message_id',
        'disk',
        'path',
        'original_filename',
        'mime_type',
        'size_bytes',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
        ];
    }

    /** @return BelongsTo<TourCabinetSupportMessage, $this> */
    public function message(): BelongsTo
    {
        return $this->belongsTo(TourCabinetSupportMessage::class, 'message_id');
    }

    public function deleteStoredFile(): void
    {
        if ($this->path !== '') {
            Storage::disk($this->disk)->delete($this->path);
        }
    }
}
