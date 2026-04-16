<?php

namespace App\Support;

use App\Models\Lms\LmsProfile;
use App\Models\User;

/**
 * Куда вести пользователя после входа на портал и ссылки «ЛК» в шапке.
 */
final class PostAuthRedirect
{
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
     * Режим «Я клиент», пользователь не админ: URL по умолчанию для intended().
     */
    public static function clientPortalDefaultUrl(User $user): string
    {
        $lms = self::lmsProfileUrlForUser($user);
        if ($lms) {
            return $lms;
        }

        if ($user->is_tour_cabinet_user) {
            return route('tour-cabinet.dashboard', absolute: false);
        }

        return route('home', absolute: false);
    }
}
