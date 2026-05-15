<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->group(base_path('routes/debug.php'));

            Route::middleware('web')
                ->group(base_path('routes/test_routes.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Map the application's API routes.
     *
     * @return void
     */
    public function mapApiRoutes()
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));
    }

    /**
     * Map the application's Web routes.
     *
     * @return void
     */
    public function mapWebRoutes()
    {
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }

    /**
     * Include test routes for debugging
     */
    public function includeTestRoutes()
    {
        require base_path('routes/test_routes.php');
    }
}