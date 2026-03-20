<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        $events = LmsEvent::orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('Lms/Admin/Events/Index', [
            'events' => $events,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Lms/Admin/Events/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:lms_events,slug'],
            'description' => ['nullable', 'string'],
            'menu_config' => ['nullable', 'array'],
            'menu_config.*' => ['boolean'],
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['auth_method'] = 'email';

        LmsEvent::create($validated);

        return redirect()->route('lms.admin.events.index')->with('success', 'Событие создано');
    }

    public function show(LmsEvent $event): Response
    {
        return Inertia::render('Lms/Admin/Events/Edit', [
            'event' => $event,
        ]);
    }

    public function edit(LmsEvent $event): Response
    {
        return Inertia::render('Lms/Admin/Events/Edit', [
            'event' => $event,
        ]);
    }

    public function update(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:lms_events,slug,' . $event->id],
            'description' => ['nullable', 'string'],
            'menu_config' => ['nullable', 'array'],
            'menu_config.*' => ['boolean'],
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $event->update($validated);

        return redirect()->route('lms.admin.events.index')->with('success', 'Событие обновлено');
    }

    public function destroy(LmsEvent $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('lms.admin.events.index')->with('success', 'Событие удалено');
    }
}
