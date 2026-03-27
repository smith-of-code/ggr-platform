<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use App\Models\Tour;
use Inertia\Inertia;
use Inertia\Response;

class DirectionController extends Controller
{
    public function show(string $slug): Response
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

        return Inertia::render('Directions/Show', [
            'direction' => $direction,
            'featuredTours' => $featuredTours,
        ]);
    }
}
