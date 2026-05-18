<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsAssignment;
use App\Models\Lms\LmsAssignmentComment;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsAssignmentReview;
use App\Models\Lms\LmsAssignmentSubmission;
use App\Models\Lms\LmsAssignmentSubmissionRead;
use App\Models\Lms\LmsAssignmentTask;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsCourseStage;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsStageBlock;
use App\Models\Lms\LmsStageProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class AssignmentController extends Controller
{
    public function index(Request $request, LmsEvent $event): Response
    {
        $viewerId = (int) auth()->id();
        $accessLevel = LmsProfile::backofficeAccessForEvent(auth()->user(), $event);
        $canManageAssignments = $accessLevel === 'admin';
        $withUpdates = $request->boolean('with_updates', false);

        $assignmentsQuery = $event->assignments()
            ->withCount('submissions')
            ->orderBy('created_at', 'desc');

        if ($withUpdates) {
            $assignmentsQuery->whereHas('submissions', function ($query) use ($viewerId) {
                $query->whereNotNull('participant_last_activity_at')
                    ->where(function ($unread) use ($viewerId) {
                        $unread->whereDoesntHave('reads', function ($read) use ($viewerId) {
                            $read->where('user_id', $viewerId)->whereNotNull('last_read_at');
                        })->orWhereHas('reads', function ($read) use ($viewerId) {
                            $read->where('user_id', $viewerId)
                                ->whereColumn('last_read_at', '<', 'lms_assignment_submissions.participant_last_activity_at');
                        });
                    });
            });
        }

        $assignments = $assignmentsQuery->paginate(15)->withQueryString();
        $assignmentIds = $assignments->getCollection()->pluck('id')->all();
        $unreadCounts = collect();

        if ($assignmentIds !== []) {
            $unreadCounts = LmsAssignmentSubmission::query()
                ->whereIn('lms_assignment_id', $assignmentIds)
                ->whereNotNull('participant_last_activity_at')
                ->where(function ($unread) use ($viewerId) {
                    $unread->whereDoesntHave('reads', function ($read) use ($viewerId) {
                        $read->where('user_id', $viewerId)->whereNotNull('last_read_at');
                    })->orWhereHas('reads', function ($read) use ($viewerId) {
                        $read->where('user_id', $viewerId)
                            ->whereColumn('last_read_at', '<', 'lms_assignment_submissions.participant_last_activity_at');
                    });
                })
                ->selectRaw('lms_assignment_id, COUNT(*) as unread_count')
                ->groupBy('lms_assignment_id')
                ->pluck('unread_count', 'lms_assignment_id');
        }

        $assignments->getCollection()->transform(function (LmsAssignment $assignment) use ($unreadCounts) {
            $assignment->setAttribute('unread_submissions_count', (int) ($unreadCounts[$assignment->id] ?? 0));

            return $assignment;
        });

        return Inertia::render('Lms/Admin/Assignments/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'assignments' => $assignments,
            'canManageAssignments' => $canManageAssignments,
            'filters' => [
                'with_updates' => $withUpdates,
            ],
        ]);
    }

    public function create(LmsEvent $event): Response
    {
        return Inertia::render('Lms/Admin/Assignments/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'assignment' => null,
        ]);
    }

    public function store(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate($this->assignmentRules());

        $validated['lms_event_id'] = $event->id;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['completion_mode'] ??= 'on_review';
        $validated['gamification_points'] = (int) ($validated['gamification_points'] ?? 0);

        $templateFiles = $this->normalizeTemplateFiles($validated['template_files'] ?? []);
        unset($validated['template_files']);

        $assignment = LmsAssignment::create($validated);
        $assignment->setTemplateFiles($templateFiles);
        $assignment->save();

        $this->syncTasks($assignment, $request->input('tasks', []), $request, $event);

        return redirect()->route('lms.admin.assignments.index', $event)->with('success', 'Задание создано');
    }

    public function show(Request $request, LmsEvent $event, LmsAssignment $assignment): Response
    {
        $this->ensureAssignmentBelongsToEvent($assignment, $event);
        $viewerId = (int) auth()->id();
        $accessLevel = LmsProfile::backofficeAccessForEvent(auth()->user(), $event);
        $canReviewAssignments = in_array($accessLevel, ['admin', 'gamification_points_only'], true);
        $onlyUnread = $request->boolean('only_unread', false);
        $search = trim((string) $request->query('search', ''));
        $statusFilter = (string) $request->query('status', '');
        if (! in_array($statusFilter, ['approved', 'submitted', 'revision', 'new', 'overdue'], true)) {
            $statusFilter = '';
        }
        $directionFilter = trim((string) $request->query('direction', ''));
        if (! in_array($directionFilter, LmsProfile::DIRECTIONS, true)) {
            $directionFilter = '';
        }
        $facultyFilter = trim((string) $request->query('faculty', ''));
        if (! in_array($facultyFilter, LmsProfile::FACULTIES, true)
            || ($directionFilter !== '' && $directionFilter !== LmsProfile::DIRECTION_ENTREPRENEURS)) {
            $facultyFilter = '';
        }

        $assignment->load('tasks');

        $unreadScope = function ($unread) use ($viewerId) {
            $unread->whereDoesntHave('reads', function ($read) use ($viewerId) {
                $read->where('user_id', $viewerId)->whereNotNull('last_read_at');
            })->orWhereHas('reads', function ($read) use ($viewerId) {
                $read->where('user_id', $viewerId)
                    ->whereColumn('last_read_at', '<', 'lms_assignment_submissions.participant_last_activity_at');
            });
        };

        $baseSubmissionsQuery = $assignment->submissions();

        if ($search !== '') {
            $baseSubmissionsQuery->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'ilike', '%' . $search . '%')
                    ->orWhere('email', 'ilike', '%' . $search . '%')
                    ->orWhere('last_name', 'ilike', '%' . $search . '%')
                    ->orWhere('first_name', 'ilike', '%' . $search . '%')
                    ->orWhere('patronymic', 'ilike', '%' . $search . '%');
            });
        }

        $this->applyProfileProgramFilters($baseSubmissionsQuery, $event, $directionFilter, $facultyFilter);

        $isAssignmentOverdue = $assignment->deadline !== null && $assignment->deadline->lt(now());
        $overdueSubmissionScope = fn ($query) => $query->whereNotIn('status', ['submitted', 'approved', 'resubmitted']);

        $statusCounts = [
            'approved' => (clone $baseSubmissionsQuery)->where('status', 'approved')->count(),
            'submitted' => (clone $baseSubmissionsQuery)->where('status', 'submitted')->count(),
            'revision' => (clone $baseSubmissionsQuery)->where('status', 'revision')->count(),
            'new' => (clone $baseSubmissionsQuery)
                ->whereNotNull('participant_last_activity_at')
                ->where($unreadScope)
                ->count(),
            'overdue' => $isAssignmentOverdue
                ? (clone $baseSubmissionsQuery)->where($overdueSubmissionScope)->count()
                : 0,
        ];

        $submissionsQuery = (clone $baseSubmissionsQuery)
            ->with(['user:id,name,email', 'reviews.reviewer:id,name', 'comments.user:id,name', 'answers.task'])
            ->orderBy('created_at', 'desc');

        if ($onlyUnread) {
            $submissionsQuery->whereNotNull('participant_last_activity_at')
                ->where($unreadScope);
        }

        if ($statusFilter === 'new') {
            $submissionsQuery->whereNotNull('participant_last_activity_at')
                ->where($unreadScope);
        } elseif ($statusFilter === 'overdue') {
            $submissionsQuery->where($overdueSubmissionScope);
        } elseif ($statusFilter !== '') {
            $submissionsQuery->where('status', $statusFilter);
        }

        $submissions = $submissionsQuery->paginate(15)->withQueryString();
        $submissionIds = $submissions->getCollection()->pluck('id')->all();
        $submissionUserIds = $submissions->getCollection()->pluck('user_id')->filter()->unique()->values();
        $readMap = collect();

        if ($submissionIds !== []) {
            $readMap = LmsAssignmentSubmissionRead::query()
                ->whereIn('lms_assignment_submission_id', $submissionIds)
                ->where('user_id', $viewerId)
                ->pluck('last_read_at', 'lms_assignment_submission_id');
        }

        $profiles = LmsProfile::where('lms_event_id', $event->id)
            ->whereIn('user_id', $submissionUserIds)
            ->get(['user_id', 'project_description', 'city', 'position', 'organization', 'direction', 'faculty'])
            ->keyBy('user_id');
        $linkedCourseIds = $this->assignmentLinkedCourseIds($assignment);
        $courseEnrollments = $linkedCourseIds->isNotEmpty()
            ? LmsCourseEnrollment::whereIn('user_id', $submissionUserIds)
                ->whereIn('lms_course_id', $linkedCourseIds)
                ->whereNotIn('status', ['pending', 'rejected'])
                ->with('course:id,title')
                ->get()
                ->groupBy('user_id')
            : collect();

        $submissions->getCollection()->transform(function (LmsAssignmentSubmission $submission) use ($readMap, $isAssignmentOverdue, $profiles, $courseEnrollments) {
            $readAtRaw = $readMap->get($submission->id);
            $readAt = $readAtRaw ? Carbon::parse($readAtRaw) : null;
            $participantActivityAt = $submission->participant_last_activity_at;
            $hasUnread = $participantActivityAt !== null && ($readAt === null || $readAt->lt($participantActivityAt));
            $profile = $profiles->get($submission->user_id);

            $submission->setAttribute('read_at', $readAt ? $readAt->toIso8601String() : null);
            $submission->setAttribute('has_unread', $hasUnread);
            $submission->setAttribute('is_overdue', $isAssignmentOverdue && ! in_array($submission->status, ['submitted', 'approved', 'resubmitted'], true));
            $submission->setAttribute('participant_context', [
                'courses' => $courseEnrollments->get($submission->user_id, collect())
                    ->map(fn (LmsCourseEnrollment $enrollment) => [
                        'id' => $enrollment->course?->id,
                        'title' => $enrollment->course?->title,
                        'status' => $enrollment->status,
                    ])
                    ->filter(fn (array $course) => $course['id'] !== null)
                    ->values()
                    ->all(),
                'profile' => $profile ? [
                    'project_description' => $profile->project_description,
                    'city' => $profile->city,
                    'position' => $profile->position,
                    'organization' => $profile->organization,
                    'direction' => $profile->direction,
                    'direction_label' => $profile->direction ? (LmsProfile::DIRECTION_LABELS[$profile->direction] ?? $profile->direction) : null,
                    'faculty' => $profile->faculty,
                    'faculty_label' => $profile->faculty ? (LmsProfile::FACULTY_LABELS[$profile->faculty] ?? $profile->faculty) : null,
                ] : null,
            ]);

            return $submission;
        });

        return Inertia::render('Lms/Admin/Assignments/Submissions', [
            'event' => $event->only(['id', 'slug', 'title']),
            'assignment' => $assignment,
            'submissions' => $submissions,
            'statusCounts' => $statusCounts,
            'canReviewAssignments' => $canReviewAssignments,
            'filters' => [
                'only_unread' => $onlyUnread,
                'search' => $search,
                'status' => $statusFilter,
                'direction' => $directionFilter,
                'faculty' => $facultyFilter,
            ],
            'directionLabels' => LmsProfile::DIRECTION_LABELS,
            'facultyLabels' => LmsProfile::FACULTY_LABELS,
            'directionFaculties' => LmsProfile::DIRECTION_FACULTIES,
        ]);
    }

    public function edit(LmsEvent $event, LmsAssignment $assignment): Response
    {
        $this->ensureAssignmentBelongsToEvent($assignment, $event);

        $assignment->load('tasks');
        $assignment->setAttribute('template_files', $assignment->templateFiles());

        return Inertia::render('Lms/Admin/Assignments/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'assignment' => $assignment,
        ]);
    }

    public function update(Request $request, LmsEvent $event, LmsAssignment $assignment): RedirectResponse
    {
        $this->ensureAssignmentBelongsToEvent($assignment, $event);

        $validated = $request->validate($this->assignmentRules());

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['completion_mode'] ??= $assignment->completion_mode;
        $validated['gamification_points'] = (int) ($validated['gamification_points'] ?? 0);

        $templateFiles = $this->normalizeTemplateFiles($validated['template_files'] ?? []);
        unset($validated['template_files']);

        $assignment->update($validated);
        $assignment->setTemplateFiles($templateFiles);
        $assignment->save();

        $this->syncTasks($assignment, $request->input('tasks', []), $request, $event);

        return redirect()->route('lms.admin.assignments.index', $event)->with('success', 'Задание обновлено');
    }

    public function destroy(LmsEvent $event, LmsAssignment $assignment): RedirectResponse
    {
        $this->ensureAssignmentBelongsToEvent($assignment, $event);

        $assignment->delete();

        return redirect()->route('lms.admin.assignments.index', $event)->with('success', 'Задание удалено');
    }

    public function review(Request $request, LmsEvent $event, LmsAssignment $assignment, LmsAssignmentSubmission $submission): RedirectResponse
    {
        $this->ensureAssignmentBelongsToEvent($assignment, $event);

        if ($submission->lms_assignment_id !== $assignment->id) {
            abort(404);
        }

        $validated = $request->validate([
            'decision' => ['required', 'string', 'in:approve,revision,reject'],
            'comment' => ['nullable', 'string'],
            'files' => ['nullable', 'array', 'max:5'],
            'files.*' => ['file', 'max:20480'],
            'file_urls' => ['nullable', 'array', 'max:5'],
            'file_urls.*' => ['string', 'url'],
        ]);

        $disk = config('filesystems.upload_disk');
        $files = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('assignment-reviews/' . $assignment->id, $disk);
                $files[] = ['name' => $file->getClientOriginalName(), 'path' => Storage::disk($disk)->url($path)];
            }
        }
        foreach ($request->input('file_urls', []) as $url) {
            $files[] = ['name' => basename(parse_url($url, PHP_URL_PATH) ?: 'file'), 'path' => $url];
        }

        LmsAssignmentReview::create([
            'lms_assignment_submission_id' => $submission->id,
            'reviewer_id' => Auth::id(),
            'comment' => $validated['comment'] ?? null,
            'files' => $files ?: null,
            'decision' => $validated['decision'],
        ]);

        $statusMap = ['approve' => 'approved', 'reject' => 'rejected', 'revision' => 'revision'];
        $newStatus = $statusMap[$validated['decision']] ?? $validated['decision'];
        $submission->update(['status' => $newStatus]);

        if ($newStatus === 'approved') {
            $this->markLinkedStagesCompleted($assignment, $submission->user_id);
        }

        $this->markSubmissionAsReadForCurrentUser($submission);

        return redirect()->back()->with('success', 'Решение сохранено');
    }

    public function comment(Request $request, LmsEvent $event, LmsAssignment $assignment, LmsAssignmentSubmission $submission): RedirectResponse
    {
        $this->ensureAssignmentBelongsToEvent($assignment, $event);

        if ($submission->lms_assignment_id !== $assignment->id) {
            abort(404);
        }

        $validated = $request->validate([
            'text' => ['required', 'string', 'max:5000'],
            'files' => ['nullable', 'array', 'max:5'],
            'files.*' => ['file', 'max:20480'],
            'file_urls' => ['nullable', 'array', 'max:5'],
            'file_urls.*' => ['string', 'url'],
        ]);

        $disk = config('filesystems.upload_disk');
        $files = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('assignment-comments/' . $assignment->id, $disk);
                $files[] = ['name' => $file->getClientOriginalName(), 'path' => Storage::disk($disk)->url($path)];
            }
        }
        foreach ($request->input('file_urls', []) as $url) {
            $files[] = ['name' => basename(parse_url($url, PHP_URL_PATH) ?: 'file'), 'path' => $url];
        }

        LmsAssignmentComment::create([
            'lms_assignment_submission_id' => $submission->id,
            'user_id' => Auth::id(),
            'text' => $validated['text'],
            'files' => $files ?: null,
        ]);

        $this->markSubmissionAsReadForCurrentUser($submission);

        return redirect()->back()->with('success', 'Комментарий добавлен');
    }

    public function markRead(LmsEvent $event, LmsAssignment $assignment, LmsAssignmentSubmission $submission): RedirectResponse
    {
        $this->ensureAssignmentBelongsToEvent($assignment, $event);

        if ($submission->lms_assignment_id !== $assignment->id) {
            abort(404);
        }

        $this->markSubmissionAsReadForCurrentUser($submission);

        return redirect()->back();
    }

    private function assignmentRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'template_file' => ['nullable', 'string', 'max:500'],
            'template_file_name' => ['nullable', 'string', 'max:255'],
            'template_files' => ['nullable', 'array'],
            'template_files.*.path' => ['required_with:template_files', 'string', 'max:2000'],
            'template_files.*.name' => ['nullable', 'string', 'max:255'],
            'completion_mode' => ['sometimes', 'string', 'in:on_submit,on_review'],
            'deadline' => ['nullable', 'date'],
            'gamification_points' => ['nullable', 'integer', 'min:0'],
            'tasks' => ['nullable', 'array'],
            'tasks.*.title' => ['required', 'string', 'max:255'],
            'tasks.*.description' => ['nullable', 'string'],
            'tasks.*.response_type' => ['required', 'string', 'in:text,link,file'],
            'tasks.*.template_file' => ['nullable', 'string', 'max:500'],
            'tasks.*.template_file_name' => ['nullable', 'string', 'max:255'],
            'tasks.*.position' => ['nullable', 'integer'],
        ];
    }

    /**
     * @param  array<int, mixed>  $files
     * @return array<int, array{name: string, path: string}>
     */
    private function normalizeTemplateFiles(array $files): array
    {
        return collect($files)
            ->map(function ($file) {
                if (! is_array($file)) {
                    return null;
                }

                $path = $file['path'] ?? null;
                if (! is_string($path) || trim($path) === '') {
                    return null;
                }

                $name = is_string($file['name'] ?? null) && trim($file['name']) !== ''
                    ? trim($file['name'])
                    : basename(parse_url($path, PHP_URL_PATH) ?: 'template');

                return ['name' => $name, 'path' => $path];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function syncTasks(LmsAssignment $assignment, array $tasks, Request $request, LmsEvent $event): void
    {
        $existingIds = $assignment->tasks()->pluck('id')->toArray();
        $incomingIds = [];

        foreach ($tasks as $index => $taskData) {
            if (empty($taskData['title'])) {
                continue;
            }

            $attrs = [
                'title' => $taskData['title'],
                'description' => $taskData['description'] ?? null,
                'response_type' => $taskData['response_type'] ?? 'file',
                'template_file' => $taskData['template_file'] ?? null,
                'template_file_name' => $taskData['template_file_name'] ?? null,
                'position' => $taskData['position'] ?? $index,
            ];

            if (!empty($taskData['id']) && in_array($taskData['id'], $existingIds)) {
                $task = LmsAssignmentTask::find($taskData['id']);
                $task->update($attrs);
                $incomingIds[] = $task->id;
            } else {
                $attrs['lms_assignment_id'] = $assignment->id;
                $task = LmsAssignmentTask::create($attrs);
                $incomingIds[] = $task->id;
            }
        }

        $toDelete = array_diff($existingIds, $incomingIds);
        if ($toDelete) {
            LmsAssignmentTask::whereIn('id', $toDelete)->delete();
        }
    }

    private function ensureAssignmentBelongsToEvent(LmsAssignment $assignment, LmsEvent $event): void
    {
        if ($assignment->lms_event_id !== $event->id) {
            abort(404);
        }
    }

    private function markLinkedStagesCompleted(LmsAssignment $assignment, int $userId): void
    {
        $stageIds = LmsCourseStage::where('lms_assignment_id', $assignment->id)->pluck('id')
            ->merge(LmsStageBlock::where('lms_assignment_id', $assignment->id)->pluck('lms_course_stage_id'))
            ->unique();

        foreach ($stageIds as $stageId) {
            LmsStageProgress::updateOrCreate(
                ['lms_course_stage_id' => $stageId, 'user_id' => $userId],
                ['status' => 'completed', 'completed_at' => now()]
            );

            $stage = LmsCourseStage::find($stageId);
            if ($stage) {
                $totalStages = LmsCourseStage::where('lms_course_id', $stage->lms_course_id)->count();
                $completedStages = LmsStageProgress::whereIn(
                    'lms_course_stage_id',
                    LmsCourseStage::where('lms_course_id', $stage->lms_course_id)->pluck('id')
                )->where('user_id', $userId)->where('status', 'completed')->count();

                if ($completedStages >= $totalStages) {
                    LmsCourseEnrollment::where('lms_course_id', $stage->lms_course_id)
                        ->where('user_id', $userId)
                        ->update(['status' => 'completed', 'completed_at' => now()]);
                }
            }
        }
    }

    private function assignmentLinkedCourseIds(LmsAssignment $assignment)
    {
        return LmsCourseStage::where('lms_assignment_id', $assignment->id)
            ->pluck('lms_course_id')
            ->merge(
                LmsStageBlock::where('lms_stage_blocks.lms_assignment_id', $assignment->id)
                    ->join('lms_course_stages', 'lms_course_stages.id', '=', 'lms_stage_blocks.lms_course_stage_id')
                    ->pluck('lms_course_stages.lms_course_id')
            )
            ->filter()
            ->unique()
            ->values();
    }

    private function markSubmissionAsReadForCurrentUser(LmsAssignmentSubmission $submission): void
    {
        $userId = Auth::id();
        if (! $userId) {
            return;
        }

        LmsAssignmentSubmissionRead::updateOrCreate(
            [
                'lms_assignment_submission_id' => $submission->id,
                'user_id' => $userId,
            ],
            [
                'last_read_at' => now(),
            ]
        );
    }

    private function applyProfileProgramFilters($submissionsQuery, LmsEvent $event, string $direction, string $faculty): void
    {
        if ($direction !== '') {
            $submissionsQuery->whereIn('user_id', function ($query) use ($event, $direction) {
                $query->select('user_id')
                    ->from('lms_profiles')
                    ->where('lms_event_id', $event->id)
                    ->where('direction', $direction);
            });
        }

        if ($faculty !== '') {
            $submissionsQuery->whereIn('user_id', function ($query) use ($event, $faculty) {
                $query->select('user_id')
                    ->from('lms_profiles')
                    ->where('lms_event_id', $event->id)
                    ->where('faculty', $faculty);
            });
        }
    }
}
