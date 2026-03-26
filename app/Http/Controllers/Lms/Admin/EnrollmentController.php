<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $userIds = collect($enrollments->items())->pluck('user_id')->unique();
        $profiles = LmsProfile::where('lms_event_id', $event->id)
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        $enrollments->getCollection()->transform(function ($enrollment) use ($profiles) {
            $profile = $profiles->get($enrollment->user_id);
            $enrollment->profile_data = $profile ? [
                'phone' => $profile->phone,
                'preferred_channel' => $profile->preferred_channel,
                'organization' => $profile->organization,
                'position' => $profile->position,
                'project_description' => $profile->project_description,
            ] : null;
            return $enrollment;
        });

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

        $userIds = collect($enrollments->items())->pluck('user_id')->unique();
        $profiles = LmsProfile::where('lms_event_id', $event->id)
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        $enrollments->getCollection()->transform(function ($enrollment) use ($profiles) {
            $profile = $profiles->get($enrollment->user_id);
            $enrollment->profile_data = $profile ? [
                'phone' => $profile->phone,
                'preferred_channel' => $profile->preferred_channel,
                'organization' => $profile->organization,
                'position' => $profile->position,
                'project_description' => $profile->project_description,
            ] : null;
            return $enrollment;
        });

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

    public function approve(Request $request, LmsEvent $event, LmsCourseEnrollment $enrollment): RedirectResponse
    {
        $this->ensureEnrollmentBelongsToEvent($enrollment, $event);

        $enrollment->update([
            'status' => 'enrolled',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return $this->safeRedirect($request, $event, 'Заявка одобрена');
    }

    public function reject(Request $request, LmsEvent $event, LmsCourseEnrollment $enrollment): RedirectResponse
    {
        $this->ensureEnrollmentBelongsToEvent($enrollment, $event);

        $enrollment->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return $this->safeRedirect($request, $event, 'Заявка отклонена');
    }

    public function destroy(Request $request, LmsEvent $event, LmsCourseEnrollment $enrollment): RedirectResponse
    {
        $this->ensureEnrollmentBelongsToEvent($enrollment, $event);

        $userId = $enrollment->user_id;
        $courseId = $enrollment->lms_course_id;

        DB::table('lms_stage_progress')
            ->where('user_id', $userId)
            ->whereIn('lms_course_stage_id', function ($q) use ($courseId) {
                $q->select('id')->from('lms_course_stages')->where('lms_course_id', $courseId);
            })
            ->delete();

        $enrollment->delete();

        return $this->safeRedirect($request, $event, 'Участник отписан от курса');
    }

    private function safeRedirect(Request $request, LmsEvent $event, string $message): RedirectResponse
    {
        $referer = $request->headers->get('referer', '');
        $adminPrefix = '/lms-admin/' . $event->slug;

        if (str_contains($referer, $adminPrefix)) {
            return redirect()->to($referer)->with('success', $message);
        }

        return redirect()->route('lms.admin.enrollments.index', $event->slug)->with('success', $message);
    }

    private function ensureEnrollmentBelongsToEvent(LmsCourseEnrollment $enrollment, LmsEvent $event): void
    {
        $enrollment->loadMissing('course');
        if ($enrollment->course->lms_event_id !== $event->id) {
            abort(404);
        }
    }
}
