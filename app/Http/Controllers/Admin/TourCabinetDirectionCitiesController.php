<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\TourCabinetDirectionCity;
use App\Services\Admin\TourCabinetHubPageData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TourCabinetDirectionCitiesController extends Controller
{
    public function __construct(
        private readonly TourCabinetHubPageData $hubPageData,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render(
            'Admin/TourCabinet/DirectionCities/Index',
            $this->hubPageData->directionCitiesPayloadFromRequest($request),
        );
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
                ->route('admin.tour-cabinet.index', ['project_key' => $validated['project_key']])
                ->withFragment('tour-cabinet-admin-cities')
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
            ->route('admin.tour-cabinet.index', ['project_key' => $validated['project_key']])
            ->withFragment('tour-cabinet-admin-cities')
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
            ->route('admin.tour-cabinet.index', ['project_key' => $directionCity->project_key])
            ->withFragment('tour-cabinet-admin-cities')
            ->with('success', 'Запись обновлена.');
    }

    public function destroy(TourCabinetDirectionCity $directionCity): RedirectResponse
    {
        $pk = $directionCity->project_key;
        $directionCity->delete();

        return redirect()
            ->route('admin.tour-cabinet.index', ['project_key' => $pk])
            ->withFragment('tour-cabinet-admin-cities')
            ->with('success', 'Город убран из направления.');
    }
}
