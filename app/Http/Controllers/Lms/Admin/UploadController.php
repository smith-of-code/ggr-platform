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

    public function mediaIndex(Request $request): JsonResponse
    {
        $collection = $request->input('collection');
        $entityType = $request->input('entity_type');
        $entityId = $request->input('entity_id');
        $scope = $request->input('scope', $entityId ? 'entity' : ($collection ? 'collection' : 'all'));

        $baseQuery = UploadedMedia::latest();
        if ($request->filled('search')) {
            $baseQuery->where('original_name', 'ilike', '%' . $request->search . '%');
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $mimePrefix = match ($request->type) {
                'image' => 'image/',
                'video' => 'video/',
                'document' => 'application/',
                default => null,
            };
            if ($mimePrefix) {
                $baseQuery->where('mime_type', 'like', $mimePrefix . '%');
            }
        }

        $query = clone $baseQuery;
        if ($scope === 'entity' && $entityType && $entityId) {
            $query->where('entity_type', $entityType)->where('entity_id', $entityId);
        } elseif ($scope === 'collection' && $collection) {
            $query->where('collection', $collection);
        }

        $paginated = $query->paginate(24);
        $result = $paginated->toArray();

        $counts = [
            'all' => (clone $baseQuery)->count(),
        ];
        if ($collection) {
            $counts['collection'] = (clone $baseQuery)->where('collection', $collection)->count();
        }
        if ($entityType && $entityId) {
            $counts['entity'] = (clone $baseQuery)->where('entity_type', $entityType)->where('entity_id', $entityId)->count();
        }

        $entityLabel = null;
        if ($entityType && $entityId) {
            try {
                $model = $entityType::find($entityId);
                $entityLabel = $model?->name ?? $model?->title ?? null;
            } catch (\Throwable) {
            }
        }

        $result['counts'] = $counts;
        $result['entity_label'] = $entityLabel;

        return response()->json($result);
    }
}
