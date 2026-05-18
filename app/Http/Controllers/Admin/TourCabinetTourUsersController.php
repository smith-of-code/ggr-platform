<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\City;
use App\Models\TourCabinetCommerceArchive;
use App\Models\TourCabinetContestArchive;
use App\Models\TourCabinetDocument;
use App\Models\User;
use App\Services\Admin\TourCabinetClientContestDataService;
use App\Services\Admin\TourCabinetCommerceArchiveExportRowsService;
use App\Services\TourCabinetDocumentReviewService;
use App\Support\PostAuthRedirect;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TourCabinetTourUsersController extends Controller
{
    public function __construct(
        private readonly TourCabinetClientContestDataService $contestData,
        private readonly TourCabinetCommerceArchiveExportRowsService $commerceExportRows,
    ) {}

    public function index(Request $request): Response
    {
        $query = $this->withTourClientListColumns($this->filteredTourUsersQuery($request))
            ->orderByDesc('updated_at')
            ->orderByDesc('id');

        if (Schema::hasTable('tour_cabinet_documents')) {
            $query->with(['tourCabinetDocuments' => fn ($rel) => $rel->orderBy('type')]);
        }
        if (Schema::hasTable('tour_cabinet_contest_progress')) {
            $query->with(['tourCabinetContestProgress', 'tourCabinetContestCitySubmissions.city']);
        }
        if (Schema::hasTable('tour_cabinet_commerce_archives')) {
            $query->with(['tourCabinetCommerceArchives']);
        }

        $users = $query->paginate(25)->withQueryString()->through(fn (User $user) => $this->userListRow($user));

        return Inertia::render('Admin/TourCabinet/TourUsers/Index', [
            'users' => $users,
            'filters' => [
                'q' => trim((string) $request->query('q', '')),
                'city_id' => $request->query('city_id') !== null && $request->query('city_id') !== ''
                    ? (string) (int) $request->query('city_id')
                    : '',
                'segment' => $this->normalizedSegment($request),
            ],
            'exportCityOptions' => $this->contestData->cityOptionsForExport(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $cityIdInt = $this->validatedCityId($request);

        $query = $this->withTourClientListColumns($this->filteredTourUsersQuery($request))
            ->orderByDesc('users.id');

        $users = $query->get();
        $rows = $users->map(fn (User $u) => $this->commerceExportRows->appendRowsForUser(
            $this->contestData->buildExportRow($u, $cityIdInt),
            $u,
            $cityIdInt,
        ));

        $allKeys = collect();
        foreach ($rows as $row) {
            $allKeys = $allKeys->merge(array_keys($row));
        }
        $orderedKeys = $this->orderExportKeys($allKeys->unique()->values()->all());
        if ($orderedKeys === []) {
            $orderedKeys = ['user_id', 'email', 'fio', 'phone'];
        }

        $cityPart = $cityIdInt !== null ? '-city-'.$cityIdInt : '-all';
        $filename = 'tour-cabinet-clients'.$cityPart.'-'.date('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($orderedKeys, $rows) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            $headerLabels = array_map(fn (string $k) => $this->csvColumnTitleRu($k), $orderedKeys);
            fputcsv($out, $headerLabels, ';');
            foreach ($rows as $row) {
                $line = [];
                foreach ($orderedKeys as $k) {
                    $v = $row[$k] ?? '';
                    if (is_scalar($v) || $v === null) {
                        $line[] = $v === null ? '' : (string) $v;
                    } else {
                        $line[] = json_encode($v, JSON_UNESCAPED_UNICODE);
                    }
                }
                fputcsv($out, $line, ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function show(User $user): Response
    {
        $this->ensureTourCabinetClient($user);

        if (Schema::hasTable('tour_cabinet_documents')) {
            $user->loadMissing(['tourCabinetDocuments' => fn ($rel) => $rel->orderBy('type')]);
        }

        return Inertia::render('Admin/TourCabinet/TourUsers/Show', [
            'user' => $this->userPayload($user),
            'documentRows' => $this->documentRowsForUser($user),
            'contest' => $this->contestData->contestPayloadForUser($user),
            'contestArchives' => $this->contestArchivesForUser($user),
            'commerceArchives' => $this->commerceArchivesForUser($user),
        ]);
    }

    /**
     * Архивные конкурсные заявки участника (read-only снапшоты). Подгружаются для админ-карточки
     * клиента; даже после `admin.settings.contest-reset` архив остаётся видим (см. фичу
     * tour-cabinet-archives, требование «не пропадают заявки из админки»).
     *
     * @return list<array<string, mixed>>
     */
    private function contestArchivesForUser(User $user): array
    {
        if (! Schema::hasTable('tour_cabinet_contest_archives')) {
            return [];
        }

        return TourCabinetContestArchive::query()
            ->where('user_id', $user->id)
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (TourCabinetContestArchive $a) => [
                'id' => $a->id,
                'submitted_at' => $a->submitted_at?->toIso8601String(),
                'status' => $a->status,
                'direction_label' => data_get($a->payload, 'progress.direction_label'),
                'payload' => $a->payload,
            ])
            ->values()
            ->all();
    }

    /**
     * Архивные коммерческие заявки участника (множественные, иммутабельные).
     *
     * @return list<array<string, mixed>>
     */
    private function commerceArchivesForUser(User $user): array
    {
        if (! Schema::hasTable('tour_cabinet_commerce_archives')) {
            return [];
        }

        return TourCabinetCommerceArchive::query()
            ->where('user_id', $user->id)
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (TourCabinetCommerceArchive $a) => [
                'id' => $a->id,
                'submitted_at' => $a->submitted_at?->toIso8601String(),
                'status' => $a->status,
                'city_name' => data_get($a->payload, 'city.name'),
                'tour_title' => data_get($a->payload, 'tour.title'),
                'payload' => $a->payload,
            ])
            ->values()
            ->all();
    }

    public function downloadDocument(User $user, TourCabinetDocument $document): StreamedResponse
    {
        $this->ensureTourCabinetClient($user);
        $this->ensureDocumentBelongsToUser($user, $document);

        if (! $document->hasFile()) {
            abort(404);
        }

        $disk = config('filesystems.upload_disk', 'public');

        return Storage::disk($disk)->download(
            $document->file_path,
            $document->original_name ?: 'document'
        );
    }

    public function approveDocument(
        User $user,
        TourCabinetDocument $document,
        TourCabinetDocumentReviewService $reviewService,
    ): RedirectResponse {
        $this->ensureTourCabinetClient($user);
        $this->ensureDocumentBelongsToUser($user, $document);

        try {
            $reviewService->approve($document);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Документ подтверждён.');
    }

    public function annulDocument(
        Request $request,
        User $user,
        TourCabinetDocument $document,
        TourCabinetDocumentReviewService $reviewService,
    ): RedirectResponse {
        $this->ensureTourCabinetClient($user);
        $this->ensureDocumentBelongsToUser($user, $document);

        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:3', 'max:2000'],
        ], [
            'comment.required' => 'Укажите комментарий для участника.',
        ]);

        try {
            $reviewService->annul($document, $validated['comment']);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Документ отклонён, участнику отправлено уведомление на email.');
    }

    /**
     * @return Builder<User>
     */
    private function filteredTourUsersQuery(Request $request): Builder
    {
        $query = $this->tourUsersDirectoryQuery();

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('email', 'like', '%'.$q.'%')
                    ->orWhere('name', 'like', '%'.$q.'%')
                    ->orWhere('last_name', 'like', '%'.$q.'%')
                    ->orWhere('first_name', 'like', '%'.$q.'%');
            });
        }

        $cityId = $this->validatedCityId($request);
        if ($cityId !== null) {
            $this->applyCityFilterToQuery($query, $cityId);
        }

        $segment = $this->normalizedSegment($request);
        $this->applySegmentFilterToQuery($query, $segment);

        return $query;
    }

    /**
     * @param  Builder<User>  $query
     */
    private function withTourClientListColumns(Builder $query): Builder
    {
        if (Schema::hasTable('lms_profiles')) {
            $query->withExists('lmsProfiles');
        }

        return $query;
    }

    private function validatedCityId(Request $request): ?int
    {
        $cityRaw = $request->query('city_id');
        if ($cityRaw === null || $cityRaw === '') {
            return null;
        }
        $cityIdInt = (int) $cityRaw;
        if ($cityIdInt <= 0 || ! City::query()->whereKey($cityIdInt)->exists()) {
            abort(404);
        }

        return $cityIdInt;
    }

    private function normalizedSegment(Request $request): string
    {
        $s = (string) $request->query('segment', 'all');

        return in_array($s, ['tour_only', 'lms'], true) ? $s : 'all';
    }

    /**
     * @param  Builder<User>  $query
     */
    private function applyCityFilterToQuery(Builder $query, int $cityIdInt): void
    {
        if (! Schema::hasTable('tour_cabinet_contest_city_submissions')
            && ! Schema::hasTable('tour_cabinet_contest_progress')
            && ! Schema::hasTable('tour_cabinet_commerce_archives')) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function ($outer) use ($cityIdInt) {
            $hasClause = false;
            if (Schema::hasTable('tour_cabinet_contest_city_submissions')) {
                $outer->whereHas(
                    'tourCabinetContestCitySubmissions',
                    fn ($s) => $s->where('city_id', $cityIdInt)
                );
                $hasClause = true;
            }
            if (Schema::hasTable('tour_cabinet_contest_progress')) {
                if ($hasClause) {
                    $outer->orWhereHas(
                        'tourCabinetContestProgress',
                        fn ($p) => $p->whereJsonContains('selected_city_ids', $cityIdInt)
                    );
                } else {
                    $outer->whereHas(
                        'tourCabinetContestProgress',
                        fn ($p) => $p->whereJsonContains('selected_city_ids', $cityIdInt)
                    );
                }
                $hasClause = true;
            }
            if (Schema::hasTable('tour_cabinet_commerce_archives')) {
                if ($hasClause) {
                    $outer->orWhereHas(
                        'tourCabinetCommerceArchives',
                        fn ($a) => $a->where('city_id', $cityIdInt)
                    );
                } else {
                    $outer->whereHas(
                        'tourCabinetCommerceArchives',
                        fn ($a) => $a->where('city_id', $cityIdInt)
                    );
                }
            }
        });
    }

    /**
     * @param  Builder<User>  $query
     */
    private function applySegmentFilterToQuery(Builder $query, string $segment): void
    {
        if ($segment === 'all') {
            return;
        }

        if ($segment === 'tour_only') {
            $query->where('is_tour_cabinet_user', true);
            if (Schema::hasTable('lms_profiles')) {
                $query->whereDoesntHave('lmsProfiles');
            }

            return;
        }

        if ($segment === 'lms') {
            if (! Schema::hasTable('lms_profiles')) {
                $query->whereRaw('1 = 0');

                return;
            }
            $query->whereHas('lmsProfiles');
        }
    }

    /**
     * @return Builder<User>
     */
    private function tourUsersDirectoryQuery(): Builder
    {
        return User::query()
            ->where(function ($outer) {
                $outer->where('is_tour_cabinet_user', true);
                if (Schema::hasTable('lms_profiles')) {
                    $outer->orWhereExists(function ($sub) {
                        $sub->selectRaw('1')
                            ->from('lms_profiles')
                            ->whereColumn('lms_profiles.user_id', 'users.id');
                    });
                }
                if (Schema::hasTable('applications')) {
                    $outer->orWhereExists(function ($sub) {
                        $sub->selectRaw('1')
                            ->from('applications')
                            ->where('applications.type', 'tour')
                            ->whereRaw('LOWER(TRIM(applications.email)) = LOWER(TRIM(users.email))');
                    });
                }
            });
    }

    /**
     * Человекочитаемый заголовок колонки CSV (русский).
     */
    private function csvColumnTitleRu(string $key): string
    {
        $fixed = [
            'user_id' => 'ID пользователя',
            'email' => 'Email',
            'fio' => 'ФИО',
            'phone' => 'Телефон',
            'registered_tour_lc' => 'Регистрация в ЛК туров (да/нет)',
            'lms_vshgr' => 'Доступ ВШГР / LMS (да/нет)',
            'direction_key' => 'Код направления конкурса',
            'direction_label' => 'Направление конкурса',
            'contest_current_stage' => 'Текущий этап конкурса',
            'contest_stage2_submitted_at' => 'Дата отправки этапа 2',
            'contest_selected_city_names' => 'Выбранные города (этап 1)',
            'stage3_text' => 'Этап 3 — текст ответа',
            'stage3_video_url' => 'Этап 3 — ссылка на видео',
            'stage3_attachment_name' => 'Этап 3 — имя файла',
            'tour_applications_count' => 'Число заявок на тур',
            'tour_applications' => 'Заявки на туры (сводка)',
            'commerce_archives_count' => 'Архивов коммерческих туров (количество)',
            'commerce_archives_summary' => 'Коммерческие туры — сводка',
        ];
        if (isset($fixed[$key])) {
            return $fixed[$key];
        }

        if (preg_match('/^commerce_arch_(\d+)_(id|city|tour|submitted_at|status|lms_responses)$/', $key, $m)) {
            $n = (int) $m[1];
            $field = $m[2];
            $labels = [
                'id' => 'ID архива',
                'city' => 'Город',
                'tour' => 'Тур',
                'submitted_at' => 'Дата отправки',
                'status' => 'Статус',
                'lms_responses' => 'Ответы на анкету (JSON)',
            ];

            return 'Коммерческий тур №'.$n.' — '.$labels[$field];
        }

        if (preg_match('/^s1_(\d+)_city_name$/', $key, $m)) {
            return 'Этап 1 — город №'.$m[1].' — название';
        }
        if (preg_match('/^s1_(\d+)_submission_at$/', $key, $m)) {
            return 'Этап 1 — город №'.$m[1].' — дата отправки анкеты';
        }
        if (preg_match('/^s1_(\d+)_f(\d+)$/', $key, $m)) {
            return 'Этап 1 — город №'.$m[1].' — поле формы №'.$m[2];
        }

        if (preg_match('/^s2_q(\d+)$/', $key, $m)) {
            return 'Этап 2 — ответ на вопрос №'.$m[1];
        }

        if (preg_match('/^app_(\d+)_(id|tour|status|name|phone|created)$/', $key, $m)) {
            $n = (int) $m[1];
            $field = $m[2];
            $labels = [
                'id' => 'ID заявки',
                'tour' => 'Тур',
                'status' => 'Статус заявки',
                'name' => 'Имя в заявке',
                'phone' => 'Телефон в заявке',
                'created' => 'Дата создания заявки',
            ];

            return 'Заявка '.$n.' — '.$labels[$field];
        }

        if (preg_match('/^doc_([a-z0-9_]+)_(status|file)$/', $key, $m)) {
            $type = $m[1];
            $suffix = $m[2] === 'status' ? 'статус' : 'имя файла';
            $typeLabel = TourCabinetDocument::typeLabel($type);

            return 'Документ: '.$typeLabel.' — '.$suffix;
        }

        return $key;
    }

    /**
     * @param  list<string>  $keys
     * @return list<string>
     */
    private function orderExportKeys(array $keys): array
    {
        $priority = [
            'user_id', 'email', 'fio', 'phone',
            'registered_tour_lc', 'lms_vshgr',
            'direction_key', 'direction_label',
            'contest_current_stage', 'contest_stage2_submitted_at', 'contest_selected_city_names',
            'stage3_text', 'stage3_video_url', 'stage3_attachment_name',
            'tour_applications_count', 'tour_applications',
            'commerce_archives_count', 'commerce_archives_summary',
        ];
        foreach (range(1, 10) as $n) {
            foreach (['id', 'city', 'tour', 'submitted_at', 'status', 'lms_responses'] as $field) {
                $priority[] = "commerce_arch_{$n}_{$field}";
            }
        }
        $first = array_values(array_intersect($priority, $keys));
        $rest = collect($keys)->diff($first)->sort()->values()->all();

        return array_merge($first, $rest);
    }

    private function ensureTourCabinetClient(User $user): void
    {
        if (! $this->userQualifiesForTourUsersDirectory($user)) {
            abort(404);
        }
    }

    private function userQualifiesForTourUsersDirectory(User $user): bool
    {
        if (PostAuthRedirect::canAccessTourCabinet($user)) {
            return true;
        }

        if (! Schema::hasTable('applications')) {
            return false;
        }

        $email = trim((string) ($user->email ?? ''));

        if ($email === '') {
            return false;
        }

        return Application::query()
            ->where('type', 'tour')
            ->whereRaw('LOWER(TRIM(email)) = LOWER(?)', [$email])
            ->exists();
    }

    private function ensureDocumentBelongsToUser(User $user, TourCabinetDocument $document): void
    {
        if ($document->user_id !== $user->id) {
            abort(404);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function userListRow(User $user): array
    {
        $display = trim(implode(' ', array_filter(
            [$user->last_name, $user->first_name, $user->patronymic],
            fn ($v) => $v !== null && $v !== ''
        )));

        $overview = Schema::hasTable('tour_cabinet_documents')
            ? $this->documentsOverview($user)
            : ['label' => '—', 'tone' => 'neutral', 'approved' => 0, 'required' => 0];

        return [
            'id' => $user->id,
            'email' => $user->email,
            'display_name' => $display !== '' ? $display : (string) ($user->name ?: 'Участник'),
            'documents_overview' => $overview,
            'contest_summary' => $this->contestData->listContestSummary($user),
            'access' => [
                'tour_cabinet_registered' => (bool) $user->is_tour_cabinet_user,
                'lms_vshgr' => (bool) ($user->lms_profiles_exists ?? false),
            ],
        ];
    }

    /**
     * @return array{label: string, tone: string, approved: int, required: int, pending_review: int, awaiting_client: int}
     */
    private function documentsOverview(User $user): array
    {
        $required = TourCabinetDocument::allowedTypes();
        $byType = $user->tourCabinetDocuments->keyBy('type');
        $approved = 0;
        $pendingReview = 0;
        $awaitingClient = 0;

        foreach ($required as $type) {
            $doc = $byType->get($type);
            if (! $doc) {
                continue;
            }
            if ($doc->status === TourCabinetDocument::STATUS_APPROVED && $doc->hasFile()) {
                $approved++;
            } elseif ($doc->status === TourCabinetDocument::STATUS_PENDING_REVIEW && $doc->hasFile()) {
                $pendingReview++;
            } elseif ($doc->status === TourCabinetDocument::STATUS_ANNULLED && ! $doc->hasFile()) {
                $awaitingClient++;
            }
        }

        $totalRequired = count($required);
        $tone = 'neutral';
        $label = 'Нет загрузок';

        if ($approved === $totalRequired) {
            $label = 'Все документы подтверждены';
            $tone = 'success';
        } elseif ($pendingReview > 0) {
            $label = 'Есть файлы на проверке';
            $tone = 'warning';
        } elseif ($awaitingClient > 0) {
            $label = 'Ожидается повторная загрузка';
            $tone = 'amber';
        } elseif ($approved > 0 || $byType->isNotEmpty()) {
            $label = 'Частично загружено';
            $tone = 'neutral';
        }

        return [
            'label' => $label,
            'tone' => $tone,
            'approved' => $approved,
            'required' => $totalRequired,
            'pending_review' => $pendingReview,
            'awaiting_client' => $awaitingClient,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function documentRowsForUser(User $user): array
    {
        if (! Schema::hasTable('tour_cabinet_documents')) {
            return [];
        }

        $byType = $user->tourCabinetDocuments->keyBy('type');

        return collect(TourCabinetDocument::allowedTypes())->map(function (string $type) use ($byType) {
            $d = $byType->get($type);
            if (! $d) {
                return [
                    'type' => $type,
                    'type_label' => TourCabinetDocument::typeLabel($type),
                    'id' => null,
                    'has_file' => false,
                    'original_name' => null,
                    'status' => null,
                    'status_label' => 'Не загружен',
                    'admin_comment' => null,
                    'reviewed_at' => null,
                    'can_approve' => false,
                    'can_annul' => false,
                ];
            }

            $hasFile = $d->hasFile();
            $status = $d->status;
            $statusLabel = match ($status) {
                TourCabinetDocument::STATUS_APPROVED => 'Подтверждён',
                TourCabinetDocument::STATUS_PENDING_REVIEW => 'На проверке',
                TourCabinetDocument::STATUS_ANNULLED => $hasFile ? 'Отклонён' : 'Отклонён, ждём новый файл',
                default => $status,
            };

            return [
                'type' => $type,
                'type_label' => TourCabinetDocument::typeLabel($type),
                'id' => $d->id,
                'has_file' => $hasFile,
                'original_name' => $d->original_name,
                'status' => $status,
                'status_label' => $statusLabel,
                'admin_comment' => $d->admin_comment,
                'reviewed_at' => $d->reviewed_at?->toIso8601String(),
                'can_approve' => $hasFile && $status === TourCabinetDocument::STATUS_PENDING_REVIEW,
                'can_annul' => $hasFile && in_array($status, [
                    TourCabinetDocument::STATUS_PENDING_REVIEW,
                    TourCabinetDocument::STATUS_APPROVED,
                ], true),
            ];
        })->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        $display = trim(implode(' ', array_filter(
            [$user->last_name, $user->first_name, $user->patronymic],
            fn ($v) => $v !== null && $v !== ''
        )));

        return [
            'id' => $user->id,
            'email' => $user->email,
            'display_name' => $display !== '' ? $display : (string) ($user->name ?: 'Участник'),
            'phone' => $user->phone,
        ];
    }
}
