<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Research;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResearchController extends Controller
{
    public function index(): Response
    {
        $researches = Research::query()
            ->with('city')
            ->orderByDesc('year')
            ->orderByDesc('id')
            ->paginate(15);

        return Inertia::render('Admin/Research/Index', [
            'researches' => $researches,
        ]);
    }

    public function create(): Response
    {
        $cities = City::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Admin/Research/Form', [
            'cities' => $cities,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedResearch($request);

        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $validated['is_published'] ? now() : null;

        Research::create($validated);

        return redirect()->route('admin.research.index')->with('success', 'Исследование создано');
    }

    public function edit(Research $research): Response
    {
        $cities = City::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Admin/Research/Form', [
            'research' => $research->load('city'),
            'cities' => $cities,
        ]);
    }

    public function update(Request $request, Research $research): RedirectResponse
    {
        $validated = $this->validatedResearch($request, $research->id);

        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $validated['is_published']
            ? ($research->published_at ?? now())
            : null;

        $research->update($validated);

        return redirect()->route('admin.research.index')->with('success', 'Исследование обновлено');
    }

    public function destroy(Research $research): RedirectResponse
    {
        $research->delete();

        return redirect()->route('admin.research.index')->with('success', 'Исследование удалено');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedResearch(Request $request, ?int $exceptId = null): array
    {
        $slugRule = 'required|string|max:255|unique:researches,slug';
        if ($exceptId !== null) {
            $slugRule .= ',' . $exceptId;
        }

        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => $slugRule,
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'image' => 'nullable|string|max:2048',
            'city_id' => 'nullable|exists:cities,id',
            'year' => 'nullable|integer|min:1900|max:2100',
            'methodology' => 'nullable|string',
            'results_summary' => 'nullable|string',
            'pdf_file' => 'nullable|string|max:2048',
            'is_published' => 'boolean',
        ]);
    }
}
