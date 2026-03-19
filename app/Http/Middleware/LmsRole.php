<?php

namespace App\Http\Middleware;

use App\Models\Lms\LmsProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LmsRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $event = $request->route('event');
        $user = $request->user();

        if (!$event || !$user) {
            abort(403);
        }

        $profile = LmsProfile::where('user_id', $user->id)
            ->where('lms_event_id', $event->id)
            ->first();

        if (!$profile || !in_array($profile->role, $roles)) {
            abort(403, 'Недостаточно прав для доступа к этому разделу.');
        }

        $request->attributes->set('lms_profile', $profile);

        return $next($request);
    }
}
