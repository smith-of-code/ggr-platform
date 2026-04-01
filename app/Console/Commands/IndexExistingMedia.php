<?php

namespace App\Console\Commands;

use App\Models\UploadedMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class IndexExistingMedia extends Command
{
    protected $signature = 'media:index';
    protected $description = 'Scan uploads/images and create Media records for files not yet tracked';

    public function handle(): int
    {
        $disk = config('filesystems.upload_disk', 'public');
        $files = Storage::disk($disk)->allFiles('uploads/images');

        $existingPaths = UploadedMedia::pluck('path')->flip();
        $created = 0;

        foreach ($files as $filePath) {
            if ($existingPaths->has($filePath)) {
                continue;
            }

            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                continue;
            }

            $mimeMap = [
                'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png', 'gif' => 'image/gif',
                'webp' => 'image/webp', 'svg' => 'image/svg+xml',
            ];

            $size = 0;
            try {
                $size = Storage::disk($disk)->size($filePath);
            } catch (\Throwable) {
            }

            UploadedMedia::create([
                'filename' => basename($filePath),
                'original_name' => basename($filePath),
                'path' => $filePath,
                'url' => Storage::disk($disk)->url($filePath),
                'disk' => $disk,
                'mime_type' => $mimeMap[$ext] ?? 'image/' . $ext,
                'size' => $size,
            ]);

            $created++;
        }

        $this->info("Indexed {$created} new file(s). Total in uploaded_media table: " . UploadedMedia::count());

        return self::SUCCESS;
    }
}
