<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Services\SettingsService;
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
        $raw = $this->settings->getGroup(self::GROUP);

        $pageData = [];
        foreach ($raw as $key => $value) {
            $pageData[$key] = in_array($key, self::JSON_KEYS, true)
                ? json_decode($value, true) ?? []
                : $value;
        }

        $cityFields = ['id', 'name', 'slug', 'region', 'description', 'image', 'coat_of_arms'];

        // Pilot cities: [{city_id, description}, ...] -> merge City data with custom description
        $pilotRaw = collect($pageData['pilot_cities'] ?? []);
        $pilotIds = $pilotRaw
            ->filter(fn ($v) => is_array($v) && isset($v['city_id']))
            ->pluck('city_id')
            ->map(fn ($v) => (int) $v);

        if ($pilotIds->isNotEmpty()) {
            $cities = City::whereIn('id', $pilotIds)->where('is_active', true)->get($cityFields)->keyBy('id');
            $descMap = $pilotRaw->filter(fn ($v) => is_array($v) && isset($v['city_id']))
                ->keyBy(fn ($v) => (int) $v['city_id'])
                ->map(fn ($v) => $v['description'] ?? null);

            $pageData['pilot_cities'] = $pilotIds->map(function ($id) use ($cities, $descMap) {
                $city = $cities->get($id);
                if (! $city) return null;
                $arr = $city->toArray();
                $custom = $descMap->get($id);
                if ($custom) {
                    $arr['description'] = $custom;
                }
                return $arr;
            })->filter()->values()->toArray();
        } else {
            $pageData['pilot_cities'] = [];
        }

        // Program cities: [id, id, ...] -> resolve to City objects
        $programRaw = collect($pageData['program_cities'] ?? []);
        $programIds = $programRaw->filter(fn ($v) => is_int($v) || is_numeric($v))->map(fn ($v) => (int) $v)->values();

        if ($programIds->isNotEmpty()) {
            $cities = City::whereIn('id', $programIds)->where('is_active', true)->get($cityFields)->keyBy('id');
            $pageData['program_cities'] = $programIds->map(fn ($id) => $cities->get($id))->filter()->values()->toArray();
        } else {
            $pageData['program_cities'] = [];
        }

        return Inertia::render('Research/Index', [
            'pageData' => $pageData,
        ]);
    }
}
