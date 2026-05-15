<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <title>Email Chat | LegalConnect</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="<?php echo e(asset('css/email-chat.blade.css')); ?>">
    <style>
        /* Temporary debug highlight for suspect message IDs */
        .suspect-highlight {
            border: 2px solid #ff3b30 !important;
            box-shadow: 0 0 12px rgba(255,59,48,0.65) !important;
            background: rgba(255,59,48,0.06) !important;
            animation: suspectPulse 1.5s ease-in-out infinite;
            transition: transform 0.15s ease;
        }

        @keyframes suspectPulse {
            0% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
            100% { transform: translateY(0); }
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        /* Compose Form Header Styles */
        .compose-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            cursor: pointer;
        }

        .compose-header h5 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
        }

        .compose-header-actions {
            display: flex;
            gap: 8px;
        }

        .compose-header-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background-color 0.2s;
            font-size: 12px;
            color: white !important;
        }

        .compose-header-btn:hover {
            background: none;
        }

        .compose-form-content {
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-top: none;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .compose-form.minimized .compose-form-content {
            display: none;
        }

        .compose-form.minimized {
            margin-bottom: 0;
        }

        /* Minimized state indicator */
        .minimized-indicator {
            display: none;
            padding: 8px 15px;
            background: #e9ecef;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 12px;
            color: #ffffffff;
            cursor: pointer;
            margin: 10px 15px;
        }

        .compose-form.minimized + .minimized-indicator {
            display: block;
        }

        /* Dropdown Section Styles */
        .section-dropdown {
            cursor: pointer;
            padding: 10px 15px;
            background: black;
            font-weight: bold;
            border-bottom: 1px solid #dee2e6;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s;
        }

        .section-dropdown:hover {
            background: #333;
        }

        .section-dropdown i {
            transition: transform 0.3s ease;
        }

        .section-dropdown.collapsed i {
            transform: rotate(0deg);
        }

        .section-dropdown.expanded i {
            transform: rotate(180deg);
        }

        .section-content {
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .section-content.collapsed {
            max-height: 0;
        }

        .section-content.expanded {
            max-height: 1000px; /* Large enough to show all content */
        }

        /* Make specific dropdowns scrollable when content overflows */
        /* Make specific dropdowns scrollable when content overflows.
           Only apply the visible max-height when expanded so collapse still works. */
        #users-section-content,
        #conversations-section-content {
            padding: 8px 12px;
        }

        #users-section-content.collapsed,
        #conversations-section-content.collapsed {
            max-height: 0;
            overflow: hidden;
        }

        #users-section-content.expanded,
        #conversations-section-content.expanded {
            max-height: 320px; /* sensible viewport height for the dropdown when expanded */
            overflow-y: auto;
        }

        /* Custom scrollbar styling for the dropdown lists */
        #users-section-content.expanded::-webkit-scrollbar,
        #conversations-section-content.expanded::-webkit-scrollbar {
            width: 8px;
        }
        #users-section-content.expanded::-webkit-scrollbar-track,
        #conversations-section-content.expanded::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        #users-section-content.expanded::-webkit-scrollbar-thumb,
        #conversations-section-content.expanded::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

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
    <div id="wrapper" >
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
                <a href="<?php echo e(url('/statistics')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('statistics') ? 'active' : ''); ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Statistics</span>
                </a>
               <a href="#messagesSubmenu" 
                class="list-group-item list-group-item-action <?php echo e(request()->is('email-chat') || request()->is('messages/*') ? 'active' : ''); ?>"
                data-bs-toggle="collapse" 
                aria-expanded="<?php echo e(request()->is('email-chat') || request()->is('messages/*') ? 'true' : 'false'); ?>">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu collapse <?php echo e(request()->is('email-chat') || request()->is('messages/*') ? 'show' : ''); ?> list-group" id="messagesSubmenu">
                    <a href="<?php echo e(route('messages.email')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('email-chat') ? 'active' : ''); ?>">
                        <i class="fas fa-envelope"></i>
                        <span>Email</span>
                    </a>
                    <a href="<?php echo e(route('messages.sms')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('sms-chat') ? 'active' : ''); ?>">
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
                            <a href="<?php echo e(route('clientstbl')); ?>" class="btn btn-sm btn-primary w-100">
                                View All Pending Requests
                            </a>
                        </div>
                    </div>
                </div>

                <form id="logout-form" action="<?php echo e(route('custom.logout')); ?>" method="POST" style="display: none;">
                    <?php echo csrf_field(); ?>
                </form>
                <button type="button" class="btn logout-btn" onclick="showLogoutModal()">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>

             <div class="email-chat-container">
                <!-- Sidebar -->
                <div class="email-sidebar">
                    <div style="padding: 15px; border-bottom: 1px solid #dee2e6;">
                        <h3>Email Chat</h3>
                        <button onclick="fetchNewEmails()" style="padding: 5px 10px; background: #28a745; color: white; border: none; border-radius: 4px; margin-bottom: 10px;">
                            Check New Emails
                        </button>
                        <button onclick="startNewEmail()" style="padding: 5px 10px; background: #17a2b8; color: white; border: none; border-radius: 4px; width: 100%;">
                            + New Email
                        </button>
                    </div>

                    <!-- Registered Users Section Dropdown - CHANGED TO COLLAPSED -->
                    <div class="section-dropdown collapsed" onclick="toggleSection('users-section')">
                        <span>Registered Users</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div id="users-section-content" class="section-content collapsed">
                        <div id="users-list">
                            <?php if(count($users) > 0): ?>
                                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="contact-item" onclick="selectUser('<?php echo e($user->email); ?>', '<?php echo e($user->name); ?>')">
                                    <div class="contact-name"><?php echo e($user->name); ?></div>
                                    <div class="contact-email"><?php echo e($user->email); ?></div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <div class="no-contacts">No users found</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Email Conversations Section Dropdown - CHANGED TO COLLAPSED -->
                    <?php if(count($emailConversations) > 0): ?>
                    <div class="section-dropdown collapsed" onclick="toggleSection('conversations-section')">
                        <span>Email Conversations</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div id="conversations-section-content" class="section-content collapsed">
                        <div id="email-conversations-list">
                            <?php $__currentLoopData = $emailConversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conversation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="contact-item" onclick="selectEmail('<?php echo e($conversation['sender_email']); ?>', '<?php echo e($conversation['sender_name'] ?? $conversation['sender_email']); ?>')">
                                <div class="contact-name"><?php echo e($conversation['sender_name'] ?? $conversation['sender_email']); ?></div>
                                <div class="contact-email"><?php echo e($conversation['sender_email']); ?></div>
                                <div style="font-size: 0.8em; margin-top: 4px; opacity: 0.7;">
                                    <?php echo e(Str::limit($conversation['latest_subject'], 30)); ?>

                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Chat Area -->
                <div class="email-chat-area">
                    <div class="chat-header">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <div>
                                <h4 id="current-contact">Select a contact to start chatting</h4>
                                <div id="contact-email" style="font-size: 0.9em; opacity: 0.9;"></div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <button id="refresh-messages-btn" 
                                        style="display: none; padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9em;"
                                        title="Refresh conversation">
                                    <i class="fas fa-sync-alt"></i> Refresh Messages
                                </button>
                                <span id="auto-polling-indicator" style="display: none; font-size: 0.8em; color: #28a745;">
                                    <i class="fas fa-circle-dot" style="animation: pulse-dot 2s infinite;"></i> Auto-syncing
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="messages-container" id="messages-container">
                        <div style="text-align: center; padding: 40px; color: #6c757d;">
                            <p>Select a contact from the sidebar to view messages</p>
                        </div>
                    </div>
                    
                    <!-- Compose Form with Minimize/Hide Functionality -->
                    <div class="compose-form" id="compose-form" style="display: none;">
                    <div class="compose-header" id="compose-header">
                        <h5><i class="fas fa-edit me-2"></i>Compose Message</h5>
                        <div class="compose-header-actions">
                            <!-- Minimize button (visible when expanded) -->
                            <button class="compose-header-btn minimize-btn" id="minimize-compose" title="Minimize">
                                <i class="fas fa-minus"></i>
                            </button>
                            <!-- Expand button (visible when minimized) -->
                            <button class="compose-header-btn expand-btn" id="expand-compose-header" title="Expand" style="display: none;">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>

                        <div class="compose-form-content">
                            <form id="reply-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" id="reply-to-email">
                                
                                <div style="margin-bottom: 10px;">
                                    <input type="text" id="subject" placeholder="Subject" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
                                </div>
                                
                                <div style="margin-bottom: 10px;">
                                    <textarea id="message" placeholder="Type your message..." rows="4" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required></textarea>
                                </div>
                                <div style="margin-bottom: 10px;">
                                    <label>Attachments:</label>
                                    <input type="file" id="reply-attachments" name="attachments[]" multiple style="width:100%;" />
                                </div>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <button type="submit" style="padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px;">
                                        <i class="fas fa-paper-plane me-1"></i> Send Email
                                    </button>
                                    <!--<button type="button" id="cancel-compose" style="padding: 8px 15px; background: #6c757d; color: white; border: none; border-radius: 4px;">
                                        Cancel
                                    </button>-->
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Minimized State Indicator 
                    <div class="minimized-indicator" id="minimized-indicator" style="display: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span>
                                <i class="fas fa-edit me-2"></i>
                                Compose message to <strong id="minimized-contact"></strong>
                            </span>
                            <button class="compose-header-btn expand-minimized-btn">
                                <i class="fas fa-expand me-1"></i> Expand
                            </button>
                        </div>
                    </div>-->
                </div>
            </div>

            <!-- New Email Modal -->
            <div id="new-email-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; width: 400px;">
                    <h3>New Email</h3>
                    <form id="new-email-form">
                        <?php echo csrf_field(); ?>
                        <div style="margin-bottom: 10px;">
                            <label>To:</label>
                            <input type="email" id="new-to-email" placeholder="Recipient email" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <label>Subject:</label>
                            <input type="text" id="new-subject" placeholder="Subject" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label>Message:</label>
                            <textarea id="new-message" placeholder="Type your message..." rows="4" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required></textarea>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <label>Attachments:</label>
                            <input type="file" id="new-attachments" name="attachments[]" multiple style="width:100%;" />
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" style="padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px;">Send</button>
                            <button type="button" onclick="closeNewEmail()" style="padding: 8px 15px; background: #6c757d; color: white; border: none; border-radius: 4px;">Cancel</button>
                        </div>
                    </form>
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
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    let currentEmail = '';
        let currentName = '';
        let currentUserEmail = '<?php echo e(Auth::check() ? Auth::user()->email : ""); ?>'; // FIXED: Check for null user
        // Temporary: suspect message id to highlight for debugging (override in console: window.SUSPECT_MESSAGE_ID = 3003)
        window.SUSPECT_MESSAGE_ID = window.SUSPECT_MESSAGE_ID || 3003;
        let isComposeMinimized = false;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize sidebar functionality
            initializeSidebar();
            
            // Initialize compose form functionality
            initializeComposeForm();
            
            // Initialize section dropdowns
            initializeSectionDropdowns();
            
            // Initialize notification system
            initializeNotificationSystem();
            
            // Load Gmail messages on page load
            setTimeout(() => {
                fetchNewEmails();
            }, 1000);
            
            // Auto-fetch new emails every 15 seconds (for general inbox)
            setInterval(() => {
                if (!currentEmail) { // Only if not in a specific conversation
                    fetchNewEmails();
                }
            }, 15000);
            
           // Add refresh button event listener
            const refreshBtn = document.getElementById('refresh-messages-btn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', refreshConversation);
            }
            
            // Set up auto-refresh every 5 seconds when a conversation is active - FAST polling for incoming emails
            let pollingInterval = setInterval(() => {
                if (currentEmail) {
                    // Show polling indicator
                    const pollingIndicator = document.getElementById('auto-polling-indicator');
                    if (pollingIndicator) pollingIndicator.style.display = 'inline-block';
                    
                    // lightweight check for new messages and try to auto-append latest
                    pollLatestMessage();
                }
            }, 5000);
        });

    function initializeSidebar() {
        // Toggle sidebar
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('wrapper').classList.toggle('toggled');
        });
        
        // Close other submenus when opening a new one
        const menuItems = document.querySelectorAll('.list-group-item[data-bs-toggle="collapse"]');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                const targetId = this.getAttribute('href');
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                
                if (isExpanded) return;
                
                menuItems.forEach(otherItem => {
                    if (otherItem !== this) {
                        const otherTargetId = otherItem.getAttribute('href');
                        const otherTarget = document.querySelector(otherTargetId);
                        if (otherTarget && otherTarget.classList.contains('show')) {
                            const bsCollapse = new bootstrap.Collapse(otherTarget);
                            bsCollapse.hide();
                        }
                    }
                });
            });
        });
        
        // Set active menu item on click
        const allMenuItems = document.querySelectorAll('.list-group-item');
        allMenuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                if (this.hasAttribute('data-bs-toggle') && 
                    this.getAttribute('data-bs-toggle') === 'collapse') {
                    return;
                }
                
                allMenuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

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

    // Notification functions (same as in administrator.blade.php)
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

    // Mark notification as read
    window.markNotificationAsRead = function(id, element) {
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
    };

    // Mark all notifications as read
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

    // Refresh notifications function
    window.refreshNotifications = function() {
        loadNotifications();
    };

    function initializeSectionDropdowns() {
        // Set initial state from localStorage or default to COLLAPSED
        const usersSectionExpanded = localStorage.getItem('users-section-expanded') === 'true';
        const conversationsSectionExpanded = localStorage.getItem('conversations-section-expanded') === 'true';
        
        // Apply initial state
        setSectionState('users-section', usersSectionExpanded);
        setSectionState('conversations-section', conversationsSectionExpanded);
    }

   function initializeComposeForm() {
        const minimizeBtn = document.getElementById('minimize-compose');
        const expandBtnHeader = document.getElementById('expand-compose-header');
        const cancelBtn = document.getElementById('cancel-compose');
        const minimizedIndicator = document.getElementById('minimized-indicator');
        const expandMinimizedBtn = document.querySelector('.expand-minimized-btn');

        if (minimizeBtn) {
            minimizeBtn.addEventListener('click', minimizeComposeForm);
        }

        if (expandBtnHeader) {
            expandBtnHeader.addEventListener('click', expandComposeForm);
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeComposeForm);
        }

        if (minimizedIndicator) {
            minimizedIndicator.addEventListener('click', expandComposeForm);
        }

        if (expandMinimizedBtn) {
            expandMinimizedBtn.addEventListener('click', expandComposeForm);
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

    function minimizeComposeForm() {
        const composeForm = document.getElementById('compose-form');
        const minimizedIndicator = document.getElementById('minimized-indicator');
        const minimizeBtn = document.getElementById('minimize-compose');
        const expandBtnHeader = document.getElementById('expand-compose-header');

        if (!composeForm) return;

        // Minimize the form
        composeForm.classList.add('minimized');
        
        // Hide minimize button, show expand button in header
        if (minimizeBtn) {
            minimizeBtn.style.display = 'none';
        }
        if (expandBtnHeader) {
            expandBtnHeader.style.display = 'block';
        }
        
        // Update minimized indicator with contact info
        const minimizedContact = document.getElementById('minimized-contact');
        if (minimizedIndicator && minimizedContact) {
            minimizedContact.textContent = currentName || currentEmail;
            minimizedIndicator.style.display = 'block';
        }
    }

    function expandComposeForm() {
        const composeForm = document.getElementById('compose-form');
        const minimizedIndicator = document.getElementById('minimized-indicator');
        const minimizeBtn = document.getElementById('minimize-compose');
        const expandBtnHeader = document.getElementById('expand-compose-header');

        if (!composeForm) return;

        // Expand the form
        composeForm.classList.remove('minimized');
        
        // Show minimize button, hide expand button in header
        if (minimizeBtn) {
            minimizeBtn.style.display = 'block';
        }
        if (expandBtnHeader) {
            expandBtnHeader.style.display = 'none';
        }
        
        // Hide the minimized indicator
        if (minimizedIndicator) {
            minimizedIndicator.style.display = 'none';
        }
    }

    function closeComposeForm() {
        const composeForm = document.getElementById('compose-form');
        const minimizedIndicator = document.getElementById('minimized-indicator');
        const minimizeBtn = document.getElementById('minimize-compose');
        const expandBtnHeader = document.getElementById('expand-compose-header');

        if (!composeForm) return;

        // Hide the entire form
        composeForm.style.display = 'none';
        
        // Reset button states
        if (minimizeBtn) {
            minimizeBtn.style.display = 'block';
        }
        if (expandBtnHeader) {
            expandBtnHeader.style.display = 'none';
        }
        
        // Also hide the minimized indicator
        if (minimizedIndicator) {
            minimizedIndicator.style.display = 'none';
        }
        
        // Clear form fields
        document.getElementById('subject').value = '';
        document.getElementById('message').value = '';
    }

    // ... rest of your existing JavaScript functions (fetchNewEmails, selectUser, selectEmail, updateChatHeader, loadConversation, etc.) remain the same ...

    async function fetchNewEmails() {
        console.log('ðŸ“§ Checking for new messages...');
        
        try {
            // If a conversation is open, refresh it with latest messages
            if (currentEmail) {
                await loadConversation(currentEmail);
                return;
            }

            // Otherwise fetch inbox overview
            const response = await fetch('<?php echo e(route('email.inbox')); ?>', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'include'
            });

            if (!response.ok) throw new Error('Failed to fetch inbox');
            const data = await response.json();
            console.log('Inbox data:', data);

            // Optionally refresh UI to show updated inbox
            // TODO: refresh inbox UI using existing functions

        } catch (err) {
            console.error('Error fetching inbox:', err);
        }
    }

    function selectUser(email, name) {
        currentEmail = email;
        currentName = name;
        updateChatHeader(name, email);
        loadConversation(email);
        showComposeForm();
        // Show refresh button and polling indicator
        document.getElementById('refresh-messages-btn').style.display = 'block';
        const pollingIndicator = document.getElementById('auto-polling-indicator');
        if (pollingIndicator) pollingIndicator.style.display = 'inline-block';
    }

    function selectEmail(email, name) {
        currentEmail = email;
        currentName = name || email;
        updateChatHeader(name || email, email);
        loadConversation(email);
        showComposeForm();
        // Show refresh button and polling indicator
        document.getElementById('refresh-messages-btn').style.display = 'block';
        const pollingIndicator = document.getElementById('auto-polling-indicator');
        if (pollingIndicator) pollingIndicator.style.display = 'inline-block';
    }

        function updateChatHeader(name, email) {
        document.getElementById('current-contact').textContent = name;
        document.getElementById('contact-email').textContent = email;
        document.getElementById('reply-to-email').value = email;
        
        // Show user context - Handle null currentUserEmail
        const contactEmailElement = document.getElementById('contact-email');
        if (currentUserEmail && currentUserEmail.trim() !== '') {
            contactEmailElement.innerHTML = `${email} <br><small>Your email: ${currentUserEmail}</small>`;
        } else {
            contactEmailElement.innerHTML = `${email}`;
        }
    }
    function showComposeForm() {
        const composeForm = document.getElementById('compose-form');
        const minimizedIndicator = document.getElementById('minimized-indicator');
        const minimizeBtn = document.getElementById('minimize-compose');
        const expandBtnHeader = document.getElementById('expand-compose-header');

        if (!composeForm) return;

        // Show the form and ensure it's not minimized
        composeForm.style.display = 'block';
        composeForm.classList.remove('minimized');
        
        // Reset button states: show minimize, hide expand
        if (minimizeBtn) {
            minimizeBtn.style.display = 'block';
        }
        if (expandBtnHeader) {
            expandBtnHeader.style.display = 'none';
        }
        
        // Hide minimized indicator
        if (minimizedIndicator) {
            minimizedIndicator.style.display = 'none';
        }
    }
    // ... rest of your existing functions (loadConversation, displayMessages, createMessageElement, etc.) ...

    async function loadConversation(email) {
        if (!email) {
            console.log('No email selected');
            return;
        }
        
        console.log(`Loading conversation for: ${email} (current user: ${currentUserEmail})`);
        showLoading(true);
        
        try {
           const baseConversationUrl = `<?php echo e(url('/email/conversation')); ?>`;
           const response = await fetch(`${baseConversationUrl}/${encodeURIComponent(email)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'include' // ADD THIS LINE
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server response:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Conversation data for user:', data);
            
            if (data.status === 'success' || data.success === true) {
                displayMessages(data);
            } else {
                throw new Error(data.message || data.error || 'Failed to load conversation');
            }
            
        } catch (error) {
            console.error('Error loading conversation:', error);
            showNotification(`âŒ Error loading conversation: ${error.message}`, 'error');
            
            // Show empty state
            const container = document.getElementById('messages-container');
            container.innerHTML = `
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <p>Error loading messages. Please try again.</p>
                    <button onclick="loadConversation('${email}')" style="padding: 5px 10px; background: #007bff; color: white; border: none; border-radius: 4px; margin-top: 10px;">
                        Retry
                    </button>
                </div>
            `;
        } finally {
            showLoading(false);
        }
    }

    function displayMessages(data) {

    alert(`Received ${data.conversation ? data.conversation.length : 0} messages. Check console for details.`);
    const container = document.getElementById('messages-container');
    
    console.log('Displaying messages with data:', data);
    
    if (!(data.status === 'success' || data.success === true) || !data.conversation || data.conversation.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #6c757d;">
                <p>No messages yet. Start a conversation!</p>
                <small>Messages between you and this contact will appear here.</small>
            </div>
        `;
        return;
    }

    container.innerHTML = '';
    
    // âœ… FIX: Sort messages by sort_timestamp (or use created_at if not available)
    // Normalize sort timestamps to numeric milliseconds to avoid string-based subtraction issues
    const messages = [...data.conversation].map(m => {
        const raw = m.sort_timestamp || m.created_at;
        const ms = typeof raw === 'number' ? raw : parseTimestamp(String(raw));
        return Object.assign({}, m, { _sortTime: ms || 0 });
    }).sort((a, b) => a._sortTime - b._sortTime);
    
    console.log(`Displaying ${messages.length} messages in chronological order:`);
    
    // Log each message for debugging
    messages.forEach((msg, index) => {
        console.log(`Message ${index}:`, {
            id: msg.id,
            sender_email: msg.sender_email,
            receiver_email: msg.receiver_email,
            message_type: msg.message_type,
            sender_role: msg.sender_role,
            subject: msg.subject,
            created_at_formatted: msg.created_at_formatted
        });
    });

    // Clear and rebuild with sorted messages
    messages.forEach((msg) => {
        const messageDiv = createMessageElement(msg);
        // ensure data-sort-time is numeric for DOM inspection
        if (msg._sortTime) messageDiv.setAttribute('data-sort-time', msg._sortTime);
        container.appendChild(messageDiv);
    });
    
    // Debug: log appended messages and container state
    try {
        console.log('Appended messages count:', container.children.length);
        const last = container.children[container.children.length - 1];
        const lastFive = [];
        for (let i = Math.max(0, container.children.length - 5); i < container.children.length; i++) {
            const ch = container.children[i];
            if (ch) lastFive.push(ch.getAttribute('data-message-id'));
        }
        console.log('Last 5 appended message ids:', lastFive);
        if (last) {
            console.log('Last appended message id:', last.getAttribute('data-message-id'), 'sort-time:', last.getAttribute('data-sort-time'));
            try {
                const st = window.getComputedStyle(last);
                console.log('Last computed style - display:', st.display, 'visibility:', st.visibility, 'opacity:', st.opacity);
                console.log('Last offsets - offsetHeight:', last.offsetHeight, 'clientHeight:', last.clientHeight, 'scrollHeight:', last.scrollHeight);
            } catch (e2) {
                console.warn('Could not read computed style for last element', e2);
            }
        }
        const containerStyle = window.getComputedStyle(container);
        console.log('Container computed style - height:', containerStyle.height, 'overflowY:', containerStyle.overflowY);

        // Temporary: highlight suspect message ID in DOM for debugging
        try {
            const suspectId = window.SUSPECT_MESSAGE_ID;
            if (suspectId) {
                const suspectEl = container.querySelector(`[data-message-id="${suspectId}"]`);
                if (suspectEl) {
                    suspectEl.classList.add('suspect-highlight');
                    // Bring it to center for inspection
                    try { suspectEl.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) { container.scrollTop = suspectEl.offsetTop; }
                    console.log('Highlighted suspect message in DOM:', suspectId);
                    // Remove highlight after a while to avoid permanent UI change
                    setTimeout(() => suspectEl.classList.remove('suspect-highlight'), 15000);
                } else {
                    console.log('Suspect message not found in DOM:', suspectId);
                }
            }
        } catch (e) {
            console.warn('Error while applying suspect highlight:', e);
        }
    } catch (e) {
        console.error('DisplayMessages debug error:', e);
    }

    // Auto-scroll to bottom (newest message)
    // Force a reflow then scroll the last INCOMING message into view (if any), otherwise scroll to bottom
    // This helps when appended elements are present in the DOM but not visible due to rendering quirks
    void container.offsetHeight; // force reflow
    const allChildren = Array.from(container.children);
    const lastIncoming = allChildren.slice().reverse().find(ch => ch.classList && ch.classList.contains('incoming'));
    if (lastIncoming) {
        try {
            lastIncoming.scrollIntoView({ behavior: 'smooth', block: 'end' });
        } catch (e) {
            container.scrollTop = container.scrollHeight;
        }
    } else {
        container.scrollTop = container.scrollHeight;
    }
}

    function parseTimestamp(timestamp) {
        if (!timestamp) return 0;
        
        try {
            // Handle different timestamp formats
            if (timestamp.includes(' ') && timestamp.includes(':')) {
                // Full datetime format
                return new Date(timestamp).getTime();
            } else if (timestamp.includes('-') && !timestamp.includes(':')) {
                // Date-only format
                return new Date(timestamp + 'T00:00:00').getTime();
            } else if (timestamp.includes(':') && !timestamp.includes('-')) {
                // Time-only format - combine with today's date
                const today = new Date().toISOString().split('T')[0];
                return new Date(today + 'T' + timestamp).getTime();
            }
        } catch (e) {
            console.error('Error parsing timestamp:', timestamp, e);
        }
        
        return 0;
    }

    function createMessageElement(msg) {
    const messageDiv = document.createElement('div');
    
    // FIXED: Properly determine if message is incoming or outgoing
    // For incoming emails, we need to check message_type and sender_role
    const isIncoming = msg.message_type === 'incoming' || 
                      msg.sender_role === 'email' || 
                      msg.sender_email !== currentUserEmail;
    
    const messageType = isIncoming ? 'incoming' : 'outgoing';
    const senderName = isIncoming ? 
        (msg.sender_name || msg.sender_email || 'Unknown Sender') : 
        'You';
    
    // DEBUG: Log the message data for debugging
    console.log('Creating message element:', {
        id: msg.id,
        sender_email: msg.sender_email,
        currentUserEmail: currentUserEmail,
        message_type: msg.message_type,
        sender_role: msg.sender_role,
        isIncoming: isIncoming
    });
    
    // Use server-formatted time for display
    const displayTime = msg.created_at_formatted || formatTimestampForDisplay(msg.created_at);
    
    messageDiv.className = `message ${messageType}`;
    messageDiv.setAttribute('data-message-id', msg.id);
    messageDiv.setAttribute('data-timestamp', msg.created_at);
    messageDiv.setAttribute('data-sort-time', msg.sort_timestamp || new Date(msg.created_at).getTime());
    
    messageDiv.innerHTML = `
        <div class="message-header">
            <span class="sender-name">${escapeHtml(senderName)}</span>
            <span class="timestamp">${displayTime}</span>
        </div>
        ${msg.subject && msg.subject !== 'No Subject' ? 
            `<div class="message-subject"><strong>${escapeHtml(msg.subject)}</strong></div>` : ''}
        <div class="message-text">${escapeHtml(msg.message || '')}</div>
        <div class="message-footer">
            <span class="message-type">${isIncoming ? 'ðŸ“§ Email' : 'ðŸ’¬ Sent'}</span>
            <span class="message-email">
                ${isIncoming ? 
                    `From: ${msg.sender_email}` : 
                    `To: ${msg.receiver_email}`}
            </span>
        </div>
    `;
    
    return messageDiv;
}

// Helper function to format timestamp for display if server didn't provide formatted version
function formatTimestampForDisplay(timestamp) {
    if (!timestamp) return 'Unknown time';
    
    try {
        const date = new Date(timestamp);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    } catch (e) {
        console.error('Error formatting timestamp:', timestamp, e);
        return 'Invalid time';
    }
}

    //  function to handle conversation refresh
    async function refreshConversation() {
        if (!currentEmail) {
            showNotification('Please select a conversation first', 'warning');
            return;
        }
        
        console.log(`Refreshing conversation for: ${currentEmail}`);
        
        // Show loading state
        const refreshBtn = document.getElementById('refresh-messages-btn');
        const originalText = refreshBtn.innerHTML;
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
        refreshBtn.disabled = true;
        
        try {
            // First, fetch new emails to sync with Gmail
            await fetchNewEmails();
            
            // Then reload the conversation
            await loadConversation(currentEmail);
            
            showNotification('âœ… Conversation refreshed successfully', 'success');
            
        } catch (error) {
            console.error('Error refreshing conversation:', error);
            showNotification('âŒ Failed to refresh conversation', 'error');
        } finally {
            // Restore button state
            refreshBtn.innerHTML = originalText;
            refreshBtn.disabled = false;
        }
    }

    // Bind refresh button to the refreshConversation function when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        const refreshBtn = document.getElementById('refresh-messages-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function(e) {
                e.preventDefault();
                refreshConversation();
            });
        }
    });

    // function to check for new messages without full reload
    async function checkForNewMessages() {
        if (!currentEmail) return;
        
        try {
            const baseCheckNewUrl = `<?php echo e(url('/email/conversation')); ?>`;
            const response = await fetch(`${baseCheckNewUrl}/${encodeURIComponent(currentEmail)}/check-new`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'include'
            });
            
                if (response.ok) {
                const data = await response.json();
                if ((data.status === 'success' || data.success === true) && data.has_new_messages) {
                    // If there are new messages, show a notification
                    showNotification(`ðŸ“§ ${data.message_count} new message(s) found. Click refresh to load them.`, 'info');
                }
            }
        } catch (error) {
            console.log('Check for new messages error:', error);
        }
    }

    // Poll latest message endpoint and append if new (no full reload)
    async function pollLatestMessage() {
        if (!currentEmail) return;
        try {
            const latestUrl = `<?php echo e(url('/email/latest')); ?>/${encodeURIComponent(currentEmail)}?sync=0`;
            const resp = await fetch(latestUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'include'
            });
            if (!resp.ok) return;
            const j = await resp.json();
            if (!(j.status === 'success' && j.latest)) {
                // If no latest was returned, request a forced sync once and re-query
                try {
                    const forceUrl = latestUrl + '&sync=1';
                    const forceResp = await fetch(forceUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        credentials: 'include'
                    });
                    if (!forceResp.ok) return;
                    const fj = await forceResp.json();
                    if (!(fj.status === 'success' && fj.latest)) return;
                    // use the freshly-synced latest
                    j.latest = fj.latest;
                } catch (e2) {
                    console.warn('Forced sync failed', e2);
                    return;
                }
            }
            const latest = j.latest;

            // If latest message id is not present in DOM, append it
            const container = document.getElementById('messages-container');
            if (!container) return;
            if (container.querySelector(`[data-message-id="${latest.id}"]`)) {
                // already present
                return;
            }

            console.log('Auto-appending latest message:', latest.id);
            // Create element and append
            const el = createMessageElement(latest);
            // set sort-time attribute
            if (latest.sort_timestamp) el.setAttribute('data-sort-time', latest.sort_timestamp);
            container.appendChild(el);

            // Re-sort children by numeric data-sort-time to keep chronological order
            const children = Array.from(container.children);
            children.sort((a,b) => (parseInt(a.getAttribute('data-sort-time')||0) - parseInt(b.getAttribute('data-sort-time')||0)));
            children.forEach(c => container.appendChild(c));

            // Highlight briefly and scroll into view
            el.classList.add('suspect-highlight');
            try { el.scrollIntoView({ behavior: 'smooth', block: 'end' }); } catch(e){ container.scrollTop = container.scrollHeight; }
            setTimeout(()=>el.classList.remove('suspect-highlight'), 8000);

            // Show notification that new message arrived
            const isOutgoing = latest.message_type === 'outgoing';
            showNotification(`âœ‰ï¸ ${isOutgoing ? 'Message sent' : 'New message'} from ${latest.sender_name || latest.sender_email}`, 'success');

        } catch (e) {
            console.warn('pollLatestMessage error', e);
        }
    }

    function showLoading(show) {
        const container = document.getElementById('messages-container');
        if (show) {
            container.innerHTML = `
                <div style="text-align: center; padding: 20px; color: #6c757d;">
                    <div class="spinner-border spinner-border-sm" role="status"></div>
                    Loading messages...
                </div>
            `;
        }
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        `;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    // Utility functions
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // New Email Modal Functions
    function startNewEmail() {
        document.getElementById('new-email-modal').style.display = 'block';
    }

    function closeNewEmail() {
        document.getElementById('new-email-modal').style.display = 'none';
        document.getElementById('new-email-form').reset();
    }

    // Form submission handlers
    document.getElementById('new-email-form').addEventListener('submit', function(e) {
        e.preventDefault();
        sendEmail(
            document.getElementById('new-to-email').value,
            document.getElementById('new-subject').value,
            document.getElementById('new-message').value,
            true // isNewEmail
        );
    });

    document.getElementById('reply-form').addEventListener('submit', function(e) {
        e.preventDefault();
        if (!currentEmail) {
            showNotification('Please select a contact first', 'warning');
            return;
        }
        
        sendEmail(
            currentEmail,
            document.getElementById('subject').value,
            document.getElementById('message').value,
            false // isReply
        );
    });

    async function sendEmail(toEmail, subject, message, isNewEmail = false) {
        const formData = new FormData();
        formData.append('to_email', toEmail);
        formData.append('subject', subject);
        formData.append('message', message);
        formData.append('_token', document.querySelector('input[name="_token"]').value);

        // Attach files from either compose or reply form
        try {
            const fileInput = isNewEmail ? document.getElementById('new-attachments') : document.getElementById('reply-attachments');
            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                for (let i = 0; i < fileInput.files.length; i++) {
                    formData.append('attachments[]', fileInput.files[i]);
                }
            }
        } catch (e) {
            console.warn('Failed to append attachments', e);
        }
        
        try {
            showNotification('Sending email...', 'info');
            
            const response = await fetch('<?php echo e(route('email.send.chat')); ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'include' // ADD THIS LINE
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server error:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const contentType = response.headers.get('content-type');
            let data;
            
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                throw new Error(`Server returned: ${text.substring(0, 100)}`);
            }

            if (data.status === 'success' || data.success === true) {
                showNotification('âœ… Email sent successfully!', 'success');
                
                if (isNewEmail) {
                    closeNewEmail();
                } else {
                    document.getElementById('message').value = '';
                    // Don't clear subject for replies to maintain conversation context
                }
                
                if (toEmail === currentEmail) {
                    setTimeout(() => loadConversation(currentEmail), 1000);
                }
            } else {
                showNotification(`âŒ ${data.message || data.error || 'Failed to send email'}`, 'error');
            }
        } catch (error) {
            console.error('Error sending email:', error);
            showNotification(`âŒ Error sending email: ${error.message}`, 'error');
        }
    }

    // Utility functions
    // (escapeHtml already defined above; avoid duplicate definitions)

function formatPhoneForDisplay(phone) {
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

// Optional: Add keyboard shortcut (Ctrl+Q) for logout
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'q') {
        e.preventDefault();
        // Use Bootstrap's modal directly
        const logoutModal = new bootstrap.Modal(document.getElementById('logoutConfirmationModal'));
        logoutModal.show();
    }
});
// Simplified logout modal function without aria issues
function showLogoutModal() {
    // Create modal instance
    const modalElement = document.getElementById('logoutConfirmationModal');
    
    // Remove any aria-hidden attributes that might conflict
    modalElement.removeAttribute('aria-hidden');
    modalElement.setAttribute('aria-modal', 'true');
    
    // Use Bootstrap's modal properly
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: true,
        focus: true
    });
    
    // Show modal
    modal.show();
    
    // Listen for modal events to fix aria attributes
    modalElement.addEventListener('shown.bs.modal', function() {
        // Ensure proper accessibility
        this.removeAttribute('aria-hidden');
        this.setAttribute('aria-modal', 'true');
        
        // Focus on the cancel button
        setTimeout(() => {
            const cancelBtn = this.querySelector('.btn-secondary');
            if (cancelBtn) {
                cancelBtn.focus();
            }
        }, 100);
    });
    
    modalElement.addEventListener('hidden.bs.modal', function() {
        // When hidden, let Bootstrap handle aria-hidden
        this.removeAttribute('aria-modal');
    });
}

// Keyboard shortcut - use the button click instead of direct modal
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'q') {
        e.preventDefault();
        // Find and click the logout button
        const logoutBtn = document.querySelector('.logout-btn[onclick*="showLogoutModal"]');
        if (logoutBtn) {
            logoutBtn.click();
        } else {
            // Fallback to calling the function directly
            showLogoutModal();
        }
    }
});
</script>
<?php echo $__env->make('partials.notification-badge-visibility', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>
</html><?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\email-chat.blade.php ENDPATH**/ ?>