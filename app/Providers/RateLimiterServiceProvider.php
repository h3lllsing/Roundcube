<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimiterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(config('ratelimits.api', 60))->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(config('ratelimits.search', 20))->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('export', function (Request $request) {
            return Limit::perMinute(config('ratelimits.export', 5))->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('bulk', function (Request $request) {
            return Limit::perMinute(config('ratelimits.bulk', 10))->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('import', function (Request $request) {
            return Limit::perMinute(config('ratelimits.import', 5))->by($request->user()?->id ?: $request->ip());
        });
    }
}
