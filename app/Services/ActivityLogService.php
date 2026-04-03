<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\ExceptionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ActivityLogService
{
    public function logActivity(Request $request, Response $response, float $startTime): void
    {
        if (! config('activity-logging.enabled')) {
            return;
        }

        if ($this->isExcludedPath($request)) {
            return;
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode === 422 && ! config('activity-logging.log_422')) {
            return;
        }

        try {
            ActivityLog::create([
                'user_id' => $request->user()?->id,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route_name' => $request->route()?->getName(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status_code' => $statusCode,
                'duration_ms' => (int) ((microtime(true) - $startTime) * 1000),
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed to write activity log', ['error' => $e->getMessage()]);
        }
    }

    public function logException(Throwable $exception, ?Request $request = null): void
    {
        if (! config('activity-logging.enabled') || ! config('activity-logging.log_exceptions')) {
            return;
        }

        try {
            ExceptionLog::create([
                'user_id' => $request?->user()?->id,
                'exception_class' => get_class($exception),
                'message' => mb_substr($exception->getMessage(), 0, 65535),
                'code' => $exception->getCode() ? (string) $exception->getCode() : null,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => mb_substr($exception->getTraceAsString(), 0, 65535),
                'url' => $request?->fullUrl(),
                'method' => $request?->method(),
                'ip_address' => $request?->ip(),
                'status_code' => $this->resolveStatusCode($exception),
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed to write exception log', ['error' => $e->getMessage()]);
        }
    }

    private function isExcludedPath(Request $request): bool
    {
        $path = $request->path();

        foreach (config('activity-logging.excluded_paths', []) as $pattern) {
            if ($path === $pattern || fnmatch($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    private function resolveStatusCode(Throwable $exception): ?int
    {
        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }

        return null;
    }
}
