<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FetchAppointmentsController extends Controller
{
    public function index()
    {
        return view('fetch-appointments');
    }

    public function getAppointments(Request $request)
    {
        $status = $request->get('status', 'all');
        
        $query = DB::table('appointments');
        
        if ($status !== 'all') {
            $query->where('appointment_approval', $status);
        }
        
        $appointments = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json($appointments);
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