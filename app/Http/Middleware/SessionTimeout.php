<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * SessionTimeout
 * 
 * Enforces session timeout based on inactivity.
 * Default timeout: 30 minutes
 * 
 * Validates on both protected routes and all authenticated requests.
 */
class SessionTimeout
{
    // Session timeout in minutes
    private const SESSION_TIMEOUT = 30;

    // Last activity key in session
    private const LAST_ACTIVITY_KEY = 'last_activity';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Only enforce timeout for authenticated users
        if (Auth::check()) {
            $lastActivity = $request->session()->get(self::LAST_ACTIVITY_KEY);
            $now = now()->timestamp;

            if ($lastActivity && ($now - $lastActivity) > (self::SESSION_TIMEOUT * 60)) {
                // Session has expired due to inactivity
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->withErrors(['Session expired due to inactivity. Please login again.']);
            }

            // Update last activity timestamp
            $request->session()->put(self::LAST_ACTIVITY_KEY, $now);
        }

        return $next($request);
    }
}
