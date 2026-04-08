<?php

use App\Http\Middleware\CheckPageVisibility;
use App\Http\Middleware\EnsureLmsBackofficeAccess;
use App\Http\Middleware\EnsurePortalAdmin;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\LmsRole;
use App\Http\Middleware\LogUserActivity;
use App\Http\Middleware\SetContentLanguage;
use App\Services\ActivityLogService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/lms.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetContentLanguage::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            CheckPageVisibility::class,
            LogUserActivity::class,
        ]);

        $middleware->alias([
            'lms.role' => LmsRole::class,
            'lms.backoffice' => EnsureLmsBackofficeAccess::class,
            'portal.admin' => EnsurePortalAdmin::class,
        ]);

        // Доверяем всем прокси (т.к. Nginx на хосте + Nginx в контейнере,
        // да и IPшники в докере прыгают)
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (Throwable $e) {
            if ($e instanceof ValidationException && ! config('activity-logging.log_422')) {
                return;
            }

            $request = request();

            app(ActivityLogService::class)->logException($e, $request);
        });
    })->create();
