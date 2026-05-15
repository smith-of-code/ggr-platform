<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Tour;
use App\Models\TourCabinetCommerceCityForm;
use App\Models\TourCabinetCommerceProgress;
use App\Services\TourCabinetCommerceArchiveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TourCabinetCommerceToursController extends Controller
{
    public function storeCity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'city_id' => ['required', 'integer', 'exists:cities,id'],
        ]);

        $cityId = (int) $validated['city_id'];

        $hasActiveTour = Tour::query()
            ->where('is_active', true)
            ->whereHas('cities', fn ($q) => $q->where('cities.id', $cityId))
            ->exists();

        if (! $hasActiveTour) {
            throw ValidationException::withMessages([
                'city_id' => 'Для этого города пока нет коммерческих туров.',
            ]);
        }

        $progress = $this->progressForUser($request);

        $progress->fill([
            'city_id' => $cityId,
            'tour_id' => null,
            'lms_form_submission_id' => null,
            'completed_at' => null,
            'current_stage' => 1,
        ])->save();

        return $this->redirectToBlock();
    }

    public function storeTour(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tour_id' => ['required', 'integer', 'exists:tours,id'],
        ]);

        $progress = $this->progressForUser($request);

        if ((int) $progress->current_stage >= 3) {
            return $this->redirectToBlock();
        }

        if (! $progress->city_id) {
            throw ValidationException::withMessages([
                'tour_id' => 'Сначала выберите город.',
            ]);
        }

        $tourId = (int) $validated['tour_id'];

        $tourBelongsToCity = Tour::query()
            ->where('is_active', true)
            ->where('id', $tourId)
            ->whereHas('cities', fn ($q) => $q->where('cities.id', $progress->city_id))
            ->exists();

        if (! $tourBelongsToCity) {
            throw ValidationException::withMessages([
                'tour_id' => 'Выбранный тур недоступен в этом городе.',
            ]);
        }

        $progress->fill(['tour_id' => $tourId])->save();

        return $this->redirectToBlock();
    }

    public function completeStage1(Request $request): RedirectResponse
    {
        $progress = $this->progressForUser($request);

        if ((int) $progress->current_stage !== 1) {
            return $this->redirectToBlock();
        }

        if (! $progress->city_id || ! $progress->tour_id) {
            throw ValidationException::withMessages([
                'stage' => 'Выберите город и тур, чтобы продолжить.',
            ]);
        }

        $progress->fill(['current_stage' => 2])->save();

        return $this->redirectToBlock();
    }

    public function startCityForm(Request $request): RedirectResponse
    {
        $progress = $this->progressForUser($request);

        if ((int) $progress->current_stage < 2 || ! $progress->city_id) {
            return $this->redirectToBlock();
        }

        if ((int) $progress->current_stage >= 3) {
            return $this->redirectToBlock();
        }

        $cityForm = TourCabinetCommerceCityForm::query()
            ->where('city_id', $progress->city_id)
            ->first();

        if ($cityForm === null || trim((string) $cityForm->lms_form_slug) === '') {
            return $this->redirectToBlock()
                ->with('error', 'Анкета для выбранного города пока не настроена. Обратитесь в поддержку.');
        }

        session(['tour_cabinet_commerce_form_city_id' => (int) $progress->city_id]);

        return redirect()->route('forms.public.show', $cityForm->lms_form_slug);
    }

    /**
     * Этап 3 → действие участника «Сохранить в архив и оформить новую заявку».
     * Доступно только при `current_stage >= 3` (анкета этапа 2 уже отправлена).
     * Архивирует текущую заявку (через `TourCabinetCommerceArchiveService`) и обнуляет
     * прогресс — блок снова доступен для новой заявки. См. фичу tour-cabinet-archives.
     */
    public function archiveAndReset(
        Request $request,
        TourCabinetCommerceArchiveService $archiveService,
    ): RedirectResponse {
        $progress = $this->progressForUser($request);

        if ((int) $progress->current_stage < 3) {
            return $this->redirectToBlock()
                ->with('error', 'Нельзя сохранить в архив, пока не завершена анкета этапа 2.');
        }

        $archive = $archiveService->archiveAndResetProgress($progress, $request->user());

        if ($archive === null) {
            return $this->redirectToBlock()
                ->with('error', 'Не удалось сохранить заявку в архив. Попробуйте ещё раз или обратитесь в поддержку.');
        }

        return $this->redirectToBlock()
            ->with('success', 'Заявка отправлена и сохранена в Архиве коммерческих туров. Новая заявка может быть создана прямо сейчас.')
            ->with('tour_cabinet_commerce_just_archived', true);
    }

    public function reopenSelection(Request $request): RedirectResponse
    {
        $progress = $this->progressForUser($request);

        if ((int) $progress->current_stage >= 3) {
            return $this->redirectToBlock();
        }

        session()->forget('tour_cabinet_commerce_form_city_id');

        $progress->fill([
            'city_id' => null,
            'tour_id' => null,
            'current_stage' => 1,
        ])->save();

        return $this->redirectToBlock();
    }

    private function progressForUser(Request $request): TourCabinetCommerceProgress
    {
        return TourCabinetCommerceProgress::query()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['current_stage' => 1]
        );
    }

    private function redirectToBlock(): RedirectResponse
    {
        return redirect()->route('tour-cabinet.dashboard')->withFragment('tour-cabinet-commerce-tours');
    }
}
