<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $groups = $event->groups()
            ->withCount('members')
            ->with('curator:id,name')
            ->orderBy('title')
            ->paginate(15);

        return Inertia::render('Lms/Admin/Groups/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'groups' => $groups,
        ]);
    }

    public function create(LmsEvent $event): Response
    {
        $profileUsers = $event->profiles()->with('user:id,name,email')->get()->pluck('user')->filter()->unique('id');
        $users = $profileUsers->isNotEmpty() ? $profileUsers->values() : User::orderBy('name')->get(['id', 'name', 'email']);

        return Inertia::render('Lms/Admin/Groups/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'group' => null,
            'users' => $users,
        ]);
    }

    public function store(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'curator_id' => ['nullable', 'exists:users,id'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $validated['lms_event_id'] = $event->id;

        $group = LmsGroup::create($validated);

        if ($request->filled('user_ids')) {
            $group->members()->sync($request->user_ids);
        }

        return redirect()->route('lms.admin.groups.index', $event)->with('success', 'Группа создана');
    }

    public function edit(LmsEvent $event, LmsGroup $group): Response
    {
        $this->ensureGroupBelongsToEvent($group, $event);

        $group->load('members:id,name,email');
        $profileUsers = $event->profiles()->with('user:id,name,email')->get()->pluck('user')->filter()->unique('id');
        $users = $profileUsers->isNotEmpty() ? $profileUsers->values() : User::orderBy('name')->get(['id', 'name', 'email']);

        return Inertia::render('Lms/Admin/Groups/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'group' => $group,
            'users' => $users,
        ]);
    }

    public function update(Request $request, LmsEvent $event, LmsGroup $group): RedirectResponse
    {
        $this->ensureGroupBelongsToEvent($group, $event);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'curator_id' => ['nullable', 'exists:users,id'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $group->update($validated);

        $group->members()->sync($request->user_ids ?? []);

        return redirect()->route('lms.admin.groups.index', $event)->with('success', 'Группа обновлена');
    }

    public function destroy(LmsEvent $event, LmsGroup $group): RedirectResponse
    {
        $this->ensureGroupBelongsToEvent($group, $event);

        $group->delete();

        return redirect()->route('lms.admin.groups.index', $event)->with('success', 'Группа удалена');
    }

    private function ensureGroupBelongsToEvent(LmsGroup $group, LmsEvent $event): void
    {
        if ($group->lms_event_id !== $event->id) {
            abort(404);
        }
    }
}
