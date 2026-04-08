<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Lms\LmsProfile;
use App\Services\GamificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): Response
    {
        if ($redirect = $request->query('redirect')) {
            session(['url.intended' => $redirect]);
        }

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse|HttpResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        $lmsProfile = LmsProfile::where('user_id', $user->id)->first();

        if ($lmsProfile && $lmsProfile->role !== 'admin') {
            $event = $lmsProfile->event;
            if ($event) {
                app(GamificationService::class)->awardPoints($event, $user, 'login_daily', 'Ежедневный вход');
                $redirect = redirect()->intended(route('lms.dashboard', $event->slug, false));
                if ($request->header('X-Inertia')) {
                    return Inertia::location($redirect->getTargetUrl());
                }
                return $redirect;
            }
        }

        $redirect = redirect()->intended(route('admin.dashboard', absolute: false));
        if ($request->header('X-Inertia')) {
            return Inertia::location($redirect->getTargetUrl());
        }
        return $redirect;
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
