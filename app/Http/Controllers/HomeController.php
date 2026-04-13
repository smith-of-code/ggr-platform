<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Consent;
use App\Models\ContactSubmission;
use App\Models\Favorite;
use App\Models\Post;
use App\Models\Recipe;
use App\Models\TimelineEvent;
use App\Models\Tour;
use App\Services\ConsentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Home', $this->buildHomePageProps($request));
    }

    public function mainpage(Request $request): Response
    {
        return Inertia::render('MainPage', $this->buildHomePageProps($request));
    }

    public function moving(): Response
    {
        return Inertia::render('Moving');
    }

    private function buildHomePageProps(Request $request): array
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

        $latestRecipes = [];
        if (Schema::hasTable('recipes')) {
            $latestRecipes = Recipe::query()
                ->where('is_published', true)
                ->whereNotNull('published_at')
                ->with('city')
                ->orderByDesc('published_at')
                ->limit(6)
                ->get();
        }

        $timelineEvents = [];
        if (Schema::hasTable('timeline_events')) {
            $timelineEvents = TimelineEvent::query()
                ->where('is_active', true)
                ->orderByDesc('event_date')
                ->limit(10)
                ->get();
        }

        $latestPosts = [];
        if (Schema::hasTable('posts')) {
            $latestPosts = Post::query()
                ->where('is_published', true)
                ->whereNotNull('published_at')
                ->orderByDesc('published_at')
                ->limit(3)
                ->get(['id', 'title', 'slug', 'excerpt', 'image', 'category', 'published_at']);
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

        return [
            'featuredTours' => $featuredTours,
            'cities' => $cities,
            'allCities' => $allCities,
            'stats' => [
                'cities' => $citiesCount,
                'tours' => $toursCount,
            ],
            'latestRecipes' => $latestRecipes,
            'latestPosts' => $latestPosts,
            'timelineEvents' => $timelineEvents,
            'userFavorites' => $userFavorites,
        ];
    }

    public function contactSubmit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:50',
            'message' => 'required|string|max:5000',
            'consent' => ['accepted'],
        ], [
            'consent.accepted' => 'Необходимо дать согласие на обработку персональных данных.',
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

        ConsentService::log($request, Consent::TYPE_CONTACT_FORM, [
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        return back()->with('success', 'Сообщение отправлено. Мы свяжемся с вами в ближайшее время.');
    }
}
