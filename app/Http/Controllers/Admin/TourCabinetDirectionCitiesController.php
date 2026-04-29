<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsForm;
use App\Models\TourCabinetContestCitySubmission;
use App\Models\TourCabinetDirectionCity;
use App\Services\Admin\TourCabinetHubPageData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
            'lms_form_slug' => ['nullable', 'string', 'max:191'],
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

        $lmsFormSlug = $this->normalizeAndValidateFormSlug($validated['lms_form_slug'] ?? null);

        TourCabinetDirectionCity::query()->create([
            'direction_id' => $validated['direction_id'],
            'city_id' => $validated['city_id'],
            'needs_more_data' => (bool) ($validated['needs_more_data'] ?? false),
            'lms_form_slug' => $lmsFormSlug,
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
            'lms_form_slug' => ['sometimes', 'nullable', 'string', 'max:191'],
            'position' => ['nullable', 'integer', 'min:0', 'max:99999'],
        ]);

        $data = [];
        if (array_key_exists('needs_more_data', $validated)) {
            $data['needs_more_data'] = (bool) $validated['needs_more_data'];
        }
        if (array_key_exists('position', $validated) && $validated['position'] !== null) {
            $data['position'] = (int) $validated['position'];
        }

        $formSlugChanged = false;
        if (array_key_exists('lms_form_slug', $validated)) {
            $newSlug = $this->normalizeAndValidateFormSlug($validated['lms_form_slug']);
            $oldSlug = $directionCity->lms_form_slug;
            $oldSlug = is_string($oldSlug) && trim($oldSlug) !== '' ? trim($oldSlug) : null;
            if ($newSlug !== $oldSlug) {
                $formSlugChanged = true;
            }
            $data['lms_form_slug'] = $newSlug;
        }

        $submissionCount = 0;
        if ($formSlugChanged) {
            $submissionCount = TourCabinetContestCitySubmission::query()
                ->where('city_id', $directionCity->city_id)
                ->count();
        }

        if ($data !== []) {
            $directionCity->update($data);
        }

        $redirect = redirect()
            ->route('admin.tour-cabinet.index', ['direction_id' => $directionCity->direction_id])
            ->withFragment('tour-cabinet-admin-cities');

        if ($formSlugChanged && $submissionCount > 0) {
            return $redirect->with(
                'success',
                'Внимание: запись обновлена. У города уже есть '.$submissionCount.' сабмит(ов): старые ответы остаются, новые участники получат новую форму.',
            );
        }

        return $redirect->with('success', 'Запись обновлена.');
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

    /**
     * Нормализует значение `lms_form_slug` (`'' | null` → `null`, иначе trim) и проверяет,
     * что соответствующая `LmsForm` существует и активна.
     */
    private function normalizeAndValidateFormSlug(?string $value): ?string
    {
        $slug = is_string($value) ? trim($value) : '';
        if ($slug === '') {
            return null;
        }

        $exists = LmsForm::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'lms_form_slug' => 'Выберите активную форму из списка или оставьте пустым.',
            ]);
        }

        return $slug;
    }
}
