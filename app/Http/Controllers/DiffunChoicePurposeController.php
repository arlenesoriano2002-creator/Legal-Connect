<?php

namespace App\Http\Controllers;

use App\Models\DiffunChoicePurpose;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DiffunChoicePurposeController extends Controller
{
    /**
     * Display a listing of the purposes.
     */
    public function index()
    {
        // Check if user is staff
        if (!Auth::check() || !in_array(Auth::user()->role, ['staff', 'diffun_staff', 'admin', 'superadmin'])) {
            abort(403, 'Unauthorized access.');
        }

        $purposes = DiffunChoicePurpose::orderBy('purpose', 'asc')->get();
        
        return view('diffun_staff.purpose_choices', compact('purposes'));
    }

    /**
     * Store a newly created purpose.
     */
    public function store(Request $request)
    {
        $request->validate([
            'purpose' => 'required|string|max:255|unique:diffun_choice_purpose,purpose'
        ]);

        DiffunChoicePurpose::create([
            'purpose' => $request->purpose
        ]);

        return redirect()->route('staff.purpose.choices')
            ->with('success', 'Purpose added successfully!');
    }

    /**
     * Update the specified purpose.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'purpose' => 'required|string|max:255|unique:diffun_choice_purpose,purpose,' . $id
        ]);

        $purpose = DiffunChoicePurpose::findOrFail($id);
        $purpose->update([
            'purpose' => $request->purpose
        ]);

        return redirect()->route('staff.purpose.choices')
            ->with('success', 'Purpose updated successfully!');
    }

    /**
     * Remove the specified purpose.
     */
    public function destroy($id)
    {
        try {
            DB::table('diffun_choice_purpose')->where('id', $id)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Purpose deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting purpose: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete purpose: ' . $e->getMessage()
            ], 500);
        }
    }
}