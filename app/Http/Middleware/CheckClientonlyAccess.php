<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckClientonlyAccess
{
    /**
     * List of ALLOWED roles that CAN access this middleware check
     * (used for root route protection only)
     */
    private const ALLOWED_ROLES = [
        'client',
        'secretary',
        'clerk',
    ];

    /**
     * List of restricted roles that cannot access the client-only route
     */
    private const RESTRICTED_ROLES = [
        'admin',
        'superadmin',
        'diffun_staff',
        'cordon_staff',
        'cordon_log',
        'diffun_log',
    ];

    /**
     * Dashboard redirect mapping for restricted roles
     * Change or remove mappings as needed
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
     * Set to true to return 403 Forbidden instead of redirecting
     * Set to false to redirect to respective dashboards
     */
    private const RETURN_403_ERROR = false;

    /**
     * Handle an incoming request.
     *
     * Enforces security for client-only routes (root route).
     * Allows: client, secretary, clerk roles
     * Blocks: admin, superadmin, diffun_staff, cordon_staff, cordon_log, diffun_log
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow unauthenticated users to see the welcome page
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $userRole = $user->role;

        // ALLOWED ROLES: client, secretary, clerk - they can access
        if (in_array($userRole, self::ALLOWED_ROLES)) {
            $response = $next($request);
            
            // Add security headers
            $response->header('X-Frame-Options', 'SAMEORIGIN');
            $response->header('X-Content-Type-Options', 'nosniff');
            $response->header('X-XSS-Protection', '1; mode=block');
            $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            
            return $response;
        }

        // RESTRICTED ROLES: admin, superadmin, diffun_staff, cordon staff, etc.
        if (in_array($userRole, self::RESTRICTED_ROLES)) {
            if (self::RETURN_403_ERROR) {
                // Return 403 Forbidden response
                return response()->view('errors.403', [
                    'message' => 'Unauthorized: This page is restricted to client users only.',
                    'userRole' => $userRole,
                ], 403);
            } else {
                // Redirect to their respective dashboard with a flash message
                $dashboardRoute = self::ROLE_DASHBOARDS[$userRole] ?? '/';
                
                return redirect($dashboardRoute)
                    ->with('warning', 
                        'Access Denied: You do not have permission to access this page. ' .
                        'Only users with the client role can access this route. ' .
                        'You have been redirected to your dashboard.'
                    );
            }
        }

        // Unknown role - allow to proceed but log it
        \Log::warning('Unknown user role in CheckClientonlyAccess middleware', [
            'userId' => $user->id,
            'userRole' => $userRole,
            'path' => $request->path(),
        ]);

        $response = $next($request);

        // Add security headers
        $response->header('X-Frame-Options', 'SAMEORIGIN');
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-XSS-Protection', '1; mode=block');
        $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        return $response;
    }
}

