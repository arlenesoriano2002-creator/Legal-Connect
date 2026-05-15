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
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'cases' => 'nullable|array',
            'cases.*.case_name' => 'nullable|string|max:255',
            'cases.*.service_fee' => 'nullable|numeric|min:0',
        ]);

        $cases = collect($validated['cases'] ?? [])
            ->map(function ($case) {
                return [
                    'case_name' => trim((string) ($case['case_name'] ?? '')),
                    'service_fee' => $this->normalizeServiceFee($case['service_fee'] ?? null),
                ];
            })
            ->filter(fn ($case) => $case['case_name'] !== '')
            ->values();

        DB::transaction(function () use ($validated, $cases) {
            if ($cases->isEmpty()) {
                CaseCategory::create([
                    'category' => $validated['category'],
                    'case_name' => 'Initial Case',
                    'service_fee' => null,
                ]);

                return;
            }

            foreach ($cases as $case) {
                CaseCategory::create([
                    'category' => $validated['category'],
                    'case_name' => $case['case_name'],
                    'service_fee' => $case['service_fee'],
                ]);
            }
        });

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
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'case_name' => 'required|string|max:255',
            'service_fee' => 'nullable|numeric|min:0',
        ]);

        CaseCategory::create([
            'category' => $validated['category'],
            'case_name' => $validated['case_name'],
            'service_fee' => $this->normalizeServiceFee($validated['service_fee'] ?? null),
        ]);

        return response()->json(['success' => true, 'message' => 'Case added successfully']);
    }

    // Update case name
    public function updateCase(Request $request, $id)
    {
        $validated = $request->validate([
            'case_name' => 'required|string|max:255',
            'service_fee' => 'nullable|numeric|min:0',
        ]);

        $case = CaseCategory::findOrFail($id);
        $case->update([
            'case_name' => $validated['case_name'],
            'service_fee' => $this->normalizeServiceFee($validated['service_fee'] ?? null),
        ]);

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

    private function normalizeServiceFee($serviceFee): ?string
    {
        if ($serviceFee === null || $serviceFee === '') {
            return null;
        }

        return number_format((float) $serviceFee, 2, '.', '');
    }
}
