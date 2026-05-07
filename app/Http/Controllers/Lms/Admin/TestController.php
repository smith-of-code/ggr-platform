<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsTest;
use App\Models\Lms\LmsTestAnswer;
use App\Models\Lms\LmsTestAttempt;
use App\Models\Lms\LmsTestQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TestController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $accessLevel = LmsProfile::backofficeAccessForEvent(auth()->user(), $event);
        $canManageTests = $accessLevel === 'admin';

        $tests = $event->tests()
            ->withCount(['attempts', 'questions'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Lms/Admin/Tests/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'tests' => $tests,
            'canManageTests' => $canManageTests,
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
        $validated['in_menu'] = $request->boolean('in_menu', false);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['passing_score'] ??= 60;
        $validated['gamification_points'] = (int) ($validated['gamification_points'] ?? 0);

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
        $validated['in_menu'] = $request->boolean('in_menu', false);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['passing_score'] ??= 60;
        $validated['gamification_points'] = (int) ($validated['gamification_points'] ?? 0);

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

    public function results(Request $request, LmsEvent $event, LmsTest $test): Response
    {
        $this->ensureTestBelongsToEvent($test, $event);
        $accessLevel = LmsProfile::backofficeAccessForEvent(auth()->user(), $event);
        $canManageTests = $accessLevel === 'admin';

        $status = (string) $request->query('status', 'all');
        if (! in_array($status, ['all', 'passed', 'failed'], true)) {
            $status = 'all';
        }
        $search = trim((string) $request->query('search', ''));
        $showAnswers = $request->boolean('show_answers', false);

        $query = LmsTestAttempt::query()
            ->where('lms_test_id', $test->id)
            ->with(['user:id,name,last_name,first_name,patronymic,email']);

        if ($status === 'passed') {
            $query->where('passed', true);
        } elseif ($status === 'failed') {
            $query->where('passed', false)->whereNotNull('finished_at');
        }

        if ($search !== '') {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'ilike', '%'.$search.'%')
                    ->orWhere('email', 'ilike', '%'.$search.'%')
                    ->orWhere('last_name', 'ilike', '%'.$search.'%')
                    ->orWhere('first_name', 'ilike', '%'.$search.'%');
            });
        }

        if ($showAnswers) {
            $query->with([
                'responses.question' => fn ($q) => $q->with('answers'),
            ]);
        }

        $attempts = $query
            ->orderByDesc('started_at')
            ->paginate(20)
            ->withQueryString();

        $attempts->getCollection()->transform(function (LmsTestAttempt $attempt) use ($showAnswers) {
            $user = $attempt->user;
            $fullName = trim(implode(' ', array_filter([
                $user ? $user->last_name : null,
                $user ? $user->first_name : null,
            ])));

            $row = [
                'id' => $attempt->id,
                'status' => $attempt->status,
                'score' => $attempt->score,
                'max_score' => $attempt->max_score,
                'percentage' => $attempt->percentage,
                'passed' => (bool) $attempt->passed,
                'started_at' => $attempt->started_at,
                'finished_at' => $attempt->finished_at,
                'user' => [
                    'id' => $user ? $user->id : null,
                    'name' => $fullName !== '' ? $fullName : ($user ? $user->name : 'Участник'),
                    'email' => $user ? $user->email : null,
                ],
            ];

            if (! $showAnswers) {
                $row['responses'] = [];

                return $row;
            }

            $responses = [];
            foreach ($attempt->responses as $response) {
                $question = $response->question;
                $answers = $question ? $question->answers : collect();

                $selectedIds = is_array($response->selected_answer_ids)
                    ? array_map('intval', $response->selected_answer_ids)
                    : [];
                $selectedTexts = $answers
                    ->whereIn('id', $selectedIds)
                    ->pluck('answer')
                    ->filter()
                    ->values()
                    ->all();
                $correctTexts = $answers
                    ->where('is_correct', true)
                    ->pluck('answer')
                    ->filter()
                    ->values()
                    ->all();

                $responses[] = [
                    'question' => $question ? $question->question : 'Вопрос удалён',
                    'type' => $question ? $question->type : null,
                    'is_correct' => (bool) $response->is_correct,
                    'points_earned' => (int) ($response->points_earned ?? 0),
                    'selected_answers' => $selectedTexts,
                    'correct_answers' => $correctTexts,
                    'text_answer' => $response->text_answer,
                ];
            }

            $row['responses'] = $responses;

            return $row;
        });

        $statsBase = LmsTestAttempt::query()->where('lms_test_id', $test->id);
        $stats = [
            'total_attempts' => (clone $statsBase)->count(),
            'completed_attempts' => (clone $statsBase)->where('status', 'completed')->count(),
            'passed_attempts' => (clone $statsBase)->where('passed', true)->count(),
            'unique_users' => (clone $statsBase)->distinct('user_id')->count('user_id'),
            'avg_percentage' => round((float) ((clone $statsBase)->whereNotNull('percentage')->avg('percentage') ?? 0), 1),
        ];

        return Inertia::render('Lms/Admin/Tests/Results', [
            'event' => $event->only(['id', 'slug', 'title']),
            'test' => $test->only(['id', 'title', 'passing_score', 'max_attempts']),
            'attempts' => $attempts,
            'stats' => $stats,
            'canManageTests' => $canManageTests,
            'filters' => [
                'status' => $status,
                'search' => $search,
                'show_answers' => $showAnswers,
            ],
        ]);
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
            'in_menu' => ['boolean'],
            'passing_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:0'],
            'gamification_points' => ['nullable', 'integer', 'min:0'],
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
