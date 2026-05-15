<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentSlotController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SessionStatusController;
use App\Http\Controllers\Auth\ClientRegisterController;
use App\Http\Controllers\CustomLoginController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\ClientTableController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\UserRegisterController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\StatisticsController;
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
use App\Http\Controllers\AppointmentSchedulingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\FetchAppointmentsController;
use App\Http\Controllers\EmailReceiverController;
use App\Http\Controllers\EmailSenderController;
use App\Http\Controllers\MailjetWebhookController;
use App\Http\Controllers\MessageInquiriesController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CaseCategoryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Admin\SmsChatController;
use App\Http\Controllers\CordonCalendarController;
use App\Http\Controllers\CordonTimeslotController;
use App\Http\Controllers\DiffunStaffNotificationController;
use App\Http\Controllers\Admin\AdminAccountSettingController;
use App\Http\Controllers\Admin\AdminForgotPasswordController;
// use App\Http\Controllers\CordonStaffForgotPasswordController; // TODO: Controller not created yet
use App\Http\Controllers\StaffForgotPasswordController;
use Carbon\Carbon; 
use App\Http\Controllers\AdminNotificationController;
use App\Http\Controllers\AdminMessageNotifController;
use App\Http\Controllers\PracticeAreasController;
use App\Http\Controllers\DiagnosticController;
use App\Http\Controllers\DebugJsonController;
use App\Http\Controllers\ConcernsInquiriesController;
use App\Http\Controllers\AccountUpdateController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TestimonialFooterController;
use App\Http\Controllers\ContactFooterController;
use App\Http\Controllers\Staff\StaffNotificationController;
use App\Http\Controllers\WalkInLogsController;
use App\Http\Controllers\LogBookLoginController;
use App\Http\Controllers\DiffunChoicePurposeController;
use App\Http\Controllers\CordonChoicePurposeController;
use App\Http\Controllers\WalkinLogbookController;
use App\Http\Controllers\FeedbackReportsController;
use App\Http\Controllers\StaffPendingRequestsController;
use App\Http\Controllers\DiffunStaffAcceptedController;
use App\Http\Controllers\DiffunStaffDeniedController;
use App\Http\Controllers\Staff\StaffAccountController;
use App\Http\Controllers\StaffAccountSettingController;
// use App\Http\Controllers\CordonStaffController; // TODO: Controller not created yet
use App\Http\Controllers\CordonAppointmentCountsController;
use App\Http\Controllers\CordonWalkinsLogsController;
use App\Http\Controllers\CordonPendingRequestController;
use App\Http\Controllers\CordonAcceptRequestController;
use App\Http\Controllers\Notifications\AppointmentNotificationController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\TabSessionController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use App\Models\User;

//-----------------
// DEFAULT ROUTE
//-----------------
// Public root route (welcome) - accessible to everyone. Role-based UI handled in the view.
Route::get('/', [WelcomeController::class, 'index'])->middleware('guest.offline')->name('welcome');

//-----------------
// STATIC PAGES
//-----------------
Route::view('/welcome', 'welcome')->middleware('guest.offline')->name('welcome');

Route::get('/about', [PracticeAreasController::class, 'about'])->middleware('guest.offline')->name('about');

Route::get('/contact', [ContactFooterController::class, 'index'])->middleware('guest.offline')->name('contact');
Route::get('/welcome', [WelcomeController::class, 'index'])->middleware('guest.offline');

// Public logbook entry pages
Route::get('/diffun-logbook', [WalkinLogbookController::class, 'publicIndex'])->name('public.diffun.logbook');
Route::get('/cordon-logbook', [\App\Http\Controllers\CordonPurposeVisitController::class, 'publicIndex'])->name('public.cordon.logbook');

// Mailjet webhook endpoint (public; Mailjet will POST JSON here). Use HTTPS in production.
Route::post('/mailjet/webhook', [MailjetWebhookController::class, 'handle'])->name('mailjet.webhook');

//-----------------
// AUTHENTICATION
//-----------------
// USER AUTH
Route::get('/login', [LoginController::class, 'showLoginForm'])->middleware('guest.offline')->name('login');
Route::get('/login/session-state', [LoginController::class, 'getLoginSessionState'])->name('login.session-state');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// SESSION STATUS & INACTIVITY MANAGEMENT
Route::middleware(['auth'])->group(function () {
    Route::get('/session/status', [SessionStatusController::class, 'checkStatus'])->name('session.status');
    Route::post('/session/refresh-activity', [SessionStatusController::class, 'refreshActivity'])->name('session.refresh');
    Route::get('/session/remaining-time', [SessionStatusController::class, 'getRemainingTime'])->name('session.remaining');
});

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
// PER-TAB SESSION ROUTES
//-----------------
Route::post('/tab-session/logout', [TabSessionController::class, 'logoutTab'])->name('tab.logout');
Route::get('/tab-session/info', [TabSessionController::class, 'getTabInfo'])->name('tab.info');
Route::middleware(['auth'])->get('/tab-session/active-tabs', [TabSessionController::class, 'getActiveTabs'])->name('tab.active-tabs');

//-----------------
// PROTECTED USER ROUTES
//-----------------
Route::middleware(['auth'])->group(function () {
    // BROADCASTING AUTHENTICATION
    // This endpoint authenticates the user for Pusher private channels
    Route::post('/broadcasting/auth', function (Request $request) {
        return Broadcast::channel($request->channel_name);
    })->name('broadcasting.auth');

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

    // CLIENT MANAGEMENT - APPROVAL ROUTES
Route::post('/appointments/{id}/approve', [AppointmentController::class, 'approve'])->name('appointments.approve');
Route::post('/appointments/{id}/deny', [AppointmentController::class, 'deny'])->name('appointments.deny');
});

// Admin queue/fetch status endpoint (requires auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/queue-status', [\App\Http\Controllers\Admin\QueueStatusController::class, 'index'])->name('admin.queue.status');
});

//-----------------
// ADMIN PROTECTED ROUTES
//-----------------
Route::middleware(['auth'])->group(function () {
    // DASHBOARDS
    Route::get('/admindashboard', [AdminDashboardController::class, 'index'])->name('admindashboard');
    Route::view('/superadministrator', 'superadmin.superadministrator')->name('superadmin.page');
    Route::get('/superadmin/lawyers', [SuperAdminController::class, 'lawyers'])->name('superadmin.lawyers');
    Route::post('/superadmin/lawyers', [SuperAdminController::class, 'storeLawyer'])->name('superadmin.lawyers.store');
    Route::put('/superadmin/lawyers/{lawyer}', [SuperAdminController::class, 'updateLawyer'])->name('superadmin.lawyers.update');
    Route::delete('/superadmin/lawyers/{lawyer}', [SuperAdminController::class, 'deleteLawyer'])->name('superadmin.lawyers.delete');
    
    // SUPERADMIN SECRETARIES MANAGEMENT ROUTES
    Route::get('/superadmin/secretaries', [SuperAdminController::class, 'secretaries'])->name('superadmin.secretaries');
    Route::post('/superadmin/secretaries', [SuperAdminController::class, 'storeSecretary'])->name('superadmin.secretaries.store');
    Route::put('/superadmin/secretaries/{secretary}', [SuperAdminController::class, 'updateSecretary'])->name('superadmin.secretaries.update');
    Route::delete('/superadmin/secretaries/{secretary}', [SuperAdminController::class, 'deleteSecretary'])->name('superadmin.secretaries.delete');
    
    // SECRETARY LAWYER MANAGEMENT ROUTES
    Route::get('/secretary/lawyers', [SuperAdminController::class, 'secretaryLawyers'])->name('secretary.lawyers');
    Route::post('/secretary/lawyers', [SuperAdminController::class, 'storeSecretaryLawyer'])->name('secretary.lawyers.store');
    Route::put('/secretary/lawyers/{lawyer}', [SuperAdminController::class, 'updateSecretaryLawyer'])->name('secretary.lawyers.update');
    Route::get('/superadmin/law-offices', [SuperAdminController::class, 'lawOffices'])->name('superadmin.lawoffices');
    Route::post('/superadmin/law-offices', [SuperAdminController::class, 'storeLawOffice'])->name('superadmin.lawoffices.store');
    Route::put('/superadmin/law-offices/{lawOffice}', [SuperAdminController::class, 'updateLawOffice'])->name('superadmin.lawoffices.update');
    Route::delete('/superadmin/law-offices/{lawOffice}', [SuperAdminController::class, 'destroyLawOffice'])->name('superadmin.lawoffices.destroy');
    Route::get('/superadmin/clients', [SuperAdminController::class, 'clients'])->name('superadmin.clients');
    Route::put('/superadmin/clients/{client}', [SuperAdminController::class, 'updateClient'])->name('superadmin.clients.update');
    Route::delete('/superadmin/clients/{client}', [SuperAdminController::class, 'destroyClient'])->name('superadmin.clients.destroy');
    Route::view('/superadmin/statistics', 'superadmin.statistics')->name('superadmin.statistics');
    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics');
    Route::get('/superadmin/message-inquiries', [SuperAdminController::class, 'messageInquiries'])->name('superadmin.message-inquiries');
    Route::view('/administrator', 'administrator')->middleware(['auth'])->name('admin.page');
    
    // ADMIN ACCOUNT
    Route::get('/adminAccount', [AdminAccountController::class, 'show'])->name('adminAccount');
    Route::put('/adminAccount/update', [AdminAccountController::class, 'update'])->name('adminAccount.update');

    // CLIENT MANAGEMENT - APPROVAL ROUTES
    //Route::post('/appointments/{id}/approve', [ClientTableController::class, 'approve'])->name('appointments.approve');
   // Route::post('/appointments/{id}/deny', [ClientTableController::class, 'deny'])->name('appointments.deny');

    // CLIENT MANAGEMENT PAGES
    Route::get('/clientstbl', [ClientTableController::class, 'index'])->name('clientstbl');
    Route::get('/adminAcceptedRequest', [AppointmentController::class, 'showApprovedAppointments'])->name('adminAcceptedRequest');
    Route::get('/adminAcceptedRequest/report/pdf', [AppointmentController::class, 'generateApprovedAppointmentsReport'])->name('adminAcceptedRequest.report.pdf');
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

    // Email chat - latest message endpoint (AJAX)
    Route::get('/email/latest/{email}', [EmailReceiverController::class, 'getLatestMessage'])->name('email.latest');

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
// Terms routes (protected below as client-only)
Route::middleware(['auth', 'ensure_client'])->group(function () {
    Route::get('/Terms', [TermsController::class, 'show'])->name('Terms');
    Route::post('/terms/accept', [TermsController::class, 'accept'])->name('terms.accept');
});
Route::get('/appointment1', [AppointmentController::class, 'showAppointmentForm'])->name('appointment1');
Route::post('/appointment/step1', [AppointmentController::class, 'storeStep1'])->name('appointment.storeStep1');
Route::get('/getsched', [AppointmentController::class, 'showGetSched'])->name('getsched');
Route::get('/FinalizeAppointment', [AppointmentController::class, 'showFinalizePage'])->name('appointment.finalizePage');
Route::post('/finalize-appointment', [AppointmentController::class, 'finalizeAppointment'])->name('appointment.finalize');

// Admin Walk-ins combined view - PROTECTED by auth and CSRF middleware
Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/admin/walkins', [\App\Http\Controllers\adminWalkInsController::class, 'index'])->name('admin.walkins');
    Route::post('/admin/walkins/export/excel', [\App\Http\Controllers\adminWalkInsController::class, 'exportExcel'])->name('admin.walkins.export.excel');
    Route::post('/admin/walkins/export/pdf', [\App\Http\Controllers\adminWalkInsController::class, 'exportPdf'])->name('admin.walkins.export.pdf');
    Route::get('/admin/walkins/backup-logs', [\App\Http\Controllers\adminWalkInsController::class, 'getBackupLogs'])->name('admin.walkins.backup.logs');
    Route::get('/admin/walkins/view-backup/{id}', [\App\Http\Controllers\adminWalkInsController::class, 'viewBackup'])->name('admin.walkins.view.backup');
    Route::get('/admin/walkins/download-file/{id}', [\App\Http\Controllers\adminWalkInsController::class, 'downloadBackupFile'])->name('admin.walkins.download.file');
    Route::delete('/admin/walkins/delete-backup/{id}', [\App\Http\Controllers\adminWalkInsController::class, 'deleteBackup'])->name('admin.walkins.delete.backup');
    Route::delete('/walkins/delete/{id}', [\App\Http\Controllers\adminWalkInsController::class, 'delete']);
});

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
//Route::post('/appointments/{id}/deny-appointment', [AppointmentController::class, 'denyAppointment'])->name('appointments.denyAppointment');
//Route::post('/appointments/reaccept/{id}', [AppointmentController::class, 'reaccept'])->name('appointments.reaccept');
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
// STAFF ROUTES - UPDATED WITH AUTH MIDDLEWARE
//-----------------
Route::middleware(['auth', 'validate.tab.session', 'authorize.role:staff,secretary,clerk'])->group(function () {
    // STAFF DASHBOARD AND PAGES
    Route::get('/staff', function () {
        return view('staff.staff');
    })->name('staff');

    // dashboardStaff route now handled by DiffunStaffDashboardController (staff dashboard)
    Route::get('/dashboardStaff', [\App\Http\Controllers\DiffunStaffDashboardController::class, 'index'])->name('dashboardStaff');

    // PENDING REQUESTS
    Route::get('/StaffClientstbl', [StaffPendingRequestsController::class, 'index'])
        ->name('staff.clients.pending')
        ->middleware('auth');

        // Add API routes for AJAX operations
        Route::middleware(['auth'])->prefix('staff')->group(function () {
            Route::get('/pending-appointments', [StaffPendingRequestsController::class, 'getPendingAppointments'])
                ->name('staff.pending.appointments');
            
            Route::post('/appointments/{id}/approve', [StaffPendingRequestsController::class, 'approve'])
                ->name('staff.appointments.approve');
            
            Route::post('/appointments/{id}/deny', [StaffPendingRequestsController::class, 'deny'])
                ->name('staff.appointments.deny');
            
            Route::get('/appointments/{id}/details', [StaffPendingRequestsController::class, 'getAppointmentDetails'])
                ->name('staff.appointments.details');
            
            Route::get('/pending-statistics', [StaffPendingRequestsController::class, 'getStatistics'])
                ->name('staff.pending.statistics');
    });

    // ACCEPTED REQUESTS - Now handled by DiffunStaffAcceptedController
    Route::get('/staff/accepted-requests', [DiffunStaffAcceptedController::class, 'index'])->name('staff.acceptedRequests');
    Route::get('/staff/accepted-requests/{id}', [DiffunStaffAcceptedController::class, 'getAppointmentDetails'])->name('staff.acceptedRequests.details');
    Route::delete('/staff/accepted-requests/{id}', [DiffunStaffAcceptedController::class, 'destroy'])->name('staff.acceptedRequests.destroy');

    // Purpose Choices Routes
    Route::get('/staff/purpose-choices', [DiffunChoicePurposeController::class, 'index'])
        ->name('staff.purpose.choices');
    
    Route::post('/staff/purpose-choices', [DiffunChoicePurposeController::class, 'store'])
        ->name('staff.purpose.choices.store');
    
    Route::put('/staff/purpose-choices/{id}', [DiffunChoicePurposeController::class, 'update'])
        ->name('staff.purpose.choices.update');
    
    Route::delete('/staff/purpose-choices/{id}', [DiffunChoicePurposeController::class, 'destroy'])
    ->name('staff.purpose.choices.destroy');

    // CORDON Purpose Choices Routes (separate table/view)
    Route::get('/staff/cordon/purpose-choices', [CordonChoicePurposeController::class, 'index'])
        ->name('cordon.staff.purpose.choices');
    
    Route::post('/staff/cordon/purpose-choices', [CordonChoicePurposeController::class, 'store'])
        ->name('cordon.staff.purpose.choices.store');
    
    Route::put('/staff/cordon/purpose-choices/{id}', [CordonChoicePurposeController::class, 'update'])
        ->name('cordon.staff.purpose.choices.update');
    
    Route::delete('/staff/cordon/purpose-choices/{id}', [CordonChoicePurposeController::class, 'destroy'])
        ->name('cordon.staff.purpose.choices.destroy');

    // DENIED REQUESTS
    Route::get('/staff/denied-requests', [DiffunStaffDeniedController::class, 'index'])->name('staff.deniedRequests');
    Route::get('/staff/denied-requests/{id}', [DiffunStaffDeniedController::class, 'getAppointmentDetails'])->name('staff.deniedRequests.details');
    Route::delete('/staff/denied-requests/{id}', [DiffunStaffDeniedController::class, 'destroy'])->name('staff.deniedRequests.destroy');

    // WALK-IN LOGS ROUTES - ALL PROTECTED
    Route::get('/staff/walkins/logs', [WalkInLogsController::class, 'index'])->name('staff.walkins.logs');
    Route::get('/staff/walkins/{id}', [WalkInLogsController::class, 'show'])->name('staff.walkins.show');
    
    // Walk-in Logs Export and Backup Routes
    Route::post('/staff/walkins/logs/export/pdf', [WalkInLogsController::class, 'exportPdf'])->name('staff.walkins.logs.export.pdf');
    Route::post('/staff/walkins/logs/export/excel', [WalkInLogsController::class, 'exportExcel'])->name('staff.walkins.logs.export.excel');
    Route::get('/staff/walkins/logs/download-backup/{id}', [WalkInLogsController::class, 'downloadBackup'])->name('staff.walkins.logs.download.backup');
    
    // Walk-in Backup File Operations
    Route::get('/staff/walkins/logs/view-backup/{id}', [WalkInLogsController::class, 'viewBackup'])->name('staff.walkins.logs.view.backup');
    Route::get('/staff/walkins/logs/download-file/{id}', [WalkInLogsController::class, 'downloadBackupFile'])->name('staff.walkins.logs.download.file');
    Route::delete('/staff/walkins/logs/delete-backup/{id}', [WalkInLogsController::class, 'deleteBackup'])->name('staff.walkins.logs.delete.backup');

    // FEEDBACK REPORTS
    // Uses the FeedbackReportsController route defined later in routes/web.php

    // STAFF ACCOUNT SETTINGS ROUTES - UPDATED
    // Staff Account Settings Page
    Route::get('/staff/account-settings', [StaffAccountSettingController::class, 'index'])->name('staff.account.settings');
    
    // Update Profile Route
    Route::put('/staff/account-settings/update', [StaffAccountSettingController::class, 'updateProfile'])->name('staff.account.settings.update');
    
    // Update Password Route
    Route::put('/staff/account-settings/update-password', [StaffAccountSettingController::class, 'updatePassword'])->name('staff.account.settings.update.password');
    
    // STAFF ACCOUNT
    Route::get('/staffAccount', function () {
        return view('diffun_staff.staffaccount');
    });

    // Staff Notification Routes
    Route::prefix('staff')->group(function () {
        // Notification routes
        Route::get('/notifications/unread', [\App\Http\Controllers\Staff\StaffNotificationController::class, 'getUnread']);
        Route::get('/notifications/count', [\App\Http\Controllers\Staff\StaffNotificationController::class, 'getCount']);
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Staff\StaffNotificationController::class, 'markAsRead']);
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Staff\StaffNotificationController::class, 'markAllAsRead']);
        Route::get('/notifications/test', [\App\Http\Controllers\Staff\StaffNotificationController::class, 'createTestNotification']);
    });

    // Staff Calendar Routes
    Route::get('/staff/calendar/month/colors', [\App\Http\Controllers\StaffController::class, 'getMonthColors'])
        ->name('staff.calendar.month.colors');

    Route::get('/staff/calendar/date-data', [\App\Http\Controllers\StaffController::class, 'getDateData'])
        ->name('staff.calendar.date.data');

    Route::post('/staff/calendar/save-date-data', [\App\Http\Controllers\StaffController::class, 'saveDateData'])
        ->name('staff.calendar.save.date.data');

    // CORDON STAFF ROUTES
    Route::get('/cordon/staff', [CordonTimeslotController::class, 'index'])->name('cordon.staff');

    // Cordon Staff Calendar Routes
    Route::get('/cordon/calendar/month/colors', [CordonTimeslotController::class, 'getMonthColors'])
        ->name('cordon.calendar.month.colors');

    Route::get('/cordon/calendar/date-data', [CordonTimeslotController::class, 'getDateData'])
        ->name('cordon.calendar.date.data');

    Route::post('/cordon/calendar/save-date-data', [CordonTimeslotController::class, 'saveDateData'])
        ->name('cordon.calendar.save.date.data');

    Route::get('/cordon/available-times/{date}', [CordonTimeslotController::class, 'getAvailableTimes'])
        ->name('cordon.available.times');

    Route::post('/cordon/book-slot', [CordonTimeslotController::class, 'bookSlot'])
        ->name('cordon.book.slot');

    Route::post('/cordon/unbook-slot', [CordonTimeslotController::class, 'unbookSlot'])
        ->name('cordon.unbook.slot');

    // Cordon Walk-ins handled in consolidated cordon routes further below

    // Digital Logbook Routes
    Route::get('/staff/walkins/logbook', function () {
        // Check if user is staff
        if (!Auth::check() || !in_array(Auth::user()->role, ['staff', 'admin', 'superadmin', 'diffun_staff', 'cordon_staff'])) {
            abort(403, 'Unauthorized access.');
        }

        // Get all purposes from the database
        $purposes = DB::table('diffun_choice_purpose')
            ->orderBy('purpose', 'asc')
            ->get();
        
        return view('walkin logbook.diffun_logbook.index', compact('purposes'));
    })->name('staff.walkins.logbook');
    
    // Store route
    Route::post('/staff/walkins/logbook/store', function (Request $request) {
        // Check if user is staff
        if (!Auth::check() || !in_array(Auth::user()->role, ['staff', 'admin', 'superadmin', 'diffun_staff', 'cordon_staff'])) {
            abort(403, 'Unauthorized access.');
        }

        // Validate the form data
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'purpose' => 'required|string|max:255',
        ]);

        try {
            // Create the walk-in entry
            DB::table('diffun_walkins')->insert([
                'fullname' => $validated['fullname'],
                'contact_number' => $validated['contact_number'],
                'address' => $validated['address'],
                'purpose' => $validated['purpose'],
                'branch' => 'Diffun',
                'date_time' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Walk-in entry submitted successfully!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting entry: ' . $e->getMessage()
            ], 500);
        }
    })->name('staff.walkins.logbook.store');
});  // CLOSE: auth middleware group for all staff routes

