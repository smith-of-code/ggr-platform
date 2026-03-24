<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function image(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp,svg|max:5120',
        ]);

        $disk = config('filesystems.upload_disk');
        $path = $request->file('image')->store('uploads/images', $disk);

        return response()->json([
            'url' => Storage::disk($disk)->url($path),
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

        return response()->json([
            'url'  => Storage::disk($disk)->url($path),
            'name' => $file->getClientOriginalName(),
        ]);
    }
}
