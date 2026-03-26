<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimelineEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class TimelineEventController extends Controller
{
    public function index(): Response
    {
        $events = Schema::hasTable('timeline_events')
            ? TimelineEvent::orderByDesc('event_date')->paginate(20)
            : collect();

        return Inertia::render('Admin/Timeline/Index', [
            'events' => $events,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Timeline/Form', [
            'event' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'link' => 'nullable|url|max:2048',
            'type' => 'required|string|in:news,event,milestone',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        TimelineEvent::create($validated);

        return redirect()->route('admin.timeline.index')->with('success', 'Событие добавлено');
    }

    public function edit(TimelineEvent $timeline): Response
    {
        return Inertia::render('Admin/Timeline/Form', [
            'event' => $timeline,
        ]);
    }

    public function update(Request $request, TimelineEvent $timeline): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'link' => 'nullable|url|max:2048',
            'type' => 'required|string|in:news,event,milestone',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $timeline->update($validated);

        return redirect()->route('admin.timeline.index')->with('success', 'Событие обновлено');
    }

    public function destroy(TimelineEvent $timeline): RedirectResponse
    {
        $timeline->delete();
        return redirect()->route('admin.timeline.index')->with('success', 'Событие удалено');
    }

    public function toggleActive(TimelineEvent $timeline): RedirectResponse
    {
        $timeline->update(['is_active' => !$timeline->is_active]);
        $status = $timeline->is_active ? 'активно' : 'скрыто';
        return redirect()->back()->with('success', "Событие «{$timeline->title}» {$status}");
    }
}
