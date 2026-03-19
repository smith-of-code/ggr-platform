<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $roles = LmsRole::where('lms_event_id', $event->id)
            ->withCount('profiles')
            ->orderBy('name')
            ->get();

        return Inertia::render('Lms/Admin/Roles/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'roles' => $roles,
        ]);
    }

    public function create(LmsEvent $event): Response
    {
        return Inertia::render('Lms/Admin/Roles/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
        ]);
    }

    public function store(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $validated['lms_event_id'] = $event->id;

        LmsRole::create($validated);

        return redirect()->route('lms.admin.roles.index', $event)
            ->with('success', 'Роль создана');
    }

    public function edit(LmsEvent $event, LmsRole $role): Response
    {
        return Inertia::render('Lms/Admin/Roles/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'role' => $role,
        ]);
    }

    public function update(Request $request, LmsEvent $event, LmsRole $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        $role->update($validated);

        return redirect()->route('lms.admin.roles.index', $event)
            ->with('success', 'Роль обновлена');
    }

    public function destroy(LmsEvent $event, LmsRole $role): RedirectResponse
    {
        $role->delete();

        return redirect()->route('lms.admin.roles.index', $event)
            ->with('success', 'Роль удалена');
    }
}