//-----------------
// MESSAGING & REVIEWS
//-----------------
Route::post('/submit-message', [MessageController::class, 'store'])->name('message.store');
Route::post('/submit-review', [ReviewController::class, 'store'])->name('review.store');
Route::get('/testimonial', [TestimonialFooterController::class, 'index'])->middleware('guest.offline')->name('review.index');
Route::post('/messaging/send-message', [ClientMessageController::class, 'sendMessage'])->name('messaging.sendMessage');
Route::post('/send-message', [ClientMessageController::class, 'sendMessage'])->name('client.sendMessage');

// SMS send endpoint used by admin SMS chat UI
// Protected by auth middleware to preserve existing access control
Route::middleware(['auth'])->post('/sms/send', [SmsChatController::class, 'sendSms'])->name('sms.send');

// Raw SMS send (by phone number) for New SMS modal
Route::middleware(['auth'])->post('/admin/sms-send', [SmsChatController::class, 'sendSmsRaw'])->name('sms.send.raw');

// Diffun staff notifications endpoint (polling)
Route::middleware(['auth'])->get('/diffun-staff/notifications', [DiffunStaffNotificationController::class, 'getNotifications'])->name('diffun.notifications');
// Diffun staff mark-as-read endpoints
Route::middleware(['auth'])->post('/diffun-staff/notifications/mark-read', [DiffunStaffNotificationController::class, 'markRead'])->name('diffun.notifications.markRead');
Route::middleware(['auth'])->post('/diffun-staff/notifications/mark-all-read', [DiffunStaffNotificationController::class, 'markAllRead'])->name('diffun.notifications.markAllRead');

// Cordon staff notifications endpoint (polling) - returns recent pending appointments for Cordon
Route::middleware(['auth'])->get('/cordon-staff/notifications', [\App\Http\Controllers\Notifications\CordonNotificationController::class, 'getNotifications'])->name('cordon.notifications');
Route::middleware(['auth'])->post('/cordon-staff/notifications/mark-read', [\App\Http\Controllers\Notifications\CordonNotificationController::class, 'markRead'])->name('cordon.notifications.markRead');
Route::middleware(['auth'])->post('/cordon-staff/notifications/mark-all-read', [\App\Http\Controllers\Notifications\CordonNotificationController::class, 'markAllRead'])->name('cordon.notifications.markAllRead');

// Staff notifications endpoints (used by Cordon/Dashboard JS)
Route::middleware(['auth'])->group(function () {
    Route::get('/staff/notifications', [StaffNotificationController::class, 'index'])->name('staff.notifications.index');
    Route::get('/staff/notifications/unread', [StaffNotificationController::class, 'getUnread'])->name('staff.notifications.unread');
    Route::get('/staff/notifications/count', [StaffNotificationController::class, 'getCount'])->name('staff.notifications.count');
    Route::post('/staff/notifications/mark-all-read', [StaffNotificationController::class, 'markAllAsRead'])->name('staff.notifications.markAllRead');
    Route::post('/staff/notifications/{id}/read', [StaffNotificationController::class, 'markAsRead'])->name('staff.notifications.read');
    // Test endpoint to create a sample notification (development)
    Route::post('/staff/notifications/test', [StaffNotificationController::class, 'createTestNotification'])->name('staff.notifications.test');
});

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
    // Set user as offline before logout
    $user = Auth::user();
    if ($user) {
        $user->update(['active_status' => 0]);
    }
    
    session()->forget('showAppointmentButton');
    session()->forget('otp_verified');
    Auth::logout();
    return redirect('/welcome')->withCookie(\Illuminate\Support\Facades\Cookie::forget(\App\Http\Middleware\MarkGuestUserOffline::LAST_USER_COOKIE));
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

// Diagnostic route for testing message inquiries query
Route::get('/test/inquiry-diagnostic', [DiagnosticController::class, 'testQueryStructure']);

// JSON debug endpoint
Route::get('/test/inquiries-json', [DebugJsonController::class, 'getInquiriesJson']);

// Development helper: create several test appointments with branch variants
Route::get('/_test/create-appointments', function () {
    if (!config('app.debug')) {
        abort(404);
    }

    $variants = [
        'Cordon',
        'cordon branch office',
        'CORDON BRANCH',
        'Diffun Branch Office',
        'Some Other Branch'
    ];

    $created = [];
    foreach ($variants as $i => $branch) {
        try {
            $appt = App\Models\Appointment::create([
                'fullname' => "Test User {$i}",
                'address' => '123 Test St',
                'phone' => '0917123456',
                'email' => "test{$i}@example.com",
                'category' => 'General',
                'case_name' => 'Test Case',
                'selected_branch' => $branch,
                'selected_date' => date('Y-m-d'),
                'selected_time' => date('H:i'),
                'appointment_approval' => 'pending'
            ]);
            $created[] = ['id' => $appt->id, 'branch' => $branch];
        } catch (\Exception $e) {
            \Log::error('Test create appointment error: ' . $e->getMessage());
        }
    }

    return response()->json([
        'success' => true,
        'created' => $created
    ]);
});

///fetch appointments data
Route::get('/appointments', [FetchAppointmentsController::class, 'index'])->name('appointments.index');
Route::get('/api/appointments', [FetchAppointmentsController::class, 'getAppointments'])->name('appointments.list');
Route::get('/api/appointment-categories', [FetchAppointmentsController::class, 'getCategories'])->name('appointments.categories');
Route::get('/api/appointments/{id}', [FetchAppointmentsController::class, 'getAppointmentDetails'])->name('appointments.details');
Route::get('/api/appointment-case-names', [FetchAppointmentsController::class, 'getCaseNames'])->name('appointments.case_names');

// Law offices API
Route::get('/api/law-offices', function() {
    $offices = \App\Models\LawOffice::select('id', 'law_office')->get();
    return response()->json([
        'status' => 'success',
        'data' => $offices
    ]);
})->name('api.law-offices');

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
        ->select('time', 'color', 'description', 'booked', 'time_slot', 'slot_number', 'date_color', 'date_description')
        ->get();
    
    return response()->json([
        'date' => $date,
        'records' => $colors,
        'count' => $colors->count()
    ]);
});

// STAFF CALENDAR DIAGNOSTIC - Check what staff saved vs admin is retrieving
Route::get('/debug-staff-calendar/{date}', function($date) {
    // Get from month_colors table
    $monthColor = DB::table('month_colors')
        ->where('date', $date)
        ->first();
    
    // Get from week_colors table
    $weekColors = DB::table('week_colors')
        ->where('date', $date)
        ->get();
    
    // Get what CalendarController would return via getMonthColors
    $month = substr($date, 0, 7);
    $calendarReturn = DB::table('month_colors')
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
        'requested_date' => $date,
        'month_colors_raw' => $monthColor,
        'week_colors_count' => $weekColors->count(),
        'week_colors_sample' => $weekColors->take(3),
        'calendar_controller_return' => $calendarReturn,
        'debug_info' => [
            'date_has_data' => $monthColor !== null,
            'week_slots_found' => $weekColors->count(),
            'columns_available' => $monthColor ? array_keys((array) $monthColor) : []
        ]
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

// Terms routes handled in APPOINTMENT ROUTES section (client-only)

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
Route::middleware(['auth'])->group(function () {
    Route::get('/emails/fetch', [EmailReceiverController::class, 'fetchEmails'])->name('emails.fetch');
    Route::get('/email/conversation/{email}', [EmailReceiverController::class, 'getEmailConversation'])->name('email.conversation');
    
    // ADD THIS LINE:
    Route::get('/email/conversation/{email}/check-new', [EmailReceiverController::class, 'checkNewMessages'])->name('email.conversation.check-new');
    
    Route::post('/email/send-chat', [EmailSenderController::class, 'sendEmailFromChat'])->name('email.send.chat');
    Route::get('/email-chat', [EmailReceiverController::class, 'getEmailChatView'])->name('email.chat');
    Route::get('/email/inbox', [EmailReceiverController::class, 'getEmailInbox'])->name('email.inbox');
});

Route::get('/fetch-emails', [EmailReceiverController::class, 'fetchEmails']);
Route::post('/send-email', [EmailSenderController::class, 'sendEmailFromChat']);

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
        'mailjet_configured' => !empty(env('MAILJET_API_KEY')) && !empty(env('MAILJET_SECRET_KEY')),
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

// Debug DB inspection routes (temporary)
Route::get('/debug/chattbl/create-table', function() {
    try {
        $ddl = DB::select("SHOW CREATE TABLE chattbl");
        return response()->json($ddl);
    } catch (\Exception $e) {
        return response('Error: ' . $e->getMessage(), 500);
    }
});

Route::get('/debug/chattbl/recent', function() {
    try {
        $rows = DB::table('chattbl')->orderBy('id', 'desc')->limit(20)->get();
        return response()->json($rows);
    } catch (\Exception $e) {
        return response('Error: ' . $e->getMessage(), 500);
    }
});

Route::get('/debug/admin_message_notif/create-table', function() {
    try {
        $ddl = DB::select("SHOW CREATE TABLE admin_message_notif");
        return response()->json($ddl);
    } catch (\Exception $e) {
        return response('Error: ' . $e->getMessage(), 500);
    }
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
})->name('messages.sms.redirect');

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
    Route::get('/admin/chat/conversations/{conversation}/messages', [ChatController::class, 'adminGetMessages'])->name('admin.chat.messages');
    Route::post('/admin/chat/conversations/{conversation}/send', [ChatController::class, 'adminSendMessage'])->name('admin.chat.send');
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
    
    // Chat Dropdown Routes (for admin navigation bar)
    Route::get('/chat/recent-conversations', [ChatController::class, 'getRecentConversations'])->name('chat.recent-conversations');
    Route::get('/chat/admin/unread-messages', [ChatController::class, 'getUnreadAdminMessages'])->name('chat.admin.unread-messages');
    
    //polling chat message
    Route::post('/admin/chat/poll-messages', [ChatController::class, 'pollForNewMessages'])->name('admin.chat.poll');
});

