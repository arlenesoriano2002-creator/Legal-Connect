<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;

class DiffunStaffDashboardController extends Controller
{
    /**
     * Show dashboard for staff roles including secretary and clerk.
     */
    public function index()
    {

        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }

        // Restrict to staff roles including secretary/clerk
        if (! in_array($user->role, ['diffun_staff', 'staff', 'secretary', 'clerk'])) {
            return redirect()->route('welcome')->with('error', 'Unauthorized access.');
        }


        // Counts scoped to Diffun Branch Office only
        $branchName = 'Diffun Branch Office';

        $pendingAppointments = $this->countBranchAppointmentsByStatus($branchName, 'pending');
        $approvedAppointments = $this->countBranchAppointmentsByStatus($branchName, 'approved');
        $deniedAppointments = $this->countBranchAppointmentsByStatus($branchName, 'denied');
        $totalAppointments = $pendingAppointments + $approvedAppointments + $deniedAppointments;

        $recentAppointments = Appointment::whereRaw("LOWER(TRIM(selected_branch)) = ?", [strtolower(trim($branchName))])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('staff.dashboardStaff', compact(
            'totalAppointments',
            'pendingAppointments',
            'approvedAppointments',
            'deniedAppointments',
            'recentAppointments'
        ));
    }

    private function countBranchAppointmentsByStatus(string $branchName, string $status): int
    {
        return Appointment::whereRaw("LOWER(TRIM(selected_branch)) = ?", [strtolower(trim($branchName))])
            ->whereRaw("LOWER(TRIM(appointment_approval)) = ?", [strtolower(trim($status))])
            ->count();
    }
}
