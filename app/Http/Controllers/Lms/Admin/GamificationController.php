<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGamificationPoint;
use App\Models\Lms\LmsGroup;
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

        $gamificationGroups = $event->groups()
            ->orderBy('title')
            ->get(['id', 'title', 'linked_cities']);

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
            'gamificationGroups' => $gamificationGroups,
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
                'for_city_ranking_only' => false,
                'lms_gamification_rule_id' => null,
                'points' => $validated['points'],
                'reason' => $validated['reason'],
            ]);
        }

        return redirect()->back()->with('success', 'Баллы начислены');
    }

    public function manualGroupCityPoints(Request $request, LmsEvent $event): RedirectResponse
    {
        $profile = $event->profiles()
            ->where('user_id', auth()->id())
            ->with('lmsRole:id,name,slug')
            ->first();

        if (! $profile || ! $this->canManagePoints($profile)) {
            abort(403, 'Недостаточно прав для начисления баллов.');
        }

        $validated = $request->validate([
            'lms_group_id' => ['required', 'exists:lms_groups,id'],
            'points' => ['required', 'integer', 'min:1', 'max:100000'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $group = LmsGroup::query()
            ->where('lms_event_id', $event->id)
            ->where('id', $validated['lms_group_id'])
            ->firstOrFail();

        $cities = array_values(array_filter($group->linked_cities ?? [], fn ($c) => is_string($c) && trim($c) !== ''));
        if ($cities === []) {
            return redirect()->back()->withErrors([
                'lms_group_id' => 'У группы не указаны города. Откройте группу в разделе «Группы» и привяжите города.',
            ]);
        }

        $total = (int) $validated['points'];
        $split = $this->splitIntegerAcrossBuckets($total, count($cities));
        $batchBase = (int) ((microtime(true) * 10000) % 100000000);

        foreach ($cities as $i => $city) {
            LmsGamificationPoint::create([
                'lms_event_id' => $event->id,
                'user_id' => null,
                'for_city_ranking_only' => true,
                'city_name' => $city,
                'lms_group_id' => $group->id,
                'lms_gamification_rule_id' => null,
                'source_type' => GamificationService::SOURCE_GROUP_CITY_BONUS,
                'source_id' => $batchBase + $i,
                'points' => $split[$i],
                'reason' => $validated['reason'].' (группа «'.$group->title.'», город «'.$city.'»)',
            ]);
        }

        return redirect()->back()->with('success', 'Баллы группы учтены в рейтинге городов (личный зачёт участников не изменён).');
    }

    /**
     * @return list<int>
     */
    private function splitIntegerAcrossBuckets(int $total, int $bucketCount): array
    {
        if ($bucketCount <= 0) {
            return [];
        }
        $base = intdiv($total, $bucketCount);
        $remainder = $total % $bucketCount;
        $out = array_fill(0, $bucketCount, $base);
        for ($i = 0; $i < $remainder; $i++) {
            $out[$i]++;
        }

        return $out;
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
                'group:id,title',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $search = trim((string) $request->input('history_search', ''));
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('reason', 'ilike', '%' . $search . '%')
                    ->orWhere('city_name', 'ilike', '%' . $search . '%')
                    ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'ilike', '%' . $search . '%')
                            ->orWhere('email', 'ilike', '%' . $search . '%');
                    })
                    ->orWhereHas('rule', function (Builder $ruleQuery) use ($search) {
                        $ruleQuery->where('title', 'ilike', '%' . $search . '%');
                    })
                    ->orWhereHas('group', function (Builder $groupQuery) use ($search) {
                        $groupQuery->where('title', 'ilike', '%' . $search . '%');
                    });
            });
        }

        $type = (string) $request->input('history_type', '');
        if ($type === 'manual') {
            $query->where(function (Builder $q) {
                $q->where(function (Builder $inner) {
                    $inner->whereNull('lms_gamification_rule_id')
                        ->whereNull('source_type')
                        ->whereNull('source_id');
                })->orWhere('for_city_ranking_only', true);
            });
        } elseif ($type === 'auto') {
            $query->where(function (Builder $q) {
                $q->whereNotNull('lms_gamification_rule_id')
                    ->orWhere(function (Builder $q2) {
                        $q2->whereNotNull('source_type')
                            ->whereNotNull('source_id')
                            ->where('for_city_ranking_only', false);
                    });
            });
        }

        $groupTitle = trim((string) $request->input('history_group', ''));
        if ($groupTitle !== '') {
            $memberIds = DB::table('lms_group_members')
                ->join('lms_groups', 'lms_groups.id', '=', 'lms_group_members.lms_group_id')
                ->where('lms_groups.lms_event_id', $event->id)
                ->where('lms_groups.title', $groupTitle)
                ->pluck('lms_group_members.user_id');

            $groupIds = DB::table('lms_groups')
                ->where('lms_event_id', $event->id)
                ->where('title', $groupTitle)
                ->pluck('id');

            $query->where(function (Builder $builder) use ($memberIds, $groupIds) {
                if ($memberIds->isNotEmpty() && $groupIds->isNotEmpty()) {
                    $builder->where(function (Builder $b) use ($memberIds, $groupIds) {
                        $b->whereIn('user_id', $memberIds->all())
                            ->orWhereIn('lms_group_id', $groupIds->all());
                    });
                } elseif ($memberIds->isNotEmpty()) {
                    $builder->whereIn('user_id', $memberIds->all());
                } elseif ($groupIds->isNotEmpty()) {
                    $builder->whereIn('lms_group_id', $groupIds->all());
                }
            });
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
