<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsForm;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TourCabinetFormsController extends Controller
{
    private const SETTINGS_GROUP = 'tour_cabinet';

    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    /**
     * Список форм события LMS, привязанного к ЛК туров (тот же slug, что lms-admin/{slug}/forms).
     */
    public function index(): Response
    {
        $slug = config('tour_cabinet.lms_event_slug');
        $event = LmsEvent::where('slug', $slug)->first();

        $forms = collect();
        if ($event) {
            $forms = LmsForm::query()
                ->where('lms_event_id', $event->id)
                ->withCount('submissions')
                ->orderByDesc('updated_at')
                ->get();
        }

        $raw = $this->settings->getGroupFresh(self::SETTINGS_GROUP);

        return Inertia::render('Admin/TourCabinet/Forms/Index', [
            'lmsEvent' => $event?->only(['id', 'slug', 'title']),
            'forms' => $forms,
            'configSlug' => $slug,
            'contestFormSlugOverrides' => [
                'standard' => (string) ($raw['contest_stage1_form_slug_standard'] ?? ''),
                'more_data' => (string) ($raw['contest_stage1_form_slug_more_data'] ?? ''),
            ],
            'contestFormSlugsEffective' => [
                'standard' => $this->settings->getTourCabinetContestStage1FormSlugStandard(),
                'more_data' => $this->settings->getTourCabinetContestStage1FormSlugMoreData(),
            ],
            'formOptions' => $forms->map(fn (LmsForm $f) => [
                'slug' => $f->slug,
                'title' => $f->title,
                'is_active' => (bool) $f->is_active,
            ])->values()->all(),
        ]);
    }

    public function updateContestFormSlugs(Request $request): RedirectResponse
    {
        $eventSlug = config('tour_cabinet.lms_event_slug');
        $event = LmsEvent::where('slug', $eventSlug)->first();
        if (! $event) {
            return redirect()
                ->route('admin.tour-cabinet.forms.index')
                ->with('error', 'Событие LMS не найдено — сначала настройте TOUR_CABINET_LMS_EVENT_SLUG.');
        }

        $validated = $request->validate([
            'contest_stage1_form_slug_standard' => ['nullable', 'string', 'max:255'],
            'contest_stage1_form_slug_more_data' => ['nullable', 'string', 'max:255'],
        ]);

        $allowedSlugs = LmsForm::query()
            ->where('lms_event_id', $event->id)
            ->pluck('slug')
            ->map(fn ($s) => (string) $s)
            ->all();

        foreach (['contest_stage1_form_slug_standard', 'contest_stage1_form_slug_more_data'] as $field) {
            $value = isset($validated[$field]) ? trim((string) $validated[$field]) : '';
            if ($value !== '' && ! in_array($value, $allowedSlugs, true)) {
                throw ValidationException::withMessages([
                    $field => 'Выберите форму из списка этого события или оставьте «как в .env».',
                ]);
            }
        }

        $standard = trim((string) ($validated['contest_stage1_form_slug_standard'] ?? ''));
        $moreData = trim((string) ($validated['contest_stage1_form_slug_more_data'] ?? ''));

        $this->settings->setGroup(self::SETTINGS_GROUP, [
            'contest_stage1_form_slug_standard' => $standard,
            'contest_stage1_form_slug_more_data' => $moreData,
        ]);

        return redirect()
            ->route('admin.tour-cabinet.forms.index')
            ->with('success', 'Slug форм конкурса (этап 1) сохранены.');
    }
}
