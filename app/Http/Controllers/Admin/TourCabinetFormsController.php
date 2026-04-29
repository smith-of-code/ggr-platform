<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsForm;
use App\Services\Admin\TourCabinetHubPageData;
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
        private readonly TourCabinetHubPageData $hubPageData,
    ) {}

    /**
     * Список форм события LMS, привязанного к ЛК туров (тот же slug, что lms-admin/{slug}/forms).
     */
    public function index(): Response
    {
        return Inertia::render('Admin/TourCabinet/Forms/Index', $this->hubPageData->formsPayload());
    }

    public function updateContestFormSlugs(Request $request): RedirectResponse
    {
        $eventSlug = config('tour_cabinet.lms_event_slug');
        $event = LmsEvent::where('slug', $eventSlug)->first();
        if (! $event) {
            return redirect()
                ->route('admin.tour-cabinet.index')
                ->withFragment('tour-cabinet-admin-forms')
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
            ->route('admin.tour-cabinet.index')
            ->withFragment('tour-cabinet-admin-forms')
            ->with('success', 'Slug форм конкурса (этап 1) сохранены.');
    }

    /**
     * Сохранение slug «Стандартной анкеты» отдельного блока на дашборде ЛК туров.
     * Допустима любая активная форма платформы (без фильтра по lms_event_id).
     * Пустое значение очищает настройку (блок на дашборде скрыт).
     */
    public function updateDashboardStandardFormSlug(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'dashboard_standard_form_slug' => ['nullable', 'string', 'max:255'],
        ]);

        $slug = isset($validated['dashboard_standard_form_slug'])
            ? trim((string) $validated['dashboard_standard_form_slug'])
            : '';

        if ($slug !== '') {
            $exists = LmsForm::query()
                ->where('slug', $slug)
                ->where('is_active', true)
                ->exists();

            if (! $exists) {
                throw ValidationException::withMessages([
                    'dashboard_standard_form_slug' => 'Выберите активную форму из списка или оставьте пустым.',
                ]);
            }
        }

        $this->settings->setGroup(self::SETTINGS_GROUP, [
            'dashboard_standard_form_slug' => $slug,
        ]);

        return redirect()
            ->route('admin.tour-cabinet.index')
            ->withFragment('tour-cabinet-admin-forms')
            ->with('success', 'Стандартная анкета дашборда сохранена.');
    }

    /**
     * Сохранить настройку письма «Конкурс пройден» (этап 3 завершён).
     * Поля: enabled (bool), subject (≤255), body (≤20000). Запись в settings группу tour_cabinet.
     */
    public function updateContestCompletionNotification(Request $request): RedirectResponse
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
            'contest_completion_notification_enabled' => $enabled ? '1' : '0',
            'contest_completion_notification_subject' => $subject,
            'contest_completion_notification_body' => $body,
        ]);

        return redirect()
            ->route('admin.tour-cabinet.index')
            ->withFragment('tour-cabinet-admin-completion-notification')
            ->with('success', 'Уведомление о завершении конкурса сохранено.');
    }

    public function updateContestStageDeadlines(Request $request): RedirectResponse
    {
        $merged = $request->all();
        foreach ([1, 2, 3] as $i) {
            foreach (['deadline_start', 'deadline_end'] as $part) {
                $k = "stage_{$i}_{$part}";
                if (array_key_exists($k, $merged) && $merged[$k] === '') {
                    $merged[$k] = null;
                }
            }
        }
        $request->merge($merged);

        $rules = [];
        foreach ([1, 2, 3] as $i) {
            $rules["stage_{$i}_deadline_start"] = ['nullable', 'date'];
            $rules["stage_{$i}_deadline_end"] = ['nullable', 'date'];
        }

        $validated = $request->validate($rules);

        foreach ([1, 2, 3] as $i) {
            $start = $validated["stage_{$i}_deadline_start"] ?? null;
            $end = $validated["stage_{$i}_deadline_end"] ?? null;
            $start = is_string($start) ? trim($start) : '';
            $end = is_string($end) ? trim($end) : '';
            if ($start !== '' && $end !== '' && $end < $start) {
                throw ValidationException::withMessages([
                    "stage_{$i}_deadline_end" => 'Дата окончания не может быть раньше даты начала.',
                ]);
            }
        }

        $payload = [];
        foreach ([1, 2, 3] as $i) {
            $s = $validated["stage_{$i}_deadline_start"] ?? null;
            $e = $validated["stage_{$i}_deadline_end"] ?? null;
            $payload["contest_stage_{$i}_deadline_start"] = is_string($s) ? trim($s) : '';
            $payload["contest_stage_{$i}_deadline_end"] = is_string($e) ? trim($e) : '';
        }

        $this->settings->setGroup(self::SETTINGS_GROUP, $payload);

        return redirect()
            ->route('admin.tour-cabinet.index')
            ->withFragment('tour-cabinet-admin-deadlines')
            ->with('success', 'Сроки этапов конкурса сохранены.');
    }
}
