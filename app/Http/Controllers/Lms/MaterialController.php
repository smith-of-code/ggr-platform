<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsMaterialSection;
use App\Models\Lms\LmsProfile;
use App\Services\GamificationService;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MaterialController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $user = auth()->user();
        $groupIds = DB::table('lms_group_members')
            ->where('user_id', $user->id)
            ->pluck('lms_group_id');

        $sections = LmsMaterialSection::where('lms_event_id', $event->id)
            ->orderBy('position')
            ->when($groupIds->isNotEmpty(), function ($query) use ($groupIds) {
                $query->where(function ($q) use ($groupIds) {
                    $q->whereDoesntHave('groups')
                        ->orWhereHas('groups', fn($g) => $g->whereIn('lms_groups.id', $groupIds));
                });
            }, function ($query) {
                $query->whereDoesntHave('groups');
            })
            ->get(['id', 'title', 'content', 'in_menu', 'position']);

        $profile = LmsProfile::where('lms_event_id', $event->id)->where('user_id', $user->id)->first();

        return Inertia::render('Lms/Materials/Index', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'sections' => $sections,
        ]);
    }

    public function show(LmsEvent $event, LmsMaterialSection $section): Response
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

        $profile = LmsProfile::where('lms_event_id', $event->id)->where('user_id', $user->id)->first();

        app(GamificationService::class)->awardPoints($event, $user, 'material_view', "Материал: {$section->title}");

        $section->load('files');

        return Inertia::render('Lms/Materials/Show', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'section' => [
                ...$section->only(['id', 'title', 'content']),
                'files' => $section->files->map(fn ($f) => $f->only(['id', 'title', 'file_path', 'file_name', 'file_size'])),
            ],
        ]);
    }
}
