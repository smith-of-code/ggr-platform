<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CityController extends Controller
{
    public function index(): Response
    {
        $cities = City::where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return Inertia::render('Cities/Index', [
            'cities' => $cities,
        ]);
    }

    public function show(string $slug): Response
    {
        $city = City::where('slug', $slug)
            ->where('is_active', true)
            ->with(['tours' => fn ($q) => $q->where('is_active', true)->with('departures')])
            ->firstOrFail();

        return Inertia::render('Cities/Show', [
            'city' => $city,
        ]);
    }
}
