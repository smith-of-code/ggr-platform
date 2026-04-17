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
use App\Support\MailDisplayName;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Yandex\Provider;

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

        RateLimiter::for('tour-cabinet-support-ticket', function (Request $request) {
            return Limit::perMinute(6)->by((string) ($request->user()?->id ?? $request->ip()));
        });

        RateLimiter::for('tour-cabinet-support-message', function (Request $request) {
            return Limit::perMinute(30)->by((string) ($request->user()?->id ?? $request->ip()));
        });

        RateLimiter::for('tour-cabinet-support-download', function (Request $request) {
            return Limit::perMinute(60)->by((string) ($request->user()?->id ?? $request->ip()));
        });

        app(SettingsService::class)->applyMailConfig();

        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $broker = config('auth.defaults.passwords');
            $expire = (int) config("auth.passwords.{$broker}.expire");

            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $fromName = MailDisplayName::resolve();

            return (new MailMessage)
                ->subject('Сброс пароля — '.$fromName)
                ->view('emails.reset-password', [
                    'resetUrl' => $url,
                    'expireMinutes' => $expire,
                    'mailFromName' => $fromName,
                ]);
        });

        // Участник LMS открывает /courses/{slug}; админка передаёт id.
        // Параметр event на момент bind может быть ещё строкой (slug из URI), а не LmsEvent.
        Route::bind('course', function (string $value, \Illuminate\Routing\Route $route): LmsCourse {
            $eventParam = $route->parameter('event');
            $event = $eventParam instanceof LmsEvent
                ? $eventParam
                : (is_string($eventParam) && $eventParam !== ''
                    ? LmsEvent::query()->where('slug', $eventParam)->first()
                    : null);

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

        LmsStageProgress::created(function (LmsStageProgress $model) use ($observer) {
            if ($model->status === 'completed') {
                $observer->maybeAwardModuleComplete($model);
            }
        });
        LmsStageProgress::updated(function (LmsStageProgress $model) use ($observer) {
            if ($model->status !== 'completed' || ! $model->wasChanged('status')) {
                return;
            }
            $observer->maybeAwardModuleComplete($model);
        });
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
            $event->extendSocialite('yandex', Provider::class);
        });
    }
}
