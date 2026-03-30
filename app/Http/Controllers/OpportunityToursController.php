<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use App\Models\Tour;
use App\Services\SettingsService;
use Inertia\Inertia;
use Inertia\Response;

class OpportunityToursController extends Controller
{
    private const GROUP = 'opportunity_tours';

    private const JSON_KEYS = [
        'stats', 'emotions', 'partners', 'socials',
        'faq', 'videos', 'participation_steps', 'featured_tour_ids',
        'projects',
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

        $tourIds = $pageData['featured_tour_ids'] ?? [];

        $featuredTours = $tourIds
            ? Tour::whereIn('id', $tourIds)
                ->where('is_active', true)
                ->with(['cities', 'departures'])
                ->orderBy('position')
                ->get()
            : Tour::where('is_featured', true)
                ->where('is_active', true)
                ->with(['cities', 'departures'])
                ->orderBy('position')
                ->limit(8)
                ->get();

        $directions = Direction::where('is_active', true)
            ->orderBy('position')
            ->get(['id', 'title', 'slug', 'description', 'image', 'project_key']);

        return Inertia::render('OpportunityTours/Index', [
            'featuredTours' => $featuredTours,
            'directions' => $directions,
            'pageData' => $pageData,
        ]);
    }
}
