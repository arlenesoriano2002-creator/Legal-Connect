<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\NotifApprovalAppointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientTableController extends Controller
{
    public function index()
    {
        $appointments = Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['pending'])->get();
        return view('clientstbl', compact('appointments'));
    }

    public function approve($id)
    {
        \Log::info('ClientTableController: Approve method called for appointment ID: ' . $id);
        
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->appointment_approval = 'approved';
            $appointment->processed_by = Auth::user()->name ?? 'System';
            $appointment->save();

            // Insert notification
            $this->insertApprovalNotification($appointment);

            // Create notification for the user
            $user = User::where('email', $appointment->email)->first();
            if ($user) {
                // Make sure NotificationController exists and has createNotification method
                \App\Http\Controllers\NotificationController::createNotification(
                    $user->id,
                    'appointment_approved',
                    'Your appointment on ' . $appointment->selected_date . ' at ' . $appointment->selected_time . ' has been approved.'
                );
            }

            return redirect()->back()->with('success', 'Appointment approved successfully.');
            
        } catch (\Exception $e) {
            \Log::error('Error in ClientTableController approve method: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to approve appointment.');
        }
    }

    public function deny($id)
    {
        \Log::info('ClientTableController: Deny method called for appointment ID: ' . $id);
        
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->appointment_approval = 'denied';
            $appointment->processed_by = Auth::user()->name ?? 'System';
            $appointment->save();

            // Insert notification
            $this->insertApprovalNotification($appointment);

            // Create notification for the user
            $user = User::where('email', $appointment->email)->first();
            if ($user) {
                \App\Http\Controllers\NotificationController::createNotification(
                    $user->id,
                    'appointment_denied',
                    'Your appointment on ' . $appointment->selected_date . ' at ' . $appointment->selected_time . ' has been denied.'
                );
            }

            return redirect()->back()->with('success', 'Appointment has been denied.');
            
        } catch (\Exception $e) {
            \Log::error('Error in ClientTableController deny method: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to deny appointment.');
        }
    }

    private function insertApprovalNotification($appointment)
    {
        try {
            \Log::info('Inserting approval notification for appointment: ' . $appointment->id);

            $existingNotification = NotifApprovalAppointment::where('email', $appointment->email)
                ->where('appointment_date', $appointment->selected_date)
                ->where('appointment_time', $appointment->selected_time)
                ->first();

            if ($existingNotification) {
                $existingNotification->update([
                    'appointment_approval' => $appointment->appointment_approval,
                    'updated_at' => now(),
                ]);
            } else {
                NotifApprovalAppointment::create([
                    'fullname' => $appointment->fullname,
                    'email' => $appointment->email,
                    'appointment_approval' => $appointment->appointment_approval,
                    'appointment_date' => $appointment->selected_date,
                    'appointment_time' => $appointment->selected_time,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Failed to insert approval notification: ' . $e->getMessage());
        }
    }
        public function delete($id)
    {
        \Log::info('ClientTableController: Delete method called for appointment ID: ' . $id);
        
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->delete();

            \Log::info('Appointment deleted successfully: ' . $id);

            return redirect()->back()->with('success', 'Appointment deleted successfully.');
            
        } catch (\Exception $e) {
            \Log::error('Error in ClientTableController delete method: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete appointment.');
        }
    }
}