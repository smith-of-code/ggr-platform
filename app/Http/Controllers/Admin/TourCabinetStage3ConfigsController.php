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

        DB::transaction(function () use ($direction, $validated, $title, $responseFormat): void {
            TourCabinetContestStage3Config::query()->updateOrCreate(
                ['direction_id' => $direction->id],
                [
                    'title' => $title,
                    'task_body' => $validated['task_body'],
                    'response_format' => $responseFormat,
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
}
