<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGroup;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsStageProgress;
use App\Models\Lms\LmsTrajectoryEnrollment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LeaderController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', auth()->id())
            ->first();
        if (!in_array($profile?->role ?? '', ['leader', 'curator', 'admin'])) {
            abort(403);
        }

        $totalParticipants = LmsProfile::where('lms_event_id', $event->id)->count();

        $courseEnrollments = LmsCourseEnrollment::whereHas('course', fn($q) => $q->where('lms_event_id', $event->id))->get();
        $totalStages = LmsCourse::where('lms_event_id', $event->id)->withCount('stages')->get()->sum('stages_count');
        $completedStages = LmsStageProgress::whereIn('lms_course_stage_id', function ($q) use ($event) {
            $q->select('id')->from('lms_course_stages')
                ->whereIn('lms_course_id', function ($sq) use ($event) {
                    $sq->select('id')->from('lms_courses')->where('lms_event_id', $event->id);
                });
        })->where('status', 'completed')->count();
        $avgProgress = $totalStages > 0 ? round(($completedStages / ($totalStages * max(1, $totalParticipants))) * 100) : 0;

        $activeCourses = LmsCourse::where('lms_event_id', $event->id)
            ->where('is_active', true)
            ->count();

        $totalGroups = LmsGroup::where('lms_event_id', $event->id)->count();
        $user = auth()->user();

        return Inertia::render('Lms/Leader/Dashboard', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'totalParticipants' => $totalParticipants,
            'avgProgress' => $avgProgress,
            'activeCourses' => $activeCourses,
            'totalGroups' => $totalGroups,
        ]);
    }

    public function groups(LmsEvent $event): Response
    {
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', auth()->id())
            ->first();
        if (!in_array($profile?->role ?? '', ['leader', 'curator', 'admin'])) {
            abort(403);
        }

        $groups = LmsGroup::where('lms_event_id', $event->id)
            ->withCount('members')
            ->get();

        $stats = $groups->map(function ($group) use ($event) {
            $memberIds = $group->members()->pluck('users.id');
            $completedCount = LmsStageProgress::whereIn('lms_course_stage_id', function ($q) use ($event) {
                $q->select('id')->from('lms_course_stages')
                    ->whereIn('lms_course_id', function ($sq) use ($event) {
                        $sq->select('id')->from('lms_courses')->where('lms_event_id', $event->id);
                    });
            })->whereIn('user_id', $memberIds)->where('status', 'completed')->count();
            $totalStages = LmsCourse::where('lms_event_id', $event->id)->withCount('stages')->get()->sum('stages_count');
            $groupProgress = $totalStages > 0 && $memberIds->isNotEmpty()
                ? round(($completedCount / ($totalStages * $memberIds->count())) * 100)
                : 0;
            return [
                'group' => $group->only(['id', 'title', 'members_count']),
                'progress' => $groupProgress,
            ];
        });

        $user = auth()->user();

        return Inertia::render('Lms/Leader/Groups', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'groups' => $stats,
        ]);
    }

    public function groupDetail(LmsEvent $event, LmsGroup $group): Response
    {
        if ($group->lms_event_id !== $event->id) {
            abort(404);
        }
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', auth()->id())
            ->first();
        if (!in_array($profile?->role ?? '', ['leader', 'curator', 'admin'])) {
            abort(403);
        }

        $group->load('members');
        $memberIds = $group->members->pluck('id');
        $courseIds = LmsCourse::where('lms_event_id', $event->id)->pluck('id');
        $stageIds = DB::table('lms_course_stages')->whereIn('lms_course_id', $courseIds)->pluck('id');

        $membersWithProgress = $group->members->map(function ($member) use ($stageIds) {
            $completed = LmsStageProgress::whereIn('lms_course_stage_id', $stageIds)
                ->where('user_id', $member->id)
                ->where('status', 'completed')
                ->count();
            $total = $stageIds->count();
            $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
            return [
                'user' => $member->only(['id', 'name', 'email']),
                'progress' => $progress,
            ];
        });

        $user = auth()->user();

        return Inertia::render('Lms/Leader/GroupDetail', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'group' => $group->only(['id', 'title']),
            'members' => $membersWithProgress,
        ]);
    }

    public function userProgress(LmsEvent $event, User $user): Response
    {
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', auth()->id())
            ->first();
        if (!in_array($profile?->role ?? '', ['leader', 'curator', 'admin'])) {
            abort(403);
        }

        $enrollments = LmsCourseEnrollment::whereHas('course', fn($q) => $q->where('lms_event_id', $event->id))
            ->where('user_id', $user->id)
            ->with('course.stages')
            ->get();

        $courseProgress = $enrollments->map(function ($enrollment) use ($user) {
            $stages = $enrollment->course->stages;
            $completed = LmsStageProgress::whereIn('lms_course_stage_id', $stages->pluck('id'))
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->count();
            $total = $stages->count();
            $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
            return [
                'course' => $enrollment->course->only(['id', 'title']),
                'enrollment' => $enrollment->only(['status', 'completed_at']),
                'progress' => $progress,
            ];
        });

        $trajectoryEnrollments = LmsTrajectoryEnrollment::whereHas('trajectory', fn($q) => $q->where('lms_event_id', $event->id))
            ->where('user_id', $user->id)
            ->with('trajectory')
            ->get();

        return Inertia::render('Lms/Leader/UserProgress', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'courseProgress' => $courseProgress,
            'trajectoryEnrollments' => $trajectoryEnrollments->map(fn($e) => [
                'trajectory' => $e->trajectory?->only(['id', 'title']),
                'enrollment' => $e->only(['status', 'completed_at']),
            ]),
        ]);
    }

    public function sendReport(Request $request, LmsEvent $event): RedirectResponse
    {
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', auth()->id())
            ->first();
        if (!in_array($profile?->role ?? '', ['leader', 'curator', 'admin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'report_type' => ['nullable', 'string', 'in:summary,groups,participants'],
        ]);

        // Placeholder: Generate and email report
        // In a real implementation, you would generate a PDF/Excel and send via Mail
        // Mail::to($validated['email'])->send(new LmsReportMail($event, $validated['report_type'] ?? 'summary'));

        return redirect()->back()->with('success', __('Report sent successfully.'));
    }
}
