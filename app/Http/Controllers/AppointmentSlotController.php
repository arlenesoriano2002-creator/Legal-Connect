<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\AppointmentSlot;

class AppointmentSlotController extends Controller
{
public function store(Request $request)
{
    \Log::info('Incoming availability:', $request->all());

    $availability = $request->input('availability');

    if (!is_array($availability)) {
        return response()->json(['status' => 'error', 'message' => 'Invalid input format'], 400);
    }

    try {
        foreach ($availability as $slot) {
            // Check if slot already exists
            $existingSlot = AppointmentSlot::where('date', $slot['date'])
                ->where('time', $slot['time'])
                ->first();

            if (!$existingSlot) {
                $newSlot = AppointmentSlot::create([
                    'date' => $slot['date'],
                    'time' => $slot['time'],
                    'booked' => false
                ]);
                \Log::info('Slot saved:', $newSlot->toArray());
            } else {
                \Log::info('Slot already exists:', $existingSlot->toArray());
            }
        }

        return response()->json(['status' => 'success']);
    } catch (\Exception $e) {
        \Log::error('Slot save failed: ' . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => 'Server Error'], 500);
    }
}



    public function index()
{
    return response()->json(AppointmentSlot::all());
}

    public function destroy(Request $request)
{
    $request->validate([
        'date' => 'required|date',
        'time' => 'required|string',
    ]);

    AppointmentSlot::where('date', $request->date)
                   ->where('time', $request->time)
                   ->delete();

    return response()->json(['status' => 'deleted']);
}
public function deleteAvailability(Request $request)
{
    $request->validate([
        'date' => 'required|date',
        'time' => 'required|string',
    ]);

    $deleted = DB::table('appointment_slots')
        ->where('date', $request->date)
        ->where('time', $request->time)
        ->delete();

    if ($deleted) {
        return response()->json(['status' => 'success']);
    } else {
        return response()->json(['status' => 'failed'], 404);
    }
}
public function update(Request $request, $id)
{
    $appointment = Appointment::findOrFail($id);

    $request->validate([
        'fullname' => 'required|string',
        'address' => 'required|string',
        'phone' => 'required|string',
        'consulting' => 'required|string',
        'schedule_date' => 'required|date', // Changed from selected_date
        'schedule_time' => 'required|string', // Changed from selected_time
        'term_status' => 'required|string',
    ]);

    $appointment->update([
        'fullname' => $request->fullname,
        'address' => $request->address,
        'phone' => $request->phone,
        'consulting' => $request->consulting,
        'schedule_date' => $request->schedule_date, // Changed from selected_date
        'schedule_time' => $request->schedule_time, // Changed from selected_time
        'term_status' => $request->term_status,
    ]);

    return response()->json(['success' => true, 'message' => 'Appointment updated successfully.']);
}

public function destroyById($id)
{
    $slot = AppointmentSlot::find($id);

    if (!$slot) {
        return response()->json(['status' => 'error', 'message' => 'Slot not found'], 404);
    }

    $slot->delete();

    return response()->json(['status' => 'success']);
}
public function getAvailableTimes($date)
{
    $slots = DB::table('appointment_slots')
        ->whereDate('date', $date)
        ->select('id', 'time', 'booked')
        ->orderBy('time')
        ->get();

    return response()->json($slots);
}
public function bookSlot(Request $request)
{
    $request->validate([
        'date' => 'required|date',
        'time' => 'required|string'
    ]);

    $slot = AppointmentSlot::where('date', $request->date)
        ->where('time', $request->time)
        ->where('booked', false)
        ->first();

    if ($slot) {
        $slot->booked = true;
        $slot->save();
        return response()->json(['status' => 'success']);
    }

    return response()->json(['status' => 'error', 'message' => 'Slot already booked']);
}
public function unbookSlot(Request $request)
{
    $request->validate([
        'date' => 'required|date',
        'time' => 'required|string',
    ]);

    $slot = AppointmentSlot::where('date', $request->date)
        ->where('time', $request->time)
        ->where('booked', true)
        ->first();

    if ($slot) {
        $slot->booked = false;
        $slot->save();
        return response()->json(['status' => 'success']);
    }

    return response()->json(['status' => 'error', 'message' => 'Slot not found or not booked'], 404);
}
public function show($id)
{
    $slot = AppointmentSlot::find($id);
    if (!$slot) {
        return response()->json(['status' => 'error', 'message' => 'Slot not found'], 404);
    }
    return response()->json($slot);
}

