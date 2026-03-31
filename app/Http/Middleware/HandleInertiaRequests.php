<?php

namespace App\Http\Middleware;

use App\Models\Lms\LmsProfile;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'csrf' => csrf_token(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'subscribed' => fn () => $request->session()->get('subscribed'),
                'profile_completed' => fn () => $request->session()->get('profile_completed'),
            ],
            'user' => fn () => $request->user()?->only(['id', 'name', 'email', 'phone']),
            'profile' => function () use ($request) {
                $user = $request->user();
                if (!$user) return null;

                $eventSlug = $request->route('event');
                if (!$eventSlug) return null;

                $eventId = is_object($eventSlug)
                    ? $eventSlug->id
                    : \App\Models\Lms\LmsEvent::where('slug', $eventSlug)->value('id');

                if (!$eventId) return null;

                return LmsProfile::where('user_id', $user->id)
                    ->where('lms_event_id', $eventId)
                    ->with('lmsRole:id,name,slug')
                    ->first();
            },
            'gamificationEnabled' => function () use ($request) {
                $user = $request->user();
                if (!$user) return false;

                $eventSlug = $request->route('event');
                if (!$eventSlug) return false;

                $event = is_object($eventSlug)
                    ? $eventSlug
                    : \App\Models\Lms\LmsEvent::where('slug', $eventSlug)->first();

                if (!$event) return false;

                return app(GamificationService::class)->isGamificationEnabled($event, $user);
            },
        ];
    }
}
