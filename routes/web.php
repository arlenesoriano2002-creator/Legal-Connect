<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentSlotController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ClientRegisterController;
use App\Http\Controllers\CustomLoginController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\ClientTableController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\UserRegisterController;
use App\Http\Controllers\AdminDashboardController;
use App\Models\Appointment;
use App\Http\Controllers\ClientMessageController;
use App\Models\ArchivedAppointment;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FeedbackChartController;
use App\Http\Controllers\BackupArchivedController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\FetchAppointmentsController;
use App\Http\Controllers\EmailReceiverController;
use App\Http\Controllers\EmailSenderController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CaseCategoryController;
use App\Http\Controllers\ChatController;
//-----------------
// DEFAULT ROUTE
//-----------------
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

//-----------------
// STATIC PAGES
//-----------------
Route::view('/welcome', 'welcome')->name('welcome');
Route::view('/about', 'about')->name('about');
Route::view('/contact', 'contact')->name('contact');
Route::view('/Terms', 'Terms')->name('Terms');

//-----------------
// AUTHENTICATION
//-----------------
// USER AUTH
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// REGISTRATION & OTP
Route::get('/register', [UserRegisterController::class, 'showForm'])->name('register.form');
Route::post('/register', [UserRegisterController::class, 'register'])->name('register');
Route::get('/verify-otp', [UserRegisterController::class, 'showOtpForm'])->name('otp.form');
Route::post('/verify-otp', [UserRegisterController::class, 'verifyOtp'])->name('otp.verify');
Route::post('/resend-otp', [UserRegisterController::class, 'resendOtp'])->name('otp.resend');

