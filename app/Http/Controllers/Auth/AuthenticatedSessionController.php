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
use Illuminate\Validation\ValidationException;
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

        $qp = $request->query('portal');
        $defaultPortal = $qp === 'student' ? 'student' : 'client';

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'defaultPortal' => $defaultPortal,
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
        $portal = $request->input('portal', 'client');
        if (! in_array($portal, ['client', 'student'], true)) {
            $portal = 'client';
        }

        if ($portal === 'student') {
            $lmsProfile = LmsProfile::query()
                ->where('user_id', $user->id)
                ->with('event:id,slug,title')
                ->orderByDesc('activated_at')
                ->orderByDesc('id')
                ->first();

            $event = $lmsProfile?->event;
            if (! $event) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => 'Вход как студент недоступен: для этого аккаунта нет записи в образовательной программе.',
                ]);
            }

            if ($lmsProfile->role !== 'admin') {
                app(GamificationService::class)->awardPoints($event, $user, 'login_daily', 'Ежедневный вход');
            }

            $redirect = redirect()->intended(route('lms.dashboard', ['event' => $event->slug], false));

            return $this->redirectForInertia($request, $redirect);
        }

        // Режим «Я клиент»: не отправляем в LMS автоматически
        if ($user->is_admin) {
            $redirect = redirect()->intended(route('admin.dashboard', absolute: false));

            return $this->redirectForInertia($request, $redirect);
        }

        if ($user->is_tour_cabinet_user) {
            $redirect = redirect()->intended(route('tour-cabinet.dashboard', absolute: false));

            return $this->redirectForInertia($request, $redirect);
        }

        $redirect = redirect()->intended(route('profile.edit', absolute: false));

        return $this->redirectForInertia($request, $redirect);
    }

    private function redirectForInertia(Request $request, RedirectResponse $redirect): RedirectResponse|HttpResponse
    {
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
