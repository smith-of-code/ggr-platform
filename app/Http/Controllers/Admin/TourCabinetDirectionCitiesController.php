<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourCabinetDirectionCity;
use App\Services\Admin\TourCabinetHubPageData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $validated = $request->validate([
            'direction_id' => ['required', 'integer', 'exists:directions,id'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'needs_more_data' => ['sometimes', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:99999'],
        ]);

        $exists = TourCabinetDirectionCity::query()
            ->where('direction_id', $validated['direction_id'])
            ->where('city_id', $validated['city_id'])
            ->exists();
        if ($exists) {
            return redirect()
                ->route('admin.tour-cabinet.index', ['direction_id' => $validated['direction_id']])
                ->withFragment('tour-cabinet-admin-cities')
                ->with('error', 'Этот город уже добавлен в направление.');
        }

        $position = $validated['position'] ?? null;
        if ($position === null) {
            $position = (int) TourCabinetDirectionCity::query()
                ->where('direction_id', $validated['direction_id'])
                ->max('position') + 1;
        }

        TourCabinetDirectionCity::query()->create([
            'direction_id' => $validated['direction_id'],
            'city_id' => $validated['city_id'],
            'needs_more_data' => (bool) ($validated['needs_more_data'] ?? false),
            'position' => $position,
        ]);

        return redirect()
            ->route('admin.tour-cabinet.index', ['direction_id' => $validated['direction_id']])
            ->withFragment('tour-cabinet-admin-cities')
            ->with('success', 'Город добавлен в направление.');
    }

    public function update(Request $request, TourCabinetDirectionCity $directionCity): RedirectResponse
    {
        if (! $directionCity->direction_id) {
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
            ->route('admin.tour-cabinet.index', ['direction_id' => $directionCity->direction_id])
            ->withFragment('tour-cabinet-admin-cities')
            ->with('success', 'Запись обновлена.');
    }

    public function destroy(TourCabinetDirectionCity $directionCity): RedirectResponse
    {
        $dirId = $directionCity->direction_id;
        $directionCity->delete();

        return redirect()
            ->route('admin.tour-cabinet.index', ['direction_id' => $dirId])
            ->withFragment('tour-cabinet-admin-cities')
            ->with('success', 'Город убран из направления.');
    }
}
