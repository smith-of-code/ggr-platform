<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Tour;
use App\Models\TourCabinetDirectionCity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TourCabinetDirectionCitiesController extends Controller
{
    public function index(Request $request): Response
    {
        $keys = array_keys(Tour::PROJECTS);
        $projectKey = $request->query('project_key');
        if (! is_string($projectKey) || ! in_array($projectKey, $keys, true)) {
            $projectKey = $keys[0];
        }

        $rows = TourCabinetDirectionCity::query()
            ->where('project_key', $projectKey)
            ->with('city:id,name,slug,is_active')
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $usedCityIds = TourCabinetDirectionCity::query()
            ->where('project_key', $projectKey)
            ->pluck('city_id')
            ->all();

        $cityOptions = City::query()
            ->where('is_active', true)
            ->whereNotIn('id', $usedCityIds)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $directions = collect(Tour::PROJECTS)->map(fn (string $label, string $key) => [
            'key' => $key,
            'label' => $label,
        ])->values()->all();

        return Inertia::render('Admin/TourCabinet/DirectionCities/Index', [
            'directions' => $directions,
            'projectKey' => $projectKey,
            'rows' => $rows,
            'cityOptions' => $cityOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $keys = array_keys(Tour::PROJECTS);
        $validated = $request->validate([
            'project_key' => ['required', 'string', Rule::in($keys)],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'needs_more_data' => ['sometimes', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:99999'],
        ]);

        $exists = TourCabinetDirectionCity::query()
            ->where('project_key', $validated['project_key'])
            ->where('city_id', $validated['city_id'])
            ->exists();
        if ($exists) {
            return redirect()
                ->route('admin.tour-cabinet.direction-cities.index', ['project_key' => $validated['project_key']])
                ->with('error', 'Этот город уже добавлен в направление.');
        }

        $position = $validated['position'] ?? null;
        if ($position === null) {
            $position = (int) TourCabinetDirectionCity::query()
                ->where('project_key', $validated['project_key'])
                ->max('position') + 1;
        }

        TourCabinetDirectionCity::query()->create([
            'project_key' => $validated['project_key'],
            'city_id' => $validated['city_id'],
            'needs_more_data' => (bool) ($validated['needs_more_data'] ?? false),
            'position' => $position,
        ]);

        return redirect()
            ->route('admin.tour-cabinet.direction-cities.index', ['project_key' => $validated['project_key']])
            ->with('success', 'Город добавлен в направление.');
    }

    public function update(Request $request, TourCabinetDirectionCity $directionCity): RedirectResponse
    {
        $keys = array_keys(Tour::PROJECTS);
        if (! in_array($directionCity->project_key, $keys, true)) {
            abort(404);
        }

        $validated = $request->validate([
            'needs_more_data' => ['sometimes', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:99999'],
        ]);

        $data = [];
        if (array_key_exists('needs_more_data', $validated)) {
            $data['needs_more_data'] = (bool) $validated['needs_more_data'];
        }
        if (array_key_exists('position', $validated) && $validated['position'] !== null) {
            $data['position'] = (int) $validated['position'];
        }
        if ($data !== []) {
            $directionCity->update($data);
        }

        return redirect()
            ->route('admin.tour-cabinet.direction-cities.index', ['project_key' => $directionCity->project_key])
            ->with('success', 'Запись обновлена.');
    }

    public function destroy(TourCabinetDirectionCity $directionCity): RedirectResponse
    {
        $pk = $directionCity->project_key;
        $directionCity->delete();

        return redirect()
            ->route('admin.tour-cabinet.direction-cities.index', ['project_key' => $pk])
            ->with('success', 'Город убран из направления.');
    }
}
