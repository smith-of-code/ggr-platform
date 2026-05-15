<?php

namespace App\Services;

use App\Models\City;
use App\Models\Lms\LmsForm;
use App\Models\Lms\LmsFormSubmission;
use App\Models\Tour;
use App\Models\TourCabinetCommerceArchive;
use App\Models\TourCabinetCommerceProgress;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Сервис архивации коммерческой заявки. После сабмита анкеты этапа 2:
 *  1) Собирает иммутабельный snapshot (город, тур, ответы LMS-формы, текст уведомления этапа 3).
 *  2) Создаёт запись в `tour_cabinet_commerce_archives`.
 *  3) Обнуляет прогресс пользователя (`current_stage = 1`, city/tour/submission/completed_at = null),
 *     чтобы блок «Коммерческие туры» в ЛК стал снова активен для следующей заявки.
 *
 * Цикл повторяется без ограничений (см. фичу tour-cabinet-archives).
 */
class TourCabinetCommerceArchiveService
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    public function archiveAndResetProgress(
        TourCabinetCommerceProgress $progress,
        User $user,
        LmsFormSubmission $submission,
    ): ?TourCabinetCommerceArchive {
        try {
            $payload = $this->buildSnapshotPayload($progress, $submission);

            return DB::transaction(function () use ($progress, $submission, $payload): TourCabinetCommerceArchive {
                $archive = TourCabinetCommerceArchive::query()->create([
                    'user_id' => $progress->user_id,
                    'city_id' => $progress->city_id,
                    'tour_id' => $progress->tour_id,
                    'lms_form_submission_id' => $submission->id,
                    'submitted_at' => now(),
                    'status' => TourCabinetCommerceArchive::STATUS_SENT,
                    'payload' => $payload,
                ]);

                $progress->forceFill([
                    'current_stage' => 1,
                    'city_id' => null,
                    'tour_id' => null,
                    'lms_form_submission_id' => null,
                    'completed_at' => null,
                ])->save();

                return $archive;
            });
        } catch (\Throwable $e) {
            Log::warning('tour_cabinet_commerce_archive_failed: '.$e->getMessage(), [
                'user_id' => $progress->user_id,
                'progress_id' => $progress->id,
                'submission_id' => $submission->id,
            ]);

            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSnapshotPayload(
        TourCabinetCommerceProgress $progress,
        LmsFormSubmission $submission,
    ): array {
        $city = $progress->city_id ? City::query()->find($progress->city_id) : null;
        $tour = $progress->tour_id ? Tour::query()->find($progress->tour_id) : null;

        $submission->loadMissing(['form:id,slug,title', 'responses.field:id,label,key']);
        $form = $submission->relationLoaded('form') ? $submission->form : null;

        $responses = [];
        foreach ($submission->responses as $r) {
            $responses[] = [
                'label' => $r->field?->label ?? $r->field?->key ?? 'Поле',
                'key' => $r->field?->key,
                'value' => $r->value,
            ];
        }

        $stage3 = $this->settings->getTourCabinetCommerceToursStage3Notification();

        return [
            'city' => $city ? ['id' => $city->id, 'name' => $city->name] : null,
            'tour' => $tour ? ['id' => $tour->id, 'title' => $tour->title] : null,
            'lms_form' => [
                'slug' => $form instanceof LmsForm ? $form->slug : null,
                'title' => $form instanceof LmsForm ? $form->title : null,
                'submission_id' => $submission->id,
                'responses' => $responses,
            ],
            'stage3_notification' => [
                'subject' => (string) ($stage3['subject'] ?? ''),
                'body' => (string) ($stage3['body'] ?? ''),
            ],
        ];
    }
}
