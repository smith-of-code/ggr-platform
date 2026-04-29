<?php

namespace App\Services\Admin;

use App\Models\City;
use App\Models\Direction;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsForm;
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

        $allForms = LmsForm::query()
            ->orderByDesc('updated_at')
            ->get(['id', 'slug', 'title', 'is_active']);

        return [
            'lmsEvent' => $event?->only(['id', 'slug', 'title']),
            'forms' => $forms,
            'configSlug' => $slug,
            'contestFormSlugOverrides' => [
                'standard' => (string) ($raw['contest_stage1_form_slug_standard'] ?? ''),
                'more_data' => (string) ($raw['contest_stage1_form_slug_more_data'] ?? ''),
            ],
            'dashboardStandardFormSlug' => (string) ($raw['dashboard_standard_form_slug'] ?? ''),
            'formOptions' => $forms->map(fn (LmsForm $f) => [
                'slug' => $f->slug,
                'title' => $f->title,
                'is_active' => (bool) $f->is_active,
            ])->values()->all(),
            'allFormsOptions' => $allForms->map(fn (LmsForm $f) => [
                'slug' => $f->slug,
                'title' => $f->title,
                'is_active' => (bool) $f->is_active,
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function directionCitiesPayload(?int $directionIdQuery): array
    {
        $allDirections = Direction::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('title')
            ->get(['id', 'title']);

        $directionId = $directionIdQuery;
        if ($directionId === null || ! $allDirections->contains('id', $directionId)) {
            $directionId = $allDirections->first()?->id;
        }

        $rows = TourCabinetDirectionCity::query()
            ->where('direction_id', $directionId)
            ->with('city:id,name,slug,is_active')
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $usedCityIds = TourCabinetDirectionCity::query()
            ->where('direction_id', $directionId)
            ->pluck('city_id')
            ->all();

        $cityOptions = City::query()
            ->where('is_active', true)
            ->whereNotIn('id', $usedCityIds)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $directions = $allDirections->map(fn (Direction $d) => [
            'key' => $d->id,
            'label' => $d->title,
        ])->values()->all();

        return [
            'directions' => $directions,
            'directionId' => $directionId,
            'rows' => $rows,
            'cityOptions' => $cityOptions,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function directionCitiesPayloadFromRequest(Request $request): array
    {
        $q = $request->query('direction_id');
        $id = is_numeric($q) ? (int) $q : null;

        return $this->directionCitiesPayload($id);
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

        return [
            'questions' => $questions,
            'directions' => Direction::projectList(),
        ];
    }

    /**
     * Настройки проверочного задания (этап 3) по направлениям конкурса.
     *
     * @return array<string, mixed>
     */
    public function stage3ConfigsPayload(): array
    {
        $activeDirections = Direction::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('title')
            ->get(['id', 'title']);

        $directions = $activeDirections->map(fn (Direction $d) => [
            'key' => $d->id,
            'label' => $d->title,
        ])->values()->all();

        $saved = Schema::hasTable('tour_cabinet_contest_stage3_configs')
            ? TourCabinetContestStage3Config::query()->get()->keyBy('direction_id')
            : collect();

        $directionMax = Schema::hasTable('tour_cabinet_contest_direction_settings')
            ? TourCabinetContestDirectionSetting::query()->get()->keyBy('direction_id')
            : collect();

        $configs = [];
        foreach ($activeDirections as $dir) {
            $row = $saved->get($dir->id);
            $maxRow = $directionMax->get($dir->id);
            $configs[] = [
                'direction_id' => $dir->id,
                'direction_label' => $dir->title,
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
