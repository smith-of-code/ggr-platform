<?php

namespace App\Services;

use App\Models\TourCabinetDocument;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

/**
 * SSoT для проверки полноты профиля участника ЛК Туры
 * (используется в дашборде, middleware tour-cabinet.profile-complete и UI-плашке).
 */
class TourCabinetProfileCompleteness
{
    public const FIELD_LAST_NAME = 'last_name';

    public const FIELD_FIRST_NAME = 'first_name';

    public const FIELD_GENDER = 'gender';

    public const FIELD_BIRTH_DATE = 'birth_date';

    public const FIELD_PHONE = 'phone';

    public const FIELD_EMAIL = 'email';

    public const FIELD_PERSONAL_DATA_CONSENT = 'personal_data_consent';

    /** @return list<string> */
    public const REQUIRED_PROFILE_FIELDS = [
        self::FIELD_LAST_NAME,
        self::FIELD_FIRST_NAME,
        self::FIELD_GENDER,
        self::FIELD_BIRTH_DATE,
        self::FIELD_PHONE,
        self::FIELD_EMAIL,
    ];

    public function isComplete(User $user): bool
    {
        return $this->missingFields($user) === [];
    }

    /**
     * Список ключей, по которым профиль считается незаполненным.
     *
     * @return list<string>
     */
    public function missingFields(User $user): array
    {
        $missing = [];

        foreach (self::REQUIRED_PROFILE_FIELDS as $field) {
            if (! $this->profileFieldFilled($user, $field)) {
                $missing[] = $field;
            }
        }

        if (! $this->hasValidPersonalDataConsent($user)) {
            $missing[] = self::FIELD_PERSONAL_DATA_CONSENT;
        }

        return $missing;
    }

    private function profileFieldFilled(User $user, string $field): bool
    {
        $value = $user->{$field} ?? null;

        if ($field === self::FIELD_BIRTH_DATE) {
            return $value !== null;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return $value !== null && $value !== '';
    }

    /**
     * Согласие на ОПД считается валидным, если у участника есть запись `tour_cabinet_documents`
     * типа `personal_data_consent` со статусом `pending_review` или `approved`
     * и непустым `file_path`. Статус `annulled` снова блокирует доступ.
     */
    private function hasValidPersonalDataConsent(User $user): bool
    {
        if (! Schema::hasTable('tour_cabinet_documents')) {
            return true;
        }

        return TourCabinetDocument::query()
            ->where('user_id', $user->id)
            ->where('type', TourCabinetDocument::TYPE_PERSONAL_DATA_CONSENT)
            ->whereIn('status', [
                TourCabinetDocument::STATUS_PENDING_REVIEW,
                TourCabinetDocument::STATUS_APPROVED,
            ])
            ->whereNotNull('file_path')
            ->where('file_path', '!=', '')
            ->exists();
    }
}
