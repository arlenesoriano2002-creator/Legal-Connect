<?php

namespace App\Http\Controllers;

use App\Models\CaseCategory;
use App\Helpers\TabAuthHelper;
use Illuminate\Http\Request;

class PracticeAreasController extends Controller
{
    /**
     * Display the about page with grouped practice areas
     */
    public function about(Request $request)
    {
        // Fetch all case categories and group by category
        $caseCategories = CaseCategory::all();
        
        // Group case names by category
        $groupedCases = [];
        foreach ($caseCategories as $case) {
            $category = $case->category;
            $caseName = $case->case_name;
            
            if (!isset($groupedCases[$category])) {
                $groupedCases[$category] = [];
            }
            
            $groupedCases[$category][] = $caseName;
        }
        
        // Get distinct categories for footer
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
        
        return view('about', compact('groupedCases', 'categories', 'isTabAuthenticated', 'currentTabId'));
    }
}