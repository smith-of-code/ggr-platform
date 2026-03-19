<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsTrajectory;
use App\Models\Lms\LmsTrajectoryStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrajectoryController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $trajectories = $event->trajectories()
            ->with('steps.course')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Lms/Admin/Trajectories/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'trajectories' => $trajectories,
        ]);
    }

    public function create(LmsEvent $event): Response
    {
        $courses = $event->courses()->orderBy('position')->get(['id', 'title']);

        return Inertia::render('Lms/Admin/Trajectories/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'trajectory' => null,
            'courses' => $courses,
        ]);
    }

    public function store(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'steps' => ['nullable', 'array'],
            'steps.*.lms_course_id' => ['required', 'exists:lms_courses,id'],
            'steps.*.is_locked' => ['boolean'],
            'steps.*.opens_at' => ['nullable', 'date'],
            'steps.*.position' => ['nullable', 'integer'],
        ]);

        $validated['lms_event_id'] = $event->id;
        $validated['is_active'] = $request->boolean('is_active', true);

        $trajectory = LmsTrajectory::create($validated);

        $this->syncSteps($trajectory, $event, $validated['steps'] ?? []);

        return redirect()->route('lms.admin.trajectories.index', $event)->with('success', 'Траектория создана');
    }

    public function edit(LmsEvent $event, LmsTrajectory $trajectory): Response
    {
        $this->ensureTrajectoryBelongsToEvent($trajectory, $event);

        $trajectory->load('steps.course');
        $courses = $event->courses()->orderBy('position')->get(['id', 'title']);

        return Inertia::render('Lms/Admin/Trajectories/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'trajectory' => $trajectory,
            'courses' => $courses,
        ]);
    }

    public function update(Request $request, LmsEvent $event, LmsTrajectory $trajectory): RedirectResponse
    {
        $this->ensureTrajectoryBelongsToEvent($trajectory, $event);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'steps' => ['nullable', 'array'],
            'steps.*.lms_course_id' => ['required', 'exists:lms_courses,id'],
            'steps.*.is_locked' => ['boolean'],
            'steps.*.opens_at' => ['nullable', 'date'],
            'steps.*.position' => ['nullable', 'integer'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $trajectory->update($validated);

        $this->syncSteps($trajectory, $event, $validated['steps'] ?? []);

        return redirect()->route('lms.admin.trajectories.index', $event)->with('success', 'Траектория обновлена');
    }

    public function destroy(LmsEvent $event, LmsTrajectory $trajectory): RedirectResponse
    {
        $this->ensureTrajectoryBelongsToEvent($trajectory, $event);

        $trajectory->delete();

        return redirect()->route('lms.admin.trajectories.index', $event)->with('success', 'Траектория удалена');
    }

    private function syncSteps(LmsTrajectory $trajectory, LmsEvent $event, array $steps): void
    {
        $trajectory->steps()->delete();

        foreach ($steps as $index => $step) {
            $course = LmsCourse::find($step['lms_course_id']);
            if ($course && $course->lms_event_id === $event->id) {
                LmsTrajectoryStep::create([
                    'lms_trajectory_id' => $trajectory->id,
                    'lms_course_id' => $step['lms_course_id'],
                    'is_locked' => $step['is_locked'] ?? false,
                    'opens_at' => $step['opens_at'] ?? null,
                    'position' => $step['position'] ?? $index,
                ]);
            }
        }
    }

    private function ensureTrajectoryBelongsToEvent(LmsTrajectory $trajectory, LmsEvent $event): void
    {
        if ($trajectory->lms_event_id !== $event->id) {
            abort(404);
        }
    }
}
