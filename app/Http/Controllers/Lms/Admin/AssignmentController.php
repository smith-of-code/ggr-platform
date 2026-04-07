<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsAssignment;
use App\Models\Lms\LmsAssignmentComment;
use App\Models\Lms\LmsAssignmentReview;
use App\Models\Lms\LmsAssignmentSubmission;
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
use Inertia\Inertia;
use Inertia\Response;

class AssignmentController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $assignments = $event->assignments()->withCount('submissions')->orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('Lms/Admin/Assignments/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'assignments' => $assignments,
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

        $validated = $this->handleTemplateUpload($request, $validated, $event);

        $assignment = LmsAssignment::create($validated);

        $this->syncTasks($assignment, $request->input('tasks', []), $request, $event);

        return redirect()->route('lms.admin.assignments.index', $event)->with('success', 'Задание создано');
    }

    public function show(LmsEvent $event, LmsAssignment $assignment): Response
    {
        $this->ensureAssignmentBelongsToEvent($assignment, $event);

        $assignment->load('tasks');

        $submissions = $assignment->submissions()
            ->with(['user:id,name,email', 'reviews.reviewer:id,name', 'comments.user:id,name', 'answers.task'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Lms/Admin/Assignments/Submissions', [
            'event' => $event->only(['id', 'slug', 'title']),
            'assignment' => $assignment,
            'submissions' => $submissions,
        ]);
    }

    public function edit(LmsEvent $event, LmsAssignment $assignment): Response
    {
        $this->ensureAssignmentBelongsToEvent($assignment, $event);

        $assignment->load('tasks');

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

        $validated = $this->handleTemplateUpload($request, $validated, $event);

        $assignment->update($validated);

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
        ]);

        $disk = config('filesystems.upload_disk');
        $files = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('assignment-reviews/' . $assignment->id, $disk);
                $files[] = ['name' => $file->getClientOriginalName(), 'path' => Storage::disk($disk)->url($path)];
            }
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
            'user_id' => Auth::id(),
            'text' => $validated['text'],
            'files' => $files ?: null,
        ]);

        return redirect()->back()->with('success', 'Комментарий добавлен');
    }

    private function assignmentRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'template_file' => ['nullable', 'string', 'max:500'],
            'completion_mode' => ['sometimes', 'string', 'in:on_submit,on_review'],
            'deadline' => ['nullable', 'date'],
            'tasks' => ['nullable', 'array'],
            'tasks.*.title' => ['required', 'string', 'max:255'],
            'tasks.*.description' => ['nullable', 'string'],
            'tasks.*.response_type' => ['required', 'string', 'in:text,link,file'],
            'tasks.*.template_file' => ['nullable', 'string', 'max:500'],
            'tasks.*.template_file_name' => ['nullable', 'string', 'max:255'],
            'tasks.*.position' => ['nullable', 'integer'],
        ];
    }

    private function handleTemplateUpload(Request $request, array $validated, LmsEvent $event): array
    {
        if ($request->hasFile('template_file_upload')) {
            $request->validate(['template_file_upload' => ['file', 'max:51200']]);
            $disk = config('filesystems.upload_disk');
            $file = $request->file('template_file_upload');
            $path = $file->store('uploads/assignment-templates', $disk);
            $validated['template_file'] = Storage::disk($disk)->url($path);
            $validated['template_file_name'] = $file->getClientOriginalName();
        }

        return $validated;
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
}
