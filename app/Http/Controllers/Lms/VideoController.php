<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsVideo;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller
{
    public function index(Request $request, LmsEvent $event): Response
    {
        $user = auth()->user();

        $enrolledCourseIds = LmsCourseEnrollment::query()
            ->where('user_id', $user->id)
            ->whereHas('course', fn ($q) => $q->where('lms_event_id', $event->id))
            ->pluck('lms_course_id');

        $filterCourseId = $request->integer('lms_course_id') ?: null;
        if ($filterCourseId !== null) {
            $belongs = LmsCourse::where('id', $filterCourseId)
                ->where('lms_event_id', $event->id)
                ->exists();
            if (! $belongs) {
                $filterCourseId = null;
            }
        }

        $videosQuery = LmsVideo::query()
            ->where('lms_event_id', $event->id)
            ->where('is_active', true);

        if ($filterCourseId !== null) {
            $videosQuery->whereHas('courses', fn ($q) => $q->where('lms_courses.id', $filterCourseId));
        } else {
            $videosQuery->where(function ($query) use ($enrolledCourseIds) {
                $query->where('visible_to_all', true);
                if ($enrolledCourseIds->isNotEmpty()) {
                    $query->orWhereHas('courses', fn ($cq) => $cq->whereIn('lms_courses.id', $enrolledCourseIds));
                }
            });
        }

        if ($search = $request->get('search')) {
            $videosQuery->where('title', 'ilike', '%'.$search.'%');
        }

        $videos = $videosQuery->orderByDesc('created_at')->paginate(12)->withQueryString();

        $profile = LmsProfile::where('lms_event_id', $event->id)->where('user_id', $user->id)->first();

        $programFilterCourses = $event->courses()->orderBy('title')->get(['id', 'title']);

        return Inertia::render('Lms/Videos/Index', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'user' => $user->only(['id', 'name', 'email']),
            'profile' => $profile,
            'videos' => $videos,
            'programFilterCourses' => $programFilterCourses,
            'filters' => [
                'search' => $request->get('search'),
                'lms_course_id' => $filterCourseId,
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
