<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TabSessionManager;
use Illuminate\Support\Facades\Auth;

/**
 * Enforce Single Active Session Per User
 * 
 * This middleware ensures that only one active session exists per user.
 * If a request comes from a tab that is not the most recent login,
 * the user will be logged out and redirected to login.
 */
class EnforceSingleSession
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
        $user = Auth::user();
        
        // Only check if user is authenticated
        if (!$user) {
            return $next($request);
        }
        
        // Get the tab token from the request header or session
        $tabToken = $request->header('X-Tab-Token') ?? \Illuminate\Support\Facades\Session::get('tab_session.tab_token');
        
        if (!$tabToken) {
            // No tab token - user might be using old login method
            // Allow them to continue for backward compatibility
            \Log::debug('No tab token found for user', ['user_id' => $user->id]);
            return $next($request);
        }
        
        try {
            // Check if this tab token is still valid
            $tabSession = TabSessionManager::getTabSession($tabToken);
            
            if (!$tabSession) {
                // Token not found or expired
                // This tab's session is no longer active (likely logged out by another tab)
                \Log::warning('Tab session not found - user logged out from another tab', [
                    'user_id' => $user->id,
                    'tab_token' => substr($tabToken, 0, 10) . '...'
                ]);
                
                // Log the user out
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Redirect to login with message
                return redirect()->route('login')->with('warning', 
                    'Your session was closed because you logged in on another device or tab.'
                );
            }
            
            // Verify the user ID in the token matches the authenticated user
            if ((int)$tabSession['user_id'] !== (int)$user->id) {
                // Different user - should not happen
                \Log::warning('Tab token user mismatch', [
                    'authenticated_user' => $user->id,
                    'token_user' => $tabSession['user_id']
                ]);
                
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')->with('error', 
                    'Session validation failed. Please log in again.'
                );
            }
            
            // Refresh the token to extend its expiration
            TabSessionManager::refreshTabToken($tabToken);
            
        } catch (\Exception $e) {
            \Log::warning('Error validating tab session', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            // On error, continue (fail-safe mode)
        }
        
        return $next($request);
    }
}
