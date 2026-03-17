<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsCourseStage;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use ZipArchive;

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
            'roles' => LmsRole::where('lms_event_id', $event->id)->orderBy('name')->get(['id', 'name', 'slug']),
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
            'stages.*.is_locked' => ['nullable', 'boolean'],
        ]);

        $validated['lms_event_id'] = $event->id;
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['sequential'] = $request->boolean('sequential', true);
        $validated['is_active'] = $request->boolean('is_active', true);

        $course = LmsCourse::create($validated);

        $this->syncStages($course, $validated['stages'] ?? []);

        if ($request->has('role_ids')) {
            $course->roleAccess()->sync($request->input('role_ids', []));
        }

        return redirect()->route('lms.admin.courses.index', $event)->with('success', 'Курс создан');
    }

    public function edit(LmsEvent $event, LmsCourse $course): Response
    {
        $this->ensureCourseBelongsToEvent($course, $event);

        $course->load(['stages', 'roleAccess']);

        return Inertia::render('Lms/Admin/Courses/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'course' => $course,
            'tests' => $event->tests()->orderBy('title')->get(['id', 'title']),
            'assignments' => $event->assignments()->orderBy('title')->get(['id', 'title']),
            'videos' => $event->videos()->orderBy('title')->get(['id', 'title']),
            'roles' => LmsRole::where('lms_event_id', $event->id)->orderBy('name')->get(['id', 'name', 'slug']),
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
            'stages.*.is_locked' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['sequential'] = $request->boolean('sequential', true);
        $validated['is_active'] = $request->boolean('is_active', true);

        $course->update($validated);

        $this->syncStages($course, $validated['stages'] ?? []);

        if ($request->has('role_ids')) {
            $course->roleAccess()->sync($request->input('role_ids', []));
        }

        return redirect()->route('lms.admin.courses.index', $event)->with('success', 'Курс обновлён');
    }

    public function destroy(LmsEvent $event, LmsCourse $course): RedirectResponse
    {
        $this->ensureCourseBelongsToEvent($course, $event);

        $course->delete();

        return redirect()->route('lms.admin.courses.index', $event)->with('success', 'Курс удалён');
    }

    public function uploadScorm(Request $request, LmsEvent $event): JsonResponse
    {
        $request->validate([
            'scorm_file' => ['required', 'file', 'mimes:zip', 'max:102400'],
        ]);

        $file = $request->file('scorm_file');
        $hash = Str::random(16);
        $dirName = 'scorm/' . $event->slug . '/' . $hash;

        $zipPath = $file->store('tmp', 'local');
        $fullZipPath = storage_path('app/' . $zipPath);

        $zip = new ZipArchive();
        if ($zip->open($fullZipPath) !== true) {
            @unlink($fullZipPath);
            return response()->json(['error' => 'Не удалось открыть ZIP-архив'], 422);
        }

        $hasManifest = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (basename($name) === 'imsmanifest.xml') {
                $hasManifest = true;
                break;
            }
        }

        if (!$hasManifest) {
            $zip->close();
            @unlink($fullZipPath);
            return response()->json(['error' => 'ZIP не содержит imsmanifest.xml — не является SCORM-пакетом'], 422);
        }

        $extractPath = public_path($dirName);
        @mkdir($extractPath, 0755, true);
        $zip->extractTo($extractPath);
        $zip->close();
        @unlink($fullZipPath);

        $launchFile = $this->findScormLaunchFile($extractPath);
        $scormUrl = '/' . $dirName . '/' . $launchFile;

        return response()->json([
            'url' => $scormUrl,
            'directory' => '/' . $dirName,
            'filename' => $file->getClientOriginalName(),
        ]);
    }

    private function findScormLaunchFile(string $extractPath): string
    {
        $manifestPath = null;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($extractPath));
        foreach ($iterator as $file) {
            if ($file->getFilename() === 'imsmanifest.xml') {
                $manifestPath = $file->getPathname();
                break;
            }
        }

        if ($manifestPath && file_exists($manifestPath)) {
            $xml = @simplexml_load_file($manifestPath);
            if ($xml) {
                $xml->registerXPathNamespace('imscp', 'http://www.imsproject.org/xsd/imscp_rootv1p1p2');
                $xml->registerXPathNamespace('adlcp', 'http://www.adlnet.org/xsd/adlcp_rootv1p2');

                $resources = $xml->xpath('//resource[@adlcp:scormtype="sco" or @adlcp:scormType="sco"]/@href');
                if (empty($resources)) {
                    $resources = $xml->xpath('//resource/@href');
                }
                if (!empty($resources)) {
                    $href = (string) $resources[0];
                    $relDir = str_replace($extractPath . '/', '', dirname($manifestPath));
                    if ($relDir !== $extractPath && $relDir !== '.') {
                        return $relDir . '/' . $href;
                    }
                    return $href;
                }
            }
        }

        foreach (['index.html', 'index.htm', 'player.html', 'launch.html'] as $fallback) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($extractPath));
            foreach ($iterator as $f) {
                if ($f->getFilename() === $fallback) {
                    return str_replace($extractPath . '/', '', $f->getPathname());
                }
            }
        }

        return 'index.html';
    }

    private function syncStages(LmsCourse $course, array $stages): void
    {
        $course->stages()->delete();

        foreach ($stages as $index => $stage) {
            $data = [
                'lms_course_id' => $course->id,
                'title' => $stage['title'],
                'type' => $stage['type'] ?? null,
                'content' => $stage['content'] ?? null,
                'position' => $stage['position'] ?? $index,
                'is_locked' => $stage['is_locked'] ?? false,
            ];

            if (($stage['type'] ?? null) === 'test' && !empty($stage['content'])) {
                $data['lms_test_id'] = is_numeric($stage['content']) ? (int) $stage['content'] : null;
            }
            if (($stage['type'] ?? null) === 'assignment' && !empty($stage['content'])) {
                $data['lms_assignment_id'] = is_numeric($stage['content']) ? (int) $stage['content'] : null;
            }
            if (($stage['type'] ?? null) === 'video' && !empty($stage['content'])) {
                $data['lms_video_id'] = is_numeric($stage['content']) ? (int) $stage['content'] : null;
            }
            if (($stage['type'] ?? null) === 'scorm' && !empty($stage['content'])) {
                $data['scorm_package'] = $stage['content'];
            }

            LmsCourseStage::create($data);
        }
    }

    private function ensureCourseBelongsToEvent(LmsCourse $course, LmsEvent $event): void
    {
        if ($course->lms_event_id !== $event->id) {
            abort(404);
        }
    }
}
