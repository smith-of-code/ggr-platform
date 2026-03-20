<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
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

        $videosQuery = LmsVideo::where('lms_event_id', $event->id)
            ->where('is_active', true)
            ->where(function ($query) use ($groupIds) {
                $query->whereDoesntHave('groups');
                if ($groupIds->isNotEmpty()) {
                    $query->orWhereHas('groups', fn($q) => $q->whereIn('lms_groups.id', $groupIds));
                }
            });

        if ($search = $request->get('search')) {
            $videosQuery->where('title', 'ilike', '%' . $search . '%');
        }

        $videos = $videosQuery->paginate(12)->withQueryString();

        $profile = LmsProfile::where('lms_event_id', $event->id)->where('user_id', $user->id)->first();

        return Inertia::render('Lms/Videos/Index', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'videos' => $videos,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(LmsEvent $event, LmsVideo $video): Response
    {
        if ($video->lms_event_id !== $event->id) {
            abort(404);
        }
        $user = auth()->user();
        $groupIds = DB::table('lms_group_members')
            ->where('user_id', $user->id)
            ->pluck('lms_group_id');

        $hasAccess = $video->groups->isEmpty()
            || $video->groups()->whereIn('lms_groups.id', $groupIds)->exists();

        if (!$hasAccess) {
            abort(403);
        }

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
