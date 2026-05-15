<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientRole
{
    /**
     * Dashboard redirect mapping for non-client roles
     */
    private const ROLE_DASHBOARDS = [
        'admin' => '/admindashboard',
        'superadmin' => '/admindashboard',
        'diffun_staff' => '/dashboardStaff',
        'cordon_staff' => '/CordonDashboard',
        'cordon_log' => '/CordonLogbookDashboard',
        'diffun_log' => '/DiffunLogbookDashboard',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            // Not authenticated — redirect to login
            return redirect()->route('login')->with('warning', 'Please login to access that page.');
        }

        $user = Auth::user();
        $role = $user->role ?? null;

        if ($role !== 'client') {
            // If you prefer a 403 response instead of redirect, change this behavior.
            $dashboard = self::ROLE_DASHBOARDS[$role] ?? '/';

            // Return 403 for log roles that shouldn't be redirected (optional)
            // return response()->view('errors.403', ['userRole' => $role], 403);

            return redirect($dashboard)->with('warning', 'Unauthorized: only client users may access that page.');
        }

        $response = $next($request);

        // Add some basic security headers
        $response->header('X-Frame-Options', 'SAMEORIGIN');
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}
