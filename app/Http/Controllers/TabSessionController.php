<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TabSessionManager;
use Illuminate\Support\Facades\Auth;

class TabSessionController extends Controller
{
    /**
     * Logout the current tab only
     * This is called when a tab is about to close or user explicitly logs out from that tab
     */
    public function logoutTab(Request $request)
    {
        // Set user as offline before logout
        $user = Auth::user();
        if ($user) {
            $user->update(['active_status' => 0]);
        }
        
        $tabToken = $request->header('X-Tab-Token');

        if ($tabToken) {
            TabSessionManager::logoutTab($tabToken);
        }

        // Also logout the Laravel session
        Auth::logout();

        return response()->json(['success' => true, 'message' => 'Tab session ended']);
    }

    /**
     * Get current tab session info
     * Useful for frontend to confirm tab token is valid
     */
    public function getTabInfo(Request $request)
    {
        $tabToken = $request->header('X-Tab-Token');

        if (!$tabToken) {
            return response()->json(['success' => false, 'message' => 'No tab token found'], 401);
        }

        $session = TabSessionManager::getTabSession($tabToken);

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Tab session expired or invalid'], 401);
        }

        return response()->json([
            'success' => true,
            'tab_id' => $session['tab_id'],
            'user_id' => $session['user_id'],
            'expires_at' => $session['expires_at'],
        ]);
    }

    /**
     * Get all active tabs for the current user
     * Useful for debugging/monitoring
     */
    public function getActiveTabs(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $tabs = TabSessionManager::getUserActiveTabs(auth()->id());

        return response()->json(['success' => true, 'tabs' => $tabs]);
    }
}
