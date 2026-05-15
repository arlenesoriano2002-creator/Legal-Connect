<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Admin Dashboard</title>
    
    <!-- Remove the Tailwind CDN and use only Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    {{-- Global Error Handler --}}
    @include('partials.global-error-handler')
    
    <link rel="stylesheet" href="{{ asset('css/staff/StaffClientstbl.blade.css') }}">

</head>
<body>
    <div id="wrapper">
       <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">
                <img src="{{ asset('logo6.png') }}" alt="LegalConnect logo" width="40" height="40" style="border-radius: 50%;">
                <span>LegalConnect</span>
            </div>
            <div class="list-group list-group-flush">
                <!-- Dashboard link - Already correct -->
                <a href="{{ route('dashboardStaff') }}" class="list-group-item list-group-item-action {{ request()->routeIs('dashboardStaff') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                
                <!-- Set Time link - Already correct -->
                <a href="{{ route('staff') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff') ? 'active' : '' }}">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Set Time</span>
                </a>
                
                <!-- Walk-ins logs - Need to create route in web.php -->
                <a href="{{ route('staff.walkins.logs') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.walkins.logs') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard" style="color: #cdd3df;"></i>
                    <span>Walk-ins logs</span>
                </a>
                
                <!-- Feedbacks - Need to create route in web.php -->
                <a href="{{ route('staff.feedback.reports') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.feedback.reports') ? 'active' : '' }}">
                    <i class="fa-solid fa-comments" style="color: #d7dae0;"></i>
                    <span>Feedbacks</span>
                </a>
                
                <!-- Pending Requests - Already has route -->
                <a href="{{ route('staff.clients.pending') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.clients.pending') ? 'active' : '' }}">
                    <i class="fas fa-clock"></i>
                    <span>Pending Requests</span>
                </a>
                
                <!-- Accepted Requests - Already has route -->
                <a href="{{ route('staff.acceptedRequests') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.acceptedRequests') ? 'active' : '' }}">
                    <i class="fas fa-check-circle"></i>
                    <span>Accepted Requests</span>
                </a>
                
                <!-- Denied Requests - Already has route -->
                <a href="{{ route('staff.deniedRequests') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.deniedRequests') ? 'active' : '' }}">
                    <i class="fas fa-times-circle"></i>
                    <span>Denied Requests</span>
                </a>
                <a href="{{ route('diffun.message.inquiries') }}" class="list-group-item list-group-item-action {{ request()->routeIs('diffun.message.inquiries') ? 'active' : '' }}">
                    <i class="fas fa-envelope"></i>
                    <span>Message Inquiries</span>
                </a>

                <!-- Account Setting - Need to create route in web.php -->
                <a href="{{ route('staff.account.settings') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.account.settings') ? 'active' : '' }}">
                    <i class="fas fa-user-cog"></i>
                    <span>Account Setting</span>
                </a>
            </div>
        </div>
        
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="top-bar" role="banner">
                <button class="btn btn-primary" id="menu-toggle">
                    <i class="fas fa-bars"></i> 
                </button>
                
                <div class="top-bar-spacer"></div>
                <!-- Notification container (ensures diffunNotifications bell appears) -->
                <div class="notification-container" id="diffun-notification-container" style="position:relative;margin-left:12px">
                    <button id="diffunNotificationBtn" class="notification-btn btn btn-light" style="position:relative">
                        <i class="fas fa-bell"></i>
                        <span id="diffunNotificationBadge" class="badge" style="display:none;position:absolute;top:-6px;right:-6px;background:#ff4757;color:#fff;padding:2px 6px;border-radius:12px;font-size:11px">0</span>
                    </button>
                    <div id="diffunNotificationDropdown" class="notification-dropdown" style="display:none;position:absolute;right:0;top:40px;z-index:9999;width:360px;background:#fff;border:1px solid #e6e6e6;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,0.08);overflow:hidden">
                        <div class="notification-header" style="padding:8px 12px;border-bottom:1px solid #f0f0f0;background:#fafafa;display:flex;justify-content:space-between;align-items:center">
                            <strong>Notifications</strong>
                            <div style="display:flex;align-items:center;gap:8px">
                                <button id="diffunMarkAllBtn" class="btn btn-sm btn-outline-secondary" style="font-size:11px;padding:3px 8px">Mark all as read</button>
                                <small id="diffunNotificationTime" style="color:#888;font-size:12px"></small>
                            </div>
                        </div>
                        <div id="diffunNotificationList" class="notification-list" style="max-height:320px;overflow:auto;padding:8px">No new notifications</div>
                        <div style="padding:8px;border-top:1px solid #f0f0f0;background:#fafafa;text-align:center;font-size:13px;color:#666">
                            <a href="/StaffClientstbl" style="text-decoration:none">View all</a>
                        </div>
                    </div>
                </div>
                <!-- Message Notification Dropdown - COMMENTED OUT 
                {{--
                <div class="notification-container">
                    <button class="notification-btn" id="messageNotificationBtn">
                        <img src="{{ asset('notification-bell.png') }}" alt="Messages" width="20" height="20">
                        <span class="badge" id="messageNotificationBadge">0</span>
                    </button>
                    
                    <div class="notification-dropdown" id="messageNotificationDropdown">
                        <div class="notification-header">
                            <h4>Message Notifications</h4>
                            <div class="notification-actions">
                                <button class="btn btn-sm btn-link" id="markAllMessageReadBtn">Mark all as read</button>
                                <button class="btn btn-sm btn-link" id="refreshMessageNotificationsBtn">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="notification-list" id="messageNotificationList">
                            <div class="notification-empty">
                                <i class="fas fa-bell-slash"></i>
                                <p>No new message notifications</p>
                            </div>
                        </div>
                        
                        <div class="notification-footer">
                            <div class="d-flex gap-2">
                                <a href="{{ route('messages.email') }}" class="btn btn-sm btn-primary flex-fill">
                                    <i class="fas fa-envelope me-1"></i> Email
                                </a>
                                <a href="{{ route('admin.system-chat') }}" class="btn btn-sm btn-info flex-fill">
                                    <i class="fas fa-comments me-1"></i> Chat
                                </a>
                                <a href="{{ route('messages.sms') }}" class="btn btn-sm btn-success flex-fill">
                                    <i class="fas fa-sms me-1"></i> SMS
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                --}}-->

                <!-- Notification Dropdown -->
                <div class="notification-container">
                     <!--<button class="notification-btn" id="notificationBtn">
                        <i class="fas fa-bell"></i>
                        <span class="badge" id="notificationBadge">0</span>
                    </button>-->
                    
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-header">
                            <h4>Appointment Request Notifications</h4>
                            <div class="notification-actions">
                                <button class="btn btn-sm btn-link" id="markAllReadBtn">Mark all as read</button>
                                <button class="btn btn-sm btn-link" onclick="refreshNotifications()">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="notification-list" id="notificationList">
                            <div class="notification-empty">
                                <i class="fas fa-bell-slash"></i>
                                <p>No new notifications</p>
                            </div>
                        </div>
                        
                        <div class="notification-footer">
                            <a href="{{ route('clientstbl') }}" class="btn btn-sm btn-primary w-100">
                                View All Pending Requests
                            </a>
                        </div>
                    </div>
                </div>
                   <!-- Add this button temporarily for testing -->
                <div style="position: fixed; bottom: 10px; right: 10px; z-index: 9999; display: flex; gap: 10px;">
                   <!-- <button onclick="testNotification()" class="btn btn-info btn-sm">
                        Test Appt Notif
                    </button>-->
                    {{-- <button onclick="testMessageNotification()" class="btn btn-warning btn-sm">
                        Test Message Notif
                    </button> --}}
                </div>
                <!-- Log Out -->
                <form id="logout-form" action="{{ route('custom.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <button type="button" class="btn logout-btn" onclick="showLogoutModal()">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>
            
            <!-- Rest of your dashboard content remains the same -->
            <div class="dashboard-container">
                <div class="container-fluid">
                    <!-- Page Header -->
                    <div class="page-header mb-4">
                        <h1 class="page-title">
                            <i class="fas fa-clock me-2"></i>Pending Appointments
                        </h1>
                        <div class="page-subtitle">
                            Manage pending appointment requests 
                        </div>
                    </div>

                    <!-- Statistics Cards 
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stat-card bg-primary">
                                <div class="stat-icon">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                                <div class="stat-content">
                                    <h3 id="totalPendingCount">0</h3>
                                    <p>Total Pending</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-info">
                                <div class="stat-icon">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <div class="stat-content">
                                    <h3 id="todayPendingCount">0</h3>
                                    <p>Today's Pending</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-warning">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-content">
                                    <h3 id="totalClientsCount">0</h3>
                                    <p>Total Clients</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-success">
                                <div class="stat-icon">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <div class="stat-content">
                                    <h3 id="categoriesCount">0</h3>
                                    <p>Categories</p>
                                </div>
                            </div>
                        </div>
                    </div>-->

                    <!-- Controls -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                    <button class="btn btn-outline-primary" onclick="refreshTable()">
                                        <i class="fas fa-sync-alt me-1"></i> Refresh
                                    </button>
                                   <!-- <button class="btn btn-outline-success" onclick="exportToExcel()">
                                        <i class="fas fa-file-excel me-1"></i> Export Excel
                                    </button>-->
                                   <!-- <button class="btn btn-outline-danger" onclick="exportToPDF()">
                                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                                    </button>-->
                                </div>
                                
                                <div class="d-flex align-items-center">
                                    <div class="input-group" style="width: 300px;">
                                        <input type="text" id="searchInput" class="form-control" placeholder="Search appointments...">
                                        <button class="btn btn-outline-secondary" type="button" onclick="searchTable()">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Appointments Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list me-2"></i>Pending Appointment Requests
                                <span class="badge bg-primary ms-2" id="tableCount">0</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0" id="pendingAppointmentsTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <!-- Removed ID column -->
                                            <th>Client Name & Email</th>
                                            <th>Contact Info</th>
                                            <th>Appointment Details</th>
                                            <th>Request Date</th>
                                            <th>Status</th>
                                            <th width="150">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="appointmentsTableBody">
                                        <!-- Data will be loaded via AJAX -->
                                        <tr id="loadingRow">
                                            <!-- Changed colspan from 7 to 6 -->
                                            <td colspan="6" class="text-center py-5">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p class="mt-2">Loading appointments...</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> appointments
                                </div>
                                <nav>
                                    <ul class="pagination mb-0" id="pagination">
                                        <!-- Pagination will be added via JavaScript -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View Details Modal -->
            <div class="modal fade" id="viewDetailsModal" tabindex="-1" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="viewDetailsModalLabel">
                                <i class="fas fa-eye me-2"></i>Appointment Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="appointmentDetailsContent">
                            <!-- Details will be loaded here -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deny Appointment Modal (Simplified - No Reason Input) -->
            <div class="modal fade" id="denyModal" tabindex="-1" aria-labelledby="denyModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="denyModalLabel">
                                <i class="fas fa-times-circle me-2"></i>Deny Appointment
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Are you sure you want to deny this appointment?
                            </div>
                            <input type="hidden" id="denyAppointmentId" name="id">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmDenyBtn">
                                <i class="fas fa-times me-1"></i> Deny Appointment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
    </div>
<!-- Bootstrap Modal for Logout Confirmation -->
    <div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-labelledby="logoutModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="title-header">
                    <h5 class="modal-title" id="logoutModalLabel">
                        <i class="fas fa-sign-out-alt me-2"></i>Confirm Logout
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <center>
                <div class="content-modal">
                    <div style="font-size: 48px; color: #ffc107; margin-bottom: 15px;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                   
                    <h4 class="mb-3">Confirm Logout</h4>
                    <p>Are you sure you want to log out?<br>You will be redirected to the login page.</p>
                </div>
                 </center>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt me-1"></i> Log Out
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container for notifications -->
    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/staff/StaffClientstbl.js') }}"></script>
    <script src="{{ asset('js/staff/diffunNotifications.js') }}"></script>

</body>
</html>
