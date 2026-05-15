<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="user-id" content="<?php echo e(Auth::id()); ?>">
    <meta name="user-role" content="<?php echo e(Auth::user()->role ?? ''); ?>">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <title>Admin Dashboard</title>
    
    <!-- Remove the Tailwind CDN and use only Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    
    <?php echo $__env->make('partials.global-error-handler', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
    <link rel="stylesheet" href="<?php echo e(asset('css/staff/dashboardStaff.blade.css')); ?>">

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
                <a href="<?php echo e(route('dashboardStaff')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('dashboardStaff') ? 'active' : ''); ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="<?php echo e(route('staff')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('staff') ? 'active' : ''); ?>">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Set Time</span>
                </a>
                
                <a href="<?php echo e(route('staff.walkins.logs')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('staff.walkins.logs') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-clipboard" style="color: #cdd3df;"></i>
                    <span>Walk-ins logs</span>
                </a>
                
                <a href="<?php echo e(route('staff.feedback.reports')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('staff.feedback.reports') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-comments" style="color: #d7dae0;"></i>
                    <span>Feedbacks</span>
                </a>
                
                <a href="<?php echo e(route('staff.clients.pending')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('staff.clients.pending') ? 'active' : ''); ?>">
                    <i class="fas fa-clock"></i>
                    <span>Pending Requests</span>
                </a>
                
                <a href="<?php echo e(route('staff.acceptedRequests')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('staff.acceptedRequests') ? 'active' : ''); ?>">
                    <i class="fas fa-check-circle"></i>
                    <span>Accepted Requests</span>
                </a>
                <a href="<?php echo e(route('staff.deniedRequests')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('staff.deniedRequests') ? 'active' : ''); ?>">
                    <i class="fas fa-times-circle"></i>
                    <span>Denied Requests</span>
                </a>
                <a href="<?php echo e(route('diffun.message.inquiries')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('diffun.message.inquiries') ? 'active' : ''); ?>">
                    <i class="fas fa-envelope"></i>
                    <span>Message Inquiries</span>
                </a>

                <a href="<?php echo e(route('staff.account.settings')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('staff.account.settings') ? 'active' : ''); ?>">
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
                -->

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
                            <a href="<?php echo e(route('clientstbl')); ?>" class="btn btn-sm btn-primary w-100">
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
                    
                </div>-->
                <!-- Log Out -->
                <form id="logout-form" action="<?php echo e(route('custom.logout')); ?>" method="POST" style="display: none;">
                    <?php echo csrf_field(); ?>
                </form>
                <button type="button" class="btn logout-btn" onclick="showLogoutModal()">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>
            
            <!-- Rest of your dashboard content remains the same -->
            <div class="dashboard-container">
                <h1 class="dashboard-title">Appointment Analytics Dashboard</h1>
                
                <div class="stats-container">
                    <!-- Total Appointments Card -->
                    <div class="stat-card card-total">
                        <div class="stat-header">
                            <div class="stat-title">Total Appointments</div>
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo e($totalAppointments); ?></div>
                        <div class="stat-footer">
                            <span class="stat-trend">
                                <i class="fas fa-chart-line"></i> All Time
                            </span>
                        </div>
                    </div>
                    
                    <!-- Pending Appointments Card - Clickable -->
                    <div class="stat-card card-pending clickable-card" onclick="window.location.href='<?php echo e(route('staff.clients.pending')); ?>'">
                        <div class="stat-header">
                            <div class="stat-title">Pending Requests</div>
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo e($pendingAppointments); ?></div>
                        <div class="stat-footer">
                            <span class="stat-trend">
                                <i class="fas fa-user-clock"></i> Awaiting Review
                            </span>
                        </div>
                    </div>
                    
                    <!-- Approved Appointments Card - Clickable -->
                    <div class="stat-card card-approved clickable-card" onclick="window.location.href='<?php echo e(route('staff.acceptedRequests')); ?>'">
                        <div class="stat-header">
                            <div class="stat-title">Approved Requests</div>
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo e($approvedAppointments); ?></div>
                        <div class="stat-footer">
                            <span class="stat-trend">
                                <i class="fas fa-calendar-plus"></i> Confirmed
                            </span>
                        </div>
                    </div>
                    
                    <!-- Denied Appointments Card - Clickable -->
                    <div class="stat-card card-denied clickable-card" onclick="window.location.href='<?php echo e(route('staff.deniedRequests')); ?>'">
                        <div class="stat-header">
                            <div class="stat-title">Denied Requests</div>
                            <div class="stat-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo e($deniedAppointments); ?></div>
                        <div class="stat-footer">
                            <span class="stat-trend">
                                <i class="fas fa-ban"></i> Not Approved
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity Section -->
                <div class="recent-activity">
                    <h2 class="activity-title">Recent Appointment Requests</h2>
                    <ul class="activity-list">
                        <?php $__currentLoopData = $recentAppointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(isset($appointment->selected_branch) && trim($appointment->selected_branch) === 'Diffun Branch Office'): ?>
                            <li class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="activity-details">
                                    <div class="activity-name"><?php echo e($appointment->fullname); ?></div>
                                    <div class="activity-time"><?php echo e($appointment->created_at->format('M d, Y h:i A')); ?></div>
                                    <div class="activity-branch" style="font-size:12px;color:#666"><?php echo e($appointment->selected_branch); ?></div>
                                </div>
                                <span class="activity-status status-<?php echo e($appointment->appointment_approval); ?>">
                                    <?php echo e(ucfirst($appointment->appointment_approval)); ?>

                                </span>
                            </li>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>
    </div><!-- Bootstrap Modal for Logout Confirmation -->
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
        // Ensure user data is available globally
        window.currentUser = {
            id: <?php echo e(Auth::id()); ?>,
            role: '<?php echo e(Auth::user()->role ?? 'unknown'); ?>'
        };
        
        // Store in localStorage as well for cross-tab communication
        try {
            localStorage.setItem('currentUserId', window.currentUser.id);
        } catch (e) {
            console.warn('Could not store user ID in localStorage');
        }
    </script>

 <script src="<?php echo e(asset('js/staff/dashboardStaff.js')); ?>"></script>
    <script src="<?php echo e(asset('js/staff/diffunNotifications.js')); ?>"></script>
  <script>
    console.log("Current route: <?php echo e(Request::path()); ?>");
    // Check if there are any dynamic elements that might be trying to use dashboardStaff.page
    document.addEventListener('DOMContentLoaded', function() {
        const allElements = document.querySelectorAll('*');
        allElements.forEach(el => {
            if (el.outerHTML.includes('dashboardStaff.page')) {
                console.log('Found reference to dashboardStaff.page in:', el);
            }
        });
    });
</script>
</body>
</html><?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\staff\dashboardStaff.blade.php ENDPATH**/ ?>