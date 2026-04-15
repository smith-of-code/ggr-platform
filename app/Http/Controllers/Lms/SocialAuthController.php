<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use App\Models\SocialAccount;
use App\Services\GamificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const ALLOWED_PROVIDERS = ['vkontakte', 'yandex'];

    public function redirectToLogin(LmsEvent $event, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::ALLOWED_PROVIDERS), 404);

        session([
            'social_flow' => 'login',
            'social_event_slug' => $event->slug,
        ]);

        return $this->buildDriver($provider)->redirect();
    }

    public function redirectToGlobalLogin(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::ALLOWED_PROVIDERS), 404);

        $portal = $request->query('portal', 'client');
        if (! in_array($portal, ['client', 'student'], true)) {
            $portal = 'client';
        }

        session([
            'social_flow' => 'login_global',
            'social_event_slug' => null,
            'login_portal' => $portal,
        ]);

        return $this->buildDriver($provider)->redirect();
    }

    public function redirectToLink(LmsEvent $event, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::ALLOWED_PROVIDERS), 404);

        session([
            'social_flow' => 'link',
            'social_event_slug' => $event->slug,
        ]);

        return $this->buildDriver($provider)->redirect();
    }

    private function buildDriver(string $provider)
    {
        return Socialite::driver($provider);
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::ALLOWED_PROVIDERS), 404);

        $flow = session()->pull('social_flow', 'login');
        $eventSlug = session()->pull('social_event_slug');

        if ($flow !== 'login_global' && !$eventSlug) {
            return redirect()->route('login')->withErrors(['social' => 'Сессия истекла. Попробуйте ещё раз.']);
        }

        $event = $eventSlug ? LmsEvent::where('slug', $eventSlug)->firstOrFail() : null;

        try {
            $socialUser = $this->buildDriver($provider)->user();
        } catch (\Exception) {
            if ($flow === 'link') {
                return redirect()->route('lms.profile.edit', $event)->withErrors(['social' => 'Не удалось получить данные от провайдера.']);
            }
            return redirect()->route('login')->withErrors(['social' => 'Не удалось получить данные от провайдера.']);
        }

        if ($flow === 'link') {
            return $this->handleLink($event, $provider, $socialUser);
        }

        if ($flow === 'login_global') {
            return $this->handleGlobalLogin($provider, $socialUser);
        }

        return $this->handleLogin($event, $provider, $socialUser);
    }

    public function unlink(LmsEvent $event, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::ALLOWED_PROVIDERS), 404);

        Auth::user()->socialAccounts()
            ->where('provider', $provider)
            ->delete();

        return redirect()->route('lms.profile.edit', $event);
    }

    private function handleLink(LmsEvent $event, string $provider, $socialUser): RedirectResponse
    {
        $existing = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($existing && $existing->user_id !== Auth::id()) {
            return redirect()->route('lms.profile.edit', $event)
                ->withErrors(['social' => 'Этот аккаунт уже привязан к другому пользователю.']);
        }

        Auth::user()->socialAccounts()->updateOrCreate(
            ['provider' => $provider],
            [
                'provider_id' => $socialUser->getId(),
                'token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'expires_at' => $socialUser->expiresIn ? now()->addSeconds($socialUser->expiresIn) : null,
            ]
        );

        return redirect()->route('lms.profile.edit', $event);
    }

    private function handleGlobalLogin(string $provider, $socialUser): RedirectResponse
    {
        $account = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if (!$account) {
            return redirect()->route('login')
                ->withErrors(['social' => 'Аккаунт не привязан. Войдите по email и привяжите аккаунт в профиле.']);
        }

        $account->update([
            'token' => $socialUser->token,
            'refresh_token' => $socialUser->refreshToken,
            'expires_at' => $socialUser->expiresIn ? now()->addSeconds($socialUser->expiresIn) : null,
        ]);

        $user = $account->user;
        Auth::login($user, remember: true);
        request()->session()->regenerate();

        $portal = session()->pull('login_portal', 'client');
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
                request()->session()->invalidate();
                request()->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['social' => 'Вход как студент недоступен: нет записи в образовательной программе.']);
            }

            if ($lmsProfile->role !== 'admin') {
                app(GamificationService::class)->awardPoints($event, $user, 'login_daily', 'Ежедневный вход');
            }

            return redirect()->intended(route('lms.dashboard', ['event' => $event->slug], false));
        }

        if ($user->is_admin) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        if ($user->is_tour_cabinet_user) {
            return redirect()->intended(route('tour-cabinet.dashboard', absolute: false));
        }

        return redirect()->intended(route('profile.edit', absolute: false));
    }

    private function handleLogin(LmsEvent $event, string $provider, $socialUser): RedirectResponse
    {
        $account = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if (!$account) {
            return redirect()->route('login')
                ->withErrors(['social' => 'Аккаунт не привязан. Войдите по email и привяжите аккаунт в профиле.']);
        }

        $account->update([
            'token' => $socialUser->token,
            'refresh_token' => $socialUser->refreshToken,
            'expires_at' => $socialUser->expiresIn ? now()->addSeconds($socialUser->expiresIn) : null,
        ]);

        Auth::login($account->user, remember: true);
        request()->session()->regenerate();

        app(GamificationService::class)->awardPoints($event, $account->user, 'login_daily', 'Ежедневный вход');

        return redirect()->route('lms.dashboard', $event);
    }
}
