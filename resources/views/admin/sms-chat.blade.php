<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>SMS Chat | LegalConnect</title>
    @php
    function formatPhone($phone) {
        if (!$phone) return 'No phone number';
        
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) === 10) {
            return '(' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4);
        } elseif (strlen($phone) === 11 && substr($phone, 0, 1) === '0') {
            return '(' . substr($phone, 1, 3) . ') ' . substr($phone, 4, 3) . '-' . substr($phone, 7, 4);
        }
        
        return $phone;
    }
    @endphp
    <link rel="stylesheet" href="{{ asset('css/sms-chat.blade.css') }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Inherit all styles from email-chat */
        
        /* ============================= */
        /* ðŸ”¹ NOTIFICATION STYLES ðŸ”¹ */
        /* ============================= */
        .notification-container {
            position: relative;
            margin-right: 15px;
        }

        .notification-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #333;
            position: relative;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .notification-btn:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
        }

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

        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 400px;
            max-width: 90vw;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            border: 1px solid #e0e0e0;
            display: none;
            z-index: 9999;
            margin-top: 10px;
            overflow: hidden;
        }

        .notification-dropdown.show {
            display: block;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            background: #fafafa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-header h4 {
            margin: 0;
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }

        .notification-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .notification-actions .btn-link {
            padding: 2px 6px;
            font-size: 13px;
            color: #666;
            text-decoration: none;
            border: 1px solid  #666; 
            border-radius: 4px;
        }

        .notification-actions .btn-link:hover {
            color: #007bff;
            border-color: #007bff;
            text-decoration: none;
        }

        .notification-list {
            max-height: 400px;
            overflow-y: auto;
            padding: 10px 0;
        }

        .notification-empty {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .notification-empty i {
            font-size: 2.5rem;
            margin-bottom: 10px;
            opacity: 0.5;
        }

        .notification-empty p {
            margin: 0;
            font-size: 14px;
        }

        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f5f5f5;
            cursor: pointer;
            transition: background-color 0.2s ease;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }

        .notification-item.unread {
            background-color: #f0f7ff;
            border-left: 3px solid #007bff;
        }

        .notification-item.unread:hover {
            background-color: #e6f2ff;
        }

        .notification-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .notification-message {
            color: #666;
            font-size: 13px;
            line-height: 1.4;
            margin-bottom: 8px;
        }

        .notification-time {
            color: #999;
            font-size: 11px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .notification-time i {
            font-size: 10px;
        }

        .notification-actions-row {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        .notification-actions-row .btn {
            padding: 4px 10px;
            font-size: 12px;
            border-radius: 4px;
        }

        .notification-footer {
            padding: 15px 20px;
            border-top: 1px solid #f0f0f0;
            background: #fafafa;
        }

        /* Scrollbar styling for notification list */
        .notification-list::-webkit-scrollbar {
            width: 6px;
        }

        .notification-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .notification-list::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .notification-list::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .notification-dropdown {
                width: 320px;
                right: -50px;
            }
            
            .notification-item {
                padding: 12px 15px;
            }
        }

        @media (max-width: 480px) {
            .notification-dropdown {
                width: 280px;
                right: -80px;
            }
            
            .notification-container {
                margin-right: 8px;
            }
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar (same as email-chat) -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">
                <img src="{{ asset('logo6.png') }}" alt="LegalConnect logo" width="40" height="40" style="border-radius: 50%;">
                <span>LegalConnect</span>
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
                <a href="#messagesSubmenu" 
                    class="list-group-item list-group-item-action {{ request()->is('email-chat') || request()->is('admin/sms-chat') || request()->is('admin/system-chat') ? 'active' : '' }}"
                    data-bs-toggle="collapse" 
                    aria-expanded="{{ request()->is('email-chat') || request()->is('admin/sms-chat') || request()->is('admin/system-chat') ? 'true' : 'false' }}">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu collapse {{ request()->is('email-chat') || request()->is('admin/sms-chat') || request()->is('admin/system-chat') ? 'show' : '' }} list-group" id="messagesSubmenu">
                    <a href="{{ route('messages.email') }}" class="list-group-item list-group-item-action {{ request()->is('email-chat') ? 'active' : '' }}">
                        <i class="fas fa-envelope"></i>
                        <span>Email</span>
                    </a>
                    <a href="{{ route('messages.sms') }}" class="list-group-item list-group-item-action {{ request()->is('admin/sms-chat') ? 'active' : '' }}">
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
                <a href="#requestsSubmenu" class="list-group-item list-group-item-action {{ request()->is('clientstbl') || request()->is('adminAcceptedRequest') || request()->is('adminDeniedRequest') ? 'active' : '' }}" data-bs-toggle="collapse" aria-expanded="{{ request()->is('clientstbl') || request()->is('adminAcceptedRequest') || request()->is('adminDeniedRequest') ? 'true' : 'false' }}">
                    <i class="fas fa-list-alt"></i>
                    <span>Appointment Requests</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu collapse {{ request()->is('clientstbl') || request()->is('adminAcceptedRequest') || request()->is('adminDeniedRequest') ? 'show' : '' }} list-group" id="requestsSubmenu">
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
                <div class="burger-menu">
                    <button class="btn btn-primary" id="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                
                <div class="top-bar-spacer"></div>

                <!-- Notification Dropdown (NEW) -->
                <div class="notification-container">
                    <button class="notification-btn" id="notificationBtn">
                        <i class="fas fa-bell"></i>
                        <span class="badge" id="notificationBadge">0</span>
                    </button>
                    
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

                <!-- Log Out -->
                <form id="logout-form" action="{{ route('custom.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <button type="button" class="btn logout-btn" aria-label="Log out" data-bs-toggle="modal" data-bs-target="#logoutConfirmationModal">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>

            <div class="sms-chat-container">
                <!-- Sidebar -->
                <div class="sms-sidebar">
                    <div style="padding: 15px; border-bottom: 1px solid #dee2e6;">
                        <h3>SMS Chat</h3>
                        <button onclick="startNewSms()" style="padding: 5px 10px; background: #17a2b8; color: white; border: none; border-radius: 4px; width: 100%; margin-bottom: 10px;">
                            <i class="fas fa-plus me-1"></i> New SMS
                        </button>
                    </div>

                    <!-- Registered Users Section Dropdown -->
                    <div class="section-dropdown collapsed" onclick="toggleSection('users-section')">
                        <span>Registered Users</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div id="users-section-content" class="section-content collapsed">
                        <div id="users-list">
                            @if($users->count() > 0)
                                @foreach($users as $user)
                                <div class="contact-item" onclick="selectUser('{{ $user->id }}', '{{ $user->name }}', '{{ $user->cp_number }}')">
                                    <div class="contact-name">{{ $user->name }}</div>
                                    <div class="contact-phone">{{ $user->cp_number ? formatPhone($user->cp_number) : 'No phone number' }}</div>
                                </div>
                                @endforeach
                            @else
                                <div class="no-contacts">No users with phone numbers found</div>
                            @endif
                        </div>
                    </div>

                    <!-- SMS Conversations Section Dropdown -->
                    @if($smsConversations->count() > 0)
                    <div class="section-dropdown collapsed" onclick="toggleSection('conversations-section')">
                        <span>SMS Conversations</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div id="conversations-section-content" class="section-content collapsed">
                        <div id="sms-conversations-list">
                            @foreach($smsConversations as $userId => $conversation)
                                @php
                                    $user = \App\Models\User::find($userId);
                                    $latestMessage = \App\Models\SmsMessage::where('receiver_id', $userId)
                                        ->orWhere('sender_id', $userId)
                                        ->latest()
                                        ->first();
                                @endphp
                                @if($user)
                                <div class="contact-item" onclick="selectUser('{{ $user->id }}', '{{ $user->name }}', '{{ $user->cp_number }}')">
                                    <div class="contact-name">{{ $user->name }}</div>
                                    <div class="contact-phone">{{ formatPhone($user->cp_number) }}</div>
                                    @if($latestMessage)
                                    <div style="font-size: 0.8em; margin-top: 4px; opacity: 0.7;">
                                        {{ Str::limit($latestMessage->message, 30) }}
                                    </div>
                                    @endif
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- Chat Area -->
                <div class="sms-chat-area">
                    <div class="chat-header">
                        <h4 id="current-contact">Select a contact to start SMS chat</h4>
                        <div id="contact-phone" style="font-size: 0.9em; opacity: 0.9;"></div>
                    </div>
                    
                    <div class="messages-container" id="messages-container">
                        <div style="text-align: center; padding: 40px; color: #6c757d;">
                            <p><i class="fas fa-sms fa-2x mb-3"></i></p>
                            <p>Select a contact from the sidebar to view SMS messages</p>
                            <small>Messages sent and received will appear here</small>
                        </div>
                    </div>
                    
                    <!-- Compose Form -->
                    <div class="compose-form" id="compose-form" style="display: none;">
                        <form id="sms-form">
                            @csrf
                            <input type="hidden" id="to-user-id">
                            
                            <div style="margin-bottom: 10px;">
                                <textarea id="sms-message" 
                                          placeholder="Type your SMS message (max 160 characters)..." 
                                          rows="3" 
                                          style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                                          maxlength="160"
                                          required></textarea>
                                <div class="character-count" id="char-count">0/160</div>
                            </div>
                            
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('file-input').click()" style="padding: 8px 15px; background: #6c757d; color: white; border: none; border-radius: 4px;">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <button type="submit" style="padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 4px;">
                                    <i class="fas fa-paper-plane me-1"></i> Send SMS
                                </button>
                                <button type="button" id="cancel-sms" style="padding: 8px 15px; background: #6c757d; color: white; border: none; border-radius: 4px;">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Modal for Logout Confirmation -->
    <div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
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

                <!-- New SMS Modal -->
                <div class="modal fade" id="newSmsModal" tabindex="-1" aria-labelledby="newSmsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form id="new-sms-form">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="newSmsModalLabel">New SMS</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="new-sms-phone" class="form-label">Mobile Number</label>
                                        <input type="text" class="form-control" id="new-sms-phone" placeholder="e.g. 09171234567" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new-sms-message" class="form-label">Message</label>
                                        <textarea class="form-control" id="new-sms-message" rows="3" maxlength="160" required></textarea>
                                        <small id="new-sms-char-count" class="form-text text-muted">0/160</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Send SMS</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/sms-chat.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize notification system (same as email-chat)
            initializeNotificationSystem();
            
            // Initialize other SMS-specific functionality
            initializeSmsChat();
        });

        function initializeNotificationSystem() {
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationDropdown = document.getElementById('notificationDropdown');
            const markAllReadBtn = document.getElementById('markAllReadBtn');
            
            // Toggle notification dropdown
            if (notificationBtn) {
                notificationBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    notificationDropdown.classList.toggle('show');
                    // If dropdown opened, immediately hide badge and mark as read (user viewed notifications)
                    if (notificationDropdown.classList.contains('show')) {
                        try {
                            // Visual hide immediately
                            updateNotificationBadge(0);
                        } catch (err) {
                            console.error('updateNotificationBadge not available', err);
                        }
                        try {
                            // Mark all as read on server (non-blocking)
                            markAllNotificationsAsRead();
                        } catch (err) {
                            console.error('markAllNotificationsAsRead not available', err);
                        }
                    }
                });
            }
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (notificationBtn && notificationDropdown &&
                    !notificationBtn.contains(e.target) && 
                    !notificationDropdown.contains(e.target)) {
                    notificationDropdown.classList.remove('show');
                }
            });
            
            // Mark all as read
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    markAllNotificationsAsRead();
                });
            }
            
            // Initialize notification system
            loadNotifications();
            
            // Real-time polling every 10 seconds
            setInterval(() => {
                if (!notificationDropdown.classList.contains('show')) {
                    fetch('/admin/notifications/count')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const currentCount = parseInt(document.getElementById('notificationBadge').textContent);
                                if (data.unread_count > currentCount) {
                                    loadNotifications();
                                }
                                updateNotificationBadge(data.unread_count);
                            }
                        })
                        .catch(error => {
                            console.error('Real-time polling error:', error);
                        });
                }
            }, 10000); // 10 seconds
        }

        // Notification functions (same as in email-chat.blade.php)
        window.loadNotifications = function() {
            const notificationList = document.getElementById('notificationList');
            if (!notificationList) return;
            
            fetch('/admin/notifications/unread')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        updateNotificationBadge(data.unread_count);
                        renderNotifications(data.notifications);
                    } else {
                        console.error('Notification error:', data.error || 'Unknown error');
                        showFallbackNotifications();
                    }
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    showFallbackNotifications();
                });
        };

        function updateNotificationBadge(count) {
            const notificationBadge = document.getElementById('notificationBadge');
            if (notificationBadge) {
                notificationBadge.textContent = count;
                notificationBadge.style.display = count > 0 ? 'block' : 'none';
            }
        }

        function formatTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            
            // Check if date is valid
            if (isNaN(date.getTime())) {
                return 'Recently';
            }
            
            const seconds = Math.floor((now - date) / 1000);
            
            let interval = Math.floor(seconds / 31536000);
            if (interval >= 1) return interval + ' year' + (interval > 1 ? 's' : '') + ' ago';
            
            interval = Math.floor(seconds / 2592000);
            if (interval >= 1) return interval + ' month' + (interval > 1 ? 's' : '') + ' ago';
            
            interval = Math.floor(seconds / 86400);
            if (interval >= 1) return interval + ' day' + (interval > 1 ? 's' : '') + ' ago';
            
            interval = Math.floor(seconds / 3600);
            if (interval >= 1) return interval + ' hour' + (interval > 1 ? 's' : '') + ' ago';
            
            interval = Math.floor(seconds / 60);
            if (interval >= 1) return interval + ' minute' + (interval > 1 ? 's' : '') + ' ago';
            
            return 'Just now';
        }

        function renderNotifications(notifications) {
            const notificationList = document.getElementById('notificationList');
            if (!notificationList) return;
            
            if (!notifications || notifications.length === 0) {
                notificationList.innerHTML = `
                    <div class="notification-empty">
                        <i class="fas fa-bell-slash"></i>
                        <p>No new notifications</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            
            notifications.forEach(notification => {
                const timeAgo = formatTimeAgo(notification.created_at);
                const isUnread = !notification.is_read;
                
                // Determine icon and redirect URL based on notification type
                let iconClass = 'fas fa-calendar-plus';
                let redirectUrl = '{{ route("clientstbl") }}';
                let seeMoreText = 'See More';
                
                if (notification.type === 'message') {
                    switch (notification.icon_type) {
                        case 'envelope':
                            iconClass = 'fas fa-envelope';
                            seeMoreText = 'View Email';
                            break;
                        case 'sms':
                            iconClass = 'fas fa-sms';
                            seeMoreText = 'View SMS';
                            break;
                        case 'comments':
                            iconClass = 'fas fa-comments';
                            seeMoreText = 'View Chat';
                            break;
                        default:
                            iconClass = 'fas fa-comments';
                            seeMoreText = 'View Message';
                            break;
                    }
                    redirectUrl = notification.redirect_url;
                }
                
                html += `
                    <div class="notification-item ${isUnread ? 'unread' : ''}" 
                         data-id="${notification.id}" 
                         onclick="markNotificationAsRead('${notification.id}', this)">
                        <div class="notification-icon">
                            <i class="${iconClass}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">${escapeHtml(notification.title)}</div>
                            <div class="notification-message">${escapeHtml(notification.message)}</div>
                            <div class="notification-time">
                                <i class="far fa-clock"></i>
                                ${timeAgo}
                            </div>
                            <div class="notification-actions-row">
                                <button class="btn btn-sm btn-outline-primary see-more-btn" 
                                        onclick="event.stopPropagation(); window.location.href='${redirectUrl}'">
                                    <i class="fas fa-external-link-alt"></i> ${seeMoreText}
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            notificationList.innerHTML = html;
        }

        function showFallbackNotifications() {
            const notificationList = document.getElementById('notificationList');
            if (notificationList) {
                notificationList.innerHTML = `
                    <div class="notification-empty">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Unable to load notifications</p>
                        <small>Please check your connection</small>
                    </div>
                `;
            }
        }

        // Mark notification as read
        window.markNotificationAsRead = function(id, element) {
            fetch(`/admin/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (element) {
                        element.classList.remove('unread');
                    }
                    updateNotificationBadge(data.unread_count);
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        };

        // Mark all notifications as read
        function markAllNotificationsAsRead() {
            fetch('/admin/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove unread class from all items
                    document.querySelectorAll('.notification-item.unread').forEach(item => {
                        item.classList.remove('unread');
                    });
                    updateNotificationBadge(0);
                }
            })
            .catch(error => {
                console.error('Error marking all notifications as read:', error);
            });
        }

        // Refresh notifications function
        window.refreshNotifications = function() {
            loadNotifications();
        };

        function escapeHtml(unsafe) {
            if (!unsafe) return '';
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // SMS chat specific initialization
        function initializeSmsChat() {
            // Initialize section dropdowns
            const usersSectionExpanded = localStorage.getItem('users-section-expanded') === 'true';
            const conversationsSectionExpanded = localStorage.getItem('conversations-section-expanded') === 'true';
            
            // Apply initial state
            setSectionState('users-section', usersSectionExpanded);
            setSectionState('conversations-section', conversationsSectionExpanded);
            
            // Initialize character counter
            const smsMessage = document.getElementById('sms-message');
            const charCount = document.getElementById('char-count');
            
            if (smsMessage && charCount) {
                smsMessage.addEventListener('input', function() {
                    const length = this.value.length;
                    charCount.textContent = length + '/160';
                    charCount.style.color = length > 140 ? '#dc3545' : length > 120 ? '#ffc107' : '#28a745';
                });
            }
            
            // Cancel SMS button
            const cancelSmsBtn = document.getElementById('cancel-sms');
            if (cancelSmsBtn) {
                cancelSmsBtn.addEventListener('click', function() {
                    document.getElementById('compose-form').style.display = 'none';
                    document.getElementById('sms-message').value = '';
                    document.getElementById('char-count').textContent = '0/160';
                });
            }
            
            // SMS form submission
            const smsForm = document.getElementById('sms-form');
            if (smsForm) {
                smsForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    sendSms();
                });
            }

            // New SMS modal form handling
            const newSmsForm = document.getElementById('new-sms-form');
            if (newSmsForm) {
                const phoneInput = document.getElementById('new-sms-phone');
                const msgInput = document.getElementById('new-sms-message');
                const charCount = document.getElementById('new-sms-char-count');

                msgInput.addEventListener('input', function() {
                    charCount.textContent = this.value.length + '/160';
                });

                newSmsForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const phone = phoneInput.value.trim();
                    const message = msgInput.value.trim();
                    if (!phone || !message) {
                        alert('Please enter phone number and message');
                        return;
                    }

                    const sendBtn = newSmsForm.querySelector('button[type="submit"]');
                    const orig = sendBtn.innerHTML;
                    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                    sendBtn.disabled = true;

                    fetch('{{ url('/admin/sms-send') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ phone_number: phone, message: message })
                    })
                    .then(response => {
                        const contentType = response.headers.get('content-type') || '';
                        if (contentType.includes('application/json')) {
                            return response.json().then(data => ({ ok: response.ok, json: data }));
                        }
                        // Fallback: return text (likely an HTML error page)
                        return response.text().then(text => ({ ok: response.ok, text }));
                    })
                    .then(result => {
                        if (result.json) {
                            const data = result.json;
                            if (data.status === 'success') {
                                showToast('SMS sent successfully!', 'success');
                                // Close modal
                                const modalEl = document.getElementById('newSmsModal');
                                const modal = bootstrap.Modal.getInstance(modalEl);
                                if (modal) modal.hide();
                                // Optionally reload conversations
                                loadSmsConversation(document.getElementById('to-user-id').value || null);
                            } else {
                                showToast('Failed to send SMS: ' + (data.message || 'Unknown'), 'error');
                                console.error('SMS send JSON error:', data);
                            }
                        } else {
                            // Non-JSON response (HTML error page etc.)
                                console.error('SMS send non-JSON response (status ' + (result.status || 'unknown') + '):', result.text);
                                showToast('Failed to send SMS: server responded with status ' + (result.status || 'unknown'), 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Fetch error:', err);
                        showToast('Failed to send SMS. Please try again.', 'error');
                    })
                    .finally(() => {
                        sendBtn.innerHTML = orig;
                        sendBtn.disabled = false;
                    });
                });
            }
        }

        function toggleSection(sectionId) {
            const dropdown = document.querySelector(`.section-dropdown[onclick*="${sectionId}"]`);
            const content = document.getElementById(`${sectionId}-content`);
            
            if (!dropdown || !content) return;
            
            const isCurrentlyExpanded = dropdown.classList.contains('expanded');
            
            if (isCurrentlyExpanded) {
                // Collapse
                dropdown.classList.remove('expanded');
                dropdown.classList.add('collapsed');
                content.classList.remove('expanded');
                content.classList.add('collapsed');
            } else {
                // Expand
                dropdown.classList.remove('collapsed');
                dropdown.classList.add('expanded');
                content.classList.remove('collapsed');
                content.classList.add('expanded');
            }
            
            // Save state to localStorage
            localStorage.setItem(`${sectionId}-expanded`, !isCurrentlyExpanded);
        }

        function setSectionState(sectionId, expanded) {
            const dropdown = document.querySelector(`.section-dropdown[onclick*="${sectionId}"]`);
            const content = document.getElementById(`${sectionId}-content`);
            
            if (!dropdown || !content) return;
            
            if (expanded) {
                dropdown.classList.remove('collapsed');
                dropdown.classList.add('expanded');
                content.classList.remove('collapsed');
                content.classList.add('expanded');
            } else {
                dropdown.classList.remove('expanded');
                dropdown.classList.add('collapsed');
                content.classList.remove('expanded');
                content.classList.add('collapsed');
            }
        }

        // SMS chat functions
        function selectUser(userId, name, phone) {
            document.getElementById('current-contact').textContent = name;
            document.getElementById('contact-phone').textContent = phone ? formatPhoneDisplay(phone) : 'No phone number';
            document.getElementById('to-user-id').value = userId;
            
            // Check if phone number exists
            if (!phone || phone === 'No phone number' || phone.trim() === '') {
                document.getElementById('compose-form').style.display = 'none';
                alert('This user does not have a valid phone number. Please update their contact information first.');
                return;
            }
            
            // Show compose form
            document.getElementById('compose-form').style.display = 'block';
            
            // Load conversation
            loadSmsConversation(userId);
        }

        function formatPhoneDisplay(phone) {
            if (!phone) return '';
            
            // Remove non-numeric characters
            phone = phone.replace(/\D/g, '');
            
            if (phone.length === 10) {
                return '(' + phone.substring(0, 3) + ') ' + phone.substring(3, 6) + '-' + phone.substring(6);
            } else if (phone.length === 11 && phone.startsWith('0')) {
                return '(' + phone.substring(1, 4) + ') ' + phone.substring(4, 7) + '-' + phone.substring(7);
            } else if (phone.length === 12 && phone.startsWith('63')) {
                return '+63 ' + phone.substring(2, 5) + ' ' + phone.substring(5, 8) + ' ' + phone.substring(8);
            }
            
            return phone;
        }

        function startNewSms() {
            const modalEl = document.getElementById('newSmsModal');
            if (!modalEl) return;
            const modal = new bootstrap.Modal(modalEl);
            // Reset form
            document.getElementById('new-sms-phone').value = '';
            document.getElementById('new-sms-message').value = '';
            document.getElementById('new-sms-char-count').textContent = '0/160';
            modal.show();
        }

        function loadSmsConversation(userId) {
            if (!userId) return;
            
            const messagesContainer = document.getElementById('messages-container');
            messagesContainer.innerHTML = '<div class="loading">Loading messages...</div>';
            
            fetch(`/admin/sms-conversation/${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        renderMessages(data.conversation);
                    } else {
                        messagesContainer.innerHTML = '<div class="error">Failed to load messages</div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading conversation:', error);
                    messagesContainer.innerHTML = '<div class="error">Error loading conversation</div>';
                });
        }
        function renderMessages(messages) {
    const container = document.getElementById('messages-container');
    container.innerHTML = '';
    
    if (!messages || messages.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #6c757d;">
                <p><i class="fas fa-sms fa-2x mb-3"></i></p>
                <p>No messages yet</p>
                <small>Start the conversation by sending a message</small>
            </div>
        `;
        return;
    }
    
    messages.forEach(msg => {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${msg.is_incoming ? 'incoming' : 'outgoing'}`;
        
        messageDiv.innerHTML = `
            <div class="message-header">
                <span class="sender">${msg.sender_name || 'Unknown'}</span>
                <span class="time">${msg.created_at_formatted}</span>
            </div>
            <div class="message-body">${msg.message}</div>
            <div class="message-footer">
                <span class="status">${msg.status || 'sent'}</span>
                <span class="phone">${msg.formatted_phone || msg.phone_number}</span>
            </div>
        `;
        
        container.appendChild(messageDiv);
    });
    
    // Scroll to bottom
    container.scrollTop = container.scrollHeight;
}

        function sendSms() {
    const userId = document.getElementById('to-user-id').value;
    const message = document.getElementById('sms-message').value;
    
    if (!userId || !message.trim()) {
        alert('Please enter a message');
        return;
    }

    // Show loading state
    const sendBtn = document.querySelector('#sms-form button[type="submit"]');
    const originalText = sendBtn.innerHTML;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    sendBtn.disabled = true;

    // Make AJAX request
    fetch("{{ route('sms.send') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            to_user_id: userId,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('SMS Response:', data);
        
        if (data.status === 'success') {
            // Success - clear form and reload conversation
            document.getElementById('sms-message').value = '';
            document.getElementById('char-count').textContent = '0/160';
            
            // Reload the conversation
            if (userId) {
                loadSmsConversation(userId);
            }
            
            // Show success message
            showToast('SMS sent successfully! Status: ' + (data.sms?.status || 'queued'), 'success');
        } else {
            // Error
            showToast('Failed to send SMS: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to send SMS. Please try again.', 'error');
    })
    .finally(() => {
        // Reset button
        sendBtn.innerHTML = originalText;
        sendBtn.disabled = false;
    });
}
        // Helper function to show toast messages
function showToast(message, type = 'success') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast-message ${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
    `;
    
    // Add to page
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 3000);
}
// Add CSS for toast
const toastCSS = `
.toast-message {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-left: 4px solid #28a745;
    padding: 12px 16px;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 9999;
    animation: slideIn 0.3s ease;
}
.toast-message.error {
    border-left-color: #dc3545;
}
.toast-message i {
    font-size: 18px;
}
.toast-message.success i {
    color: #28a745;
}
.toast-message.error i {
    color: #dc3545;
}
.toast-message button {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #666;
}
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
`;
// Add style to head
const style = document.createElement('style');
style.textContent = toastCSS;
document.head.appendChild(style);
    </script>
@include('partials.notification-badge-visibility')
</body>
</html>