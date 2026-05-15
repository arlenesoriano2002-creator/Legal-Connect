<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TabSessionManager;

class ValidateTabSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Only validate if user is authenticated
        if (!auth()->check()) {
            \Log::debug('ValidateTabSession: No auth, continuing');
            return $next($request);
        }

        $user = auth()->user();
        \Log::debug('ValidateTabSession check', [
            'path' => $request->path(),
            'user_id' => $user->id,
            'user_role' => $user->role,
            'has_tab_token_header' => !!$request->header('X-Tab-Token'),
            'has_session_tab' => !!$request->session()->get('tab_session'),
        ]);

        $tabToken = $request->header('X-Tab-Token');

        // If no tab token in header, allow authenticated users to continue
        // This handles legacy sessions or cases where tab token is not set
        if (!$tabToken) {
            \Log::debug('ValidateTabSession: No X-Tab-Token header, allowing authenticated user to continue');
            return $next($request);
        }

        // Validate the tab token
        $tabSession = TabSessionManager::getTabSession($tabToken);

        if (!$tabSession) {
            \Log::warning('ValidateTabSession: Invalid tab token', ['tabToken' => substr($tabToken, 0, 10)]);
            // Tab token is invalid or expired
            auth()->logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your session has expired. Please log in again.',
                    'code' => 'SESSION_INVALID'
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Your session has expired. Please log in again.');
        }

        // Verify that the tab token's user_id matches the authenticated user
        if ((int)$tabSession['user_id'] !== (int)auth()->id()) {
            \Log::warning('ValidateTabSession: User mismatch', [
                'token_user' => $tabSession['user_id'],
                'auth_user' => auth()->id()
            ]);
            // Another user logged in on this tab - immediate logout
            auth()->logout();

            // Delete the mismatched tab session
            TabSessionManager::logoutTab($tabToken);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Another account was logged in on this tab. Please log in again.',
                    'code' => 'SESSION_HIJACKED'
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Another account was logged in on this tab. Please log in again.');
        }

        \Log::debug('ValidateTabSession: Valid token, continuing', ['user_id' => $user->id]);
        // Refresh the tab token expiration on each request
        TabSessionManager::refreshTabToken($tabToken);

        return $next($request);
    }
}
