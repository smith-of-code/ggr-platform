<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function file(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        $disk = config('filesystems.upload_disk', 'public');
        $file = $request->file('file');

        $filename = Str::ulid() . '.' . ($file->guessExtension() ?: 'bin');
        $directory = 'uploads/files/' . now()->format('Y/m');

        try {
            $path = Storage::disk($disk)->putFileAs($directory, $file, $filename, 'public');

            if (!$path) {
                throw new \RuntimeException("putFileAs returned empty path (disk: {$disk})");
            }

            return response()->json([
                'url' => Storage::disk($disk)->url($path),
                'name' => $file->getClientOriginalName(),
            ]);
        } catch (\Throwable $e) {
            Log::error('File upload failed', [
                'disk' => $disk,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Не удалось загрузить файл. Попробуйте ещё раз.',
            ], 500);
        }
    }

    public function image(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp,svg|max:5120',
        ]);

        $disk = config('filesystems.upload_disk', 'public');
        $file = $request->file('image');

        $filename = Str::ulid() . '.' . ($file->guessExtension() ?: 'jpg');
        $directory = 'uploads/images/' . now()->format('Y/m');

        try {
            $path = Storage::disk($disk)->putFileAs($directory, $file, $filename, 'public');

            if (!$path) {
                throw new \RuntimeException("putFileAs returned empty path (disk: {$disk})");
            }

            $url = Storage::disk($disk)->url($path);

            if ($disk === 'public' && !file_exists(public_path('storage'))) {
                \Artisan::call('storage:link');
            }

            return response()->json([
                'url' => $url,
                'path' => $path,
            ]);
        } catch (\Throwable $e) {
            Log::error('Image upload failed', [
                'disk' => $disk,
                'error' => $e->getMessage(),
            ]);

            if ($disk !== 'public') {
                try {
                    $fallbackPath = Storage::disk('public')->putFileAs($directory, $file, $filename, 'public');

                    if (!file_exists(public_path('storage'))) {
                        \Artisan::call('storage:link');
                    }

                    return response()->json([
                        'url' => Storage::disk('public')->url($fallbackPath),
                        'path' => $fallbackPath,
                    ]);
                } catch (\Throwable $fallbackError) {
                    Log::error('Image upload fallback also failed', [
                        'error' => $fallbackError->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'message' => 'Не удалось загрузить изображение. Попробуйте ещё раз.',
            ], 500);
        }
    }
}
