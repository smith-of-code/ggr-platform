<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\VshgrPageContent;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VshgrPageController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    public function index(): Response
    {
        $raw = $this->settings->getGroupFresh(VshgrPageContent::GROUP);
        $pageData = VshgrPageContent::mergeFromStored($raw);

        return Inertia::render('Admin/VshgrPage/Index', [
            'pageData' => $pageData,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero_eyebrow' => 'required|string|max:120',
            'hero_title' => 'required|string|max:500',
            'hero_description' => 'required|string|max:3000',

            'catalog_title' => 'required|string|max:255',
            'catalog_subtitle' => 'required|string|max:500',
            'catalog_empty_text' => 'required|string|max:500',

            'announcements_title' => 'required|string|max:255',
            'announcements_subtitle' => 'required|string|max:500',

            'cta_title' => 'required|string|max:500',
            'cta_body' => 'required|string|max:2000',
            'cta_button_label' => 'required|string|max:120',

            'regulation_url' => 'required|url|max:2000',
            'regulation_button_label' => 'required|string|max:120',
            'regulation_caption' => 'required|string|max:1000',

            'form_title' => 'required|string|max:255',
            'form_subtitle' => 'required|string|max:500',

            'socials_title' => 'required|string|max:255',
            'socials_subtitle' => 'required|string|max:500',

            'socials' => 'present|array',
            'socials.*.name' => 'required|string|max:255',
            'socials.*.url' => 'required|url|max:500',
            'socials.*.icon' => 'required|string|max:50',
        ]);

        $values = [];
        foreach ($validated as $key => $value) {
            $values[$key] = in_array($key, VshgrPageContent::JSON_KEYS, true)
                ? json_encode($value, JSON_UNESCAPED_UNICODE)
                : $value;
        }

        $this->settings->setGroup(VshgrPageContent::GROUP, $values);

        return redirect()->back()->with('success', 'Страница «ВШГР» сохранена');
    }
}
