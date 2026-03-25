<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Favorite;
use App\Models\Tour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FavoriteController extends Controller
{
    public function toggle(Request $request, string $type, int $id): RedirectResponse
    {
        $favorableClass = match ($type) {
            'city' => City::class,
            'tour' => Tour::class,
            default => null,
        };

        if ($favorableClass === null) {
            abort(404);
        }

        $favorable = $favorableClass::query()->whereKey($id)->firstOrFail();

        $favorite = Favorite::query()
            ->where('user_id', $request->user()->id)
            ->where('favorable_type', $favorableClass)
            ->where('favorable_id', $favorable->id)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return back()->with('success', 'Удалено из избранного.');
        }

        Favorite::query()->create([
            'user_id' => $request->user()->id,
            'favorable_type' => $favorableClass,
            'favorable_id' => $favorable->id,
        ]);

        return back()->with('success', 'Добавлено в избранное.');
    }

    public function index(Request $request): Response
    {
        $favorites = Favorite::query()
            ->where('user_id', $request->user()->id)
            ->with('favorable')
            ->get();

        $cities = $favorites
            ->where('favorable_type', City::class)
            ->pluck('favorable')
            ->filter()
            ->values();

        $tours = $favorites
            ->where('favorable_type', Tour::class)
            ->pluck('favorable')
            ->filter()
            ->values();

        return Inertia::render('Favorites/Index', [
            'favorites' => [
                'cities' => $cities,
                'tours' => $tours,
            ],
        ]);
    }
}
