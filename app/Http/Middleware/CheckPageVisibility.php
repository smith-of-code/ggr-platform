<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckPageVisibility
{
    public function __construct(
        private SettingsService $settings,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            return $next($request);
        }

        $path = '/'.ltrim($request->path(), '/');
        $hiddenPages = $this->settings->getHiddenPages();
        $pages = config('page_visibility.pages', []);

        foreach ($pages as $page) {
            if (! in_array($page['slug'], $hiddenPages, true)) {
                continue;
            }

            $prefix = $page['route_prefix'];
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return Inertia::render('PageUnderConstruction')->toResponse($request);
            }
        }

        return $next($request);
    }
}
