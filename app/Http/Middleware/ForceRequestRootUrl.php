<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Чтобы route(), redirect, Ziggy (@routes) и подписанные URL текущего запроса
 * не уезжали на APP_URL другого хоста при одном деплое на main + lms.
 */
final class ForceRequestRootUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isSecure()) {
            URL::forceScheme('https');
        }

        URL::forceRootUrl($request->getSchemeAndHttpHost());

        return $next($request);
    }
}
