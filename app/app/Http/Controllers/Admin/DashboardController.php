<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\City;
use App\Models\Tour;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'cities' => City::count(),
                'tours' => Tour::count(),
                'applications' => Application::count(),
            ],
            'recentApplications' => Application::with(['tour', 'tourDeparture'])
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }
}
