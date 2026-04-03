<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('_activity_log_start', microtime(true));

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $startTime = $request->attributes->get('_activity_log_start', microtime(true));

        $this->activityLogService->logActivity($request, $response, $startTime);
    }
}
