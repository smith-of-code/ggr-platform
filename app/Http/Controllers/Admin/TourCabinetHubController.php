<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use Inertia\Inertia;
use Inertia\Response;

class TourCabinetHubController extends Controller
{
    /**
     * Обзор раздела настроек личного кабинета туров / конкурса в админке портала.
     */
    public function index(): Response
    {
        $slug = config('tour_cabinet.lms_event_slug');
        $event = LmsEvent::where('slug', $slug)->first();

        return Inertia::render('Admin/TourCabinet/Hub', [
            'lmsEvent' => $event?->only(['id', 'slug', 'title']),
            'configSlug' => $slug,
        ]);
    }
}
