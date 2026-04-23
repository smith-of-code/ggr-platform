<?php

namespace App\Services\Admin;

use App\Models\City;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsForm;
use App\Models\Tour;
use App\Models\TourCabinetContestDirectionSetting;
use App\Models\TourCabinetContestStage2Question;
use App\Models\TourCabinetContestStage3Config;
use App\Models\TourCabinetDirectionCity;
use Illuminate\Support\Facades\Schema;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class TourCabinetHubPageData
{
    private const SETTINGS_GROUP = 'tour_cabinet';

    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function formsPayload(): array
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

        return [
            'lmsEvent' => $event?->only(['id', 'slug', 'title']),
            'forms' => $forms,
            'configSlug' => $slug,
            'contestFormSlugOverrides' => [
                'standard' => (string) ($raw['contest_stage1_form_slug_standard'] ?? ''),
                'more_data' => (string) ($raw['contest_stage1_form_slug_more_data'] ?? ''),
            ],
            'formOptions' => $forms->map(fn (LmsForm $f) => [
                'slug' => $f->slug,
                'title' => $f->title,
                'is_active' => (bool) $f->is_active,
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function directionCitiesPayload(?string $projectKeyQuery): array
    {
        $keys = array_keys(Tour::PROJECTS);
        $projectKey = $projectKeyQuery;
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

        return [
            'directions' => $directions,
            'projectKey' => $projectKey,
            'rows' => $rows,
            'cityOptions' => $cityOptions,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function directionCitiesPayloadFromRequest(Request $request): array
    {
        $q = $request->query('project_key');

        return $this->directionCitiesPayload(is_string($q) ? $q : null);
    }

    /**
     * @return array<string, mixed>
     */
    public function stage2QuestionsPayload(): array
    {
        $questions = TourCabinetContestStage2Question::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $directions = collect(Tour::PROJECTS)->map(fn (string $label, string $key) => [
            'key' => $key,
            'label' => $label,
        ])->values()->all();

        return [
            'questions' => $questions,
            'directions' => $directions,
        ];
    }

    /**
     * Настройки проверочного задания (этап 3) по направлениям конкурса.
     *
     * @return array<string, mixed>
     */
    public function stage3ConfigsPayload(): array
    {
        $directions = collect(Tour::PROJECTS)->map(fn (string $label, string $key) => [
            'key' => $key,
            'label' => $label,
        ])->values()->all();

        $saved = Schema::hasTable('tour_cabinet_contest_stage3_configs')
            ? TourCabinetContestStage3Config::query()->get()->keyBy('project_key')
            : collect();

        $directionMax = Schema::hasTable('tour_cabinet_contest_direction_settings')
            ? TourCabinetContestDirectionSetting::query()->get()->keyBy('project_key')
            : collect();

        $configs = [];
        foreach (Tour::PROJECTS as $key => $label) {
            $row = $saved->get($key);
            $maxRow = $directionMax->get($key);
            $configs[] = [
                'project_key' => $key,
                'direction_label' => $label,
                'title' => $row?->title ?? '',
                'task_body' => $row?->task_body ?? '',
                'response_format' => $row?->response_format ?? TourCabinetContestStage3Config::FORMAT_VIDEO_LINK,
                'is_saved' => $row !== null,
                'max_contest_stages' => $maxRow !== null
                    ? min(3, max(1, (int) $maxRow->max_contest_stages))
                    : 3,
            ];
        }

        return [
            'configs' => $configs,
            'directions' => $directions,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function contestStageDeadlinesPayload(): array
    {
        $d = $this->settings->getTourCabinetContestStageDeadlines();

        return [
            'stages' => [
                [
                    'num' => 1,
                    'title' => 'Этап I — анкета персональных данных',
                    'deadline_start' => $d[1]['start'] ?? '',
                    'deadline_end' => $d[1]['end'] ?? '',
                ],
                [
                    'num' => 2,
                    'title' => 'Этап II — развёрнутые ответы на вопросы',
                    'deadline_start' => $d[2]['start'] ?? '',
                    'deadline_end' => $d[2]['end'] ?? '',
                ],
                [
                    'num' => 3,
                    'title' => 'Этап III — проверочное задание',
                    'deadline_start' => $d[3]['start'] ?? '',
                    'deadline_end' => $d[3]['end'] ?? '',
                ],
            ],
        ];
    }
}
