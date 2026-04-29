<?php

namespace App\Services;

use App\Models\City;
use App\Models\Direction;
use App\Models\TourCabinetContestCitySubmission;
use App\Models\TourCabinetContestDirectionSetting;
use App\Models\TourCabinetContestProgress;
use App\Models\TourCabinetContestStage2Answer;
use App\Models\TourCabinetContestStage2Question;
use App\Models\TourCabinetContestStage3Config;
use App\Models\TourCabinetDirectionCity;
use App\Models\TourDeparture;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

final class TourCabinetContestDashboardData
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly TourCabinetContestStage1FormResolver $stage1FormResolver,
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

        $directions = Direction::projectList();

        $cities = [];
        if ($progress->direction_id) {
            $cityRows = TourCabinetDirectionCity::query()
                ->where('direction_id', $progress->direction_id)
                ->with('city:id,name,slug')
                ->orderBy('position')
                ->orderBy('id')
                ->get();

            foreach ($cityRows as $row) {
                if (! $row->city) {
                    continue;
                }
                $resolved = $this->stage1FormResolver->resolveForRow($row);
                $cities[] = [
                    'id' => $row->city->id,
                    'name' => $row->city->name,
                    'slug' => $row->city->slug,
                    'needs_more_data' => (bool) $row->needs_more_data,
                    'has_form' => $resolved !== null,
                ];
            }
        }

        $selectedIds = array_values(array_map('intval', $progress->selected_city_ids ?? []));
        $selectedCitiesPayload = [];
        if ($progress->direction_id && $selectedIds !== []) {
            $subs = TourCabinetContestCitySubmission::query()
                ->where('user_id', $user->id)
                ->whereIn('city_id', $selectedIds)
                ->get()
                ->keyBy('city_id');

            $rows = TourCabinetDirectionCity::query()
                ->where('direction_id', $progress->direction_id)
                ->whereIn('city_id', $selectedIds)
                ->get()
                ->keyBy('city_id');

            foreach ($selectedIds as $cid) {
                $row = $rows->get($cid);
                $city = City::query()->find($cid);
                if (! $city || ! $row) {
                    continue;
                }
                $slug = $this->stage1FormResolver->resolveForRow($row);
                $submitted = $subs->has($cid);
                $subRow = $subs->get($cid);
                $selectedCitiesPayload[] = [
                    'id' => $city->id,
                    'name' => $city->name,
                    'slug' => $city->slug,
                    'needs_more_data' => (bool) $row->needs_more_data,
                    'form_slug' => $slug,
                    'submitted' => $submitted,
                    'auto_completed' => $slug === null && ! $submitted,
                    'submission_id' => $subRow ? $subRow->lms_form_submission_id : null,
                ];
            }
        }

        $stage1Complete = $this->stage1Complete($progress, $user->id);

        $step = 'direction';
        if ($progress->direction_id) {
            $step = $selectedIds !== [] ? 'forms' : 'cities';
            if ($step === 'forms' && ! $stage1Complete && Session::get('tour_cabinet_contest_reopen_cities')) {
                $step = 'cities';
            }
        }

        $contestLocationOffers = $this->contestLocationOffers($progress, $user);
        $stageDeadlines = $this->settings->getTourCabinetContestStageDeadlines();
        $stage3Config = TourCabinetContestStage3Config::forDirection($progress->direction_id);
        $stage3Filled = $this->isStage3ResponseComplete($progress, $stage3Config);
        $maxContestStages = TourCabinetContestDirectionSetting::maxContestStagesForDirection($progress->direction_id);
        $contestStageSummary = $this->contestStageSummary(
            $progress,
            $stage1Complete,
            $stageDeadlines,
            $stage3Filled,
            $maxContestStages
        );

        $questions = $this->activeStage2QuestionsQuery($progress->direction_id)
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
            'min_length' => $q->min_length,
            'max_length' => $q->max_length,
        ])->values()->all();

        return [
            'contestProgress' => [
                'current_stage' => (int) $progress->current_stage,
                'stage2_submitted_at' => $progress->stage2_submitted_at?->toIso8601String(),
                'max_contest_stages' => $maxContestStages,
                'stage2_locked' => $this->isStage2LockedForParticipant($progress, $maxContestStages),
            ],
            'contestStage1' => [
                'step' => $step,
                'progress' => [
                    'direction_id' => $progress->direction_id,
                    'selected_city_ids' => $selectedIds,
                    'current_stage' => (int) $progress->current_stage,
                ],
                'directions' => $directions,
                'cities' => $cities,
                'selectedCitiesForForms' => $selectedCitiesPayload,
                'stage1Complete' => $stage1Complete,
            ],
            'contestStage2Questions' => $questionsPayload,
            'contestStage3Progress' => $this->contestStage3ProgressPayload($progress, $stage3Config, $stage3Filled),
            'contestLocationOffers' => $contestLocationOffers,
            'contestStageSummary' => $contestStageSummary,
        ];
    }

    /**
     * @return list<array{
     *     id: int,
     *     direction_id: int,
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

        $directionIds = Direction::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->pluck('id');

        foreach ($directionIds as $dirId) {
            $dcRows = TourCabinetDirectionCity::query()
                ->where('direction_id', $dirId)
                ->with('city:id,name')
                ->orderBy('position')
                ->orderBy('id')
                ->get();

            foreach ($dcRows as $dc) {
                if (! $dc->city) {
                    continue;
                }
                $cityId = (int) $dc->city_id;
                $inSelection = $progress->direction_id === $dirId && in_array($cityId, $selected, true);
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
                    'direction_id' => $dirId,
                    'city_id' => $cityId,
                    'city_name' => $dc->city->name,
                    'date_range' => $this->dateRangeForContestCity($dirId, $cityId),
                    'footnote' => $footnote,
                    'button_kind' => $buttonKind,
                    'city_form_url' => $cityFormUrl,
                ];
            }
        }

        return $rows;
    }

    private function dateRangeForContestCity(int $directionId, int $cityId): ?string
    {
        $dep = TourDeparture::query()
            ->whereHas('tour', function (Builder $q) use ($directionId, $cityId): void {
                $q->where('direction_id', $directionId)
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
     * @param  array<int, array{start: ?string, end: ?string}>  $stageDeadlines
     * @return list<array{
     *     roman: string,
     *     title: string,
     *     label: string,
     *     status_key: string,
     *     status_label: string,
     *     deadline_start: ?string,
     *     deadline_end: ?string,
     *     deadline_display: ?string
     * }>
     */
    /**
     * Этап 3 считается завершённым при заполненном тексте и втором поле по формату (или только текст, если настройки направления ещё не заданы).
     */
    private function isStage3ResponseComplete(TourCabinetContestProgress $progress, ?TourCabinetContestStage3Config $config): bool
    {
        if (! filled($progress->stage3_text) || trim((string) $progress->stage3_text) === '') {
            return false;
        }
        if ($config === null) {
            return true;
        }
        if ($config->usesFileUpload()) {
            return filled($progress->stage3_attachment_path);
        }

        return filled($progress->stage3_video_url) && trim((string) $progress->stage3_video_url) !== '';
    }

    /**
     * @return array<string, mixed>
     */
    private function contestStage3ProgressPayload(
        TourCabinetContestProgress $progress,
        ?TourCabinetContestStage3Config $config,
        bool $isComplete,
    ): array {
        $format = $config?->response_format ?? TourCabinetContestStage3Config::FORMAT_VIDEO_LINK;

        return [
            'current_stage' => (int) $progress->current_stage,
            'stage3_text' => $progress->stage3_text,
            'stage3_video_url' => $progress->stage3_video_url,
            'stage3_attachment_original_name' => $progress->stage3_attachment_original_name,
            'stage3_has_attachment' => filled($progress->stage3_attachment_path),
            'is_complete' => $isComplete,
            'assignment' => [
                'title' => $config?->title ?? 'Проверочное задание',
                'task_body' => $config?->task_body ?? '',
                'response_format' => $format,
                'from_config' => $config !== null,
                'text_min_length' => $config?->text_min_length,
                'text_max_length' => $config?->text_max_length,
            ],
        ];
    }

    private function isStage2LockedForParticipant(TourCabinetContestProgress $progress, int $maxContestStages): bool
    {
        if ($maxContestStages < 2) {
            return true;
        }
        $st = (int) $progress->current_stage;
        // Этап 2 заблокирован, пока участник не завершил Этап 1
        // (нажатие «Перейти к этапу 2» переводит current_stage в 2; до этого момента
        // даже клик по вкладке «Этап II» не должен открывать форму ответов).
        if ($st < 2) {
            return true;
        }
        if ($st > 2) {
            return true;
        }
        if ($st === 2 && filled($progress->stage2_submitted_at)) {
            return true;
        }

        return false;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function contestStageSummary(
        TourCabinetContestProgress $progress,
        bool $stage1Complete,
        array $stageDeadlines,
        bool $stage3Filled,
        int $maxContestStages,
    ): array {
        $st = (int) $progress->current_stage;
        $maxContestStages = min(3, max(1, $maxContestStages));

        $s1 = $this->stageSummaryRow(
            'I',
            'Этап I',
            'Анкета персональных данных',
            $stage1Complete,
            $progress->direction_id !== null && ! $stage1Complete,
            $stageDeadlines[1] ?? ['start' => null, 'end' => null],
        );

        $rows = [$s1];

        if ($maxContestStages >= 2) {
            $stage2Done = filled($progress->stage2_submitted_at) || $st > 2;
            $stage2InProgress = $st === 2 && ! $stage2Done;
            $rows[] = $this->stageSummaryRow(
                'II',
                'Этап II',
                'Развернутые ответы на вопросы',
                $stage2Done,
                $stage2InProgress,
                $stageDeadlines[2] ?? ['start' => null, 'end' => null],
            );
        }

        if ($maxContestStages >= 3) {
            $rows[] = $this->stageSummaryRow(
                'III',
                'Этап III',
                'Проверочное задание',
                $stage3Filled,
                $st >= 3 && ! $stage3Filled,
                $stageDeadlines[3] ?? ['start' => null, 'end' => null],
            );
        }

        return $rows;
    }

    /**
     * @param  array{start: ?string, end: ?string}  $deadline
     * @return array{
     *     roman: string,
     *     title: string,
     *     label: string,
     *     status_key: string,
     *     status_label: string,
     *     deadline_start: ?string,
     *     deadline_end: ?string,
     *     deadline_display: ?string
     * }
     */
    private function stageSummaryRow(string $roman, string $title, string $label, bool $done, bool $inProgress, array $deadline): array
    {
        $deadlineStart = $deadline['start'] ?? null;
        $deadlineEnd = $deadline['end'] ?? null;
        $deadlineDisplay = $this->formatContestDeadlineLabel($deadlineStart, $deadlineEnd);

        if ($done) {
            return [
                'roman' => $roman,
                'title' => $title,
                'label' => $label,
                'status_key' => 'done',
                'status_label' => 'заполнено',
                'deadline_start' => $deadlineStart,
                'deadline_end' => $deadlineEnd,
                'deadline_display' => $deadlineDisplay,
            ];
        }
        if ($inProgress) {
            return [
                'roman' => $roman,
                'title' => $title,
                'label' => $label,
                'status_key' => 'in_progress',
                'status_label' => 'в процессе',
                'deadline_start' => $deadlineStart,
                'deadline_end' => $deadlineEnd,
                'deadline_display' => $deadlineDisplay,
            ];
        }

        return [
            'roman' => $roman,
            'title' => $title,
            'label' => $label,
            'status_key' => 'todo',
            'status_label' => 'не начато',
            'deadline_start' => $deadlineStart,
            'deadline_end' => $deadlineEnd,
            'deadline_display' => $deadlineDisplay,
        ];
    }

    private function formatContestDeadlineLabel(?string $start, ?string $end): ?string
    {
        if ($start === null && $end === null) {
            return null;
        }
        $fmt = fn (?string $iso) => $iso === null ? null : Carbon::parse($iso)->format('d.m.Y');
        $a = $fmt($start);
        $b = $fmt($end);
        if ($a !== null && $b !== null) {
            return "с {$a} по {$b}";
        }
        if ($a !== null) {
            return "с {$a}";
        }
        if ($b !== null) {
            return "до {$b}";
        }

        return null;
    }

    private function stage1Complete(TourCabinetContestProgress $progress, int $userId): bool
    {
        $ids = array_values(array_map('intval', $progress->selected_city_ids ?? []));
        if ($ids === [] || ! $progress->direction_id) {
            return false;
        }

        $resolved = $this->stage1FormResolver->resolveBatchForDirection((int) $progress->direction_id, $ids);

        foreach ($ids as $cityId) {
            $expectedSlug = $resolved[$cityId] ?? null;
            if ($expectedSlug === null) {
                continue;
            }
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
    private function activeStage2QuestionsQuery(?int $directionId): Builder
    {
        return TourCabinetContestStage2Question::query()
            ->where('is_active', true)
            ->where(function ($q) use ($directionId): void {
                $q->whereNull('direction_id');
                if ($directionId) {
                    $q->orWhere('direction_id', $directionId);
                }
            });
    }
}
