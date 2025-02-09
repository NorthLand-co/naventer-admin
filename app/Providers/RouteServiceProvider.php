<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->configureRateLimiting();

        // other boot logic...
    }

    protected function configureRateLimiting()
    {
        RateLimiter::for('global', function (Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60);
        });

        RateLimiter::for('api', function (Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
        });

        // Additional custom rate limiters can be defined here
    }
}
