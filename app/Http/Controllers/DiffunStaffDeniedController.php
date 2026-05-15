<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DiffunStaffDeniedController extends Controller
{
    /**
     * Display a listing of denied appointments for Diffun branch.
     */
    public function index()
    {
        // Check if user is staff
        if (!Auth::check() || !in_array(Auth::user()->role, ['staff', 'diffun_staff', 'secretary', 'clerk', 'admin', 'superadmin'])) {
            abort(403, 'Unauthorized access.');
        }

        // Get denied appointments for Diffun branch
        $appointments = Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['denied'])
            ->whereRaw("LOWER(TRIM(selected_branch)) = ?", ['diffun branch office'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('staff.staffDeniedRequest', compact('appointments'));
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

        // Check if appointment belongs to Diffun Branch Office and is denied
        if (strtolower(trim($appointment->selected_branch ?? '')) !== 'diffun branch office' || 
            strtolower(trim($appointment->appointment_approval ?? '')) !== 'denied') {
            return response()->json(['error' => 'You can only view denied appointments from Diffun Branch'], 403);
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
     * Delete a denied appointment.
     */
    public function destroy($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            
            // Verify it's from Diffun branch and is denied
            if ($appointment->selected_branch !== 'Diffun Branch Office' || 
                $appointment->appointment_approval !== 'denied') {
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
            'total_denied' => Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['denied'])
                ->whereRaw("LOWER(TRIM(selected_branch)) = ?", ['diffun branch office'])
                ->count(),
            
            'today_denied' => Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['denied'])
                ->whereRaw("LOWER(TRIM(selected_branch)) = ?", ['diffun branch office'])
                ->whereDate('created_at', today())
                ->count(),
            
            'this_week_denied' => Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['denied'])
                ->whereRaw("LOWER(TRIM(selected_branch)) = ?", ['diffun branch office'])
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            
            'this_month_denied' => Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['denied'])
                ->whereRaw("LOWER(TRIM(selected_branch)) = ?", ['diffun branch office'])
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
        
        return response()->json($stats);
    }
}