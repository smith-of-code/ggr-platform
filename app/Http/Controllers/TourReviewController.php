<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\TourReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TourReviewController extends Controller
{
    public function store(Request $request, Tour $tour): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'text' => 'nullable|string|max:2000',
        ]);

        $existing = TourReview::where('tour_id', $tour->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            return back()->with('error', 'Вы уже оставили отзыв на этот тур.');
        }

        TourReview::create([
            'tour_id' => $tour->id,
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'text' => $validated['text'] ?? null,
            'is_approved' => false,
        ]);

        return back()->with('success', 'Спасибо! Ваш отзыв отправлен на модерацию.');
    }
}
