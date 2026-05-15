<?php

namespace App\Http\Controllers\TourCabinet\Archives;

use App\Http\Controllers\Controller;
use App\Models\TourCabinetCommerceArchive;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommerceArchiveController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        $archives = TourCabinetCommerceArchive::query()
            ->where('user_id', $userId)
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->through(fn (TourCabinetCommerceArchive $a) => [
                'id' => $a->id,
                'submitted_at' => $a->submitted_at?->toIso8601String(),
                'status' => $a->status,
                'city_name' => data_get($a->payload, 'city.name'),
                'tour_title' => data_get($a->payload, 'tour.title'),
            ]);

        return Inertia::render('TourCabinet/Archives/Commerce/Index', [
            'archives' => $archives,
        ]);
    }

    public function show(Request $request, TourCabinetCommerceArchive $archive): Response
    {
        if ((int) $archive->user_id !== (int) $request->user()->id) {
            abort(404);
        }

        return Inertia::render('TourCabinet/Archives/Commerce/Show', [
            'archive' => [
                'id' => $archive->id,
                'submitted_at' => $archive->submitted_at?->toIso8601String(),
                'status' => $archive->status,
                'payload' => $archive->payload,
            ],
        ]);
    }
}
