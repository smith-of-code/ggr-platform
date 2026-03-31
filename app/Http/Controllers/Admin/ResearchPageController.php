<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResearchPageController extends Controller
{
    private const GROUP = 'research_page';

    private const JSON_KEYS = [
        'tasks',
        'pilot_cities',
        'stats',
        'program_cities',
    ];

    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    public function index(): Response
    {
        $raw = $this->settings->getGroupFresh(self::GROUP);

        $data = [];
        foreach ($raw as $key => $value) {
            $data[$key] = in_array($key, self::JSON_KEYS, true)
                ? json_decode($value, true) ?? []
                : $value;
        }

        $cities = City::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'region', 'image', 'coat_of_arms']);

        return Inertia::render('Admin/ResearchPage/Index', [
            'pageData' => $data,
            'cities' => $cities,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'required|string|max:255',
            'hero_description' => 'required|string|max:2000',

            'tasks_title' => 'required|string|max:255',
            'tasks' => 'required|array|min:1',
            'tasks.*.title' => 'required|string|max:255',
            'tasks.*.text' => 'required|string|max:2000',

            'pilot_cities_title' => 'required|string|max:255',
            'pilot_cities' => 'nullable|array',
            'pilot_cities.*.city_id' => 'required|integer|exists:cities,id',
            'pilot_cities.*.description' => 'nullable|string|max:2000',

            'stats_title' => 'required|string|max:255',
            'stats' => 'required|array|min:1',
            'stats.*.value' => 'required|string|max:50',
            'stats.*.label' => 'required|string|max:500',

            'results_title' => 'required|string|max:255',
            'results_description' => 'required|string|max:2000',
            'results_button_text' => 'nullable|string|max:255',
            'results_button_url' => 'nullable|string|max:2048',
            'results_image' => 'nullable|string|max:2048',

            'program_cities_title' => 'required|string|max:255',
            'program_cities' => 'nullable|array',
            'program_cities.*' => 'integer|exists:cities,id',
        ]);

        $values = [];
        foreach ($validated as $key => $value) {
            $values[$key] = in_array($key, self::JSON_KEYS, true)
                ? json_encode($value, JSON_UNESCAPED_UNICODE)
                : $value;
        }

        $this->settings->setGroup(self::GROUP, $values);

        return redirect()->back()->with('success', 'Страница «Исследования» сохранена');
    }
}
