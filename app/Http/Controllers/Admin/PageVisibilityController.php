<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageVisibilityController extends Controller
{
    public function __construct(
        private SettingsService $settings,
    ) {}

    public function index(): Response
    {
        $pages = config('page_visibility.pages', []);
        $hiddenPages = $this->settings->getHiddenPages();

        $items = array_map(fn (array $page) => [
            ...$page,
            'hidden' => in_array($page['slug'], $hiddenPages, true),
        ], $pages);

        return Inertia::render('Admin/Settings/PageVisibility', [
            'pages' => $items,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'pages' => ['required', 'array'],
            'pages.*.slug' => ['required', 'string'],
            'pages.*.hidden' => ['required', 'boolean'],
        ]);

        $visibility = [];
        foreach ($request->input('pages') as $page) {
            $visibility[$page['slug']] = $page['hidden'];
        }

        $this->settings->setPageVisibility($visibility);

        return redirect()->route('admin.settings.page-visibility')
            ->with('success', 'Настройки видимости сохранены');
    }
}
