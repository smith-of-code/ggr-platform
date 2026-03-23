<?php

namespace App\Providers;

use App\Models\Lms\LmsAssignmentReview;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsStageProgress;
use App\Models\Lms\LmsTestAttempt;
use App\Models\Lms\LmsTrajectoryEnrollment;
use App\Observers\LmsProgressObserver;
use App\Services\GamificationService;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
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

        $observer = app(LmsProgressObserver::class);

        LmsStageProgress::updated(fn ($model) => $observer->stageCompleted($model));
        LmsCourseEnrollment::updated(fn ($model) => $observer->courseCompleted($model));
        LmsTestAttempt::created(fn ($model) => $observer->testPassed($model));
        LmsAssignmentReview::created(fn ($model) => $observer->assignmentApproved($model));
        LmsTrajectoryEnrollment::updated(fn ($model) => $observer->trajectoryCompleted($model));

        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('vkontakte', \SocialiteProviders\VKontakte\Provider::class);
            $event->extendSocialite('yandex', \SocialiteProviders\Yandex\Provider::class);
        });
    }
}
