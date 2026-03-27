<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsInvitation;
use App\Models\Lms\LmsProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function redirectToGlobalLogin(LmsEvent $event): RedirectResponse
    {
        return redirect()->to('/login?redirect=' . urlencode('/lms/' . $event->slug));
    }

    public function showInvite(LmsEvent $event, string $token): Response|RedirectResponse
    {
        $invitation = LmsInvitation::where('token', $token)
            ->where('lms_event_id', $event->id)
            ->with('role:id,name')
            ->first();

        if (!$invitation || !$invitation->isValid()) {
            return Inertia::render('Lms/Auth/Invite', [
                'event' => $event->only(['id', 'slug', 'title']),
                'invitation' => null,
                'error' => 'Ссылка недействительна или срок её действия истёк.',
            ]);
        }

        return Inertia::render('Lms/Auth/Invite', [
            'event' => $event->only(['id', 'slug', 'title']),
            'invitation' => [
                'token' => $invitation->token,
                'role' => $invitation->role?->name,
                'label' => $invitation->label,
            ],
            'error' => null,
        ]);
    }

    public function registerByInvite(Request $request, LmsEvent $event, string $token): RedirectResponse
    {
        $invitation = LmsInvitation::where('token', $token)
            ->where('lms_event_id', $event->id)
            ->first();

        if (!$invitation || !$invitation->isValid()) {
            return redirect()->route('lms.invite', [$event, $token])
                ->withErrors(['token' => 'Ссылка недействительна или срок её действия истёк.']);
        }

        $validated = $request->validate([
            'last_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'patronymic' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $fullName = trim($validated['last_name'] . ' ' . $validated['first_name']);

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            $user = User::create([
                'name' => $fullName,
                'patronymic' => $validated['patronymic'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => $validated['password'],
            ]);
        }

        LmsProfile::firstOrCreate(
            ['user_id' => $user->id, 'lms_event_id' => $event->id],
            [
                'role' => 'participant',
                'lms_role_id' => $invitation->lms_role_id,
                'phone' => $validated['phone'],
            ]
        );

        $invitation->increment('uses_count');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('lms.dashboard', $event);
    }

    public function showActivate(LmsEvent $event, string $token): Response|RedirectResponse
    {
        $profile = LmsProfile::where('invite_token', $token)
            ->where('lms_event_id', $event->id)
            ->with(['user:id,name,last_name,first_name,patronymic,email,phone', 'lmsRole:id,name'])
            ->first();

        if (!$profile) {
            return Inertia::render('Lms/Auth/Activate', [
                'event' => $event->only(['id', 'slug', 'title']),
                'profile' => null,
                'error' => 'Ссылка недействительна или уже была использована.',
            ]);
        }

        return Inertia::render('Lms/Auth/Activate', [
            'event' => $event->only(['id', 'slug', 'title']),
            'profile' => [
                'last_name' => $profile->user->last_name,
                'first_name' => $profile->user->first_name,
                'patronymic' => $profile->user->patronymic,
                'email' => $profile->user->email,
                'phone' => $profile->phone ?? $profile->user->phone,
                'position' => $profile->position,
                'city' => $profile->city,
                'role' => $profile->lmsRole?->name,
            ],
            'token' => $token,
            'error' => null,
        ]);
    }

    public function activate(Request $request, LmsEvent $event, string $token): RedirectResponse
    {
        $profile = LmsProfile::where('invite_token', $token)
            ->where('lms_event_id', $event->id)
            ->first();

        if (!$profile) {
            return back()->withErrors(['token' => 'Ссылка недействительна или уже была использована.']);
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::findOrFail($profile->user_id);
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $profile->update([
            'status' => 'active',
            'activated_at' => now(),
            'invite_token' => null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('lms.dashboard', $event);
    }

    public function logout(Request $request, LmsEvent $event): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
