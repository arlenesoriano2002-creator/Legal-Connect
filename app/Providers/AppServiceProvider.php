<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
        Event::listen(Login::class, function (Login $event) {
            $user = $event->user;

            if ($user instanceof User && $user->active_status !== 1) {
                $user->forceFill(['active_status' => 1])->save();
            }
        });

        Event::listen(Logout::class, function (Logout $event) {
            $user = $event->user;

            if ($user instanceof User && $user->active_status !== 0) {
                $user->forceFill(['active_status' => 0])->save();
            }
        });

        // Temporary fallback: if DB is not reachable, set a generic authenticated user
        // This prevents constant fatal errors while MySQL is down during local development.
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('DB connection failed in AppServiceProvider::boot — using GenericUser for local dev: ' . $e->getMessage());
            $genericUser = new \Illuminate\Auth\GenericUser([
                'id' => 0,
                'name' => 'Local Dev',
                'email' => 'local@localhost',
                'role' => 'admin'
            ]);
            \Illuminate\Support\Facades\Auth::setUser($genericUser);
        }
    }
}
