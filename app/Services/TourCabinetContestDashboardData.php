<?php

namespace App\Services;

use App\Models\City;
use App\Models\Tour;
use App\Models\TourCabinetContestCitySubmission;
use App\Models\TourCabinetContestProgress;
use App\Models\TourCabinetContestStage2Answer;
use App\Models\TourCabinetContestStage2Question;
use App\Models\TourCabinetDirectionCity;
use App\Models\TourDeparture;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

final class TourCabinetContestDashboardData
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $progress = TourCabinetContestProgress::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['current_stage' => 1]
        );

        $directions = collect(Tour::PROJECTS)->map(fn (string $label, string $key) => [
            'key' => $key,
            'label' => $label,
        ])->values()->all();

        $cities = [];
        if ($progress->project_key) {
            $cities = TourCabinetDirectionCity::query()
                ->where('project_key', $progress->project_key)
                ->with('city:id,name,slug')
                ->orderBy('position')
                ->orderBy('id')
                ->get()
                ->map(fn (TourCabinetDirectionCity $row) => [
                    'id' => $row->city->id,
                    'name' => $row->city->name,
                    'slug' => $row->city->slug,
                    'needs_more_data' => $row->needs_more_data,
                ])
                ->all();
        }

        $selectedIds = array_values(array_map('intval', $progress->selected_city_ids ?? []));
        $selectedCitiesPayload = [];
        if ($progress->project_key && $selectedIds !== []) {
            $subs = TourCabinetContestCitySubmission::query()
                ->where('user_id', $user->id)
                ->whereIn('city_id', $selectedIds)
                ->get()
                ->keyBy('city_id');

            foreach ($selectedIds as $cid) {
                $row = TourCabinetDirectionCity::query()
                    ->where('project_key', $progress->project_key)
                    ->where('city_id', $cid)
                    ->first();
                $city = City::query()->find($cid);
                if (! $city || ! $row) {
                    continue;
                }
                $standard = $this->settings->getTourCabinetContestStage1FormSlugStandard();
                $moreData = $this->settings->getTourCabinetContestStage1FormSlugMoreData();
                $slug = $row->needs_more_data ? $moreData : $standard;
                $subRow = $subs->get($cid);
                $selectedCitiesPayload[] = [
                    'id' => $city->id,
                    'name' => $city->name,
                    'slug' => $city->slug,
                    'needs_more_data' => $row->needs_more_data,
                    'form_slug' => $slug,
                    'submitted' => $subs->has($cid),
                    'submission_id' => $subRow ? $subRow->lms_form_submission_id : null,
                ];
            }
        }

        $step = 'direction';
        if ($progress->project_key) {
            $step = $selectedIds !== [] ? 'forms' : 'cities';
        }

        $stage1Complete = $this->stage1Complete($progress, $user->id);

        $contestLocationOffers = $this->contestLocationOffers($progress, $user);
        $contestStageSummary = $this->contestStageSummary($progress, $stage1Complete);

        $questions = $this->activeStage2QuestionsQuery($progress->project_key)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $answers = TourCabinetContestStage2Answer::query()
            ->where('user_id', $user->id)
            ->whereIn('question_id', $questions->pluck('id'))
            ->get()
            ->keyBy('question_id');

        $questionsPayload = $questions->map(fn (TourCabinetContestStage2Question $q) => [
            'id' => $q->id,
            'body' => $q->body,
            'answer_text' => optional($answers->get($q->id))->answer_text ?? '',
        ])->values()->all();

        return [
            'contestProgress' => [
                'current_stage' => (int) $progress->current_stage,
            ],
            'contestStage1' => [
                'step' => $step,
                'progress' => [
                    'project_key' => $progress->project_key,
                    'selected_city_ids' => $selectedIds,
                    'current_stage' => (int) $progress->current_stage,
                ],
                'directions' => $directions,
                'cities' => $cities,
                'selectedCitiesForForms' => $selectedCitiesPayload,
                'formSlugsConfigured' => [
                    'standard' => (bool) $this->settings->getTourCabinetContestStage1FormSlugStandard(),
                    'more_data' => (bool) $this->settings->getTourCabinetContestStage1FormSlugMoreData(),
                ],
                'stage1Complete' => $stage1Complete,
            ],
            'contestStage2Questions' => $questionsPayload,
            'contestStage3Progress' => [
                'current_stage' => (int) $progress->current_stage,
                'stage3_text' => $progress->stage3_text,
                'stage3_video_url' => $progress->stage3_video_url,
            ],
            'contestLocationOffers' => $contestLocationOffers,
            'contestStageSummary' => $contestStageSummary,
        ];
    }

    /**
     * @return list<array{
     *     id: int,
     *     project_key: string,
     *     city_id: int,
     *     city_name: string,
     *     date_range: ?string,
     *     footnote: ?string,
     *     button_kind: 'pending_review'|'participate',
     *     city_form_url: ?string
     * }>
     */
    private function contestLocationOffers(TourCabinetContestProgress $progress, User $user): array
    {
        $selected = array_map('intval', $progress->selected_city_ids ?? []);
        $rows = [];

        foreach (array_keys(Tour::PROJECTS) as $projectKey) {
            $dcRows = TourCabinetDirectionCity::query()
                ->where('project_key', $projectKey)
                ->with('city:id,name')
                ->orderBy('position')
                ->orderBy('id')
                ->get();

            foreach ($dcRows as $dc) {
                if (! $dc->city) {
                    continue;
                }
                $cityId = (int) $dc->city_id;
                $inSelection = $progress->project_key === $projectKey && in_array($cityId, $selected, true);
                $submitted = $inSelection && TourCabinetContestCitySubmission::query()
                    ->where('user_id', $user->id)
                    ->where('city_id', $cityId)
                    ->exists();

                $footnote = $dc->needs_more_data
                    ? '*проверка службы безопасности/требуется заполнение доп. анкеты'
                    : null;

                $buttonKind = $submitted ? 'pending_review' : 'participate';
                $cityFormUrl = null;
                if ($buttonKind === 'participate' && $inSelection && ! $submitted) {
                    $cityFormUrl = route('tour-cabinet.contest.city-form', ['city' => $cityId], false);
                }

                $rows[] = [
                    'id' => $dc->id,
                    'project_key' => $projectKey,
                    'city_id' => $cityId,
                    'city_name' => $dc->city->name,
                    'date_range' => $this->dateRangeForContestCity($projectKey, $cityId),
                    'footnote' => $footnote,
                    'button_kind' => $buttonKind,
                    'city_form_url' => $cityFormUrl,
                ];
            }
        }

        return $rows;
    }

    private function dateRangeForContestCity(string $projectKey, int $cityId): ?string
    {
        $dep = TourDeparture::query()
            ->whereHas('tour', function (Builder $q) use ($projectKey, $cityId): void {
                $q->where('project', $projectKey)
                    ->where('is_active', true)
                    ->whereHas('cities', fn (Builder $cq) => $cq->where('cities.id', $cityId));
            })
            ->orderBy('start_date')
            ->first();

        if (! $dep?->start_date || ! $dep?->end_date) {
            return null;
        }

        return $this->formatDepartureRange($dep->start_date, $dep->end_date);
    }

    private function formatDepartureRange(\DateTimeInterface $start, \DateTimeInterface $end): string
    {
        if ($start->format('Y-m') === $end->format('Y-m')) {
            return $start->format('d.m').'–'.$end->format('d.m.Y');
        }

        return $start->format('d.m.Y').'–'.$end->format('d.m.Y');
    }

    /**
     * @return list<array{roman: string, title: string, label: string, status_key: string, status_label: string}>
     */
    private function contestStageSummary(TourCabinetContestProgress $progress, bool $stage1Complete): array
    {
        $st = (int) $progress->current_stage;
        $stage3Filled = filled($progress->stage3_text);

        $s1 = $this->stageSummaryRow(
            'I',
            'Этап I',
            'Анкета персональных данных',
            $stage1Complete,
            $progress->project_key !== null && ! $stage1Complete,
        );

        $s2 = $this->stageSummaryRow(
            'II',
            'Этап II',
            'Развернутые ответы на вопросы',
            $st >= 3,
            $st === 2,
        );

        $s3 = $this->stageSummaryRow(
            'III',
            'Этап III',
            'Проверочное задание',
            $stage3Filled,
            $st >= 3 && ! $stage3Filled,
        );

        return [$s1, $s2, $s3];
    }

    /**
     * @return array{roman: string, title: string, label: string, status_key: string, status_label: string}
     */
    private function stageSummaryRow(string $roman, string $title, string $label, bool $done, bool $inProgress): array
    {
        if ($done) {
            return [
                'roman' => $roman,
                'title' => $title,
                'label' => $label,
                'status_key' => 'done',
                'status_label' => 'заполнено',
            ];
        }
        if ($inProgress) {
            return [
                'roman' => $roman,
                'title' => $title,
                'label' => $label,
                'status_key' => 'in_progress',
                'status_label' => 'в процессе',
            ];
        }

        return [
            'roman' => $roman,
            'title' => $title,
            'label' => $label,
            'status_key' => 'todo',
            'status_label' => 'не начато',
        ];
    }

    private function stage1Complete(TourCabinetContestProgress $progress, int $userId): bool
    {
        $ids = array_values(array_map('intval', $progress->selected_city_ids ?? []));
        if ($ids === [] || ! $progress->project_key) {
            return false;
        }
        foreach ($ids as $cityId) {
            if (! TourCabinetContestCitySubmission::query()
                ->where('user_id', $userId)
                ->where('city_id', $cityId)
                ->exists()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return Builder<TourCabinetContestStage2Question>
     */
    private function activeStage2QuestionsQuery(?string $projectKey): Builder
    {
        return TourCabinetContestStage2Question::query()
            ->where('is_active', true)
            ->where(function ($q) use ($projectKey): void {
                $q->whereNull('project_key');
                if ($projectKey) {
                    $q->orWhere('project_key', $projectKey);
                }
            });
    }
}
