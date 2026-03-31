<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Favorite;
use App\Models\Vacancy;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class CityController extends Controller
{
    public function index(\Illuminate\Http\Request $request): Response
    {
        $query = City::where('is_active', true);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('region', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }

        $sort = $request->input('sort', 'position');
        match ($sort) {
            'name' => $query->orderBy('name'),
            'population_desc' => $query->orderByDesc('population'),
            'population_asc' => $query->orderBy('population'),
            default => $query->orderBy('position')->orderBy('name'),
        };

        $cities = $query->get();

        $regions = City::where('is_active', true)
            ->whereNotNull('region')
            ->where('region', '!=', '')
            ->distinct()
            ->orderBy('region')
            ->pluck('region');

        return Inertia::render('Cities/Index', [
            'cities' => $cities,
            'regions' => $regions,
            'filters' => $request->only('search', 'region', 'sort'),
        ]);
    }

    public function show(string $slug): Response
    {
        $eagerLoad = [
            'tours' => fn ($q) => $q->where('is_active', true)->with('departures'),
        ];

        if (Schema::hasTable('recipes')) {
            $eagerLoad['recipes'] = fn ($q) => $q
                ->where('is_published', true)
                ->whereNotNull('published_at')
                ->orderByDesc('published_at');
        }

        if (Schema::hasTable('vacancies')) {
            $eagerLoad['vacancies'] = fn ($q) => $q
                ->where('is_published', true)
                ->orderBy('position')
                ->orderByDesc('created_at');
        }

        $city = City::where('slug', $slug)
            ->where('is_active', true)
            ->with($eagerLoad)
            ->firstOrFail();

        $user = auth()->user();
        $isFavorited = false;
        if ($user !== null && Schema::hasTable('favorites')) {
            $isFavorited = Favorite::query()
                ->where('user_id', $user->id)
                ->where('favorable_type', City::class)
                ->where('favorable_id', $city->id)
                ->exists();
        }

        return Inertia::render('Cities/Show', [
            'city' => $city,
            'isFavorited' => $isFavorited,
        ]);
    }
}
