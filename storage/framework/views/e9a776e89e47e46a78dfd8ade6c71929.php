<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <title>System Chat | LegalConnect</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/system-chat.blade.css')); ?>">
    
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">
                <img src="<?php echo e(asset('logo6.png')); ?>" alt="LegalConnect logo" width="40" height="40" style="border-radius: 50%;">
                <span>LegalConnect</span>
            </div>
            <div class="list-group list-group-flush">
                <a href="<?php echo e(url('/admindashboard')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('admindashboard') ? 'active' : ''); ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="<?php echo e(url('/administrator')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('administrator') ? 'active' : ''); ?>">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Set Time</span>
                </a>
                 <a href="<?php echo e(url('/appointments')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('appointments') ? 'active' : ''); ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Logs Requests</span>
                </a>
                 <a href="<?php echo e(route('admin.walkins')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('admin.walkins') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-clipboard" style="color: #cdd3df;"></i>
                    <span>Walk-Ins logs</span>
                </a>
                <a href="#messagesSubmenu" 
                class="list-group-item list-group-item-action <?php echo e(request()->is('email-chat') || request()->is('admin/system-chat') ? 'active' : ''); ?>"
                data-bs-toggle="collapse" 
                aria-expanded="<?php echo e(request()->is('email-chat') || request()->is('admin/system-chat') ? 'true' : 'false'); ?>">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu collapse <?php echo e(request()->is('email-chat') || request()->is('admin/system-chat') ? 'show' : ''); ?> list-group" id="messagesSubmenu">
                    <a href="<?php echo e(route('messages.email')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('email-chat') ? 'active' : ''); ?>">
                        <i class="fas fa-envelope"></i>
                        <span>Email</span>
                    </a>
                    <a href="<?php echo e(route('messages.sms')); ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-sms"></i>
                        <span>SMS</span>
                    </a>
                    <a href="<?php echo e(route('admin.system-chat')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('admin/system-chat') ? 'active' : ''); ?>">
                        <i class="fas fa-comments"></i>
                        <span>System Chatting</span>
                    </a>
                </div>
                <a href="<?php echo e(url('/practice-areas')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('practice-areas') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-suitcase"></i>
                    <span>Services</span>
                </a>

                <a href="#requestsSubmenu" class="list-group-item list-group-item-action <?php echo e(request()->is('clientstbl') || request()->is('adminAcceptedRequest') || request()->is('adminDeniedRequest') ? 'active' : ''); ?>" data-bs-toggle="collapse" aria-expanded="<?php echo e(request()->is('clientstbl') || request()->is('adminAcceptedRequest') || request()->is('adminDeniedRequest') ? 'true' : 'false'); ?>">
                    <i class="fas fa-list-alt"></i>
                    <span>Appointment Requests</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu collapse <?php echo e(request()->is('clientstbl') || request()->is('adminAcceptedRequest') || request()->is('adminDeniedRequest') ? 'show' : ''); ?> list-group" id="requestsSubmenu">
                    <a href="<?php echo e(url('/clientstbl')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('clientstbl') ? 'active' : ''); ?>">
                        <i class="fas fa-clock"></i>
                        <span>Pending Requests</span>
                    </a>
                    <a href="<?php echo e(url('/adminAcceptedRequest')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('adminAcceptedRequest') ? 'active' : ''); ?>">
                        <i class="fas fa-check-circle"></i>
                        <span>Accepted Requests</span>
                    </a>
                    <a href="<?php echo e(url('/adminDeniedRequest')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('adminDeniedRequest') ? 'active' : ''); ?>">
                        <i class="fas fa-times-circle"></i>
                        <span>Denied Requests</span>
                    </a>
                </div>

                <a href="<?php echo e(url('/adminAccount')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('adminAccount') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-user-group"></i>
                    <span>All Staff Accounts</span>
                </a>
                <a href="<?php echo e(route('admin.account.settings')); ?>"
                class="list-group-item list-group-item-action <?php echo e(request()->routeIs('admin.account.settings') ? 'active' : ''); ?>">
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

                <!-- Notification Dropdown -->
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
                            <a href="<?php echo e(route('clientstbl')); ?>" class="btn btn-sm btn-primary w-100">
                                View All Pending Requests
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Log Out -->
                <form id="logout-form" action="<?php echo e(route('custom.logout')); ?>" method="POST" style="display: none;">
                    <?php echo csrf_field(); ?>
                </form>
                <button type="button" class="btn logout-btn" aria-label="Log out" onclick="showLogoutConfirmation()">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>

            <!-- Main Chat Container -->
            <div class="container-chat" style="height: calc(100vh - 60px);">
                 <div class="row chat-container" style="height: 100% !important;">
                    <!-- Sidebar with Conversations -->
                    <div class="col-md-4 chat-sidebar">
                        <div class="chat-sidebar-inner">
                            <div class="chat-sidebar-header">
                                <h4 class="mb-3">System Chat</h4>
                                <div class="input-group mb-3">
                                    <input type="text" id="search-clients" class="form-control" placeholder="Search clients...">
                                    <button class="btn btn-light" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <button class="btn btn-light btn-sm w-100" onclick="refreshConversations()">
                                    <i class="fas fa-sync-alt"></i> Refresh Conversations
                                </button>
                            </div>
                            
                            <div class="chat-sidebar-content">
                                <!-- Dropdown for Active Conversations -->
                                <div class="dropdown-section">
                                    <div class="dropdown-header" data-bs-toggle="collapse" data-bs-target="#activeConversationsCollapse" 
                                        aria-expanded="true" aria-controls="activeConversationsCollapse">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-comments me-2"></i>
                                                <strong>Active Conversations</strong>
                                            </div>
                                            <div class="dropdown-indicator">
                                                <i class="fas fa-chevron-down"></i>
                                            </div>
                                        </div>
                                        <small class="text-light opacity-75">Click to expand/collapse</small>
                                    </div>
                                    
                                    <div class="collapse show" id="activeConversationsCollapse">
                                        <div class="dropdown-body">
                                            <div id="conversations-list" class="conversations-list">
                                                <div class="loading-dots">
                                                    <span></span>
                                                    <span></span>
                                                    <span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Dropdown for All Registered Clients -->
                                <div class="dropdown-section">
                                    <div class="dropdown-header" data-bs-toggle="collapse" data-bs-target="#allClientsCollapse" 
                                        aria-expanded="true" aria-controls="allClientsCollapse">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-users me-2"></i>
                                                <strong>All Registered Clients</strong>
                                            </div>
                                            <div class="dropdown-indicator">
                                                <i class="fas fa-chevron-down"></i>
                                            </div>
                                        </div>
                                        <small class="text-light opacity-75">Click to start conversation</small>
                                    </div>
                                    
                                    <div class="collapse show" id="allClientsCollapse">
                                        <div class="dropdown-body">
                                            <div id="all-clients-list" class="all-clients-list">
                                                <!-- Clients will be loaded here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Main Chat Area -->
                    <div class="col-md-8 p-0 d-flex flex-column chat-main">
                        <!-- Chat Header -->
                        <div class="chat-header">
                            <div>
                                <h5 id="current-client-name">Select a conversation</h5>
                                <small id="current-client-email"></small>
                                <div id="current-client-status" class="current-client-status d-none"></div>
                            </div>
                            <div id="typing-indicator" class="typing-indicator" style="display: none;"></div>
                        </div>
                        
                        <!-- Messages Container -->
                        <div class="messages-container" id="messages-container">
                            <!-- Status message will be shown here -->
                            <div class="chat-status" id="chat-status">
                                Select a conversation from the sidebar to start chatting
                            </div>
                            <!-- Messages will be loaded here -->
                        </div>
                                                    
                        <!-- Message Input Area (Always visible) -->
                        <div class="message-input-area">
                            <form id="message-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" id="conversation-id">
                                <input type="hidden" id="client-id">
                                
                                <div class="input-group">
                                    <button type="button" id="video-call-btn" class="btn btn-outline-secondary" onclick="initiateVideoCallAdmin()" title="Select an online client to start a video call" disabled>
                                        <i class="fas fa-video"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('file-input').click()">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                    <input type="file" id="file-input" style="display: none;" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt">
                                    
                                    <input type="text" id="message-input" class="form-control" placeholder="Type your message..." autocomplete="off">
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                                
                                <div id="file-preview" class="mt-2" style="display: none;"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Modal for Logout Confirmation -->
