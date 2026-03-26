<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsCourseModule;
use App\Models\Lms\LmsCourseStage;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsStageProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request, LmsEvent $event): Response
    {
        $user = auth()->user();
        $profile = LmsProfile::where('user_id', $user->id)
            ->where('lms_event_id', $event->id)
            ->first();

        $query = LmsCourse::where('lms_event_id', $event->id)
            ->where('is_active', true)
            ->orderBy('position')
            ->with('stages');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('title', 'ilike', "%{$s}%")->orWhere('description', 'ilike', "%{$s}%"));
        }

        $courses = $query->paginate(12)->withQueryString();

        $enrollmentMap = LmsCourseEnrollment::whereIn('lms_course_id', $courses->pluck('id'))
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('lms_course_id');

        $coursesData = $courses->through(function ($course) use ($user, $enrollmentMap) {
            $enrollment = $enrollmentMap->get($course->id);
            $completedStages = LmsStageProgress::whereIn('lms_course_stage_id', $course->stages->pluck('id'))
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->count();
            $progress = $course->stages->count() > 0
                ? round(($completedStages / $course->stages->count()) * 100)
                : 0;
            $isActiveEnrollment = $enrollment && !in_array($enrollment->status, ['pending', 'rejected']);
            return [
                'course' => $course->only(['id', 'slug', 'title', 'description', 'image', 'starts_at', 'ends_at']),
                'enrolled' => $isActiveEnrollment,
                'enrollment' => $enrollment?->only(['id', 'status', 'completed_at']),
                'progress' => $isActiveEnrollment ? $progress : 0,
                'stages_count' => $course->stages->count(),
            ];
        });

        return Inertia::render('Lms/Courses/Index', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'courses' => $coursesData,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(LmsEvent $event, LmsCourse $course): Response
    {
        if ($course->lms_event_id !== $event->id) {
            abort(404);
        }
        $user = auth()->user();
        $course->load(['stages', 'modules.stages']);

        $enrollment = LmsCourseEnrollment::where('lms_course_id', $course->id)
            ->where('user_id', $user->id)
            ->first();

        $stageProgress = LmsStageProgress::whereIn('lms_course_stage_id', $course->stages->pluck('id'))
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('lms_course_stage_id');

        $isSequential = (bool) $course->sequential;

        // Глобальный расчёт доступности по всем этапам курса (по position)
        $stageAvailability = [];
        $prevCompleted = true;
        foreach ($course->stages->sortBy('position') as $stage) {
            $progress = $stageProgress->get($stage->id);
            $isAvailable = true;
            if ($stage->available_from && now()->lt($stage->available_from)) {
                $isAvailable = false;
            }
            if ($isSequential && !$prevCompleted) {
                $isAvailable = false;
            }
            $stageAvailability[$stage->id] = $isAvailable;
            $prevCompleted = $progress?->status === 'completed';
        }

        $modules = $course->modules->map(function ($module) use ($stageProgress, $stageAvailability) {
            $moduleStages = $module->stages->sortBy('position')->map(function ($stage) use ($stageProgress, $stageAvailability) {
                $progress = $stageProgress->get($stage->id);
                return [
                    'stage' => $stage->only(['id', 'title', 'description', 'type', 'position', 'is_locked', 'available_from', 'duration_minutes']),
                    'progress' => $progress?->only(['status', 'completed_at', 'score']),
                    'is_available' => $stageAvailability[$stage->id] ?? true,
                ];
            })->values();

            return [
                'module' => $module->only(['id', 'title', 'description', 'position', 'available_from', 'available_to', 'unlock_type']),
                'is_available' => $module->isAvailable(),
                'stages' => $moduleStages,
            ];
        });

        $orphanStages = $course->stages->whereNull('lms_course_module_id')->sortBy('position')->map(function ($stage) use ($stageProgress, $stageAvailability) {
            $progress = $stageProgress->get($stage->id);
            return [
                'stage' => $stage->only(['id', 'title', 'description', 'type', 'position', 'is_locked', 'available_from', 'duration_minutes']),
                'progress' => $progress?->only(['status', 'completed_at', 'score']),
                'is_available' => $stageAvailability[$stage->id] ?? true,
            ];
        })->values();

        $profile = LmsProfile::where('user_id', $user->id)
            ->where('lms_event_id', $event->id)
            ->first();

        return Inertia::render('Lms/Courses/Show', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'course' => $course->only(['id', 'slug', 'title', 'description', 'image', 'sequential', 'starts_at', 'ends_at']),
            'enrollment' => $enrollment?->only(['id', 'status', 'completed_at']),
            'modules' => $modules,
            'orphanStages' => $orphanStages,
            'stages' => $course->stages->sortBy('position')->map(fn($s) => $s->only(['id', 'title', 'type', 'position']))->values(),
            'isProfileComplete' => $profile?->isProfileComplete() ?? false,
        ]);
    }

    public function enroll(LmsEvent $event, LmsCourse $course): RedirectResponse
    {
        if ($course->lms_event_id !== $event->id) {
            abort(404);
        }
        $user = auth()->user();

        $profile = LmsProfile::where('user_id', $user->id)
            ->where('lms_event_id', $event->id)
            ->first();

        if (! $profile || ! $profile->isProfileComplete()) {
            return redirect()->back()->withErrors([
                'enroll' => 'Для записи на курс необходимо заполнить профиль.',
            ]);
        }

        $existing = LmsCourseEnrollment::where('lms_course_id', $course->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            if ($existing->status === 'rejected') {
                $existing->update([
                    'status' => $course->requires_approval ? 'pending' : 'enrolled',
                    'reviewed_at' => null,
                    'reviewed_by' => null,
                ]);
            }
            return redirect()->back();
        }

        LmsCourseEnrollment::create([
            'lms_course_id' => $course->id,
            'user_id' => $user->id,
            'status' => $course->requires_approval ? 'pending' : 'enrolled',
        ]);

        return redirect()->back();
    }
}
