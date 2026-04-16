<?php

namespace App\Http\Middleware;

use App\Support\PostAuthRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectTourCabinetGuest
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && PostAuthRedirect::canAccessTourCabinet($user)) {
            return redirect()->route('tour-cabinet.dashboard');
        }

        return $next($request);
    }
}
