<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use App\Models\Tour;
use Inertia\Inertia;
use Inertia\Response;

class OpportunityToursController extends Controller
{
    public function index(): Response
    {
        $featuredTours = Tour::where('is_featured', true)
            ->where('is_active', true)
            ->with(['cities', 'departures'])
            ->orderBy('position')
            ->limit(8)
            ->get();

        $directions = Direction::where('is_active', true)
            ->orderBy('position')
            ->get(['id', 'title', 'slug', 'description', 'image', 'project_key']);

        return Inertia::render('OpportunityTours/Index', [
            'featuredTours' => $featuredTours,
            'directions' => $directions,
        ]);
    }
}
