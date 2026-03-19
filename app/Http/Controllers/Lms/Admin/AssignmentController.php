<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsAssignment;
use App\Models\Lms\LmsAssignmentReview;
use App\Models\Lms\LmsAssignmentSubmission;
use App\Models\Lms\LmsEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'template_file' => ['nullable', 'string', 'max:500'],
            'completion_mode' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
        ]);

        $validated['lms_event_id'] = $event->id;
        $validated['is_active'] = $request->boolean('is_active', true);

        LmsAssignment::create($validated);

        return redirect()->route('lms.admin.assignments.index', $event)->with('success', 'Задание создано');
    }

    public function show(LmsEvent $event, LmsAssignment $assignment): Response
    {
        $this->ensureAssignmentBelongsToEvent($assignment, $event);

        $submissions = $assignment->submissions()
            ->with('user:id,name,email')
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

        return Inertia::render('Lms/Admin/Assignments/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'assignment' => $assignment,
        ]);
    }

    public function update(Request $request, LmsEvent $event, LmsAssignment $assignment): RedirectResponse
    {
        $this->ensureAssignmentBelongsToEvent($assignment, $event);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'template_file' => ['nullable', 'string', 'max:500'],
            'completion_mode' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $assignment->update($validated);

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
        ]);

        LmsAssignmentReview::create([
            'lms_assignment_submission_id' => $submission->id,
            'reviewer_id' => Auth::id(),
            'comment' => $validated['comment'] ?? null,
            'decision' => $validated['decision'],
        ]);

        $statusMap = ['approve' => 'approved', 'reject' => 'rejected', 'revision' => 'revision'];
        $submission->update(['status' => $statusMap[$validated['decision']] ?? $validated['decision']]);

        return redirect()->back()->with('success', 'Решение сохранено');
    }

    private function ensureAssignmentBelongsToEvent(LmsAssignment $assignment, LmsEvent $event): void
    {
        if ($assignment->lms_event_id !== $event->id) {
            abort(404);
        }
    }
}
