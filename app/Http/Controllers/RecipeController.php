<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecipeController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Recipe::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->with('city')
            ->orderByDesc('published_at');

        if ($request->filled('city')) {
            $query->where('city_id', $request->integer('city'));
        }

        $recipes = $query->paginate(12)->withQueryString();

        $cities = City::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Recipes/Index', [
            'recipes' => $recipes,
            'cities' => $cities,
            'filters' => $request->only(['city']),
        ]);
    }

    public function show(string $slug): Response
    {
        $recipe = Recipe::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->with('city')
            ->firstOrFail();

        return Inertia::render('Recipes/Show', [
            'recipe' => $recipe,
        ]);
    }
}
