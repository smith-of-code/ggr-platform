<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use App\Models\TourCabinetContestDirectionSetting;
use App\Models\TourCabinetContestStage3Config;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TourCabinetStage3ConfigsController extends Controller
{
    public function update(Request $request, Direction $direction): RedirectResponse
    {
        $maxStages = (int) $request->input('max_contest_stages', 3);
        $maxStages = min(3, max(1, $maxStages));

        $rules = [
            'task_body' => ['nullable', 'string', 'max:50000'],
            'max_contest_stages' => ['required', 'integer', 'min:1', 'max:3'],
            'text_min_length' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'text_max_length' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
        $rules['title'] = $maxStages >= 3
            ? ['required', 'string', 'max:500']
            : ['nullable', 'string', 'max:500'];
        $rules['response_format'] = $maxStages >= 3
            ? ['required', 'string', Rule::in([
                TourCabinetContestStage3Config::FORMAT_VIDEO_LINK,
                TourCabinetContestStage3Config::FORMAT_FILE_UPLOAD,
            ])]
            : ['nullable', 'string', Rule::in([
                TourCabinetContestStage3Config::FORMAT_VIDEO_LINK,
                TourCabinetContestStage3Config::FORMAT_FILE_UPLOAD,
            ])];

        $validated = $request->validate($rules);

        $title = $maxStages >= 3
            ? (string) $validated['title']
            : (string) ($validated['title'] ?? '');
        $responseFormat = $validated['response_format'] ?? TourCabinetContestStage3Config::FORMAT_VIDEO_LINK;

        $textMin = $this->normalizeLength($validated['text_min_length'] ?? null);
        $textMax = $this->normalizeLength($validated['text_max_length'] ?? null);
        if ($textMin !== null && $textMax !== null && $textMin > $textMax) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'text_min_length' => 'Минимум символов не может превышать максимум.',
            ]);
        }

        DB::transaction(function () use ($direction, $validated, $title, $responseFormat, $textMin, $textMax): void {
            TourCabinetContestStage3Config::query()->updateOrCreate(
                ['direction_id' => $direction->id],
                [
                    'title' => $title,
                    'task_body' => $validated['task_body'],
                    'response_format' => $responseFormat,
                    'text_min_length' => $textMin,
                    'text_max_length' => $textMax,
                ]
            );
            TourCabinetContestDirectionSetting::query()->updateOrCreate(
                ['direction_id' => $direction->id],
                ['max_contest_stages' => (int) $validated['max_contest_stages']]
            );
        });

        return redirect()
            ->route('admin.tour-cabinet.index')
            ->withFragment('tour-cabinet-admin-stage3')
            ->with('success', 'Настройки этапа 3 для выбранного направления сохранены.');
    }

    /**
     * Привести значение к unsigned int или null (пустая строка/0 трактуется как «без лимита»).
     */
    private function normalizeLength(mixed $value): ?int
    {
        if ($value === null || $value === '' || (int) $value <= 0) {
            return null;
        }

        return (int) $value;
    }
}