<div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">
                    <i class="fas fa-sign-out-alt me-2"></i>Confirm Logout
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div style="font-size: 48px; color: #ffc107; margin-bottom: 15px;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h4 class="mb-3">Confirm Logout</h4>
                <p>Are you sure you want to log out?<br>You will be redirected to the login page.</p>
            </div>
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
    <!-- Pusher for real-time -->
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <script src="<?php echo e(asset('js/system-chat.js')); ?>"></script>
    <!-- WebRTC Call Manager -->
    <script src="<?php echo e(asset('js/webrtc-call.js')); ?>"></script>
    <script>
window.chatConfig = {
    routes: {
        conversations: "<?php echo e(route('admin.chat.conversations')); ?>",
        startConversation: "<?php echo e(route('admin.chat.start')); ?>",
        sendMessage: "<?php echo e(route('admin.chat.send', ':conversationId')); ?>",
        messages: "<?php echo e(route('admin.chat.messages', ':conversationId')); ?>",
        markConversationAsRead: "<?php echo e(route('admin.chat.conversation.read', ':conversationId')); ?>",
        markMessageAsRead: "<?php echo e(route('chat.message.read', ':messageId')); ?>",
        pollMessages: "<?php echo e(route('admin.chat.poll')); ?>", // Add this line
        typing: "<?php echo e(route('admin.chat.typing')); ?>",
        downloadFile: "<?php echo e(route('admin.chat.messages.download', ':messageId')); ?>"
    },
    adminId: <?php echo e(Auth::id()); ?>,
    csrfToken: "<?php echo e(csrf_token()); ?>",
    broadcastDriver: "<?php echo e(config('broadcasting.default')); ?>",
    pusherKey: "<?php echo e(config('broadcasting.connections.pusher.key')); ?>",
    pusherCluster: "<?php echo e(config('broadcasting.connections.pusher.options.cluster')); ?>"
};
</script>
    <script>
        // Notification System
        document.addEventListener('DOMContentLoaded', function() {
            initializeNotificationSystem();
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

        function loadNotifications() {
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
        }

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
                let redirectUrl = '<?php echo e(route("clientstbl")); ?>';
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

        function markNotificationAsRead(id, element) {
            fetch(`/admin/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
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
        }

        function markAllNotificationsAsRead() {
            fetch('/admin/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
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

        function refreshNotifications() {
            loadNotifications();
        }

        function escapeHtml(unsafe) {
            if (!unsafe) return '';
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Logout confirmation function
        function showLogoutConfirmation() {
            const logoutModal = new bootstrap.Modal(document.getElementById('logoutConfirmationModal'));
            logoutModal.show();
        }
    </script>
<?php echo $__env->make('partials.notification-badge-visibility', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>
</html> 
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\admin\chat\index.blade.php ENDPATH**/ ?>