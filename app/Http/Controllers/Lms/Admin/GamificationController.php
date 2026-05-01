<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGamificationPoint;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsGamificationRule;
use App\Services\GamificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class GamificationController extends Controller
{
    public function index(Request $request, LmsEvent $event): Response
    {
        $rules = $event->gamificationRules()->orderBy('created_at', 'desc')->paginate(15);
        $profile = $event->profiles()
            ->where('user_id', auth()->id())
            ->with('lmsRole:id,name,slug')
            ->first();
        $canManageRules = $profile ? LmsProfile::isBackofficeAdminProfile($profile) : false;

        $groupUserMap = [];
        $event->groups()
            ->with('members:id')
            ->get(['id', 'lms_event_id', 'title'])
            ->each(function ($group) use (&$groupUserMap) {
                foreach ($group->members as $member) {
                    $groupUserMap[$member->id][] = $group->title;
                }
            });

        $profiles = $event->profiles()->with(['user:id,name,email', 'lmsRole:id,name', 'cityRelation:id,name'])->get();
        $users = $profiles->map(function ($profile) use ($groupUserMap) {
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
                'groups' => array_values(array_unique($groupUserMap[$user->id] ?? [])),
            ];
        })->filter()->unique('id')->values();

        $gamification = app(GamificationService::class);
        $leaderboard = $gamification->getAdminLeaderboardRows($event, 100);
        $userIds = array_column($leaderboard, 'id');
        $pointsByUser = $gamification->getRecentPointsByUserIds($event, $userIds, 100);
        $canManagePointAdjustments = $profile ? $this->canManagePoints($profile) : false;
        $pointsHistory = $this->buildPointsHistoryQuery($request, $event)
            ->paginate(30)
            ->withQueryString();
        $historyGroupOptions = $event->groups()
            ->orderBy('title')
            ->pluck('title')
            ->filter(fn ($title) => is_string($title) && trim($title) !== '')
            ->map(fn ($title) => trim((string) $title))
            ->unique()
            ->values();

        return Inertia::render('Lms/Admin/Gamification/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'rules' => $rules,
            'users' => $users,
            'leaderboard' => $leaderboard,
            'pointsByUser' => $pointsByUser,
            'pointsHistory' => $pointsHistory,
            'historyFilters' => [
                'search' => trim((string) $request->input('history_search', '')),
                'type' => (string) $request->input('history_type', ''),
                'group' => trim((string) $request->input('history_group', '')),
                'date_from' => (string) $request->input('history_date_from', ''),
                'date_to' => (string) $request->input('history_date_to', ''),
            ],
            'historyGroupOptions' => $historyGroupOptions,
            'canManageRules' => $canManageRules,
            'canManagePointAdjustments' => $canManagePointAdjustments,
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

    public function destroyPoint(LmsEvent $event, LmsGamificationPoint $point): RedirectResponse
    {
        if ($point->lms_event_id !== $event->id) {
            abort(404);
        }

        $profile = $event->profiles()
            ->where('user_id', auth()->id())
            ->with('lmsRole:id,name,slug')
            ->first();

        if (! $profile || ! $this->canManagePoints($profile)) {
            abort(403, 'Недостаточно прав для удаления начислений баллов.');
        }

        $point->delete();

        return redirect()->back()->with('success', 'Начисление удалено.');
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

    private function canManagePoints(LmsProfile $profile): bool
    {
        if (LmsProfile::isBackofficeAdminProfile($profile) || LmsProfile::isGamificationPointsOnlyProfile($profile)) {
            return true;
        }

        $candidates = array_filter([
            $profile->role,
            $profile->lmsRole ? $profile->lmsRole->slug : null,
            $profile->lmsRole ? $profile->lmsRole->name : null,
        ], fn ($v) => is_string($v) && trim($v) !== '');

        foreach ($candidates as $candidate) {
            $value = mb_strtolower(trim($candidate));
            if ($value === 'participant' || $value === 'участник') {
                return false;
            }
        }

        return true;
    }

    private function buildPointsHistoryQuery(Request $request, LmsEvent $event): Builder
    {
        $query = LmsGamificationPoint::query()
            ->where('lms_event_id', $event->id)
            ->with([
                'user:id,name,email',
                'rule:id,title',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $search = trim((string) $request->input('history_search', ''));
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('reason', 'ilike', '%' . $search . '%')
                    ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'ilike', '%' . $search . '%')
                            ->orWhere('email', 'ilike', '%' . $search . '%');
                    })
                    ->orWhereHas('rule', function (Builder $ruleQuery) use ($search) {
                        $ruleQuery->where('title', 'ilike', '%' . $search . '%');
                    });
            });
        }

        $type = (string) $request->input('history_type', '');
        if ($type === 'manual') {
            $query->whereNull('lms_gamification_rule_id');
        } elseif ($type === 'auto') {
            $query->whereNotNull('lms_gamification_rule_id');
        }

        $groupTitle = trim((string) $request->input('history_group', ''));
        if ($groupTitle !== '') {
            $memberIds = DB::table('lms_group_members')
                ->join('lms_groups', 'lms_groups.id', '=', 'lms_group_members.lms_group_id')
                ->where('lms_groups.lms_event_id', $event->id)
                ->where('lms_groups.title', $groupTitle)
                ->pluck('lms_group_members.user_id');

            $query->whereIn('user_id', $memberIds->all());
        }

        $dateFrom = trim((string) $request->input('history_date_from', ''));
        if ($dateFrom !== '') {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        $dateTo = trim((string) $request->input('history_date_to', ''));
        if ($dateTo !== '') {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query;
    }
}
