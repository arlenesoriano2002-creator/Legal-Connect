<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect('/login');
        }
        
        // Check if user has admin or superadmin role
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            abort(403, 'Unauthorized access.');
        }
        
        return $next($request);
    }
}