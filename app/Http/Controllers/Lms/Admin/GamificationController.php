<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGamificationPoint;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsGamificationRule;
use App\Services\GamificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class GamificationController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $rules = $event->gamificationRules()->orderBy('created_at', 'desc')->paginate(15);
        $profile = $event->profiles()
            ->where('user_id', auth()->id())
            ->with('lmsRole:id,name,slug')
            ->first();
        $canManageRules = $profile ? LmsProfile::isBackofficeAdminProfile($profile) : false;

        $profiles = $event->profiles()->with(['user:id,name,email', 'lmsRole:id,name', 'cityRelation:id,name'])->get();
        $users = $profiles->map(function ($profile) {
            $user = $profile->user;
            if (!$user) return null;
            $roleName = $profile->lmsRole ? $profile->lmsRole->name : null;
            $cityName = $profile->city;
            if (!$cityName && $profile->cityRelation) {
                $cityName = $profile->cityRelation->name;
            }
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $roleName ?? $profile->role ?? '—',
                'city' => $cityName,
            ];
        })->filter()->unique('id')->values();

        $gamification = app(GamificationService::class);
        $leaderboard = $gamification->getAdminLeaderboardRows($event, 100);
        $userIds = array_column($leaderboard, 'id');
        $pointsByUser = $gamification->getRecentPointsByUserIds($event, $userIds, 100);

        return Inertia::render('Lms/Admin/Gamification/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'rules' => $rules,
            'users' => $users,
            'leaderboard' => $leaderboard,
            'pointsByUser' => $pointsByUser,
            'canManageRules' => $canManageRules,
        ]);
    }

    public function create(LmsEvent $event): Response
    {
        return Inertia::render('Lms/Admin/Gamification/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'rule' => null,
            'actions' => GamificationService::$defaultActions,
        ]);
    }

    public function store(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate($this->ruleValidationRules(), $this->ruleValidationMessages());

        $validated['lms_event_id'] = $event->id;
        $validated['is_auto'] = $request->boolean('is_auto', true);
        $validated['is_active'] = $request->boolean('is_active', true);

        LmsGamificationRule::create($validated);

        return redirect()->route('lms.admin.gamification.index', $event)->with('success', 'Правило создано');
    }

    public function edit(LmsEvent $event, LmsGamificationRule $gamification): Response
    {
        $this->ensureRuleBelongsToEvent($gamification, $event);

        return Inertia::render('Lms/Admin/Gamification/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'rule' => $gamification,
            'actions' => GamificationService::$defaultActions,
        ]);
    }

    public function update(Request $request, LmsEvent $event, LmsGamificationRule $gamification): RedirectResponse
    {
        $this->ensureRuleBelongsToEvent($gamification, $event);

        $validated = $request->validate($this->ruleValidationRules(), $this->ruleValidationMessages());

        $validated['is_auto'] = $request->boolean('is_auto', true);
        $validated['is_active'] = $request->boolean('is_active', true);

        $gamification->update($validated);

        return redirect()->route('lms.admin.gamification.index', $event)->with('success', 'Правило обновлено');
    }

    public function destroy(LmsEvent $event, LmsGamificationRule $gamification): RedirectResponse
    {
        $this->ensureRuleBelongsToEvent($gamification, $event);

        $gamification->delete();

        return redirect()->route('lms.admin.gamification.index', $event)->with('success', 'Правило удалено');
    }

    public function manualPoints(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'points' => ['required', 'integer'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        foreach ($validated['user_ids'] as $userId) {
            LmsGamificationPoint::create([
                'lms_event_id' => $event->id,
                'user_id' => $userId,
                'lms_gamification_rule_id' => null,
                'points' => $validated['points'],
                'reason' => $validated['reason'],
            ]);
        }

        return redirect()->back()->with('success', 'Баллы начислены');
    }

    private function ensureRuleBelongsToEvent(LmsGamificationRule $rule, LmsEvent $event): void
    {
        if ($rule->lms_event_id !== $event->id) {
            abort(404);
        }
    }

    private function ruleValidationRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'action' => ['required', 'string', Rule::in(array_keys(GamificationService::$defaultActions))],
            'points' => ['required', 'integer', 'min:0'],
            'max_times' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function ruleValidationMessages(): array
    {
        return [
            'title.required' => 'Укажите название правила.',
            'title.max' => 'Название правила не должно превышать 255 символов.',
            'action.required' => 'Выберите действие для правила.',
            'action.in' => 'Выбрано некорректное действие.',
            'points.required' => 'Укажите количество баллов.',
            'points.integer' => 'Баллы должны быть целым числом.',
            'points.min' => 'Баллы не могут быть отрицательными.',
            'max_times.integer' => 'Лимит начислений должен быть целым числом.',
            'max_times.min' => 'Лимит начислений не может быть отрицательным.',
        ];
    }
}
