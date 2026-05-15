<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\TabSessionManager;

class TabAuthHelper
{
    /**
     * Check if the current request is authenticated via tab token or session
     * Implements per-tab isolation while allowing initial page loads after login
     *
     * Logic:
     * 1. First check if user is authenticated in Laravel session
     * 2. For subsequent requests with X-Tab-ID header: verify it matches the session tab_id
     * 3. For initial page loads (no X-Tab-ID header): trust the session if tab_session exists
     * 4. For new tabs (X-Tab-ID exists but doesn't match session): show guest UI
     *
     * @param Request $request
     * @return bool
     */
    public static function isTabAuthenticated(Request $request): bool
    {
        // First check: User must be logged in via Laravel session
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        
        // Check if we have valid session data from login
        $hasSessionData = $request->session()->has('tab_session');
        if (!$hasSessionData) {
            // No tab session data - user is not authenticated for this tab
            \Log::debug('No tab session data in Laravel session', [
                'user_id' => $user->id
            ]);
            return false;
        }

        $sessionTabId = $request->session()->get('tab_session.tab_id');
        $sessionTabToken = $request->session()->get('tab_session.tab_token');
        
        // Get headers from request
        $headerTabToken = $request->header('X-Tab-Token');
        $headerTabId = $request->header('X-Tab-ID');

        // CASE 1: Request has X-Tab-Token header (subsequent request after JS initialization)
        if ($headerTabToken) {
            try {
                $session = TabSessionManager::getTabSession($headerTabToken);
                if ($session && $session['user_id'] === $user->id) {
                    TabSessionManager::refreshTabToken($headerTabToken);
                    \Log::debug('Tab authenticated via token header', [
                        'user_id' => $user->id
                    ]);
                    return true;
                }
            } catch (\Exception $e) {
                \Log::debug('Tab token validation failed', ['error' => $e->getMessage()]);
            }
        }

        // CASE 2: Request has X-Tab-ID header (JavaScript is running)
        // This determines if this is the SAME tab that authenticated or a DIFFERENT tab
        if ($headerTabId && $sessionTabId) {
            if ($headerTabId === $sessionTabId) {
                // Tab IDs match - this is the authenticated tab
                \Log::debug('Tab authenticated via matching tab ID', [
                    'user_id' => $user->id,
                    'tab_id' => $headerTabId
                ]);
                return true;
            } else {
                // Tab IDs don't match - this is a different tab trying to use another's session
                \Log::debug('Tab ID mismatch - different tab, showing guest UI', [
                    'request_tab_id' => $headerTabId,
                    'session_tab_id' => $sessionTabId
                ]);
                return false;
            }
        }

        // CASE 3: No X-Tab-ID header (initial page load, JavaScript hasn't sent headers yet)
        // If Laravel user auth + tab session exist, allow authenticated view (persistent across pages)
        if (!$headerTabId && $sessionTabId && $sessionTabToken) {
            \Log::debug('Initial page load with existing tab session, allowing authenticated UI', [
                'user_id' => $user->id,
                'tab_id' => $sessionTabId
            ]);
            return true;
        }

        // CASE 4: All other scenarios - not authenticated
        \Log::debug('Tab not authenticated - no matching credentials', [
            'user_id' => $user->id,
            'has_session_data' => $hasSessionData,
            'has_header_token' => (bool) $headerTabToken,
            'has_header_tab_id' => (bool) $headerTabId
        ]);
        return false;
    }

    /**
     * Check if the current tab is a guest (no valid tab authentication)
     *
     * @param Request $request
     * @return bool
     */
    public static function isTabGuest(Request $request): bool
    {
        return !self::isTabAuthenticated($request);
    }

    /**
     * Get the current tab's ID from the request
     *
     * @param Request $request
     * @return string|null
     */
    public static function getCurrentTabId(Request $request): ?string
    {
        return $request->session()->get('tab_session.tab_id') ?? $request->header('X-Tab-ID');
    }

    /**
     * Get the current tab's token from the request
     *
     * @param Request $request
     * @return string|null
     */
    public static function getCurrentTabToken(Request $request): ?string
    {
        return $request->session()->get('tab_session.tab_token') ?? $request->header('X-Tab-Token');
    }

    /**
     * Store tab session data in the session
     * Called after successful login to persist tab session
     *
     * @param Request $request
     * @param array $tabSessionData
     * @return void
     */
    public static function storeTabSession(Request $request, array $tabSessionData): void
    {
        $request->session()->put('tab_session', $tabSessionData);
    }

    /**
     * Clear the tab session data from the session
     * Called on logout
     *
     * @param Request $request
     * @return void
     */
    public static function clearTabSession(Request $request): void
    {
        $request->session()->forget('tab_session');
    }
}
