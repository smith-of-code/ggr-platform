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
    /**
     * Session-маркер, который выставляется linker'ом после успешной архивации;
     * `FormPublicController::submit` забирает его через `session()->pull(...)`,
     * чтобы редиректнуть участника на дашборд (`#tour-cabinet-commerce-tours`)
     * вместо возврата на страницу формы. См. фичу `tour-cabinet-archives`, Task 5.
     */
    public const SESSION_KEY_REDIRECT_TO_DASHBOARD = 'tour_cabinet_commerce_redirect_to_dashboard';

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

        // Архивируем заявку и сбрасываем прогресс — блок «Коммерческие туры» в ЛК
        // снова становится активным для следующей заявки (см. фичу tour-cabinet-archives).
        $archiveService = app(TourCabinetCommerceArchiveService::class);
        $archive = $archiveService->archiveAndResetProgress($progress, $user, $submission);

        session()->forget('tour_cabinet_commerce_form_city_id');

        if ($archive === null) {
            // Архивация упала (ошибка залогирована внутри сервиса). Прогресс при этом тоже
            // не обновлён — лучше не «терять» сабмит: фолбэк — старая логика (просто пометить
            // current_stage=3), чтобы пользователь увидел экран ожидания обратной связи.
            $payload = [
                'current_stage' => 3,
                'lms_form_submission_id' => $submission->id,
            ];
            if ($progress->completed_at === null) {
                $payload['completed_at'] = now();
            }
            $progress->fill($payload)->save();

            return;
        }

        session()->flash('success', 'Заявка отправлена и сохранена в Архиве коммерческих туров. Новая заявка может быть создана прямо сейчас.');
        session()->flash('tour_cabinet_commerce_just_archived', true);
        session()->put(self::SESSION_KEY_REDIRECT_TO_DASHBOARD, true);
    }
}
