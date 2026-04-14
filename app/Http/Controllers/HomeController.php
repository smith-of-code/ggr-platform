<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\MainPageController;
use App\Models\Application;
use App\Models\City;
use App\Models\Consent;
use App\Models\ContactSubmission;
use App\Models\Favorite;
use App\Models\Post;
use App\Models\Recipe;
use App\Models\TimelineEvent;
use App\Models\Tour;
use App\Services\ConsentService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('MainPage', $this->buildHomePageProps($request));
    }

    public function moving(): Response
    {
        return Inertia::render('Moving');
    }

    private function buildMainPageData(): array
    {
        $settings = app(SettingsService::class);
        $raw = $settings->getGroup('main_page');
        $defaults = MainPageController::defaults();

        $jsonKeys = [
            'block_order', 'program_stages', 'program_cities', 'program_results',
            'city_benefits', 'additional_initiatives', 'videos',
            'contact_bullets', 'contacts', 'socials', 'section_titles',
            'stats_cards', 'video_presentation',
        ];

        $data = [];
        foreach ($defaults as $key => $value) {
            $data[$key] = $raw[$key] ?? $value;
        }
        foreach ($raw as $key => $value) {
            if (!array_key_exists($key, $data)) {
                $data[$key] = $value;
            }
        }
        foreach ($jsonKeys as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                $data[$key] = json_decode($data[$key], true) ?? [];
            }
        }

        if (empty($data['block_order'])) {
            $data['block_order'] = MainPageController::defaultBlockOrder();
        } else {
            $existingIds = array_column($data['block_order'], 'id');
            foreach (MainPageController::defaultBlockOrder() as $block) {
                if (!in_array($block['id'], $existingIds, true)) {
                    $data['block_order'][] = $block;
                }
            }
        }

        return $data;
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
            'pageData' => $this->buildMainPageData(),
        ];
    }

    public function contactSubmit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns',
            'phone' => 'required|string|max:50',
            'message' => 'required|string|max:5000',
            'consent' => ['accepted'],
        ], [
            'email.email' => 'Введите корректный email-адрес.',
            'phone.required' => 'Введите номер телефона.',
            'consent.accepted' => 'Необходимо дать согласие на обработку персональных данных.',
        ]);

        $validated['phone'] = self::normalizePhone($validated['phone']);

        Application::create([
            'type' => 'program_info',
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'data' => ['message' => $validated['message']],
            'status' => 'new',
        ]);

        if (Schema::hasTable('contact_submissions')) {
            ContactSubmission::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'message' => $validated['message'],
                'source' => 'home_contact',
            ]);
        }

        ConsentService::log($request, Consent::TYPE_CONTACT_FORM, [
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        return back()->with('success', 'Сообщение отправлено. Мы свяжемся с вами в ближайшее время.');
    }

    /**
     * @throws ValidationException
     */
    private static function normalizePhone(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value);

        if (str_starts_with($digits, '8') && strlen($digits) === 11) {
            $digits = '7' . substr($digits, 1);
        }

        if (!preg_match('/^7\d{10}$/', $digits)) {
            throw ValidationException::withMessages([
                'phone' => 'Введите корректный номер телефона в формате +7 (XXX) XXX-XX-XX',
            ]);
        }

        return '+' . $digits;
    }
}
