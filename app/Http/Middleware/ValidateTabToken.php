<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\TabSessionManager;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware to validate per-tab session tokens
 * If a tab token is present, use it to authenticate the user
 * Otherwise, fall back to the default Laravel session authentication
 */
class ValidateTabToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for per-tab token in request header
        $tabToken = $request->header('X-Tab-Token');

        if ($tabToken) {
            // Validate the per-tab token
            $user = TabSessionManager::validateTabToken($tabToken);

            if ($user) {
                // Authenticate the user using the tab token
                Auth::setUser($user);

                // Refresh the token to extend its lifetime
                TabSessionManager::refreshTabToken($tabToken);

                // Store the tab token in the request for potential use in controllers
                $request->attributes->set('tab_token', $tabToken);
            } else {
                // Invalid or expired tab token - reject the request
                return response()->json(['message' => 'Invalid or expired tab token'], 401);
            }
        }

        return $next($request);
    }
}
