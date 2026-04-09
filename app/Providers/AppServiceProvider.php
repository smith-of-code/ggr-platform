<?php

namespace App\Providers;

use App\Models\Lms\LmsAssignmentReview;
use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsStageProgress;
use App\Models\Lms\LmsTestAttempt;
use App\Models\Lms\LmsTrajectoryEnrollment;
use App\Observers\LmsProgressObserver;
use App\Services\GamificationService;
use App\Services\SettingsService;
use App\Socialite\VkIdProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GamificationService::class);
        $this->app->singleton(SettingsService::class);
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        app(SettingsService::class)->applyMailConfig();

        // Участник LMS открывает /courses/{slug}; админка и API передают id.
        Route::bind('course', function (string $value, \Illuminate\Routing\Route $route): LmsCourse {
            $event = $route->parameter('event');

            if ($event instanceof LmsEvent) {
                $course = ctype_digit($value)
                    ? LmsCourse::query()
                        ->where('lms_event_id', $event->id)
                        ->where('id', (int) $value)
                        ->first()
                    : LmsCourse::query()
                        ->where('lms_event_id', $event->id)
                        ->where('slug', $value)
                        ->first();
            } else {
                if (! ctype_digit($value)) {
                    abort(404);
                }
                $course = LmsCourse::query()->where('id', (int) $value)->first();
            }

            abort_if($course === null, 404);

            return $course;
        });

        $observer = app(LmsProgressObserver::class);

        LmsStageProgress::updated(fn ($model) => $observer->stageCompleted($model));
        LmsCourseEnrollment::updated(fn ($model) => $observer->courseCompleted($model));
        LmsTestAttempt::updated(fn ($model) => $observer->testPassed($model));
        LmsAssignmentReview::created(fn ($model) => $observer->assignmentApproved($model));
        LmsTrajectoryEnrollment::updated(fn ($model) => $observer->trajectoryCompleted($model));

        Socialite::extend('vkontakte', function ($app) {
            $config = $app['config']['services.vkontakte'];

            return new VkIdProvider(
                $app['request'],
                $config['client_id'],
                $config['client_secret'] ?? '',
                $config['redirect'],
            );
        });

        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('yandex', \SocialiteProviders\Yandex\Provider::class);
        });
    }
}
