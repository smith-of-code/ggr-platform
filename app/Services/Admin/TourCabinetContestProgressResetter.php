<?php

namespace App\Services\Admin;

use App\Models\TourCabinetContestCitySubmission;
use App\Models\TourCabinetContestProgress;
use App\Models\TourCabinetContestStage2Answer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class TourCabinetContestProgressResetter
{
    /**
     * Полный сброс прогресса конкурса участника: чистит все три таблицы
     * (`tour_cabinet_contest_city_submissions`, `tour_cabinet_contest_stage2_answers`,
     * `tour_cabinet_contest_progress`) и удаляет файл-вложение этапа 3 со storage.
     *
     * LMS-`lms_form_submissions` намеренно остаются в БД (см. spec, Constraints).
     */
    public function reset(User $user): void
    {
        $userId = $user->id;

        $progress = TourCabinetContestProgress::query()
            ->where('user_id', $userId)
            ->first();

        $attachmentPath = $progress?->stage3_attachment_path;

        $deletedCounts = DB::transaction(function () use ($userId): array {
            $citySubmissions = TourCabinetContestCitySubmission::query()
                ->where('user_id', $userId)
                ->delete();

            $stage2Answers = TourCabinetContestStage2Answer::query()
                ->where('user_id', $userId)
                ->delete();

            $progressDeleted = TourCabinetContestProgress::query()
                ->where('user_id', $userId)
                ->delete();

            return [
                'city_submissions' => (int) $citySubmissions,
                'stage2_answers' => (int) $stage2Answers,
                'progress' => (int) $progressDeleted,
            ];
        });

        if (filled($attachmentPath)) {
            $disk = config('filesystems.upload_disk', 'public');
            try {
                Storage::disk($disk)->delete($attachmentPath);
            } catch (\Throwable $e) {
                Log::warning('tour_cabinet_contest_progress_reset_storage_failed', [
                    'user_id' => $userId,
                    'path' => $attachmentPath,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        Log::info('contest_progress_reset', array_merge(
            ['user_id' => $userId, 'attachment_path' => $attachmentPath],
            $deletedCounts,
        ));
    }
}
