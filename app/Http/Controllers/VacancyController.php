<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class VacancyController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Vacancy::where('is_published', true)
            ->whereNotNull('published_at')
            ->with('city')
            ->orderBy('position')
            ->orderByDesc('published_at');

        if ($request->filled('city')) {
            $query->where('city_id', $request->city);
        }

        if ($request->filled('type')) {
            $query->where('employment_type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('company', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        $vacancies = $query->paginate(12)->withQueryString();

        $cities = City::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Vacancies/Index', [
            'vacancies' => $vacancies,
            'cities' => $cities,
            'filters' => $request->only('city', 'type', 'search'),
        ]);
    }

    public function show(Vacancy $vacancy): Response
    {
        if (!$vacancy->is_published) {
            abort(404);
        }

        $vacancy->load('city');

        $related = Vacancy::where('is_published', true)
            ->whereNotNull('published_at')
            ->where('id', '!=', $vacancy->id)
            ->when($vacancy->city_id, fn($q) => $q->where('city_id', $vacancy->city_id))
            ->limit(3)
            ->get();

        return Inertia::render('Vacancies/Show', [
            'vacancy' => $vacancy,
            'relatedVacancies' => $related,
        ]);
    }
}
