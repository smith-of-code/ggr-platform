<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request, LmsEvent $event): Response
    {
        $query = $event->profiles()->with('user:id,name,email');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('group')) {
            $query->whereIn('user_id', function ($q) use ($request) {
                $q->select('user_id')
                    ->from('lms_group_members')
                    ->where('lms_group_id', $request->group);
            });
        }

        $profiles = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $groups = $event->groups()->orderBy('title')->get(['id', 'title']);

        return Inertia::render('Lms/Admin/Users/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'profiles' => $profiles,
            'groups' => $groups,
            'filters' => $request->only(['role', 'group']),
        ]);
    }

    public function show(LmsEvent $event, LmsProfile $profile): Response
    {
        $this->ensureProfileBelongsToEvent($profile, $event);

        $profile->load('user:id,name,email,created_at');

        return Inertia::render('Lms/Admin/Users/Show', [
            'event' => $event->only(['id', 'slug', 'title']),
            'profile' => $profile,
        ]);
    }

    public function update(Request $request, LmsEvent $event, LmsProfile $profile): RedirectResponse
    {
        $this->ensureProfileBelongsToEvent($profile, $event);

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:participant,curator,admin'],
            'position' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
        ]);

        $profile->update($validated);

        return redirect()->back()->with('success', 'Профиль обновлён');
    }

    public function destroy(LmsEvent $event, LmsProfile $profile): RedirectResponse
    {
        $this->ensureProfileBelongsToEvent($profile, $event);

        $profile->delete();

        return redirect()->route('lms.admin.users.index', $event)->with('success', 'Профиль удалён из события');
    }

    private function ensureProfileBelongsToEvent(LmsProfile $profile, LmsEvent $event): void
    {
        if ($profile->lms_event_id !== $event->id) {
            abort(404);
        }
    }
}
