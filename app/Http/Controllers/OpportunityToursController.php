<?php

namespace App\Http\Controllers;

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

        return Inertia::render('OpportunityTours/Index', [
            'featuredTours' => $featuredTours,
        ]);
    }
}
