<?php

namespace App\Http\Controllers;

use App\Models\Appointment;      
use App\Models\AppointmentSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\LawOffice;
use App\Models\ArchivedAppointment;
use Illuminate\Support\Facades\Mail; // Add this line
use App\Mail\AppointmentStatusMail;
use App\Models\NotifApprovalAppointment; // Add this import
use App\Http\Controllers\AdminNotificationController;
use App\Models\SmsMessage;
use App\Http\Controllers\Admin\SmsChatController;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AppointmentController extends Controller
{

    public function store(Request $request)
{
    $validated = $request->validate([
        // your validation rules
    ]);
    
    $appointment = Appointment::create($validated);

     event(new AppointmentCreated($appointment));
    
    // IMPORTANT: Create admin notification
    AdminNotificationController::createForPendingAppointment($appointment);
    
    return response()->json([
        'success' => true,
        'message' => 'Appointment request submitted successfully!',
        'data' => $appointment
    ]);
}
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
                'branch' => 'required|string|max:255', // Add branch validation
            ]);

            // Clear any existing session data first
            $request->session()->forget(['fullname', 'address', 'phone', 'email', 'category', 'case_id', 
                                    'selected_case_id', 'selected_case_name', 'selected_category', 'branch']);

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
            }

            // Save the session to ensure persistence
            $request->session()->save();

            \Log::info('Session data stored:', [
                'fullname' => session('fullname'),
                'category' => session('category'),
                'case_id' => session('case_id'),
                'selected_case_name' => session('selected_case_name'),
                'selected_category' => session('selected_category'),
                'branch' => session('branch') // Log branch
            ]);

            // REMOVE THIS - Appointment creation should only happen in finalizeAppointment()
            // $appointment = Appointment::create($validated);
            
            // REMOVE THIS - Notification will be triggered when appointment is created in finalizeAppointment()
            // if ($appointment->appointment_approval == 'pending') {
            //     AdminNotificationController::createForPendingAppointment($appointment);
            // }

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
    public function getSched(Request $request)
    {
        $date = $request->date;
        $branch = $request->branch;

        // Choose table based on branch
        $table = $branch === 'Cordon Branch Office'
            ? 'cordon_time_slots'
            : 'diffun_time_slots';

        $schedules = DB::table($table)
            ->where('date', $date)
            ->orderBy('time')
            ->get([
                'time',
                'slot_number'   // ✅ ONLY THIS
            ]);

        return response()->json($schedules);
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

  
public function finalizeAppointment(Request $request)
{
    \Log::info('Finalize Appointment Request Data: ', $request->all());
    
    $validated = $request->validate([
        'fullname'        => 'required|string|max:255',
        'address'         => 'required|string|max:255',
        'phone'           => 'required|string|max:20',
        'email'           => 'required|email|max:255',
        'category'        => 'required|string|max:255',
        'case_name'       => 'required|string|max:255',
        'selected_branch' => 'required|string|max:255',
        'selected_date'   => 'required|string|min:10', // Accept date strings in YYYY-MM-DD format
        'selected_time'   => 'required|string',
        'id_front'        => 'required|image|mimes:jpg,jpeg,png|max:2048',
        'id_back'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    \Log::info('Validated data:', $validated);

    if ($this->isPastTimeSlot($request->selected_date, $request->selected_time)) {
        return back()
            ->withInput()
            ->withErrors([
                'selected_time' => 'The selected time slot has already passed. Please choose another available time.'
            ])
            ->with('error', 'The selected time slot has already passed. Please choose another available time.');
    }

    try {
        // First, decrement the slot count based on branch
        $branch = $request->selected_branch;
        
            if ($branch === 'Cordon Branch Office') {

            $cordonController = new \App\Http\Controllers\CordonCalendarController();

            $decrementResponse = $cordonController->decrementSlotCount(new Request([
                'date' => $request->selected_date,
                'time_range' => $request->selected_time
            ]));

        } else {

            $decrementResponse = $this->decrementSlotCount(new Request([
                'date' => $request->selected_date,
                'time_range' => $request->selected_time
            ]));

        }


        if ($decrementResponse->getStatusCode() !== 200) {
            $responseData = json_decode($decrementResponse->getContent(), true);
            throw new \Exception($responseData['message'] ?? 'Failed to reserve time slot');
        }

        // Then create the appointment
        $idFrontPath = $request->file('id_front')->store('ids', 'public');
        $idBackPath = $request->file('id_back') ? $request->file('id_back')->store('ids', 'public') : null;

        $appointment = Appointment::create([
            'fullname'             => $request->fullname,
            'address'              => $request->address,
            'phone'                => $request->phone,
            'email'                => $request->email,
            'category'             => $request->category,
            'case_name'            => $request->case_name,
            'selected_branch'      => $request->selected_branch,
            'selected_date'        => $request->selected_date,
            'selected_time'        => $request->selected_time,
            'term_status'          => $request->term_status ?? 'pending',
            'id_front'             => $idFrontPath,
            'id_back'              => $idBackPath,
            'appointment_approval' => 'pending',
        ]);

        // ✅ Create notification for admin
        AdminNotificationController::createForPendingAppointment($appointment);

        // Update user's law_office if not set
        $user = Auth::user();
        if ($user && empty($user->law_office)) {
            $user->law_office = $request->selected_branch;
            $user->save();
        }

        // Clear session data
        $request->session()->forget(['fullname', 'address', 'phone', 'email', 'category', 'case_id', 
                                   'selected_case_id', 'selected_case_name', 'selected_category', 'branch']);

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

    // Fetch law offices from database
    $lawOffices = LawOffice::select('id', 'law_office')->get();

    return view('appointment1', compact('fullname', 'phone', 'email', 'address', 'caseCategories', 'lawOffices'));
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

    $date = $request->query('date');
    $time = $request->query('time');
    $branch = $request->query('branch') ?? session('branch'); // Get branch from query or session

    $fullname = session('fullname');
    $address = session('address');
    $phone = session('phone');
    $email = session('email');
    $status_approval = session('status_approval');
    
    $selected_category = session('selected_category');
    $selected_case_name = session('selected_case_name');
    $selected_branch = $branch; // Store the branch

    return view('FinalizeAppointment', compact(
        'date',
        'time',
        'fullname',
        'address',
        'phone',
        'email',
        'status_approval',
        'selected_category',
        'selected_case_name',
        'selected_branch' // Pass branch to view
    ));
}

    public function showApprovedAppointments(Request $request)
    {
        $appointments = $this->buildApprovedAppointmentsQuery($request)
            ->orderBy('created_at', 'desc')
            ->get();

        $categories = DB::table('case_categories')->select('category')->distinct()->orderBy('category')->pluck('category');

        return view('adminAcceptedRequest', compact('appointments', 'categories'));
    }

    public function generateApprovedAppointmentsReport(Request $request)
    {
        $appointments = $this->buildApprovedAppointmentsQuery($request)
            ->orderBy('created_at', 'desc')
            ->get();

        $filterInfo = [
            'date' => $request->date ?: 'All Dates',
            'time' => $request->time ?: 'All Time Slots',
            'category' => $request->category ?: 'All Categories',
            'search' => $request->search ?: 'No search filter',
        ];

        $branch = 'Admin Overview';

        $pdf = Pdf::loadView('reports.accepted_appointments_report', compact('appointments', 'filterInfo', 'branch'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('admin_accepted_appointments_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }

    private function buildApprovedAppointmentsQuery(Request $request)
    {
        $query = Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['approved']);

        if ($request->filled('date')) {
            $query->where('selected_date', $request->date);
        }

        if ($request->filled('time')) {
            $query->where('selected_time', $request->time);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $searchTerm = trim($request->search);

            $query->where(function ($searchQuery) use ($searchTerm) {
                $likeSearch = '%' . $searchTerm . '%';

                $searchQuery->where('fullname', 'like', $likeSearch)
                    ->orWhere('address', 'like', $likeSearch)
                    ->orWhere('phone', 'like', $likeSearch)
                    ->orWhere('consulting', 'like', $likeSearch)
                    ->orWhere('selected_branch', 'like', $likeSearch)
                    ->orWhere('appointment_approval', 'like', $likeSearch);
            });
        }

        return $query;
    }

    public function showDenied()
    {
        $appointments = Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['denied'])->get();
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

    if ($this->isPastTimeSlot($request->selected_date, $request->selected_time)) {
        return back()
            ->withInput()
            ->withErrors([
                'selected_time' => 'The selected time slot has already passed. Please choose another available time.'
            ])
            ->with('error', 'The selected time slot has already passed. Please choose another available time.');
    }

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
        $appointments = \App\Models\Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['denied'])->get();
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
    $branch = session('branch'); // Get selected office/branch

    $date = session('schedule_date');
    $time = session('schedule_time');

    // Get the law_office_id from the law_office name
    $lawOfficeId = null;
    if ($branch) {
        $lawOffice = \App\Models\LawOffice::where('law_office', $branch)->first();
        if ($lawOffice) {
            $lawOfficeId = $lawOffice->id;
        }
    }

    return view('getsched', compact(
        'fullname',
        'address',
        'phone',
        'email',
        'consulting',
        'status_approval',
        'date',
        'time',
        'branch',
        'lawOfficeId'
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
        $appointments = DB::table('appointments')
            ->whereRaw('LOWER(appointment_approval) = ?', ['denied'])
            ->get();
        return view('diffun_staff.staffdeniedrequest', compact('appointments'));
    }

   public function staffDashboard()
{
    $user = Auth::user();
    
    // Add a check for authentication
    if (!$user) {
        return redirect()->route('login')->with('error', 'Please log in first.');
    }
    
    // Add a check if user is staff
    if (!in_array($user->role, ['cordon_staff', 'diffun_staff', 'staff'])) {
        return redirect()->route('welcome')->with('error', 'Unauthorized access.');
    }
    
    $branchName = $user->role === 'cordon_staff' ? 'Cordon Branch Office' : 'Diffun Branch Office';

    $pendingAppointments = Appointment::whereRaw("LOWER(TRIM(selected_branch)) = ?", [strtolower(trim($branchName))])
        ->whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['pending'])
        ->count();
    $approvedAppointments = Appointment::whereRaw("LOWER(TRIM(selected_branch)) = ?", [strtolower(trim($branchName))])
        ->whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['approved'])
        ->count();
    $deniedAppointments = Appointment::whereRaw("LOWER(TRIM(selected_branch)) = ?", [strtolower(trim($branchName))])
        ->whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['denied'])
        ->count();
    $totalAppointments = $pendingAppointments + $approvedAppointments + $deniedAppointments;

    $recentAppointments = Appointment::whereRaw("LOWER(TRIM(selected_branch)) = ?", [strtolower(trim($branchName))])
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

    // Return different views based on user role
    if ($user->role === 'cordon_staff') {
        return view('cordon_staff.dashboardStaff', compact(
            'totalAppointments',
            'pendingAppointments',
            'approvedAppointments',
            'deniedAppointments',
            'recentAppointments'
        ));
    } elseif ($user->role === 'diffun_staff') {
        return view('diffun_staff.dashboardStaff', compact(
            'totalAppointments',
            'pendingAppointments',
            'approvedAppointments',
            'deniedAppointments',
            'recentAppointments'
        ));
    } else {
        // Default fallback for any other staff role
        return view('diffun_staff.dashboardStaff', compact(
            'totalAppointments',
            'pendingAppointments',
            'approvedAppointments',
            'deniedAppointments',
            'recentAppointments'
        ));
    }
}

    public function lawyerOfficeRequests()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'lawyer') {
            return redirect()->route('login')->with('error', 'You must be logged in as a lawyer.');
        }

        if (!$user->law_office_id) {
            return redirect()->back()->with('error', 'Your lawyer account is not assigned to a law office.');
        }

        $officeId = $user->law_office_id;

        $pendingAppointments = Appointment::forOffice($officeId)
            ->withSelectedTime()
            ->byApproval('pending')
            ->count();

        $approvedAppointments = Appointment::forOffice($officeId)
            ->withSelectedTime()
            ->byApproval('approved')
            ->count();

        $deniedAppointments = Appointment::forOffice($officeId)
            ->withSelectedTime()
            ->byApproval('denied')
            ->count();

        return view('lawyer.office_requests', compact(
            'pendingAppointments',
            'approvedAppointments',
            'deniedAppointments'
        ));
    }

    public function approve(Request $request, $id)
    {
        \Log::info('Approve method called for appointment ID: ' . $id);
    $appointment = Appointment::findOrFail($id);
        // Prefer email/phone values posted from the frontend (use displayed values). Fall back to DB values.
        $postedEmail = trim($request->input('email', '') ?? '');
        $postedPhone = trim($request->input('phone', '') ?? '');

        if ($postedEmail) {
            \Log::info('Override recipient email from request payload', ['appointment_id' => $id, 'posted_email' => $postedEmail]);
            $recipientEmail = $postedEmail;
        } else {
            $recipientEmail = trim($appointment->email ?? '');
        }

        if ($postedPhone) {
            \Log::info('Override recipient phone from request payload', ['appointment_id' => $id, 'posted_phone' => $postedPhone]);
            $recipientPhone = $postedPhone;
        } else {
            $recipientPhone = trim($appointment->phone ?? '');
        }
    $appointment->appointment_approval = 'approved';
    $appointment->processed_by = Auth::user()->name ?? 'System';
    $appointment->save();
        $statusMessage = $this->buildAppointmentStatusMessage($appointment, 'approved');

        // Insert notification
        $this->insertApprovalNotification($appointment);

        // Create in-app notification for the user
        $user = User::where('email', $appointment->email)->first();
        if ($user) {
            NotificationController::createNotification(
                $user->id,
                'appointment_approved',
                $statusMessage
            );
        }
        $this->dispatchAppointmentStatusNotifications(
            $appointment->id,
            'approved',
            $recipientEmail,
            $recipientPhone,
            $user?->id
        );

        return response()->json([
            'success' => true,
            'email_sent' => null,
            'sms_sent' => null,
            'sms_status' => null,
            'notifications_queued' => true,
            'message' => $statusMessage,
            'admin_message' => 'Appointment approved successfully.'
        ]);
}

// Keep only ONE deny method
public function deny(\Illuminate\Http\Request $request, $id)
{
    \Log::info('Deny method called for appointment ID: ' . $id);
    $appointment = Appointment::findOrFail($id);

    // Prefer email/phone values posted from the frontend (use displayed values). Fall back to DB values.
    $postedEmail = trim($request->input('email', '') ?? '');
    $postedPhone = trim($request->input('phone', '') ?? '');

    if ($postedEmail) {
        \Log::info('Override recipient email from request payload', ['appointment_id' => $id, 'posted_email' => $postedEmail]);
        $recipientEmail = $postedEmail;
    } else {
        $recipientEmail = trim($appointment->email ?? '');
    }

    if ($postedPhone) {
        \Log::info('Override recipient phone from request payload', ['appointment_id' => $id, 'posted_phone' => $postedPhone]);
        $recipientPhone = $postedPhone;
    } else {
        $recipientPhone = trim($appointment->phone ?? '');
    }

    $appointment->appointment_approval = 'denied';
    $appointment->processed_by = Auth::user()->name ?? 'System';
    $appointment->save();
    $statusMessage = $this->buildAppointmentStatusMessage($appointment, 'denied');

    // Insert notification
    $this->insertApprovalNotification($appointment);

    // Create in-app notification for the user
    $user = User::where('email', $appointment->email)->first();
    if ($user) {
        NotificationController::createNotification(
            $user->id,
            'appointment_denied',
            $statusMessage
        );
    }
    $this->dispatchAppointmentStatusNotifications(
        $appointment->id,
        'denied',
        $recipientEmail,
        $recipientPhone,
        $user?->id
    );

    return response()->json([
        'success' => true,
        'email_sent' => null,
        'sms_sent' => null,
        'sms_status' => null,
        'notifications_queued' => true,
        'message' => $statusMessage,
        'admin_message' => 'Appointment denied successfully.'
    ]);
}

private function dispatchAppointmentStatusNotifications(
    int $appointmentId,
    string $status,
    ?string $recipientEmail,
    ?string $recipientPhone,
    ?int $userId = null
): void {
    app()->terminating(function () use ($appointmentId, $status, $recipientEmail, $recipientPhone, $userId) {
        $appointment = Appointment::find($appointmentId);

        if (!$appointment) {
            Log::warning('Skipping queued appointment notifications because appointment was not found.', [
                'appointment_id' => $appointmentId,
                'status' => $status,
            ]);
            return;
        }

        $statusMessage = $this->buildAppointmentStatusMessage($appointment, $status);
        $user = $userId ? User::find($userId) : User::where('email', $appointment->email)->first();

        try {
            $to = trim((string) $recipientEmail);

            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                Log::warning('Skipping appointment status email because recipient is invalid or empty.', [
                    'appointment_id' => $appointment->id,
                    'recipient' => $to,
                    'status' => $status,
                ]);
            } else {
                $fromAddress = env('MAIL_FROM_ADDRESS') ?: env('MAIL_USERNAME') ?: config('mail.from.address');
                $fromName = env('MAIL_FROM_NAME') ?: config('mail.from.name') ?: 'LegalConnect';
                $mailable = new AppointmentStatusMail($appointment, $status);

                if ($fromAddress) {
                    $mailable->from($fromAddress, $fromName);
                }

                Log::info('Sending appointment status email after response.', [
                    'appointment_id' => $appointment->id,
                    'status' => $status,
                    'to' => $to,
                ]);

                Mail::to($to)->send($mailable);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send appointment status email after response: ' . $e->getMessage());
        }

        try {
            $smsController = new SmsChatController();
            $formattedPhone = $smsController->formatPhoneForApi($recipientPhone ?: $appointment->phone);
            $smsText = "Hello {$appointment->fullname}, {$statusMessage} - LegalConnect";
            $smsResponse = $smsController->sendViaIprog($formattedPhone, $smsText);
            $smsSent = (bool) ($smsResponse['success'] ?? false);
            $smsStatus = $smsResponse['status'] ?? ($smsResponse['response']['status'] ?? null);

            try {
                $currentUser = Auth::user();
                SmsMessage::create([
                    'sender_id' => $currentUser ? $currentUser->id : null,
                    'receiver_id' => $user ? $user->id : ($currentUser ? $currentUser->id : null),
                    'phone_number' => $recipientPhone ?: $appointment->phone,
                    'message' => $smsText,
                    'message_type' => 'outgoing',
                    'status' => $smsStatus ?? ($smsSent ? 'sent' : 'failed'),
                    'message_id' => $smsResponse['message_id'] ?? $smsResponse['id'] ?? null
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to save SmsMessage record after response: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Failed to send appointment status SMS after response: ' . $e->getMessage());
        }
    });
}

private function buildAppointmentStatusMessage($appointment, string $status): string
{
    $caseName = trim((string) ($appointment->case_name ?? 'your selected case'));
    $category = trim((string) ($appointment->category ?? 'your selected category'));
    $status = strtolower(trim($status)) === 'denied' ? 'denied' : 'approved';
    $serviceFeeText = $this->getAppointmentServiceFeeText($appointment);

    return "Your appointment request for {$caseName} under {$category} has been {$status}. Service Fee: {$serviceFeeText}.";
}

private function getAppointmentServiceFeeText($appointment): string
{
    $caseName = trim((string) ($appointment->case_name ?? ''));
    $category = trim((string) ($appointment->category ?? ''));

    if ($caseName === '' || $category === '') {
        return 'Not set yet';
    }

    $serviceFee = DB::table('case_categories')
        ->where('category', $category)
        ->where('case_name', $caseName)
        ->value('service_fee');

    if ($serviceFee === null || $serviceFee === '') {
        return 'Not set yet';
    }

    return "\u{20B1}" . number_format((float) $serviceFee, 2);
}
   
    

    public function reaccept($id)
    {
        \Log::info('Reaccept method called for appointment ID: ' . $id);
        $appointment = Appointment::findOrFail($id);
        $appointment->appointment_approval = 'approved';
        $appointment->processed_by = Auth::user()->name ?? 'System';
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

    foreach ($weekColors as $wc) {
        $formattedColors[$wc->time] = [
            'color' => $wc->color,
            'description' => 'Available slots: ' . (int) $wc->slot_number,
            'booked' => $wc->booked,
            'slot_number' => (int) $wc->slot_number
        ];
    }

    
    return response()->json($formattedColors);
}

public function getWeekColors(Request $request)
{
    $date = $request->query('date'); // Format: YYYY-MM-DD
    
    $weekColors = DB::table('week_colors')
        ->where('date', $date)
        ->select('time','color','description','booked','time_slot','slot_number') // Ensure slot_number is included
        ->orderBy('time')
        ->get();
    
    return response()->json($weekColors); // This returns an array
}

public function bookWeekSlot(Request $request)
{
    $validated = $request->validate([
        'date' => 'required|date',
        'time_range' => 'required|string',
        'office_id' => 'required|integer|exists:law_offices,id'
    ]);

    try {
        \Log::info('Booking week slot:', $validated);

        if ($this->isPastTimeSlot($validated['date'], $validated['time_range'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'This time slot has already passed. Please choose another available time.'
            ], 422);
        }
        
        // Check if the slot exists and is available
        // Note: week_colors table does NOT have law_office_id column
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
        if ($weekSlot->booked == 1 && (int) $weekSlot->slot_number <= 0) {
            return response()->json([
                'status' => 'error', 
                'message' => 'This time slot is already full. Please choose another time.'
            ]);
        }

        // Check if the slot is green (available)
        if ($weekSlot->color !== 'green') {
            return response()->json([
                'status' => 'error', 
                'message' => 'This time slot is not available. Please choose a green time slot.'
            ]);
        }

        // 🔥 FIX: Check slot_number for available slots, not time_slot
        if ($weekSlot->slot_number <= 0) {
            return response()->json([
                'status' => 'error', 
                'message' => 'This time slot is already full.'
            ]);
        }

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

        if ($this->isPastTimeSlot($request->date, $request->time_range)) {
            return response()->json([
                'status' => 'error',
                'message' => 'This time slot has already passed. Please choose another available time.'
            ], 422);
        }

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

        if (($weekColor->color ?? null) !== 'green') {
            return response()->json([
                'status' => 'error',
                'message' => 'This time slot is not available.'
            ], 400);
        }

        // 🔥 FIX: Check slot_number for available slots, not time_slot
        if ($weekColor->slot_number <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'This time slot is already full.'
            ], 400);
        }

        // 🔥 FIX: Decrement slot_number, not time_slot
        $newSlotCount = $weekColor->slot_number - 1;

        // Update using the same time format as found
        DB::table('week_colors')
            ->where('date', $request->date)
            ->where('time', $weekColor->time)
            ->update([
                'slot_number' => $newSlotCount, // 🔥 FIX: Update slot_number
                'booked' => $newSlotCount <= 0 ? 1 : 0
            ]);

        \Log::info('Slot count decremented successfully. New slot_number: ' . $newSlotCount);

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

private function isPastTimeSlot(?string $date, ?string $timeRange): bool
{
    $slotStart = $this->parseTimeSlotStart($date, $timeRange);

    if (!$slotStart) {
        return false;
    }

    return $slotStart->lt(Carbon::now(config('app.timezone', 'Asia/Manila')));
}

private function parseTimeSlotStart(?string $date, ?string $timeRange): ?Carbon
{
    if (!$date || !$timeRange) {
        return null;
    }

    $parts = preg_split('/\s*-\s*/', $timeRange);
    $startTime = trim($parts[0] ?? '');

    if ($startTime === '') {
        return null;
    }

    $normalizedStartTime = strtoupper(preg_replace('/\s*([AP]M)$/i', ' $1', $startTime));

    try {
        return Carbon::createFromFormat(
            'Y-m-d g:i A',
            "{$date} {$normalizedStartTime}",
            config('app.timezone', 'Asia/Manila')
        );
    } catch (\Exception $e) {
        Log::warning('Unable to parse appointment time slot.', [
            'date' => $date,
            'time_range' => $timeRange,
            'error' => $e->getMessage(),
        ]);

        return null;
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
}
