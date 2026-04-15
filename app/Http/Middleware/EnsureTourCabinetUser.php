<?php

namespace App\Http\Middleware;

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
        if (! $user->is_tour_cabinet_user) {
            return redirect()
                ->route('home')
                ->with('error', 'Этот раздел доступен только участникам личного кабинета туров.');
        }

        return $next($request);
    }
}