public function storeAvailability(Request $request)
{
    $availability = $request->input('availability', []);

    foreach ($availability as $slot) {
        AppointmentSlot::create([
            'date' => $slot['date'],
            'time' => $slot['time'],
        ]);
    }

    return response()->json(['status' => 'success']);
}
public function newStore(Request $request)
{
    $request->validate([
        'fullname' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'consulting' => 'required|string',
        'schedule_date' => 'required|date', // Changed from selected_date
        'schedule_time' => 'required|string', // Changed from selected_time
        'term_status' => 'required|string',
        'id_front' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        'id_back' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $frontImage = $request->file('id_front')->store('ids', 'public');
    $backImage = $request->file('id_back')->store('ids', 'public');

    Appointment::create([
        'fullname' => $request->fullname,
        'address' => $request->address,
        'phone' => $request->phone,
        'consulting' => $request->consulting,
        'schedule_date' => $request->schedule_date, // Changed from selected_date
        'schedule_time' => $request->schedule_time, // Changed from selected_time
        'term_status' => $request->term_status,
        'id_front' => $frontImage,
        'id_back' => $backImage,
        'appointment_approval' => 'pending',
    ]);

    return redirect()->back()->with('success', 'Client added successfully!');
}
public function approve($id)
{
    \Log::info('=== APPROVE METHOD CALLED ===');
    \Log::info('Approve method called for appointment ID: ' . $id);
    
    try {
        $appointment = Appointment::findOrFail($id);
        \Log::info('Found appointment:', ['id' => $appointment->id, 'current_status' => $appointment->appointment_approval]);
        
        $appointment->appointment_approval = 'approved';
        $appointment->save();
        
        \Log::info('Appointment status updated to: ' . $appointment->appointment_approval);

        // Insert notification
        $this->insertApprovalNotification($appointment);

        // Create notification for the user
        $user = User::where('email', $appointment->email)->first();
        if ($user) {
            NotificationController::createNotification(
                $user->id,
                'appointment_approved',
                'Your appointment on ' . $appointment->selected_date . ' at ' . $appointment->selected_time . ' has been approved.'
            );
        }

        \Log::info('=== APPROVE METHOD COMPLETED SUCCESSFULLY ===');
        return redirect()->back()->with('success', 'Appointment approved successfully.');
        
    } catch (\Exception $e) {
        \Log::error('Error in approve method: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to approve appointment.');
    }
}

public function deny($id)
{
    \Log::info('=== DENY METHOD CALLED ===');
    \Log::info('Deny method called for appointment ID: ' . $id);
    
    try {
        $appointment = Appointment::findOrFail($id);
        \Log::info('Found appointment:', ['id' => $appointment->id, 'current_status' => $appointment->appointment_approval]);
        
        $appointment->appointment_approval = 'denied';
        $appointment->save();
        
        \Log::info('Appointment status updated to: ' . $appointment->appointment_approval);

        // Insert notification
        $this->insertApprovalNotification($appointment);

        // Create notification for the user
        $user = User::where('email', $appointment->email)->first();
        if ($user) {
            NotificationController::createNotification(
                $user->id,
                'appointment_denied',
                'Your appointment on ' . $appointment->selected_date . ' at ' . $appointment->selected_time . ' has been denied.'
            );
        }

        \Log::info('=== DENY METHOD COMPLETED SUCCESSFULLY ===');
        return redirect()->back()->with('success', 'Appointment has been denied.');
        
    } catch (\Exception $e) {
        \Log::error('Error in deny method: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to deny appointment.');
    }
}
}
