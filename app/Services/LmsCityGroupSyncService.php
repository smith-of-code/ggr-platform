<?php

namespace App\Services;

use App\Models\City;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGroup;
use App\Models\Lms\LmsProfile;
class LmsCityGroupSyncService
{
    public function syncForEvent(LmsEvent $event): void
    {
        $this->backfillProfileCityIds($event);

        $cityIds = LmsProfile::query()
            ->where('lms_event_id', $event->id)
            ->whereNotNull('city_id')
            ->distinct()
            ->pluck('city_id');

        $cities = City::query()
            ->whereIn('id', $cityIds)
            ->get(['id', 'name']);

        foreach ($cities as $city) {
            $group = LmsGroup::query()->firstOrCreate(
                [
                    'lms_event_id' => $event->id,
                    'city_id' => $city->id,
                    'is_city_group' => true,
                ],
                [
                    'title' => $city->name,
                    'linked_cities' => [$city->name],
                ],
            );

            if ($group->title !== $city->name) {
                $group->update([
                    'title' => $city->name,
                    'linked_cities' => [$city->name],
                    'is_city_group' => true,
                ]);
            }

            $this->syncGroupMembers($event, $group, (int) $city->id);
        }
    }

    /**
     * Проставляет city_id профилям по совпадению текста city со справочником cities.
     */
    private function backfillProfileCityIds(LmsEvent $event): void
    {
        $cityNameToId = City::query()
            ->pluck('id', 'name')
            ->mapWithKeys(fn ($id, string $name) => [$this->normalizeCityKey($name) => (int) $id]);

        if ($cityNameToId->isEmpty()) {
            return;
        }

        $profiles = LmsProfile::query()
            ->where('lms_event_id', $event->id)
            ->whereNull('city_id')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->get(['id', 'city']);

        foreach ($profiles as $profile) {
            $cityId = $cityNameToId->get($this->normalizeCityKey($profile->city));
            if ($cityId !== null) {
                $profile->update(['city_id' => $cityId]);
            }
        }
    }

    private function normalizeCityKey(string $name): string
    {
        return mb_strtolower(trim($name));
    }

    private function syncGroupMembers(LmsEvent $event, LmsGroup $group, int $cityId): void
    {
        $adminUserIds = LmsProfile::query()
            ->where('lms_event_id', $event->id)
            ->where('role', 'admin')
            ->pluck('user_id');

        $desiredUserIds = LmsProfile::query()
            ->where('lms_event_id', $event->id)
            ->where('city_id', $cityId)
            ->whereNotIn('user_id', $adminUserIds)
            ->pluck('user_id')
            ->all();

        $existing = $group->members()->get()->keyBy('id');
        $syncData = [];
        foreach ($desiredUserIds as $userId) {
            $member = $existing->get($userId);
            $syncData[$userId] = [
                'is_gamification_inactive' => (bool) ($member?->pivot->is_gamification_inactive ?? false),
            ];
        }

        $group->members()->sync($syncData);
    }

    public function setMemberGamificationInactive(LmsGroup $group, int $userId, bool $inactive): void
    {
        if (! $group->is_city_group) {
            return;
        }

        if (! $group->members()->where('users.id', $userId)->exists()) {
            abort(404);
        }

        $group->members()->updateExistingPivot($userId, [
            'is_gamification_inactive' => $inactive,
        ]);
    }
}
