<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\TourCabinetContestDirectionSetting;
use App\Models\TourCabinetContestStage3Config;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TourCabinetStage3ConfigsController extends Controller
{
    public function update(Request $request, string $projectKey): RedirectResponse
    {
        if (! array_key_exists($projectKey, Tour::PROJECTS)) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:500'],
            'task_body' => ['nullable', 'string', 'max:50000'],
            'response_format' => ['required', 'string', Rule::in([
                TourCabinetContestStage3Config::FORMAT_VIDEO_LINK,
                TourCabinetContestStage3Config::FORMAT_FILE_UPLOAD,
            ])],
            'max_contest_stages' => ['required', 'integer', 'min:1', 'max:3'],
        ]);

        DB::transaction(function () use ($projectKey, $validated): void {
            TourCabinetContestStage3Config::query()->updateOrCreate(
                ['project_key' => $projectKey],
                [
                    'title' => $validated['title'],
                    'task_body' => $validated['task_body'],
                    'response_format' => $validated['response_format'],
                ]
            );
            TourCabinetContestDirectionSetting::query()->updateOrCreate(
                ['project_key' => $projectKey],
                ['max_contest_stages' => (int) $validated['max_contest_stages']]
            );
        });

        return redirect()
            ->route('admin.tour-cabinet.index')
            ->withFragment('tour-cabinet-admin-stage3')
            ->with('success', 'Настройки этапа 3 для выбранного направления сохранены.');
    }
}
