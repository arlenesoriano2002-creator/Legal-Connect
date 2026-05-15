<?php

namespace App\Http\Controllers;

use App\Models\CaseCategory;
use App\Helpers\TabAuthHelper;
use Illuminate\Http\Request;

class ContactFooterController extends Controller
{
    /**
     * Display the contact page with footer categories
     */
    public function index(Request $request)
    {
        // Get distinct categories from the database
        $categories = CaseCategory::select('category')
            ->distinct()
            ->orderBy('category')
            ->get()
            ->pluck('category');
        
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
        
        return view('contact', compact('categories', 'isTabAuthenticated', 'currentTabId'));
    }
}