<?php

namespace App\Http\Controllers\Admin;

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

        $disk = config('filesystems.upload_disk', 'public');
        $path = $request->file('image')->store('uploads/images', $disk);

        return response()->json([
            'url' => Storage::disk($disk)->url($path),
        ]);
    }
}
