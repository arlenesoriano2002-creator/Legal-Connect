<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\CaseCategory;

class FetchAppointmentsController extends Controller
{
    public function index()
    {
        return view('fetch-appointments');
    }

    public function getAppointments(Request $request)
    {
        $status = $request->get('status', 'all');
        $category = $request->get('category', 'all');
        
        $query = DB::table('appointments');
        
        if ($status !== 'all') {
            $query->whereRaw("LOWER(TRIM(appointment_approval)) = ?", [strtolower(trim($status))]);
        }

        if ($category && $category !== 'all') {
            // Filter by category column on appointments table
            $query->where('category', $category);
        }
        
        $appointments = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json($appointments);
    }

    /**
     * Return list of distinct categories from case_categories table
     */
    public function getCategories()
    {
        $categories = CaseCategory::getCategories();
        return response()->json(['categories' => $categories]);
    }

    /**
     * Return list of distinct case_name values from case_categories table
     * Optionally filter by category via ?category=...
     */
    public function getCaseNames(Request $request)
    {
        $category = $request->get('category', null);

        $query = CaseCategory::select('case_name')->distinct()->orderBy('case_name');
        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        $caseNames = $query->get()->pluck('case_name');

        return response()->json(['case_names' => $caseNames]);
    }

    public function getAppointmentDetails($id)
{
    $appointment = DB::table('appointments')->where('id', $id)->first();
    
    if (!$appointment) {
        return response()->json(['error' => 'Appointment not found'], 404);
    }
    
    // Use the full path from database
    $baseUrl = url('/');
    $appointment->id_front_url = $appointment->id_front ? $baseUrl . '/storage/' . $appointment->id_front : null;
    $appointment->id_back_url = $appointment->id_back ? $baseUrl . '/storage/' . $appointment->id_back : null;
    
    return response()->json($appointment);
}
}
