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
            'infrastructure' => 'nullable|array',
            'position' => 'nullable|integer',
            'is_active' => 'boolean',
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
            'infrastructure' => 'nullable|array',
            'position' => 'nullable|integer',
            'is_active' => 'boolean',
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
