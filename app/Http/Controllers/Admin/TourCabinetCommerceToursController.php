<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsForm;
use App\Models\TourCabinetCommerceCityForm;
use App\Services\Admin\TourCabinetHubPageData;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TourCabinetCommerceToursController extends Controller
{
    private const SETTINGS_GROUP = 'tour_cabinet';
    private const FRAGMENT = 'tour-cabinet-admin-commerce-tours';

    public function __construct(
        private readonly SettingsService $settings,
        private readonly TourCabinetHubPageData $hubPageData,
    ) {}

    public function index(): Response
    {
        return Inertia::render(
            'Admin/TourCabinet/CommerceTours/Index',
            $this->hubPageData->commerceToursPayload(),
        );
    }

    public function storeCityForm(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'lms_form_slug' => ['required', 'string', 'max:191'],
        ]);

        $slug = trim((string) $validated['lms_form_slug']);
        $this->ensureFormSlugExists($slug);

        $exists = TourCabinetCommerceCityForm::query()
            ->where('city_id', $validated['city_id'])
            ->exists();

        if ($exists) {
            return $this->redirectBack()->with('error', 'Для этого города уже задана форма — отредактируйте существующую запись.');
        }

        TourCabinetCommerceCityForm::query()->create([
            'city_id' => (int) $validated['city_id'],
            'lms_form_slug' => $slug,
        ]);

        return $this->redirectBack()->with('success', 'Форма привязана к городу.');
    }

    public function updateCityForm(Request $request, TourCabinetCommerceCityForm $cityForm): RedirectResponse
    {
        $validated = $request->validate([
            'lms_form_slug' => ['required', 'string', 'max:191'],
        ]);

        $slug = trim((string) $validated['lms_form_slug']);
        $this->ensureFormSlugExists($slug);

        $cityForm->update(['lms_form_slug' => $slug]);

        return $this->redirectBack()->with('success', 'Форма обновлена.');
    }

    public function destroyCityForm(TourCabinetCommerceCityForm $cityForm): RedirectResponse
    {
        $cityForm->delete();

        return $this->redirectBack()->with('success', 'Привязка формы удалена.');
    }

    public function updateStage3Notification(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:20000'],
        ]);

        $enabled = (bool) ($validated['enabled'] ?? false);
        $subject = isset($validated['subject']) ? trim((string) $validated['subject']) : '';
        $body = isset($validated['body']) ? (string) $validated['body'] : '';

        $this->settings->setGroup(self::SETTINGS_GROUP, [
            'commerce_tours_enabled' => $enabled ? '1' : '0',
            'commerce_tours_stage3_subject' => $subject,
            'commerce_tours_stage3_body' => $body,
        ]);

        return $this->redirectBack()->with('success', 'Настройки блока «Коммерческие туры» сохранены.');
    }

    private function ensureFormSlugExists(string $slug): void
    {
        $exists = LmsForm::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'lms_form_slug' => 'Выберите активную форму из списка.',
            ]);
        }
    }

    private function redirectBack(): RedirectResponse
    {
        return redirect()
            ->route('admin.tour-cabinet.index')
            ->withFragment(self::FRAGMENT);
    }
}
