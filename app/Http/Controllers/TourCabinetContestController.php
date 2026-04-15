<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Tour;
use App\Models\TourCabinetContestCitySubmission;
use App\Models\TourCabinetContestProgress;
use App\Models\TourCabinetContestStage2Answer;
use App\Models\TourCabinetContestStage2Question;
use App\Models\TourCabinetDirectionCity;
use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TourCabinetContestController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    public function show(): RedirectResponse
    {
        return $this->redirectToContestBlock();
    }

    public function completeStage1(Request $request): RedirectResponse
    {
        $progress = TourCabinetContestProgress::query()->where('user_id', $request->user()->id)->firstOrFail();
        if ((int) $progress->current_stage !== 1) {
            return $this->redirectToContestBlock();
        }
        if (! $this->stage1Complete($progress, $request->user()->id)) {
            throw ValidationException::withMessages([
                'stage' => 'Сначала отправьте анкеты по всем выбранным городам.',
            ]);
        }
        $progress->update(['current_stage' => 2]);

        return $this->redirectToContestBlock();
    }

    public function showStage2(): RedirectResponse
    {
        return $this->redirectToContestBlock();
    }

    public function storeStage2(Request $request): RedirectResponse
    {
        $user = $request->user();
        $progress = TourCabinetContestProgress::query()->where('user_id', $user->id)->firstOrFail();
        if ((int) $progress->current_stage !== 2) {
            return $this->redirectToContestBlock();
        }

        $questions = $this->activeStage2QuestionsQuery($progress->project_key)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $answersInput = $request->input('answers', []);
        if (! is_array($answersInput)) {
            throw ValidationException::withMessages([
                'answers' => 'Некорректный формат ответов.',
            ]);
        }

        foreach ($questions as $q) {
            $raw = $answersInput[$q->id] ?? $answersInput[(string) $q->id] ?? '';
            if (trim((string) $raw) === '') {
                throw ValidationException::withMessages([
                    'answers.'.$q->id => 'Заполните ответ на все вопросы.',
                ]);
            }
        }

        DB::transaction(function () use ($questions, $answersInput, $user): void {
            foreach ($questions as $q) {
                $text = trim((string) ($answersInput[$q->id] ?? $answersInput[(string) $q->id] ?? ''));
                TourCabinetContestStage2Answer::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'question_id' => $q->id,
                    ],
                    [
                        'answer_text' => $text,
                    ]
                );
            }
        });

        $progress->update(['current_stage' => 3]);

        return $this->redirectToContestBlock();
    }

    public function showStage3(): RedirectResponse
    {
        return $this->redirectToContestBlock();
    }

    public function storeStage3(Request $request): RedirectResponse
    {
        $progress = TourCabinetContestProgress::query()->where('user_id', $request->user()->id)->firstOrFail();
        if ((int) $progress->current_stage < 3) {
            return $this->redirectToContestBlock();
        }

        if (filled($progress->stage3_text)) {
            return $this->redirectToContestBlock()
                ->with('error', 'Ответ этапа 3 уже сохранён, редактирование недоступно.');
        }

        $validated = $request->validate([
            'stage3_text' => ['required', 'string', 'max:20000'],
            'stage3_video_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $video = isset($validated['stage3_video_url']) ? trim((string) $validated['stage3_video_url']) : '';
        if ($video !== '' && ! preg_match('#^https?://#i', $video)) {
            throw ValidationException::withMessages([
                'stage3_video_url' => 'Укажите ссылку, начинающуюся с http:// или https://',
            ]);
        }

        $progress->update([
            'stage3_text' => $validated['stage3_text'],
            'stage3_video_url' => $video !== '' ? $video : null,
        ]);

        return $this->redirectToContestBlock()
            ->with('success', 'Данные этапа 3 сохранены.');
    }

    private function redirectToContestBlock(): RedirectResponse
    {
        return redirect()->route('tour-cabinet.dashboard')->withFragment('tour-cabinet-contest');
    }

    private function stage1Complete(TourCabinetContestProgress $progress, int $userId): bool
    {
        $ids = array_values(array_map('intval', $progress->selected_city_ids ?? []));
        if ($ids === [] || ! $progress->project_key) {
            return false;
        }
        foreach ($ids as $cityId) {
            if (! TourCabinetContestCitySubmission::query()
                ->where('user_id', $userId)
                ->where('city_id', $cityId)
                ->exists()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return Builder<TourCabinetContestStage2Question>
     */
    private function activeStage2QuestionsQuery(?string $projectKey): Builder
    {
        return TourCabinetContestStage2Question::query()
            ->where('is_active', true)
            ->where(function ($q) use ($projectKey): void {
                $q->whereNull('project_key');
                if ($projectKey) {
                    $q->orWhere('project_key', $projectKey);
                }
            });
    }

    public function storeDirection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_key' => ['required', 'string', Rule::in(array_keys(Tour::PROJECTS))],
        ]);

        $progress = TourCabinetContestProgress::query()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['current_stage' => 1]
        );

        $progress->update([
            'project_key' => $validated['project_key'],
            'selected_city_ids' => null,
            'current_stage' => 1,
        ]);

        return $this->redirectToContestBlock();
    }

    public function storeCities(Request $request): RedirectResponse
    {
        $progress = TourCabinetContestProgress::query()->where('user_id', $request->user()->id)->firstOrFail();
        if (! $progress->project_key) {
            throw ValidationException::withMessages([
                'city_ids' => 'Сначала выберите направление.',
            ]);
        }

        $validated = $request->validate([
            'city_ids' => ['required', 'array', 'min:1', 'max:3'],
            'city_ids.*' => ['integer', 'distinct'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $validated['city_ids'])));
        if (count($ids) > 3) {
            throw ValidationException::withMessages([
                'city_ids' => 'Можно выбрать не более трёх городов.',
            ]);
        }

        $allowed = TourCabinetDirectionCity::query()
            ->where('project_key', $progress->project_key)
            ->whereIn('city_id', $ids)
            ->pluck('city_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($ids as $id) {
            if (! in_array($id, $allowed, true)) {
                throw ValidationException::withMessages([
                    'city_ids' => 'Один или несколько городов недоступны для выбранного направления.',
                ]);
            }
        }

        $progress->update(['selected_city_ids' => $ids]);

        return $this->redirectToContestBlock();
    }

    public function startCityForm(Request $request, City $city): RedirectResponse
    {
        $progress = TourCabinetContestProgress::query()->where('user_id', $request->user()->id)->firstOrFail();
        $selected = array_map('intval', $progress->selected_city_ids ?? []);
        if (! in_array($city->id, $selected, true)) {
            abort(403);
        }

        $row = TourCabinetDirectionCity::query()
            ->where('project_key', $progress->project_key)
            ->where('city_id', $city->id)
            ->firstOrFail();

        $standard = $this->settings->getTourCabinetContestStage1FormSlugStandard();
        $moreData = $this->settings->getTourCabinetContestStage1FormSlugMoreData();
        $slug = $row->needs_more_data ? $moreData : $standard;
        if (! $slug) {
            return $this->redirectToContestBlock()
                ->with('error', 'Форма для этого типа городов не настроена: задайте slug в админке (ЛК туров → формы) или в config/tour_cabinet.php / .env.');
        }

        session(['tour_cabinet_contest_form_city_id' => $city->id]);

        return redirect()->route('forms.public.show', $slug);
    }
}
