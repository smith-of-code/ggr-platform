<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\TourCabinetContestCitySubmission;
use App\Models\TourCabinetContestDirectionSetting;
use App\Models\TourCabinetContestProgress;
use App\Models\TourCabinetContestStage2Answer;
use App\Models\TourCabinetContestStage2Question;
use App\Models\TourCabinetContestStage3Config;
use App\Models\TourCabinetDirectionCity;
use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
        $maxStages = TourCabinetContestDirectionSetting::maxContestStagesForDirection($progress->direction_id);
        if ($maxStages >= 2) {
            $progress->update(['current_stage' => 2]);
        }

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

        $validated = $request->validate([
            'finalize' => ['required', 'boolean'],
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'string', 'max:20000'],
        ]);

        $finalize = $validated['finalize'];
        $answersInput = $validated['answers'] ?? [];

        $questions = $this->activeStage2QuestionsQuery($progress->direction_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($finalize) {
            if ($questions->isNotEmpty()) {
                foreach ($questions as $q) {
                    $raw = $answersInput[$q->id] ?? $answersInput[(string) $q->id] ?? '';
                    if (trim((string) $raw) === '') {
                        throw ValidationException::withMessages([
                            'answers.'.$q->id => 'Заполните ответ на все вопросы перед отправкой организаторам.',
                        ]);
                    }
                }
            }

            DB::transaction(function () use ($questions, $answersInput, $user, $progress): void {
                $now = now();
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
                $maxStages = TourCabinetContestDirectionSetting::maxContestStagesForDirection($progress->direction_id);
                $payload = [];
                if ($maxStages >= 3) {
                    $payload['current_stage'] = 3;
                } elseif ($maxStages >= 2) {
                    $payload['current_stage'] = 2;
                }
                if ($maxStages >= 2) {
                    $payload['stage2_submitted_at'] = $now;
                }
                $progress->update($payload);
            });

            $redirect = $this->redirectToContestBlock();
            if ($questions->isNotEmpty()) {
                return $redirect->with('success', 'Ответы этапа 2 отправлены организаторам.');
            }

            return $redirect;
        }

        if ($questions->isEmpty()) {
            return $this->redirectToContestBlock();
        }

        DB::transaction(function () use ($questions, $answersInput, $user): void {
            foreach ($questions as $q) {
                $raw = $answersInput[$q->id] ?? $answersInput[(string) $q->id] ?? '';
                $text = is_string($raw) ? $raw : '';
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

        return $this->redirectToContestBlock()
            ->with('success', 'Черновик ответов сохранён. Нажмите «Отправить», когда будете готовы передать ответы организаторам.');
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

        $maxStages = TourCabinetContestDirectionSetting::maxContestStagesForDirection($progress->direction_id);
        if ($maxStages < 3) {
            return $this->redirectToContestBlock()
                ->with('error', 'Для выбранного направления этап 3 не проводится.');
        }

        if ($this->isStage3ResponseCompleteForLock($progress)) {
            return $this->redirectToContestBlock()
                ->with('error', 'Ответ этапа 3 уже сохранён, редактирование недоступно.');
        }

        $config = TourCabinetContestStage3Config::forDirection($progress->direction_id);
        $disk = config('filesystems.upload_disk', 'public');

        if ($config === null) {
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

            if ($progress->stage3_attachment_path) {
                Storage::disk($disk)->delete($progress->stage3_attachment_path);
            }

            $progress->update([
                'stage3_text' => $validated['stage3_text'],
                'stage3_video_url' => $video !== '' ? $video : null,
                'stage3_attachment_path' => null,
                'stage3_attachment_original_name' => null,
            ]);

            return $this->redirectToContestBlock()
                ->with('success', 'Данные этапа 3 сохранены.');
        }

        if ($config->usesFileUpload()) {
            $validated = $request->validate([
                'stage3_text' => ['required', 'string', 'max:20000'],
                'stage3_attachment' => ['required', 'file', 'max:51200', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,odt,odp,zip'],
            ]);

            $path = $request->file('stage3_attachment')->store('tour-cabinet/contest-stage3/'.$progress->user_id, $disk);
            $original = $request->file('stage3_attachment')->getClientOriginalName();

            if ($progress->stage3_attachment_path) {
                Storage::disk($disk)->delete($progress->stage3_attachment_path);
            }

            $progress->update([
                'stage3_text' => $validated['stage3_text'],
                'stage3_video_url' => null,
                'stage3_attachment_path' => $path,
                'stage3_attachment_original_name' => $original,
            ]);

            return $this->redirectToContestBlock()
                ->with('success', 'Данные этапа 3 сохранены.');
        }

        $validated = $request->validate([
            'stage3_text' => ['required', 'string', 'max:20000'],
            'stage3_video_url' => ['required', 'string', 'max:2048'],
        ]);

        $video = trim((string) $validated['stage3_video_url']);
        if ($video === '' || ! preg_match('#^https?://#i', $video)) {
            throw ValidationException::withMessages([
                'stage3_video_url' => 'Укажите ссылку, начинающуюся с http:// или https://',
            ]);
        }

        if ($progress->stage3_attachment_path) {
            Storage::disk($disk)->delete($progress->stage3_attachment_path);
        }

        $progress->update([
            'stage3_text' => $validated['stage3_text'],
            'stage3_video_url' => $video,
            'stage3_attachment_path' => null,
            'stage3_attachment_original_name' => null,
        ]);

        return $this->redirectToContestBlock()
            ->with('success', 'Данные этапа 3 сохранены.');
    }

    /**
     * Скачать прикреплённый к этапу 3 файл (только владелец).
     */
    public function downloadStage3Attachment(Request $request): BinaryFileResponse
    {
        $progress = TourCabinetContestProgress::query()->where('user_id', $request->user()->id)->firstOrFail();
        if (TourCabinetContestDirectionSetting::maxContestStagesForDirection($progress->direction_id) < 3) {
            abort(404);
        }
        if (! $progress->stage3_attachment_path) {
            abort(404);
        }

        $disk = config('filesystems.upload_disk', 'public');

        return Storage::disk($disk)->download(
            $progress->stage3_attachment_path,
            $progress->stage3_attachment_original_name ?: 'file'
        );
    }

    private function isStage3ResponseCompleteForLock(TourCabinetContestProgress $progress): bool
    {
        $config = TourCabinetContestStage3Config::forDirection($progress->direction_id);
        if (! filled($progress->stage3_text) || trim((string) $progress->stage3_text) === '') {
            return false;
        }
        if ($config === null) {
            return true;
        }
        if ($config->usesFileUpload()) {
            return filled($progress->stage3_attachment_path);
        }

        return filled($progress->stage3_video_url) && trim((string) $progress->stage3_video_url) !== '';
    }

    private function redirectToContestBlock(): RedirectResponse
    {
        return redirect()->route('tour-cabinet.dashboard')->withFragment('tour-cabinet-contest');
    }

    private function stage1Complete(TourCabinetContestProgress $progress, int $userId): bool
    {
        $ids = array_values(array_map('intval', $progress->selected_city_ids ?? []));
        if ($ids === [] || ! $progress->direction_id) {
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
    private function activeStage2QuestionsQuery(?int $directionId): Builder
    {
        return TourCabinetContestStage2Question::query()
            ->where('is_active', true)
            ->where(function ($q) use ($directionId): void {
                $q->whereNull('direction_id');
                if ($directionId) {
                    $q->orWhere('direction_id', $directionId);
                }
            });
    }

    public function storeDirection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'direction_id' => ['required', 'integer', 'exists:directions,id'],
        ]);

        $progress = TourCabinetContestProgress::query()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['current_stage' => 1]
        );

        $progress->update([
            'direction_id' => $validated['direction_id'],
            'selected_city_ids' => null,
            'current_stage' => 1,
        ]);

        return $this->redirectToContestBlock();
    }

    public function storeCities(Request $request): RedirectResponse
    {
        $progress = TourCabinetContestProgress::query()->where('user_id', $request->user()->id)->firstOrFail();
        if (! $progress->direction_id) {
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
            ->where('direction_id', $progress->direction_id)
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

        Session::forget('tour_cabinet_contest_reopen_cities');

        return $this->redirectToContestBlock();
    }

    /**
     * Вернуться к выбору городов (до завершения этапа 1), чтобы добавить или заменить города.
     */
    public function reopenCitySelection(Request $request): RedirectResponse
    {
        $progress = TourCabinetContestProgress::query()->where('user_id', $request->user()->id)->firstOrFail();
        $selected = array_map('intval', $progress->selected_city_ids ?? []);
        if (! $progress->direction_id || $selected === []) {
            return $this->redirectToContestBlock();
        }
        if ($this->stage1Complete($progress, $request->user()->id)) {
            return $this->redirectToContestBlock();
        }

        Session::put('tour_cabinet_contest_reopen_cities', true);

        return $this->redirectToContestBlock();
    }

    /**
     * Убрать город из выбранных до отправки анкеты по этому городу.
     */
    public function removeSelectedCity(Request $request, City $city): RedirectResponse
    {
        $userId = $request->user()->id;
        $progress = TourCabinetContestProgress::query()->where('user_id', $userId)->firstOrFail();
        $selected = array_map('intval', $progress->selected_city_ids ?? []);
        if (! in_array($city->id, $selected, true)) {
            abort(404);
        }

        if (TourCabinetContestCitySubmission::query()
            ->where('user_id', $userId)
            ->where('city_id', $city->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'city' => 'Нельзя убрать город после отправки анкеты.',
            ]);
        }

        $newIds = array_values(array_filter($selected, fn (int $id) => $id !== $city->id));

        if ((int) session('tour_cabinet_contest_form_city_id') === $city->id) {
            session()->forget('tour_cabinet_contest_form_city_id');
        }

        $progress->update([
            'selected_city_ids' => $newIds === [] ? null : $newIds,
        ]);

        if ($newIds === []) {
            Session::forget('tour_cabinet_contest_reopen_cities');
        }

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
            ->where('direction_id', $progress->direction_id)
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
