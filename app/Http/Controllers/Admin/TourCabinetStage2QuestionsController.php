<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\TourCabinetContestStage2Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TourCabinetStage2QuestionsController extends Controller
{
    public function index(): Response
    {
        $questions = TourCabinetContestStage2Question::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $directions = collect(Tour::PROJECTS)->map(fn (string $label, string $key) => [
            'key' => $key,
            'label' => $label,
        ])->values()->all();

        return Inertia::render('Admin/TourCabinet/Stage2Questions/Index', [
            'questions' => $questions,
            'directions' => $directions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $keys = array_keys(Tour::PROJECTS);
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'is_active' => ['sometimes', 'boolean'],
            'project_key' => ['nullable', 'string', 'max:32'],
        ]);

        $pk = isset($validated['project_key']) ? trim((string) $validated['project_key']) : '';
        $pk = $pk === '' ? null : $pk;
        if ($pk !== null && ! in_array($pk, $keys, true)) {
            throw ValidationException::withMessages([
                'project_key' => 'Недопустимое направление.',
            ]);
        }

        $sort = $validated['sort_order'] ?? null;
        if ($sort === null) {
            $sort = (int) TourCabinetContestStage2Question::query()->max('sort_order') + 1;
        }

        TourCabinetContestStage2Question::query()->create([
            'body' => $validated['body'],
            'sort_order' => $sort,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'project_key' => $pk,
        ]);

        return redirect()
            ->route('admin.tour-cabinet.stage2-questions.index')
            ->with('success', 'Вопрос добавлен.');
    }

    public function update(Request $request, string $question): RedirectResponse
    {
        $model = TourCabinetContestStage2Question::query()->findOrFail((int) $question);

        $keys = array_keys(Tour::PROJECTS);
        $validated = $request->validate([
            'body' => ['sometimes', 'required', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'is_active' => ['sometimes', 'boolean'],
            'project_key' => ['sometimes', 'nullable', 'string', 'max:32'],
        ]);

        $data = [];
        if (array_key_exists('body', $validated)) {
            $data['body'] = $validated['body'];
        }
        if (array_key_exists('sort_order', $validated) && $validated['sort_order'] !== null) {
            $data['sort_order'] = (int) $validated['sort_order'];
        }
        if (array_key_exists('is_active', $validated)) {
            $data['is_active'] = (bool) $validated['is_active'];
        }
        if (array_key_exists('project_key', $validated)) {
            $pk = trim((string) ($validated['project_key'] ?? ''));
            $pk = $pk === '' ? null : $pk;
            if ($pk !== null && ! in_array($pk, $keys, true)) {
                throw ValidationException::withMessages([
                    'project_key' => 'Недопустимое направление.',
                ]);
            }
            $data['project_key'] = $pk;
        }
        if ($data !== []) {
            $model->update($data);
        }

        return redirect()
            ->route('admin.tour-cabinet.stage2-questions.index')
            ->with('success', 'Вопрос обновлён.');
    }

    public function destroy(string $question): RedirectResponse
    {
        $model = TourCabinetContestStage2Question::query()->findOrFail((int) $question);
        $model->delete();

        return redirect()
            ->route('admin.tour-cabinet.stage2-questions.index')
            ->with('success', 'Вопрос удалён.');
    }
}
