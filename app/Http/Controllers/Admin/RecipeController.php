<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Recipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RecipeController extends Controller
{
    public function index(): Response
    {
        $recipes = Recipe::query()
            ->with('city')
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('Admin/Recipes/Index', [
            'recipes' => $recipes,
        ]);
    }

    public function create(): Response
    {
        $cities = City::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Admin/Recipes/Form', [
            'cities' => $cities,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedRecipe($request);

        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $validated['is_published'] ? now() : null;

        Recipe::create($validated);

        return redirect()->route('admin.recipes.index')->with('success', 'Рецепт создан');
    }

    public function edit(Recipe $recipe): Response
    {
        $cities = City::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Admin/Recipes/Form', [
            'recipe' => $recipe->load('city'),
            'cities' => $cities,
        ]);
    }

    public function update(Request $request, Recipe $recipe): RedirectResponse
    {
        $validated = $this->validatedRecipe($request, $recipe->id);

        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $validated['is_published']
            ? ($recipe->published_at ?? now())
            : null;

        $recipe->update($validated);

        return redirect()->route('admin.recipes.index')->with('success', 'Рецепт обновлён');
    }

    public function destroy(Recipe $recipe): RedirectResponse
    {
        $recipe->delete();

        return redirect()->route('admin.recipes.index')->with('success', 'Рецепт удалён');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedRecipe(Request $request, ?int $exceptId = null): array
    {
        $slugRule = 'required|string|max:255|unique:recipes,slug';
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
            'cooking_time' => 'nullable|string|max:255',
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
            'servings' => 'nullable|integer|min:1|max:999',
            'ingredients' => 'nullable|array',
            'ingredients.*.name' => 'nullable|string|max:500',
            'ingredients.*.amount' => 'nullable|string|max:255',
            'is_published' => 'boolean',
        ]);
    }
}
