<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourCabinetContestStage2Answer;
use Inertia\Inertia;
use Inertia\Response;

class TourCabinetStage2AnswersController extends Controller
{
    /**
     * Ответы участников по этапу 2 (после отправки организаторам или устаревший сценарий: уже на этапе 3+).
     */
    public function index(): Response
    {
        $answers = TourCabinetContestStage2Answer::query()
            ->with([
                'user:id,name,email,first_name,last_name,patronymic',
                'user.tourCabinetContestProgress',
                'question:id,body',
            ])
            ->whereRaw('trim(answer_text) <> ?', [''])
            ->whereHas('user.tourCabinetContestProgress', function ($q): void {
                $q->where(function ($inner): void {
                    $inner->whereNotNull('stage2_submitted_at')
                        ->orWhere('current_stage', '>=', 3);
                });
            })
            ->orderByDesc('id')
            ->paginate(40)
            ->through(function (TourCabinetContestStage2Answer $a): array {
                $progress = $a->user->tourCabinetContestProgress;
                $submittedAt = $progress?->stage2_submitted_at;
                if ($submittedAt === null && $progress !== null && (int) $progress->current_stage >= 3) {
                    $submittedAtIso = $a->updated_at?->toIso8601String();
                } else {
                    $submittedAtIso = $submittedAt?->toIso8601String();
                }

                $composed = trim(implode(' ', array_filter(
                    [$a->user->last_name, $a->user->first_name, $a->user->patronymic],
                    fn ($v) => $v !== null && $v !== ''
                )));
                $userDisplay = $composed !== '' ? $composed : (string) ($a->user->name ?: $a->user->email);

                return [
                    'id' => $a->id,
                    'question_body' => $a->question->body,
                    'answer_text' => $a->answer_text,
                    'user_display' => $userDisplay,
                    'user_email' => $a->user->email,
                    'submitted_at' => $submittedAtIso,
                ];
            });

        return Inertia::render('Admin/TourCabinet/Stage2Answers/Index', [
            'answers' => $answers,
        ]);
    }
}
