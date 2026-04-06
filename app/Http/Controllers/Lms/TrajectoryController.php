<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsAssignmentSubmission;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGrantEnrollment;
use App\Models\Lms\LmsMaterialSection;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsStageProgress;
use App\Models\Lms\LmsTrajectory;
use App\Models\Lms\LmsTrajectoryEnrollment;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TrajectoryController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $user = auth()->user();

        $trajectory = LmsTrajectory::where('lms_event_id', $event->id)
            ->where('is_active', true)
            ->with('blocks.assignment')
            ->first();

        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        $timeline = [];

        if ($trajectory) {
            foreach ($trajectory->blocks->sortBy('position') as $block) {
                $item = [
                    'type' => $block->type,
                    'title' => $block->title,
                    'description' => $block->description,
                    'date_label' => $block->date_label,
                    'date_start' => $block->date_start?->toDateString(),
                    'date_end' => $block->date_end?->toDateString(),
                    'material_url' => $block->material_url,
                ];

                if ($block->type === 'task' && $block->assignment) {
                    $assignment = $block->assignment;
                    $item['title'] = $item['title'] ?: $assignment->title;
                    $item['route'] = route('lms.assignments.show', ['event' => $event->slug, 'assignment' => $assignment->id]);

                    $submission = LmsAssignmentSubmission::where('lms_assignment_id', $assignment->id)
                        ->where('user_id', $user->id)
                        ->first();

                    $item['submission_status'] = $submission?->status;
                }

                $timeline[] = $item;
            }

            $courseItems = $this->buildCourseItems($event, $user);
            $grantItems = $this->buildGrantItems($event, $user);

            foreach ($courseItems as $ci) {
                $timeline[] = $ci;
            }
            foreach ($grantItems as $gi) {
                $timeline[] = $gi;
            }

            usort($timeline, function ($a, $b) {
                $da = $a['date_start'] ?? '9999-12-31';
                $db = $b['date_start'] ?? '9999-12-31';
                return strcmp($da, $db);
            });
        }

        $hasMaterials = LmsMaterialSection::where('lms_event_id', $event->id)->exists();

        return Inertia::render('Lms/Trajectories/Index', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'trajectory' => $trajectory?->only(['id', 'title', 'description']),
            'timeline' => $timeline,
            'hasMaterials' => $hasMaterials,
        ]);
    }

    public function show(LmsEvent $event, LmsTrajectory $trajectory): Response
    {
        return $this->index($event);
    }

    public function enroll(LmsEvent $event, LmsTrajectory $trajectory): RedirectResponse
    {
        if ($trajectory->lms_event_id !== $event->id) {
            abort(404);
        }
        $user = auth()->user();

        LmsTrajectoryEnrollment::firstOrCreate(
            [
                'lms_trajectory_id' => $trajectory->id,
                'user_id' => $user->id,
            ],
            ['status' => 'enrolled']
        );

        $unlockedCourses = $trajectory->steps()
            ->where('is_locked', false)
            ->where(function ($q) {
                $q->whereNull('opens_at')->orWhere('opens_at', '<=', now());
            })
            ->pluck('lms_course_id')
            ->unique();

        foreach ($unlockedCourses as $courseId) {
            LmsCourseEnrollment::firstOrCreate(
                ['lms_course_id' => $courseId, 'user_id' => $user->id],
                ['status' => 'enrolled']
            );
        }

        return redirect()->back();
    }

    private function buildCourseItems(LmsEvent $event, $user): array
    {
        $enrollments = LmsCourseEnrollment::where('user_id', $user->id)
            ->whereHas('course', fn ($q) => $q->where('lms_event_id', $event->id))
            ->with('course.stages')
            ->get();

        $items = [];
        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            if (! $course) continue;

            $stageIds = $course->stages->pluck('id');
            $completedStages = $stageIds->isEmpty() ? 0 : LmsStageProgress::whereIn('lms_course_stage_id', $stageIds)
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->count();
            $totalStages = $stageIds->count();
            $progress = $totalStages > 0 ? round(($completedStages / $totalStages) * 100) : 0;

            $dateLabel = $this->formatDateRange($course->starts_at, $course->ends_at);

            $stagesList = $course->stages->sortBy('position')->map(fn ($s) => [
                'title' => $s->title,
                'completed' => LmsStageProgress::where('lms_course_stage_id', $s->id)
                    ->where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->exists(),
            ])->values()->toArray();

            $items[] = [
                'type' => 'course',
                'title' => $course->title,
                'description' => $course->description ? strip_tags($course->description) : null,
                'date_label' => $dateLabel,
                'date_start' => $course->starts_at?->toDateString(),
                'date_end' => $course->ends_at?->toDateString(),
                'route' => route('lms.courses.show', ['event' => $event->slug, 'course' => $course->slug]),
                'progress' => $progress,
                'enrolled' => true,
                'status' => $enrollment->status,
                'stages' => $stagesList,
            ];
        }

        return $items;
    }

    private function buildGrantItems(LmsEvent $event, $user): array
    {
        $enrollments = LmsGrantEnrollment::where('user_id', $user->id)
            ->whereHas('grant', fn ($q) => $q->where('lms_event_id', $event->id))
            ->with('grant')
            ->get();

        $items = [];
        foreach ($enrollments as $ge) {
            $grant = $ge->grant;

            $dateLabel = $this->formatDateRange($grant->application_start, $grant->application_end);

            $items[] = [
                'type' => 'grant',
                'title' => $grant->title,
                'description' => $grant->description ? strip_tags($grant->description) : null,
                'date_label' => $dateLabel,
                'date_start' => $grant->application_start?->toDateString(),
                'date_end' => $grant->application_end?->toDateString(),
                'route' => route('lms.grants.show', ['event' => $event->slug, 'grant' => $grant->id]),
                'enrolled' => true,
            ];
        }

        return $items;
    }

    private function formatDateRange($start, $end): ?string
    {
        if ($start && $end) {
            return $start->format('d.m') . ' – ' . $end->format('d.m.Y');
        }
        if ($start) {
            return 'с ' . $start->format('d.m.Y');
        }
        if ($end) {
            return 'до ' . $end->format('d.m.Y');
        }
        return null;
    }
}
