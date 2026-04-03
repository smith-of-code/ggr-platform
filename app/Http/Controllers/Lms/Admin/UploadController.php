<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\UploadedMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function image(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp,svg|max:10240',
        ]);

        $disk = config('filesystems.upload_disk');
        $file = $request->file('image');
        $path = $file->store('uploads/images', $disk);
        $url = Storage::disk($disk)->url($path);

        UploadedMedia::create([
            'filename' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'url' => $url,
            'disk' => $disk,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'collection' => $request->input('collection'),
            'entity_type' => $request->input('entity_type'),
            'entity_id' => $request->input('entity_id'),
        ]);

        return response()->json([
            'url' => $url,
        ]);
    }

    public function file(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        $disk = config('filesystems.upload_disk');
        $file = $request->file('file');
        $path = $file->store('uploads/kb', $disk);
        $url = Storage::disk($disk)->url($path);

        UploadedMedia::create([
            'filename' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'url' => $url,
            'disk' => $disk,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'collection' => $request->input('collection'),
            'entity_type' => $request->input('entity_type'),
            'entity_id' => $request->input('entity_id'),
        ]);

        return response()->json([
            'url'  => $url,
            'name' => $file->getClientOriginalName(),
        ]);
    }
}
