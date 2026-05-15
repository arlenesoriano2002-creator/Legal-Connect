<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DiffunStaffAcceptedController extends Controller
{
    /**
     * Display a listing of approved appointments for Diffun branch.
     */
    public function index(Request $request)
    {
        // Check if user is staff
        if (!Auth::check() || !in_array(Auth::user()->role, ['staff', 'diffun_staff', 'secretary', 'clerk', 'admin', 'superadmin'])) {
            abort(403, 'Unauthorized access.');
        }

        $query = Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['approved'])
            ->whereRaw("LOWER(TRIM(selected_branch)) = ?", ['diffun branch office'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('date')) {
            $query->where('selected_date', $request->date);
        }

        if ($request->filled('time')) {
            $query->where('selected_time', $request->time);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $appointments = $query->get();

        $categories = DB::table('case_categories')->select('category')->distinct()->orderBy('category')->pluck('category');

        return view('staff.staffAcceptedRequest', compact('appointments', 'categories'));
    }

    /**
     * Delete an approved appointment.
     */
    public function destroy($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            
            // Verify it's from Diffun branch and is approved
            if (! $this->isDiffunBranch($appointment->selected_branch) || 
                strtolower(trim($appointment->appointment_approval ?? '')) !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid appointment or permission denied'
                ], 403);
            }
            
            $appointment->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Appointment deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting appointment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics for the dashboard.
     */
    public function getStatistics()
    {
        $stats = [
            'total_accepted' => Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['approved'])
                ->whereRaw("LOWER(TRIM(selected_branch)) LIKE ?", ['%diffun%'])
                ->count(),
            
            'today_accepted' => Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['approved'])
                ->whereRaw("LOWER(TRIM(selected_branch)) LIKE ?", ['%diffun%'])
                ->whereDate('created_at', today())
                ->count(),
            
            'this_week_accepted' => Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['approved'])
                ->whereRaw("LOWER(TRIM(selected_branch)) LIKE ?", ['%diffun%'])
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            
            'this_month_accepted' => Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['approved'])
                ->whereRaw("LOWER(TRIM(selected_branch)) LIKE ?", ['%diffun%'])
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
        
        return response()->json($stats);
    }

    private function isDiffunBranch($branch)
    {
        return str_contains(strtolower(trim($branch ?? '')), 'diffun');
    }

    /**
     * Get appointment details for modal view
     */
    public function getAppointmentDetails($id)
    {
    // Check if user is staff
    if (!Auth::check() || !in_array(Auth::user()->role, ['staff', 'diffun_staff', 'secretary', 'clerk', 'admin', 'superadmin'])) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $appointment = Appointment::find($id);
    
    if (!$appointment) {
        return response()->json(['error' => 'Appointment not found'], 404);
    }

        // Check if appointment belongs to Diffun Branch Office and is approved (normalize values)
        $approval = strtolower(trim($appointment->appointment_approval ?? ''));

        if (! $this->isDiffunBranch($appointment->selected_branch) || $approval !== 'approved') {
            // Return diagnostic info to help frontend surface why the appointment was rejected
            return response()->json([
            'success' => false,
            'error' => 'Appointment exists but is not an approved Diffun appointment',
            'data' => [
                'selected_branch' => $appointment->selected_branch,
                'appointment_approval' => $appointment->appointment_approval,
            ]
        ], 403);
    }

    // Convert image paths to full URLs
    $appointmentArray = $appointment->toArray();
    
    // Helper function to generate correct image URL
    function generateImageUrl($imagePath) {
        if (empty($imagePath) || $imagePath === 'null' || $imagePath === 'NULL') {
            return null;
        }
        
        // If it's already a full URL or base64, return as is
        if (strpos($imagePath, 'http') === 0 || strpos($imagePath, 'data:image') === 0) {
            return $imagePath;
        }
        
        // Check if it's just a filename (no path separators)
        if (strpos($imagePath, '/') === false && strpos($imagePath, '\\') === false) {
            // It's just a filename, prepend the storage path
            return asset('storage/ids/' . $imagePath);
        }
        
        // If it contains path separators but starts with storage or ids
        if (strpos($imagePath, 'storage') !== false || strpos($imagePath, 'ids') !== false) {
            // Remove any leading slashes or backslashes
            $cleanPath = ltrim($imagePath, '/\\');
            
            // If it starts with 'storage/', use asset()
            if (strpos($cleanPath, 'storage/') === 0) {
                return asset($cleanPath);
            }
            
            // If it starts with 'ids/', prepend 'storage/'
            if (strpos($cleanPath, 'ids/') === 0) {
                return asset('storage/' . $cleanPath);
            }
            
            // Otherwise, assume it's relative to storage/ids
            return asset('storage/ids/' . $cleanPath);
        }
        
        // Fallback: try to construct URL from the raw path
        return asset('storage/ids/' . basename($imagePath));
    }
    
    // Generate URLs for both ID images
    $appointmentArray['id_front'] = generateImageUrl($appointment->id_front);
    $appointmentArray['id_back'] = generateImageUrl($appointment->id_back);

    return response()->json([
        'success' => true,
        'appointment' => $appointmentArray
    ]);
    }

    /**
     * Generate PDF report for filtered appointments
     */
    public function generateReportPdf(Request $request)
    {
        // Build the query with the same filters as the index method
        $query = Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['approved'])
            ->whereRaw("LOWER(TRIM(selected_branch)) LIKE ?", ['%diffun%'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('date')) {
            $query->where('selected_date', $request->date);
        }

        if ($request->filled('time')) {
            $query->where('selected_time', $request->time);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $appointments = $query->get();

        $filterInfo = [
            'date' => $request->date ?? 'All Dates',
            'time' => $request->time ?? 'All Times',
            'category' => $request->category ?? 'All Categories'
        ];

        $branch = 'Diffun';

        $pdf = \PDF::loadView('reports.accepted_appointments_report', compact('appointments', 'filterInfo', 'branch'));

        return $pdf->download('diffun_accepted_appointments_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }
}