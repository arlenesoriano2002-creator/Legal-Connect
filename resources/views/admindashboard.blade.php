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
    
    <link rel="stylesheet" href="{{ asset('css/admindashboard.blade.css') }}">

    <style>
        /* Ensure notification badge numbers are centered and fully visible */
        .badge {
            position: absolute;
            top: 0;
            right: 0;
            background: #ff4757;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            font-weight: bold;
            border: 2px solid #f9f9f9;
        }
    </style>

    <script>
        @php
            $dashboardUser = Auth::user();
            $isAdminNotificationUser = $dashboardUser && in_array($dashboardUser->role, ['admin', 'superadmin']);
        @endphp
        // Global configuration object for JavaScript
        window.LegalConnect = {
            csrfToken: '{{ csrf_token() }}',
            broadcastDriver: '{{ config("broadcasting.default") }}',
            pusherKey: '{{ config("broadcasting.connections.pusher.key") }}',
            pusherCluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}',
            authId: '{{ Auth::id() }}',
            authRole: '{{ $dashboardUser?->role }}',
            adminNotificationsEnabled: {{ $isAdminNotificationUser ? 'true' : 'false' }},
            keepArchiveOpen: {{ session('keepArchiveOpen') ? 'true' : 'false' }},
            routes: {
                clientstbl: '{{ route("clientstbl") }}',
                adminAcceptedRequest: '{{ route("adminAcceptedRequest") }}',
                adminDeniedRequest: '{{ route("adminDeniedRequest") }}',
                messagesEmail: '{{ route("messages.email") }}',
                adminSystemChat: '{{ route("admin.system-chat") }}',
                messagesSms: '{{ route("messages.sms") }}',
                adminCreateBackup: '{{ route("admin.createBackup") }}',
                adminNotificationsUnread: '{{ route("admin.notifications.unread") }}',
                adminNotificationsCount: '{{ route("admin.notifications.count") }}',
                adminNotificationsMarkAllRead: '{{ route("admin.notifications.mark-all-read") }}',
                adminNotificationsBase: '{{ url("/admin/notifications") }}',
            }
        };
    </script>
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading" style="display: flex; flex-direction: column; align-items: center; justify-content: space-between; padding: 10px;">
               <!--<div class="close-btn" style=" margin-left: 10px; margin-bottom: 19px; ">
                    <button class="sidebar-close-btn" id="sidebar-close-btn" title="Close sidebar">
                        &times;
                    </button>
                </div>-->
                <div class="head-content" style="display: flex; flex-direction: row; align-items: center;">
                <img src="{{ asset('logo6.png') }}" alt="LegalConnect logo" width="40" height="40" style="border-radius: 50%;">
                <span>LegalConnect</span>
                </div>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ url('/admindashboard') }}" class="list-group-item list-group-item-action {{ request()->is('admindashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="{{ url('/administrator') }}" class="list-group-item list-group-item-action {{ request()->is('administrator') ? 'active' : '' }}">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Set Time</span>
                </a>
                <a href="{{ url('/appointments') }}" class="list-group-item list-group-item-action {{ request()->is('appointments') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Logs Requests</span>
                </a>
                <a href="{{ route('admin.walkins') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.walkins') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard" style="color: #cdd3df;"></i>
                    <span>Walk-Ins logs</span>
                </a>
                <a href="{{ url('/statistics') }}" class="list-group-item list-group-item-action {{ request()->is('statistics') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>Statistics</span>
                </a>
                <a href="#messagesSubmenu" 
                class="list-group-item list-group-item-action {{ request()->is('email-chat') || request()->is('messages/*') ? 'active' : '' }}"
                data-bs-toggle="collapse" 
                aria-expanded="{{ request()->is('email-chat') || request()->is('messages/*') ? 'true' : 'false' }}">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu collapse {{ request()->is('email-chat') || request()->is('messages/*') ? 'show' : '' }} list-group" id="messagesSubmenu">
                    <a href="{{ route('messages.email') }}" class="list-group-item list-group-item-action {{ request()->is('email-chat') ? 'active' : '' }}">
                        <i class="fas fa-envelope"></i>
                        <span>Email</span>
                    </a>
                    <a href="{{ route('messages.sms') }}" class="list-group-item list-group-item-action {{ request()->is('sms-chat') ? 'active' : '' }}">
                        <i class="fas fa-sms"></i>
                        <span>SMS</span>
                    </a>
                    <a href="{{ route('admin.system-chat') }}" class="list-group-item list-group-item-action {{ request()->is('admin/system-chat') ? 'active' : '' }}">
                        <i class="fas fa-comments"></i>
                        <span>System Chatting</span>
                    </a>
                </div>
                <a href="{{ url('/practice-areas') }}" class="list-group-item list-group-item-action {{ request()->is('practice-areas') ? 'active' : '' }}">
                    <i class="fa-solid fa-suitcase"></i>
                    
                    <span>Services</span>
                </a>
                <a href="#requestsSubmenu" class="list-group-item list-group-item-action" data-bs-toggle="collapse" aria-expanded="false">
                    <i class="fas fa-list-alt"></i>
                    <span>Appointment Requests</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu collapse list-group {{ request()->is('clientstbl') || request()->is('adminAcceptedRequest') || request()->is('adminDeniedRequest') ? 'show' : '' }}" id="requestsSubmenu">
                    <a href="{{ url('/clientstbl') }}" class="list-group-item list-group-item-action {{ request()->is('clientstbl') ? 'active' : '' }}">
                        <i class="fas fa-clock"></i>
                        <span>Pending Requests</span>
                    </a>
                    <a href="{{ url('/adminAcceptedRequest') }}" class="list-group-item list-group-item-action {{ request()->is('adminAcceptedRequest') ? 'active' : '' }}">
                        <i class="fas fa-check-circle"></i>
                        <span>Accepted Requests</span>
                    </a>
                    <a href="{{ url('/adminDeniedRequest') }}" class="list-group-item list-group-item-action {{ request()->is('adminDeniedRequest') ? 'active' : '' }}">
                        <i class="fas fa-times-circle"></i>
                        <span>Denied Requests</span>
                    </a>
                </div>

                <a href="{{ url('/adminAccount') }}" class="list-group-item list-group-item-action {{ request()->is('adminAccount') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-group"></i>
                    <span>All Staff Accounts</span>
                </a>
                <a href="{{ route('admin.account.settings') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('admin.account.settings') ? 'active' : '' }}">
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
                @if($isAdminNotificationUser)
                    <div class="notification-container">
                        <button class="notification-btn" id="notificationBtn">
                            <i class="fas fa-bell"></i>
                        </button>
                        <span class="badge" id="notificationBadge">0</span>
                        
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-header">
                                <h4>Notifications</h4>
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
                @endif
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
            
            <!-- Rest of your dashboard content remains the same -->
            <div class="dashboard-container">
        <!-- Updated Header Section -->
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-800">Appointment Analytics Dashboard</h1>
                        <p class="text-gray-600 mt-2">Provides a clear overview of all appointment activities</p>
                    </div>

                    <!-- Rest of your dashboard content -->
                    <div class="stats-container">
                        <!-- Total Appointments Card -->
                        <div class="stat-card card-total">
                            <div class="stat-header">
                                <div class="stat-title">Total Appointments</div>
                                <div class="stat-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                            </div>
                            <div class="stat-value">{{ $totalAppointments }}</div>
                            <div class="stat-footer">
                                <span class="stat-trend">
                                    <i class="fas fa-chart-line"></i> All Time
                                </span>
                            </div>
                        </div>
                    
                    <!-- Pending Appointments Card - Clickable -->
                    <div class="stat-card card-pending clickable-card" onclick="window.location.href='{{ route('clientstbl') }}'">
                        <div class="stat-header">
                            <div class="stat-title">Pending Requests</div>
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="stat-value">{{ $pendingAppointments }}</div>
                        <div class="stat-footer">
                            <span class="stat-trend">
                                <i class="fas fa-user-clock"></i> Awaiting Review
                            </span>
                        </div>
                    </div>
                    
                    <!-- Approved Appointments Card - Clickable -->
                    <div class="stat-card card-approved clickable-card" onclick="window.location.href='{{ route('adminAcceptedRequest') }}'">
                        <div class="stat-header">
                            <div class="stat-title">Approved Requests</div>
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-value">{{ $approvedAppointments }}</div>
                        <div class="stat-footer">
                            <span class="stat-trend">
                                <i class="fas fa-calendar-plus"></i> Confirmed
                            </span>
                        </div>
                    </div>
                    
                    <!-- Denied Appointments Card - Clickable -->
                    <div class="stat-card card-denied clickable-card" onclick="window.location.href='{{ route('adminDeniedRequest') }}'">
                        <div class="stat-header">
                            <div class="stat-title">Denied Requests</div>
                            <div class="stat-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                        <div class="stat-value">{{ $deniedAppointments }}</div>
                        <div class="stat-footer">
                            <span class="stat-trend">
                                <i class="fas fa-ban"></i> Not Approved
                            </span>
                        </div>
                    </div>
                    
                    <!-- logs & backups-->
                    <div class="archive-card" style="width: 18rem; height: 10.5rem;">
                        <div class="card-body">
                            <h5 class="card-title">Logs Records & Backups</h5>
                            <p class="card-text">View Logs appointments and download it.</p>
                            <div class="card-footer" style="text-align:center;">
                                <button onclick="window.location.href='{{ url('/appointments') }}'" class="modal-btn primary" type="button">
                                    Records
                                </button>
                                <button id="btnViewBackups" class="modal-btn secondary" type="button">Backups</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity Section -->
                <div class="activity-section">
                    <!-- Left: Recent Activity -->
                    <div class="recent-activity">
                        <h3 class="activity-title">Recent Appointment Requests</h3>
                        <ul class="activity-list">
                            @forelse($recentAppointments as $appointment)
                                @php
                                    $statusClass = match($appointment->appointment_approval) {
                                        'pending' => 'status-pending',
                                        'approved' => 'status-approved',
                                        'denied' => 'status-denied',
                                        default => ''
                                    };
                                @endphp
                                <li class="activity-item">
                                    <div class="activity-icon"><i class="fas fa-user"></i></div>
                                    <div class="activity-details">
                                        <div class="activity-name">{{ $appointment->fullname ?? 'Unknown Client' }}</div>
                                        <div class="activity-time">
                                            {{ \Carbon\Carbon::parse($appointment->created_at)->format('M d, Y h:i A') }}
                                        </div>
                                    </div>
                                    <div class="activity-status {{ $statusClass }}">
                                        {{ ucfirst($appointment->appointment_approval) }}
                                    </div>
                                </li>
                            @empty
                                <li class="activity-item">
                                    <div class="activity-details">No recent appointments found.</div>
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    <!-- Right: Feedback Chart 
                    <div class="feedback-chart">
                        <h3 class="activity-title">Feedback Summary</h3>
                        <canvas id="feedbackBarChart"></canvas>
                    </div>-->
                </div>
            </div>
        </div>
    </div>

           

    <!-- ====================== BACKUP MODAL ====================== -->
<dialog id="backupModal" class="admin-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Database Backups</h2>
            <!-- Add filter dropdown here -->
            <div class="modal-actions">
                 <button class="modal-btn close-modal">Ã—</button>
                <div class="backup-filter-container">
                    <label for="backupFilter">Filter Backups:</label>
                    <select id="backupFilter" class="backup-filter-select">
                        <option value="all">All Backups</option>
                        <option value="pending">Pending</option>
                        <option value="denied">Denied</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-body">
            @include('partials.backup-manager', ['backups' => $backups])
        </div>
    </div>
</dialog>

    <!-- Chat Panel Dropdown -->
    @php
        $users = \App\Models\User::select('id', 'name', 'email')->whereNotNull('email')->get();
    @endphp

    <div class="chat-panel" id="chatPanel">
        <div class="chat-panel-container">
            <!-- Users List -->
            <div class="user-list-section">
                <input type="text" id="searchUser" placeholder="Search user..." onkeyup="filterUsers()">
                <ul id="userList">
                    @foreach($users as $user)
                        <li class="user-item" onclick="selectUser('{{ $user->id }}', '{{ $user->name }}', '{{ $user->email }}')">
                            <strong>{{ $user->name }}</strong><br>
                            <span style="font-size: 12px; color: gray;">{{ $user->email }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Chat Section -->
            <div class="message-section">
                <div class="message-display">
                    <p id="noMessageText" style="color: gray;">Select a user to start chat</p>
                </div>
                <form id="sendMessageForm" method="POST" action="{{ route('client.sendMessage') }}">
                    @csrf
                    <input type="hidden" name="receiver_id" id="receiverId">
                    <input type="text" name="subject" placeholder="Subject..." required>
                    <textarea name="message" placeholder="Type your message..." rows="3" required></textarea>
                    <button type="submit">Send Message</button>
                </form>
            </div>
        </div>
    </div>

    <div id="backupSuccessToast" class="toast-success">
        âœ… Backup Created Successfully!
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
  <!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>

<!-- Per-Tab Auth Manager (automatically sends X-Tab-Token header on all requests) -->
<script src="{{ asset('js/per-tab-auth-manager.js') }}"></script>

<!-- Session Timeout Manager - Auto-logout after inactivity -->
<script src="{{ asset('js/session-timeout-manager.js') }}"></script>

<!-- History Control Manager - Prevent back navigation to protected pages -->
<script src="{{ asset('js/history-control-manager.js') }}"></script>

<!-- Meta tag for authentication detection -->
<meta name="auth-user" content="{{ Auth::user()->id }}">

<!-- Your external JavaScript file -->
<script src="{{ asset('js/admindashboard.js') }}"></script>

@include('partials.notification-badge-visibility')
</body>
</html>
