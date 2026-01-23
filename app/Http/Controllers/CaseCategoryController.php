<?php

namespace App\Http\Controllers;

use App\Models\CaseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaseCategoryController extends Controller
{
    // Display all categories
    public function index()
    {
        // Get unique categories with their case counts
        $categories = CaseCategory::select('category', DB::raw('COUNT(*) as case_count'))
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        // Get all case names grouped by category
        $casesByCategory = [];
        foreach ($categories as $category) {
            $casesByCategory[$category->category] = CaseCategory::where('category', $category->category)
                ->orderBy('case_name')
                ->get();
        }

        return view('practice-areas', [
            'categories' => $categories,
            'casesByCategory' => $casesByCategory
        ]);
    }

    // Add new category
    public function storeCategory(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255'
        ]);

        // Create the category with an initial empty case name
        CaseCategory::create([
            'category' => $request->category,
            'case_name' => 'Initial Case'
        ]);

        return response()->json(['success' => true, 'message' => 'Category added successfully']);
    }

    // Update category name
    public function updateCategory(Request $request, $oldCategory)
    {
        $request->validate([
            'new_category' => 'required|string|max:255'
        ]);

        // Update all records with the old category name
        CaseCategory::where('category', $oldCategory)
            ->update(['category' => $request->new_category]);

        return response()->json(['success' => true, 'message' => 'Category updated successfully']);
    }

    // Delete category and all its cases
    public function destroyCategory($category)
    {
        CaseCategory::where('category', $category)->delete();
        
        return response()->json(['success' => true, 'message' => 'Category deleted successfully']);
    }

    // Add new case name under category
    public function storeCase(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'case_name' => 'required|string|max:255'
        ]);

        CaseCategory::create([
            'category' => $request->category,
            'case_name' => $request->case_name
        ]);

        return response()->json(['success' => true, 'message' => 'Case added successfully']);
    }

    // Update case name
    public function updateCase(Request $request, $id)
    {
        $request->validate([
            'case_name' => 'required|string|max:255'
        ]);

        $case = CaseCategory::findOrFail($id);
        $case->update(['case_name' => $request->case_name]);

        return response()->json(['success' => true, 'message' => 'Case updated successfully']);
    }

    // Delete case name
    public function destroyCase($id)
    {
        $case = CaseCategory::findOrFail($id);
        $case->delete();

        return response()->json(['success' => true, 'message' => 'Case deleted successfully']);
    }

    // Get all cases for a specific category (for modal)
    public function getCategoryCases($category)
    {
        $cases = CaseCategory::where('category', $category)
            ->orderBy('case_name')
            ->get();

        return response()->json($cases);
    }
}