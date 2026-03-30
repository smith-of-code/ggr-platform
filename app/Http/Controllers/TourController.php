<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Favorite;
use App\Models\Tour;
use App\Models\TourReaction;
use App\Models\TourReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TourController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Tour::where('is_active', true)
            ->with(['cities', 'departures']);

        if ($request->filled('project')) {
            $query->where('project', $request->project);
        }
        if ($request->filled('season')) {
            $query->where('season', $request->season);
        }
        if ($request->filled('participation_type')) {
            $query->where('participation_type', $request->participation_type);
        }
        if ($request->filled('city')) {
            $query->whereHas('cities', fn ($q) => $q->where('cities.id', $request->city));
        }
        if ($request->boolean('for_children')) {
            $query->where('for_children', true);
        }
        if ($request->boolean('for_foreigners')) {
            $query->where('for_foreigners', true);
        }

        $tours = $query->orderBy('position')->orderBy('title')->paginate(12);
        $cities = City::where('is_active', true)->orderBy('name')->get();

        return Inertia::render('Tours/Index', [
            'tours' => $tours,
            'cities' => $cities,
            'filters' => $request->only(['project', 'season', 'participation_type', 'city', 'for_children', 'for_foreigners']),
        ]);
    }

    public function show(string $slug): Response
    {
        $tour = Tour::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['cities', 'departures', 'media'])
            ->firstOrFail();

        $user = auth()->user();
        $userReaction = null;
        $isFavorited = false;
        if ($user !== null) {
            $userReaction = TourReaction::query()
                ->where('tour_id', $tour->id)
                ->where('user_id', $user->id)
                ->value('emoji');
            $isFavorited = Favorite::query()
                ->where('user_id', $user->id)
                ->where('favorable_type', Tour::class)
                ->where('favorable_id', $tour->id)
                ->exists();
        }

        $tour->setAttribute('is_favorited', $isFavorited);
        if ($tour->getAttribute('reactions_count') === null) {
            $tour->setAttribute('reactions_count', []);
        }

        $reviews = TourReview::where('tour_id', $tour->id)
            ->where('is_approved', true)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->get();

        $userReviewExists = $user
            ? TourReview::where('tour_id', $tour->id)->where('user_id', $user->id)->exists()
            : false;

        return Inertia::render('Tours/Show', [
            'tour' => $tour,
            'userReaction' => $userReaction,
            'reviews' => $reviews,
            'userReviewExists' => $userReviewExists,
        ]);
    }

    public function react(Request $request, Tour $tour): RedirectResponse
    {
        $validated = $request->validate([
            'emoji' => 'required|string|in:'.implode(',', array_keys(TourReaction::EMOJIS)),
        ]);

        $emojiKey = $validated['emoji'];

        DB::transaction(function () use ($request, $tour, $emojiKey): void {
            $existing = TourReaction::query()
                ->where('tour_id', $tour->id)
                ->where('user_id', $request->user()->id)
                ->first();

            if ($existing !== null && $existing->emoji === $emojiKey) {
                $existing->delete();
            } else {
                TourReaction::query()->updateOrCreate(
                    [
                        'tour_id' => $tour->id,
                        'user_id' => $request->user()->id,
                    ],
                    [
                        'ip_address' => $request->ip() ?? '',
                        'emoji' => $emojiKey,
                    ]
                );
            }

            $counts = [];
            foreach (array_keys(TourReaction::EMOJIS) as $key) {
                $counts[$key] = $tour->reactions()->where('emoji', $key)->count();
            }

            $tour->update(['reactions_count' => $counts]);
        });

        return back()->with('success', 'Реакция сохранена.');
    }
}
