<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Tour;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TourController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Tour::where('is_active', true)
            ->with(['cities', 'departures']);

        if ($request->filled('project')) {
            $query->where('project', $request->project);
        }
        if ($request->filled('season')) {
            $query->where('season', $request->season);
        }
        if ($request->filled('participation_type')) {
            $query->where('participation_type', $request->participation_type);
        }
        if ($request->filled('city')) {
            $query->whereHas('cities', fn ($q) => $q->where('cities.id', $request->city));
        }
        if ($request->boolean('for_children')) {
            $query->where('for_children', true);
        }
        if ($request->boolean('for_foreigners')) {
            $query->where('for_foreigners', true);
        }

        $tours = $query->orderBy('position')->orderBy('title')->paginate(12);
        $cities = City::where('is_active', true)->orderBy('name')->get();

        return Inertia::render('Tours/Index', [
            'tours' => $tours,
            'cities' => $cities,
            'filters' => $request->only(['project', 'season', 'participation_type', 'city', 'for_children', 'for_foreigners']),
        ]);
    }

    public function show(string $slug): Response
    {
        $tour = Tour::where('slug', $slug)
            ->where('is_active', true)
            ->with(['cities', 'departures', 'media'])
            ->firstOrFail();

        return Inertia::render('Tours/Show', [
            'tour' => $tour,
        ]);
    }
}
