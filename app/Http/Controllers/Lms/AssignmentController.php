<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsAssignment;
use App\Models\Lms\LmsAssignmentComment;
use App\Models\Lms\LmsAssignmentSubmission;
use App\Models\Lms\LmsAssignmentTask;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsCourseStage;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsStageBlock;
use App\Models\Lms\LmsStageProgress;
use App\Models\Lms\LmsSubmissionAnswer;
use App\Models\UploadedMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssignmentController extends Controller
{
    public function index(Request $request, LmsEvent $event): Response
    {
        $user = auth()->user();

        $enrolledCourseIds = LmsCourseEnrollment::where('user_id', $user->id)
            ->whereHas('course', fn($q) => $q->where('lms_event_id', $event->id))
            ->pluck('lms_course_id');

        $courseAssignmentIds = DB::table('lms_stage_blocks')
            ->join('lms_course_stages', 'lms_course_stages.id', '=', 'lms_stage_blocks.lms_course_stage_id')
            ->whereIn('lms_course_stages.lms_course_id', $enrolledCourseIds)
            ->whereNotNull('lms_stage_blocks.lms_assignment_id')
            ->distinct()
            ->pluck('lms_stage_blocks.lms_assignment_id');

        $allLinkedAssignmentIds = DB::table('lms_stage_blocks')
            ->whereNotNull('lms_assignment_id')
            ->distinct()
            ->pluck('lms_assignment_id');

        $assignmentsQuery = LmsAssignment::where('lms_event_id', $event->id)
            ->where('is_active', true)
            ->where(function ($q) use ($courseAssignmentIds, $allLinkedAssignmentIds) {
                $q->whereIn('id', $courseAssignmentIds)
                  ->orWhereNotIn('id', $allLinkedAssignmentIds);
            });

        if ($search = $request->get('search')) {
            $assignmentsQuery->where('title', 'ilike', '%' . $search . '%');
        }

        $assignmentsQuery->orderByRaw('CASE WHEN deadline IS NULL THEN 1 ELSE 0 END')
            ->orderBy('deadline');

        $assignmentsPaginator = $assignmentsQuery->paginate(12)->withQueryString();
        $assignmentIds = collect($assignmentsPaginator->items())->pluck('id');

        $submissions = LmsAssignmentSubmission::whereIn('lms_assignment_id', $assignmentIds)
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('lms_assignment_id');

        $assignmentsWithStatus = collect($assignmentsPaginator->items())->map(function ($assignment) use ($submissions) {
            $submission = $submissions->get($assignment->id);
            return [
                'assignment' => $assignment->only(['id', 'title', 'description', 'deadline']),
                'submission' => $submission?->only(['id', 'status', 'created_at']),
            ];
        });

        $assignmentsPaginator->setCollection($assignmentsWithStatus);

        return Inertia::render('Lms/Assignments/Index', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'assignments' => $assignmentsPaginator,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(LmsEvent $event, LmsAssignment $assignment): Response
    {
        if ($assignment->lms_event_id !== $event->id) {
            abort(404);
        }
        $user = auth()->user();

        $isLinkedToCourse = DB::table('lms_stage_blocks')
            ->where('lms_assignment_id', $assignment->id)
            ->exists();

        if ($isLinkedToCourse) {
            $hasAccess = DB::table('lms_stage_blocks')
                ->join('lms_course_stages', 'lms_course_stages.id', '=', 'lms_stage_blocks.lms_course_stage_id')
                ->join('lms_course_enrollments', function ($join) use ($user) {
                    $join->on('lms_course_enrollments.lms_course_id', '=', 'lms_course_stages.lms_course_id')
                         ->where('lms_course_enrollments.user_id', '=', $user->id);
                })
                ->where('lms_stage_blocks.lms_assignment_id', $assignment->id)
                ->exists();

            if (!$hasAccess) {
                abort(403);
            }
        }

        $assignment->load('tasks');

        $submission = LmsAssignmentSubmission::where('lms_assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->with(['reviews.reviewer:id,name', 'comments.user:id,name', 'answers.task'])
            ->first();

        $disk = config('filesystems.upload_disk');
        $isS3 = (config("filesystems.disks.{$disk}.driver") === 's3');

        return Inertia::render('Lms/Assignments/Show', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'assignment' => array_merge(
                $assignment->only(['id', 'title', 'description', 'template_file', 'template_file_name', 'deadline', 'completion_mode']),
                ['tasks' => $assignment->tasks]
            ),
            'submission' => $submission,
            'presignedUpload' => $isS3 ? [
                'presignedUrlEndpoint' => route('lms.upload.presigned-url', ['event' => $event->slug]),
                'confirmEndpoint' => route('lms.upload.confirm', ['event' => $event->slug]),
            ] : null,
        ]);
    }

    public function submit(Request $request, LmsEvent $event, LmsAssignment $assignment): RedirectResponse
    {
        if ($assignment->lms_event_id !== $event->id) {
            abort(404);
        }
        $user = auth()->user();

        $assignment->load('tasks');
        $hasTasks = $assignment->tasks->isNotEmpty();

        $validated = $request->validate([
            'text_content' => ['nullable', 'string'],
            'link' => ['nullable', 'url', 'max:500'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file'],
            'file_urls' => ['nullable', 'array'],
            'file_urls.*' => ['string', 'url'],
            'answers' => ['nullable', 'array'],
            'answers.*.task_id' => ['required_with:answers', 'integer'],
            'answers.*.text_content' => ['nullable', 'string'],
            'answers.*.link' => ['nullable', 'url', 'max:500'],
        ]);

        $disk = config('filesystems.upload_disk');

        $legacyFiles = $this->collectFileUrls($request, 'assignments/' . $assignment->id);

        $status = $assignment->completion_mode === 'on_submit' ? 'approved' : 'submitted';

        $submission = LmsAssignmentSubmission::updateOrCreate(
            ['lms_assignment_id' => $assignment->id, 'user_id' => $user->id],
            [
                'text_content' => $hasTasks ? null : ($validated['text_content'] ?? null),
                'link' => $hasTasks ? null : ($validated['link'] ?? null),
                'files' => $hasTasks ? null : $legacyFiles,
                'status' => $status,
                'participant_last_activity_at' => now(),
            ]
        );

        if ($hasTasks) {
            $this->saveAnswers($submission, $assignment, $request, $validated['answers'] ?? []);
        }

        if ($status === 'approved') {
            $this->markLinkedStagesCompleted($assignment, $user);
        }

        return redirect()->back();
    }

    public function draft(Request $request, LmsEvent $event, LmsAssignment $assignment): RedirectResponse
    {
        if ($assignment->lms_event_id !== $event->id) {
            abort(404);
        }
        $user = auth()->user();

        $assignment->load('tasks');
        $hasTasks = $assignment->tasks->isNotEmpty();

        $validated = $request->validate([
            'text_content' => ['nullable', 'string'],
            'link' => ['nullable', 'url', 'max:500'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file'],
            'file_urls' => ['nullable', 'array'],
            'file_urls.*' => ['string', 'url'],
            'answers' => ['nullable', 'array'],
            'answers.*.task_id' => ['required_with:answers', 'integer'],
            'answers.*.text_content' => ['nullable', 'string'],
            'answers.*.link' => ['nullable', 'url', 'max:500'],
        ]);

        $disk = config('filesystems.upload_disk');

        $existing = LmsAssignmentSubmission::where('lms_assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->first();

        $legacyFiles = [];
        if (!$hasTasks) {
            $legacyFiles = $this->collectFileUrls($request, 'assignments/' . $assignment->id);
            $legacyFiles = $existing ? array_merge($existing->files ?? [], $legacyFiles) : $legacyFiles;
        }

        $submission = LmsAssignmentSubmission::updateOrCreate(
            ['lms_assignment_id' => $assignment->id, 'user_id' => $user->id],
            [
                'text_content' => $hasTasks ? null : ($validated['text_content'] ?? null),
                'link' => $hasTasks ? null : ($validated['link'] ?? null),
                'files' => $hasTasks ? null : $legacyFiles,
                'status' => 'draft',
            ]
        );

        if ($hasTasks) {
            $this->saveAnswers($submission, $assignment, $request, $validated['answers'] ?? []);
        }

        return redirect()->back();
    }

    public function comment(Request $request, LmsEvent $event, LmsAssignment $assignment): RedirectResponse
    {
        if ($assignment->lms_event_id !== $event->id) {
            abort(404);
        }

        $user = auth()->user();
        $submission = LmsAssignmentSubmission::where('lms_assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

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
            'user_id' => $user->id,
            'text' => $validated['text'],
            'files' => $files ?: null,
        ]);

        $submission->update([
            'participant_last_activity_at' => now(),
        ]);

        return redirect()->back();
    }

    public function downloadTemplate(LmsEvent $event, LmsAssignment $assignment): StreamedResponse
    {
        if ($assignment->lms_event_id !== $event->id || !$assignment->template_file) {
            abort(404);
        }

        [$disk, $path] = $this->resolveFileLocation($assignment->template_file);
        $name = $assignment->template_file_name ?: basename(parse_url($assignment->template_file, PHP_URL_PATH) ?: 'template');

        return Storage::disk($disk)->download($path, $name);
    }

    public function downloadTaskTemplate(LmsEvent $event, LmsAssignment $assignment, int $task): StreamedResponse
    {
        if ($assignment->lms_event_id !== $event->id) {
            abort(404);
        }

        $taskModel = LmsAssignmentTask::where('id', $task)
            ->where('lms_assignment_id', $assignment->id)
            ->firstOrFail();

        if (!$taskModel->template_file) {
            abort(404);
        }

        [$disk, $path] = $this->resolveFileLocation($taskModel->template_file);
        $name = $taskModel->template_file_name ?: basename(parse_url($taskModel->template_file, PHP_URL_PATH) ?: 'template');

        return Storage::disk($disk)->download($path, $name);
    }

    public function update(
        Request $request,
        LmsEvent $event,
        LmsAssignment $assignment,
        LmsAssignmentSubmission $submission
    ): RedirectResponse {
        if ($assignment->lms_event_id !== $event->id || $submission->lms_assignment_id !== $assignment->id) {
            abort(404);
        }
        $user = auth()->user();
        if ($submission->user_id !== $user->id || $submission->lms_assignment_id !== $assignment->id) {
            abort(403);
        }

        $validated = $request->validate([
            'text_content' => ['nullable', 'string'],
            'link' => ['nullable', 'url', 'max:500'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file'],
            'file_urls' => ['nullable', 'array'],
            'file_urls.*' => ['string', 'url'],
        ]);

        $files = $submission->files ?? [];
        $newFiles = $this->collectFileUrls($request, 'assignments/' . $assignment->id);
        $files = array_merge($files, $newFiles);

        $submission->update([
            'text_content' => $validated['text_content'] ?? $submission->text_content,
            'link' => $validated['link'] ?? $submission->link,
            'files' => $files,
            'status' => 'resubmitted',
            'participant_last_activity_at' => now(),
        ]);

        return redirect()->back();
    }

    private function saveAnswers(LmsAssignmentSubmission $submission, LmsAssignment $assignment, Request $request, array $answers): void
    {
        $disk = config('filesystems.upload_disk');
        $taskMap = $assignment->tasks->keyBy('id');

        foreach ($answers as $index => $answerData) {
            $taskId = $answerData['task_id'] ?? null;
            if (!$taskId || !$taskMap->has($taskId)) {
                continue;
            }

            $task = $taskMap->get($taskId);
            $files = null;

            if ($task->response_type === 'file') {
                $uploaded = [];
                if ($request->hasFile("answers.{$index}.files")) {
                    foreach ($request->file("answers.{$index}.files") as $file) {
                        $path = $file->store('assignments/' . $assignment->id, $disk);
                        $uploaded[] = [
                            'name' => $file->getClientOriginalName(),
                            'path' => Storage::disk($disk)->url($path),
                        ];
                    }
                }
                foreach ($answerData['file_urls'] ?? [] as $fileUrl) {
                    $uploaded[] = [
                        'name' => $fileUrl['name'] ?? basename(parse_url($fileUrl['url'] ?? '', PHP_URL_PATH) ?: 'file'),
                        'path' => $fileUrl['url'] ?? $fileUrl,
                    ];
                }
                $files = $uploaded ?: null;
            }

            LmsSubmissionAnswer::updateOrCreate(
                [
                    'lms_assignment_submission_id' => $submission->id,
                    'lms_assignment_task_id' => $taskId,
                ],
                [
                    'text_content' => $task->response_type === 'text' ? ($answerData['text_content'] ?? null) : null,
                    'link' => $task->response_type === 'link' ? ($answerData['link'] ?? null) : null,
                    'files' => $task->response_type === 'file' ? $files : null,
                ]
            );
        }
    }

    private function collectFileUrls(Request $request, string $directory): array
    {
        $disk = config('filesystems.upload_disk');
        $urls = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store($directory, $disk);
                $urls[] = Storage::disk($disk)->url($path);
            }
        }

        foreach ($request->input('file_urls', []) as $url) {
            $urls[] = $url;
        }

        return $urls;
    }

    /**
     * @return array{0: string, 1: string} [disk, path]
     */
    private function resolveFileLocation(string $url): array
    {
        $media = UploadedMedia::where('url', $url)->first();
        if ($media) {
            return [$media->disk, $media->path];
        }

        $disk = config('filesystems.upload_disk');
        $baseUrl = rtrim(Storage::disk($disk)->url(''), '/');
        $path = ltrim(str_replace($baseUrl, '', $url), '/');

        if ($path !== $url && Storage::disk($disk)->exists($path)) {
            return [$disk, $path];
        }

        abort(404);
    }

    private function markLinkedStagesCompleted(LmsAssignment $assignment, $user): void
    {
        $stageIds = collect();

        $directStageIds = LmsCourseStage::where('lms_assignment_id', $assignment->id)->pluck('id');
        $stageIds = $stageIds->merge($directStageIds);

        $blockStageIds = LmsStageBlock::where('lms_assignment_id', $assignment->id)->pluck('lms_course_stage_id');
        $stageIds = $stageIds->merge($blockStageIds);

        $stageIds = $stageIds->unique();

        foreach ($stageIds as $stageId) {
            LmsStageProgress::updateOrCreate(
                ['lms_course_stage_id' => $stageId, 'user_id' => $user->id],
                ['status' => 'completed', 'completed_at' => now()]
            );

            $stage = LmsCourseStage::find($stageId);
            if ($stage) {
                $totalStages = LmsCourseStage::where('lms_course_id', $stage->lms_course_id)->count();
                $completedStages = LmsStageProgress::whereIn(
                    'lms_course_stage_id',
                    LmsCourseStage::where('lms_course_id', $stage->lms_course_id)->pluck('id')
                )
                    ->where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->count();

                if ($completedStages >= $totalStages) {
                    LmsCourseEnrollment::where('lms_course_id', $stage->lms_course_id)
                        ->where('user_id', $user->id)
                        ->update(['status' => 'completed', 'completed_at' => now()]);
                }
            }
        }
    }
}
