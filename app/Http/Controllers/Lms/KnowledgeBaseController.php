<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsKbSection;
use App\Models\Lms\LmsProfile;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class KnowledgeBaseController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $user = auth()->user();
        $groupIds = DB::table('lms_group_members')
            ->where('user_id', $user->id)
            ->pluck('lms_group_id');

        $sections = LmsKbSection::where('lms_event_id', $event->id)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('position')
            ->when($groupIds->isNotEmpty(), function ($query) use ($groupIds) {
                $query->where(function ($q) use ($groupIds) {
                    $q->whereDoesntHave('groups')
                        ->orWhereHas('groups', fn($g) => $g->whereIn('lms_groups.id', $groupIds));
                });
            }, function ($query) {
                $query->whereDoesntHave('groups');
            })
            ->get();

        $sectionsData = $sections->map(fn($s) => [
            'id' => $s->id,
            'title' => $s->title,
            'description' => $s->description,
            'children' => $s->children->map(fn($c) => $c->only(['id', 'title', 'description'])),
        ]);

        $profile = LmsProfile::where('lms_event_id', $event->id)->where('user_id', $user->id)->first();

        return Inertia::render('Lms/KnowledgeBase/Index', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'sections' => $sectionsData,
        ]);
    }

    public function show(LmsEvent $event, LmsKbSection $section): Response
    {
        if ($section->lms_event_id !== $event->id) {
            abort(404);
        }
        $user = auth()->user();
        $groupIds = DB::table('lms_group_members')
            ->where('user_id', $user->id)
            ->pluck('lms_group_id');

        $hasAccess = $section->groups->isEmpty()
            || $section->groups()->whereIn('lms_groups.id', $groupIds)->exists();

        if (!$hasAccess) {
            abort(403);
        }

        $section->load(['children.items', 'items']);
        $profile = LmsProfile::where('lms_event_id', $event->id)->where('user_id', $user->id)->first();

        return Inertia::render('Lms/KnowledgeBase/Show', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'section' => [
                'id' => $section->id,
                'title' => $section->title,
                'description' => $section->description,
                'children' => $section->children->sortBy('position')->values(),
                'items' => $section->items->sortBy('position')->values(),
            ],
        ]);
    }
}
