<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(LmsEvent $event): Response
    {
        $user = auth()->user();
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrCreate(
                ['user_id' => $user->id, 'lms_event_id' => $event->id],
                []
            );

        $socialAccounts = $user->socialAccounts()
            ->get(['provider', 'created_at'])
            ->keyBy('provider');

        return Inertia::render('Lms/Profile/Edit', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'profile' => $profile,
            'user' => $user->only(['name', 'email']),
            'socialAccounts' => $socialAccounts,
        ]);
    }

    public function update(Request $request, LmsEvent $event): RedirectResponse
    {
        $user = auth()->user();
        $validated = $request->validate([
            'position' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $profile->update(array_filter($validated));

        return redirect()->back();
    }
}
