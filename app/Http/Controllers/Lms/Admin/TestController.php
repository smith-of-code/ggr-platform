<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsTest;
use App\Models\Lms\LmsTestAnswer;
use App\Models\Lms\LmsTestQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TestController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $tests = $event->tests()
            ->withCount(['attempts', 'questions'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Lms/Admin/Tests/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'tests' => $tests,
        ]);
    }

    public function create(LmsEvent $event): Response
    {
        return Inertia::render('Lms/Admin/Tests/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'test' => null,
        ]);
    }

    public function store(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $this->validateTest($request);

        $validated['lms_event_id'] = $event->id;
        $validated['shuffle_questions'] = $request->boolean('shuffle_questions', false);
        $validated['shuffle_answers'] = $request->boolean('shuffle_answers', false);
        $validated['show_correct_answers'] = $request->boolean('show_correct_answers', true);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['passing_score'] ??= 60;

        $test = LmsTest::create($validated);

        $this->syncQuestions($test, $validated['questions'] ?? []);

        return redirect()->route('lms.admin.tests.index', $event)->with('success', 'Тест создан');
    }

    public function edit(LmsEvent $event, LmsTest $test): Response
    {
        $this->ensureTestBelongsToEvent($test, $event);

        $test->load(['questions.answers']);

        return Inertia::render('Lms/Admin/Tests/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'test' => $test,
        ]);
    }

    public function update(Request $request, LmsEvent $event, LmsTest $test): RedirectResponse
    {
        $this->ensureTestBelongsToEvent($test, $event);

        $validated = $this->validateTest($request);

        $validated['shuffle_questions'] = $request->boolean('shuffle_questions', false);
        $validated['shuffle_answers'] = $request->boolean('shuffle_answers', false);
        $validated['show_correct_answers'] = $request->boolean('show_correct_answers', true);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['passing_score'] ??= 60;

        $test->update($validated);

        $this->syncQuestions($test, $validated['questions'] ?? []);

        return redirect()->route('lms.admin.tests.index', $event)->with('success', 'Тест обновлён');
    }

    public function destroy(LmsEvent $event, LmsTest $test): RedirectResponse
    {
        $this->ensureTestBelongsToEvent($test, $event);

        $test->delete();

        return redirect()->route('lms.admin.tests.index', $event)->with('success', 'Тест удалён');
    }

    private function validateTest(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:0'],
            'shuffle_questions' => ['boolean'],
            'shuffle_answers' => ['boolean'],
            'show_correct_answers' => ['boolean'],
            'passing_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:0'],
            'questions' => ['nullable', 'array'],
            'questions.*.question' => ['required', 'string'],
            'questions.*.type' => ['nullable', 'string'],
            'questions.*.points' => ['nullable', 'integer', 'min:0'],
            'questions.*.position' => ['nullable', 'integer'],
            'questions.*.answers' => ['nullable', 'array'],
            'questions.*.answers.*.answer' => ['required', 'string'],
            'questions.*.answers.*.is_correct' => ['boolean'],
            'questions.*.answers.*.position' => ['nullable', 'integer'],
        ]);
    }

    private function syncQuestions(LmsTest $test, array $questions): void
    {
        $test->questions()->delete();

        foreach ($questions as $qIndex => $q) {
            $question = LmsTestQuestion::create([
                'lms_test_id' => $test->id,
                'question' => $q['question'],
                'type' => $q['type'] ?? 'single',
                'points' => $q['points'] ?? 1,
                'position' => $q['position'] ?? $qIndex,
            ]);

            foreach ($q['answers'] ?? [] as $aIndex => $a) {
                LmsTestAnswer::create([
                    'lms_test_question_id' => $question->id,
                    'answer' => $a['answer'],
                    'is_correct' => $a['is_correct'] ?? false,
                    'position' => $a['position'] ?? $aIndex,
                ]);
            }
        }
    }

    private function ensureTestBelongsToEvent(LmsTest $test, LmsEvent $event): void
    {
        if ($test->lms_event_id !== $event->id) {
            abort(404);
        }
    }
}