//-----------------
// WEBRTC CALL ROUTES
//-----------------
Route::middleware(['auth'])->group(function () {
    // Diagnostics
    Route::get('/diagnostics/webrtc', function () {
        return view('diagnostics.webrtc');
    })->name('diagnostics.webrtc');

    // Call UI and Management
    Route::get('/call/{receiverId}', [CallController::class, 'show'])->name('call.show');
    
    // Call Actions
    Route::post('/call/initiate', [CallController::class, 'initiate'])->name('call.initiate');
    Route::post('/call/answer', [CallController::class, 'answer'])->name('call.answer');
    Route::post('/call/reject', [CallController::class, 'reject'])->name('call.reject');
    Route::post('/call/end', [CallController::class, 'end'])->name('call.end');
    
    // WebRTC Signaling
    Route::post('/call/sdp-offer', [CallController::class, 'sendSdpOffer'])->name('call.sdp-offer');
    Route::post('/call/sdp-answer', [CallController::class, 'sendSdpAnswer'])->name('call.sdp-answer');
    Route::post('/call/ice-candidate', [CallController::class, 'sendIceCandidate'])->name('call.ice-candidate');
    
    // Call History
    Route::get('/call/history', [CallController::class, 'history'])->name('call.history');
    Route::get('/call/with/{userId}', [CallController::class, 'getCallsWith'])->name('call.with');
    Route::get('/call/incoming', [CallController::class, 'checkIncomingCalls'])->name('call.incoming');
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

// SMS Routes
Route::middleware(['auth'])->prefix('admin')->group(function () {
    // SMS Chat
    Route::get('/sms-chat', [SmsChatController::class, 'index'])->name('messages.sms');
    Route::get('/sms-conversation/{userId}', [SmsChatController::class, 'getConversation'])->name('sms.conversation');
    Route::post('/sms-send', [SmsChatController::class, 'sendSms'])->name('admin.sms.send');
    Route::get('/sms-status/{messageId}', [SmsChatController::class, 'checkDeliveryStatus'])->name('sms.status');
});
Route::get('/admin/test-textbee', [SmsChatController::class, 'testTextBee'])->name('test.textbee');

Route::get('/test-sms-api', function() {
    $apiKey = '8c2fba32-4358-4ad2-9c41-a7cfcfea91c5';
    $url = 'https://api.textbee.dev/api/v1/sms/send';
    
    // Test if API endpoint is accessible
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    return response()->json([
        'api_endpoint' => $url,
        'http_status' => $httpCode,
        'accessible' => $httpCode !== 0 && $httpCode !== 404,
        'server_time' => now()->toDateTimeString()
    ]);
});
//route to check device status
Route::get('/admin/textbee-device-status', [SmsChatController::class, 'checkDeviceStatus'])->name('textbee.device.status');

Route::get('/test-textbee-correct', function() {
    $apiKey = '8c2fba32-4358-4ad2-9c41-a7cfcfea91c5';
    $deviceId = '692b0d2bd3fdd9bd6ca58fcb';
    
    // CORRECT endpoint from documentation
    $url = "https://api.textbee.dev/api/v1/gateway/{$deviceId}/send-sms";
    
    $testPhone = '09916156687'; // Replace with your test number
    $formattedPhone = preg_replace('/[^0-9]/', '', $testPhone);
    if (strlen($formattedPhone) === 10) {
        $formattedPhone = '63' . $formattedPhone;
    } elseif (strlen($formattedPhone) === 11 && substr($formattedPhone, 0, 2) === '09') {
        $formattedPhone = '63' . substr($formattedPhone, 1);
    }
    
    $data = [
        'recipients' => [$formattedPhone],
        'message' => 'Test SMS from LegalConnect - Correct API'
    ];
    
    Log::info('Testing TextBee with correct endpoint: ' . $url);
    Log::info('Data: ' . json_encode($data));
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return response()->json([
        'http_code' => $httpCode,
        'response' => json_decode($response, true),
        'raw_response' => $response,
        'error' => $error,
        'request' => [
            'url' => $url,
            'data' => $data,
            'phone_formatted' => $formattedPhone
        ]
    ]);
});

Route::get('/test-textbee-device-info', function() {
    $apiKey = '8c2fba32-4358-4ad2-9c41-a7cfcfea91c5';
    $deviceId = '692b0d2bd3fdd9bd6ca58fcb';
    
    $url = "https://api.textbee.dev/api/v1/gateway/devices/{$deviceId}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $apiKey,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return response()->json([
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error,
        'url' => $url
    ]);
});
Route::get('/test-textbee-api-structure', function() {
    $apiKey = '8c2fba32-4358-4ad2-9c41-a7cfcfea91c5';
    
    // Try different endpoints
    $endpoints = [
        'https://api.textbee.dev/api/v1/gateway',
        'https://api.textbee.dev/api/v1/devices',
        'https://api.textbee.dev/api/v1/sms',
        'https://api.textbee.dev/api/v1/balance'
    ];
    
    $results = [];
    
    foreach ($endpoints as $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'x-api-key: ' . $apiKey,
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $results[$url] = [
            'status' => $httpCode,
            'exists' => $httpCode !== 404 && $httpCode !== 0
        ];
    }
    
    return response()->json($results);
});
Route::get('/test-textbee-actual-endpoint', function() {
    $apiKey = '8c2fba32-4358-4ad2-9c41-a7cfcfea91c5';
    $deviceId = '692b0d2bd3fdd9bd6ca58fcb';
    
    // Try different variations
    $variations = [
        "https://api.textbee.dev/api/v1/gateway/devices/{$deviceId}/send-sms",
        "https://api.textbee.dev/api/v1/gateway/{$deviceId}/send-sms",
        "https://api.textbee.dev/api/v1/devices/{$deviceId}/send-sms",
        "https://api.textbee.dev/api/v1/sms/send"  // Might be different endpoint
    ];
    
    $results = [];
    
    foreach ($variations as $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'x-api-key: ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $results[$url] = [
            'http_code' => $httpCode,
            'exists' => $httpCode !== 404 && $httpCode !== 0,
            'url' => $url
        ];
    }
    
    return response()->json($results);
});

Route::get('/debug-css-files', function() {
    $files = [
        'sms-chat' => public_path('css/sms-chat.blade.css'),
        'email-chat' => public_path('css/email-chat.blade.css'),
        'system-chat' => public_path('css/system-chat.blade.css')
    ];
    
    $results = [];
    foreach ($files as $name => $path) {
        $results[$name] = [
            'exists' => file_exists($path),
            'path' => $path,
            'url' => asset("css/{$name}.blade.css"),
            'size' => file_exists($path) ? filesize($path) : 0
        ];
    }
    
    return response()->json($results);
});

Route::get('/chat/download', [\App\Http\Controllers\ChatController::class, 'download'])
    ->name('chat.download.index');

    Route::get('/test-api-admins', function() {
    $admins = DB::table('users')
                ->whereIn('role', ['admin', 'superadmin'])
                ->select('id', 'name', 'email', 'image')
                ->orderBy('name', 'asc')
                ->get();
    
    return response()->json([
        'success' => true,
        'count' => $admins->count(),
        'admins' => $admins
    ]);
});
// Message dropdown routes
Route::middleware(['auth'])->group(function () {
    // Get
    Route::get('/api/admins', function() {
        $admins = DB::table('users')
                    ->whereIn('role', ['admin', 'superadmin'])
                    ->select('id', 'name', 'email', 'image')
                    ->orderBy('name', 'asc')
                    ->get();
        
        return response()->json([
            'success' => true,
            'admins' => $admins
        ]);
    })->name('api.admins');
    
    // Get or create conversation with admin
    Route::get('/api/conversation/admin/{adminId}', [ChatController::class, 'getOrCreateConversationWithAdmin']);
});
// Chat routes for clients
Route::middleware(['auth'])->group(function () {
    // Get unread messages count for client
    Route::get('/chat/unread-count/client', [ChatController::class, 'getUnreadMessagesCountForClient'])
        ->name('chat.unread-count.client');
    
    // Get or create conversation with specific admin
    Route::get('/api/conversation/admin/{adminId}', [ChatController::class, 'getOrCreateConversationWithAdmin']);
    
    // Get all admins (for client to select)
    Route::get('/api/admins', function() {
        $admins = User::where('role', 'admin')
            ->select('id', 'name', 'email', 'image')
            ->orderBy('name', 'asc')
            ->get();
        
        return response()->json([
            'success' => true,
            'admins' => $admins
        ]);
    });
});
// Chat routes for all users
Route::middleware(['auth'])->group(function () {
    // Get all admins for client selection
    Route::get('/api/admins', [ChatController::class, 'getAdmins']);
    
    // Get or create conversation with specific admin
    Route::get('/api/conversation/admin/{adminId}', [ChatController::class, 'getOrCreateConversationWithAdmin']);
    
    // Client chat routes
    Route::prefix('chat')->group(function () {
        // Get unread count for client
        Route::get('/unread-count/client', [ChatController::class, 'getUnreadMessagesCountForClient'])->name('chat.unread-count.client');
        
        // Mark conversation as read
        Route::post('/conversations/{conversationId}/read', [ChatController::class, 'markConversationAsRead']);
        
        // Download file
        Route::get('/download/{messageId}', [ChatController::class, 'downloadFile'])->name('chat.download');
        
        // Get unread count
        Route::get('/unread-count', [ChatController::class, 'getUnreadCount'])->name('chat.unread-count');
        
        // Get recent conversations for admin
        Route::get('/recent-conversations', [ChatController::class, 'getRecentConversations'])->name('chat.recent-conversations');
    });
    
    // Admin chat routes
    Route::prefix('chat/admin')->group(function () {
        Route::get('/messages/{conversationId}', [ChatController::class, 'adminGetMessages']);
        Route::post('/send/{conversationId}', [ChatController::class, 'adminSendMessage']);
    });
});
// In web.php or api.php
Route::post('/chat/mark-all-read', [ChatController::class, 'markAllAsRead'])
    ->middleware('auth')
    ->name('chat.mark-all-read');

    // Note: Cordon routes with prefix are defined separately below at Route::prefix('cordon')->group()
    // These duplicate routes were causing conflicts and have been removed.
    // Debug routes (temporary)
    Route::get('/debug/tables', function() {
        return response()->json([
            'cordon_date_availabilities_exists' => \Schema::hasTable('cordon_date_availabilities'),
            'cordon_time_slots_exists' => \Schema::hasTable('cordon_time_slots'),
            'cordon_records_count' => [
                'date_availabilities' => \DB::table('cordon_date_availabilities')->count(),
                'time_slots' => \DB::table('cordon_time_slots')->count()
            ]
        ]);
    });
    // Debug route to verify branch separation
// Route::get('/debug/branch-separation', function() {
//     return response()->json([
//         'diffun_tables' => [
//             'month_colors_exists' => \Schema::hasTable('month_colors'),
//             'week_colors_exists' => \Schema::hasTable('week_colors'),
//             'records_count' => [
//                 'month_colors' => \DB::table('month_colors')->count(),
//                 'week_colors' => \DB::table('week_colors')->count()
//             ]
//         ],
//         'cordon_tables' => [
//             'cordon_date_availabilities_exists' => \Schema::hasTable('cordon_date_availabilities'),
//             'cordon_time_slots_exists' => \Schema::hasTable('cordon_time_slots'),
//             'records_count' => [
//                 'date_availabilities' => \DB::table('cordon_date_availabilities')->count(),
//                 'time_slots' => \DB::table('cordon_time_slots')->count()
//             ]
//         ],
//         'endpoints' => [
//             'diffun_month_colors' => route('calendar.month.colors'),
//             'cordon_month_colors' => route('cordon.calendar.month.colors'),
//             'diffun_date_data' => route('calendar.date.data'),
//             'cordon_date_data' => route('cordon.calendar.date.data')
//         ]
//     ]);
// });

