<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtomsVkusaContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AtomsVkusaController extends Controller
{
    public function edit(): Response
    {
        $content = AtomsVkusaContent::content();

        return Inertia::render('Admin/AtomsVkusa/Form', [
            'content' => $content,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if ($request->has('reviews')) {
            $reviews = $request->input('reviews', []);
            foreach ($reviews as $i => $review) {
                if (isset($review['rating']) && $review['rating'] === '') {
                    $reviews[$i]['rating'] = null;
                }
            }
            $request->merge(['reviews' => $reviews]);
        }

        $validated = $request->validate([
            'hero_title' => 'nullable|string|max:500',
            'hero_description' => 'nullable|string|max:5000',
            'hero_image' => 'nullable|string|max:500',

            'competition_stages' => 'nullable|array',
            'competition_stages.*.title' => 'required|string|max:500',
            'competition_stages.*.description' => 'nullable|string|max:2000',

            'participation_conditions' => 'nullable|array',
            'participation_conditions.*.title' => 'required|string|max:500',
            'participation_conditions.*.description' => 'nullable|string|max:2000',

            'selection_criteria' => 'nullable|array',
            'selection_criteria.*.title' => 'required|string|max:500',
            'selection_criteria.*.description' => 'nullable|string|max:2000',

            'results_year' => 'nullable|string|max:10',
            'results_content' => 'nullable|string|max:10000',
            'results_gallery' => 'nullable|array',
            'results_gallery.*.url' => 'required|string|max:500',
            'results_gallery.*.caption' => 'nullable|string|max:500',
            'results_videos' => 'nullable|array',
            'results_videos.*.url' => 'required|string|max:500',
            'results_videos.*.title' => 'nullable|string|max:500',
            'results_cases' => 'nullable|array',
            'results_cases.*.name' => 'required|string|max:255',
            'results_cases.*.city' => 'nullable|string|max:255',
            'results_cases.*.text' => 'nullable|string|max:2000',
            'results_cases.*.image' => 'nullable|string|max:500',

            'why_important_content' => 'nullable|string|max:10000',
            'why_important_stats' => 'nullable|array',
            'why_important_stats.*.value' => 'required|string|max:100',
            'why_important_stats.*.label' => 'required|string|max:255',

            'map_cities' => 'nullable|array',
            'map_cities.*.name' => 'required|string|max:255',
            'map_cities.*.lat' => 'required|numeric',
            'map_cities.*.lng' => 'required|numeric',
            'map_cities.*.recipe_title' => 'nullable|string|max:500',
            'map_cities.*.recipe_image' => 'nullable|string|max:500',

            'application_form_title' => 'nullable|string|max:500',
            'application_form_text' => 'nullable|string|max:5000',

            'partners' => 'nullable|array',
            'partners.*.name' => 'required|string|max:255',
            'partners.*.logo' => 'nullable|string|max:500',
            'partners.*.url' => 'nullable|string|max:500',

            'reviews' => 'nullable|array',
            'reviews.*.name' => 'required|string|max:255',
            'reviews.*.role' => 'nullable|string|max:255',
            'reviews.*.text' => 'required|string|max:2000',
            'reviews.*.rating' => 'nullable|integer|min:1|max:5',
            'reviews.*.avatar' => 'nullable|string|max:500',

            'tourism_help_content' => 'nullable|string|max:10000',
            'tourism_help_items' => 'nullable|array',
            'tourism_help_items.*.title' => 'required|string|max:500',
            'tourism_help_items.*.description' => 'nullable|string|max:2000',
            'tourism_help_items.*.image' => 'nullable|string|max:500',
        ]);

        $content = AtomsVkusaContent::content();
        $content->fill($validated);
        $content->save();

        return back()->with('success', 'Контент «Атомы вкуса» сохранён.');
    }
}
