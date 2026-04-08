<?php

namespace App\Http\Middleware;

use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLmsBackofficeAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $event = $request->route('event');

        if ($event instanceof LmsEvent) {
            if (! LmsProfile::userIsLmsAdminForEvent($user, $event)) {
                abort(403, 'Недостаточно прав для доступа к администрированию LMS.');
            }

            return $next($request);
        }

        if (! LmsProfile::userHasAnyLmsAdminProfile($user)) {
            abort(403, 'Недостаточно прав для доступа к администрированию LMS.');
        }

        return $next($request);
    }
}
