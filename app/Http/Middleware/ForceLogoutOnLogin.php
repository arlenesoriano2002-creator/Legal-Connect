<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ForceLogoutOnLogin
 * 
 * When a user accesses the /login route, immediately:
 * - Destroy the existing session
 * - Clear all authentication data
 * - Reset the application state
 * 
 * This ensures that accessing /login always provides a clean slate.
 */
class ForceLogoutOnLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the request is to the login route
        if ($request->is('login') || $request->path() === 'login') {
            // Log out the current user
            Auth::logout();

            // Invalidate the current session
            $request->session()->invalidate();

            // Regenerate CSRF token for security
            $request->session()->regenerateToken();

            // Clear all session data
            $request->session()->flush();

            // Force a new session to be created
            $request->session()->regenerate();
        }

        return $next($request);
    }
}
