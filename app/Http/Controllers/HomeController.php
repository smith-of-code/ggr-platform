<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\ContactSubmission;
use App\Models\Favorite;
use App\Models\TimelineEvent;
use App\Models\Tour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        $featuredTours = Tour::where('is_featured', true)
            ->where('is_active', true)
            ->with(['cities', 'departures'])
            ->orderBy('position')
            ->limit(6)
            ->get();

        $cities = City::where('is_active', true)
            ->orderBy('position')
            ->limit(6)
            ->get();

        $allCities = City::where('is_active', true)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->select('id', 'name', 'slug', 'lat', 'lng', 'region', 'population', 'image')
            ->orderBy('position')
            ->get();

        $citiesCount = City::where('is_active', true)->count();
        $toursCount = Tour::where('is_active', true)->count();

        $timelineEvents = [];
        if (Schema::hasTable('timeline_events')) {
            $timelineEvents = TimelineEvent::query()
                ->where('is_active', true)
                ->orderByDesc('event_date')
                ->limit(10)
                ->get();
        }

        $userFavorites = null;
        if ($request->user() && Schema::hasTable('favorites')) {
            $userFavorites = [
                'cityIds' => Favorite::query()
                    ->where('user_id', $request->user()->id)
                    ->where('favorable_type', City::class)
                    ->pluck('favorable_id')
                    ->all(),
                'tourIds' => Favorite::query()
                    ->where('user_id', $request->user()->id)
                    ->where('favorable_type', Tour::class)
                    ->pluck('favorable_id')
                    ->all(),
            ];
        }

        return Inertia::render('Home', [
            'featuredTours' => $featuredTours,
            'cities' => $cities,
            'allCities' => $allCities,
            'stats' => [
                'cities' => $citiesCount,
                'tours' => $toursCount,
            ],
            'timelineEvents' => $timelineEvents,
            'userFavorites' => $userFavorites,
        ]);
    }

    public function contactSubmit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:50',
            'message' => 'required|string|max:5000',
        ]);

        if (!Schema::hasTable('contact_submissions')) {
            return back()->with('success', 'Сообщение отправлено.');
        }

        ContactSubmission::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'],
            'source' => 'home_contact',
        ]);

        return back()->with('success', 'Сообщение отправлено. Мы свяжемся с вами в ближайшее время.');
    }
}
