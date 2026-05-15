<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthorizeRole
{
    /**
     * Handle an incoming request.
     * 
     * Checks if the authenticated user has one of the required roles.
     * If not, either redirects to their proper dashboard or returns 403.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $role - Role(s) to check (comma-separated for multiple: 'admin,superadmin')
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // DEBUG: Log the raw role parameters
        \Log::warning('AuthorizeRole parameters', [
            'roles_param' => $roles,
            'roles_count' => count($roles),
            'roles_imploded' => implode(',', $roles),
            'path' => $request->path()
        ]);

        // Must be authenticated first
        if (!auth()->check()) {
            \Log::debug('AuthorizeRole: Not authenticated');
            return redirect()->route('login');
        }

        $user = auth()->user();
        $allowedRoles = array_map('trim', $roles);

        \Log::debug('AuthorizeRole check', [
            'path' => $request->path(),
            'user_id' => $user->id,
            'user_role' => $user->role,
            'allowed_roles' => $allowedRoles,
            'role_match' => in_array($user->role, $allowedRoles)
        ]);

        // Check if user has one of the required roles
        if (in_array($user->role, $allowedRoles)) {
            \Log::debug('AuthorizeRole: Authorized');
            return $next($request);
        }

        // User lacks required role - redirect to appropriate dashboard based on actual role
        \Log::warning('AuthorizeRole: Unauthorized', [
            'user_role' => $user->role,
            'allowed' => $allowedRoles,
            'path' => $request->path()
        ]);
        return $this->redirectToDashboard($user);
    }

    /**
     * Redirect user to their appropriate dashboard based on their role
     * 
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectToDashboard($user)
    {
        // Map roles to their dashboard routes (use existing, valid route names)
    $dashboards = [
    'admin' => 'admindashboard',
    'superadmin' => 'admindashboard',
    'lawyer' => 'admindashboard',

    // ✅ USE ONLY THIS
    'staff' => 'dashboardStaff',
    'secretary' => 'dashboardStaff',

    'client' => 'welcome',
    ];


        $routeName = $dashboards[$user->role] ?? 'welcome';

        return redirect()
            ->route($routeName)
            ->with('error', 'You do not have permission to access that page. You have been redirected to your dashboard.');
    }
}
