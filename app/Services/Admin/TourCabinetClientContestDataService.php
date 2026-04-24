<?php

namespace App\Services\Admin;

use App\Models\Application;
use App\Models\City;
use App\Models\Direction;
use App\Models\TourCabinetContestCitySubmission;
use App\Models\TourCabinetContestProgress;
use App\Models\TourCabinetContestStage2Answer;
use App\Models\TourCabinetContestStage2Question;
use App\Models\TourCabinetContestStage3Config;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

final class TourCabinetClientContestDataService
{
    /**
     * Города, по которым есть заявки этапа 1 или выбор в прогрессе — для фильтра выгрузки.
     *
     * @return list<array{id: int, name: string}>
     */
    public function cityOptionsForExport(): array
    {
        if (! Schema::hasTable('cities')) {
            return [];
        }

        $ids = collect();

        if (Schema::hasTable('tour_cabinet_contest_city_submissions')) {
            $ids = $ids->merge(
                TourCabinetContestCitySubmission::query()->distinct()->pluck('city_id')
            );
        }

        if (Schema::hasTable('tour_cabinet_contest_progress')) {
            TourCabinetContestProgress::query()
                ->whereNotNull('selected_city_ids')
                ->pluck('selected_city_ids')
                ->each(function ($arr) use ($ids) {
                    if (is_array($arr)) {
                        foreach ($arr as $id) {
                            $ids->push((int) $id);
                        }
                    }
                });
        }

        $ids = $ids->filter(fn ($id) => $id > 0)->unique()->values();
        if ($ids->isEmpty()) {
            return [];
        }

        return City::query()
            ->whereIn('id', $ids->all())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (City $c) => ['id' => $c->id, 'name' => $c->name])
            ->values()
            ->all();
    }

    /**
     * Краткая строка для таблицы списка клиентов.
     */
    public function listContestSummary(User $user): string
    {
        if (! Schema::hasTable('tour_cabinet_contest_progress')) {
            return '—';
        }

        $user->loadMissing(['tourCabinetContestProgress', 'tourCabinetContestCitySubmissions.city']);

        $progress = $user->tourCabinetContestProgress;
        $parts = [];

        if ($progress?->direction_id) {
            $parts[] = Direction::allProjectMap()[$progress->direction_id] ?? '—';
        }

        if ($progress) {
            $parts[] = 'этап '.(int) $progress->current_stage;
        }

        $cityNames = $user->tourCabinetContestCitySubmissions
            ->map(fn ($s) => $s->city?->name)
            ->filter()
            ->unique()
            ->values();

        if ($cityNames->isNotEmpty()) {
            $parts[] = 'города: '.$cityNames->implode(', ');
        } elseif ($progress && is_array($progress->selected_city_ids) && count($progress->selected_city_ids) > 0) {
            $names = City::query()->whereIn('id', $progress->selected_city_ids)->orderBy('name')->pluck('name');
            if ($names->isNotEmpty()) {
                $parts[] = 'выбор: '.$names->implode(', ');
            }
        }

        return $parts !== [] ? implode(' · ', $parts) : '—';
    }

    /**
     * Полные данные конкурса и заявок на туры для карточки клиента.
     *
     * @return array<string, mixed>
     */
    public function contestPayloadForUser(User $user): array
    {
        $payload = [
            'progress' => null,
            'stage1_city_forms' => [],
            'stage2_qa' => [],
            'stage3' => null,
            'tour_applications' => [],
        ];

        if (Schema::hasTable('tour_cabinet_contest_progress')) {
            $user->loadMissing('tourCabinetContestProgress');
            $progress = $user->tourCabinetContestProgress;
            if ($progress) {
                $selected = is_array($progress->selected_city_ids) ? $progress->selected_city_ids : [];
                $cityRows = $selected !== []
                    ? City::query()->whereIn('id', $selected)->orderBy('name')->get(['id', 'name'])
                    : collect();

                $directionMap = Direction::allProjectMap();
                $payload['progress'] = [
                    'direction_id' => $progress->direction_id,
                    'direction_label' => $progress->direction_id
                        ? ($directionMap[$progress->direction_id] ?? null)
                        : null,
                    'current_stage' => (int) $progress->current_stage,
                    'selected_cities' => $cityRows->map(fn (City $c) => ['id' => $c->id, 'name' => $c->name])->values()->all(),
                    'stage2_submitted_at' => $progress->stage2_submitted_at?->toIso8601String(),
                    'updated_at' => $progress->updated_at?->toIso8601String(),
                ];

                $stage3Config = Schema::hasTable('tour_cabinet_contest_stage3_configs')
                    ? TourCabinetContestStage3Config::forDirection($progress->direction_id)
                    : null;

                $payload['stage3'] = [
                    'assignment_title' => $stage3Config?->title ?? 'Проверочное задание',
                    'task_body' => $stage3Config?->task_body,
                    'response_format' => $stage3Config?->response_format ?? TourCabinetContestStage3Config::FORMAT_VIDEO_LINK,
                    'text' => $progress->stage3_text,
                    'video_url' => $progress->stage3_video_url,
                    'attachment_original_name' => $progress->stage3_attachment_original_name,
                    'has_attachment' => filled($progress->stage3_attachment_path),
                    'attachment_download_url' => filled($progress->stage3_attachment_path)
                        ? route('admin.tour-cabinet.stage3-answers.attachment', ['id' => $progress->id], false)
                        : null,
                ];
            }
        }

        if (Schema::hasTable('tour_cabinet_contest_city_submissions')) {
            $subs = TourCabinetContestCitySubmission::query()
                ->where('user_id', $user->id)
                ->with([
                    'city:id,name',
                    'submission.responses.field:id,label,key',
                ])
                ->orderBy('id')
                ->get();

            foreach ($subs as $sub) {
                $responses = [];
                if ($sub->submission && $sub->submission->relationLoaded('responses')) {
                    foreach ($sub->submission->responses as $r) {
                        $responses[] = [
                            'label' => $r->field?->label ?? $r->field?->key ?? 'Поле',
                            'key' => $r->field?->key,
                            'value' => $r->value,
                        ];
                    }
                }
                $payload['stage1_city_forms'][] = [
                    'city_id' => $sub->city_id,
                    'city_name' => $sub->city?->name ?? ('Город #'.$sub->city_id),
                    'submission_id' => $sub->lms_form_submission_id,
                    'submitted_at' => $sub->submission?->created_at?->toIso8601String(),
                    'responses' => $responses,
                ];
            }
        }

        if (Schema::hasTable('tour_cabinet_contest_stage2_answers') && Schema::hasTable('tour_cabinet_contest_stage2_questions')) {
            $directionId = $user->tourCabinetContestProgress?->direction_id;
            $questions = $this->activeStage2QuestionsQuery($directionId)->orderBy('sort_order')->orderBy('id')->get();
            if ($questions->isNotEmpty()) {
                $answersByQ = TourCabinetContestStage2Answer::query()
                    ->where('user_id', $user->id)
                    ->whereIn('question_id', $questions->pluck('id'))
                    ->get()
                    ->keyBy('question_id');

                foreach ($questions as $q) {
                    $a = $answersByQ->get($q->id);
                    $payload['stage2_qa'][] = [
                        'question_id' => $q->id,
                        'question_body' => $q->body,
                        'answer_text' => $a?->answer_text ?? '',
                        'updated_at' => $a?->updated_at?->toIso8601String(),
                    ];
                }
            }
        }

        if (Schema::hasTable('applications')) {
            $payload['tour_applications'] = $this->tourApplicationsPayload($user);
        }

        return $payload;
    }

    /**
     * Одна строка для CSV (ключи — заголовки колонок).
     *
     * @return array<string, string|int|float|null>
     */
    public function buildExportRow(User $user, ?int $cityFilterId): array
    {
        $user->loadMissing([
            'tourCabinetContestProgress',
            'tourCabinetContestCitySubmissions.city',
            'tourCabinetDocuments',
        ]);

        $row = [
            'user_id' => $user->id,
            'email' => (string) ($user->email ?? ''),
            'fio' => $this->displayName($user),
            'phone' => (string) ($user->phone ?? ''),
            'registered_tour_lc' => $user->is_tour_cabinet_user ? 'да' : 'нет',
            'lms_vshgr' => ($user->lms_profiles_exists ?? false) ? 'да' : 'нет',
        ];

        $progress = $user->tourCabinetContestProgress;
        $dirMap = Direction::allProjectMap();
        $row['direction_id'] = $progress?->direction_id ?? '';
        $row['direction_label'] = $progress && $progress->direction_id
            ? ($dirMap[$progress->direction_id] ?? '')
            : '';
        $row['contest_current_stage'] = $progress ? (int) $progress->current_stage : '';
        $row['contest_stage2_submitted_at'] = $progress?->stage2_submitted_at?->format('Y-m-d H:i:s') ?? '';
        $row['contest_selected_city_names'] = '';
        if ($progress && is_array($progress->selected_city_ids) && $progress->selected_city_ids !== []) {
            $row['contest_selected_city_names'] = City::query()
                ->whereIn('id', $progress->selected_city_ids)
                ->orderBy('name')
                ->pluck('name')
                ->implode('; ');
        }

        $row['stage3_text'] = (string) ($progress?->stage3_text ?? '');
        $row['stage3_video_url'] = (string) ($progress?->stage3_video_url ?? '');
        $row['stage3_attachment_name'] = (string) ($progress?->stage3_attachment_original_name ?? '');

        $subs = $user->tourCabinetContestCitySubmissions;
        if ($cityFilterId !== null) {
            $subs = $subs->where('city_id', $cityFilterId)->values();
        }

        foreach ($subs as $sub) {
            $cityLabel = $sub->city?->name ?? 'city_'.$sub->city_id;
            $prefix = 's1_'.$sub->city_id.'_';
            $row[$prefix.'city_name'] = $cityLabel;
            $row[$prefix.'submission_at'] = $sub->submission?->created_at?->format('Y-m-d H:i:s') ?? '';

            if ($sub->submission) {
                $sub->submission->loadMissing('responses.field');
                foreach ($sub->submission->responses as $r) {
                    $row[$prefix.'f'.$r->lms_form_field_id] = (string) ($r->value ?? '');
                }
            }
        }

        $dirId = $progress?->direction_id;
        $questions = $this->activeStage2QuestionsQuery($dirId)->orderBy('sort_order')->orderBy('id')->get();
        $answersByQ = TourCabinetContestStage2Answer::query()
            ->where('user_id', $user->id)
            ->whereIn('question_id', $questions->pluck('id'))
            ->pluck('answer_text', 'question_id');
        foreach ($questions as $q) {
            $row['s2_q'.$q->id] = (string) ($answersByQ[$q->id] ?? '');
        }

        $apps = Schema::hasTable('applications') ? $this->tourApplicationsPayload($user) : [];
        $row['tour_applications_count'] = count($apps);
        $row['tour_applications'] = collect($apps)->map(function (array $a) {
            return '#'.$a['id'].' '.$a['tour_title'].' ['.$a['status_label'].']';
        })->implode("\n");

        foreach ($apps as $i => $a) {
            if ($i >= 10) {
                break;
            }
            $n = $i + 1;
            $row["app_{$n}_id"] = $a['id'];
            $row["app_{$n}_tour"] = $a['tour_title'];
            $row["app_{$n}_status"] = $a['status_label'];
            $row["app_{$n}_name"] = $a['name'] ?? '';
            $row["app_{$n}_phone"] = $a['phone'] ?? '';
            $row["app_{$n}_created"] = $a['created_at'] ?? '';
        }

        if (Schema::hasTable('tour_cabinet_documents')) {
            foreach ($user->tourCabinetDocuments as $doc) {
                $key = 'doc_'.$doc->type.'_status';
                $row[$key] = (string) ($doc->status ?? '');
                $row['doc_'.$doc->type.'_file'] = (string) ($doc->original_name ?? '');
            }
        }

        return $row;
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

    /**
     * @return list<array<string, mixed>>
     */
    private function tourApplicationsPayload(User $user): array
    {
        $email = trim((string) ($user->email ?? ''));
        if ($email === '') {
            return [];
        }

        return Application::query()
            ->where('type', 'tour')
            ->whereRaw('LOWER(TRIM(email)) = LOWER(?)', [$email])
            ->with([
                'tour:id,title',
                'tourDeparture:id,tour_id,start_date,end_date',
                'tourDeparture.tour:id,title',
            ])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function (Application $app): array {
                return [
                    'id' => $app->id,
                    'name' => $app->name,
                    'email' => $app->email,
                    'phone' => $app->phone,
                    'status' => $app->status,
                    'status_label' => match ($app->status) {
                        'new', 'in_progress' => 'На проверке',
                        'approved' => 'Одобрена',
                        'rejected' => 'Отклонена',
                        default => 'На проверке',
                    },
                    'tour_title' => $this->tourApplicationTitle($app),
                    'date_range' => $this->formatTourApplicationDateRange($app),
                    'data_json' => json_encode($app->data ?? new \stdClass, JSON_UNESCAPED_UNICODE),
                    'created_at' => $app->created_at?->format('Y-m-d H:i:s'),
                ];
            })
            ->all();
    }

    private function displayName(User $user): string
    {
        $composed = trim(implode(' ', array_filter(
            [$user->last_name, $user->first_name, $user->patronymic],
            fn ($v) => $v !== null && $v !== ''
        )));

        return $composed !== '' ? $composed : (string) ($user->name ?: '');
    }

    private function tourApplicationTitle(Application $app): string
    {
        foreach ([
            $app->tour?->title,
            $app->tourDeparture?->tour?->title,
            data_get($app->data, 'tour_title'),
        ] as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return Application::TYPES['tour'] ?? 'Заявка на тур';
    }

    private function formatTourApplicationDateRange(Application $app): ?string
    {
        $dep = $app->tourDeparture;
        if (! $dep || ! $dep->start_date || ! $dep->end_date) {
            return null;
        }

        $start = $dep->start_date;
        $end = $dep->end_date;
        if ($start->format('Y-m') === $end->format('Y-m')) {
            return $start->format('d.m').'–'.$end->format('d.m.Y');
        }

        return $start->format('d.m.Y').'–'.$end->format('d.m.Y');
    }
}
