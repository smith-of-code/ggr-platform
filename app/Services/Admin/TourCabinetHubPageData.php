<?php

namespace App\Services\Admin;

use App\Models\City;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsForm;
use App\Models\Tour;
use App\Models\TourCabinetContestStage2Question;
use App\Models\TourCabinetDirectionCity;
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
}
