<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGamificationPoint;
use App\Models\Lms\LmsGamificationRule;
use App\Services\GamificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GamificationController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $rules = $event->gamificationRules()->orderBy('created_at', 'desc')->paginate(15);

        $profiles = $event->profiles()->with(['user:id,name,email', 'lmsRole:id,name'])->get();
        $users = $profiles->map(function ($profile) {
            $user = $profile->user;
            if (!$user) return null;
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $profile->lmsRole?->name ?? $profile->role ?? '—',
            ];
        })->filter()->unique('id')->values();

        return Inertia::render('Lms/Admin/Gamification/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'rules' => $rules,
            'users' => $users,
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
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'action' => ['nullable', 'string'],
            'points' => ['required', 'integer', 'min:0'],
            'max_times' => ['nullable', 'integer', 'min:0'],
        ]);

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

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'action' => ['nullable', 'string'],
            'points' => ['required', 'integer', 'min:0'],
            'max_times' => ['nullable', 'integer', 'min:0'],
        ]);

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
}
