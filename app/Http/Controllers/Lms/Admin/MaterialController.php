<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsMaterialFile;
use App\Models\Lms\LmsMaterialSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaterialController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $materials = $event->materialSections()
            ->with('groups')
            ->orderBy('position')
            ->orderBy('title')
            ->paginate(15);

        return Inertia::render('Lms/Admin/Materials/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'materials' => $materials,
        ]);
    }

    public function create(LmsEvent $event): Response
    {
        $groups = $event->groups()->orderBy('title')->get(['id', 'title']);

        return Inertia::render('Lms/Admin/Materials/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'material' => null,
            'groups' => $groups,
        ]);
    }

    public function store(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'in_menu' => ['boolean'],
            'position' => ['nullable', 'integer'],
            'files' => ['nullable', 'array'],
            'files.*.title' => ['required', 'string', 'max:255'],
            'files.*.file_path' => ['required', 'string', 'max:500'],
            'files.*.file_name' => ['required', 'string', 'max:255'],
        ]);

        $validated['lms_event_id'] = $event->id;
        $validated['in_menu'] = $request->boolean('in_menu', false);
        $validated['position'] = $validated['position'] ?? 0;

        $material = LmsMaterialSection::create($validated);

        if ($request->filled('group_ids')) {
            $material->groups()->sync($request->group_ids);
        }

        $this->syncFiles($material, $request->input('files', []));

        return redirect()->route('lms.admin.materials.index', $event)->with('success', 'Раздел материалов создан');
    }

    public function edit(LmsEvent $event, LmsMaterialSection $material): Response
    {
        $this->ensureMaterialBelongsToEvent($material, $event);

        $material->load(['groups', 'files']);
        $groups = $event->groups()->orderBy('title')->get(['id', 'title']);

        return Inertia::render('Lms/Admin/Materials/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'material' => $material,
            'groups' => $groups,
        ]);
    }

    public function update(Request $request, LmsEvent $event, LmsMaterialSection $material): RedirectResponse
    {
        $this->ensureMaterialBelongsToEvent($material, $event);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'in_menu' => ['boolean'],
            'position' => ['nullable', 'integer'],
            'files' => ['nullable', 'array'],
            'files.*.id' => ['nullable', 'integer'],
            'files.*.title' => ['required', 'string', 'max:255'],
            'files.*.file_path' => ['required', 'string', 'max:500'],
            'files.*.file_name' => ['required', 'string', 'max:255'],
        ]);

        $validated['in_menu'] = $request->boolean('in_menu', false);
        $validated['position'] = $validated['position'] ?? 0;

        $material->update($validated);

        $material->groups()->sync($request->group_ids ?? []);

        $this->syncFiles($material, $request->input('files', []));

        return redirect()->route('lms.admin.materials.index', $event)->with('success', 'Раздел материалов обновлён');
    }

    public function destroy(LmsEvent $event, LmsMaterialSection $material): RedirectResponse
    {
        $this->ensureMaterialBelongsToEvent($material, $event);

        $material->delete();

        return redirect()->route('lms.admin.materials.index', $event)->with('success', 'Раздел материалов удалён');
    }

    private function syncFiles(LmsMaterialSection $material, array $files): void
    {
        $incomingIds = collect($files)->pluck('id')->filter()->all();

        $material->files()->whereNotIn('id', $incomingIds)->delete();

        foreach ($files as $i => $fileData) {
            $attrs = [
                'title' => $fileData['title'],
                'file_path' => $fileData['file_path'],
                'file_name' => $fileData['file_name'],
                'file_size' => $fileData['file_size'] ?? null,
                'position' => $i,
            ];

            if (!empty($fileData['id'])) {
                LmsMaterialFile::where('id', $fileData['id'])
                    ->where('lms_material_section_id', $material->id)
                    ->update($attrs);
            } else {
                $material->files()->create($attrs);
            }
        }
    }

    private function ensureMaterialBelongsToEvent(LmsMaterialSection $material, LmsEvent $event): void
    {
        if ($material->lms_event_id !== $event->id) {
            abort(404);
        }
    }
}
