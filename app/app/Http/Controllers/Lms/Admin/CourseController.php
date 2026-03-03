<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsCourseStage;
use App\Models\Lms\LmsEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $courses = $event->courses()->with('stages')->withCount('enrollments')->orderBy('position')->orderBy('title')->paginate(15);

        return Inertia::render('Lms/Admin/Courses/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'courses' => $courses,
        ]);
    }

    public function create(LmsEvent $event): Response
    {
        return Inertia::render('Lms/Admin/Courses/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'course' => null,
            'tests' => $event->tests()->orderBy('title')->get(['id', 'title']),
            'assignments' => $event->assignments()->orderBy('title')->get(['id', 'title']),
            'videos' => $event->videos()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function store(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:500'],
            'sequential' => ['boolean'],
            'is_active' => ['boolean'],
            'stages' => ['nullable', 'array'],
            'stages.*.title' => ['required', 'string', 'max:255'],
            'stages.*.type' => ['nullable', 'string'],
            'stages.*.content' => ['nullable', 'string'],
            'stages.*.position' => ['nullable', 'integer'],
        ]);

        $validated['lms_event_id'] = $event->id;
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['sequential'] = $request->boolean('sequential', true);
        $validated['is_active'] = $request->boolean('is_active', true);

        $course = LmsCourse::create($validated);

        $this->syncStages($course, $validated['stages'] ?? []);

        return redirect()->route('lms.admin.courses.index', $event)->with('success', 'Курс создан');
    }

    public function edit(LmsEvent $event, LmsCourse $course): Response
    {
        $this->ensureCourseBelongsToEvent($course, $event);

        $course->load('stages');

        return Inertia::render('Lms/Admin/Courses/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'course' => $course,
            'tests' => $event->tests()->orderBy('title')->get(['id', 'title']),
            'assignments' => $event->assignments()->orderBy('title')->get(['id', 'title']),
            'videos' => $event->videos()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(Request $request, LmsEvent $event, LmsCourse $course): RedirectResponse
    {
        $this->ensureCourseBelongsToEvent($course, $event);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:500'],
            'sequential' => ['boolean'],
            'is_active' => ['boolean'],
            'stages' => ['nullable', 'array'],
            'stages.*.title' => ['required', 'string', 'max:255'],
            'stages.*.type' => ['nullable', 'string'],
            'stages.*.content' => ['nullable', 'string'],
            'stages.*.position' => ['nullable', 'integer'],
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['sequential'] = $request->boolean('sequential', true);
        $validated['is_active'] = $request->boolean('is_active', true);

        $course->update($validated);

        $this->syncStages($course, $validated['stages'] ?? []);

        return redirect()->route('lms.admin.courses.index', $event)->with('success', 'Курс обновлён');
    }

    public function destroy(LmsEvent $event, LmsCourse $course): RedirectResponse
    {
        $this->ensureCourseBelongsToEvent($course, $event);

        $course->delete();

        return redirect()->route('lms.admin.courses.index', $event)->with('success', 'Курс удалён');
    }

    private function syncStages(LmsCourse $course, array $stages): void
    {
        $course->stages()->delete();

        foreach ($stages as $index => $stage) {
            LmsCourseStage::create([
                'lms_course_id' => $course->id,
                'title' => $stage['title'],
                'type' => $stage['type'] ?? null,
                'content' => $stage['content'] ?? null,
                'position' => $stage['position'] ?? $index,
            ]);
        }
    }

    private function ensureCourseBelongsToEvent(LmsCourse $course, LmsEvent $event): void
    {
        if ($course->lms_event_id !== $event->id) {
            abort(404);
        }
    }
}
