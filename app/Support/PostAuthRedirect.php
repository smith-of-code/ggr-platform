<?php

namespace App\Support;

use App\Models\Lms\LmsProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Куда вести пользователя после входа на портал и ссылки «ЛК» в шапке.
 */
final class PostAuthRedirect
{
    /** После входа: «клиент» → ЛК туров в приоритете; «студент» → ВШГР (LMS). */
    public const LOGIN_PORTAL_SESSION_KEY = 'auth.portal';

    /**
     * После входа в режиме «Я студент»: session url.intended часто указывает на ЛК туров (/tour-cabinet).
     * Для студента учитываем intended только если это путь внутри LMS (/lms/...), иначе — дефолтный URL профиля.
     */
    public static function studentLoginTargetUrl(string $defaultLmsProfileUrl): string
    {
        $intended = Session::pull('url.intended');
        if (! is_string($intended) || $intended === '') {
            return $defaultLmsProfileUrl;
        }

        $path = parse_url($intended, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            $path = str_starts_with($intended, '/') ? $intended : '';
        }
        if ($path === '' || ! str_starts_with($path, '/lms')) {
            return $defaultLmsProfileUrl;
        }

        $query = parse_url($intended, PHP_URL_QUERY);
        $fragment = parse_url($intended, PHP_URL_FRAGMENT);
        $target = $path.($query ? '?'.$query : '').($fragment ? '#'.$fragment : '');

        return $target;
    }

    public static function lmsProfileUrlForUser(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        $profile = LmsProfile::query()
            ->where('user_id', $user->id)
            ->with('event:id,slug')
            ->orderByDesc('activated_at')
            ->orderByDesc('id')
            ->first();

        if (! $profile?->event?->slug) {
            return null;
        }

        return route('lms.profile.edit', ['event' => $profile->event->slug], false);
    }

    public static function canAccessTourCabinet(?User $user): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->is_tour_cabinet_user) {
            return true;
        }

        return LmsProfile::query()->where('user_id', $user->id)->exists();
    }

    public static function tourCabinetDashboardUrl(?User $user): ?string
    {
        return self::canAccessTourCabinet($user)
            ? route('tour-cabinet.dashboard', absolute: false)
            : null;
    }

    /**
     * Абсолютный URL ЛК туров на портале (main), если задан PORTAL_PUBLIC_URL / tour_cabinet.portal_public_url.
     */
    public static function tourCabinetPortalAbsoluteUrl(): ?string
    {
        $base = rtrim((string) config('tour_cabinet.portal_public_url', ''), '/');
        if ($base === '') {
            return null;
        }

        return $base.route('tour-cabinet.dashboard', absolute: false);
    }

    /**
     * Режим «Я клиент», пользователь не админ: URL по умолчанию для intended().
     * Сначала ЛК туров (если есть доступ), иначе LMS, иначе главная.
     */
    public static function clientPortalDefaultUrl(User $user): string
    {
        if (self::canAccessTourCabinet($user)) {
            return route('tour-cabinet.dashboard', absolute: false);
        }

        $lms = self::lmsProfileUrlForUser($user);
        if ($lms) {
            return $lms;
        }

        if ($user->is_tour_cabinet_user) {
            return route('tour-cabinet.dashboard', absolute: false);
        }

        return route('home', absolute: false);
    }

    public static function rememberLoginPortal(Request $request, string $portal): void
    {
        if (! in_array($portal, ['client', 'student'], true)) {
            $portal = 'client';
        }
        $request->session()->put(self::LOGIN_PORTAL_SESSION_KEY, $portal);
    }
}
