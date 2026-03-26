<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Tour;
use App\Models\TourDeparture;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TourController extends Controller
{
    public function index(): Response
    {
        $tours = Tour::with('cities')->orderBy('position')->orderBy('title')->paginate(15);

        return Inertia::render('Admin/Tours/Index', [
            'tours' => $tours,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Tours/Form', [
            'cities' => City::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTour($request);
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);
        $validated['bchp_participant'] = $request->boolean('bchp_participant', false);
        $validated['for_children'] = $request->boolean('for_children', false);
        $validated['for_foreigners'] = $request->boolean('for_foreigners', false);
        $validated['closed_city'] = $request->boolean('closed_city', false);

        $tour = Tour::create($validated);

        if ($request->filled('city_ids')) {
            $tour->cities()->sync($request->city_ids);
        }

        $this->syncDepartures($tour, $request->departures ?? []);

        return redirect()->route('admin.tours.index')->with('success', 'Тур создан');
    }

    public function edit(Tour $tour): Response
    {
        $tour->load(['cities', 'departures']);

        return Inertia::render('Admin/Tours/Form', [
            'tour' => $tour,
            'cities' => City::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Tour $tour): RedirectResponse
    {
        $validated = $this->validateTour($request);
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);
        $validated['bchp_participant'] = $request->boolean('bchp_participant', false);
        $validated['for_children'] = $request->boolean('for_children', false);
        $validated['for_foreigners'] = $request->boolean('for_foreigners', false);
        $validated['closed_city'] = $request->boolean('closed_city', false);

        $tour->update($validated);

        $tour->cities()->sync($request->city_ids ?? []);

        $this->syncDepartures($tour, $request->departures ?? []);

        return redirect()->route('admin.tours.index')->with('success', 'Тур обновлён');
    }

    public function destroy(Tour $tour): RedirectResponse
    {
        $tour->delete();

        return redirect()->route('admin.tours.index')->with('success', 'Тур удалён');
    }

    public function toggleActive(Tour $tour): RedirectResponse
    {
        $tour->update(['is_active' => !$tour->is_active]);

        return redirect()->back()->with('success', $tour->is_active ? 'Тур активирован' : 'Тур скрыт');
    }

    private function validateTour(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'start_city' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:100',
            'project' => 'nullable|string|in:start_atomgrad,atoms_vkusa,llr',
            'participation_type' => 'nullable|string|in:contest,paid',
            'season' => 'nullable|string|in:winter,spring,summer,autumn',
            'group_size' => 'nullable|string|max:100',
            'min_age' => 'nullable|integer|min:0',
            'price_from' => 'nullable|numeric|min:0',
            'departure_info' => 'nullable|string',
            'accommodation_info' => 'nullable|string',
            'conditions' => 'nullable|string',
            'cost_info' => 'nullable|string',
            'application_info' => 'nullable|string',
            'position' => 'nullable|integer',
            'image' => 'nullable|string',
            'gallery' => 'nullable|array',
            'gallery.*' => 'string',
            'video_url' => 'nullable|string|max:500',
        ]);
    }

    private function syncDepartures(Tour $tour, array $departures): void
    {
        $tour->departures()->delete();

        foreach ($departures as $d) {
            if (!empty($d['start_date']) && !empty($d['end_date']) && isset($d['price_per_person'])) {
                TourDeparture::create([
                    'tour_id' => $tour->id,
                    'start_date' => $d['start_date'],
                    'end_date' => $d['end_date'],
                    'price_per_person' => $d['price_per_person'],
                ]);
            }
        }
    }
}
