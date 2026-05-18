<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGrant;
use App\Models\Lms\LmsGrantDocument;
use App\Models\Lms\LmsGrantEnrollment;
use App\Models\Lms\LmsGrantEnrollmentComment;
use App\Models\Lms\LmsProfile;
use App\Services\StyledXlsxBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GrantController extends Controller
{
    private const PARTICIPANT_STATUS_LABELS = [
        'new' => 'Новая',
        'called' => 'Созвонились',
        'no_answer' => 'Не дозвонились',
        'interested' => 'Заинтересован',
        'not_interested' => 'Не заинтересован',
        'done' => 'Обработано',
    ];

    public function index(LmsEvent $event): Response
    {
        $grants = LmsGrant::where('lms_event_id', $event->id)
            ->withCount('enrollments')
            ->orderBy('position')
            ->paginate(15);

        return Inertia::render('Lms/Admin/Grants/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'grants' => $grants,
        ]);
    }

    public function create(LmsEvent $event): Response
    {
        return Inertia::render('Lms/Admin/Grants/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
        ]);
    }

    public function store(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(LmsGrant::TYPES))],
            'city' => ['nullable', 'array'],
            'city.*' => ['string', 'max:255'],
            'description' => ['nullable', 'string'],
            'application_start' => ['nullable', 'date'],
            'application_end' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);

        $validated['city'] = !empty($validated['city']) ? $validated['city'] : null;

        $grant = LmsGrant::create([
            ...$validated,
            'lms_event_id' => $event->id,
        ]);

        $this->syncDocuments($request, $grant);

        return redirect()->route('lms.admin.grants.index', $event->slug);
    }

    public function edit(LmsEvent $event, LmsGrant $grant): Response
    {
        if ($grant->lms_event_id !== $event->id) {
            abort(404);
        }

        $grant->load('documents');

        return Inertia::render('Lms/Admin/Grants/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'grant' => $grant,
        ]);
    }

    public function update(Request $request, LmsEvent $event, LmsGrant $grant): RedirectResponse
    {
        if ($grant->lms_event_id !== $event->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(LmsGrant::TYPES))],
            'city' => ['nullable', 'array'],
            'city.*' => ['string', 'max:255'],
            'description' => ['nullable', 'string'],
            'application_start' => ['nullable', 'date'],
            'application_end' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);

        $validated['city'] = !empty($validated['city']) ? $validated['city'] : null;

        $grant->update($validated);
        $this->syncDocuments($request, $grant);

        return redirect()->route('lms.admin.grants.index', $event->slug);
    }

    public function enrollments(LmsEvent $event, LmsGrant $grant): JsonResponse
    {
        if ($grant->lms_event_id !== $event->id) {
            abort(404);
        }

        $enrollments = LmsGrantEnrollment::where('lms_grant_id', $grant->id)
            ->with([
                'user:id,name,last_name,first_name,patronymic,email,phone',
                'adminComments.admin:id,name',
            ])
            ->orderByDesc('created_at')
            ->get();
        $profiles = LmsProfile::where('lms_event_id', $event->id)
            ->whereIn('user_id', $enrollments->pluck('user_id')->filter()->unique())
            ->get(['user_id', 'city', 'position', 'organization', 'project_description'])
            ->keyBy('user_id');

        $enrollments = $enrollments
            ->map(fn ($e) => [
                'id' => $e->id,
                'user' => $e->user?->only(['id', 'name', 'last_name', 'first_name', 'patronymic', 'email', 'phone']),
                'profile' => $profiles->get($e->user_id)?->only(['city', 'position', 'organization', 'project_description']),
                'latest_admin_status' => $e->adminComments->first()?->status,
                'admin_comments' => $this->formatAdminCommentsForPayload($e),
                'created_at' => $e->created_at?->format('d.m.Y H:i'),
            ]);

        return response()->json($enrollments);
    }

    public function export(Request $request, LmsEvent $event): StreamedResponse
    {
        $grantId = (int) $request->query('grant_id', 0);
        $grants = LmsGrant::where('lms_event_id', $event->id)
            ->when($grantId > 0, fn ($query) => $query->where('id', $grantId))
            ->with([
                'enrollments.user:id,name,last_name,first_name,patronymic,email,phone',
                'enrollments.adminComments.admin:id,name',
            ])
            ->orderBy('position')
            ->orderBy('title')
            ->get();

        $profiles = LmsProfile::where('lms_event_id', $event->id)
            ->whereIn('user_id', $grants->flatMap(fn (LmsGrant $grant) => $grant->enrollments->pluck('user_id'))->filter()->unique())
            ->get(['user_id', 'city', 'position', 'organization', 'project_description'])
            ->keyBy('user_id');

        $xlsx = $this->buildGrantsXlsx($grants, $profiles);
        $fileSuffix = $grantId > 0 && $grants->first()
            ? '-grant-'.$grants->first()->id
            : '';

        return response()->streamDownload(function () use ($xlsx) {
            echo $xlsx;
        }, 'lms-grants-'.$event->slug.$fileSuffix.'-participants.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function participants(LmsEvent $event, LmsGrant $grant): Response
    {
        if ($grant->lms_event_id !== $event->id) {
            abort(404);
        }

        $enrollments = LmsGrantEnrollment::where('lms_grant_id', $grant->id)
            ->with([
                'user:id,name,last_name,first_name,patronymic,email,phone',
                'adminComments.admin:id,name',
            ])
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();
        $profiles = LmsProfile::where('lms_event_id', $event->id)
            ->whereIn('user_id', $enrollments->getCollection()->pluck('user_id')->filter()->unique())
            ->get(['user_id', 'city', 'position', 'organization', 'project_description'])
            ->keyBy('user_id');

        $enrollments->getCollection()->transform(fn (LmsGrantEnrollment $enrollment) => [
            'id' => $enrollment->id,
            'user' => $enrollment->user?->only(['id', 'name', 'last_name', 'first_name', 'patronymic', 'email', 'phone']),
            'profile' => $profiles->get($enrollment->user_id)?->only(['city', 'position', 'organization', 'project_description']),
            'latest_admin_status' => $enrollment->adminComments->first()?->status,
            'admin_comments' => $this->formatAdminCommentsForPayload($enrollment),
            'created_at' => $enrollment->created_at?->format('d.m.Y H:i'),
        ]);

        return Inertia::render('Lms/Admin/Grants/Participants', [
            'event' => $event->only(['id', 'slug', 'title']),
            'grant' => array_merge(
                $grant->only(['id', 'title', 'type', 'application_start', 'application_end', 'is_active']),
                [
                    'type_label' => LmsGrant::TYPES[$grant->type] ?? $grant->type,
                    'application_period' => $this->formatApplicationPeriod($grant),
                    'application_status' => $this->applicationStatus($grant),
                ],
            ),
            'enrollments' => $enrollments,
            'statusLabels' => self::PARTICIPANT_STATUS_LABELS,
        ]);
    }

    public function storeParticipantComment(Request $request, LmsEvent $event, LmsGrant $grant, LmsGrantEnrollment $enrollment): RedirectResponse
    {
        if ($grant->lms_event_id !== $event->id || $enrollment->lms_grant_id !== $grant->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(self::PARTICIPANT_STATUS_LABELS))],
            'comment' => ['nullable', 'string', 'max:5000'],
        ]);
        $comment = trim((string) ($validated['comment'] ?? ''));
        $status = $validated['status'] ?? null;

        if ($comment === '' && ! $status) {
            return redirect()->back()->with('error', 'Добавьте комментарий или выберите статус');
        }

        LmsGrantEnrollmentComment::create([
            'lms_grant_enrollment_id' => $enrollment->id,
            'admin_id' => Auth::id(),
            'status' => $status,
            'comment' => $comment !== '' ? $comment : null,
        ]);

        return redirect()->back()->with('success', 'Комментарий сохранён');
    }

    public function destroy(LmsEvent $event, LmsGrant $grant): RedirectResponse
    {
        if ($grant->lms_event_id !== $event->id) {
            abort(404);
        }

        $grant->delete();

        return redirect()->route('lms.admin.grants.index', $event->slug);
    }

    private function syncDocuments(Request $request, LmsGrant $grant): void
    {
        $keepIds = $request->input('keep_document_ids', []);

        $grant->documents()
            ->whereNotIn('id', $keepIds)
            ->each(function (LmsGrantDocument $doc) {
                $disk = config('filesystems.upload_disk');
                Storage::disk($disk)->delete($doc->file_path);
                $doc->delete();
            });

        $maxPos = $grant->documents()->max('position') ?? -1;

        if ($request->hasFile('new_documents')) {
            $disk = config('filesystems.upload_disk');

            foreach ($request->file('new_documents') as $file) {
                $path = $file->store('grant-documents/' . $grant->id, $disk);
                $grant->documents()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'position' => ++$maxPos,
                ]);
            }
        }

        if ($request->filled('media_document_urls')) {
            $names = $request->input('media_document_names', []);
            foreach ($request->input('media_document_urls') as $i => $url) {
                $grant->documents()->create([
                    'file_path' => $url,
                    'original_name' => $names[$i] ?? basename(parse_url($url, PHP_URL_PATH) ?: $url),
                    'position' => ++$maxPos,
                ]);
            }
        }
    }

    private function formatApplicationPeriod(LmsGrant $grant): string
    {
        $start = $grant->application_start?->format('d.m.Y');
        $end = $grant->application_end?->format('d.m.Y');

        return collect([$start, $end])->filter()->join(' - ') ?: 'Без срока';
    }

    private function applicationStatus(LmsGrant $grant): string
    {
        if (! $grant->is_active) {
            return 'Неактивна';
        }

        $now = now();
        if ($grant->application_start && $now->lt($grant->application_start)) {
            return 'Приём ещё не начался';
        }
        if ($grant->application_end && $now->gt($grant->application_end)) {
            return 'Приём завершён';
        }

        return 'Идёт приём заявок';
    }

    private function buildGrantsXlsx($grants, $profiles): string
    {
        $summaryRows = [[
            'Название возможности', 'Тип', 'Приём заявок', 'Статус приёма', 'Участников',
        ]];
        $participantRows = [[
            'Название возможности', 'Тип', 'Приём заявок', 'Статус приёма',
            'ФИО участника', 'Email', 'Телефон', 'Проект', 'Город', 'Должность', 'Организация',
            'Статус обработки', 'Комментарии администраторов', 'Дата участия',
        ]];

        foreach ($grants as $grant) {
            $period = $this->formatApplicationPeriod($grant);
            $status = $this->applicationStatus($grant);
            $type = LmsGrant::TYPES[$grant->type] ?? $grant->type;

            $summaryRows[] = [$grant->title, $type, $period, $status, (string) $grant->enrollments->count()];

            if ($grant->enrollments->isEmpty()) {
                $participantRows[] = [$grant->title, $type, $period, $status, '', '', '', '', '', '', '', '', '', ''];
                continue;
            }

            foreach ($grant->enrollments as $enrollment) {
                $user = $enrollment->user;
                $profile = $profiles->get($enrollment->user_id);
                $latestStatus = $enrollment->adminComments->first()?->status;
                $participantRows[] = [
                    $grant->title,
                    $type,
                    $period,
                    $status,
                    $this->userFullName($user),
                    $user?->email ?? '',
                    $user?->phone ?? '',
                    $profile?->project_description ?? '',
                    $profile?->city ?? '',
                    $profile?->position ?? '',
                    $profile?->organization ?? '',
                    $latestStatus ? (self::PARTICIPANT_STATUS_LABELS[$latestStatus] ?? $latestStatus) : '',
                    $this->formatAdminCommentsForExport($enrollment),
                    $enrollment->created_at?->format('d.m.Y H:i') ?? '',
                ];
            }
        }

        return app(StyledXlsxBuilder::class)->build([
            [
                'name' => 'Сводка',
                'title' => 'Сводка по возможностям',
                'rows' => $summaryRows,
                'widths' => [42, 16, 24, 24, 14],
            ],
            [
                'name' => 'Участники',
                'title' => 'Участники возможностей',
                'rows' => $participantRows,
                'widths' => [42, 16, 24, 24, 34, 28, 20, 58, 22, 28, 32, 24, 58, 20],
            ],
        ]);
    }

    private function userFullName($user): string
    {
        if (! $user) {
            return '';
        }

        return collect([$user->last_name, $user->first_name, $user->patronymic])
            ->filter()
            ->join(' ') ?: (string) $user->name;
    }

    private function formatAdminCommentsForPayload(LmsGrantEnrollment $enrollment): array
    {
        return $enrollment->adminComments
            ->map(fn (LmsGrantEnrollmentComment $comment) => [
                'id' => $comment->id,
                'status' => $comment->status,
                'status_label' => $comment->status ? (self::PARTICIPANT_STATUS_LABELS[$comment->status] ?? $comment->status) : null,
                'comment' => $comment->comment,
                'admin_name' => $comment->admin?->name,
                'created_at' => $comment->created_at?->format('d.m.Y H:i'),
            ])
            ->values()
            ->all();
    }

    private function formatAdminCommentsForExport(LmsGrantEnrollment $enrollment): string
    {
        return $enrollment->adminComments
            ->reverse()
            ->map(function (LmsGrantEnrollmentComment $comment) {
                $status = $comment->status ? (self::PARTICIPANT_STATUS_LABELS[$comment->status] ?? $comment->status) : null;
                $parts = array_filter([
                    $comment->created_at?->format('d.m.Y H:i'),
                    $comment->admin?->name,
                    $status ? '['.$status.']' : null,
                    $comment->comment,
                ]);

                return implode(' ', $parts);
            })
            ->implode("\n");
    }
}
