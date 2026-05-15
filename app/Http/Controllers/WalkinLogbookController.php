<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WalkinLogbookController extends Controller
{
    /**
     * Display the Diffun digital logbook form without requiring authentication.
     */
    public function publicIndex()
    {
        return view('walkin logbook.diffun_logbook.index');
    }

    /**
     * Display the digital logbook form
     */
    public function index()
    {
        // Check if user is staff
        if (!Auth::check() || !in_array(Auth::user()->role, ['staff', 'admin', 'superadmin', 'diffun_staff', 'cordon_staff'])) {
            abort(403, 'Unauthorized access.');
        }

        // Get all purposes from the database
        $purposes = DB::table('diffun_choice_purpose')
            ->orderBy('purpose', 'asc')
            ->get();
        
        return view('walkin logbook.diffun_logbook.index', compact('purposes'));
    }

    /**
     * Get all law offices
     */
    public function getLawOffices()
    {
        try {
            $offices = DB::table('law_offices')
                ->select('id', 'law_office')
                ->orderBy('law_office', 'asc')
                ->get();
            
            return response()->json($offices);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    /**
     * Store a new walk-in entry
     */
    public function store(Request $request)
    {
        // Check if user is staff
       // if (!Auth::check() || !in_array(Auth::user()->role, ['staff', 'admin', 'superadmin', 'diffun_staff', 'cordon_staff'])) {
       //     abort(403, 'Unauthorized access.');
       // }

        // Clean contact number - remove all non-numeric characters
        $cleanedContact = preg_replace('/\D/', '', $request->contact_number);
        
        // Validate the form data
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'contact_number' => 'required|string|size:11|regex:/^[0-9]{11}$/',
            'address' => 'required|string|max:500',
            'purpose' => 'required|string|max:255',
            'law_office_id' => 'required|integer|exists:law_offices,id',
        ], [
            'contact_number.size' => 'Contact number must be exactly 11 digits.',
            'contact_number.regex' => 'Contact number must contain only numbers.',
            'law_office_id.required' => 'Please select a law office.',
            'law_office_id.exists' => 'The selected law office does not exist.',
        ]);

        // Use cleaned contact number
        $validated['contact_number'] = $cleanedContact;
        $recordedTime = now(); 
        try {
            // Create the walk-in entry
            DB::table('diffun_walkins')->insert([
                'fullname' => $validated['fullname'],
                'contact_number' => $validated['contact_number'],
                'address' => $validated['address'],
                'purpose' => $validated['purpose'],
                'law_office_id' => $validated['law_office_id'],
                'branch' => 'Diffun',
                'date_time' => $recordedTime,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Return success response
           return response()->json([
                'success' => true,
                'message' => 'Walk-in entry submitted successfully!',
                'recorded_time' => $recordedTime->format('Y-m-d H:i:s'),
                'client_time' => $request->filled('client_datetime') 
                    ? \Carbon\Carbon::parse($request->client_datetime)->format('Y-m-d H:i:s')
                    : $recordedTime->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting entry: ' . $e->getMessage()
            ], 500);
        }
    }
}
