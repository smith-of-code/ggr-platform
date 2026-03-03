<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(LmsEvent $event): Response
    {
        return Inertia::render('Lms/Auth/Login', [
            'event' => $event->only(['id', 'slug', 'title']),
        ]);
    }

    public function login(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($validated, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('lms.dashboard', $event);
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

    public function showRegister(LmsEvent $event): Response
    {
        return Inertia::render('Lms/Auth/Register', [
            'event' => $event->only(['id', 'slug', 'title']),
        ]);
    }

    public function register(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        LmsProfile::create([
            'user_id' => $user->id,
            'lms_event_id' => $event->id,
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

        return redirect()->route('lms.login', $event);
    }
}
