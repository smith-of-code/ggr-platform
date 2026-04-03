<?php

namespace App\Console\Commands;

use App\Models\UploadedMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class IndexExistingMedia extends Command
{
    protected $signature = 'media:index';
    protected $description = 'Scan uploads/images, uploads/files, uploads/kb and create Media records for files not yet tracked';

    private const MIME_MAP = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png', 'gif' => 'image/gif',
        'webp' => 'image/webp', 'svg' => 'image/svg+xml',
        'mp4' => 'video/mp4', 'webm' => 'video/webm',
        'mov' => 'video/quicktime', 'avi' => 'video/x-msvideo',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'zip' => 'application/zip',
    ];

    public function handle(): int
    {
        $disk = config('filesystems.upload_disk', 'public');

        $directories = ['uploads/images', 'uploads/files', 'uploads/kb'];
        $allFiles = [];
        foreach ($directories as $dir) {
            try {
                $allFiles = array_merge($allFiles, Storage::disk($disk)->allFiles($dir));
            } catch (\Throwable) {
            }
        }

        $existingPaths = UploadedMedia::pluck('path')->flip();
        $created = 0;

        foreach ($allFiles as $filePath) {
            if ($existingPaths->has($filePath)) {
                continue;
            }

            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mime = self::MIME_MAP[$ext] ?? null;
            if (!$mime) {
                continue;
            }

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
                'mime_type' => $mime,
                'size' => $size,
            ]);

            $created++;
        }

        $this->info("Indexed {$created} new file(s). Total in uploaded_media table: " . UploadedMedia::count());

        return self::SUCCESS;
    }
}
