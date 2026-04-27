<?php

namespace App\Services;

use App\Models\UploadedMedia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PresignedUploadService
{
    private const PRESIGNED_TTL_MINUTES = 15;

    private const MAX_FILE_SIZE = 104_857_600; // 100 MB

    private const MAX_IMAGE_SIZE = 10_485_760; // 10 MB

    private const IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

    public function generatePresignedUrl(
        string $filename,
        string $contentType,
        int $size,
        ?string $directory = null,
        ?string $collection = null,
    ): array {
        $disk = config('filesystems.upload_disk', 'public');
        $diskConfig = config("filesystems.disks.{$disk}");

        if (($diskConfig['driver'] ?? null) !== 's3') {
            return ['mode' => 'server'];
        }

        $this->validateFileParams($contentType, $size);

        $isImage = in_array($contentType, self::IMAGE_MIMES, true);
        $subDir = $directory ?: ($isImage ? 'uploads/images' : 'uploads/files');
        $key = $subDir . '/' . now()->format('Y/m') . '/' . Str::ulid() . '.' . $this->guessExtension($filename, $contentType);

        $result = Storage::disk($disk)->temporaryUploadUrl(
            $key,
            now()->addMinutes(self::PRESIGNED_TTL_MINUTES),
            ['ContentType' => $contentType],
        );

        $url = is_array($result) ? ($result['url'] ?? $result[0] ?? '') : (string) $result;
        $headers = is_array($result) ? ($result['headers'] ?? $result[1] ?? []) : [];
        if (is_array($headers)) {
            $headers = array_map(fn($v) => is_array($v) ? implode(', ', $v) : $v, $headers);
        }

        return [
            'mode' => 'presigned',
            'url' => (string) $url,
            'key' => $key,
            'headers' => array_merge($headers, ['Content-Type' => $contentType]),
        ];
    }

    public function confirm(
        string $key,
        string $originalName,
        string $contentType,
        int $size,
        ?string $collection = null,
        ?string $entityType = null,
        string|int|null $entityId = null,
    ): array {
        $disk = config('filesystems.upload_disk', 'public');

        if (!Storage::disk($disk)->exists($key)) {
            throw new \RuntimeException('File not found in storage after upload.');
        }

        $url = Storage::disk($disk)->url($key);

        UploadedMedia::create([
            'filename' => basename($key),
            'original_name' => $originalName,
            'path' => $key,
            'url' => $url,
            'disk' => $disk,
            'mime_type' => $contentType,
            'size' => $size,
            'collection' => $collection,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]);

        return [
            'url' => $url,
            'name' => $originalName,
        ];
    }

    private function validateFileParams(string $contentType, int $size): void
    {
        $isImage = in_array($contentType, self::IMAGE_MIMES, true);
        $maxSize = $isImage ? self::MAX_IMAGE_SIZE : self::MAX_FILE_SIZE;

        if ($size > $maxSize) {
            $maxMb = intdiv($maxSize, 1_048_576);
            throw new \InvalidArgumentException("File size exceeds maximum of {$maxMb} MB.");
        }

        if ($size <= 0) {
            throw new \InvalidArgumentException('File size must be positive.');
        }
    }

    private function guessExtension(string $filename, string $contentType): string
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if ($ext) {
            return strtolower($ext);
        }

        return match ($contentType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'application/pdf' => 'pdf',
            default => 'bin',
        };
    }
}
