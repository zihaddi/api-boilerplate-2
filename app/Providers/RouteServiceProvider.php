<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Add specific rate limit for contacts endpoint
        RateLimiter::for('contacts', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip())->response(function () {
                    return response()->json([
                        'message' => 'Too many attempts. Please try again later.',
                        'status' => 429
                    ], 429);
                }),
                Limit::perHour(20)->by($request->ip())
            ];
        });
    }
}
