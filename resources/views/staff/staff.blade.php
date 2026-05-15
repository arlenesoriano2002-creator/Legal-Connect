<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ Auth::id() }}">
    <meta name="user-role" content="{{ Auth::user()->role ?? '' }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Staff Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/staff/staff.blade.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff/staff-calendar.css') }}">
    
    <!-- jQuery FIRST (before toastr) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    {{-- Global Error Handler AFTER jQuery --}}
    @include('partials.global-error-handler')
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
                   <!-- Add this button temporarily for testing 
                <div style="position: fixed; bottom: 10px; right: 10px; z-index: 9999; display: flex; gap: 10px;">
                    <button onclick="testNotification()" class="btn btn-info btn-sm">
                        Test Appt Notif
                    </button>
                    {{-- <button onclick="testMessageNotification()" class="btn btn-warning btn-sm">
                        Test Message Notif
                    </button> --}}
                </div>-->
                <!-- Log Out -->
                <form id="logout-form" action="{{ route('custom.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <button type="button" class="btn logout-btn" onclick="showLogoutModal()">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>
            
            <!-- dashboard content -->
           <div class="calendar-container">
                <div class="calendar-views" style="flex: 1; min-width: 100%;">
                    <!-- Staff Branch Calendar -->
                    <div class="view-tabs-container">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="background: #0d6efd; color: white; padding: 6px 12px; border-radius: 4px; font-weight: 500; font-size: 13px;">
                                📍 {{ Auth::user()->law_office_id && Auth::user()->lawOffice ? Auth::user()->lawOffice->law_office : 'Diffun Branch' }}
                            </span>
                            <div class="view-tabs">
                                <div class="view-tab active" data-view="month">Calendar</div>
                            </div>
                        </div>
                        <div class="refresh-container">
                            <button id="refreshStaffCalendar" class="refresh-btn-tabs" title="Refresh Calendar">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    
                    <div class="view-content">
                        <!-- Month View -->
                        <div id="staffMonthView" class="view-pane">
                            <div class="month-calendar">
                                <div class="calendar-header">
                                    <button class="nav-btn" id="staffPrevMonth">&lt;</button>
                                    <h3 id="staffCurrentMonthYear">{{ date('F Y') }}</h3>
                                    <button class="nav-btn" id="staffNextMonth">&gt;</button>
                                </div>
                                
                                <div class="weekdays">
                                    <div>Sun</div>
                                    <div>Mon</div>
                                    <div>Tue</div>
                                    <div>Wed</div>
                                    <div>Thu</div>
                                    <div>Fri</div>
                                    <div>Sat</div>
                                </div>
                                
                                <div class="days-grid" id="staffMonthGrid"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="staffMessageContainer"></div>
                </div>
            </div>

            <!-- Color Selection Modal for Staff -->
            <div class="modal fade" id="staffColorSelectionModal" tabindex="-1" aria-labelledby="staffColorSelectionModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staffColorSelectionModalLabel">
                                <i class="fas fa-calendar-day"></i> 
                                Calendar Settings for <span id="staffModalDateDisplay"></span>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="modal-section">
                                <h6><i class="fas fa-palette"></i> Date Availability Selection</h6>
                                <p class="text-muted small mb-3">Set the overall availability for this entire date</p>
                                
                                <div class="d-flex gap-3 mb-3">
                                    <div class="modal-color-option flex-fill" data-color="red">
                                        <div class="time-slot-color-indicator color-red"></div>
                                        <span style="margin-left: 10px;">Not Available</span>
                                    </div>
                                    <div class="modal-color-option flex-fill" data-color="orange">
                                        <div class="time-slot-color-indicator color-orange"></div>
                                        <span style="margin-left: 10px;">Holiday</span>
                                    </div>
                                    <div class="modal-color-option flex-fill" data-color="green">
                                        <div class="time-slot-color-indicator color-green"></div>
                                        <span style="margin-left: 10px;">Available</span>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <label for="staffDateDescriptionInput" class="form-label">
                                        <i class="fas fa-sticky-note"></i> Date Description
                                    </label>
                                    <textarea class="form-control" id="staffDateDescriptionInput" rows="2" 
                                            placeholder="Add description for the entire date"></textarea>
                                </div>
                            </div>

                            <div class="modal-section">
                                <h6><i class="fas fa-clock"></i> Time Slot Management</h6>
                                <p class="text-muted small mb-3">
                                    Set time slots availability. Time slots range from 8:00 AM to 5:00 PM, with a maximum of 4 appointments per slot.
                                </p>
                                <div class="mb-3">
                                    <label for="staffSetAllAvailability" class="form-label fw-semibold mb-1">Set All Availability</label>
                                    <select class="form-select" id="staffSetAllAvailability">
                                        <option value="">Select availability</option>
                                        <option value="green">Available</option>
                                        <option value="red">Not Available</option>
                                    </select>
                                </div>
                                
                                <div class="time-slots-container">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Time Slot</th>
                                                    <th width="80">Slot Capacity (Max 4)</th>
                                                    <th>Description</th>
                                                    <th width="150">Availability</th>
                                                </tr>
                                            </thead>
                                            <tbody id="staffTimeSlotsTableBody">
                                                <!-- Time slots will be dynamically populated here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="button" class="btn btn-primary" id="saveStaffModalChanges">
                                <i class="fas fa-save"></i> Save Changes
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


    <!-- Set current user globally for WebRTC and other scripts -->
    <script>
        window.currentUser = {
            id: {{ Auth::id() }},
            role: '{{ Auth::user()->role ?? "staff" }}'
        };
        
        // Store in localStorage for cross-tab communication
        try {
            localStorage.setItem('currentUserId', window.currentUser.id);
        } catch (e) {
            console.warn('Could not store user ID in localStorage');
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Per-Tab Authentication Manager (sends tab_token header on all requests) -->
    <script src="{{ asset('js/per-tab-auth-manager.js') }}"></script>
    
    <!-- Custom JS -->
    <script src="{{ asset('js/staff/staff.js') }}"></script>
    <script src="{{ asset('js/staff/staff-calendar.js') }}"></script>
    <script src="{{ asset('js/staff/diffunNotifications.js') }}"></script>
    <script>
    // Test if CSS is loaded
    $(document).ready(function() {
        // Check if CSS is applied
        console.log('Calendar container found:', $('#staffMonthView').length);
        
        // Check if CSS file loaded
        var cssLoaded = false;
        $('link[rel="stylesheet"]').each(function() {
            if ($(this).attr('href') && $(this).attr('href').includes('staff-calendar.css')) {
                cssLoaded = true;
                console.log('Staff calendar CSS loaded:', $(this).attr('href'));
            }
        });
        
        if (!cssLoaded) {
            console.error('Staff calendar CSS not found!');
            // Try to load it dynamically
            $('head').append('<link rel="stylesheet" href="/css/staff/staff-calendar.css" type="text/css" />');
        }
    });
</script>

</body>
</html>
