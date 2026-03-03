<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGroup;
use App\Models\Lms\LmsVideo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        ]);
    }

    private function ensureVideoBelongsToEvent(LmsVideo $video, LmsEvent $event): void
    {
        if ($video->lms_event_id !== $event->id) {
            abort(404);
        }
    }
}