//temporary route to fix Cordon branch tables
Route::get('/fix-cordon-tables', function() {
    try {
        if (!Schema::hasTable('cordon_date_availabilities')) {
            Schema::create('cordon_date_availabilities', function (Blueprint $table) {
                $table->id();
                $table->date('date')->unique();
                $table->string('availability_status')->default('available');
                $table->string('date_color')->nullable();
                $table->text('description')->nullable();
                $table->integer('total_slots')->default(9);
                $table->integer('booked_slots')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cordon_time_slots')) {
            Schema::create('cordon_time_slots', function (Blueprint $table) {
                $table->id();
                $table->date('date');
                $table->integer('slot_number');
                $table->time('start_time');
                $table->time('end_time');
                $table->string('status')->default('available');
                $table->string('slot_color')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                
                $table->unique(['date', 'slot_number']);
            });
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Cordon tables created successfully',
            'tables' => [
                'cordon_date_availabilities' => Schema::hasTable('cordon_date_availabilities'),
                'cordon_time_slots' => Schema::hasTable('cordon_time_slots')
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}); 

// Consolidated CORDON STAFF VIEWS - single prefix group to avoid duplicate/conflicting routes
Route::middleware(['auth'])->prefix('cordon')->group(function () {
    // Dashboard and staff landing
    Route::get('/dashboard', [CordonAppointmentCountsController::class, 'index'])->name('cordon.dashboard');
    Route::get('/staff', function(){ return view('cordon_staff.staff'); })->name('cordon.staff');

    // Main walk-ins routes (primary names used in blades)
    Route::get('/staff/walkins/logs', [App\Http\Controllers\CordonWalkinsLogsController::class, 'index'])->name('cordon.staff.walkins.logs');
    // Compatibility aliases
    Route::get('/walkins-logs', [App\Http\Controllers\CordonWalkinsLogsController::class, 'index'])->name('cordon.walkins');
    Route::get('/walkins/logs', [App\Http\Controllers\CordonWalkinsLogsController::class, 'index'])->name('cordon.walkins.logs');

    // Show individual walkin (still provided by the generic WalkInLogsController)
    Route::get('/walkins/{id}', [WalkInLogsController::class, 'show'])->name('cordon.staff.walkins.show');

    // Export and backup routes (use staff WalkInLogsController implementations)
    Route::post('/walkins/logs/export/pdf', [WalkInLogsController::class, 'exportPdf'])->name('cordon.staff.walkins.logs.export.pdf');
    Route::post('/walkins/logs/export/excel', [WalkInLogsController::class, 'exportExcel'])->name('cordon.staff.walkins.logs.export.excel');
    Route::get('/walkins/logs/download-backup/{id}', [WalkInLogsController::class, 'downloadBackup'])->name('cordon.staff.walkins.logs.download.backup');
    Route::get('/walkins/logs/view-backup/{id}', [WalkInLogsController::class, 'viewBackup'])->name('cordon.staff.walkins.logs.view.backup');
    Route::get('/walkins/logs/download-file/{id}', [WalkInLogsController::class, 'downloadBackupFile'])->name('cordon.staff.walkins.logs.download.file');
    Route::delete('/walkins/logs/delete-backup/{id}', [WalkInLogsController::class, 'deleteBackup'])->name('cordon.staff.walkins.logs.delete.backup');

    // Other cordon pages
    // TODO: Create CordonStaffController to enable these routes
    // Route::get('/feedback-reports', [CordonStaffController::class, 'feedbackReports'])->name('cordon.staff.feedback.reports');
    // Route::get('/pending-requests', [CordonStaffController::class, 'pendingRequests'])->name('cordon.staff.clients.pending');
    // Route::get('/accepted-requests', [CordonStaffController::class, 'acceptedRequests'])->name('cordon.staff.acceptedRequests');
    // Route::get('/accepted-requests/report/pdf', [CordonStaffController::class, 'generateReportPdf'])->name('cordon.staff.acceptedRequests.report.pdf');
    // Route::get('/denied-requests', [CordonStaffController::class, 'deniedRequests'])->name('cordon.staff.deniedRequests');
    // Route::middleware(['auth:cordon_staff', 'validate.tab.session', 'authorize.role:cordon_staff'])->get('/account-settings', [CordonStaffController::class, 'accountSettings'])->name('cordon.staff.account.settings');
    // TODO: Create CordonStaffForgotPasswordController to enable these routes
    // Route::middleware(['auth:cordon_staff', 'validate.tab.session', 'authorize.role:cordon_staff'])->get('/staff/account-settings/forgot-password', [CordonStaffForgotPasswordController::class, 'showEmailForm'])->name('staff.password.request');
    // Route::middleware(['auth:cordon_staff', 'validate.tab.session', 'authorize.role:cordon_staff'])->post('/staff/account-settings/forgot-password/send-code', [CordonStaffForgotPasswordController::class, 'sendCode'])->name('staff.password.email');
    // Route::middleware(['auth:cordon_staff', 'validate.tab.session', 'authorize.role:cordon_staff'])->get('/staff/account-settings/forgot-password/verify', [CordonStaffForgotPasswordController::class, 'showVerifyForm'])->name('staff.password.verify');
    // Route::middleware(['auth:cordon_staff', 'validate.tab.session', 'authorize.role:cordon_staff'])->post('/staff/account-settings/forgot-password/verify', [CordonStaffForgotPasswordController::class, 'verifyCode'])->name('staff.password.verify.submit');
    // Route::middleware(['auth:cordon_staff', 'validate.tab.session', 'authorize.role:cordon_staff'])->get('/staff/account-settings/forgot-password/reset', [CordonStaffForgotPasswordController::class, 'showResetForm'])->name('staff.password.reset');
    // Route::middleware(['auth:cordon_staff', 'validate.tab.session', 'authorize.role:cordon_staff'])->post('/staff/account-settings/forgot-password/reset', [CordonStaffForgotPasswordController::class, 'resetPassword'])->name('staff.password.reset.submit');
});
// Temporary debug route to verify cordon_walkins table and connection
Route::get('/test-cordon-walkins', function() {
    try {
        $exists = \Illuminate\Support\Facades\Schema::hasTable('cordon_walkins');
        $rows = [];
        if ($exists) {
            $rows = \Illuminate\Support\Facades\DB::table('cordon_walkins')->orderBy('created_at','desc')->limit(10)->get();
        }

        return response()->json([
            'success' => true,
            'table_exists' => $exists,
            'count' => is_array($rows) ? count($rows) : $rows->count(),
            'rows' => $rows,
            'db' => \Illuminate\Support\Facades\DB::connection()->getDatabaseName()
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
});

Route::get('/cordon/accepted-requests', [CordonAcceptRequestController::class, 'index'])
    ->name('cordon.accepted');

// AJAX data endpoint for accepted cordon appointments
Route::get('/cordon/accepted-requests/data', [CordonAcceptRequestController::class, 'apiIndex'])
    ->name('cordon.accepted.data');

// Details and delete endpoints (require auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/cordon/accepted-requests/{id}/details', [CordonAcceptRequestController::class, 'details'])
        ->name('cordon.accepted.details');

    Route::delete('/cordon/accepted-requests/{id}', [CordonAcceptRequestController::class, 'destroy'])
        ->name('cordon.accepted.destroy');
    
    // Serve ID images
    Route::get('/cordon/accepted-requests/{id}/id-image/{side}', [CordonAcceptRequestController::class, 'idImage'])
        ->name('cordon.accepted.id_image');
});

// Fallback for cordon denied requests link referenced in views
Route::get('/cordon/denied-requests', function () {
    // Pass denied appointments for Cordon Branch Office to the view to avoid undefined variable errors
    $appointments = App\Models\Appointment::whereRaw("LOWER(TRIM(selected_branch)) = 'cordon branch office'")
        ->whereRaw("LOWER(TRIM(appointment_approval)) = 'denied'")
        ->orderBy('created_at', 'desc')
        ->get();

    return view('cordon_staff.staffDeniedRequest', compact('appointments'));
})->name('cordon.denied');
// Details and delete endpoints for cordon denied appointments
Route::middleware(['auth'])->group(function () {
    Route::get('/cordon/denied-requests/{id}/details', function ($id) {
        $appointment = App\Models\Appointment::find($id);
        if (! $appointment) {
            return response()->json(['success' => false, 'error' => 'Appointment not found'], 404);
        }

        // Ensure this is a cordon denied appointment
        $branch = strtolower(trim($appointment->selected_branch ?? ''));
        $approval = strtolower(trim($appointment->appointment_approval ?? ''));
        if ($branch !== 'cordon branch office' || $approval !== 'denied') {
            return response()->json(['success' => false, 'error' => 'Appointment exists but is not a denied Cordon appointment', 'data' => ['selected_branch' => $appointment->selected_branch, 'appointment_approval' => $appointment->appointment_approval]], 403);
        }

        // Convert to array and normalize image URLs similar to other controllers
        $data = $appointment->toArray();
        foreach (['id_front', 'id_back'] as $field) {
            if (empty($data[$field])) continue;
            $path = $data[$field];
            if (filter_var($path, FILTER_VALIDATE_URL)) { $data[$field] = $path; continue; }
            if (file_exists(public_path('storage/' . ltrim($path, '/')))) { $data[$field] = url('storage/' . ltrim($path, '/')); continue; }
            if (file_exists(public_path($path))) { $data[$field] = url($path); continue; }
            $data[$field] = $path;
        }

        return response()->json(['success' => true, 'appointment' => $data]);
    })->name('cordon.denied.details');

    Route::delete('/cordon/denied-requests/{id}', function ($id) {
        $appointment = App\Models\Appointment::find($id);
        if (! $appointment) {
            return response()->json(['success' => false, 'message' => 'Appointment not found'], 404);
        }
        $branch = strtolower(trim($appointment->selected_branch ?? ''));
        if ($branch !== 'cordon branch office') {
            return response()->json(['success' => false, 'message' => 'Permission denied'], 403);
        }
        try {
            $appointment->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete appointment'], 500);
        }
    })->name('cordon.denied.destroy');
});
// In routes/web.php or routes/api.php
Route::get('/test-cordon', [CordonCalendarController::class, 'testConnection']);
Route::get('/cordon/month-colors', [CordonCalendarController::class, 'getMonthColors']);
Route::get('/cordon/date-data', [CordonCalendarController::class, 'getDateData']);
Route::get('/debug-cordon-slot/{date}', function($date) {
    $slots = DB::table('cordon_time_slots')
        ->where('date', $date)
        ->select('slot_number', 'slot_color', 'description', 'status')
        ->orderBy('slot_number')
        ->get();
    
    $dateData = DB::table('cordon_date_availabilities')
        ->where('date', $date)
        ->first();
    
    return response()->json([
        'date' => $date,
        'date_data' => $dateData,
        'slots' => $slots,
        'count' => $slots->count(),
        'slot_colors_null' => $slots->where('slot_color', null)->count()
    ]);
});


Route::get('/debug/cordon-slot-data/{date}', function($date) {
    // Get raw data from Cordon tables
    $dateData = DB::table('cordon_date_availabilities')
        ->where('date', $date)
        ->first();
    
    $timeSlots = DB::table('cordon_time_slots')
        ->where('date', $date)
        ->orderBy('slot_number')
        ->get(['id','date','time','time_slot','slot_number','color','description','booked']);

    
    return response()->json([
        'date_data' => $dateData,
        'time_slots_raw' => $timeSlots,
        'time_slots_count' => $timeSlots->count(),
        'schema_info' => [
            'cordon_time_slots_columns' => Schema::getColumnListing('cordon_time_slots'),
            'week_colors_columns' => Schema::getColumnListing('week_colors'),
            'diffun_example' => DB::table('week_colors')->where('date', $date)->first()
        ]
    ]);
});

Route::get('/initialize-cordon-calendar', function() {
    try {
        // Check if tables exist
        if (!Schema::hasTable('cordon_date_availabilities')) {
            Schema::create('cordon_date_availabilities', function (Blueprint $table) {
                $table->id();
                $table->date('date')->unique();
                $table->string('availability_status')->default('available');
                $table->string('date_color')->nullable();
                $table->text('description')->nullable();
                $table->integer('total_slots')->default(9);
                $table->integer('booked_slots')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cordon_time_slots')) {
            Schema::create('cordon_time_slots', function (Blueprint $table) {
                $table->id();
                $table->date('date');
                $table->integer('slot_number');
                $table->time('start_time');
                $table->time('end_time');
                $table->string('status')->default('available');
                $table->string('slot_color')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                
                $table->unique(['date', 'slot_number']);
            });
        }

        // Initialize some dates for the next 30 days
        $startDate = now()->format('Y-m-d');
        $endDate = now()->addDays(30)->format('Y-m-d');

        $response = Http::post(url('/cordon/initialize'), [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cordon calendar initialized',
            'tables_created' => true,
            'initialization_response' => $response->json()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

Route::get('/debug-cordon-system', function() {
    try {
        // Check tables
        $tablesExist = [
            'cordon_date_availabilities' => Schema::hasTable('cordon_date_availabilities'),
            'cordon_time_slots' => Schema::hasTable('cordon_time_slots')
        ];

        // Check data
        $dataCounts = [
            'cordon_date_availabilities' => DB::table('cordon_date_availabilities')->count(),
            'cordon_time_slots' => DB::table('cordon_time_slots')->count()
        ];

        // Check sample data
        $sampleDate = now()->format('Y-m-d');
        $sampleData = [
            'date_availability' => DB::table('cordon_date_availabilities')->where('date', $sampleDate)->first(),
            'time_slots' => DB::table('cordon_time_slots')->where('date', $sampleDate)->get()
        ];

        return response()->json([
            'success' => true,
            'tables_exist' => $tablesExist,
            'data_counts' => $dataCounts,
            'sample_date' => $sampleDate,
            'sample_data' => $sampleData,
            'selected_branch_in_session' => session('branch'),
            'endpoints' => [
                'month_colors' => url('/cordon/calendar/month/colors?month=' . now()->format('Y-m')),
                'week_data' => url('/cordon/calendar/week/load-data?date=' . $sampleDate)
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
Route::get('/fix-cordon-schema', function() {
    try {
        // Check if table exists
        if (!Schema::hasTable('cordon_date_availabilities')) {
            return response()->json([
                'success' => false,
                'message' => 'Table cordon_date_availabilities does not exist'
            ]);
        }

        // Get current columns
        $columns = Schema::getColumnListing('cordon_date_availabilities');
        
        $addedColumns = [];
        
        // Add total_slots if missing
        if (!in_array('total_slots', $columns)) {
            DB::statement('ALTER TABLE cordon_date_availabilities ADD COLUMN total_slots INT DEFAULT 9');
            $addedColumns[] = 'total_slots';
        }
        
        // Add booked_slots if missing
        if (!in_array('booked_slots', $columns)) {
            DB::statement('ALTER TABLE cordon_date_availabilities ADD COLUMN booked_slots INT DEFAULT 0');
            $addedColumns[] = 'booked_slots';
        }
        
        // Check if month column exists for getMonthColors method
        if (!in_array('month', $columns)) {
            // Add month column by extracting from date
            DB::statement('ALTER TABLE cordon_date_availabilities ADD COLUMN month VARCHAR(7) GENERATED ALWAYS AS (DATE_FORMAT(date, "%Y-%m")) STORED');
            $addedColumns[] = 'month';
        }

        return response()->json([
            'success' => true,
            'message' => empty($addedColumns) ? 'All columns already exist' : 'Added columns: ' . implode(', ', $addedColumns),
            'current_columns' => Schema::getColumnListing('cordon_date_availabilities')
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
Route::get('/ensure-cordon-tables', function() {
    try {
        // Create cordon_date_availabilities table if it doesn't exist
        if (!Schema::hasTable('cordon_date_availabilities')) {
            Schema::create('cordon_date_availabilities', function (Blueprint $table) {
                $table->id();
                $table->date('date')->unique();
                $table->string('availability_status')->default('available');
                $table->string('date_color')->nullable();
                $table->text('description')->nullable();
                $table->integer('total_slots')->default(9);
                $table->integer('booked_slots')->default(0);
                $table->string('month')->virtualAs('DATE_FORMAT(date, "%Y-%m")');
                $table->timestamps();
            });
        } else {
            // Table exists, ensure all columns are present
            $columns = Schema::getColumnListing('cordon_date_availabilities');
            
            // Add missing columns
            if (!in_array('total_slots', $columns)) {
                Schema::table('cordon_date_availabilities', function (Blueprint $table) {
                    $table->integer('total_slots')->default(9)->after('description');
                });
            }
            
            if (!in_array('booked_slots', $columns)) {
                Schema::table('cordon_date_availabilities', function (Blueprint $table) {
                    $table->integer('booked_slots')->default(0)->after('total_slots');
                });
            }
            
            if (!in_array('month', $columns)) {
                Schema::table('cordon_date_availabilities', function (Blueprint $table) {
                    $table->string('month')->virtualAs('DATE_FORMAT(date, "%Y-%m")')->after('date');
                });
            }
        }

        // Create cordon_time_slots table if it doesn't exist
        if (!Schema::hasTable('cordon_time_slots')) {
            Schema::create('cordon_time_slots', function (Blueprint $table) {
                $table->id();
                $table->date('date');
                $table->integer('slot_number');
                $table->time('start_time');
                $table->time('end_time');
                $table->string('status')->default('available');
                $table->string('slot_color')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                
                $table->unique(['date', 'slot_number']);
            });
        }

        // Populate with sample data for next 30 days if empty
        $dateCount = DB::table('cordon_date_availabilities')->count();
        if ($dateCount === 0) {
            // Initialize date range
            $startDate = now()->format('Y-m-d');
            $endDate = now()->addDays(30)->format('Y-m-d');
            
            $response = Http::post(url('/cordon/initialize'), [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cordon tables ensured successfully',
            'table_status' => [
                'cordon_date_availabilities' => [
                    'exists' => Schema::hasTable('cordon_date_availabilities'),
                    'count' => DB::table('cordon_date_availabilities')->count(),
                    'columns' => Schema::getColumnListing('cordon_date_availabilities')
                ],
                'cordon_time_slots' => [
                    'exists' => Schema::hasTable('cordon_time_slots'),
                    'count' => DB::table('cordon_time_slots')->count(),
                    'columns' => Schema::getColumnListing('cordon_time_slots')
                ]
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
Route::get('/test-cordon-endpoints', function() {
    $date = now()->format('Y-m-d');
    $month = now()->format('Y-m');
    
    $endpoints = [
        'month_colors' => url("/cordon/calendar/month/colors?month={$month}"),
        'week_data' => url("/cordon/calendar/week/load-data?date={$date}")
    ];
    
    $results = [];
    
    foreach ($endpoints as $name => $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $results[$name] = [
            'url' => $url,
            'http_code' => $httpCode,
            'success' => $httpCode === 200,
            'response' => json_decode($response, true) ?? substr($response, 0, 200)
        ];
    }
    
    // Also check database structure
    $dbStructure = [];
    if (Schema::hasTable('cordon_date_availabilities')) {
        $dbStructure['cordon_date_availabilities'] = [
            'columns' => Schema::getColumnListing('cordon_date_availabilities'),
            'sample' => DB::table('cordon_date_availabilities')->where('date', $date)->first()
        ];
    }
    
    return response()->json([
        'endpoint_tests' => $results,
        'database_structure' => $dbStructure,
        'session_branch' => session('branch'),
        'current_date' => $date,
        'current_month' => $month
    ]);
});

Route::get('/debug-cordon-data/{date}', function($date) {
    return response()->json([
        'date_availability' => DB::table('cordon_date_availabilities')->where('date', $date)->first(),
        'time_slots' => DB::table('cordon_time_slots')->where('date', $date)->get(),
        'month_colors' => DB::table('cordon_date_availabilities')
            ->where('date', 'like', substr($date, 0, 7) . '%')
            ->whereNotNull('date_color')
            ->select('date', 'date_color as color', 'description')
            ->get()
    ]);
});

// Add this to web.php
Route::get('/debug-cordon-time-slots/{date}', function($date) {
    $slots = DB::table('cordon_time_slots')
        ->where('date', $date)
        ->select('*')
        ->orderBy('time_slot', 'asc')
        ->get();
    
    $dateData = DB::table('cordon_date_availabilities')
        ->where('date', $date)
        ->first();
    
    return response()->json([
        'date' => $date,
        'date_data' => $dateData,
        'time_slots' => $slots,
        'count' => $slots->count()
    ]);
});Route::get('/debug-cordon-state/{date?}', function($date = null) {
    $date = $date ?: date('Y-m-d');
    
    $tablesExist = [
        'cordon_date_availabilities' => Schema::hasTable('cordon_date_availabilities'),
        'cordon_time_slots' => Schema::hasTable('cordon_time_slots')
    ];
    
    $dateData = null;
    $timeSlots = [];
    
    if ($tablesExist['cordon_date_availabilities']) {
        $dateData = DB::table('cordon_date_availabilities')
            ->where('date', $date)
            ->first();
    }
    
    if ($tablesExist['cordon_time_slots']) {
        $timeSlots = DB::table('cordon_time_slots')
            ->where('date', $date)
            ->orderBy('time_slot', 'asc')
            ->get(['id','date','time','time_slot','slot_number','color','description','booked']);

    }
    
    // Check table structures
    $tableStructures = [];
    foreach ($tablesExist as $table => $exists) {
        if ($exists) {
            $tableStructures[$table] = Schema::getColumnListing($table);
        }
    }
    
    return response()->json([
        'status' => 'success',
        'date' => $date,
        'tables_exist' => $tablesExist,
        'table_structures' => $tableStructures,
        'date_data' => $dateData,
        'time_slots' => $timeSlots,
        'time_slots_count' => count($timeSlots)
    ]);
});
// Add this route for debugging
Route::get('/cordon/calendar/debug-slot-data', [CordonCalendarController::class, 'debugSlotData']);

// Add these missing Cordon routes
Route::prefix('cordon')->group(function () {
    Route::get('/calendar/month/colors', [CordonCalendarController::class, 'getMonthColors'])->name('cordon.calendar.month.colors');
    Route::get('/calendar/date-data', [CordonCalendarController::class, 'getDateData'])->name('cordon.calendar.date.data');
    Route::post('/calendar/save-date-data', [CordonCalendarController::class, 'saveDateData'])->name('cordon.calendar.save.date.data');
    Route::get('/calendar/week/load-data', [CordonCalendarController::class, 'loadWeekData'])->name('cordon.calendar.week.load.data');
    Route::get('/available-times/{date}', [CordonCalendarController::class, 'getAvailableTimes'])->name('cordon.available.times');
    Route::post('/book-slot', [CordonCalendarController::class, 'bookSlot'])->name('cordon.book.slot');
    Route::post('/unbook-slot', [CordonCalendarController::class, 'unbookSlot'])->name('cordon.unbook.slot');
    Route::post('/initialize', [CordonCalendarController::class, 'initializeDateRange'])->name('cordon.initialize');
});

Route::get('/cordon/calendar/check-constraint', [CordonCalendarController::class, 'checkUniqueConstraint']);

//slot indicator
Route::get('/get-schedule', [AppointmentController::class, 'getSched']);


// Backup logs route
Route::get('/appointments/backup-logs', function() {
    // Check if user is admin
    $admin = Auth::user();
    if (!$admin || !in_array($admin->role, ['admin', 'superadmin'])) {
        abort(403, 'Unauthorized access.');
    }
    
    // Get backups with proper ordering
    $backups = \App\Models\Backup::orderBy('created_at', 'desc')->get();
    
    return response()->json($backups);
})->name('appointments.backup-logs');

Route::get('/debug-backup-43', function() {
    $backup = \App\Models\Backup::find(43);
    
    if (!$backup) {
        return response()->json(['error' => 'Backup 43 not found']);
    }
    
    return response()->json([
        'backup' => [
            'id' => $backup->id,
            'file_name' => $backup->file_name,
            'decrypted_file_name' => $backup->decrypted_file_name,
            'file_path' => $backup->file_path,
            'decrypted_file_path' => $backup->decrypted_file_path,
            'created_at' => $backup->created_at,
        ],
        'file_exists' => \Storage::disk('local')->exists($backup->decrypted_file_path),
        'file_path_full' => storage_path('app/' . $backup->decrypted_file_path)
    ]);
});

Route::get('/debug-all-routes', function() {
    $routes = collect(\Route::getRoutes())->map(function ($route) {
        return [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $route->getActionName(),
        ];
    })->filter(function ($route) {
        return str_contains($route['uri'], 'backup');
    });
    
    return response()->json($routes->values());
});

Route::get('/test-route-exists', function() {
    $route = app('router')->getRoutes()->getByName('backup.view');
    
    return response()->json([
        'route_exists' => !is_null($route),
        'route_info' => $route ? [
            'uri' => $route->uri(),
            'methods' => $route->methods(),
            'action' => $route->getActionName(),
        ] : null,
        'test_url' => route('backup.view', ['id' => 43])
    ]);
});

Route::get('/backup/test/{id}', function($id) {
    $backup = \App\Models\Backup::find($id);
    
    if (!$backup) {
        return response()->json(['error' => 'Backup not found'], 404);
    }
    
    return response()->json([
        'status' => 'success',
        'backup' => [
            'id' => $backup->id,
            'name' => $backup->decrypted_file_name,
            'exists' => \Storage::disk('local')->exists($backup->decrypted_file_path)
        ]
    ]);
});

Route::get('/test-backup-exists/{id}', function($backupId) {
    $backup = \App\Models\Backup::find($backupId);
    
    if (!$backup) {
        return response()->json([
            'error' => 'Backup not found in database',
            'id' => $backupId
        ], 404);
    }
    
    $fileExists = \Storage::disk('local')->exists($backup->decrypted_file_path);
    
    return response()->json([
        'backup' => [
            'id' => $backup->id,
            'file_name' => $backup->file_name,
            'decrypted_file_name' => $backup->decrypted_file_name,
            'decrypted_file_path' => $backup->decrypted_file_path,
            'created_at' => $backup->created_at
        ],
        'file_exists' => $fileExists,
        'full_path' => $fileExists ? storage_path('app/' . $backup->decrypted_file_path) : null,
        'direct_url' => route('backup.view', ['id' => $backupId])
    ]);
});

// Add this route for testing CSV parsing
Route::get('/test-csv-parse/{backupId}', function($backupId) {
    $backup = \App\Models\Backup::find($backupId);
    
    if (!$backup) {
        return response()->json(['error' => 'Backup not found'], 404);
    }
    
    $controller = new \App\Http\Controllers\BackupArchivedController();
    return $controller->getCsvDataAsJson($backup);
});

Route::get('/debug-pdf-view/{id}', function($id) {
    $backup = \App\Models\Backup::find($id);
    
    if (!$backup) {
        return "Backup not found";
    }
    
    $path = storage_path('app/' . $backup->decrypted_file_path);
    
    return response()->json([
        'backup_id' => $id,
        'file_name' => $backup->decrypted_file_name,
        'file_path' => $backup->decrypted_file_path,
        'full_path' => $path,
        'file_exists' => file_exists($path),
        'file_size' => file_exists($path) ? filesize($path) : 0,
        'is_pdf' => pathinfo($backup->decrypted_file_name, PATHINFO_EXTENSION) === 'pdf',
        'direct_url' => url('/backup/view/' . $id . '?inline=true'),
        'test_link' => '<a href="' . url('/backup/view/' . $id . '?inline=true') . '" target="_blank">Test PDF Link</a>'
    ]);
});

//csv admin main preview
// Debug route for testing backup preview
Route::get('/debug-backup-preview/{id}', function($id) {
    $backup = \App\Models\Backup::find($id);
    
    if (!$backup) {
        return response()->json(['error' => 'Backup not found'], 404);
    }
    
    $path = storage_path('app/' . $backup->decrypted_file_path);
    
    return response()->json([
        'backup_id' => $id,
        'file_name' => $backup->decrypted_file_name,
        'file_path' => $backup->decrypted_file_path,
        'full_path' => $path,
        'file_exists' => file_exists($path),
        'file_size' => file_exists($path) ? filesize($path) : 0,
        'extension' => pathinfo($backup->decrypted_file_name, PATHINFO_EXTENSION),
        'test_urls' => [
            'pdf_inline' => '/backup/view/' . $id . '?inline=true',
            'csv_json' => '/backup/view/' . $id . '?format=json',
            'download' => '/backup/download/' . $id,
        ]
    ]);
});

// Test CSV parsing directly
Route::get('/test-csv-parse/{id}', function($id) {
    $backup = \App\Models\Backup::find($id);
    
    if (!$backup) {
        return response()->json(['error' => 'Backup not found'], 404);
    }
    
    $path = storage_path('app/' . $backup->decrypted_file_path);
    
    if (!file_exists($path)) {
        return response()->json(['error' => 'File not found'], 404);
    }
    
    // Read first few lines
    $lines = [];
    if (($handle = fopen($path, 'r')) !== false) {
        for ($i = 0; $i < 10; $i++) {
            $line = fgetcsv($handle);
            if ($line === false) break;
            $lines[] = $line;
        }
        fclose($handle);
    }
    
    return response()->json([
        'file_name' => $backup->decrypted_file_name,
        'first_10_lines' => $lines,
        'line_count' => count($lines)
    ]);
});

Route::get('/backup/view/{backupId}/csv', [BackupArchivedController::class, 'viewCsvBackup']);
Route::get('/backup/view/{backupId}', [BackupArchivedController::class, 'viewBackupFile']);

Route::get('/debug-backup-file/{id}', function($id) {
    $backup = \App\Models\Backup::find($id);
    
    if (!$backup) {
        return response()->json(['error' => 'Backup not found'], 404);
    }
    
    $path = storage_path('app/' . $backup->decrypted_file_path);
    
    return response()->json([
        'backup' => [
            'id' => $backup->id,
            'encrypted_file_name' => $backup->file_name,
            'decrypted_file_name' => $backup->decrypted_file_name,
            'decrypted_file_path' => $backup->decrypted_file_path,
            'full_path' => $path,
            'file_exists' => file_exists($path),
            'file_size' => file_exists($path) ? filesize($path) : 0,
            'is_readable' => file_exists($path) ? is_readable($path) : false
        ],
        'storage_disk_files' => \Storage::files('backups'),
        'backups_in_db' => \App\Models\Backup::count()
    ]);
});


Route::get('/test-file-existence/{backupId}', function($backupId) {
    try {
        $backup = \App\Models\Backup::findOrFail($backupId);
        
        $path = storage_path('app/' . $backup->decrypted_file_path);
        
        return response()->json([
            'backup' => [
                'id' => $backup->id,
                'encrypted_name' => $backup->file_name,
                'decrypted_name' => $backup->decrypted_file_name,
                'decrypted_path' => $backup->decrypted_file_path,
                'full_path' => $path,
                'exists' => file_exists($path),
                'readable' => file_exists($path) ? is_readable($path) : false,
                'size' => file_exists($path) ? filesize($path) : 0,
                'extension' => pathinfo($backup->decrypted_file_name, PATHINFO_EXTENSION)
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/test-backup-model/{id}', function($id) {
    $backup = \App\Models\Backup::find($id);
    
    if (!$backup) {
        return response()->json(['error' => 'Backup not found'], 404);
    }
    
    return response()->json([
        'model_data' => [
            'attributes' => $backup->getAttributes(),
            'appends' => $backup->getAppends(),
            'decrypted_file_name' => $backup->decrypted_file_name,
            'decrypted_file_path' => $backup->decrypted_file_path,
            'methods' => get_class_methods($backup)
        ]
    ]);
});

Route::get('/backup/view/{id}/csv', [BackupArchivedController::class, 'viewCsvBackup']);


//admin account setting



Route::middleware(['auth', 'validate.tab.session', 'authorize.role:admin,superadmin,lawyer'])->prefix('admin')->group(function () {
    Route::get('/account/settings', [AdminAccountSettingController::class, 'index'])
        ->name('admin.account.settings');

    Route::get('/account/settings/forgot-password', [AdminForgotPasswordController::class, 'showEmailForm'])
        ->name('admin.account.settings.forgot-password.email');

    Route::post('/account/settings/forgot-password/send-code', [AdminForgotPasswordController::class, 'sendCode'])
        ->name('admin.account.settings.forgot-password.send');

    Route::get('/account/settings/forgot-password/verify', [AdminForgotPasswordController::class, 'showVerifyForm'])
        ->name('admin.account.settings.forgot-password.verify');

    Route::post('/account/settings/forgot-password/verify', [AdminForgotPasswordController::class, 'verifyCode'])
        ->name('admin.account.settings.forgot-password.verify.submit');

    Route::get('/account/settings/forgot-password/reset', [AdminForgotPasswordController::class, 'showResetForm'])
        ->name('admin.account.settings.forgot-password.reset');

    Route::post('/account/settings/forgot-password/reset', [AdminForgotPasswordController::class, 'resetPassword'])
        ->name('admin.account.settings.forgot-password.reset.submit');
    
    Route::put('/account/settings/profile', [AdminAccountSettingController::class, 'updateProfile'])
        ->name('admin.account.settings.update.profile');
    
    Route::put('/account/settings/password', [AdminAccountSettingController::class, 'updatePassword'])
        ->name('admin.account.settings.update.password');
    
    Route::delete('/account/settings/delete', [AdminAccountSettingController::class, 'deleteAccount'])
        ->name('admin.account.settings.delete');

    Route::get('/lawyer/office-requests', [\App\Http\Controllers\AppointmentController::class, 'lawyerOfficeRequests'])
        ->name('admin.lawyer.office.requests');
});

Route::get('/admin/account-setting', [AdminAccountSettingController::class, 'index'])
    ->name('admin.account.setting');


    Route::get('/test-mail/{id}', function($id) {
    try {
        $appointment = \App\Models\Appointment::find($id);
        
        if (!$appointment) {
            return "Appointment not found";
        }
        
        \Log::info('Testing email for appointment: ' . $appointment->id);
        \Log::info('Email address: ' . $appointment->email);
        
        Mail::send('appointment_approved', ['appointment' => $appointment], function($message) use ($appointment) {
            $message->to($appointment->email)
                    ->subject('Test Email - LegalConnect');
        });
        
        return "Test email sent to " . $appointment->email;
        
    } catch (\Exception $e) {
        \Log::error('Test email error: ' . $e->getMessage());
        return "Error: " . $e->getMessage();
    }
});

// Appointment approval routes
//Route::post('/appointments/{id}/approve', [AppointmentController::class, 'approve'])->name('appointments.approve');
//Route::post('/appointments/{id}/deny', [AppointmentController::class, 'deny'])->name('appointments.deny');
Route::get('/appointments/{id}', [AppointmentController::class, 'show'])->name('appointments.show');

// Alternative routes (if used)
//Route::post('/appointments/{id}/accept', [AppointmentController::class, 'accept'])->name('appointments.accept');
//Route::post('/appointments/{id}/deny-appointment', [AppointmentController::class, 'denyAppointment'])->name('appointments.denyAppointment');

Route::get('/test-email-smtp', function() {
    try {
        // Test SMTP connection first
        $transport = new \Swift_SmtpTransport(
            env('MAIL_HOST'),
            env('MAIL_PORT'),
            env('MAIL_ENCRYPTION')
        );
        $transport->setUsername(env('MAIL_USERNAME'));
        $transport->setPassword(env('MAIL_PASSWORD'));
        
        $mailer = new \Swift_Mailer($transport);
        $mailer->getTransport()->start();
        
        return 'SMTP connection successful!';
        
    } catch (\Swift_TransportException $e) {
        return 'SMTP connection failed: ' . $e->getMessage();
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::get('/test-send-email', function() {
    try {
        \Mail::raw('This is a test email from LegalConnect', function($message) {
            $message->to('cafirma.jerome2002@gmail.com')
                    ->subject('Test Email from LegalConnect');
        });
        
        return 'Email sent successfully to cafirma.jerome2002@gmail.com';
        
    } catch (\Exception $e) {
        return 'Failed to send email: ' . $e->getMessage();
    }
});

//email cahtting Test Route to Debug Timestamps

Route::get('/debug-chat-timestamps/{email}', function($email) {
    try {
        $conversation = DB::table('chattbl')
            ->where('sender_email', $email)
            ->orWhere('receiver_email', $email)
            ->orderBy('timestamp_normalized', 'asc')
            ->select('id', 'sender_email', 'receiver_email', 'subject', 
                    'created_at', 'timestamp_normalized', 
                    DB::raw('DATE_FORMAT(timestamp_normalized, "%Y-%m-%d %h:%i:%s %p") as formatted_time'),
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %h:%i:%s %p") as created_formatted'),
                    'message_type')
            ->get();
        
        return response()->json([
            'email' => $email,
            'count' => $conversation->count(),
            'messages' => $conversation,
            'current_time' => Carbon::now('Asia/Manila')->format('Y-m-d H:i:s'),
            'current_formatted' => Carbon::now('Asia/Manila')->format('M j, Y g:i A')
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// In routes/web.php temporarily
Route::get('/fix-chat-timestamps-now', function() {
    try {
        // Step 1: Populate timestamp_normalized
        DB::statement("
            UPDATE chattbl 
            SET timestamp_normalized = 
                CASE 
                    WHEN created_at LIKE '____-__-__ %' THEN created_at
                    WHEN created_at LIKE '____-__-__' THEN CONCAT(created_at, ' 00:00:00')
                    WHEN created_at LIKE '__:__:__' THEN 
                        CASE 
                            WHEN updated_at LIKE '____-__-__ %' THEN CONCAT(SUBSTRING(updated_at, 1, 10), ' ', created_at)
                            WHEN updated_at LIKE '____-__-__' THEN CONCAT(updated_at, ' ', created_at)
                            ELSE CONCAT('1970-01-01 ', created_at)
                        END
                    ELSE created_at
                END
            WHERE timestamp_normalized IS NULL
        ");
        
        $updated = DB::select("SELECT ROW_COUNT() as affected");
        
        // Step 2: Verify data
        $sample = DB::table('chattbl')
            ->select('id', 'created_at', 'timestamp_normalized', 
                    DB::raw('DATE_FORMAT(timestamp_normalized, "%M %e, %Y %l:%i %p") as display_time'))
            ->whereNotNull('timestamp_normalized')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();
        
        return response()->json([
            'success' => true,
            'updated_count' => $updated[0]->affected ?? 0,
            'sample_data' => $sample,
            'instructions' => [
                '1. Run this fix script',
                '2. Update EmailReceiverController to use orderBy(\'timestamp_normalized\', \'asc\')',
                '3. Update display logic to use timestamp_normalized directly',
                '4. Clear browser cache and reload email chat'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
// route to fix existing data
Route::get('/fix-all-chat-timestamps', function() {
    try {
        // Step 1: Update all records to have proper timestamp_normalized
        DB::statement("
            UPDATE chattbl 
            SET timestamp_normalized = 
                CASE 
                    WHEN timestamp_normalized IS NOT NULL 
                    THEN CONCAT(timestamp_normalized, ' +08:00')
                    WHEN created_at IS NOT NULL 
                    THEN CONCAT(DATE(created_at), ' ', 
                           CASE 
                               WHEN TIME(created_at) = '00:00:00' 
                               THEN '12:00:00' 
                               ELSE TIME(created_at) 
                           END, ' +08:00')
                    ELSE CONCAT(CURRENT_DATE, ' 12:00:00 +08:00')
                END
        ");
        
        // Step 2: Check the fix
        $sampleMessages = DB::table('chattbl')
            ->select('id', 'sender_email', 'receiver_email', 
                    'created_at', 'timestamp_normalized',
                    DB::raw("DATE_FORMAT(CONVERT_TZ(
                        STR_TO_DATE(REPLACE(timestamp_normalized, ' +08:00', ''), '%Y-%m-%d %H:%i:%s'),
                        '+00:00', 
                        '+08:00'
                    ), '%b %e, %Y %l:%i %p') as manila_time"))
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Fixed all chat timestamps with Manila timezone',
            'sample_data' => $sampleMessages,
            'instructions' => 'Now reload your email chat page'
        ]);
        
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

//debug route for receiving email
Route::get('/debug-email-timestamp/{id}', function($id) {
    try {
        $message = DB::table('chattbl')->find($id);
        
        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }
        
        // Test different parsing methods
        $rawTimestamp = $message->timestamp_normalized ?? $message->created_at;
        
        $tests = [];
        
        // Method 1: Your current method
        $tests['current_method'] = [
            'input' => $rawTimestamp,
            'output' => Carbon::parse($rawTimestamp . ' +08:00')->format('M j, Y g:i A'),
            'timezone' => 'Asia/Manila (+08:00)'
        ];
        
        // Method 2: Assume it's already Manila time
        $tests['as_manila_time'] = [
            'input' => $rawTimestamp,
            'output' => Carbon::createFromFormat('Y-m-d H:i:s', $rawTimestamp, 'Asia/Manila')->format('M j, Y g:i A'),
            'timezone' => 'Asia/Manila (explicit)'
        ];
        
        // Method 3: Parse as UTC then convert to Manila
        $tests['utc_to_manila'] = [
            'input' => $rawTimestamp,
            'output' => Carbon::parse($rawTimestamp)->setTimezone('Asia/Manila')->format('M j, Y g:i A'),
            'timezone' => 'UTC → Asia/Manila'
        ];
        
        return response()->json([
            'message_id' => $message->id,
            'sender_email' => $message->sender_email,
            'receiver_email' => $message->receiver_email,
            'message_type' => $message->message_type,
            'raw_timestamp_normalized' => $message->timestamp_normalized,
            'raw_created_at' => $message->created_at,
            'tests' => $tests,
            'actual_email_time' => 'Jan 2, 2026 4:39 PM',
            'current_display' => 'Jan 3, 2026 12:39 AM',
            'diagnosis' => 'Look for which test matches the actual email time (4:39 PM)'
        ]);
        
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
// Fix all existing chat timestamps
Route::get('/fix-all-email-timestamps', function() {
    try {
        // First, fix incoming emails (message_type = 'incoming')
        $incomingMessages = DB::table('chattbl')
            ->where('message_type', 'incoming')
            ->where('sender_role', 'email')
            ->get();
        
        $updatedCount = 0;
        
        foreach ($incomingMessages as $msg) {
            $timestamp = $msg->timestamp_normalized ?? $msg->created_at;
            
            if ($timestamp) {
                try {
                    // Parse the timestamp
                    $date = Carbon::parse($timestamp);
                    
                    // If it looks like UTC time (ends with 00:00:00 or has Z), convert to Manila
                    if ($date->format('H:i:s') === '00:00:00' || 
                        strpos($timestamp, 'Z') !== false ||
                        $date->hour < 12) { // If hour is AM (likely UTC converted wrong)
                        
                        // Add 8 hours to convert UTC to Manila
                        $manilaTime = $date->addHours(8)->setTimezone('Asia/Manila')->toDateTimeString();
                        
                        DB::table('chattbl')
                            ->where('id', $msg->id)
                            ->update([
                                'timestamp_normalized' => $manilaTime,
                                'created_at' => $manilaTime,
                                'updated_at' => now()
                            ]);
                        
                        $updatedCount++;
                        Log::info("Fixed message ID {$msg->id}: {$timestamp} -> {$manilaTime}");
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to fix message ID {$msg->id}: " . $e->getMessage());
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => "Fixed {$updatedCount} incoming email timestamps",
            'updated' => $updatedCount,
            'total_incoming' => $incomingMessages->count()
        ]);
        
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/debug-timestamp', [EmailReceiverController::class, 'debugTimestamp']);

Route::get('/debug-email-time/{dateString}', function($dateString) {
    try {
        // Decode the URL-encoded string
        $decodedString = urldecode($dateString);
        
        $tests = [
            'Input (raw)' => $dateString,
            'Input (decoded)' => $decodedString,
            'Current Manila time' => Carbon::now('Asia/Manila')->format('M j, Y g:i A'),
            'Parsed as Manila (no timezone specified)' => Carbon::parse($decodedString)->format('M j, Y g:i A'),
            'Parsed with Manila timezone' => Carbon::parse($decodedString, 'Asia/Manila')->format('M j, Y g:i A'),
            'Parsed as UTC then converted to Manila' => Carbon::parse($decodedString, 'UTC')->setTimezone('Asia/Manila')->format('M j, Y g:i A'),
            'Using createFromFormat' => Carbon::createFromFormat('M j, Y g:i a', $decodedString, 'Asia/Manila')->format('M j, Y g:i A'),
        ];
        
        return response()->json($tests);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

// Admin Appointment Scheduling Routes (Multi-Office Support)
Route::prefix('admin')->middleware(['auth', 'authorize.role:admin,superadmin'])->group(function () {
    // Set office for admin session
    Route::post('/set-office', function(Request $request) {
        $request->validate(['office_id' => 'required|exists:law_offices,id']);
        session(['law_office_id' => $request->office_id]);
        return response()->json(['status' => 'success', 'message' => 'Office set successfully']);
    })->name('admin.set-office');
    
    // Scheduling - List all office schedules
    Route::get('/scheduling', [AppointmentSchedulingController::class, 'index'])->name('admin.scheduling.index');
    
    // Scheduling per office
    Route::prefix('/scheduling/{officeId}')->group(function () {
        Route::get('/calendar', [AppointmentSchedulingController::class, 'showCalendar'])->name('admin.scheduling.calendar');
        Route::get('/slots', [AppointmentSchedulingController::class, 'getSlots'])->name('admin.scheduling.slots');
        Route::post('/slot/store', [AppointmentSchedulingController::class, 'storeSlot'])->name('admin.scheduling.slot.store');
        Route::put('/slot/{slotId}', [AppointmentSchedulingController::class, 'updateSlot'])->name('admin.scheduling.slot.update');
        Route::delete('/slot/{slotId}', [AppointmentSchedulingController::class, 'destroySlot'])->name('admin.scheduling.slot.destroy');
        Route::post('/availability', [AppointmentSchedulingController::class, 'storeAvailability'])->name('admin.scheduling.availability.store');
    });
});

// Admin Notification Routes
Route::prefix('admin')->middleware(['auth', 'authorize.role:admin,superadmin'])->group(function () {
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('admin.notifications');
    Route::get('/notifications/unread', [AdminNotificationController::class, 'getUnread'])->name('admin.notifications.unread');
    Route::post('/notifications/{id}/read', [AdminNotificationController::class, 'markAsRead'])->name('admin.notifications.mark-read');
    Route::post('/notifications/mark-all-read', [AdminNotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark-all-read');
    Route::get('/notifications/count', [AdminNotificationController::class, 'getCount'])->name('admin.notifications.count');
});
// Add to web.php
Route::get('/test-notification', function() {
    try {
        // Check if user is admin
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Create a test appointment
        $appointment = \App\Models\Appointment::create([
            'fullname' => 'Test User',
            'address' => 'Test Address',
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'category' => 'Test Category',
            'case_name' => 'Test Case',
            'selected_branch' => 'Diffun Branch Office',
            'selected_date' => now()->addDays(1)->format('Y-m-d'),
            'selected_time' => '09:00 AM',
            'term_status' => 'pending',
            'appointment_approval' => 'pending',
        ]);
        
        // Create notification using the corrected method
        \App\Models\AdminNotification::create([
            'type' => 'pending_request',
            'title' => 'New Appointment Request',
            'message' => 'New appointment request from ' . $appointment->fullname . ' for ' . $appointment->selected_date . ' at ' . $appointment->selected_time,
            'appointment_id' => $appointment->id,
            'is_read' => false
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Test notification created',
            'appointment_id' => $appointment->id,
            'notification_count' => \App\Models\AdminNotification::where('is_read', false)->count()
        ]);
    } catch (\Exception $e) {
        \Log::error('Test notification error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
})->middleware('auth');

//routes notif
Route::get('/debug-notification-test', function() {
    try {
        // Test if we can create a notification
        $notification = \App\Models\AdminNotification::create([
            'type' => 'test',
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
            'appointment_id' => null,
            'is_read' => false
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Notification created successfully',
            'notification' => $notification,
            'total_unread' => \App\Models\AdminNotification::where('is_read', false)->count()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Admin Message Notification Routes

Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Message notification routes
    Route::prefix('message-notifications')->group(function () {
        Route::get('/unread', [AdminMessageNotifController::class, 'getUnread'])->name('admin.message-notifications.unread');
        Route::get('/count', [AdminMessageNotifController::class, 'getUnreadCount'])->name('admin.message-notifications.count');
        Route::post('/{id}/read', [AdminMessageNotifController::class, 'markAsRead'])->name('admin.message-notifications.read');
        Route::post('/mark-all-read', [AdminMessageNotifController::class, 'markAllAsRead'])->name('admin.message-notifications.mark-all-read');
        Route::post('/create-test', [AdminMessageNotifController::class, 'createTestNotificationForDashboard'])
            ->name('admin.message-notifications.create-test');
        Route::get('/all', [AdminMessageNotifController::class, 'getAll'])->name('admin.message-notifications.all');
    });
});


// Chat notification routes
Route::post('/chat/create-notification', [ChatController::class, 'createNotification'])->name('chat.create-notification');
Route::get('/chat/notifications', [ChatController::class, 'getNotifications'])->name('chat.notifications');
Route::post('/chat/mark-notification-read/{id}', [ChatController::class, 'markNotificationAsRead'])->name('chat.mark-notification-read');

// Message notification routes
Route::middleware(['auth'])->group(function () {
    Route::post('/chat/mark-all-read', [ChatController::class, 'markAllAsRead'])->name('chat.mark-all-read');
    Route::post('/chat/send', [ChatController::class, 'clientSendMessage'])->name('client.chat.send');
    Route::post('/chat/create-notification', [ChatController::class, 'createNotification'])->name('chat.create-notification');
});

// Test route for message notifications (remove in production)
Route::post('/admin/message-notifications/test', function() {
    $user = Auth::user();
    
    // Create a test notification
    $notification = \App\Models\MessageNotification::create([
        'user_id' => $user->id,
        'type' => 'system_chat',
        'title' => 'Test Message Notification',
        'message' => 'This is a test notification for the message system.',
        'is_read' => false
    ]);
    
    return response()->json([
        'success' => true,
        'message' => 'Test message notification created successfully',
        'notification' => $notification
    ]);
})->middleware('auth:admin')->name('admin.message-notifications.test');

Route::get('/test-message-notif', function() {
    try {
        $admin = Auth::user();
        
        if (!$admin || !in_array($admin->role, ['admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized - Not an admin'
            ], 403);
        }

        // Check if table exists
        $tableExists = Schema::hasTable('admin_message_notif');
        
        if (!$tableExists) {
            return response()->json([
                'success' => false,
                'error' => 'Table admin_message_notif does not exist'
            ], 500);
        }

        // Get count of all notifications
        $allCount = AdminMessageNotif::count();
        $unreadCount = AdminMessageNotif::where('receiver_id', $admin->id)
            ->where('is_read', false)
            ->count();
        
        // Get some sample notifications
        $sampleNotifications = AdminMessageNotif::where('receiver_id', $admin->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at
                ];
            });

        return response()->json([
            'success' => true,
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'admin_role' => $admin->role,
            'table_exists' => $tableExists,
            'total_notifications_in_db' => $allCount,
            'unread_count_for_admin' => $unreadCount,
            'sample_notifications' => $sampleNotifications,
            'message' => 'Test successful'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->middleware('auth');

// temporary route for debugging MESSAGE NOTIF TEST
Route::post('/admin/message-notifications/create-test-debug', function() {
    try {
        $admin = Auth::user();
        
        if (!$admin || !in_array($admin->role, ['admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Create a test notification
        $notification = \App\Models\AdminMessageNotif::create([
            'type' => 'system_chat',
            'title' => 'Test Message Notification',
            'message' => 'This is a test message notification from the system. Created at: ' . now()->format('Y-m-d H:i:s'),
            'sender_id' => $admin->id,
            'sender_name' => $admin->name,
            'sender_email' => $admin->email,
            'receiver_id' => $admin->id,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Test notification created successfully',
            'notification' => $notification,
            'unread_count' => \App\Models\AdminMessageNotif::where('receiver_id', $admin->id)
                ->where('is_read', false)
                ->count()
        ]);
    } catch (\Exception $e) {
        \Log::error('Error creating test notification: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
})->middleware('auth');

// Contact form routes
Route::get('/message', [App\Http\Controllers\ConcernsInquiriesController::class, 'create'])
    ->name('message.create');

Route::post('/message', [App\Http\Controllers\ConcernsInquiriesController::class, 'store'])
    ->name('message.store');

// Admin routes for messages (protected by auth middleware)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/messages', [App\Http\Controllers\ConcernsInquiriesController::class, 'index'])
        ->name('messages.index')
        ->middleware('can:viewAny,App\Models\ConcernsInquiriesMessage');
    
    Route::get('/admin/messages/{id}', [App\Http\Controllers\ConcernsInquiriesController::class, 'show'])
        ->name('messages.show')
        ->middleware('can:view,message');
    
    Route::put('/admin/messages/{id}/status', [App\Http\Controllers\ConcernsInquiriesController::class, 'updateStatus'])
        ->name('messages.update-status')
        ->middleware('can:update,message');
    
    Route::delete('/admin/messages/{id}', [App\Http\Controllers\ConcernsInquiriesController::class, 'destroy'])
        ->name('messages.destroy')
        ->middleware('can:delete,message');
    
    Route::get('/admin/messages/statistics', [App\Http\Controllers\ConcernsInquiriesController::class, 'statistics'])
        ->name('messages.statistics');
});

Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

// user account update
Route::middleware(['auth'])->group(function () {
    Route::get('/account/edit', [AccountUpdateController::class, 'edit'])->name('account.edit');
    Route::post('/account/update', [AccountUpdateController::class, 'update'])->name('account.update');
    Route::post('/account/request-password-change', [AccountUpdateController::class, 'requestPasswordChange'])->name('account.request.password.change');
    Route::post('/account/verify-otp-password', [AccountUpdateController::class, 'verifyOtpAndUpdatePassword'])->name('account.verify.otp.password');
    Route::post('/account/resend-otp', [AccountUpdateController::class, 'resendOtp'])->name('account.resend.otp');
});
// Test email configuration
Route::get('/test-email-config', function() {
    try {
        // Test email sending using Laravel's Mail facade
        Mail::raw('Test email from LegalConnect', function($message) {
            $message->to('cafirma.jerome2002@gmail.com')
                    ->subject('Test Email Configuration')
                    ->from(
                        env('MAIL_FROM_ADDRESS', 'noreply@legalconnect.com'),
                        env('MAIL_FROM_NAME', 'LegalConnect')
                    );
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Test email sent successfully!',
            'config' => [
                'driver' => env('MAIL_MAILER'),
                'host' => env('MAIL_HOST'),
                'port' => env('MAIL_PORT'),
                'username' => env('MAIL_USERNAME'),
                'from_address' => env('MAIL_FROM_ADDRESS'),
                'from_name' => env('MAIL_FROM_NAME'),
                'encryption' => env('MAIL_ENCRYPTION'),
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Email sending failed: ' . $e->getMessage(),
            'config' => [
                'host' => env('MAIL_HOST'),
                'port' => env('MAIL_PORT'),
                'username' => env('MAIL_USERNAME'),
                'from_address' => env('MAIL_FROM_ADDRESS'),
                'encryption' => env('MAIL_ENCRYPTION'),
            ]
        ], 500);
    }
});


// Send a test email
Route::get('/send-test-email', function() {
    try {
        \Mail::raw('Test email from LegalConnect', function($message) {
            $message->to('cafirma.jerome2002@gmail.com')
                    ->subject('Test Email from LegalConnect');
        });
        
        return 'Test email sent successfully!';
        
    } catch (\Exception $e) {
        return 'Failed to send test email: ' . $e->getMessage();
    }
});
Route::get('/test-email-account-update', function() {
    try {
        // Get the authenticated user
        $user = Auth::user();
        
        if (!$user) {
            return "No user logged in. Please log in first.";
        }
        
        // Test with Laravel Mail facade
        $hardcodedResult = false;
        $hardcodedError = null;
        try {
            Mail::raw('Test email from LegalConnect', function($message) {
                $message->to('cafirma.jerome2002@gmail.com')
                        ->subject('Test Email from LegalConnect')
                        ->from(
                            env('MAIL_FROM_ADDRESS', 'noreply@legalconnect.com'),
                            env('MAIL_FROM_NAME', 'LegalConnect')
                        );
            });
            $hardcodedResult = true;
        } catch (\Exception $e) {
            $hardcodedResult = false;
            $hardcodedError = $e->getMessage();
        }
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'tests' => [
                'laravel_mail_facade' => [
                    'success' => $hardcodedResult,
                    'error' => $hardcodedError
                ]
            ],
            'mail_config' => [
                'driver' => env('MAIL_MAILER'),
                'host' => env('MAIL_HOST'),
                'port' => env('MAIL_PORT'),
                'username' => env('MAIL_USERNAME'),
                'from_address' => env('MAIL_FROM_ADDRESS'),
                'from_name' => env('MAIL_FROM_NAME'),
                'encryption' => env('MAIL_ENCRYPTION'),
                'password_length' => strlen(env('MAIL_PASSWORD', '')),
            ],
            'analysis' => [
                'issue' => 'Email configuration might be incorrect',
                'likely_cause' => 'SMTP settings, password, or 2-Factor Authentication',
                'solution' => 'Check .env file settings and ensure proper SMTP configuration'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->middleware('auth');

Route::get('/test-google-settings', function() {
    $username = env('MAIL_USERNAME');
    $password = env('MAIL_PASSWORD');
    
    return response()->json([
        'email_configuration' => [
            'username' => $username,
            'password_exists' => !empty($password),
            'password_length' => strlen($password),
            'is_app_password_format' => strlen($password) === 16 && !str_contains($password, ' '),
            'common_issues' => [
                '2fa_enabled' => 'If you have 2FA, you MUST use an App Password',
                'less_secure_apps' => 'Less secure app access might be disabled',
                'app_password_needed' => 'Check if this is actually an App Password'
            ]
        ],
        'steps_to_fix' => [
            '1. Go to https://myaccount.google.com/security',
            '2. Scroll to "Signing in to Google"',
            '3. If 2-Step Verification is ON, generate an App Password',
            '4. Go to https://myaccount.google.com/lesssecureapps and turn ON (if no 2FA)',
            '5. Use the App Password in your .env file'
        ]
    ]);
});

Route::get('/app-password-instructions', function() {
    return '
    <html>
    <head><title>Generate App Password - Legal Connect</title></head>
    <body style="font-family: Arial, sans-serif; padding: 20px;">
        <h2>How to Generate a Gmail App Password</h2>
        
        <div style="background: #f0f8ff; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3>Step-by-Step Instructions:</h3>
            <ol>
                <li>Go to: <a href="https://myaccount.google.com/security" target="_blank">Google Security Settings</a></li>
                <li>Scroll to "Signing in to Google" section</li>
                <li>If "2-Step Verification" is ON:
                    <ul>
                        <li>Click on "2-Step Verification"</li>
                        <li>Scroll to bottom and click "App passwords"</li>
                        <li>Select "Mail" as the app</li>
                        <li>Select "Windows Computer" or "Other" as device</li>
                        <li>Click "Generate"</li>
                        <li>Copy the 16-character password (no spaces)</li>
                    </ul>
                </li>
                <li>If "2-Step Verification" is OFF:
                    <ul>
                        <li>Go to: <a href="https://myaccount.google.com/lesssecureapps" target="_blank">Less secure app access</a></li>
                        <li>Turn ON "Allow less secure apps"</li>
                        <li>Use your regular Gmail password</li>
                    </ul>
                </li>
            </ol>
        </div>
        
        <div style="background: #fff3cd; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3>⚠️ Important Notes:</h3>
            <ul>
                <li>App Passwords are 16 characters (e.g., "abcd efgh ijkl mnop" but without spaces)</li>
                <li>Remove any spaces from the generated password</li>
                <li>Update your .env file: <code>MAIL_PASSWORD=your_16_char_password</code></li>
                <li>Clear cache: <code>php artisan config:clear</code></li>
            </ul>
        </div>
        
        <form method="POST" action="/test-app-password" style="margin-top: 30px;">
            @csrf
            <h3>Test Your App Password:</h3>
            <input type="text" name="app_password" placeholder="Enter your 16-character App Password" 
                   style="width: 300px; padding: 10px; margin-right: 10px;">
            <button type="submit" style="padding: 10px 20px; background: #28a745; color: white; border: none;">
                Test This Password
            </button>
        </form>
    </body>
    </html>
    ';
});

Route::post('/test-app-password', function(Request $request) {
    $password = str_replace(' ', '', $request->input('app_password'));
    
    try {
        // Test the password with Gmail SMTP
        $transport = new \Swift_SmtpTransport('smtp.gmail.com', 587, 'tls');
        $transport->setUsername('cafirma.jerome2002@gmail.com');
        $transport->setPassword($password);
        
        $mailer = new \Swift_Mailer($transport);
        $mailer->getTransport()->start();
        
        // Also try sending a test email
        config(['mail.mailers.smtp.password' => $password]);
        
        \Mail::raw('Test email using App Password', function($message) {
            $message->to('cafirma.jerome2002@gmail.com')
                    ->subject('App Password Test - Success!');
        });
        
        return response()->json([
            'success' => true,
            'message' => '✅ App Password works perfectly!',
            'instructions' => [
                '1. Update your .env file:',
                'MAIL_PASSWORD=' . $password,
                '2. Clear config cache: php artisan config:clear',
                '3. Test your account update feature again'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'password_tested' => substr($password, 0, 4) . '...' . substr($password, -4),
            'password_length' => strlen($password),
            'suggestions' => [
                'Make sure you generated the App Password correctly',
                'Try port 465 with SSL instead of 587 with TLS',
                'Go back and regenerate the App Password'
            ]
        ]);
    }
});
Route::post('/test-password', function(Request $request) {
    $password = $request->input('password');
    
    try {
        // Temporarily change config to test password
        config([
            'mail.mailers.smtp.username' => 'cafirma.jerome2002@gmail.com',
            'mail.mailers.smtp.password' => $password,
        ]);
        
        Mail::raw('Test password', function($message) {
            $message->to('cafirma.jerome2002@gmail.com')
                    ->subject('Test Password')
                    ->from(
                        env('MAIL_FROM_ADDRESS', 'noreply@legalconnect.com'),
                        env('MAIL_FROM_NAME', 'LegalConnect')
                    );
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Password works! Use this in your .env file:',
            'password' => $password,
            'env_line' => 'MAIL_PASSWORD=' . $password
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'suggestion' => 'Make sure you\'re using an App Password if 2FA is enabled'
        ]);
    }
});


Route::get('/test-otp-email', function() {
    try {
        // Test the exact email address from your form
        $toEmail = 'geralynmanuel566@gmail.com';
        
        \Mail::raw('Test OTP Email: 123456', function($message) use ($toEmail) {
            $message->to($toEmail)
                   ->subject('Test OTP Email')
                   ->from('cafirma.jerome2002@gmail.com', 'LegalConnect');
        });
        
        return 'Test email sent to ' . $toEmail;
        
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage() . 
               '<br><br>Check: 1. App Password 2. 2FA enabled 3. Less secure apps access';
    }
});

// Add this to web.php temporarily
Route::get('/test-smtp-fix', function() {
    try {
        // Test simple email
        \Mail::raw('Test OTP Email from LegalConnect', function($message) {
            $message->to('geralynmanuel566@gmail.com')
                   ->subject('Test OTP Email')
                   ->from('cafirma.jerome2002@gmail.com', 'LegalConnect');
        });
        
        \Log::info('Test email sent successfully');
        return 'Email sent successfully! Check logs.';
        
    } catch (\Exception $e) {
        \Log::error('Email error: ' . $e->getMessage());
        return 'Error: ' . $e->getMessage();
    }
});

//test for insert/update staff account
Route::get('/debug-user-data/{id}', function($id) {
    $user = \App\Models\User::find($id);
    return response()->json([
        'user' => $user,
        'fillable' => $user->getFillable(),
        'attributes' => $user->getAttributes()
    ]);
});

Route::post('/test-role-submission', function(Request $request) {
    return response()->json([
        'role_value' => $request->role,
        'all_data' => $request->all(),
        'role_type' => gettype($request->role),
        'role_is_null' => is_null($request->role),
        'role_is_empty' => empty($request->role),
        'role_is_string' => is_string($request->role)
    ]);
});

Route::get('/debug-user-creation', function() {
    try {
        // Test creating a user directly
        $user = new \App\Models\User();
        $user->name = 'Test Staff';
        $user->username = 'test@example.com'; // Required field
        $user->email = 'test@example.com';
        $user->cp_number = '1234567890';
        $user->password = bcrypt('Test@123');
        $user->role = 'staff';
        $user->is_verified = true;
        $user->email_verified_at = now();
        
        $user->save();
        
        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'user' => $user
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Add to web.php temporarily
Route::get('/test-direct-insert', function() {
    try {
        $user = new \App\Models\User();
        $user->name = 'Test User';
        $user->username = 'test@example.com';
        $user->email = 'test@example.com';
        $user->cp_number = '1234567890';
        $user->password = bcrypt('Test@123');
        $user->role = 'diffun_staff'; // Explicitly set role
        $user->is_verified = true;
        $user->email_verified_at = now();
        
        $user->save();
        
        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'user' => $user->toArray()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
Route::post('/adminAccount/staff/create-test', [AdminAccountController::class, 'createStaffTest'])->name('adminAccount.staff.create-test');

// Debug current users in database
Route::get('/debug-users', function() {
    $users = \App\Models\User::all();
    return response()->json([
        'count' => $users->count(),
        'users' => $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ];
        })
    ]);
});

// Test form submission directly
Route::get('/test-form-submission', function() {
    return '
    <form action="' . route('adminAccount.staff.create') . '" method="POST" enctype="multipart/form-data">
        ' . csrf_field() . '
        <h3>Test Form</h3>
        <div>
            <label>Name:</label>
            <input type="text" name="name" value="Test User" required>
        </div>
        <div>
            <label>Email:</label>
            <input type="email" name="email" value="test' . time() . '@example.com" required>
        </div>
        <div>
            <label>Contact:</label>
            <input type="text" name="cp_number" value="1234567890" required>
        </div>
        <div>
            <label>Role:</label>
            <select name="role" required>
                <option value="staff">Staff</option>
                <option value="diffun_staff">Diffun Staff</option>
                <option value="cordon_staff">Cordon Staff</option>
            </select>
        </div>
        <div>
            <label>Password:</label>
            <input type="password" name="password" value="Test@123" required>
            <small>Use: Test@123 (uppercase T, lowercase est, number 123, special @)</small>
        </div>
        <button type="submit">Submit Test</button>
    </form>
    ';
});

// Walk-in Logbook Public Routes (without auth for kiosk mode)
Route::prefix('walkin-logbook')->group(function () {
    Route::get('/login', [LogBookLoginController::class, 'showLoginForm'])->name('logbook.login.form');
    Route::post('/login', [LogBookLoginController::class, 'login'])->name('logbook.login');
    Route::post('/logbook-logout', [LogBookLoginController::class, 'logout'])->name('logbook.logout');
    Route::get('/diffun', [LogBookLoginController::class, 'showDiffunLogbook'])->name('logbook.diffun');
    // Use CordonPurposeVisitController to render the cordon logbook with purposes
    Route::get('/cordon', [\App\Http\Controllers\CordonPurposeVisitController::class, 'index'])->name('logbook.cordon');
    // Endpoint to return JSON list of cordon purposes
    Route::get('/cordon/purposes', [\App\Http\Controllers\CordonPurposeVisitController::class, 'listJson'])->name('logbook.cordon.purposes');
    
    // Add store routes for diffun and cordon logbooks (without auth middleware - for kiosk)
    Route::post('/diffun/store', [WalkinLogbookController::class, 'store'])->name('logbook.diffun.store');
    Route::post('/cordon/store', [\App\Http\Controllers\CordonLogbookController::class, 'store'])->name('logbook.cordon.store');
    
    // Optional: Create sample users (remove in production)
    Route::get('/create-sample-users', [LogBookLoginController::class, 'createSampleUser'])->name('logbook.create.sample');
});

//walkins debug/ testing
Route::get('/test-purposes', function() {
    $purposes = DB::table('diffun_choice_purpose')
        ->orderBy('purpose', 'asc')
        ->get();
    
    return response()->json([
        'count' => $purposes->count(),
        'purposes' => $purposes
    ]);
});
Route::get('/staff/walkins/logbook/purposes', function() {
    $purposes = DB::table('diffun_choice_purpose')
        ->orderBy('purpose', 'asc')
        ->get();
    return response()->json($purposes);
});

Route::get('/staff/walkins/logbook/law-offices', function() {
    $offices = DB::table('law_offices')
        ->select('id', 'law_office')
        ->orderBy('law_office', 'asc')
        ->get();
    return response()->json($offices);
});

//WALKINS EXCEL PACKAGE INSTALL ROUTES
Route::get('/check-excel-package', function() {
    if (class_exists('Maatwebsite\Excel\Excel')) {
        return "Excel package is installed!";
    } else {
        return "Excel package is NOT installed. Run: composer require maatwebsite/excel";
    }
});

//walk ins delete 
Route::delete('/staff/walkins/delete/{id}', [WalkInLogsController::class, 'deleteWalkin'])
    ->name('staff.walkins.delete')
    ->middleware('auth');

// STAFF FEEDBACK REPORTS ROUTES
Route::get('/staff/feedback-reports', [FeedbackReportsController::class, 'index'])->name('staff.feedback.reports');
Route::get('/staff/feedback-reports/generate-pdf', [FeedbackReportsController::class, 'generatePdf'])->name('staff.feedback.reports.generate-pdf');
Route::get('/staff/feedback-reports/export-csv', [FeedbackReportsController::class, 'exportCsv'])->name('staff.feedback.reports.export-csv');
Route::get('/staff/feedback-reports/chart-data', [FeedbackReportsController::class, 'getChartData'])->name('staff.feedback.reports.chart-data');

Route::get('/debug-images/{id}', function($id) {
    $appointment = \App\Models\Appointment::find($id);
    
    if (!$appointment) {
        return 'Appointment not found';
    }
    
    return response()->json([
        'id' => $appointment->id,
        'fullname' => $appointment->fullname,
        'id_front' => $appointment->id_front,
        'id_back' => $appointment->id_back,
        'id_front_exists_in_db' => !empty($appointment->id_front),
        'id_back_exists_in_db' => !empty($appointment->id_back),
        'storage_path' => storage_path('app/public/ids'),
        'public_path' => public_path('storage/ids')
    ]);
});



// Accepted and Denied requests routes - Protected by auth middleware
Route::middleware(['auth'])->group(function () {
    // Accepted requests routes
    Route::get('/staff/accepted-requests', [DiffunStaffAcceptedController::class, 'index'])->name('staff.acceptedRequests');
    Route::get('/staff/accepted-requests/{id}/details', [DiffunStaffAcceptedController::class, 'getAppointmentDetails'])->name('staff.acceptedRequests.details');
    Route::delete('/staff/accepted-requests/{id}', [DiffunStaffAcceptedController::class, 'destroy'])->name('staff.acceptedRequests.destroy');
    Route::get('/staff/accepted-requests/report/pdf', [DiffunStaffAcceptedController::class, 'generateReportPdf'])->name('staff.acceptedRequests.report.pdf');

    // Denied requests routes
    Route::get('/staff/denied-requests', [DiffunStaffDeniedController::class, 'index'])->name('staff.deniedRequests');
    Route::get('/staff/denied-requests/{id}/details', [DiffunStaffDeniedController::class, 'getAppointmentDetails'])->name('staff.deniedRequests.details');
    Route::delete('/staff/denied-requests/{id}', [DiffunStaffDeniedController::class, 'destroy'])->name('staff.deniedRequests.destroy');
});

// Test route for debugging inquiries data
Route::get('/test/inquiries-data', [\App\Http\Controllers\TestInquiriesController::class, 'test']);

// Message Inquiries Routes - Diffun Staff
Route::middleware(['auth'])->group(function () {
    Route::get('/staff/message-inquiries', [MessageInquiriesController::class, 'diffunIndex'])->name('diffun.message.inquiries');
    Route::get('/staff/message-inquiries/data', [MessageInquiriesController::class, 'getInquiries'])->name('diffun.message.inquiries.data');
    Route::post('/staff/message-inquiries/send-email', [MessageInquiriesController::class, 'sendEmailReply'])->name('diffun.message.inquiries.send.email');
    Route::post('/staff/message-inquiries/send-sms', [MessageInquiriesController::class, 'sendSmsReply'])->name('diffun.message.inquiries.send.sms');
    Route::delete('/staff/message-inquiries/{id}', [MessageInquiriesController::class, 'destroy'])->name('diffun.message.inquiries.destroy');
});

// Walk-in Logbook Public Routes (without auth for kiosk mode)
// Debug routes for staff account settings
Route::get('/debug/staff-auth', function() {
    return response()->json([
        'is_authenticated' => Auth::check(),
        'user' => Auth::check() ? [
            'id' => Auth::id(),
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
            'role' => Auth::user()->role,
        ] : null,
        'session_id' => session()->getId(),
        'session_data' => session()->all(),
    ]);
});

Route::get('/debug/staff-routes', function() {
    $routes = collect(\Route::getRoutes())->map(function ($route) {
        return [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $route->getActionName(),
        ];
    })->filter(function ($route) {
        return str_contains($route['uri'] ?? '', 'staff') && 
               str_contains($route['uri'] ?? '', 'account');
    });
    
    return response()->json($routes->values());
});

//admin account  image debug

Route::get('/fix-all-staff-images', function() {
    try {
        // Get all staff users
        $staffUsers = \App\Models\User::whereIn('role', ['staff', 'diffun_staff', 'cordon_staff'])->get();
        $updated = 0;
        
        echo "<h3>Fixing Staff Images</h3>";
        echo "<pre>";
        
        foreach ($staffUsers as $staff) {
            $currentImage = $staff->image;
            echo "User ID: {$staff->id}, Name: {$staff->name}\n";
            echo "Current image path: $currentImage\n";
            
            // If no image path, set to default
            if (!$currentImage) {
                $staff->image = 'staff_images/default-avatar.png';
                $staff->save();
                $updated++;
                echo "Set to default avatar (no image path)\n";
                echo "-----------------------------------\n";
                continue;
            }
            
            // Check if the file exists in the current path
            if (file_exists(public_path($currentImage))) {
                // If the current path is not in staff_images, then move it to staff_images
                if (!str_contains($currentImage, 'staff_images/')) {
                    $filename = basename($currentImage);
                    $newPath = 'staff_images/' . $filename;
                    
                    // Ensure the staff_images directory exists
                    if (!file_exists(public_path('staff_images'))) {
                        mkdir(public_path('staff_images'), 0755, true);
                    }
                    
                    // Copy the file to staff_images
                    copy(public_path($currentImage), public_path($newPath));
                    $staff->image = $newPath;
                    $staff->save();
                    $updated++;
                    echo "Moved to staff_images: $newPath\n";
                } else {
                    // Already in staff_images and file exists, nothing to do.
                    echo "Already in staff_images and file exists.\n";
                }
            } else {
                // File does not exist in the current path.
                // Try the alternative directory.
                $filename = basename($currentImage);
                if (str_contains($currentImage, 'staff_images/')) {
                    // Current path is staff_images, so look in uploads
                    $alternativePath = 'uploads/' . $filename;
                } else {
                    // Current path is uploads, so look in staff_images
                    $alternativePath = 'staff_images/' . $filename;
                }
                
                if (file_exists(public_path($alternativePath))) {
                    // If the alternative path is in staff_images, then update the database to that path.
                    if (str_contains($alternativePath, 'staff_images/')) {
                        $staff->image = $alternativePath;
                        $staff->save();
                        $updated++;
                        echo "Updated to existing file in staff_images: $alternativePath\n";
                    } else {
                        // The alternative path is in uploads, so copy it to staff_images and update the database.
                        $newPath = 'staff_images/' . $filename;
                        // Ensure the staff_images directory exists
                        if (!file_exists(public_path('staff_images'))) {
                            mkdir(public_path('staff_images'), 0755, true);
                        }
                        copy(public_path($alternativePath), public_path($newPath));
                        $staff->image = $newPath;
                        $staff->save();
                        $updated++;
                        echo "Copied from uploads to staff_images: $newPath\n";
                    }
                } else {
                    // File not found in either, set to default.
                    $staff->image = 'staff_images/default-avatar.png';
                    $staff->save();
                    $updated++;
                    echo "File not found, set to default avatar.\n";
                }
            }
            echo "-----------------------------------\n";
        }
        
        echo "</pre>";
        echo "<h4>Updated $updated staff records</h4>";
        
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});


Route::get('/setup-staff-images-directory', function() {
    try {
        $staffImagesPath = public_path('staff_images');
        
        // Create directory
        if (!file_exists($staffImagesPath)) {
            mkdir($staffImagesPath, 0755, true);
            echo "Created directory: staff_images<br>";
        } else {
            echo "Directory already exists: staff_images<br>";
        }
        
        // Create default avatar if it doesn't exist
        $defaultAvatarPath = public_path('staff_images/default-avatar.png');
        if (!file_exists($defaultAvatarPath)) {
            // Copy from uploads if it exists
            $uploadsDefault = public_path('uploads/default-avatar.png');
            if (file_exists($uploadsDefault)) {
                copy($uploadsDefault, $defaultAvatarPath);
                echo "Copied default avatar from uploads<br>";
            } else {
                // Create a simple placeholder
                $image = imagecreatetruecolor(150, 150);
                $bgColor = imagecolorallocate($image, 200, 200, 200);
                $textColor = imagecolorallocate($image, 100, 100, 100);
                
                imagefill($image, 0, 0, $bgColor);
                imagestring($image, 5, 40, 65, 'No Image', $textColor);
                imagepng($image, $defaultAvatarPath);
                imagedestroy($image);
                
                echo "Created default avatar placeholder<br>";
            }
        } else {
            echo "Default avatar already exists<br>";
        }
        
        echo "<h3>Setup Complete!</h3>";
        echo "Directory: " . realpath($staffImagesPath) . "<br>";
        echo "Default Avatar: " . ($defaultAvatarPath) . "<br>";
        
        // List current files
        echo "<h4>Current files in staff_images:</h4>";
        if (file_exists($staffImagesPath)) {
            $files = scandir($staffImagesPath);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    echo "- $file<br>";
                }
            }
        }
        
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

//sms test route

Route::get('/test-sms-simple', function() {
    $apiKey = '8c2fba32-4358-4ad2-9c41-a7cfcfea91c5';
    $deviceId = '692b0d2bd3fdd9bd6ca58fcb';
    
    // Format phone number (use your own phone for testing)
    $phone = '09916156687'; // Replace with your number
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) === 10) {
        $phone = '63' . $phone;
    } elseif (strlen($phone) === 11 && substr($phone, 0, 2) === '09') {
        $phone = '63' . substr($phone, 1);
    }
    
    $data = [
        'recipients' => [$phone],
        'message' => 'Test SMS from LegalConnect'
    ];
    
    $url = "https://api.textbee.dev/api/v1/gateway/{$deviceId}/send-sms";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return response()->json([
        'success' => $httpCode === 200,
        'http_code' => $httpCode,
        'response' => json_decode($response, true),
        'error' => $error,
        'phone' => $phone
    ]);
});

Route::get('/test-textbee-device', function() {
    $apiKey = '8c2fba32-4358-4ad2-9c41-a7cfcfea91c5';
    $deviceId = '692b0d2bd3fdd9bd6ca58fcb';
    
    $url = "https://api.textbee.dev/api/v1/gateway/devices/{$deviceId}";
    
    Log::info('Testing device endpoint: ' . $url);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $apiKey,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return response()->json([
        'device_id' => $deviceId,
        'http_code' => $httpCode,
        'response' => json_decode($response, true) ?? $response,
        'error' => $error,
        'url' => $url,
        'api_key_truncated' => substr($apiKey, 0, 8) . '...'
    ]);
});

Route::get('/test-textbee-exact', function() {
    $apiKey = '8c2fba32-4358-4ad2-9c41-a7cfcfea91c5';
    $deviceId = '692b0d2bd3fdd9bd6ca58fcb';
    
    // Use a real Philippine number format (replace with yours)
    $testPhone = '639082004651'; // Replace with your number
    
    $url = "https://api.textbee.dev/api/v1/gateway/devices/{$deviceId}/send-sms";
    
    $data = [
        'recipients' => [$testPhone],
        'message' => 'Hello from LegalConnect! Testing TextBee API.'
    ];
    
    Log::info('Testing TextBee with exact documentation format');
    Log::info('URL: ' . $url);
    Log::info('Data: ' . json_encode($data));
    $httpResponse = Http::timeout(10)
        ->withHeaders([
            'x-api-key' => $apiKey,
            'Accept' => 'application/json',
        ])
        ->asJson()
        ->post($url, $data);

    $httpCode = $httpResponse->status();
    $response = $httpResponse->body();
    $json = null;
    try {
        $json = $httpResponse->json();
    } catch (\Throwable $e) {
        $json = null;
    }
    $error = $httpResponse->successful() ? null : $httpResponse->reason();

    return response()->json([
        'success' => $httpCode === 200,
        'http_code' => $httpCode,
        'response' => $json ?? $response,
        'raw_response' => $response,
        'error' => $error,
        'request' => [
            'url' => $url,
            'data' => $data,
            'phone' => $testPhone,
            'headers' => [
                'x-api-key' => substr($apiKey, 0, 8) . '...'
            ]
        ]
    ]);
});

Route::get('/test-textbee-auth', function() {
    $apiKey = '8c2fba32-4358-4ad2-9c41-a7cfcfea91c5';
    
    // Try to access a simple endpoint to test authentication
    $url = "https://api.textbee.dev/api/v1/gateway/devices";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $apiKey,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return response()->json([
        'api_key_valid' => $httpCode !== 401 && $httpCode !== 403,
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ]);
});

Route::get('/test-textbee-real', function() {
    $apiKey = '8c2fba32-4358-4ad2-9c41-a7cfcfea91c5';
    $deviceId = '692b0d2bd3fdd9bd6ca58fcb';
    
    // Use your actual phone number from .env
    $testPhone = env('SMS_TEST_PHONE', '639916156687');
    
    $url = "https://api.textbee.dev/api/v1/gateway/devices/{$deviceId}/send-sms";
    
    $data = [
        'recipients' => [$testPhone],
        'message' => 'Hello from LegalConnect! This is a real test SMS.'
    ];
    
    Log::info('Testing TextBee with real phone: ' . $testPhone);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return response()->json([
        'success' => $httpCode === 201,
        'http_code' => $httpCode,
        'response' => json_decode($response, true) ?? $response,
        'error' => $error,
        'phone_used' => $testPhone,
        'note' => 'Check your phone for the SMS message'
    ]);
});

Route::get('/check-sms-status/{batchId}', function($batchId) {
    $apiKey = '8c2fba32-4358-4ad2-9c41-a7cfcfea91c5';
    
    $url = "https://api.textbee.dev/api/v1/sms/batches/{$batchId}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $apiKey,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return response()->json([
        'batch_id' => $batchId,
        'http_code' => $httpCode,
        'response' => json_decode($response, true) ?? $response,
        'error' => $error
    ]);
});

// Staff Account Settings Routes
Route::middleware(['auth', 'validate.tab.session', 'authorize.role:staff,secretary,clerk'])->group(function () {

    Route::get('/staff/account-settings', [StaffAccountSettingController::class, 'index'])
        ->name('staff.account.settings');
    
    Route::put('/staff/account-settings/update', [StaffAccountSettingController::class, 'updateProfile'])
        ->name('staff.account.settings.update');
        
    Route::put('/staff/account-settings/update-password', [StaffAccountSettingController::class, 'updatePassword'])
        ->name('staff.account.settings.update.password');

    Route::get('/staff/account-settings/forgot-password', [StaffForgotPasswordController::class, 'showEmailForm'])
        ->name('staff.account.settings.forgot-password.email');

    Route::post('/staff/account-settings/forgot-password/send-code', [StaffForgotPasswordController::class, 'sendCode'])
        ->name('staff.account.settings.forgot-password.send');

    Route::get('/staff/account-settings/forgot-password/verify', [StaffForgotPasswordController::class, 'showVerifyForm'])
        ->name('staff.account.settings.forgot-password.verify');

    Route::post('/staff/account-settings/forgot-password/verify', [StaffForgotPasswordController::class, 'verifyCode'])
        ->name('staff.account.settings.forgot-password.verify.submit');

    Route::get('/staff/account-settings/forgot-password/reset', [StaffForgotPasswordController::class, 'showResetForm'])
        ->name('staff.account.settings.forgot-password.reset');

    Route::post('/staff/account-settings/forgot-password/reset', [StaffForgotPasswordController::class, 'resetPassword'])
        ->name('staff.account.settings.forgot-password.reset.submit');
        
    Route::get('/staff/walkins/logs/logbook-passwords', [WalkInLogsController::class, 'getLogbookPasswords'])
        ->name('staff.walkins.logs.logbook-passwords');
    
    Route::put('/staff/walkins/logs/logbook-password/update', [WalkInLogsController::class, 'updateLogbookPassword'])
        ->name('staff.walkins.logs.logbook-password.update');

    Route::get('/staff/walkins/logs/logbook-password', [WalkInLogsController::class, 'getLogbookPassword'])
        ->name('staff.walkins.logs.logbook-password');

});
// Add this to your web.php temporarily
Route::get('/debug-cordon-tables', function() {
    try {
        return response()->json([
            'cordon_walkins_table_exists' => \Schema::hasTable('cordon_walkins'),
            'cordon_walkins_columns' => \Schema::hasTable('cordon_walkins') ? \Schema::getColumnListing('cordon_walkins') : [],
            'cordon_walkins_count' => \DB::table('cordon_walkins')->count(),
            'first_5_records' => \DB::table('cordon_walkins')->limit(5)->get(),
            'database_connection' => \DB::connection()->getDatabaseName(),
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/test-cordon-insert', function() {
    try {
        DB::table('cordon_walkins')->insert([
            'fullname' => 'Test User ' . time(),
            'address' => 'Test Address, Cordon',
            'contact_number' => '09123456789',
            'purpose' => 'Test Purpose',
            'branch' => 'Cordon',
            'date_time' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Test data inserted into cordon_walkins',
            'count' => DB::table('cordon_walkins')->count()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

// Cordon pending requests routes (controller: CordonPendingRequestController)
Route::middleware(['auth'])->prefix('cordon')->group(function () {
    Route::get('/clients/pending', [\App\Http\Controllers\CordonPendingRequestController::class, 'index'])->name('cordon.staff.clients.pending');
    Route::get('/clients/pending/data', [\App\Http\Controllers\CordonPendingRequestController::class, 'getPendingAppointments'])->name('cordon.staff.clients.pending.data');
    Route::get('/clients/pending/{id}', [\App\Http\Controllers\CordonPendingRequestController::class, 'getAppointmentDetails'])->name('cordon.staff.clients.pending.details');
    Route::post('/clients/pending/{id}/approve', [\App\Http\Controllers\CordonPendingRequestController::class, 'approve'])->name('cordon.staff.clients.pending.approve');
    Route::post('/clients/pending/{id}/deny', [\App\Http\Controllers\CordonPendingRequestController::class, 'deny'])->name('cordon.staff.clients.pending.deny');
    Route::get('/clients/pending/statistics', [\App\Http\Controllers\CordonPendingRequestController::class, 'getStatistics'])->name('cordon.staff.clients.pending.statistics');
});

// Cordon notification routes
Route::middleware(['auth'])->group(function () {
    Route::get('/cordon/notifications', [\App\Http\Controllers\Notifications\CordonNotificationController::class, 'getNotifications'])
        ->name('cordon.notifications');
    
    // Also add to staff notifications for compatibility
    Route::get('/staff/notifications', function(Request $request) {
        $branch = $request->input('branch', 'diffun');
        
        if ($branch === 'cordon') {
            return app(\App\Http\Controllers\Notifications\CordonNotificationController::class)->getNotifications($request);
        }
        
        // Default to Diffun notifications
        return app(\App\Http\Controllers\Staff\StaffNotificationController::class)->getNotifications($request);
    })->name('staff.notifications');
});

// IMAP debugging endpoint deprecated. IMAP-based fetching is disabled. Use Mailjet webhooks for inbound mail.
Route::get('/debug-imap-connection', function() {
    return response()->json(['success' => false, 'error' => 'IMAP is deprecated. Use Mailjet webhooks instead.'], 200);
})->middleware('auth')->name('debug.imap');

// Temporary: trigger IMAP sync and return result
Route::get('/debug/sync-inbox', function() {
    try {
        $service = new \App\Services\EmailChatService();
        $result = $service->fetchNewEmails();
        return response()->json($result);
    } catch (\Exception $e) {
        \Log::error('Debug sync inbox error: ' . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
});

// Temporary: get conversation related to an email (no auth) for debugging
Route::get('/debug/get-conversation/{email}', function($email) {
    try {
        $email = filter_var(urldecode($email), FILTER_SANITIZE_EMAIL);
        $messages = DB::table('chattbl')
            ->where('sender_email', $email)
            ->orWhere('receiver_email', $email)
            ->orderBy('timestamp_normalized', 'asc')
            ->get();

        // Normalize shape to match EmailReceiverController::getEmailConversation response
        $conversation = $messages->map(function ($message) {
            $timestamp = $message->timestamp_normalized ?? $message->created_at;

            if ($timestamp) {
                $createdAt = \Carbon\Carbon::parse($timestamp, 'Asia/Manila');
                $formattedTime = $createdAt->format('M j, Y g:i A');
                $sortTimestamp = $createdAt->timestamp * 1000;
            } else {
                $formattedTime = 'Unknown time';
                $sortTimestamp = 0;
            }

            return [
                'id' => $message->id,
                'sender_email' => $message->sender_email,
                'sender_name' => $message->sender_name,
                'receiver_email' => $message->receiver_email,
                'subject' => $message->subject,
                'message' => $message->message,
                'sender_role' => $message->sender_role,
                'message_type' => $message->message_type,
                'created_at' => $message->created_at,
                'timestamp_normalized' => $message->timestamp_normalized,
                'created_at_formatted' => $formattedTime,
                'sort_timestamp' => $sortTimestamp,
                'updated_at' => $message->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'email' => $email,
            'count' => $conversation->count(),
            'conversation' => $conversation
        ]);
    } catch (\Exception $e) {
        \Log::error('Debug get conversation error: ' . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
});

Route::get('/debug-email-messages/{email}', function($email) {
    $currentUser = Auth::user();
    $currentUserEmail = $currentUser->email;
    
    $messages = DB::table('chattbl')
        ->where(function($query) use ($currentUserEmail, $email) {
            $query->where('sender_email', $currentUserEmail)
                  ->where('receiver_email', $email);
        })
        ->orWhere(function($query) use ($currentUserEmail, $email) {
            $query->where('sender_email', $email)
                  ->where('receiver_email', $currentUserEmail);
        })
        ->orderBy('timestamp_normalized', 'asc')
        ->get();
    
    return response()->json([
        'current_user' => $currentUserEmail,
        'target_email' => $email,
        'total_messages' => $messages->count(),
        'messages' => $messages->map(function($msg) {
            return [
                'id' => $msg->id,
                'sender_email' => $msg->sender_email,
                'sender_name' => $msg->sender_name,
                'receiver_email' => $msg->receiver_email,
                'sender_id' => $msg->sender_id,
                'message_type' => $msg->message_type,
                'sender_role' => $msg->sender_role,
                'subject' => $msg->subject,
                'timestamp_normalized' => $msg->timestamp_normalized,
                'created_at' => $msg->created_at,
            ];
        })
    ]);
})->middleware('auth');

/*
=====================================
        TEST ROUTES
=====================================
*/

// Test Routes - No authentication required
Route::get('/test/connection', [TestController::class, 'testBasicConnection'])->name('test.connection');

// Test Routes - Authentication required
Route::middleware('auth')->group(function () {
    Route::get('/test/auth', [TestController::class, 'testAuthentication'])->name('test.auth');
    Route::get('/test/broadcasting', [TestController::class, 'testBroadcasting'])->name('test.broadcasting');
    Route::get('/test/database', [TestController::class, 'testDatabase'])->name('test.database');
    Route::get('/test/call-controller', [TestController::class, 'testCallController'])->name('test.call-controller');
    Route::get('/test/call-page/{receiverId?}', [TestController::class, 'testCallPage'])->name('test.call-page');
    Route::get('/test/broadcasting-auth', [TestController::class, 'testBroadcastingAuth'])->name('test.broadcasting-auth');
    Route::get('/test/webrtc', [TestController::class, 'testWebRtc'])->name('test.webrtc');
    Route::get('/test/system', [TestController::class, 'testFullSystem'])->name('test.system');
    Route::get('/test', [TestController::class, 'testDashboard'])->name('test.dashboard');
});
