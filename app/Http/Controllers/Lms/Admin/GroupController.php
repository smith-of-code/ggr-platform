<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGroup;
use App\Models\Lms\LmsProfile;
use App\Models\User;
use App\Services\LmsCityGroupSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    public function __construct(
        private readonly LmsCityGroupSyncService $cityGroupSync,
    ) {}

    public function index(LmsEvent $event): Response
    {
        $this->cityGroupSync->syncForEvent($event);

        $profile = $this->currentProfile($event);
        $canManageAllGroups = $profile ? LmsProfile::isBackofficeAdminProfile($profile) : false;

        $groupsQuery = $event->groups()
            ->withCount('members')
            ->with(['curator:id,name', 'city:id,name'])
            ->orderByDesc('is_city_group')
            ->orderBy('title');

        if (! $canManageAllGroups) {
            $groupsQuery->where('curator_id', auth()->id());
        }

        $groups = $groupsQuery->paginate(15);

        return Inertia::render('Lms/Admin/Groups/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'groups' => $groups,
            'canSetCurator' => $canManageAllGroups,
        ]);
    }

    private function cityOptionsForEvent(LmsEvent $event): array
    {
        return LmsProfile::query()
            ->where('lms_event_id', $event->id)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->map(fn (string $city) => ['value' => $city, 'label' => $city])
            ->values()
            ->all();
    }

    public function create(LmsEvent $event): Response
    {
        $profile = $this->currentProfile($event);
        $canSetCurator = $profile ? LmsProfile::isBackofficeAdminProfile($profile) : false;
        $profileUsers = $event->profiles()->with('user:id,name,email')->get()->pluck('user')->filter()->unique('id');
        $users = $profileUsers->isNotEmpty() ? $profileUsers->values() : User::orderBy('name')->get(['id', 'name', 'email']);

        return Inertia::render('Lms/Admin/Groups/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'group' => null,
            'users' => $users,
            'cityOptions' => $this->cityOptionsForEvent($event),
            'canSetCurator' => $canSetCurator,
            'fixedCuratorName' => auth()->user() ? auth()->user()->name : null,
        ]);
    }

    public function store(Request $request, LmsEvent $event): RedirectResponse
    {
        $profile = $this->currentProfile($event);
        $canSetCurator = $profile ? LmsProfile::isBackofficeAdminProfile($profile) : false;

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'curator_id' => ['nullable', 'exists:users,id'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'linked_cities' => ['nullable', 'array'],
            'linked_cities.*' => ['string', 'max:255'],
        ]);

        $linkedCities = $this->normalizeLinkedCities($validated['linked_cities'] ?? []);

        if (! $canSetCurator) {
            $validated['curator_id'] = auth()->id();
        }

        $group = LmsGroup::create([
            'lms_event_id' => $event->id,
            'title' => $validated['title'],
            'curator_id' => $validated['curator_id'] ?? null,
            'linked_cities' => $linkedCities,
            'is_city_group' => false,
        ]);

        if ($request->filled('user_ids')) {
            $group->members()->sync($request->user_ids);
        }

        return redirect()->route('lms.admin.groups.index', $event)->with('success', 'Группа создана');
    }

    public function edit(LmsEvent $event, LmsGroup $group): Response
    {
        $this->ensureGroupBelongsToEvent($group, $event);
        $profile = $this->currentProfile($event);
        $canSetCurator = $profile ? LmsProfile::isBackofficeAdminProfile($profile) : false;
        $this->ensureCanManageGroup($group, $canSetCurator);

        if ($group->is_city_group) {
            $this->cityGroupSync->syncForEvent($event);
            $group->refresh()->load([
                'members:id,name,email',
                'city:id,name',
            ]);
        } else {
            $group->load('members:id,name,email');
        }

        $profileUsers = $event->profiles()->with('user:id,name,email')->get()->pluck('user')->filter()->unique('id');
        $users = $profileUsers->isNotEmpty() ? $profileUsers->values() : User::orderBy('name')->get(['id', 'name', 'email']);

        return Inertia::render('Lms/Admin/Groups/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'group' => $group,
            'users' => $users,
            'cityOptions' => $this->cityOptionsForEvent($event),
            'canSetCurator' => $canSetCurator,
            'fixedCuratorName' => auth()->user() ? auth()->user()->name : null,
        ]);
    }

    public function update(Request $request, LmsEvent $event, LmsGroup $group): RedirectResponse
    {
        $this->ensureGroupBelongsToEvent($group, $event);
        $profile = $this->currentProfile($event);
        $canSetCurator = $profile ? LmsProfile::isBackofficeAdminProfile($profile) : false;
        $this->ensureCanManageGroup($group, $canSetCurator);

        if ($group->is_city_group) {
            $validated = $request->validate([
                'member_inactive' => ['nullable', 'array'],
                'member_inactive.*' => ['boolean'],
            ]);

            if (! empty($validated['member_inactive'])) {
                foreach ($validated['member_inactive'] as $userId => $inactive) {
                    $this->cityGroupSync->setMemberGamificationInactive(
                        $group,
                        (int) $userId,
                        (bool) $inactive,
                    );
                }
            }

            return redirect()->route('lms.admin.groups.index', $event)->with('success', 'Группа обновлена');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'curator_id' => ['nullable', 'exists:users,id'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'linked_cities' => ['nullable', 'array'],
            'linked_cities.*' => ['string', 'max:255'],
        ]);

        if (! $canSetCurator) {
            $validated['curator_id'] = auth()->id();
        }

        $group->update([
            'title' => $validated['title'],
            'curator_id' => $validated['curator_id'] ?? null,
            'linked_cities' => $this->normalizeLinkedCities($validated['linked_cities'] ?? []),
        ]);

        $group->members()->sync($request->user_ids ?? []);

        return redirect()->route('lms.admin.groups.index', $event)->with('success', 'Группа обновлена');
    }

    public function destroy(LmsEvent $event, LmsGroup $group): RedirectResponse
    {
        $this->ensureGroupBelongsToEvent($group, $event);
        $profile = $this->currentProfile($event);
        $canSetCurator = $profile ? LmsProfile::isBackofficeAdminProfile($profile) : false;
        $this->ensureCanManageGroup($group, $canSetCurator);

        if ($group->is_city_group) {
            return redirect()->route('lms.admin.groups.index', $event)
                ->with('error', 'Системную городскую группу нельзя удалить');
        }

        $group->delete();

        return redirect()->route('lms.admin.groups.index', $event)->with('success', 'Группа удалена');
    }

    private function ensureGroupBelongsToEvent(LmsGroup $group, LmsEvent $event): void
    {
        if ($group->lms_event_id !== $event->id) {
            abort(404);
        }
    }

    private function ensureCanManageGroup(LmsGroup $group, bool $canManageAllGroups): void
    {
        if ($canManageAllGroups) {
            return;
        }

        if ((int) $group->curator_id !== (int) auth()->id()) {
            abort(403, 'Можно управлять только группами, где вы куратор.');
        }
    }

    private function currentProfile(LmsEvent $event): ?LmsProfile
    {
        return $event->profiles()
            ->where('user_id', auth()->id())
            ->with('lmsRole:id,name,slug')
            ->first();
    }

    /**
     * @param  array<int, string>  $cities
     * @return list<string>
     */
    private function normalizeLinkedCities(array $cities): array
    {
        $out = [];
        foreach ($cities as $c) {
            $t = is_string($c) ? trim($c) : '';
            if ($t !== '' && ! in_array($t, $out, true)) {
                $out[] = $t;
            }
        }

        return $out;
    }
}
