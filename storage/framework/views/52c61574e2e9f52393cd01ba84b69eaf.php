<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <title>Appointments Management</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    
    <?php echo $__env->make('partials.global-error-handler', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
    <link rel="stylesheet" href="<?php echo e(asset('css/fetch-appointments.css')); ?>">
    
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">
                <div class="close-btn" style="margin-left:10px; margin-bottom:6px; display:none;">
                    <button class="sidebar-close-btn" id="sidebar-close-btn" title="Close sidebar">&times;</button>
                </div>
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
                <!--<a href="<?php echo e(url('/messages')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('messages') ? 'active' : ''); ?>">
                    <i class="fas fa-comments"></i>
                    <span>Messages</span>
                </a>-->
                <a href="#requestsSubmenu" class="list-group-item list-group-item-action" data-bs-toggle="collapse" aria-expanded="false">
                    <i class="fas fa-list-alt"></i>
                    <span>Appointment Requests</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu collapse list-group <?php echo e(request()->is('clientstbl') || request()->is('adminAcceptedRequest') || request()->is('adminDeniedRequest') ? 'show' : ''); ?>" id="requestsSubmenu">
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
                <button class="btn btn-primary" id="menu-toggle">
                    <i class="fas fa-bars"></i> 
                </button>
                
                <div class="top-bar-spacer"></div>
                
                <!-- Notification Dropdown (Same as administrator.blade.php) -->
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

            <div class="dashboard-container">
                <!-- Header -->
                <div class="page-header d-flex justify-content-between align-items-center">
                    <div>
                        <h1>Logs Requests</h1>
                        <p>Access and manage all appointment logs entries and review, filter, and maintain your logs records with ease.</p>
                    </div>

                    <div>
                        <!-- Right-aligned buttons (moved from filters); keep same IDs so JS continues to work -->
                        <div class="btn-group" role="group" aria-label="Backup and refresh">
                            <button id="saveExcelBtn" class="btn btn-success me-2">
                                <i class="fas fa-file-excel me-2"></i>
                            </button>
                            <button id="saveBackupBtn" class="btn btn-danger me-2">
                                <i class="fas fa-file-pdf me-2"></i>
                            </button>
                            <button id="viewBackupLogsBtn" class="btn btn-info me-2">
                                <i class="fas fa-history me-2"></i>
                            </button>
                            <button id="refreshBtn" class="btn btn-secondary">
                                <i class="fas fa-refresh me-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Feedback summary removed as requested -->

                <!-- Filters and Search -->
                <div class="filter-section">
                    <div class="row align-items-end">
                            <div class="col-md-3 mb-3">
                                <label for="categoryFilter">Filter by Category</label>
                                <select id="categoryFilter" class="form-control">
                                    <option value="all">All Categories</option>
                                    <!-- Options populated dynamically -->
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="caseNameFilter">Filter by Case Name</label>
                                <select id="caseNameFilter" class="form-control">
                                    <option value="all">All Case Names</option>
                                    <!-- Options populated dynamically -->
                                </select>
                            </div>
                            <!--<div class="col-md-2 mb-3">
                                <label for="branchFilter">Filter by Branch</label>
                                <select id="branchFilter" class="form-control">
                                    <option value="all">All Branches</option>
                                    <option value="Cordon Branch Office">Cordon Branch Office</option>
                                    <option value="Diffun Branch Office">Diffun Branch Office</option>
                                </select>
                            </div>-->
                            <div class="col-md-2 mb-3">
                                <label for="statusFilter">Filter by Status</label>
                                <select id="statusFilter" class="form-control">
                                    <option value="all">All Appointments</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved" selected>Approved</option>
                                    <option value="denied">Denied</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="searchInput">Search</label>
                                <div class="search-container">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" id="searchInput" placeholder="Search appointments..." class="form-control search-input">
                                </div>
                            </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-container">
                    <div class="appointments-table">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <!--<th>ID</th>-->
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Category</th>
                                        <th>Case Name</th>
                                        <!--<th>Branch</th>-->  
                                        <th>Date & Time</th>
                                        <th>Status</th>
                                        <th>Approved By</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="appointmentsTable">
                                    <!-- Data will be loaded here via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Loading State -->
                        <div id="loadingState" class="text-center py-8">
                            <div class="loading-spinner"></div>
                            <p class="text-muted mt-2">Loading appointments...</p>
                        </div>
                        
                        <!-- Empty State -->
                        <div id="emptyState" class="text-center py-8 d-none">
                            <i class="fas fa-calendar-times text-4xl text-muted mb-4"></i>
                            <p class="text-muted text-lg">No appointments found</p>
                            <p class="text-muted">Try changing your filters or search terms</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="appointmentModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Appointment Details</h5>
                </div>
                <div class="modal-body">
                    <div id="modalContent">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Toast -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                PDF backup saved successfully!
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
        <!-- Backup Logs Modal -->
        <div class="modal fade" id="backupLogsModal" tabindex="-1" aria-labelledby="backupLogsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="backupLogsModalLabel">Backup Logs & Preview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs" id="backupLogsTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="backup-list-tab" data-bs-toggle="tab" data-bs-target="#backup-list" type="button" role="tab" aria-controls="backup-list" aria-selected="true">
                                    <i class="fas fa-list me-2"></i>Backup Logs
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pdf-preview-tab" data-bs-toggle="tab" data-bs-target="#pdf-preview" type="button" role="tab" aria-controls="pdf-preview" aria-selected="false">
                                    <i class="fas fa-file-pdf me-2"></i>PDF Preview
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="csv-preview-tab" data-bs-toggle="tab" data-bs-target="#csv-preview" type="button" role="tab" aria-controls="csv-preview" aria-selected="false">
                                    <i class="fas fa-file-excel me-2"></i>CSV Preview
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content p-3" id="backupLogsTabContent">
                            <!-- Backup List Tab -->
                            <div class="tab-pane fade show active" id="backup-list" role="tabpanel" aria-labelledby="backup-list-tab">
                                <div id="backupLogsContainer" class="backup-logs-container">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading backup logs...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Loading backup logs...</p>
                                    </div>
                                </div>
                            </div>

                           <!-- PDF Preview Tab -->
                            <div class="tab-pane fade" id="pdf-preview" role="tabpanel" aria-labelledby="pdf-preview-tab">
                                <div class="pdf-preview-container">
                                    <div id="pdfLoading" class="alert alert-info d-none">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        Loading PDF preview...
                                    </div>
                                    <div id="pdfError" class="alert alert-danger d-none">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <span id="pdfErrorMessage">Failed to load PDF preview.</span>
                                    </div>
                                    <iframe id="pdfPreviewFrame" 
                                            style="width: 100%; height: 600px; border: 1px solid #dee2e6; border-radius: 4px;"
                                            onload="document.getElementById('pdfLoading').classList.add('d-none');"
                                            onerror="document.getElementById('pdfError').classList.remove('d-none');"></iframe>
                                </div>
                            </div>

                            <!-- CSV Preview Tab -->
                            <div class="tab-pane fade" id="csv-preview" role="tabpanel" aria-labelledby="csv-preview-tab">
                                <div class="csv-preview-container">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Select a CSV backup from the "Backup Logs" tab to preview it here.
                                    </div>
                                    <div class="table-responsive">
                                        <table id="csvPreviewTable" class="table table-sm table-bordered table-hover">
                                            <thead id="csvPreviewTableHead">
                                                <!-- CSV headers will be loaded here -->
                                            </thead>
                                            <tbody id="csvPreviewTableBody">
                                                <!-- CSV rows will be loaded here -->
                                            </tbody>
                                        </table>
                                        <div id="csvPagination" class="d-none">
                                            <nav>
                                                <ul class="pagination justify-content-center">
                                                    <li class="page-item"><a class="page-link" href="#" data-page="prev">Previous</a></li>
                                                    <li class="page-item"><a class="page-link" href="#" data-page="next">Next</a></li>
                                                </ul>
                                            </nav>
                                            <p class="text-center text-muted" id="csvPageInfo"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>



    <script>
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

    // Global notification functions
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
            
            html += `
                <div class="notification-item ${isUnread ? 'unread' : ''}" 
                     data-id="${notification.id}" 
                     onclick="markNotificationAsRead(${notification.id}, this)">
                    <div class="notification-icon">
                        <i class="fas fa-calendar-plus"></i>
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
                                    onclick="event.stopPropagation(); window.location.href='<?php echo e(route('clientstbl')); ?>'">
                                <i class="fas fa-external-link-alt"></i> See More
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

    // Simplified logout modal function without aria issues
    function showLogoutModal() {
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
                showLogoutModal();
            }
        }
    });

    // Global error handler for better debugging
    window.addEventListener('error', function(e) {
        console.error('Global error caught:', e.message, 'at', e.filename, 'line', e.lineno);
    });

    // Add to the <script> section in fetch-appointments.blade.php
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing notification system...');
        
        // Simple sidebar toggle
        const menuToggle = document.getElementById('menu-toggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                document.getElementById('wrapper').classList.toggle('toggled');
            });
        }
        
        // Notification System Setup
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
    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar close button (mobile)
        const sidebarCloseBtn = document.getElementById('sidebar-close-btn');
        if (sidebarCloseBtn) {
            sidebarCloseBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const wrapper = document.getElementById('wrapper');
                if (wrapper) wrapper.classList.remove('toggled');
                document.body.classList.remove('sidebar-open');
            });
        }

        // Close sidebar when clicking outside on mobile (safe, does not touch menu-toggle)
        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('wrapper');
            const sidebar = document.getElementById('sidebar-wrapper');
            const menuToggle = document.getElementById('menu-toggle');

            if (window.innerWidth <= 640 && wrapper && wrapper.classList.contains('toggled') && sidebar && !sidebar.contains(e.target) && (!menuToggle || !menuToggle.contains(e.target)) ) {
                wrapper.classList.remove('toggled');
                document.body.classList.remove('sidebar-open');
            }
        });
    });
    </script>
    <?php echo $__env->make('partials.notification-badge-visibility', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <script src="<?php echo e(asset('js/fetch-appointments.js')); ?>"></script>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\fetch-appointments.blade.php ENDPATH**/ ?>