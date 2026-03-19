<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;
use Laravel\Sentinel\Sentinel;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    public function boot(): void
    {
        $this->registerSentinelDriver();

        parent::boot();
    }

    /**
     * Allow Sentinel to pass in local environment behind Docker reverse proxy.
     * Without this, trustProxies(at: '*') triggers Sentinel's proxy protection.
     */
    private function registerSentinelDriver(): void
    {
        Sentinel::extend('horizon', function () {
            return new class(fn () => app()) extends \Laravel\Sentinel\Drivers\Driver {
                public function authorize(\Illuminate\Http\Request $request): bool
                {
                    return true;
                }
            };
        });
    }

    protected function authorization(): void
    {
        $this->gate();

        Horizon::auth(function ($request) {
            if (app()->environment('local')) {
                return true;
            }

            return Gate::check('viewHorizon', [$request->user()]);
        });
    }

    /**
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null) {
            if (app()->environment('local')) {
                return true;
            }

            return $user && $user->email && in_array($user->email, [
                    "admin@rosatom-travel.ru"
            ]);
        });
    }
}
