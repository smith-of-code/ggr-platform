<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function image(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp,svg|max:5120',
        ]);

        $path = $request->file('image')->store('uploads/images', 'public');

        return response()->json([
            'url' => '/storage/' . $path,
        ]);
    }

    public function file(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        $file = $request->file('file');
        $path = $file->store('uploads/kb', 'public');

        return response()->json([
            'url'  => '/storage/' . $path,
            'name' => $file->getClientOriginalName(),
        ]);
    }
}
