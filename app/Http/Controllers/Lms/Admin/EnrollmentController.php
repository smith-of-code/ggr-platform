<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EnrollmentController extends Controller
{
    public function index(Request $request, LmsEvent $event): Response
    {
        $status = $request->query('status', 'pending');

        $query = LmsCourseEnrollment::whereHas('course', fn($q) => $q->where('lms_event_id', $event->id))
            ->with(['user:id,name,email', 'course:id,title', 'reviewer:id,name']);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $enrollments = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $courses = $event->courses()->orderBy('title')->get(['id', 'title']);

        $counts = LmsCourseEnrollment::whereHas('course', fn($q) => $q->where('lms_event_id', $event->id))
            ->selectRaw("
                count(*) as total,
                count(*) filter (where status = 'pending') as pending,
                count(*) filter (where status = 'enrolled') as enrolled,
                count(*) filter (where status = 'rejected') as rejected
            ")
            ->first();

        return Inertia::render('Lms/Admin/Enrollments/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'enrollments' => $enrollments,
            'courses' => $courses,
            'currentStatus' => $status,
            'counts' => [
                'all' => $counts->total ?? 0,
                'pending' => $counts->pending ?? 0,
                'enrolled' => $counts->enrolled ?? 0,
                'rejected' => $counts->rejected ?? 0,
            ],
        ]);
    }

    public function courseEnrollments(Request $request, LmsEvent $event, LmsCourse $course): Response
    {
        if ($course->lms_event_id !== $event->id) {
            abort(404);
        }

        $status = $request->query('status', 'pending');

        $query = LmsCourseEnrollment::where('lms_course_id', $course->id)
            ->with(['user:id,name,email', 'reviewer:id,name']);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $enrollments = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $counts = LmsCourseEnrollment::where('lms_course_id', $course->id)
            ->selectRaw("
                count(*) as total,
                count(*) filter (where status = 'pending') as pending,
                count(*) filter (where status = 'enrolled') as enrolled,
                count(*) filter (where status = 'rejected') as rejected
            ")
            ->first();

        return Inertia::render('Lms/Admin/Enrollments/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'enrollments' => $enrollments,
            'course' => $course->only(['id', 'title']),
            'courses' => [],
            'currentStatus' => $status,
            'counts' => [
                'all' => $counts->total ?? 0,
                'pending' => $counts->pending ?? 0,
                'enrolled' => $counts->enrolled ?? 0,
                'rejected' => $counts->rejected ?? 0,
            ],
        ]);
    }

    public function approve(LmsEvent $event, LmsCourseEnrollment $enrollment): RedirectResponse
    {
        $this->ensureEnrollmentBelongsToEvent($enrollment, $event);

        $enrollment->update([
            'status' => 'enrolled',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Заявка одобрена');
    }

    public function reject(LmsEvent $event, LmsCourseEnrollment $enrollment): RedirectResponse
    {
        $this->ensureEnrollmentBelongsToEvent($enrollment, $event);

        $enrollment->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Заявка отклонена');
    }

    private function ensureEnrollmentBelongsToEvent(LmsCourseEnrollment $enrollment, LmsEvent $event): void
    {
        $enrollment->loadMissing('course');
        if ($enrollment->course->lms_event_id !== $event->id) {
            abort(404);
        }
    }
}
