<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\TourCabinetDocument;
use App\Models\User;
use App\Services\TourCabinetDocumentReviewService;
use App\Support\PostAuthRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TourCabinetTourUsersController extends Controller
{
    public function index(Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));

        $query = User::query()
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
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('email', 'like', '%'.$q.'%')
                    ->orWhere('name', 'like', '%'.$q.'%')
                    ->orWhere('last_name', 'like', '%'.$q.'%')
                    ->orWhere('first_name', 'like', '%'.$q.'%');
            });
        }

        if (Schema::hasTable('tour_cabinet_documents')) {
            $query->with(['tourCabinetDocuments' => fn ($rel) => $rel->orderBy('type')]);
        }

        $users = $query->paginate(25)->through(fn (User $user) => $this->userListRow($user));

        return Inertia::render('Admin/TourCabinet/TourUsers/Index', [
            'users' => $users,
            'filters' => ['q' => $q],
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
        ]);
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
