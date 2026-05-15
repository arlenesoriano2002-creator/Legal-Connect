<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckSessionInactivity Middleware
 * 
 * Validates user session based on inactivity timeout.
 * If user is inactive for longer than configured timeout, 
 * automatically logs them out and redirects to login.
 * 
 * Role-based timeouts:
 * - Admin/Superadmin: 15 minutes
 * - Secretary/Clerk: 60 minutes
 * - Client: 30 minutes
 * - Default: 30 minutes
 */
class CheckSessionInactivity
{
    // Session timeout in minutes (role-based)
    private const ROLE_TIMEOUTS = [
        'admin' => 15,
        'superadmin' => 15,
        'secretary' => 60,
        'clerk' => 60,
        'client' => 30,
        'diffun_staff' => 60,
        'cordon_staff' => 60,
        'default' => 30,
    ];
    
    private const WARNING_MINUTES = 2;
    private const LAST_ACTIVITY_KEY = 'last_activity_timestamp';

    /**
     * Get timeout in minutes based on user role
     */
    private function getTimeoutMinutes(Request $request): int
    {
        if (!Auth::check()) {
            return self::ROLE_TIMEOUTS['default'];
        }
        
        $userRole = Auth::user()->role ?? 'default';
        return self::ROLE_TIMEOUTS[$userRole] ?? self::ROLE_TIMEOUTS['default'];
    }

    /**
     * Check if request should reset activity timer (AJAX/API calls count as activity)
     */
    private function shouldResetActivity(Request $request): bool
    {
        // AJAX requests reset activity timer
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return true;
        }
        
        // API calls reset activity timer
        if (str_starts_with($request->path(), 'api/')) {
            return true;
        }
        
        // Skip logout and notification endpoints
        if ($request->routeIs('logout', 'custom.logout', 'tab.logout', 'admin.notifications', 'diffun-staff.notifications')) {
            return true;
        }
        
        return false;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip inactivity check for logout route itself
        if ($request->routeIs('logout', 'custom.logout', 'tab.logout')) {
            return $next($request);
        }

        if (Auth::check()) {
            $lastActivity = Session::get(self::LAST_ACTIVITY_KEY);
            $currentTime = time();
            $timeoutMinutes = $this->getTimeoutMinutes($request);
            $timeoutSeconds = $timeoutMinutes * 60;
            $warningSeconds = ($timeoutMinutes - self::WARNING_MINUTES) * 60;

            // First request: Set initial activity timestamp
            if (!$lastActivity) {
                Session::put(self::LAST_ACTIVITY_KEY, $currentTime);
                return $next($request);
            }

            $inactiveSeconds = $currentTime - $lastActivity;

            // Session has expired - log out immediately
            if ($inactiveSeconds > $timeoutSeconds) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                \Log::info('User auto-logged out due to inactivity', [
                    'user_id' => Auth::id() ?? 'unknown',
                    'role' => Auth::user()->role ?? 'unknown',
                    'inactive_minutes' => round($inactiveSeconds / 60, 2),
                    'timeout_minutes' => $timeoutMinutes
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your session has expired due to inactivity. Please login again.',
                        'session_expired' => true
                    ], 401);
                }

                return redirect('/login')->with('warning', 'Your session expired due to inactivity. Please login again.');
            }

            // Session warning: Show popup after inactivity but before timeout
            if ($inactiveSeconds >= $warningSeconds) {
                Session::put('session_warning_shown', true);
            } else {
                // User is active - update last activity timestamp
                // Reset activity for standard page loads and AJAX requests
                if ($this->shouldResetActivity($request)) {
                    Session::put(self::LAST_ACTIVITY_KEY, $currentTime);
                }
            }
        }

        return $next($request);
    }
}
