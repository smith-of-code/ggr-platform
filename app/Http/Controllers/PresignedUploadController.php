<?php

namespace App\Http\Controllers;

use App\Services\PresignedUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresignedUploadController extends Controller
{
    public function __construct(
        private readonly PresignedUploadService $service,
    ) {}

    public function presignedUrl(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'filename' => ['required', 'string', 'max:255'],
            'content_type' => ['required', 'string', 'max:127'],
            'size' => ['required', 'integer', 'min:1'],
            'directory' => ['nullable', 'string', 'max:255'],
            'collection' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $result = $this->service->generatePresignedUrl(
                filename: $validated['filename'],
                contentType: $validated['content_type'],
                size: $validated['size'],
                directory: $validated['directory'] ?? null,
                collection: $validated['collection'] ?? null,
            );

            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function confirm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:500'],
            'original_name' => ['required', 'string', 'max:255'],
            'content_type' => ['required', 'string', 'max:127'],
            'size' => ['required', 'integer', 'min:1'],
            'collection' => ['nullable', 'string', 'max:255'],
            'entity_type' => ['nullable', 'string', 'max:255'],
            'entity_id' => ['nullable'],
        ]);

        try {
            $result = $this->service->confirm(
                key: $validated['key'],
                originalName: $validated['original_name'],
                contentType: $validated['content_type'],
                size: $validated['size'],
                collection: $validated['collection'] ?? null,
                entityType: $validated['entity_type'] ?? null,
                entityId: $validated['entity_id'] ?? null,
            );

            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
