<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * SessionManagementService
 * 
 * Centralized service for managing user sessions, timeouts, and authentication state.
 * Provides helper methods for session operations throughout the application.
 */
class SessionManagementService
{
    // Session timeout in minutes
    private const SESSION_TIMEOUT = 30;

    // Session warning time before expiration (in minutes)
    private const SESSION_WARNING_TIME = 5;

    // Last activity key in session
    private const LAST_ACTIVITY_KEY = 'last_activity';

    // Session created time key
    private const SESSION_CREATED_KEY = 'session_created';

    /**
     * Initialize session tracking for new login
     *
     * @return void
     */
    public static function initializeSession()
    {
        Session::put(self::LAST_ACTIVITY_KEY, now()->timestamp);
        Session::put(self::SESSION_CREATED_KEY, now()->timestamp);
    }

    /**
     * Update last activity timestamp
     *
     * @return void
     */
    public static function updateLastActivity()
    {
        Session::put(self::LAST_ACTIVITY_KEY, now()->timestamp);
    }

    /**
     * Check if session is still valid based on inactivity timeout
     *
     * @return bool
     */
    public static function isSessionValid()
    {
        if (!Auth::check()) {
            return false;
        }

        $lastActivity = Session::get(self::LAST_ACTIVITY_KEY);

        if (!$lastActivity) {
            return false;
        }

        $inactiveSeconds = now()->timestamp - $lastActivity;
        $timeoutSeconds = self::SESSION_TIMEOUT * 60;

        return $inactiveSeconds < $timeoutSeconds;
    }

    /**
     * Check if session is about to expire (within warning time)
     *
     * @return bool
     */
    public static function isSessionExpiring()
    {
        if (!Auth::check()) {
            return false;
        }

        $lastActivity = Session::get(self::LAST_ACTIVITY_KEY);

        if (!$lastActivity) {
            return false;
        }

        $inactiveSeconds = now()->timestamp - $lastActivity;
        $warningSeconds = (self::SESSION_TIMEOUT - self::SESSION_WARNING_TIME) * 60;

        return $inactiveSeconds >= $warningSeconds && $inactiveSeconds < (self::SESSION_TIMEOUT * 60);
    }

    /**
     * Get remaining session time in seconds
     *
     * @return int
     */
    public static function getRemainingSessionTime()
    {
        $lastActivity = Session::get(self::LAST_ACTIVITY_KEY);

        if (!$lastActivity) {
            return 0;
        }

        $inactiveSeconds = now()->timestamp - $lastActivity;
        $timeoutSeconds = self::SESSION_TIMEOUT * 60;
        $remaining = $timeoutSeconds - $inactiveSeconds;

        return max(0, $remaining);
    }

    /**
     * Forcefully destroy session
     *
     * @return void
     */
    public static function destroySession()
    {
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();
        Session::flush();
    }

    /**
     * Clear all authentication data
     *
     * @return void
     */
    public static function clearAuthenticationData()
    {
        Session::forget([
            self::LAST_ACTIVITY_KEY,
            self::SESSION_CREATED_KEY,
            'auth_token',
            'user_id',
            'user_role',
            'tab_session'
        ]);
    }

    /**
     * Get session timeout in minutes
     *
     * @return int
     */
    public static function getSessionTimeout()
    {
        return self::SESSION_TIMEOUT;
    }

    /**
     * Get session warning time in minutes
     *
     * @return int
     */
    public static function getSessionWarningTime()
    {
        return self::SESSION_WARNING_TIME;
    }
}
