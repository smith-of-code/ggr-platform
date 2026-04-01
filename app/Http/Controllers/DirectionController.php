<?php

namespace App\Http\Controllers;

use App\Models\AtomsVkusaContent;
use App\Models\City;
use App\Models\Direction;
use App\Models\Post;
use App\Models\Recipe;
use App\Models\Tour;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DirectionController extends Controller
{
    public function show(Request $request, string $slug): Response
    {
        $direction = Direction::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $featuredTours = [];
        $ids = $direction->featured_tour_ids ?? [];

        if (!empty($ids)) {
            $featuredTours = Tour::whereIn('id', $ids)
                ->where('is_active', true)
                ->orderBy('position')
                ->get();
        }

        if ($direction->project_key === 'atoms_vkusa') {
            return $this->showAtomsVkusa($request, $direction, $featuredTours);
        }

        return Inertia::render('Directions/Show', [
            'direction' => $direction,
            'featuredTours' => $featuredTours,
        ]);
    }

    private function showAtomsVkusa(Request $request, Direction $direction, $featuredTours): Response
    {
        $content = AtomsVkusaContent::content();

        $recipesQuery = Recipe::where('is_published', true)
            ->with('city')
            ->latest('published_at');

        if ($request->filled('recipe_city')) {
            $recipesQuery->where('city_id', $request->recipe_city);
        }

        $recipes = $recipesQuery->paginate(12)->withQueryString();
        $recipeCities = City::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $news = Post::where('category', 'atoms_vkusa')
            ->where('is_published', true)
            ->latest('published_at')
            ->take(6)
            ->get();

        return Inertia::render('Directions/ShowAtomsVkusa', [
            'direction' => $direction,
            'content' => $content,
            'featuredTours' => $featuredTours,
            'recipes' => $recipes,
            'recipeCities' => $recipeCities,
            'news' => $news,
            'recipeFilters' => $request->only(['recipe_city']),
        ]);
    }
}
