<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourCabinetContestStage2Question;
use App\Services\Admin\TourCabinetHubPageData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TourCabinetStage2QuestionsController extends Controller
{
    public function __construct(
        private readonly TourCabinetHubPageData $hubPageData,
    ) {}

    public function index(): Response
    {
        return Inertia::render(
            'Admin/TourCabinet/Stage2Questions/Index',
            $this->hubPageData->stage2QuestionsPayload(),
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'is_active' => ['sometimes', 'boolean'],
            'direction_id' => ['nullable', 'integer', 'exists:directions,id'],
            'min_length' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'max_length' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        $minLength = $this->normalizeLength($validated['min_length'] ?? null);
        $maxLength = $this->normalizeLength($validated['max_length'] ?? null);
        $this->ensureLengthRangeIsValid($minLength, $maxLength);

        $dirId = isset($validated['direction_id']) && $validated['direction_id'] ? (int) $validated['direction_id'] : null;

        $sort = $validated['sort_order'] ?? null;
        if ($sort === null) {
            $sort = (int) TourCabinetContestStage2Question::query()->max('sort_order') + 1;
        }

        TourCabinetContestStage2Question::query()->create([
            'body' => $validated['body'],
            'sort_order' => $sort,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'direction_id' => $dirId,
            'min_length' => $minLength,
            'max_length' => $maxLength,
        ]);

        return redirect()
            ->route('admin.tour-cabinet.index')
            ->withFragment('tour-cabinet-admin-stage2')
            ->with('success', 'Вопрос добавлен.');
    }

    public function update(Request $request, string $question): RedirectResponse
    {
        $model = TourCabinetContestStage2Question::query()->findOrFail((int) $question);

        $validated = $request->validate([
            'body' => ['sometimes', 'required', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'is_active' => ['sometimes', 'boolean'],
            'direction_id' => ['sometimes', 'nullable', 'integer', 'exists:directions,id'],
            'min_length' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100000'],
            'max_length' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100000'],
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
        if (array_key_exists('direction_id', $validated)) {
            $data['direction_id'] = $validated['direction_id'] ? (int) $validated['direction_id'] : null;
        }
        if (array_key_exists('min_length', $validated)) {
            $data['min_length'] = $this->normalizeLength($validated['min_length']);
        }
        if (array_key_exists('max_length', $validated)) {
            $data['max_length'] = $this->normalizeLength($validated['max_length']);
        }

        $effectiveMin = array_key_exists('min_length', $data) ? $data['min_length'] : $model->min_length;
        $effectiveMax = array_key_exists('max_length', $data) ? $data['max_length'] : $model->max_length;
        $this->ensureLengthRangeIsValid($effectiveMin, $effectiveMax);

        if ($data !== []) {
            $model->update($data);
        }

        return redirect()
            ->route('admin.tour-cabinet.index')
            ->withFragment('tour-cabinet-admin-stage2')
            ->with('success', 'Вопрос обновлён.');
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

    /**
     * Если оба лимита заданы, min <= max. Иначе бросает ValidationException.
     */
    private function ensureLengthRangeIsValid(?int $min, ?int $max): void
    {
        if ($min !== null && $max !== null && $min > $max) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'min_length' => 'Минимум символов не может превышать максимум.',
            ]);
        }
    }

    public function destroy(string $question): RedirectResponse
    {
        $model = TourCabinetContestStage2Question::query()->findOrFail((int) $question);
        $model->delete();

        return redirect()
            ->route('admin.tour-cabinet.index')
            ->withFragment('tour-cabinet-admin-stage2')
            ->with('success', 'Вопрос удалён.');
    }
}
