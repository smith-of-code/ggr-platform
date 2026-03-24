<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGroup;
use App\Models\Lms\LmsVideo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $videos = $event->videos()->with('groups')->orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('Lms/Admin/Videos/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'videos' => $videos,
        ]);
    }

    public function create(LmsEvent $event): Response
    {
        $groups = $event->groups()->orderBy('title')->get(['id', 'title']);

        return Inertia::render('Lms/Admin/Videos/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'video' => null,
            'groups' => $groups,
        ]);
    }

    public function store(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $this->validateVideo($request);

        $validated['lms_event_id'] = $event->id;
        $validated['is_recording'] = $request->boolean('is_recording', false);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['source'] = $validated['source'] ?? $this->detectSource($validated['url'] ?? null);

        $validated['thumbnail'] = $this->resolveThumbnail($request, $validated['url'] ?? null);

        $video = LmsVideo::create($validated);

        if ($request->filled('group_ids')) {
            $video->groups()->sync($request->group_ids);
        }

        return redirect()->route('lms.admin.videos.index', $event)->with('success', 'Видео создано');
    }

    public function edit(LmsEvent $event, LmsVideo $video): Response
    {
        $this->ensureVideoBelongsToEvent($video, $event);

        $video->load('groups');
        $groups = $event->groups()->orderBy('title')->get(['id', 'title']);

        return Inertia::render('Lms/Admin/Videos/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'video' => $video,
            'groups' => $groups,
        ]);
    }

    public function update(Request $request, LmsEvent $event, LmsVideo $video): RedirectResponse
    {
        $this->ensureVideoBelongsToEvent($video, $event);

        $validated = $this->validateVideo($request);

        $validated['is_recording'] = $request->boolean('is_recording', false);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['source'] = $validated['source'] ?? $this->detectSource($validated['url'] ?? null);

        $validated['thumbnail'] = $this->resolveThumbnail($request, $validated['url'] ?? null, $video->thumbnail);

        $video->update($validated);

        $video->groups()->sync($request->group_ids ?? []);

        return redirect()->route('lms.admin.videos.index', $event)->with('success', 'Видео обновлено');
    }

    public function destroy(LmsEvent $event, LmsVideo $video): RedirectResponse
    {
        $this->ensureVideoBelongsToEvent($video, $event);

        $video->delete();

        return redirect()->route('lms.admin.videos.index', $event)->with('success', 'Видео удалено');
    }

    private function validateVideo(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'source' => ['nullable', 'string'],
            'url' => ['nullable', 'string', 'url'],
            'file_path' => ['nullable', 'string'],
            'duration_seconds' => ['nullable', 'integer', 'min:1'],
            'thumbnail_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_thumbnail' => ['nullable', 'boolean'],
        ]);
    }

    private function resolveThumbnail(Request $request, ?string $url, ?string $existing = null): ?string
    {
        if ($request->boolean('remove_thumbnail')) {
            return null;
        }

        if ($request->hasFile('thumbnail_file')) {
            $disk = config('filesystems.upload_disk');
            $path = $request->file('thumbnail_file')->store('thumbnails/videos', $disk);
            return Storage::disk($disk)->url($path);
        }

        if ($existing) {
            return $existing;
        }

        if ($url) {
            return $this->fetchThumbnail($url);
        }

        return null;
    }

    private function fetchThumbnail(string $url): ?string
    {
        // Rutube
        if (preg_match('/rutube\.ru\/video\/([a-zA-Z0-9]+)/', $url, $m)) {
            try {
                $resp = Http::timeout(5)->get("https://rutube.ru/api/video/{$m[1]}/");
                if ($resp->ok()) {
                    return $resp->json('thumbnail_url');
                }
            } catch (\Throwable) {}
        }

        // YouTube
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $url, $m)) {
            return "https://img.youtube.com/vi/{$m[1]}/hqdefault.jpg";
        }

        return null;
    }

    private function detectSource(?string $url): string
    {
        if (!$url) {
            return 'upload';
        }
        if (preg_match('/rutube\.ru/', $url)) {
            return 'rutube';
        }
        return 'link';
    }

    private function ensureVideoBelongsToEvent(LmsVideo $video, LmsEvent $event): void
    {
        if ($video->lms_event_id !== $event->id) {
            abort(404);
        }
    }
}
