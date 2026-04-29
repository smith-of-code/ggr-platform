<?php

namespace App\Services;

use App\Models\Lms\LmsForm;
use App\Models\Lms\LmsFormSubmission;
use App\Models\TourCabinetCommerceCityForm;
use App\Models\TourCabinetCommerceProgress;
use App\Support\PostAuthRedirect;
use Illuminate\Support\Facades\Auth;

class TourCabinetCommerceToursFormLinker
{
    public static function tryLinkAfterSubmission(LmsForm $form, LmsFormSubmission $submission): void
    {
        $user = Auth::user();
        if (! $user || ! PostAuthRedirect::canAccessTourCabinet($user)) {
            return;
        }

        $cityId = (int) session()->get('tour_cabinet_commerce_form_city_id', 0);
        if ($cityId < 1) {
            return;
        }

        $cityForm = TourCabinetCommerceCityForm::query()
            ->where('city_id', $cityId)
            ->first();

        if (! $cityForm || trim((string) $cityForm->lms_form_slug) === '') {
            return;
        }

        if ($form->slug !== $cityForm->lms_form_slug) {
            return;
        }

        $progress = TourCabinetCommerceProgress::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $progress || (int) $progress->city_id !== $cityId) {
            return;
        }

        if ((int) $progress->current_stage < 2) {
            // Defense-in-depth: переход к Этапу 2 разблокирован только после явного
            // нажатия «Перейти к этапу 2 →» (метод `TourCabinetCommerceToursController::completeStage1`).
            // На штатном UI-флоу сюда нельзя попасть — `startCityForm` сам не пускает при `current_stage < 2`,
            // — но дублирующий гард предотвращает скачок 1 → 3 при возможной ручной подмене сессии.
            return;
        }

        if ((int) $progress->current_stage >= 3) {
            session()->forget('tour_cabinet_commerce_form_city_id');

            return;
        }

        $payload = [
            'current_stage' => 3,
            'lms_form_submission_id' => $submission->id,
        ];

        if ($progress->completed_at === null) {
            $payload['completed_at'] = now();
        }

        $progress->fill($payload)->save();

        session()->forget('tour_cabinet_commerce_form_city_id');
    }
}
