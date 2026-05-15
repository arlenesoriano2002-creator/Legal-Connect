<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Services\TabSessionManager;
use App\Models\User;

/**
 * Per-Tab Authentication Helper
 * 
 * Provides methods to get the authenticated user for the CURRENT TAB,
 * not the globally authenticated user from the shared session cookie.
 * 
 * This solves the multi-tab session collision issue where all tabs
 * would share the same Auth::user() even though they should be
 * handling different users.
 */
class PerTabAuthHelper
{
    /**
     * Get the authenticated user for the current tab
     * 
     * Uses database-stored tab session (via tab_token) for true per-tab isolation.
     * Falls back to Auth::user() if tab data is not available.
     * 
     * @return \App\Models\User|null The user authenticated on this specific tab
     */
    public static function getTabUser()
    {
        try {
            $request = request();
            $userId = null;
            
            // Strategy 1: Try to get user_id from per-tab session storage (Laravel session with tab key)
            $tabSessionData = Session::get('tab_session');
            if ($tabSessionData && !empty($tabSessionData['user_id'])) {
                $userId = $tabSessionData['user_id'];
                \Log::debug('PerTabAuthHelper: Got user_id from tab_session', ['user_id' => $userId]);
            }
            
            // Strategy 2: If no user_id in session, try to get from database via tab_token
            if (!$userId) {
                $tabToken = $request->header('X-Tab-Token');
                if ($tabToken) {
                    try {
                        // Query the database for this tab token's user
                        $tabSession = TabSessionManager::getTabSession($tabToken);
                        if ($tabSession) {
                            $userId = $tabSession['user_id'];
                            \Log::debug('PerTabAuthHelper: Got user_id from database via tab_token', ['user_id' => $userId]);
                        }
                    } catch (\Exception $e) {
                        \Log::debug('PerTabAuthHelper: Could not get tab session from database', ['error' => $e->getMessage()]);
                    }
                }
            }
            
            // If still no user_id, fall back to global Auth::user()
            if (!$userId) {
                \Log::debug('PerTabAuthHelper: No tab-specific user found, using Auth::user()');
                return Auth::user();
            }
            
            // Get the user from database
            $user = User::find($userId);
            
            if (!$user) {
                // User no longer exists, fall back to Auth::user()
                \Log::warning('PerTabAuthHelper: User not found in database', ['user_id' => $userId]);
                return Auth::user();
            }

            \Log::debug('PerTabAuthHelper: Returning per-tab user', ['user_id' => $user->id, 'name' => $user->name]);
            return $user;
        } catch (\Exception $e) {
            // If anything fails, fall back to Auth::user()
            \Log::warning('PerTabAuthHelper::getTabUser() error: ' . $e->getMessage());
            return Auth::user();
        }
    }

    /**
     * Check if the current global user matches the per-tab user
     * 
     * Returns false if the session has been hijacked (global user differs from tab user)
     * 
     * @return bool True if users match or no tab session exists, False if hijack detected
     */
    public static function isTabUserValid()
    {
        try {
            $tabSessionData = Session::get('tab_session');
            
            // No tab session data means we're in fallback mode
            if (!$tabSessionData || empty($tabSessionData['user_id'])) {
                return true;
            }

            $globalUser = Auth::user();
            $tabUserId = $tabSessionData['user_id'];

            // Both should exist and match
            if (!$globalUser) {
                return false;
            }

            return (int)$globalUser->id === (int)$tabUserId;
        } catch (\Exception $e) {
            \Log::warning('PerTabAuthHelper::isTabUserValid() error: ' . $e->getMessage());
            return true;
        }
    }

    /**
     * Store per-tab session data in Laravel session
     * 
     * Called by LoginController after successful login
     * 
     * @param  array  $tabSessionData Contains user_id, tab_token, tab_id, created_at, expires_at
     * @return void
     */
    public static function storeTabSession($tabSessionData)
    {
        try {
            Session::put('tab_session', [
                'user_id' => $tabSessionData['user_id'] ?? null,
                'tab_token' => $tabSessionData['tab_token'] ?? null,
                'tab_id' => $tabSessionData['tab_id'] ?? null,
                'created_at' => $tabSessionData['created_at'] ?? null,
                'expires_at' => $tabSessionData['expires_at'] ?? null,
            ]);
            
            Session::save();
        } catch (\Exception $e) {
            \Log::warning('PerTabAuthHelper::storeTabSession() error: ' . $e->getMessage());
        }
    }

    /**
     * Clear per-tab session data when logging out
     * 
     * @return void
     */
    public static function clearTabSession()
    {
        try {
            Session::forget('tab_session');
            Session::save();
        } catch (\Exception $e) {
            \Log::warning('PerTabAuthHelper::clearTabSession() error: ' . $e->getMessage());
        }
    }

    /**
     * Get the user ID for the current tab
     * 
     * @return int|null
     */
    public static function getTabUserId()
    {
        $user = self::getTabUser();
        return $user ? $user->id : null;
    }

    /**
     * Get the role for the current tab's user
     * 
     * @return string|null
     */
    public static function getTabUserRole()
    {
        $user = self::getTabUser();
        return $user ? $user->role : null;
    }
}
