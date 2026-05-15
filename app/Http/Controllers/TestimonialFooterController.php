<?php

namespace App\Http\Controllers;

use App\Models\CaseCategory;
use App\Helpers\TabAuthHelper;
use Illuminate\Http\Request;

class TestimonialFooterController extends Controller
{
    /**
     * Show the testimonial page with reviews and categories
     */
    public function index(Request $request)
    {
        // Get reviews data (keep existing logic from ReviewController)
        $reviews = \App\Models\Review::orderBy('created_at', 'desc')->get();
        $averageRating = $reviews->avg('rating');
        $totalReviews = $reviews->count();
        
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
        
        return view('testimonial', compact('reviews', 'averageRating', 'totalReviews', 'categories', 'isTabAuthenticated', 'currentTabId'));
    }
}