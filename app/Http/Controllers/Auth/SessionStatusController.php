<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * SessionStatusController
 * 
 * Handles session status checks and inactivity monitoring.
 * Provides endpoints for frontend to:
 * - Check if session is still valid
 * - Get remaining session time
 * - Refresh activity timestamp
 * - Handle session warnings
 */
class SessionStatusController extends Controller
{
    private const SESSION_TIMEOUT_MINUTES = 15;
    private const WARNING_MINUTES = 2;
    private const LAST_ACTIVITY_KEY = 'last_activity_timestamp';

    /**
     * Check current session status
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'authenticated' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $lastActivity = Session::get(self::LAST_ACTIVITY_KEY);
        $currentTime = time();
        $timeoutSeconds = self::SESSION_TIMEOUT_MINUTES * 60;

        if (!$lastActivity) {
            $inactiveSeconds = 0;
            $remainingSeconds = $timeoutSeconds;
        } else {
            $inactiveSeconds = $currentTime - $lastActivity;
            $remainingSeconds = max(0, $timeoutSeconds - $inactiveSeconds);
        }

        $isExpiring = $inactiveSeconds >= (($this::SESSION_TIMEOUT_MINUTES - $this::WARNING_MINUTES) * 60);

        return response()->json([
            'authenticated' => true,
            'user_id' => Auth::id(),
            'user_email' => Auth::user()->email,
            'user_role' => Auth::user()->role ?? null,
            'inactive_seconds' => $inactiveSeconds,
            'remaining_seconds' => $remainingSeconds,
            'timeout_minutes' => self::SESSION_TIMEOUT_MINUTES,
            'warning_minutes' => self::WARNING_MINUTES,
            'is_expiring' => $isExpiring,
            'last_activity' => $lastActivity
        ]);
    }

    /**
     * Refresh session activity timestamp
     * Called when user performs an action
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshActivity(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false], 401);
        }

        Session::put(self::LAST_ACTIVITY_KEY, time());
        
        \Log::debug('Session activity refreshed', [
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session activity refreshed'
        ]);
    }

    /**
     * Get remaining session time in seconds
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRemainingTime(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['remaining_seconds' => 0], 401);
        }

        $lastActivity = Session::get(self::LAST_ACTIVITY_KEY);
        $currentTime = time();
        $timeoutSeconds = self::SESSION_TIMEOUT_MINUTES * 60;

        if (!$lastActivity) {
            $remainingSeconds = $timeoutSeconds;
        } else {
            $inactiveSeconds = $currentTime - $lastActivity;
            $remainingSeconds = max(0, $timeoutSeconds - $inactiveSeconds);
        }

        return response()->json([
            'remaining_seconds' => $remainingSeconds,
            'remaining_minutes' => round($remainingSeconds / 60, 2)
        ]);
    }
}
