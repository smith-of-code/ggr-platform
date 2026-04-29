<?php

namespace App\Services;

use App\Models\City;
use App\Models\Tour;
use App\Models\TourCabinetCommerceCityForm;
use App\Models\TourCabinetCommerceProgress;
use App\Models\User;

class TourCabinetCommerceToursDashboardData
{
    public function __construct(private readonly SettingsService $settings)
    {
    }

    /**
     * Payload блока «Коммерческие туры» для дашборда ЛК туров.
     *
     * @return array{
     *   enabled: bool,
     *   currentStage: int,
     *   cityId: ?int,
     *   tourId: ?int,
     *   completedAt: ?string,
     *   availableCities: array<int, array{id:int, name:string}>,
     *   availableTours: array<int, array{id:int, title:string}>,
     *   hasCityForm: bool,
     *   stage2Locked: bool,
     *   stage3: array{subject:string, body:string}
     * }
     */
    public function buildPayload(User $user): array
    {
        $stage3 = $this->settings->getTourCabinetCommerceToursStage3Notification();

        $progress = TourCabinetCommerceProgress::query()
            ->where('user_id', $user->id)
            ->first();

        $cityId = $progress?->city_id;
        $tourId = $progress?->tour_id;
        $currentStage = (int) ($progress?->current_stage ?? 1);

        $availableCities = City::query()
            ->where('is_active', true)
            ->whereHas('tours', fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (City $c) => ['id' => $c->id, 'name' => $c->name])
            ->all();

        $availableTours = [];
        if ($cityId !== null) {
            $availableTours = Tour::query()
                ->where('is_active', true)
                ->whereHas('cities', fn ($q) => $q->where('cities.id', $cityId))
                ->orderBy('title')
                ->get(['id', 'title'])
                ->map(fn (Tour $t) => ['id' => $t->id, 'title' => $t->title])
                ->all();
        }

        $hasCityForm = $cityId !== null
            && TourCabinetCommerceCityForm::query()->where('city_id', $cityId)->exists();

        $clampedStage = max(1, min(3, $currentStage));

        return [
            'enabled' => (bool) $stage3['enabled'],
            'currentStage' => $clampedStage,
            'cityId' => $cityId,
            'tourId' => $tourId,
            'completedAt' => $progress?->completed_at?->toIso8601String(),
            'availableCities' => $availableCities,
            'availableTours' => $availableTours,
            'hasCityForm' => $hasCityForm,
            'stage2Locked' => $this->isStage2LockedForParticipant($clampedStage),
            'stage3' => [
                'subject' => (string) $stage3['subject'],
                'body' => (string) $stage3['body'],
            ],
        ];
    }

    /**
     * Этап 2 («Анкета доп. данных») доступен участнику только в окне `current_stage === 2`.
     * При `current_stage = 1` пользователь ещё не нажал «Перейти к этапу 2 →» (метод
     * `TourCabinetCommerceToursController::completeStage1`); при `current_stage >= 3` анкета
     * уже отправлена и блок переходит в режим только-просмотра ожидания обратной связи.
     */
    private function isStage2LockedForParticipant(int $currentStage): bool
    {
        return $currentStage < 2 || $currentStage >= 3;
    }
}
