<?php

namespace App\Http\Middleware;

use App\Support\PostAuthRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTourCabinetUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->guest(route('tour-cabinet.login'));
        }
        if (! PostAuthRedirect::canAccessTourCabinet($user)) {
            return redirect()
                ->route('home')
                ->with('error', 'Этот раздел доступен только участникам личного кабинета туров и обучения.');
        }

        return $next($request);
    }
}
