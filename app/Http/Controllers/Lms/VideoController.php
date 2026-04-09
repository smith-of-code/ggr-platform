<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGroup;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsVideo;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller
{
    public function index(Request $request, LmsEvent $event): Response
    {
        $user = auth()->user();
        $groupIds = DB::table('lms_group_members')
            ->where('user_id', $user->id)
            ->pluck('lms_group_id');

        $filterGroupId = $request->integer('lms_group_id') ?: null;
        if ($filterGroupId !== null) {
            $belongs = LmsGroup::where('id', $filterGroupId)
                ->where('lms_event_id', $event->id)
                ->exists();
            if (! $belongs) {
                $filterGroupId = null;
            }
        }

        $videosQuery = LmsVideo::query()
            ->where('lms_event_id', $event->id)
            ->where('is_active', true);

        if ($filterGroupId !== null) {
            $videosQuery->whereHas('groups', fn ($q) => $q->where('lms_groups.id', $filterGroupId));
        } else {
            $videosQuery->where(function ($query) use ($groupIds) {
                $query->where('visible_to_all', true);
                if ($groupIds->isNotEmpty()) {
                    $query->orWhereHas('groups', fn ($gq) => $gq->whereIn('lms_groups.id', $groupIds));
                }
            });
        }

        if ($search = $request->get('search')) {
            $videosQuery->where('title', 'ilike', '%'.$search.'%');
        }

        $videos = $videosQuery->orderByDesc('created_at')->paginate(12)->withQueryString();

        $profile = LmsProfile::where('lms_event_id', $event->id)->where('user_id', $user->id)->first();

        $programFilterGroups = $event->groups()->orderBy('title')->get(['id', 'title']);

        return Inertia::render('Lms/Videos/Index', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'videos' => $videos,
            'programFilterGroups' => $programFilterGroups,
            'filters' => [
                'search' => $request->get('search'),
                'lms_group_id' => $filterGroupId,
            ],
        ]);
    }

    public function show(LmsEvent $event, LmsVideo $video): Response
    {
        if ($video->lms_event_id !== $event->id) {
            abort(404);
        }

        if (! $video->is_active) {
            abort(404);
        }

        $user = auth()->user();

        $profile = LmsProfile::where('lms_event_id', $event->id)->where('user_id', $user->id)->first();

        app(GamificationService::class)->awardPoints($event, $user, 'video_watch', "Видео: {$video->title}");

        return Inertia::render('Lms/Videos/Show', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'video' => $video->only(['id', 'title', 'description', 'source', 'url', 'file_path', 'thumbnail']),
        ]);
    }
}
