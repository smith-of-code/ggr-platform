<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Tour;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $featuredTours = Tour::where('is_featured', true)
            ->where('is_active', true)
            ->with(['cities', 'departures'])
            ->orderBy('position')
            ->limit(6)
            ->get();

        $cities = City::where('is_active', true)
            ->orderBy('position')
            ->limit(6)
            ->get();

        $citiesCount = $cities->count();
        $toursCount = Tour::where('is_active', true)->count();

        return Inertia::render('Home', [
            'featuredTours' => $featuredTours,
            'cities' => $cities,
            'stats' => [
                'cities' => $citiesCount,
                'tours' => $toursCount,
            ],
        ]);
    }
}
