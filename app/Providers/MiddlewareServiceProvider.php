<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SessionManagementService;

/**
 * MiddlewareServiceProvider
 * 
 * Service provider for registering middleware and session management services.
 * Centralizes all middleware and service configuration.
 */
class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register SessionManagementService as a singleton
        $this->app->singleton(SessionManagementService::class, function ($app) {
            return new SessionManagementService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // All middleware registration is now handled in bootstrap/app.php
        // This provider is kept for future extensibility
    }
}
