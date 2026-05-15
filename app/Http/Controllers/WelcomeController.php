<?php

namespace App\Http\Controllers;

use App\Models\CaseCategory;
use App\Helpers\TabAuthHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class WelcomeController extends Controller
{
    /**
     * Show the welcome page with categories
     * Accessible to everyone (guests, clients, and non-client users)
     * The navbar and UI elements adjust based on user role and tab session state
     */
    public function index(Request $request)
    {
        try {
            // Get distinct categories for the footer with caching
            $categories = Cache::remember('case_categories', 3600, function () {
                return CaseCategory::select('category')
                    ->distinct()
                    ->orderBy('category')
                    ->get()
                    ->pluck('category');
            });
        } catch (\Exception $e) {
            // Fallback if categories table doesn't exist
            $categories = collect();
        }
        
        // Check if current tab is authenticated
        $isTabAuthenticated = false;
        try {
            $isTabAuthenticated = TabAuthHelper::isTabAuthenticated($request);
        } catch (\Exception $e) {
            // If authentication check fails, treat as guest
            \Log::debug('Tab authentication check failed', ['error' => $e->getMessage()]);
            $isTabAuthenticated = false;
        }
        
        $currentTabId = TabAuthHelper::getCurrentTabId($request);
        
        return view('welcome', compact('categories', 'isTabAuthenticated', 'currentTabId'));
    }

    /**
     * Client-Only Dashboard (Root Route)
     * 
     * This method is protected by CheckClientonlyAccess middleware.
     * Only authenticated users with 'client' role can access this route.
     * Other roles are redirected to their respective dashboards.
     * 
     * @return \Illuminate\View\View
     */
    public function clientDashboard(Request $request)
    {
        $user = Auth::user();
        
        try {
            // Get distinct categories for the footer
            $categories = CaseCategory::select('category')
                ->distinct()
                ->orderBy('category')
                ->get()
                ->pluck('category');
        } catch (\Exception $e) {
            // Fallback if categories table doesn't exist
            $categories = collect();
        }
        
        // Check if current tab is authenticated
        $isTabAuthenticated = false;
        try {
            $isTabAuthenticated = TabAuthHelper::isTabAuthenticated($request);
        } catch (\Exception $e) {
            \Log::debug('Tab authentication check failed', ['error' => $e->getMessage()]);
            $isTabAuthenticated = false;
        }
        
        $currentTabId = TabAuthHelper::getCurrentTabId($request);
        
        return view('welcome', [
            'categories' => $categories,
            'isClientDashboard' => true,
            'userRole' => $user ? $user->role : null,
            'isTabAuthenticated' => $isTabAuthenticated,
            'currentTabId' => $currentTabId,
        ]);
    }
}