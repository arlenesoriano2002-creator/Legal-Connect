<?php

namespace App\Http\Controllers;

use App\Models\Appointment;      
use App\Models\AppointmentSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ArchivedAppointment;
use App\Models\NotifApprovalAppointment; // Add this import

class AppointmentController extends Controller
{
public function storeStep1(Request $request)
{
    \Log::info('StoreStep1 called with data:', $request->all());
    
    $validated = $request->validate([
        'fullname' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'email' => 'required|email|max:255',
        'category' => 'required|string|max:255',
        'case_id' => 'required|exists:case_categories,id',
    ]);

    // Clear any existing session data first
    $request->session()->forget(['fullname', 'address', 'phone', 'email', 'category', 'case_id', 
                               'selected_case_id', 'selected_case_name', 'selected_category']); // Remove 'consulting'

    // Store all validated data in session
    foreach ($validated as $key => $value) {
        session([$key => $value]);
    }

    // Get the selected case details
    $selectedCase = DB::table('case_categories')->where('id', $request->case_id)->first();
    if ($selectedCase) {
        session(['selected_case_id' => $selectedCase->id]);
        session(['selected_case_name' => $selectedCase->case_name]);
        session(['selected_category' => $selectedCase->category]);
        // REMOVE THIS: session(['consulting' => $selectedCase->category . ' - ' . $selectedCase->case_name]);
    }

    // Save the session to ensure persistence
    $request->session()->save();

    \Log::info('Session data stored:', [
        'fullname' => session('fullname'),
        'category' => session('category'),
        'case_id' => session('case_id'),
        'selected_case_name' => session('selected_case_name'),
        'selected_category' => session('selected_category')
        // Remove 'consulting'
    ]);

    return redirect()->route('getsched');
}
    public function storeAvailability(Request $request)
    {
        $availability = $request->input('availability');

        if (!is_array($availability)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid input format'], 400);
        }

        try {
            foreach ($availability as $slot) {
                if (!isset($slot['date']) || !isset($slot['time'])) {
                    return response()->json(['status' => 'error', 'message' => 'Invalid slot format'], 400);
                }

                \App\Models\AppointmentSlot::create([
                    'date' => $slot['date'],
                    'time' => $slot['time'],
                    'available_slots' => $slot['available_slots'] ?? 1
                ]);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            \Log::error('Slot save failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Server Error'], 500);
        }
    }

