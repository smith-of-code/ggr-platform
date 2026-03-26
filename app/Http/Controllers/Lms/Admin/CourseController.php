<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsCourseModule;
use App\Models\Lms\LmsCourseStage;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsRole;
use App\Models\Lms\LmsStageBlock;
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
        $validated = $request->validate($this->courseRules());

        $validated['lms_event_id'] = $event->id;
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['sequential'] = $request->boolean('sequential', true);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['requires_approval'] = $request->boolean('requires_approval', false);

        $course = LmsCourse::create($validated);

        $this->syncModulesAndStages($course, $validated['modules'] ?? [], $validated['stages'] ?? []);

        if ($request->has('role_ids')) {
            $course->roleAccess()->sync($request->input('role_ids', []));
        }

        return redirect()->route('lms.admin.courses.index', $event)->with('success', 'Курс создан');
    }

    public function edit(LmsEvent $event, LmsCourse $course): Response
    {
        $this->ensureCourseBelongsToEvent($course, $event);

        $course->load(['stages.blocks', 'modules.stages.blocks', 'roleAccess']);

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

        $validated = $request->validate($this->courseRules());

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['sequential'] = $request->boolean('sequential', true);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['requires_approval'] = $request->boolean('requires_approval', false);

        $course->update($validated);

        $this->syncModulesAndStages($course, $validated['modules'] ?? [], $validated['stages'] ?? []);

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

    public function searchModules(Request $request, LmsEvent $event): JsonResponse
    {
        $q = $request->query('q', '');
        $courseIds = $event->courses()->pluck('id');

        $modules = LmsCourseModule::whereIn('lms_course_id', $courseIds)
            ->when($q, fn ($query) => $query->where('title', 'ilike', "%{$q}%"))
            ->with(['course:id,title', 'stages.blocks'])
            ->orderBy('title')
            ->limit(20)
            ->get(['id', 'lms_course_id', 'title', 'description', 'position', 'available_from', 'available_to', 'unlock_type']);

        return response()->json($modules);
    }

    public function searchStages(Request $request, LmsEvent $event): JsonResponse
    {
        $q = $request->query('q', '');
        $courseIds = $event->courses()->pluck('id');

        $stages = LmsCourseStage::whereIn('lms_course_id', $courseIds)
            ->when($q, fn ($query) => $query->where('title', 'ilike', "%{$q}%"))
            ->with(['course:id,title', 'module:id,title', 'blocks'])
            ->orderBy('title')
            ->limit(20)
            ->get();

        return response()->json($stages);
    }

    public function searchBlocks(Request $request, LmsEvent $event): JsonResponse
    {
        $q = $request->query('q', '');
        $courseIds = $event->courses()->pluck('id');

        $blocks = LmsStageBlock::whereHas('stage', fn ($query) => $query->whereIn('lms_course_id', $courseIds))
            ->with(['stage:id,title,lms_course_id,lms_course_module_id', 'stage.course:id,title', 'stage.module:id,title'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('content', 'ilike', "%{$q}%")
                        ->orWhereHas('stage', fn ($s) => $s->where('title', 'ilike', "%{$q}%"));
                });
            })
            ->orderByDesc('id')
            ->limit(30)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'type' => $b->type,
                'content' => $b->content,
                'scorm_package' => $b->scorm_package,
                'lms_test_id' => $b->lms_test_id,
                'lms_assignment_id' => $b->lms_assignment_id,
                'lms_video_id' => $b->lms_video_id,
                'stage_title' => $b->stage?->title,
                'course_title' => $b->stage?->course?->title,
                'module_title' => $b->stage?->module?->title,
            ]);

        return response()->json($blocks);
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

    private function courseRules(): array
    {
        $stageRules = [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'position' => ['nullable', 'integer'],
            'is_locked' => ['nullable', 'boolean'],
            'available_from' => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
        ];

        $blockRules = [
            'type' => ['required', 'string', 'in:content,scorm,test,assignment,video'],
            'content' => ['nullable', 'string'],
            'position' => ['nullable', 'integer'],
        ];

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:500'],
            'sequential' => ['boolean'],
            'is_active' => ['boolean'],
            'requires_approval' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'modules' => ['nullable', 'array'],
            'modules.*.title' => ['required', 'string', 'max:255'],
            'modules.*.description' => ['nullable', 'string'],
            'modules.*.position' => ['nullable', 'integer'],
            'modules.*.available_from' => ['nullable', 'date'],
            'modules.*.available_to' => ['nullable', 'date'],
            'modules.*.source_module_id' => ['nullable', 'integer', 'exists:lms_course_modules,id'],
            'modules.*.stages' => ['nullable', 'array'],
            'stages' => ['nullable', 'array'],
        ];

        $stageRules['source_stage_id'] = ['nullable', 'integer', 'exists:lms_course_stages,id'];
        $stageRules['blocks'] = ['nullable', 'array'];

        foreach ($stageRules as $field => $fieldRules) {
            $rules["modules.*.stages.*.{$field}"] = $fieldRules;
            $rules["stages.*.{$field}"] = $fieldRules;
        }

        foreach ($blockRules as $field => $fieldRules) {
            $rules["modules.*.stages.*.blocks.*.{$field}"] = $fieldRules;
            $rules["stages.*.blocks.*.{$field}"] = $fieldRules;
        }

        return $rules;
    }

    private function syncModulesAndStages(LmsCourse $course, array $modules, array $orphanStages): void
    {
        $course->stages()->delete();
        $course->modules()->delete();

        foreach ($modules as $mIndex => $module) {
            $mod = LmsCourseModule::create([
                'lms_course_id' => $course->id,
                'title' => $module['title'],
                'description' => $module['description'] ?? null,
                'position' => $module['position'] ?? $mIndex,
                'available_from' => $module['available_from'] ?? null,
                'available_to' => $module['available_to'] ?? null,
                'unlock_type' => 'date',
                'source_module_id' => $module['source_module_id'] ?? null,
            ]);

            foreach ($module['stages'] ?? [] as $sIndex => $stage) {
                $this->createStage($course, $stage, $sIndex, $mod->id);
            }
        }

        foreach ($orphanStages as $index => $stage) {
            $this->createStage($course, $stage, $index, null);
        }
    }

    private function createStage(LmsCourse $course, array $stage, int $index, ?int $moduleId): void
    {
        $blocks = $stage['blocks'] ?? [];

        $primaryType = null;
        $primaryContent = null;
        if (!empty($blocks)) {
            $primaryType = $blocks[0]['type'] ?? 'content';
            $primaryContent = $blocks[0]['content'] ?? null;
        } else {
            $primaryType = $stage['type'] ?? 'content';
            $primaryContent = $stage['content'] ?? null;
        }

        $data = [
            'lms_course_id' => $course->id,
            'lms_course_module_id' => $moduleId,
            'title' => $stage['title'],
            'type' => $primaryType,
            'content' => $primaryContent,
            'position' => $stage['position'] ?? $index,
            'is_locked' => $stage['is_locked'] ?? false,
            'available_from' => $stage['available_from'] ?? null,
            'duration_minutes' => $stage['duration_minutes'] ?? null,
            'source_stage_id' => $stage['source_stage_id'] ?? null,
        ];

        $this->applyTypeFields($data, $primaryType, $primaryContent);

        $stageModel = LmsCourseStage::create($data);

        if (!empty($blocks)) {
            foreach ($blocks as $bIndex => $block) {
                $blockData = [
                    'lms_course_stage_id' => $stageModel->id,
                    'type' => $block['type'] ?? 'content',
                    'content' => $block['content'] ?? null,
                    'position' => $block['position'] ?? $bIndex,
                ];
                $this->applyBlockTypeFields($blockData, $block['type'] ?? 'content', $block['content'] ?? null);
                LmsStageBlock::create($blockData);
            }
        } else {
            LmsStageBlock::create([
                'lms_course_stage_id' => $stageModel->id,
                'type' => $primaryType ?? 'content',
                'content' => $primaryContent,
                'position' => 0,
                'lms_test_id' => $data['lms_test_id'] ?? null,
                'lms_assignment_id' => $data['lms_assignment_id'] ?? null,
                'lms_video_id' => $data['lms_video_id'] ?? null,
                'scorm_package' => $data['scorm_package'] ?? null,
            ]);
        }
    }

    private function applyTypeFields(array &$data, ?string $type, ?string $content): void
    {
        if ($type === 'test' && $content && is_numeric($content)) {
            $data['lms_test_id'] = (int) $content;
        }
        if ($type === 'assignment' && $content && is_numeric($content)) {
            $data['lms_assignment_id'] = (int) $content;
        }
        if ($type === 'video' && $content && is_numeric($content)) {
            $data['lms_video_id'] = (int) $content;
        }
        if ($type === 'scorm' && $content) {
            $data['scorm_package'] = $content;
        }
    }

    private function applyBlockTypeFields(array &$data, string $type, ?string $content): void
    {
        if ($type === 'test' && $content && is_numeric($content)) {
            $data['lms_test_id'] = (int) $content;
        }
        if ($type === 'assignment' && $content && is_numeric($content)) {
            $data['lms_assignment_id'] = (int) $content;
        }
        if ($type === 'video' && $content && is_numeric($content)) {
            $data['lms_video_id'] = (int) $content;
        }
        if ($type === 'scorm' && $content) {
            $data['scorm_package'] = $content;
        }
    }

    private function ensureCourseBelongsToEvent(LmsCourse $course, LmsEvent $event): void
    {
        if ($course->lms_event_id !== $event->id) {
            abort(404);
        }
    }
}
