<?php

namespace App\Services;

use App\Models\Lms\LmsForm;
use App\Support\PostAuthRedirect;
use App\Models\Lms\LmsFormSubmission;
use App\Models\TourCabinetContestCitySubmission;
use App\Models\TourCabinetContestProgress;
use App\Models\TourCabinetDirectionCity;
use Illuminate\Support\Facades\Auth;

class TourCabinetContestFormLinker
{
    public static function tryLinkAfterSubmission(LmsForm $form, LmsFormSubmission $submission): void
    {
        $user = Auth::user();
        if (! $user || ! PostAuthRedirect::canAccessTourCabinet($user)) {
            return;
        }

        $cityId = (int) session()->get('tour_cabinet_contest_form_city_id', 0);
        if ($cityId < 1) {
            return;
        }

        $progress = TourCabinetContestProgress::query()->where('user_id', $user->id)->first();
        if (! $progress || ! $progress->direction_id) {
            return;
        }

        $selected = $progress->selected_city_ids ?? [];
        if (! in_array($cityId, $selected, true)) {
            return;
        }

        $row = TourCabinetDirectionCity::query()
            ->where('direction_id', $progress->direction_id)
            ->where('city_id', $cityId)
            ->first();

        if (! $row) {
            return;
        }

        $resolver = app(TourCabinetContestStage1FormResolver::class);
        $expectedSlug = $resolver->resolveForRow($row);
        if (! $expectedSlug || $form->slug !== $expectedSlug) {
            return;
        }

        TourCabinetContestCitySubmission::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'city_id' => $cityId,
            ],
            [
                'lms_form_submission_id' => $submission->id,
            ]
        );

        session()->forget('tour_cabinet_contest_form_city_id');
    }
}
