<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * CacheControlMiddleware
 * 
 * Applies cache-control headers to prevent browser caching of protected pages.
 * This ensures that authenticated pages cannot be accessed via browser cache after logout.
 */
class CacheControlMiddleware
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
        $response = $next($request);

        // Apply no-cache headers to all responses
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', '0');

        return $response;
    }
}
