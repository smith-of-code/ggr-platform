<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CityController extends Controller
{
    public function index(): Response
    {
        $cities = City::orderBy('position')->orderBy('name')->paginate(15);

        return Inertia::render('Admin/Cities/Index', [
            'cities' => $cities,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Cities/Form');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:cities,slug',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'coat_of_arms' => 'nullable|string|max:2048',
            'infrastructure' => 'nullable|array',
            'position' => 'nullable|integer',
            'is_active' => 'boolean',
            'region' => 'nullable|string|max:255',
            'population' => 'nullable|integer|min:0',
            'founded_year' => 'nullable|integer|min:1000|max:2100',
            'population_year' => 'nullable|integer|min:1900|max:2100',
            'timezone' => 'nullable|string|max:20',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'attractions' => 'nullable|array',
            'attractions.*.title' => 'required|string|max:255',
            'attractions.*.description' => 'nullable|string',
            'attractions.*.image' => 'nullable|string|max:2048',
            'social_objects' => 'nullable|array',
            'gallery' => 'nullable|array',
            'gallery.*' => 'string|max:2048',
            'video_url' => 'nullable|string|max:2048',
            'facts' => 'nullable|array',
            'facts.*.title' => 'required|string|max:5000',
            'facts.*.url' => 'nullable|string|max:2048',
            'facts.*.description' => 'nullable|string',
            'energy_cities_block' => 'nullable|array',
            'energy_cities_block.section_title' => 'nullable|string|max:500',
            'energy_cities_block.section_subtitle' => 'nullable|string|max:1000',
            'energy_cities_block.video_url' => 'nullable|string|max:2048',
            'energy_cities_block.video_title' => 'nullable|string|max:500',
            'energy_cities_block.video_subtitle' => 'nullable|string|max:1000',
            'energy_cities_block.description' => 'nullable|string',
            'energy_cities_block.button_text' => 'nullable|string|max:255',
            'energy_cities_block.button_url' => 'nullable|string|max:2048',
            'block_visibility' => 'nullable|array',
            'block_visibility.facts' => 'boolean',
            'block_visibility.infrastructure' => 'boolean',
            'block_visibility.video' => 'boolean',
            'block_visibility.attractions' => 'boolean',
            'block_visibility.social_objects' => 'boolean',
            'block_visibility.energy_cities_block' => 'boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        City::create($validated);

        return redirect()->route('admin.cities.index')->with('success', 'Город создан');
    }

    public function edit(City $city): Response
    {
        return Inertia::render('Admin/Cities/Form', [
            'city' => $city,
        ]);
    }

    public function update(Request $request, City $city): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:cities,slug,' . $city->id,
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'coat_of_arms' => 'nullable|string|max:2048',
            'infrastructure' => 'nullable|array',
            'position' => 'nullable|integer',
            'is_active' => 'boolean',
            'region' => 'nullable|string|max:255',
            'population' => 'nullable|integer|min:0',
            'founded_year' => 'nullable|integer|min:1000|max:2100',
            'population_year' => 'nullable|integer|min:1900|max:2100',
            'timezone' => 'nullable|string|max:20',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'attractions' => 'nullable|array',
            'attractions.*.title' => 'required|string|max:255',
            'attractions.*.description' => 'nullable|string',
            'attractions.*.image' => 'nullable|string|max:2048',
            'social_objects' => 'nullable|array',
            'gallery' => 'nullable|array',
            'gallery.*' => 'string|max:2048',
            'video_url' => 'nullable|string|max:2048',
            'facts' => 'nullable|array',
            'facts.*.title' => 'required|string|max:5000',
            'facts.*.url' => 'nullable|string|max:2048',
            'facts.*.description' => 'nullable|string',
            'energy_cities_block' => 'nullable|array',
            'energy_cities_block.section_title' => 'nullable|string|max:500',
            'energy_cities_block.section_subtitle' => 'nullable|string|max:1000',
            'energy_cities_block.video_url' => 'nullable|string|max:2048',
            'energy_cities_block.video_title' => 'nullable|string|max:500',
            'energy_cities_block.video_subtitle' => 'nullable|string|max:1000',
            'energy_cities_block.description' => 'nullable|string',
            'energy_cities_block.button_text' => 'nullable|string|max:255',
            'energy_cities_block.button_url' => 'nullable|string|max:2048',
            'block_visibility' => 'nullable|array',
            'block_visibility.facts' => 'boolean',
            'block_visibility.infrastructure' => 'boolean',
            'block_visibility.video' => 'boolean',
            'block_visibility.attractions' => 'boolean',
            'block_visibility.social_objects' => 'boolean',
            'block_visibility.energy_cities_block' => 'boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $city->update($validated);

        return redirect()->route('admin.cities.index')->with('success', 'Город обновлён');
    }

    public function destroy(City $city): RedirectResponse
    {
        $city->delete();

        return redirect()->route('admin.cities.index')->with('success', 'Город удалён');
    }

    public function toggleActive(City $city): RedirectResponse
    {
        $city->update(['is_active' => !$city->is_active]);

        return redirect()->back()->with('success', $city->is_active ? 'Город активирован' : 'Город скрыт');
    }
}
