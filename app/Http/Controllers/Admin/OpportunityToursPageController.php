<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use App\Models\Tour;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OpportunityToursPageController extends Controller
{
    private const GROUP = 'opportunity_tours';

    private const JSON_KEYS = [
        'stats',
        'emotions',
        'partners',
        'socials',
        'faq',
        'videos',
        'participation_steps',
        'featured_tour_ids',
        'projects',
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

        $allTours = Tour::where('is_active', true)
            ->orderBy('position')
            ->get(['id', 'title', 'slug', 'start_city']);

        $activeTourIds = $allTours->pluck('id')->all();
        if (! empty($data['featured_tour_ids'])) {
            $data['featured_tour_ids'] = array_values(
                array_filter($data['featured_tour_ids'], fn ($id) => in_array((int) $id, $activeTourIds, true))
            );
        }

        $allDirections = Direction::where('is_active', true)
            ->orderBy('position')
            ->get(['id', 'title', 'slug', 'description', 'image']);

        return Inertia::render('Admin/OpportunityToursPage/Index', [
            'pageData' => $data,
            'allTours' => $allTours,
            'allDirections' => $allDirections,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero_title' => 'required|string|max:255',
            'hero_description' => 'required|string|max:1000',

            'stats' => 'required|array|min:1',
            'stats.*.value' => 'required|string|max:50',
            'stats.*.label' => 'required|string|max:255',

            'emotions' => 'required|array|min:1',
            'emotions.*.icon' => 'required|string|max:50',
            'emotions.*.count' => 'required|string|max:50',
            'emotions.*.label' => 'required|string|max:255',

            'partners' => 'required|array|min:1',
            'partners.*.name' => 'required|string|max:255',
            'partners.*.url' => 'nullable|url|max:500',
            'partners.*.logo' => 'nullable|string|max:500',

            'socials' => 'required|array|min:1',
            'socials.*.name' => 'required|string|max:255',
            'socials.*.url' => 'required|url|max:500',
            'socials.*.icon' => 'required|string|max:50',

            'faq' => 'required|array|min:1',
            'faq.*.question' => 'required|string|max:500',
            'faq.*.answer' => 'required|string|max:5000',

            'videos' => 'nullable|array',
            'videos.*.title' => 'required|string|max:255',
            'videos.*.thumbnail' => 'nullable|string|max:500',
            'videos.*.sourceType' => 'nullable|string|in:embed,file',
            'videos.*.embedUrl' => 'nullable|string|max:500',
            'videos.*.videoFile' => 'nullable|string|max:500',

            'participation_steps' => 'required|array|min:1',
            'participation_steps.*.title' => 'required|string|max:255',
            'participation_steps.*.description' => 'required|string|max:2000',

            'projects' => 'nullable|array',
            'projects.*.type' => 'required|string|in:direction,custom',
            'projects.*.direction_id' => 'nullable|integer|exists:directions,id',
            'projects.*.title' => 'nullable|string|max:255',
            'projects.*.description' => 'nullable|string|max:2000',
            'projects.*.image' => 'nullable|string|max:500',
            'projects.*.link' => 'nullable|string|max:500',

            'featured_tour_ids' => 'nullable|array',
            'featured_tour_ids.*' => 'integer',
        ]);

        if (! empty($validated['featured_tour_ids'])) {
            $existingIds = Tour::whereIn('id', $validated['featured_tour_ids'])->pluck('id')->all();
            $validated['featured_tour_ids'] = array_values(
                array_filter($validated['featured_tour_ids'], fn ($id) => in_array($id, $existingIds, true))
            );
        }

        $values = [];
        foreach ($validated as $key => $value) {
            $values[$key] = in_array($key, self::JSON_KEYS, true)
                ? json_encode($value, JSON_UNESCAPED_UNICODE)
                : $value;
        }

        $this->settings->setGroup(self::GROUP, $values);

        return redirect()->back()->with('success', 'Страница «Туры возможностей» сохранена');
    }
}
