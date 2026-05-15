<?php

namespace App\Http\Controllers\TourCabinet\Archives;

use App\Http\Controllers\Controller;
use App\Models\TourCabinetContestArchive;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContestArchiveController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        $archives = TourCabinetContestArchive::query()
            ->where('user_id', $userId)
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->through(fn (TourCabinetContestArchive $a) => [
                'id' => $a->id,
                'submitted_at' => $a->submitted_at?->toIso8601String(),
                'status' => $a->status,
                'direction_label' => data_get($a->payload, 'progress.direction_label'),
            ]);

        return Inertia::render('TourCabinet/Archives/Contest/Index', [
            'archives' => $archives,
        ]);
    }

    public function show(Request $request, TourCabinetContestArchive $archive): Response
    {
        if ((int) $archive->user_id !== (int) $request->user()->id) {
            abort(404);
        }

        return Inertia::render('TourCabinet/Archives/Contest/Show', [
            'archive' => [
                'id' => $archive->id,
                'submitted_at' => $archive->submitted_at?->toIso8601String(),
                'status' => $archive->status,
                'payload' => $archive->payload,
            ],
        ]);
    }
}
