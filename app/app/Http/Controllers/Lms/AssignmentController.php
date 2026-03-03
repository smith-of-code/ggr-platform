<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsAssignment;
use App\Models\Lms\LmsAssignmentSubmission;
use App\Models\Lms\LmsEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssignmentController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $user = auth()->user();
        $assignments = LmsAssignment::where('lms_event_id', $event->id)
            ->where('is_active', true)
            ->get();

        $submissions = LmsAssignmentSubmission::whereIn('lms_assignment_id', $assignments->pluck('id'))
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('lms_assignment_id');

        $assignmentsWithStatus = $assignments->map(function ($assignment) use ($submissions) {
            $submission = $submissions->get($assignment->id);
            return [
                'assignment' => $assignment->only(['id', 'title', 'description', 'deadline']),
                'submission' => $submission?->only(['id', 'status', 'created_at']),
            ];
        });

        return Inertia::render('Lms/Assignments/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'assignments' => $assignmentsWithStatus,
        ]);
    }

    public function show(LmsEvent $event, LmsAssignment $assignment): Response
    {
        if ($assignment->lms_event_id !== $event->id) {
            abort(404);
        }
        $user = auth()->user();
        $assignment->load('submissions.reviews');

        $submission = LmsAssignmentSubmission::where('lms_assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->with('reviews')
            ->first();

        return Inertia::render('Lms/Assignments/Show', [
            'event' => $event->only(['id', 'slug', 'title']),
            'assignment' => $assignment->only(['id', 'title', 'description', 'template_file', 'deadline']),
            'submission' => $submission?->load('reviews'),
        ]);
    }

    public function submit(Request $request, LmsEvent $event, LmsAssignment $assignment): RedirectResponse
    {
        if ($assignment->lms_event_id !== $event->id) {
            abort(404);
        }
        $user = auth()->user();
        $validated = $request->validate([
            'text_content' => ['nullable', 'string'],
            'link' => ['nullable', 'url', 'max:500'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file'],
        ]);

        $files = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('assignments/' . $assignment->id, 'public');
                $files[] = $path;
            }
        }

        LmsAssignmentSubmission::updateOrCreate(
            [
                'lms_assignment_id' => $assignment->id,
                'user_id' => $user->id,
            ],
            [
                'text_content' => $validated['text_content'] ?? null,
                'link' => $validated['link'] ?? null,
                'files' => $files,
                'status' => 'submitted',
            ]
        );

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

        $files = $submission->files ?? [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('assignments/' . $assignment->id, 'public');
                $files[] = $path;
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
}
