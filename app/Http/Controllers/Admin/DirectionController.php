<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use App\Models\Tour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DirectionController extends Controller
{
    public function index(): Response
    {
        $directions = Direction::orderBy('position')->orderBy('title')->paginate(15);

        return Inertia::render('Admin/Directions/Index', [
            'directions' => $directions,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Directions/Form', [
            'tours' => Tour::where('is_active', true)->orderBy('title')->get(['id', 'title', 'project']),
            'projectKeys' => Tour::PROJECTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDirection($request);
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['is_active'] = $request->boolean('is_active', true);

        Direction::create($validated);

        return redirect()->route('admin.directions.index')->with('success', 'Направление создано');
    }

    public function edit(Direction $direction): Response
    {
        return Inertia::render('Admin/Directions/Form', [
            'direction' => $direction,
            'tours' => Tour::where('is_active', true)->orderBy('title')->get(['id', 'title', 'project']),
            'projectKeys' => Tour::PROJECTS,
        ]);
    }

    public function update(Request $request, Direction $direction): RedirectResponse
    {
        $validated = $this->validateDirection($request);
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $direction->update($validated);

        return redirect()->route('admin.directions.index')->with('success', 'Направление обновлено');
    }

    public function destroy(Direction $direction): RedirectResponse
    {
        $direction->delete();

        return redirect()->route('admin.directions.index')->with('success', 'Направление удалено');
    }

    public function toggleActive(Direction $direction): RedirectResponse
    {
        $direction->update(['is_active' => !$direction->is_active]);

        return redirect()->back()->with('success', $direction->is_active ? 'Направление активировано' : 'Направление скрыто');
    }

    private function validateDirection(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'project_key' => 'nullable|string|in:start_atomgrad,atoms_vkusa,llr',
            'sub_directions_title' => 'nullable|string|max:255',
            'sub_directions_description' => 'nullable|string',
            'sub_directions' => 'nullable|array',
            'sub_directions.*.name' => 'required|string|max:255',
            'sub_directions.*.description' => 'required|string',
            'target_audiences' => 'nullable|array',
            'target_audiences.*.number' => 'required|integer',
            'target_audiences.*.title' => 'required|string|max:255',
            'target_audiences.*.description' => 'required|string',
            'target_audience_note' => 'nullable|string',
            'free_participation_steps' => 'nullable|array',
            'free_participation_steps.*.title' => 'required|string|max:255',
            'free_participation_steps.*.description' => 'required|string',
            'free_participation_details' => 'nullable|array',
            'paid_participation_steps' => 'nullable|array',
            'paid_participation_steps.*.title' => 'required|string|max:255',
            'paid_participation_steps.*.description' => 'required|string',
            'featured_tour_ids' => 'nullable|array',
            'featured_tour_ids.*' => 'integer|exists:tours,id',
            'position' => 'nullable|integer',
        ]);
    }
}
