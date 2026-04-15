<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\TourCabinetHubPageData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TourCabinetHubController extends Controller
{
    /**
     * Обзор раздела настроек личного кабинета туров / конкурса в админке портала.
     */
    public function index(Request $request, TourCabinetHubPageData $hubPageData): Response
    {
        return Inertia::render('Admin/TourCabinet/Hub', [
            'formsSection' => $hubPageData->formsPayload(),
            'directionCitiesSection' => $hubPageData->directionCitiesPayloadFromRequest($request),
            'stage2Section' => $hubPageData->stage2QuestionsPayload(),
        ]);
    }
}
