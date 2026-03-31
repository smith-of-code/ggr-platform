<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsAssignment;
use App\Models\Lms\LmsAssignmentComment;
use App\Models\Lms\LmsAssignmentSubmission;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsSubmissionAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AssignmentController extends Controller
{
    public function index(Request $request, LmsEvent $event): Response
    {
        $user = auth()->user();
        $assignmentsQuery = LmsAssignment::where('lms_event_id', $event->id)
            ->where('is_active', true);

        if ($search = $request->get('search')) {
            $assignmentsQuery->where('title', 'ilike', '%' . $search . '%');
        }

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
        $assignment->load('tasks');

        $submission = LmsAssignmentSubmission::where('lms_assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->with(['reviews.reviewer:id,name', 'comments.user:id,name', 'answers.task'])
            ->first();

        return Inertia::render('Lms/Assignments/Show', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'assignment' => array_merge(
                $assignment->only(['id', 'title', 'description', 'template_file', 'template_file_name', 'deadline']),
                ['tasks' => $assignment->tasks]
            ),
            'submission' => $submission,
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
            'answers' => ['nullable', 'array'],
            'answers.*.task_id' => ['required_with:answers', 'integer'],
            'answers.*.text_content' => ['nullable', 'string'],
            'answers.*.link' => ['nullable', 'url', 'max:500'],
        ]);

        $disk = config('filesystems.upload_disk');

        $legacyFiles = [];
        if (!$hasTasks && $request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('assignments/' . $assignment->id, $disk);
                $legacyFiles[] = Storage::disk($disk)->url($path);
            }
        }

        $submission = LmsAssignmentSubmission::updateOrCreate(
            ['lms_assignment_id' => $assignment->id, 'user_id' => $user->id],
            [
                'text_content' => $hasTasks ? null : ($validated['text_content'] ?? null),
                'link' => $hasTasks ? null : ($validated['link'] ?? null),
                'files' => $hasTasks ? null : $legacyFiles,
                'status' => 'submitted',
            ]
        );

        if ($hasTasks) {
            $this->saveAnswers($submission, $assignment, $request, $validated['answers'] ?? []);
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
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('assignments/' . $assignment->id, $disk);
                    $legacyFiles[] = Storage::disk($disk)->url($path);
                }
            }
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
        ]);

        $disk = config('filesystems.upload_disk');
        $files = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('assignment-comments/' . $assignment->id, $disk);
                $files[] = ['name' => $file->getClientOriginalName(), 'path' => Storage::disk($disk)->url($path)];
            }
        }

        LmsAssignmentComment::create([
            'lms_assignment_submission_id' => $submission->id,
            'user_id' => $user->id,
            'text' => $validated['text'],
            'files' => $files ?: null,
        ]);

        return redirect()->back();
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
        ]);

        $disk = config('filesystems.upload_disk');
        $files = $submission->files ?? [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('assignments/' . $assignment->id, $disk);
                $files[] = Storage::disk($disk)->url($path);
            }
        }

        $submission->update([
            'text_content' => $validated['text_content'] ?? $submission->text_content,
            'link' => $validated['link'] ?? $submission->link,
            'files' => $files,
            'status' => 'resubmitted',
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

            if ($task->response_type === 'file' && $request->hasFile("answers.{$index}.files")) {
                $uploaded = [];
                foreach ($request->file("answers.{$index}.files") as $file) {
                    $path = $file->store('assignments/' . $assignment->id, $disk);
                    $uploaded[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => Storage::disk($disk)->url($path),
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
}
