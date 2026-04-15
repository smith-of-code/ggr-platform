<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectTourCabinetGuest
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->is_tour_cabinet_user) {
            return redirect()->route('tour-cabinet.dashboard');
        }

        return $next($request);
    }
}