// FORGOT PASSWORD
Route::get('/forgot-password', [ForgotPasswordController::class, 'showEmailForm'])->name('password.request');
Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp'])->name('password.send-otp');
Route::get('/forgot-password/otp', [ForgotPasswordController::class, 'showOtpForm'])->name('password.otp');
Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verify-otp');
Route::get('/forgot-password/reset', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset.submit');

//-----------------
// PROTECTED USER ROUTES
//-----------------
Route::middleware(['auth'])->group(function () {
    // APPOINTMENTS
    Route::get('/appointment1', [AppointmentController::class, 'showAppointmentForm'])->name('appointment1');
    
    // MESSAGING
    Route::get('/messages', [ClientMessageController::class, 'index'])->name('messages.page');
    Route::post('/messages/send', [ClientMessageController::class, 'sendMessage'])->name('client.sendMessage');
    
    // NOTIFICATIONS
    Route::get('/notifications/appointments', [NotificationController::class, 'getUserAppointmentNotifications'])->name('notifications.appointments');
    Route::get('/notifications/approval-history', [NotificationController::class, 'getUserApprovalNotifications'])->name('notifications.approval-history');
    Route::get('/notifications', [NotificationController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::get('/account-info', [AccountController::class, 'getAccountInfo'])->name('account.info');
});

//-----------------
// ADMIN PROTECTED ROUTES
//-----------------
Route::middleware(['auth'])->group(function () {
    // DASHBOARDS
    Route::get('/admindashboard', [AdminDashboardController::class, 'index'])->name('admindashboard');
    Route::view('/superadministrator', 'superadministrator')->name('superadmin.page');
    Route::view('/administrator', 'administrator')->name('admin.page');
    
    // ADMIN ACCOUNT
    Route::get('/adminAccount', [AdminAccountController::class, 'show'])->name('adminAccount');
    Route::put('/adminAccount/update', [AdminAccountController::class, 'update'])->name('adminAccount.update');

    // CLIENT MANAGEMENT - APPROVAL ROUTES
    Route::post('/appointments/{id}/approve', [ClientTableController::class, 'approve'])->name('appointments.approve');
    Route::post('/appointments/{id}/deny', [ClientTableController::class, 'deny'])->name('appointments.deny');

    // CLIENT MANAGEMENT PAGES
    Route::get('/clientstbl', [ClientTableController::class, 'index'])->name('clientstbl');
    Route::get('/adminAcceptedRequest', [AppointmentController::class, 'showApprovedAppointments'])->name('adminAcceptedRequest');
    Route::get('/adminDeniedRequest', [AppointmentController::class, 'showDeniedAppointments'])->name('adminDeniedRequest');

    // ARCHIVED APPOINTMENTS
    Route::get('/admin/archived', function () {
        $admin = Auth::user();
        if (!$admin || !in_array($admin->role, ['admin', 'superadmin'])) {
            abort(403, 'Unauthorized.');
        }

        $recentArchived = ArchivedAppointment::orderBy('created_at', 'desc')->get();

        $backupDir = storage_path('app/backups');
        $backups = [];
        if (File::exists($backupDir)) {
            $files = File::files($backupDir);
            usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
            foreach ($files as $f) {
                $backups[] = [
                    'name' => basename($f),
                    'mtime' => date('Y-m-d H:i:s', filemtime($f)),
                    'size' => filesize($f),
                ];
            }
        }

        return view('archivedAppointments', compact('recentArchived', 'backups'));
    })->name('archived.index');

    Route::get('/admin/download-backup/{filename}', function ($filename) {
        $admin = Auth::user();
        if (!$admin || !in_array($admin->role, ['admin', 'superadmin'])) {
            abort(403, 'Unauthorized access.');
        }

        $path = storage_path('app/backups/' . $filename);
        if (!File::exists($path)) {
            abort(404, 'File not found.');
        }

        return response()->download($path, $filename);
    })->name('admin.download.backup');

    // ADMIN MESSAGING
    Route::get('/messages', [ClientMessageController::class, 'index'])->name('messages.page');
    Route::post('/messages/send', [ClientMessageController::class, 'sendMessage'])->name('admin.sendMessage');

    // BACKUP ROUTES
    Route::post('/admin/create-backup', [AdminDashboardController::class, 'createBackup'])->name('admin.createBackup');
    Route::get('/admin/get-backups', [AdminDashboardController::class, 'getBackups'])->name('admin.getBackups');
    Route::post('/admin/download-backup', [AdminDashboardController::class, 'downloadBackup'])->name('admin.downloadBackup');
    Route::get('/admin/backups/refresh', [AdminDashboardController::class, 'refreshBackups']);
    
    // NEW BACKUP ROUTE FOR APPOINTMENTS
    Route::post('/appointments/backup', [BackupArchivedController::class, 'createAppointmentsBackup'])->name('appointments.backup');
});

//-----------------
// APPOINTMENT ROUTES
//-----------------
Route::get('/Terms', [TermsController::class, 'show'])->name('Terms');
Route::post('/terms/accept', [TermsController::class, 'accept'])->name('terms.accept');
Route::get('/appointment1', [AppointmentController::class, 'showAppointmentForm'])->name('appointment1');
Route::post('/appointment/step1', [AppointmentController::class, 'storeStep1'])->name('appointment.storeStep1');
Route::get('/getsched', [AppointmentController::class, 'showGetSched'])->name('getsched');
Route::get('/FinalizeAppointment', [AppointmentController::class, 'showFinalizePage'])->name('appointment.finalizePage');
Route::post('/finalize-appointment', [AppointmentController::class, 'finalizeAppointment'])->name('appointment.finalize');

// APPOINTMENT SLOTS
Route::get('/appointment-slots', [AppointmentSlotController::class, 'index']);
Route::post('/store-availability', [AppointmentSlotController::class, 'store']);
Route::delete('/delete-availability', [AppointmentSlotController::class, 'deleteAvailability']);
Route::put('/edit-slot/{id}', [AppointmentSlotController::class, 'update']);
Route::delete('/delete-slot/{id}', [AppointmentSlotController::class, 'destroyById']);
Route::get('/available-times/{date}', [AppointmentSlotController::class, 'getAvailableTimes']);
Route::post('/book-slot', [AppointmentSlotController::class, 'bookSlot']);
Route::post('/unbook-slot', [AppointmentSlotController::class, 'unbookSlot']);
Route::get('/appointment-slots/{id}', [AppointmentSlotController::class, 'show']);
Route::put('/appointment-slots/{id}', [AppointmentSlotController::class, 'update']);

// APPOINTMENT ACTIONS
Route::post('/appointments/{id}/deny-appointment', [AppointmentController::class, 'denyAppointment'])->name('appointments.denyAppointment');
Route::post('/appointments/reaccept/{id}', [AppointmentController::class, 'reaccept'])->name('appointments.reaccept');
Route::delete('/appointments/delete/{id}', [AppointmentController::class, 'delete'])->name('appointments.delete');
Route::get('/appointments/{id}', [AppointmentController::class, 'show'])->name('appointments.show');
Route::post('/appointments/update/{id}', [AppointmentController::class, 'update'])->name('appointments.update');
Route::post('/appointments/new-store', [AppointmentController::class, 'newStore'])->name('appointments.newStore');

//-----------------
// ARCHIVED & BACKUP ROUTES
//-----------------
Route::get('/archivedAppointments', fn() => redirect('/admin/archived'));
Route::delete('/archived/delete/{id}', [AppointmentController::class, 'deleteArchived'])->name('archived.delete');
Route::post('/admin/appointments/archive/{id}', [AppointmentController::class, 'archiveAppointment'])->name('archive.appointment');
Route::post('/appointments/archiveDenied/{id}', [AppointmentController::class, 'archiveDenied'])->name('appointments.archiveDenied');
Route::post('/appointments/archiveDeniedAppointment/{id}', [AppointmentController::class, 'archiveDeniedAppointment'])->name('appointments.archiveDeniedAppointment');

// BACKUP ROUTES (Public ones)
Route::get('/admin/get-backups', [BackupArchivedController::class, 'getBackups'])->name('admin.getBackups');
Route::get('/admin/backup/download/{filename}', [BackupArchivedController::class, 'downloadBackupAsPdf'])->name('admin.backup.download');
Route::delete('/admin/backup/delete/{filename}', [BackupArchivedController::class, 'deleteBackup'])->name('admin.backup.delete');

//-----------------
// STAFF ROUTES
//-----------------
Route::get('/staff', function () {
    return view('staff.staff');
})->name('staff');

Route::get('/dashboardStaff', [AppointmentController::class, 'staffDashboard'])->name('dashboardStaff');
Route::get('/StaffClientstbl', function () {
    $appointments = DB::table('appointments')
        ->where('appointment_approval', 'pending')
        ->get();
    return view('staff.StaffClientstbl', compact('appointments'));
})->name('staff.clients.pending');

Route::get('/staff/accepted-requests', function () {
    $appointments = DB::table('appointments')
        ->whereRaw('LOWER(appointment_approval) = ?', ['approved'])
        ->get();
    return view('staff.staffAcceptedRequest', compact('appointments'));
})->name('staff.acceptedRequests');

Route::get('/staff/denied-requests', [AppointmentController::class, 'deniedRequests'])->name('staff.deniedRequests');
Route::get('/staffAccount', function () {
    return view('staff.staffAccount');
});

//-----------------
// MESSAGING & REVIEWS
//-----------------
Route::post('/submit-message', [MessageController::class, 'store'])->name('message.store');
Route::post('/submit-review', [ReviewController::class, 'store'])->name('review.store');
Route::get('/testimonial', [ReviewController::class, 'index'])->name('review.index');
Route::post('/messaging/send-message', [ClientMessageController::class, 'sendMessage'])->name('messaging.sendMessage');
Route::post('/send-message', [ClientMessageController::class, 'sendMessage'])->name('client.sendMessage');

//-----------------
// FEEDBACK & ANALYTICS
//-----------------
Route::get('/feedback-data', [FeedbackChartController::class, 'getFeedbackData']);

//-----------------
// FILE SERVING
//-----------------
Route::get('/storage/images/{filename}', function ($filename) {
    $path = storage_path('app/public/ids/' . $filename);
    
    if (!File::exists($path)) {
        abort(404);
    }
    
    $file = File::get($path);
    $type = File::mimeType($path);
    
    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);
    
    return $response;
})->name('storage.image');

//-----------------
// CUSTOM LOGOUT
//-----------------
Route::post('/custom-logout', function () {
    session()->forget('showAppointmentButton');
    session()->forget('otp_verified');
    Auth::logout();
    return redirect('/welcome');
})->name('custom.logout');

Route::get('/test-email', function () {
    try {
        \Mail::raw('Test email from LegalConnect', function ($message) {
            $message->to('cafirma.jerome2002@gmail.com')
                    ->subject('Test Email');
        });
        return 'Test email sent successfully!';
    } catch (\Exception $e) {
        return 'Email error: ' . $e->getMessage();
    }
});

///fetch appointments data
Route::get('/appointments', [FetchAppointmentsController::class, 'index'])->name('appointments.index');
Route::get('/api/appointments', [FetchAppointmentsController::class, 'getAppointments'])->name('appointments.list');
Route::get('/api/appointments/{id}', [FetchAppointmentsController::class, 'getAppointmentDetails'])->name('appointments.details');
Route::get('/backup/download/{id}', [BackupArchivedController::class, 'downloadBackupFile'])->name('backup.download');

// route to admin protected routes section
Route::delete('/admin/backup/delete-by-id/{id}', [BackupArchivedController::class, 'deleteBackupById'])->name('admin.backup.delete.byid');

Route::get('/debug-backups', function() {
    $backups = \App\Models\Backup::all();
    echo "<h1>Backups Data Structure</h1>";
    foreach($backups as $backup) {
        echo "<pre>";
        print_r([
            'id' => $backup->id,
            'file_name_encrypted' => $backup->file_name,
            'file_name_decrypted' => $backup->decrypted_file_name,
            'created_at' => $backup->created_at,
            'type' => get_class($backup)
        ]);
        echo "</pre>";
    }
});

// ADMIN ACCOUNT MANAGEMENT ROUTES
Route::get('/adminAccount', [AdminAccountController::class, 'show'])->name('adminAccount');
Route::put('/adminAccount/update', [AdminAccountController::class, 'update'])->name('adminAccount.update');
Route::post('/adminAccount/staff/create', [AdminAccountController::class, 'createStaff'])->name('adminAccount.staff.create');
Route::put('/adminAccount/staff/update/{id}', [AdminAccountController::class, 'updateStaff'])->name('adminAccount.staff.update');
Route::delete('/adminAccount/staff/delete/{id}', [AdminAccountController::class, 'deleteStaff'])->name('adminAccount.staff.delete');
Route::get('/adminAccount/search', [AdminAccountController::class, 'searchStaff'])->name('adminAccount.search');

// Calendar color routes

Route::middleware('auth')->get('/chat/messages/download/{message}', [ChatController::class, 'downloadFile'])->name('chat.messages.download');

Route::get('/debug-db-structure', function() {
    $structure = DB::select("DESCRIBE month_colors");
    return response()->json([
        'table_structure' => $structure,
        'sample_records' => DB::table('month_colors')->limit(5)->get()
    ]);
});

// Month view routes
Route::get('/calendar/month/colors', [CalendarController::class, 'getMonthColors']);
Route::get('/calendar/date-data', [CalendarController::class, 'getDateData']);
Route::post('/calendar/save-date-data', [CalendarController::class, 'saveDateData']);

// Week view routes  
Route::get('/calendar/week/load-data', [CalendarController::class, 'loadWeekData']);

Route::get('/debug-calendar-response/{month}', function($month) {
    $colors = DB::table('month_colors')
        ->where('month', $month)
        ->whereNotNull('color')
        ->where('color', '!=', '')
        ->select('date', 'color', 'description')
        ->get()
        ->mapWithKeys(function ($row) {
            return [$row->date => [
                'color' => $row->color,
                'description' => $row->description
            ]];
        });

    return response()->json([
        'debug_info' => 'Raw database query result',
        'month' => $month,
        'colors_count' => $colors->count(),
        'colors_data' => $colors,
        'response_structure' => [
            'status' => 'success',
            'data' => $colors
        ]
    ]);
});

Route::post('/decrement-slot-count', [AppointmentController::class, 'decrementSlotCount'])->name('decrement.slot.count');
Route::post('/fix-existing-week-colors', [CalendarController::class, 'fixExistingWeekColors']);

// Add to web.php for debugging
Route::get('/debug-week-colors/{date}', function($date) {
    $colors = DB::table('week_colors')
        ->where('date', $date)
        ->select('time', 'color', 'description', 'booked', 'time_slot')
        ->get();
    
    return response()->json([
        'date' => $date,
        'records' => $colors,
        'count' => $colors->count()
    ]);
});

// Debug route to check month_colors data structure
Route::get('/debug-month-data/{month}', function($month) {
    $data = DB::table('month_colors')
        ->where('month', $month)
        ->whereNotNull('date_color')
        ->select('date', 'date_color', 'date_description')
        ->get()
        ->mapWithKeys(function ($row) {
            return [$row->date => [
                'color' => $row->date_color,
                'description' => $row->date_description
            ]];
        });
    
    return response()->json([
        'month' => $month,
        'data_structure' => $data,
        'sample_record' => $data->first(),
        'count' => $data->count()
    ]);
});

// New booking route for week_colors system
Route::post('/appointment/book-week-slot', [AppointmentController::class, 'bookWeekSlot'])->name('appointment.book.week.slot');

// CALENDAR ROUTES
Route::get('/calendar/week/colors', [AppointmentController::class, 'getWeekColors']);

// Terms routes
Route::get('/Terms', [TermsController::class, 'show'])->name('Terms');
Route::post('/terms/accept', [TermsController::class, 'accept'])->name('terms.accept');

// Temporary debug route - remove after fixing
Route::get('/debug-session', function() {
    return response()->json([
        'status_approval' => session('status_approval'),
        'session_id' => session()->getId(),
        'all_session_data' => session()->all(),
    ]);
});

//IDBACK & IDFRONT

// Replace the existing image route with this one
Route::get('/storage/ids/{filename}', function ($filename) {
    $path = public_path('storage/ids/' . $filename);
    
    if (!file_exists($path)) {
        abort(404, "File not found: " . $filename);
    }
    
    // Check if file is an image
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    $mime = mime_content_type($path);
    
    if (!in_array($mime, $allowedMimes)) {
        abort(403, 'Invalid file type');
    }
    
    // Set appropriate headers
    $headers = [
        'Content-Type' => $mime,
        'Content-Disposition' => 'inline', // Display in browser instead of download
        'Cache-Control' => 'public, max-age=3600', // Cache for 1 hour
    ];
    
    return response()->file($path, $headers);
})->name('storage.ids')->where('filename', '.*');

// Debug routes for image issues
Route::get('/debug-file-permissions/{filename}', function ($filename) {
    $path = public_path('storage/ids/' . $filename);
    $publicPath = public_path('storage/ids');
    
    return [
        'filename' => $filename,
        'full_path' => $path,
        'file_exists' => file_exists($path),
        'is_readable' => is_readable($path),
        'file_size' => file_exists($path) ? filesize($path) : 0,
        'directory_exists' => file_exists($publicPath),
        'directory_permissions' => file_exists($publicPath) ? substr(sprintf('%o', fileperms($publicPath)), -4) : 'N/A',
        'file_permissions' => file_exists($path) ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A',
        'direct_url' => url('/storage/ids/' . $filename),
    ];
});

// Test if we can serve a known good image
// Updated route to handle full paths including 'ids/' prefix
Route::get('/storage/{path}', function ($path) {
    $fullPath = public_path('storage/' . $path);
    
    if (!file_exists($fullPath)) {
        abort(404, "File not found: " . $path);
    }
    
    // Check if file is an image
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    $mime = mime_content_type($fullPath);
    
    if (!in_array($mime, $allowedMimes)) {
        abort(403, 'Invalid file type');
    }
    
    // Set appropriate headers
    $headers = [
        'Content-Type' => $mime,
        'Content-Disposition' => 'inline',
        'Cache-Control' => 'public, max-age=3600',
    ];
    
    return response()->file($fullPath, $headers);
})->where('path', '.*')->name('storage.path');

// Check actual file structure
Route::get('/debug-storage-structure', function () {
    $storagePath = public_path('storage');
    $idsPath = public_path('storage/ids');
    
    $structure = [
        'storage_path' => $storagePath,
        'storage_exists' => file_exists($storagePath),
        'ids_path' => $idsPath,
        'ids_exists' => file_exists($idsPath),
    ];
    
    // Check if files exist with both paths
    $sampleFiles = [
        'Wgfx8Vazj031wisbUlvBJDRBuinIIUVlxXsIk1jN.png',
        'QaPEJGJ5O1hLyN8UMtz70aJgIl5GGtgYObkQyiFk.png'
    ];
    
    foreach ($sampleFiles as $file) {
        $structure['files'][$file] = [
            'direct_path' => public_path('storage/ids/' . $file),
            'direct_exists' => file_exists(public_path('storage/ids/' . $file)),
            'full_path' => public_path('storage/ids/' . $file),
            'full_exists' => file_exists(public_path('storage/ids/' . $file)),
            'with_ids_prefix' => public_path('storage/ids/ids/' . $file),
            'with_ids_prefix_exists' => file_exists(public_path('storage/ids/ids/' . $file)),
        ];
    }
    
    // List first 10 files in ids directory
    if (file_exists($idsPath)) {
        $files = scandir($idsPath);
        $structure['actual_files_in_ids'] = array_slice(array_diff($files, ['.', '..']), 0, 10);
    }
    
    return $structure;
});

Route::get('/debug-routes', function() {
    $routes = collect(Route::getRoutes())->map(function ($route) {
        return [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $route->getActionName(),
        ];
    })->filter(function ($route) {
        return str_contains($route['uri'], 'appointments');
    });
    
    return response()->json($routes->values());
});

// Email Chat Routes
Route::get('/emails/fetch', [EmailReceiverController::class, 'fetchEmails'])->name('emails.fetch');
Route::get('/email/conversation/{email}', [EmailReceiverController::class, 'getEmailConversation'])->name('email.conversation');
Route::post('/email/send-chat', [EmailSenderController::class, 'sendEmailFromChat'])->name('email.send.chat');
Route::get('/email-chat', [EmailReceiverController::class, 'getEmailChatView'])->name('email.chat');

Route::get('/fetch-emails', [EmailReceiverController::class, 'fetchEmails']);
Route::post('/send-email', [EmailSenderController::class, 'sendEmailFromChat']);

// Email Chat Interface
Route::get('/email-chat', function () {
    // Fetch regular users (excluding admin and staff)
    $users = DB::table('users')
                ->whereNotIn('role', ['admin', 'staff'])
                ->select('id', 'name', 'email')
                ->get();

    // Fetch existing email conversations
    $emailConversations = DB::table('chattbl')
                ->where('sender_role', 'email') // incoming emails
                ->orWhere(function($query) {
                    $query->whereNotNull('receiver_email')
                          ->where('sender_role', '!=', 'email');
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function($message) {
                    // Group by sender_email for incoming, receiver_email for outgoing
                    if ($message->sender_role === 'email') {
                        return $message->sender_email;
                    } else {
                        return $message->receiver_email;
                    }
                });
                
    return view('email-chat', compact('users', 'emailConversations'));
})->name('email.chat');

// Add this to your web.php temporarily
Route::get('/fix-email-chat-db', function () {
    try {
        // Check if columns exist
        $columns = Schema::getColumnListing('chattbl');
        
        $missingColumns = [];
        if (!in_array('sender_email', $columns)) {
            $missingColumns[] = 'sender_email';
        }
        if (!in_array('sender_name', $columns)) {
            $missingColumns[] = 'sender_name';
        }
        if (!in_array('receiver_email', $columns)) {
            $missingColumns[] = 'receiver_email';
        }

        if (empty($missingColumns)) {
            return "All required columns exist in chattbl table.";
        }

        // Add missing columns
        Schema::table('chattbl', function ($table) use ($missingColumns) {
            if (in_array('sender_email', $missingColumns)) {
                $table->string('sender_email', 255)->nullable()->after('sender_id');
            }
            if (in_array('sender_name', $missingColumns)) {
                $table->string('sender_name', 255)->nullable()->after('sender_email');
            }
            if (in_array('receiver_email', $missingColumns)) {
                $table->string('receiver_email', 255)->nullable()->after('receiver_id');
            }
        });

        return "Added missing columns: " . implode(', ', $missingColumns);
        
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// Emergency database fix route
Route::get('/fix-email-chat-now', function () {
    try {
        Schema::table('chattbl', function ($table) {
            if (!Schema::hasColumn('chattbl', 'sender_email')) {
                $table->string('sender_email', 255)->nullable()->after('sender_id');
            }
            if (!Schema::hasColumn('chattbl', 'sender_name')) {
                $table->string('sender_name', 255)->nullable()->after('sender_email');
            }
        });
        
        return "✅ Database columns added successfully!";
    } catch (\Exception $e) {
        return "❌ Error: " . $e->getMessage();
    }
});

Route::get('/test-email-sending', function () {
    try {
        \Mail::raw('Test email body', function ($message) {
            $message->to('cafirma.jerome2002@gmail.com')
                   ->subject('Test Email from LegalConnect')
                   ->from(
                       env('MAIL_FROM_ADDRESS', 'noreply@legalconnect.com'),
                       env('MAIL_FROM_NAME', 'LegalConnect')
                   );
        });
        return 'Test email sent successfully!';
    } catch (\Exception $e) {
        return 'Email error: ' . $e->getMessage();
    }
});

// Debug route to check email system status
Route::get('/debug-email-system', function() {
    $status = [
        'imap_configured' => !empty(env('IMAP_USERNAME')) && !empty(env('IMAP_PASSWORD')),
        'mail_configured' => !empty(env('MAIL_USERNAME')) && !empty(env('MAIL_PASSWORD')),
        'current_user' => Auth::check() ? [
            'id' => Auth::id(),
            'email' => Auth::user()->email,
            'name' => Auth::user()->name
        ] : null,
        'chattbl_count' => DB::table('chattbl')->count(),
        'recent_messages' => DB::table('chattbl')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(),
        'chattbl_columns' => Schema::getColumnListing('chattbl')
    ];
    
    return response()->json($status);
});

Route::post('/appointments/backup-pdf', [BackupArchivedController::class, 'createAppointmentsBackupPdf'])->name('appointments.backup-pdf');
Route::get('/backup/view/{id}', [BackupArchivedController::class, 'viewBackupFile'])->name('backup.view');

// create excel file
Route::post('/appointments/backup-excel', [BackupArchivedController::class, 'createAppointmentsBackupExcel']);

// Practice Areas Routes
Route::get('/practice-areas', 'App\Http\Controllers\CaseCategoryController@index')->name('practice-areas');
Route::post('/practice-areas/category', [CaseCategoryController::class, 'storeCategory'])->name('practice-areas.storeCategory');
Route::put('/practice-areas/category/{oldCategory}', [CaseCategoryController::class, 'updateCategory'])->name('practice-areas.updateCategory');
Route::delete('/practice-areas/category/{category}', [CaseCategoryController::class, 'destroyCategory'])->name('practice-areas.destroyCategory');
Route::post('/practice-areas/case', [CaseCategoryController::class, 'storeCase'])->name('practice-areas.storeCase');
Route::put('/practice-areas/case/{id}', [CaseCategoryController::class, 'updateCase'])->name('practice-areas.updateCase');
Route::delete('/practice-areas/case/{id}', [CaseCategoryController::class, 'destroyCase'])->name('practice-areas.destroyCase');
Route::get('/practice-areas/category/{category}/cases', [CaseCategoryController::class, 'getCategoryCases'])->name('practice-areas.getCategoryCases');

// ADMIN PROTECTED ROUTES section
Route::get('/messages/email', function() {
    return redirect('/email-chat');
})->name('messages.email');

Route::get('/messages/sms', function() {
    // Placeholder for SMS - could redirect to email-chat or show a message
    return redirect('/email-chat')->with('info', 'SMS feature coming soon');
})->name('messages.sms');

Route::get('/messages/system-chat', function() {
    // Placeholder for System Chat
    return redirect('/email-chat')->with('info', 'System Chat feature coming soon');
})->name('messages.system-chat');

//-----------------
// CHAT ROUTES
//-----------------
Route::middleware(['auth'])->group(function () {
// Admin Chat Routes
Route::get('/admin/system-chat', [ChatController::class, 'adminIndex'])->name('admin.system-chat');
Route::get('/admin/chat/conversations', [ChatController::class, 'adminGetConversations'])->name('admin.chat.conversations');
Route::get('/admin/chat/conversations/{conversation}/messages', [ChatController::class, 'adminGetMessages'])->name('admin.chat.messages'); // This is named 'admin.chat.messages'
Route::post('/admin/chat/conversations/{conversation}/send', [ChatController::class, 'adminSendMessage'])->name('admin.chat.send'); // This is named 'admin.chat.send'
Route::post('/admin/chat/conversations/start', [ChatController::class, 'adminStartConversation'])->name('admin.chat.start');
Route::get('/admin/chat/messages/download/{message}', [ChatController::class, 'downloadFile'])->name('admin.chat.messages.download');
Route::get('/admin/chat/conversations/{conversation}', [ChatController::class, 'adminGetConversation'])->name('admin.chat.conversation');
Route::post('/admin/chat/conversations/{conversation}/read', [ChatController::class, 'markConversationAsRead'])->name('admin.chat.conversation.read');
Route::post('/admin/chat/typing', [ChatController::class, 'handleTyping'])->name('admin.chat.typing');
    
    // Client Chat Routes
    Route::get('/chat/conversation', [ChatController::class, 'clientGetConversation'])->name('client.chat.conversation');
    Route::post('/chat/send', [ChatController::class, 'clientSendMessage'])->name('client.chat.send');
    Route::get('/chat/unread-count', [ChatController::class, 'getUnreadCount'])->name('chat.unread-count');
    Route::get('/chat/check-new/{lastMessageId?}', [ChatController::class, 'checkNewMessages'])->name('chat.check-new');
    
    // General Chat Routes
    Route::post('/chat/typing', [ChatController::class, 'handleTyping'])->name('chat.typing');
    Route::post('/chat/messages/{message}/read', [ChatController::class, 'markMessageAsRead'])->name('chat.message.read');
    Route::post('/chat/conversations/{conversation}/read', [ChatController::class, 'markConversationAsRead'])->name('chat.conversation.read');
    
    // File Download Route (accessible by both admin and client)
    Route::get('/chat/messages/download/{message}', [ChatController::class, 'downloadFile'])->name('chat.messages.download');
});

//-----------------
// ADDITIONAL DEBUG ROUTES
//-----------------
Route::get('/debug-chat-routes', function() {
    return response()->json([
        'chat_messages_download' => route('chat.messages.download', ['message' => 1]),
        'admin_chat_messages_download' => route('admin.chat.messages.download', ['message' => 1]),
        'all_routes' => collect(Route::getRoutes())->map(function ($route) {
            return [
                'method' => implode('|', $route->methods()),
                'uri' => $route->uri(),
                'name' => $route->getName(),
            ];
        })->filter(function ($route) {
            return str_contains($route['name'] ?? '', 'chat') || str_contains($route['name'] ?? '', 'download');
        })->values()
    ]);
});

//polling chat message
Route::post('/admin/chat/poll-messages', [ChatController::class, 'pollForNewMessages'])->name('admin.chat.poll');