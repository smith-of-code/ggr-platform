<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TourReviewController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TourReview::with(['tour:id,title,slug', 'user:id,name,email'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('is_approved', $request->status === 'approved');
        }

        return Inertia::render('Admin/TourReviews/Index', [
            'reviews' => $query->paginate(20),
            'filters' => $request->only('status'),
        ]);
    }

    public function approve(TourReview $tourReview): RedirectResponse
    {
        $tourReview->update(['is_approved' => true]);

        return back()->with('success', 'Отзыв одобрен.');
    }

    public function reject(TourReview $tourReview): RedirectResponse
    {
        $tourReview->update(['is_approved' => false]);

        return back()->with('success', 'Отзыв отклонён.');
    }

    public function destroy(TourReview $tourReview): RedirectResponse
    {
        $tourReview->delete();

        return back()->with('success', 'Отзыв удалён.');
    }
}
