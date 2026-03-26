<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Favorite;
use Illuminate\Support\Facades\Schema;
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
        $eagerLoad = [
            'tours' => fn ($q) => $q->where('is_active', true)->with('departures'),
        ];

        if (Schema::hasTable('researches')) {
            $eagerLoad['researches'] = fn ($q) => $q
                ->where('is_published', true)
                ->whereNotNull('published_at')
                ->orderByDesc('published_at');
        }

        if (Schema::hasTable('recipes')) {
            $eagerLoad['recipes'] = fn ($q) => $q
                ->where('is_published', true)
                ->whereNotNull('published_at')
                ->orderByDesc('published_at');
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