    public function deleteAvailability(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required|string',
        ]);

        try {
            $deleted = DB::table('appointment_slots')
                ->where('date', $request->date)
                ->where('time', $request->time)
                ->delete();

            if ($deleted) {
                return response()->json(['status' => 'success']);
            } else {
                return response()->json(['status' => 'not_found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

   public function accept($id)
{
    \Log::info('Approve method called for appointment ID: ' . $id);
    $appointment = Appointment::findOrFail($id);
    $appointment->appointment_approval = 'approved'; // Change from 'status' to 'appointment_approval'
    $appointment->save();

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

    return redirect()->back()->with('success', 'Appointment approved successfully.');
}

public function finalizeAppointment(Request $request)
{
    \Log::info('Finalize Appointment Request Data: ', $request->all());
    
    $request->validate([
        'fullname'        => 'required|string|max:255',
        'address'         => 'required|string|max:255',
        'phone'           => 'required|string|max:20',
        'email'           => 'required|email|max:255',
        'category'        => 'required|string|max:255',
        'case_name'       => 'required|string|max:255',
        'selected_date'   => 'required|date',
        'selected_time'   => 'required|string',
        'id_front'        => 'required|image|mimes:jpg,jpeg,png|max:2048',
        'id_back'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    try {
        // First, decrement the slot count
        $decrementResponse = $this->decrementSlotCount(new Request([
            'date' => $request->selected_date,
            'time_range' => $request->selected_time
        ]));

        if ($decrementResponse->getStatusCode() !== 200) {
            $responseData = json_decode($decrementResponse->getContent(), true);
            throw new \Exception($responseData['message'] ?? 'Failed to reserve time slot');
        }

        // Then create the appointment
        $idFrontPath = $request->file('id_front')->store('ids', 'public');
        $idBackPath = $request->file('id_back') ? $request->file('id_back')->store('ids', 'public') : null;

        Appointment::create([
            'fullname'             => $request->fullname,
            'address'              => $request->address,
            'phone'                => $request->phone,
            'email'                => $request->email,
            'category'             => $request->category,
            'case_name'            => $request->case_name,
            'selected_date'        => $request->selected_date,
            'selected_time'        => $request->selected_time,
            'term_status'          => $request->term_status ?? 'pending',
            'id_front'             => $idFrontPath,
            'id_back'              => $idBackPath,
            'appointment_approval' => 'pending',
        ]);

        // Clear session data
        $request->session()->forget(['fullname', 'address', 'phone', 'email', 'category', 'case_id', 
                                   'selected_case_id', 'selected_case_name', 'selected_category']);

        return redirect()
            ->route('welcome')
            ->with('success', 'Appointment finalized successfully!');
            
    } catch (\Exception $e) {
        \Log::error('Appointment creation failed: ' . $e->getMessage());
        return back()
            ->withInput()
            ->with('error', 'Failed to create appointment. Please try again. Error: ' . $e->getMessage());
    }
}

public function showAppointmentForm()
{
    \Log::info('showAppointmentForm called - checking terms acceptance');
    
    // Check if terms were accepted
    if (session('status_approval') !== 'approved') {
        \Log::warning('Terms not accepted, redirecting to Terms page');
       return redirect()->route('getsched')->with('error', 'Please accept the terms and conditions first.');
    }

    $user = Auth::user(); 

    if (!$user) {
        return redirect('/login');
    }

    $fullname = $user->name ?? '';
    $phone    = $user->cp_number ?? '';
    $email    = $user->email ?? '';
    $address  = $user->address ?? '';

    // Fetch case categories with their cases grouped by category
    $caseCategories = DB::table('case_categories')
        ->select('category')
        ->distinct()
        ->get()
        ->map(function ($category) {
            // Get all cases for this category
            $category->cases = DB::table('case_categories')
                ->where('category', $category->category)
                ->select('id', 'case_name')
                ->get();
            return $category;
        });

    return view('appointment1', compact('fullname', 'phone', 'email', 'address', 'caseCategories'));
}
public function showFinalizePage(Request $request)
{
   Log::info('AppointmentController: showFinalizePage called');
    
    // Verify we have all required session data
    if (!session('fullname') || !session('status_approval')) {
        Log::warning('Missing required session data, redirecting to appointment1');
        return redirect()->route('appointment1')->with('error', 'Please complete your appointment information first.');
    }
    Log::info('Session status_approval in showFinalizePage: ' . session('status_approval', 'NOT SET'));
    Log::info('Full session in showFinalizePage:', session()->all());
    Log::info('Session ID in showFinalizePage: ' . session()->getId());

    $date = $request->query('date');
    $time = $request->query('time');

    $fullname = session('fullname');
    $address = session('address');
    $phone = session('phone');
    $email = session('email');
    $consulting = session('consulting');
    $status_approval = session('status_approval');
    
    // Add these lines to get the category and case name from session
    $selected_category = session('selected_category');
    $selected_case_name = session('selected_case_name');

    return view('FinalizeAppointment', compact(
        'date',
        'time',
        'fullname',
        'address',
        'phone',
        'email',
        'consulting',
        'status_approval',
        'selected_category',
        'selected_case_name'
    ));
}

    public function showApprovedAppointments()
    {
        $appointments = Appointment::where('appointment_approval', 'approved')->get();
        return view('adminAcceptedRequest', compact('appointments'));
    }

    public function showDenied()
    {
        $appointments = Appointment::where('appointment_approval', 'denied')->get();
        return view('adminDeniedRequest', compact('appointments'));
    }

    public function delete($id)
    {
        Appointment::destroy($id);
        return back()->with('success', 'Appointment removed.');
    }

    public function show($id)
    {
        $appointment = Appointment::findOrFail($id);
        return response()->json($appointment);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $request->validate([
            'fullname' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'consulting' => 'required|string',
            'selected_date' => 'required|date',
            'selected_time' => 'required|string',
            'term_status' => 'required|string',
        ]);

        $appointment->update([
            'fullname' => $request->fullname,
            'address' => $request->address,
            'phone' => $request->phone,
            'consulting' => $request->consulting,
            'selected_date' => $request->selected_date,
            'selected_time' => $request->selected_time,
            'term_status' => $request->term_status,
        ]);

        return response()->json(['success' => true, 'message' => 'Appointment updated successfully.']);
    }

public function newStore(Request $request)
{
    $request->validate([
        'fullname' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'selected_date'   => 'required|date',
        'selected_time'   => 'required|string',
        'term_status' => 'required|string',
        'id_front' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        'id_back' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $frontImage = $request->file('id_front')->store('ids', 'public');
    $backImage = $request->file('id_back')->store('ids', 'public');

    // Extract category and case_name from consulting field
    $consultingParts = explode(' - ', $request->consulting);
    $category = $consultingParts[0] ?? 'General';
    $caseName = $consultingParts[1] ?? 'Consultation';

    Appointment::create([
        'fullname'             => $request->fullname,
        'address'              => $request->address,
        'phone'                => $request->phone,
        'email'                => $request->email,
        'category'             => $category,
        'case_name'            => $caseName,
        'selected_date'        => $request->selected_date,
        'selected_time'        => $request->selected_time,
        'term_status'          => $request->term_status ?? 'pending',
        'id_front'             => $frontImage,
        'id_back'              => $backImage,
        'appointment_approval' => 'pending',
    ]);

    return redirect()->back()->with('success', 'Client added successfully!');
}
    public function showDeniedAppointments()
    {
        $appointments = \App\Models\Appointment::where('appointment_approval', 'denied')->get();
        return view('adminDeniedRequest', compact('appointments'));
    }

 public function showGetSched()
{

     Log::info('AppointmentController: showGetSched called');
    
    // Check if we have the necessary session data
    if (!session('fullname') || !session('status_approval')) {
        Log::warning('Missing session data in showGetSched, redirecting to appointment1');
        return redirect()->route('appointment1')->with('error', 'Please complete your appointment information first.');
    }
    
    Log::info('Session status_approval in showGetSched: ' . session('status_approval', 'NOT SET'));
    Log::info('Full session in showGetSched:', session()->all());

    // Check if we have the necessary session data
    if (!session('fullname') || !session('status_approval')) {
        Log::warning('Missing session data in showGetSched, redirecting to appointment1');
        return redirect()->route('appointment1')->with('error', 'Please complete your appointment information first.');
    }

    $fullname = session('fullname');
    $address = session('address');
    $phone = session('phone');
    $email = session('email');
    $consulting = session('consulting');
    $status_approval = session('status_approval');

    $date = session('schedule_date');
    $time = session('schedule_time');

    return view('getsched', compact(
        'fullname',
        'address',
        'phone',
        'email',
        'consulting',
        'status_approval',
        'date',
        'time'
    ));
}

    public function archiveAppointment($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return back()->with('error', 'Appointment not found.');
        }

        try {
            $selectedDate = ($appointment->selected_date && $appointment->selected_date !== '0000-00-00')
                ? $appointment->selected_date
                : null;

            $scheduleDate = ($appointment->schedule_date && $appointment->schedule_date !== '0000-00-00')
                ? $appointment->schedule_date
                : null;

            ArchivedAppointment::create([
                'fullname' => $appointment->fullname,
                'address' => $appointment->address,
                'phone' => $appointment->phone,
                'email' => $appointment->email,
                'consulting' => $appointment->consulting,
                'selected_date' => $selectedDate,
                'selected_time' => $appointment->selected_time ?: null,
                'schedule_date' => $scheduleDate,
                'schedule_time' => $appointment->schedule_time ?: null,
                'term_status' => $appointment->term_status,
                'appointment_approval' => $appointment->appointment_approval,
                'id_front' => $appointment->id_front,
                'id_back' => $appointment->id_back,
            ]);

            return back()->with('success', 'Archived successfully.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function validateDate($value)
    {
        if (empty($value) || strtolower($value) === 'date' || !strtotime($value)) {
            return null;
        }

        return date('Y-m-d', strtotime($value));
    }

    public function showArchivedAppointments()
    {
        $archivedAppointments = ArchivedAppointment::orderBy('selected_date', 'asc')
            ->orderBy('selected_time', 'asc')
            ->get();

        return view('archivedAppointments', compact('archivedAppointments'));
    }

    public function showSchedule()
    {
        $slots = DB::table('appointment_slots')->get();
        return view('getsched', compact('slots'));
    }

    public function deniedRequests()
    {
        $appointments = Appointment::whereRaw('LOWER(appointment_approval) = ?', ['denied'])
            ->orderBy('id', 'asc')
            ->get();

        return view('staff.staffDeniedRequest', compact('appointments'));
    }

    public function staffDashboard()
    {
        $totalAppointments    = Appointment::count();
        $pendingAppointments  = Appointment::whereRaw('LOWER(appointment_approval) = ?', ['pending'])->count();
        $approvedAppointments = Appointment::whereRaw('LOWER(appointment_approval) = ?', ['approved'])->count();
        $deniedAppointments   = Appointment::whereRaw('LOWER(appointment_approval) = ?', ['denied'])->count();

        $recentAppointments = Appointment::orderBy('created_at', 'desc')->take(5)->get();

        return view('dashboardStaff', compact(
            'totalAppointments',
            'pendingAppointments',
            'approvedAppointments',
            'deniedAppointments',
            'recentAppointments'
        ));
    }

    public function deleteArchived($id)
    {
        $archived = ArchivedAppointment::find($id);

        if (!$archived) {
            return redirect()->back()->with('error', 'Archived appointment not found.')
                ->with('keepArchiveOpen', true);
        }

        $archived->delete();

        return redirect()->back()
            ->with('success', 'Archived appointment deleted successfully.')
            ->with('keepArchiveOpen', true);
    }

    /**
     * Insert notification when appointment_approval is updated
     */
    private function insertApprovalNotification($appointment)
    {
        try {
            \Log::info('Inserting approval notification for appointment: ' . $appointment->id);

            // Check if notification already exists to avoid duplicates
            $existingNotification = NotifApprovalAppointment::where('email', $appointment->email)
                ->where('appointment_date', $appointment->selected_date)
                ->where('appointment_time', $appointment->selected_time)
                ->first();

            if ($existingNotification) {
                \Log::info('Notification already exists, updating instead');
                $existingNotification->update([
                    'appointment_approval' => $appointment->appointment_approval,
                    'updated_at' => now(),
                ]);
                $notification = $existingNotification;
            } else {
                // Insert into notifapprovalappointment table
                $notification = NotifApprovalAppointment::create([
                    'fullname' => $appointment->fullname,
                    'email' => $appointment->email,
                    'appointment_approval' => $appointment->appointment_approval,
                    'appointment_date' => $appointment->selected_date,
                    'appointment_time' => $appointment->selected_time,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            \Log::info('Notification inserted/updated successfully. Notification ID: ' . $notification->id);

        } catch (\Exception $e) {
            \Log::error('Failed to insert approval notification: ' . $e->getMessage());
            \Log::error('Error details: ' . $e->getTraceAsString());
            throw $e; // Re-throw to handle in calling method
        }
    }

    public function approve($id)
    {
        \Log::info('Approve method called for appointment ID: ' . $id);
        $appointment = Appointment::findOrFail($id);
        $appointment->appointment_approval = 'approved';
        $appointment->save();

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

        return redirect()->back()->with('success', 'Appointment approved successfully.');
    }

public function deny($id)
{
    \Log::info('Deny method called for appointment ID: ' . $id);
    $appointment = Appointment::findOrFail($id);
    $appointment->appointment_approval = 'denied'; // Change from 'status' to 'appointment_approval'
    $appointment->save();

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

    return redirect()->back()->with('success', 'Appointment has been denied.');
}

    public function denyAppointment($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->appointment_approval = 'denied';
        $appointment->save();

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

        return redirect()->back()->with('success', 'Appointment denied successfully.');
    }

    public function reaccept($id)
    {
        \Log::info('Reaccept method called for appointment ID: ' . $id);
        $appointment = Appointment::findOrFail($id);
        $appointment->appointment_approval = 'approved';
        $appointment->save();

        // Insert notification
        $this->insertApprovalNotification($appointment);

        // Create notification for the user
        $user = User::where('email', $appointment->email)->first();
        if ($user) {
            NotificationController::createNotification(
                $user->id,
                'appointment_reaccepted',
                'Your appointment on ' . $appointment->selected_date . ' at ' . $appointment->selected_time . ' has been re-accepted.'
            );
        }

        return back()->with('success', 'Appointment re-accepted.');
    }
    // Add these methods to your AppointmentController

// In AppointmentController.php
public function getMonthColors(Request $request)
{
    $month = $request->query('month'); // Format: YYYY-MM
    
    $colors = DB::table('month_colors')
                ->where('month', $month)
                ->select('date', 'color', 'description')
                ->get();
    
    // Convert to the expected format
    $formattedColors = [];
    foreach ($colors as $color) {
        $formattedColors[$color->date] = [
            'color' => $color->color,
            'description' => $color->description
        ];
    }
    
    return response()->json($formattedColors);
}

public function getWeekColors(Request $request)
{
    $date = $request->query('date'); // Format: YYYY-MM-DD
    
    $weekColors = DB::table('week_colors')
                    ->where('date', $date)
                    ->select('time', 'color', 'description', 'booked', 'time_slot') // Added time_slot
                    ->orderBy('time')
                    ->get();
    
    return response()->json($weekColors);
}

public function bookWeekSlot(Request $request)
{
    $validated = $request->validate([
        'date' => 'required|date',
        'time_range' => 'required|string'
        // Remove 'time_slot' from validation since we're using time_range directly
    ]);

    try {
        \Log::info('Booking week slot:', $validated);
        
        // Check if the slot exists and is available
        $weekSlot = DB::table('week_colors')
            ->where('date', $validated['date'])
            ->where('time', $validated['time_range'])
            ->first();

        if (!$weekSlot) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Time slot not found.'
            ]);
        }

        // Check if already booked
        if ($weekSlot->booked == 1) {
            return response()->json([
                'status' => 'error', 
                'message' => 'This time slot is already booked. Please choose another time.'
            ]);
        }

        // Check if the slot is green (available)
        if ($weekSlot->color !== 'green') {
            return response()->json([
                'status' => 'error', 
                'message' => 'This time slot is not available. Please choose a green time slot.'
            ]);
        }

        // Check if there are available slots
        if ($weekSlot->time_slot <= 0) {
            return response()->json([
                'status' => 'error', 
                'message' => 'No available slots in this time slot.'
            ]);
        }

        // Update the slot to booked
        DB::table('week_colors')
            ->where('date', $validated['date'])
            ->where('time', $validated['time_range'])
            ->update(['booked' => 1]);

        \Log::info('Week slot booked successfully:', [
            'date' => $validated['date'],
            'time_range' => $validated['time_range']
        ]);

        return response()->json(['status' => 'success']);

    } catch (\Exception $e) {
        \Log::error('Week slot booking failed: ' . $e->getMessage());
        return response()->json([
            'status' => 'error', 
            'message' => 'Booking failed. Please try again.'
        ], 500);
    }
}
public function decrementSlotCount(Request $request)
{
    $request->validate([
        'date' => 'required|date',
        'time_range' => 'required|string',
    ]);

    try {
        \Log::info('Decrementing slot count for:', $request->all());

        // Try to find the record with exact match first
        $weekColor = DB::table('week_colors')
            ->where('date', $request->date)
            ->where('time', $request->time_range)
            ->first();

        // If not found, try with variations (remove spaces before AM/PM)
        if (!$weekColor) {
            $variation = str_replace([' AM', ' PM'], ['AM', 'PM'], $request->time_range);
            $weekColor = DB::table('week_colors')
                ->where('date', $request->date)
                ->where('time', $variation)
                ->first();
        }

        if (!$weekColor) {
            \Log::warning('Week color record not found for date: ' . $request->date . ' and time: ' . $request->time_range);
            return response()->json([
                'status' => 'error',
                'message' => 'Time slot not found'
            ], 404);
        }

        // Check if there are available slots
        if ($weekColor->time_slot <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'No available slots'
            ], 400);
        }

        // Decrement the slot count
        $newSlotCount = $weekColor->time_slot - 1;

        // Update using the same time format as found
        DB::table('week_colors')
            ->where('date', $request->date)
            ->where('time', $weekColor->time) // Use the actual time from database
            ->update([
                'time_slot' => $newSlotCount,
                'booked' => $newSlotCount <= 0 ? 1 : 0
            ]);

        \Log::info('Slot count decremented successfully. New count: ' . $newSlotCount);

        return response()->json([
            'status' => 'success',
            'message' => 'Slot count updated successfully',
            'new_slot_count' => $newSlotCount
        ]);

    } catch (\Exception $e) {
        \Log::error('Failed to decrement slot count: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to update slot count'
        ], 500);
    }
}

}