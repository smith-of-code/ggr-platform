<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsKbItem;
use App\Models\Lms\LmsKbSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KnowledgeBaseController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $sections = $event->kbSections()
            ->whereNull('parent_id')
            ->with(['children.items', 'items'])
            ->orderBy('position')
            ->orderBy('title')
            ->paginate(15);

        return Inertia::render('Lms/Admin/KnowledgeBase/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'sections' => $sections,
        ]);
    }

    public function create(LmsEvent $event): Response
    {
        $parentSections = $event->kbSections()->whereNull('parent_id')->orderBy('position')->get(['id', 'title']);
        $groups = $event->groups()->orderBy('title')->get(['id', 'title']);

        return Inertia::render('Lms/Admin/KnowledgeBase/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'section' => null,
            'parentSections' => $parentSections,
            'groups' => $groups,
        ]);
    }

    public function store(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:lms_kb_sections,id'],
            'in_menu' => ['boolean'],
            'position' => ['nullable', 'integer'],
            'items' => ['nullable', 'array'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.type' => ['nullable', 'string'],
            'items.*.content' => ['nullable', 'string'],
            'items.*.url' => ['nullable', 'string'],
            'items.*.file_path' => ['nullable', 'string'],
            'items.*.position' => ['nullable', 'integer'],
        ]);

        $validated['lms_event_id'] = $event->id;
        $validated['in_menu'] = $request->boolean('in_menu', false);
        $validated['position'] = $validated['position'] ?? 0;

        $section = LmsKbSection::create($validated);

        if ($request->filled('group_ids')) {
            $section->groups()->sync($request->group_ids);
        }

        $this->syncItems($section, $validated['items'] ?? []);

        return redirect()->route('lms.admin.kb.index', $event)->with('success', 'Раздел создан');
    }

    public function edit(LmsEvent $event, LmsKbSection $kb): Response
    {
        $this->ensureSectionBelongsToEvent($kb, $event);

        $kb->load(['items', 'groups']);
        $parentSections = $event->kbSections()->whereNull('parent_id')->where('id', '!=', $kb->id)->orderBy('position')->get(['id', 'title']);
        $groups = $event->groups()->orderBy('title')->get(['id', 'title']);

        return Inertia::render('Lms/Admin/KnowledgeBase/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'section' => $kb,
            'parentSections' => $parentSections,
            'groups' => $groups,
        ]);
    }

    public function update(Request $request, LmsEvent $event, LmsKbSection $kb): RedirectResponse
    {
        $this->ensureSectionBelongsToEvent($kb, $event);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:lms_kb_sections,id'],
            'in_menu' => ['boolean'],
            'position' => ['nullable', 'integer'],
            'items' => ['nullable', 'array'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.type' => ['nullable', 'string'],
            'items.*.content' => ['nullable', 'string'],
            'items.*.url' => ['nullable', 'string'],
            'items.*.file_path' => ['nullable', 'string'],
            'items.*.position' => ['nullable', 'integer'],
        ]);

        $validated['in_menu'] = $request->boolean('in_menu', false);
        $validated['position'] = $validated['position'] ?? 0;

        $kb->update($validated);

        $kb->groups()->sync($request->group_ids ?? []);

        $this->syncItems($kb, $validated['items'] ?? []);

        return redirect()->route('lms.admin.kb.index', $event)->with('success', 'Раздел обновлён');
    }

    public function destroy(LmsEvent $event, LmsKbSection $kb): RedirectResponse
    {
        $this->ensureSectionBelongsToEvent($kb, $event);

        $kb->delete();

        return redirect()->route('lms.admin.kb.index', $event)->with('success', 'Раздел удалён');
    }

    private function syncItems(LmsKbSection $section, array $items): void
    {
        $section->items()->delete();

        foreach ($items as $index => $item) {
            LmsKbItem::create([
                'lms_kb_section_id' => $section->id,
                'title' => $item['title'],
                'type' => $item['type'] ?? 'text',
                'content' => $item['content'] ?? null,
                'url' => $item['url'] ?? null,
                'file_path' => $item['file_path'] ?? null,
                'position' => $item['position'] ?? $index,
            ]);
        }
    }

    private function ensureSectionBelongsToEvent(LmsKbSection $section, LmsEvent $event): void
    {
        if ($section->lms_event_id !== $event->id) {
            abort(404);
        }
    }
}
