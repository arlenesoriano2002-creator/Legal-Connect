<?php

namespace App\Http\Controllers;

use App\Models\CaseCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get distinct categories for the footer
     */
    public function getFooterCategories()
    {
        // Get distinct categories from the database
        $categories = CaseCategory::select('category')
            ->distinct()
            ->orderBy('category')
            ->get()
            ->pluck('category');
        
        return $categories;
    }
    
    /**
     * Get all categories with their cases
     */
    public function getCategoriesWithCases()
    {
        // Get all categories with their cases
        $categories = CaseCategory::select('category', 'case_name')
            ->orderBy('category')
            ->orderBy('case_name')
            ->get()
            ->groupBy('category');
        
        return $categories;
    }
}