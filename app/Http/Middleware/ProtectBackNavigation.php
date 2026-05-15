<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ProtectBackNavigation
 * 
 * Prevents access to protected routes via browser back button or history manipulation.
 * 
 * If user attempts to access a protected route without valid authentication,
 * they are redirected to /welcome (or /login).
 */
class ProtectBackNavigation
{
    /**
     * Protected routes that require authentication
     */
    protected $protectedRoutes = [
        'admindashboard',
        'dashboardStaff',
        'cordon/dashboard',
        'administrator',
        'appointments',
        'practice-areas',
        'clientstbl',
        'adminAcceptedRequest',
        'adminDeniedRequest',
        'adminAccount',
        'staff',
        'cordon',
        'walkin'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if current route is a protected route
        if ($this->isProtectedRoute($request)) {
            // If user is not authenticated, redirect to welcome
            if (!Auth::check()) {
                return redirect('/welcome')->with('error', 'Please login to access this page');
            }
        }

        return $next($request);
    }

    /**
     * Check if the current route is a protected route
     *
     * @param  Request  $request
     * @return bool
     */
    protected function isProtectedRoute(Request $request)
    {
        $path = $request->path();

        foreach ($this->protectedRoutes as $route) {
            if ($path === $route || \Str::startsWith($path, $route . '/')) {
                return true;
            }
        }

        return false;
    }
}
