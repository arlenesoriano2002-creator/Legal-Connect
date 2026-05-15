<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <title>Admin Walk-ins Logs</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

     <link rel="stylesheet" href="<?php echo e(asset('css/adminWalkInLogs.css')); ?>">
    <style>
        .dataTables_info {
            padding-left: 15px !important;
        }
        
        /* Fix for modal z-index issues */
         .modal-backdrop {
            z-index: 1040;
        }
        
        .modal {
            z-index: 1050;
        }
        
        #deleteConfirmationModal .modal-container {
            background-color: #fff !important;
            border: 1px solid rgba(0, 0, 0, 0.2) !important;
            border-radius: 0.3rem !important;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.5) !important;
            display: block !important;
            flex-direction: column !important;
            pointer-events: auto !important;
            position: relative !important;
            width: 100% !important;
            max-width: 500px !important;
        }
        
        #deleteConfirmationModal .title-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 1rem !important;
            border-bottom: 1px solid #dee2e6 !important;
        }
        
        #deleteConfirmationModal .content-modal {
            padding: 1rem;
        }
        
        #deleteConfirmationModal .modal-footer {
            justify-content: flex-end !important;
            padding: 0.75rem;
            border-top: 1px solid #dee2e6;
        }

        /* Notification badge alignment */
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
</head>
<body>
    <!-- Logout functions script - defined early for immediate availability -->
    <script>
        // ===== LOGOUT FUNCTIONS =====
        function showLogoutModal() {
            console.log('showLogoutModal called');
            const modalEl = document.getElementById('logoutConfirmationModal');
            console.log('modalEl:', modalEl);
            if (modalEl) {
                const bsModal = new bootstrap.Modal(modalEl, {
                    backdrop: 'static',
                    keyboard: false
                });
                bsModal.show();
                console.log('Modal shown');
            } else {
                console.error('logoutConfirmationModal not found');
            }
        }

        function confirmLogout() {
            console.log('confirmLogout called');
            const form = document.getElementById('logout-form');
            console.log('form:', form);
            if (form) {
                form.submit();
            } else {
                console.error('logout-form not found');
            }
        }
    </script>

    <!-- Bootstrap JS - loaded early for modal functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <div id="wrapper">
        <!-- Sidebar (reuse from existing staff template) -->
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

                <!-- Notification Dropdown -->
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
                <button type="button" class="btn logout-btn" onclick="showLogoutModal()">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>

            <div class="dashboard-container">
                <div class="container-fluid py-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3 mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>Combined Walk-in Logs
                        </h1>
                        <div class="d-flex gap-2">
                            <!-- Export and backup buttons -->
                            <button type="button" class="btn btn-primary" id="saveExcelBtn">
                                <i class="fas fa-file-excel me-1"></i> Save as Excel
                            </button>
                            <button type="button" class="btn btn-danger" id="savePdfBtn">
                                <i class="fas fa-file-pdf me-1"></i> Save as PDF
                            </button>
                            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#backupLogsModal">
                                <i class="fas fa-archive me-1"></i> View Backup Logs
                            </button>
                        </div>
                    </div>

                    <!-- Search Filter Only -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="filter-group">
                                        <label class="form-label"><strong>Search</strong></label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-search"></i>
                                            </span>
                                            <input type="text" class="form-control" id="searchInput" placeholder="Search walk-ins...">
                                            <button class="btn btn-outline-primary" type="button" id="refreshButton" title="Refresh Table">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>

                    <!-- Combined Table -->
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover" id="walkinsTable">
                                    <thead>
                                        <tr>
                                            <th>FULL NAME</th>
                                            <th>ADDRESS</th>
                                            <th>CONTACT</th>
                                            <th>PURPOSE</th>
                                            
                                            <th>DATE & TIME</th>
                                            <th>CREATED</th>
                                            
                                            <th>ACTIONS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(isset($walkins) && count($walkins) > 0): ?>
                                            <?php $__currentLoopData = $walkins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $walkin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($walkin->fullname); ?></td>
                                                <td><?php echo e($walkin->address); ?></td>
                                                <td><?php echo e($walkin->contact_number ?? 'N/A'); ?></td>
                                                <td><?php echo e($walkin->purpose); ?></td>
                                                
                                                <td>
                                                    <?php if($walkin->date_time): ?>
                                                        <?php echo e(date('Y-m-d g:i A', strtotime($walkin->date_time))); ?>

                                                    <?php else: ?>
                                                        N/A
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo e(date('m/d/Y', strtotime($walkin->created_at))); ?></td>
                                                
                                                <td>
                                                    <button class="btn btn-sm btn-danger delete-walkin-btn" 
                                                            data-id="<?php echo e($walkin->id); ?>" 
                                                            data-name="<?php echo e($walkin->fullname); ?>"
                                                            data-source="<?php echo e($walkin->source); ?>">
                                                        <i class="fas fa-trash-alt"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle"></i> No walk-in records found.
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
    </div>

<!-- Backup Logs Modal (copied from original) -->
<div class="modal fade" id="backupLogsModal" tabindex="-1" aria-labelledby="backupLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="backupLogsModalLabel">
                    <i class="fas fa-history me-2"></i> Backup Logs
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Filename</th>
                                <th>Date Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="backupLogsList">
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="fas fa-folder-open fa-2x mb-3"></i><br>
                                    No backup files found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal for Walk-in Records -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-container">
            <div class="title-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <center>
                <div class="content-modal">
                    <div style="font-size: 48px; color: #dc3545; margin-bottom: 15px;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                   
                    <h4 class="mb-3" id="deleteModalTitle">Confirm Delete</h4>
                    <p>Are you sure you want to delete this walk-in record?<br>This action cannot be undone.</p>
                    
                    <div class="confirmation-details mt-3" style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: left; max-width: 80%; margin: 0 auto;">
                        <p style="margin: 5px 0; font-size: 14px;"><strong>Client:</strong> <span id="deleteFileName">N/A</span></p>
                        <p style="margin: 5px 0; font-size: 14px;"><strong>Source:</strong> <span id="deleteSource">N/A</span></p>
                    </div>
                </div>
            </center>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- File Preview Modal -->
<div class="modal fade" id="backupPreviewModal" tabindex="-1" aria-labelledby="backupPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="backupPreviewModalLabel">
                    <i class="fas fa-file-alt me-2"></i> File Preview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Loading State -->
                <div id="previewLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading file preview...</p>
                </div>

                <div class="container-fluid h-100">
                    <div class="row h-100">
                        <!-- Main Content Area (80% width) -->
                        <div class="col-lg-10 col-md-9 p-0">
                            <!-- PDF Preview Section -->
                            <div id="pdfPreviewSection" style="display: none; height: 100%; overflow: auto;">
                                <div class="h-100 w-100">
                                    <iframe id="pdfPreviewFrame" class="w-100 h-100" style="border: none;"></iframe>
                                </div>
                            </div>

                            <!-- Excel Preview Section -->
                            <div id="excelPreviewSection" style="display: none; height: 100%; overflow: auto;">
                                <div class="p-4">
                                    <div class="table-responsive">
                                        <table id="excelPreviewTable" class="table table-bordered table-striped table-hover">
                                            <!-- Excel content will be inserted here -->
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- CSV Preview Section -->
                            <div id="csvPreviewSection" style="display: none; height: 100%; overflow: auto;">
                                <div class="p-4">
                                    <div class="table-responsive">
                                        <table id="csvPreviewTable" class="table table-bordered table-striped table-hover">
                                            <!-- CSV content will be inserted here -->
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Sidebar (20% width) -->
                        <div class="col-lg-2 col-md-3 p-4" style="background-color: #f8f9fa; border-left: 1px solid #dee2e6; overflow-y: auto;">
                            <div id="fileInfo" style="display: none;">
                                <h4 class="mb-3">
                                    <i class="fas fa-file me-2"></i> 
                                    <span id="backupFileName" class="text-truncate d-block fs-5 fw-bold">File</span>
                                </h4>
                                
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">
                                                <i class="fas fa-calendar me-2"></i> Created Date
                                            </h6>
                                            <p class="mb-0 fs-6 fw-semibold" id="backupFileDate">â€”</p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">
                                                <i class="fas fa-file-alt me-2"></i> File Type
                                            </h6>
                                            <p class="mb-0 fs-6 fw-semibold" id="backupFileType">â€”</p>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <h6 class="text-muted mb-1">
                                                <i class="fas fa-hashtag me-2"></i> File Size
                                            </h6>
                                            <p class="mb-0 fs-6 fw-semibold" id="backupFileSize">â€”</p>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <a id="downloadPreviewBtn" href="#" class="btn btn-success btn-sm">
                                                <i class="fas fa-download me-2"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="previewError" style="display: none;">
                                <div class="alert alert-danger" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <span id="previewErrorMessage">Error loading file preview</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="logoutModalLabel">
                    <i class="fas fa-sign-out-alt me-2"></i>Confirm Logout
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <center>
                    <div class="content-modal">
                        <div style="font-size: 48px; color: #ffc107; margin-bottom: 15px;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                       
                        <h4 class="mb-3">Confirm Logout</h4>
                        <p>Are you sure you want to log out?<br>You will be redirected to the login page.</p>
                    </div>
                </center>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmLogout()">Logout</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery (required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>

<script>
    // ===== NOTIFICATION SYSTEM =====
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

// Mark notification as read
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
function refreshNotifications() {
    loadNotifications();
}

// Utility function for escaping HTML (add if not already present)
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Initialize notification system when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Existing initialization code...
    
    // Initialize notification system
    initializeNotificationSystem();
    
    // Existing code continues...
});

// Sidebar toggle
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar-wrapper');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('menu-toggle');
    if (toggle) {
        toggle.addEventListener('click', toggleSidebar);
    }
});

    (function() {
        'use strict';

        function showToast(type, title, message) {
            try {
                var toastContainer = document.getElementById('toastContainer');
                if (!toastContainer) {
                    toastContainer = document.createElement('div');
                    toastContainer.id = 'toastContainer';
                    toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                    toastContainer.style.zIndex = '9999';
                    document.body.appendChild(toastContainer);
                }

                var toastEl = document.createElement('div');
                toastEl.className = 'toast align-items-center text-bg-' + (type || 'info') + ' border-0';
                toastEl.setAttribute('role', 'alert');
                toastEl.setAttribute('aria-live', 'assertive');
                toastEl.setAttribute('aria-atomic', 'true');
                toastEl.innerHTML = '<div class="d-flex"><div class="toast-body"><strong>' + (title || '') + ':</strong> ' + (message || '') + '</div>' +
                    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';

                toastContainer.appendChild(toastEl);
                if (window.bootstrap && bootstrap.Toast) {
                    var bt = new bootstrap.Toast(toastEl, { autohide: true, delay: 4000 });
                    bt.show();
                }
                setTimeout(function() { try { toastEl.remove(); } catch (e) {} }, 6000);
            } catch (e) {
                console.error('showToast error', e);
            }
        }

        function init() {
            if (typeof $ === 'undefined' || !$.fn || !$.fn.DataTable) {
                console.warn('jQuery/DataTables not ready');
                return;
            }

            try {
                var table = $('#walkinsTable').DataTable({ dom: 'Brtip', buttons: [], order: [[6,'desc']], pageLength: 25, searching: false });

                // ============== SEARCH FUNCTIONALITY ==============
                var searchTimeout;
                $('#searchInput').on('keyup', function() {
                    clearTimeout(searchTimeout);
                    var searchTerm = this.value.trim();
                    
                    searchTimeout = setTimeout(function() {
                        table.search('').draw();
                        
                        if (searchTerm) {
                            var lowerSearch = searchTerm.toLowerCase();
                            table.rows().every(function() {
                                var rowNode = this.node();
                                var fullname = rowNode.cells[0].textContent.toLowerCase();
                                var address = rowNode.cells[1].textContent.toLowerCase();
                                var contact = rowNode.cells[2].textContent.toLowerCase();
                                var purpose = rowNode.cells[3].textContent.toLowerCase();
                                
                                
                                var match = fullname.includes(lowerSearch) ||
                                           address.includes(lowerSearch) ||
                                           contact.includes(lowerSearch) ||
                                           purpose.includes(lowerSearch);
                                           
                                
                                $(rowNode).toggle(match);
                            });
                        } else {
                            table.rows().every(function() {
                                $(this.node()).show();
                            });
                        }
                    }, 300);
                });

                

                // ============== REFRESH TABLE ==============
                $('#refreshButton').on('click', function() {
                    location.reload();
                });

                // ============== EXPORT HANDLERS ==============
                $('#saveExcelBtn').on('click', function(e) {
                    e.preventDefault();
                    var searchVal = $('#searchInput').val();
                    
                    var csrfToken = $('meta[name="csrf-token"]').attr('content');
                    
                    console.log('Exporting Excel with search:', searchVal);
                    console.log('CSRF Token:', csrfToken);
                    
                    if (!csrfToken) {
                        showToast('danger', 'Error', 'CSRF token is missing! Please refresh the page.');
                        return;
                    }
                    
                    // Disable button during export
                    var $btn = $(this);
                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Exporting...');
                    
                    $.ajax({
                        url: '<?php echo e(route("admin.walkins.export.excel")); ?>',
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        data: { 
                            search: searchVal
                        },
                        dataType: 'json'
                    }).done(function(response) {
                        if (response.success) {
                            showToast('success', 'Success', 'Excel file saved to storage folder successfully!');
                            console.log('Excel exported:', response);
                        } else {
                            showToast('danger', 'Error', response.message || 'Failed to export Excel');
                        }
                    }).fail(function(xhr) {
                        var msg = 'Error exporting Excel';
                        try { if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message; } catch(e){}
                        showToast('danger', 'Error', msg);
                        console.error('Excel export failed:', xhr);
                    }).always(function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-file-excel me-1"></i> Save as Excel');
                    });
                });

                $('#savePdfBtn').on('click', function(e) {
                    e.preventDefault();
                    var searchVal = $('#searchInput').val();
                    
                    var csrfToken = $('meta[name="csrf-token"]').attr('content');
                    
                    console.log('Exporting PDF with search:', searchVal);
                    console.log('CSRF Token:', csrfToken);
                    
                    if (!csrfToken) {
                        showToast('danger', 'Error', 'CSRF token is missing! Please refresh the page.');
                        return;
                    }
                    
                    // Disable button during export
                    var $btn = $(this);
                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Exporting...');
                    
                    $.ajax({
                        url: '<?php echo e(route("admin.walkins.export.pdf")); ?>',
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        data: { 
                            search: searchVal
                        },
                        dataType: 'json'
                    }).done(function(response) {
                        if (response.success) {
                            showToast('success', 'Success', 'PDF file saved to storage folder successfully!');
                            console.log('PDF exported:', response);
                        } else {
                            showToast('danger', 'Error', response.message || 'Failed to export PDF');
                        }
                    }).fail(function(xhr) {
                        var msg = 'Error exporting PDF';
                        try { if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message; } catch(e){}
                        showToast('danger', 'Error', msg);
                        console.error('PDF export failed:', xhr);
                    }).always(function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-file-pdf me-1"></i> Save as PDF');
                    });
                });

                // ============== BACKUP LOGS MODAL ==============
                $(document).on('shown.bs.modal', '#backupLogsModal', function() {
                    loadBackupLogs();
                });

                function loadBackupLogs() {
                    console.log('Loading backup logs from:', '<?php echo e(route("admin.walkins.backup.logs")); ?>');
                    $.get('<?php echo e(route("admin.walkins.backup.logs")); ?>', function(response) {
                        console.log('Backup logs response:', response);
                        if (response.success && response.backupLogs) {
                            var tbody = $('#backupLogsList');
                            tbody.empty();
                            
                            if (response.backupLogs.length === 0) {
                                console.log('No backup logs found');
                                tbody.html('<tr><td colspan="3" class="text-center text-muted py-4"><i class="fas fa-folder-open fa-2x mb-3"></i><br>No backup files found.</td></tr>');
                            } else {
                                console.log('Found', response.backupLogs.length, 'backup logs');
                                response.backupLogs.forEach(function(log) {
                                    console.log('Adding log:', log);
                                    var row = '<tr>' +
                                        '<td>' + (log.decrypted_name || 'Unknown') + '</td>' +
                                        '<td>' + (log.formatted_date || '') + '</td>' +
                                        '<td>' +
                                            '<div class="btn-group btn-group-sm" role="group">' +
                                                '<button type="button" class="btn btn-info preview-backup-btn" data-id="' + log.id + '" title="Preview"><i class="fas fa-eye"></i></button>' +
                                                '<button type="button" class="btn btn-success download-backup-btn" data-id="' + log.id + '" title="Download"><i class="fas fa-download"></i></button>' +
                                                '<button type="button" class="btn btn-danger delete-backup-btn" data-id="' + log.id + '" data-name="' + (log.decrypted_name || 'Unknown') + '" title="Delete"><i class="fas fa-trash-alt"></i></button>' +
                                            '</div>' +
                                        '</td>' +
                                        '</tr>';
                                    tbody.append(row);
                                });
                                
                                // Attach event handlers to the buttons
                                attachBackupButtonHandlers();
                            }
                        } else {
                            console.error('Invalid response format:', response);
                            $('#backupLogsList').html('<tr><td colspan="3" class="text-center text-danger">Error loading backup logs</td></tr>');
                        }
                    }).fail(function(xhr, status, error) {
                        console.error('Backup logs request failed:', status, error, xhr);
                        $('#backupLogsList').html('<tr><td colspan="3" class="text-center text-danger">Error loading backup logs: ' + error + '</td></tr>');
                    });
                }
                
                function attachBackupButtonHandlers() {
                    // Download button
                    $('.download-backup-btn').off('click').on('click', function(e) {
                        e.preventDefault();
                        const backupId = $(this).data('id');
                        downloadBackupFile(backupId);
                    });
                    
                    // Preview button
                    $('.preview-backup-btn').off('click').on('click', function(e) {
                        e.preventDefault();
                        const backupId = $(this).data('id');
                        previewBackupFile(backupId);
                    });
                    
                    // Delete button
                    $('.delete-backup-btn').off('click').on('click', function(e) {
                        e.preventDefault();
                        const backupId = $(this).data('id');
                        const fileName = $(this).data('name');
                        showDeleteConfirmation(backupId, fileName);
                    });
                }
                
                function downloadBackupFile(backupId) {
                    window.location.href = '<?php echo e(route("admin.walkins.download.file", "")); ?>/' + backupId;
                }
                
                function previewBackupFile(backupId) {
                    // Show loading state
                    $('#previewLoading').show();
                    $('#pdfPreviewSection').hide();
                    $('#excelPreviewSection').hide();
                    $('#csvPreviewSection').hide();
                    $('#fileInfo').hide();
                    $('#previewError').hide();
                    
                    // Clear previous content
                    $('#pdfPreviewFrame').attr('src', '');
                    $('#excelPreviewTable').empty();
                    $('#csvPreviewTable').empty();
                    
                    // Get file details and content via API
                    $.ajax({
                        url: '<?php echo e(route("admin.walkins.view.backup", "")); ?>/' + backupId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Show file info
                                $('#backupFileName').text(response.filename);
                                $('#backupFileDate').text(response.date);
                                $('#backupFileType').text(response.type || 'Unknown');
                                $('#backupFileSize').text(response.file_size || 'N/A');
                                
                                // Set download link
                                $('#downloadPreviewBtn').attr('href', '<?php echo e(route("admin.walkins.download.file", "")); ?>/' + backupId);
                                
                                // Determine file type and show appropriate preview
                                const fileExtension = response.filename.split('.').pop().toLowerCase();
                                
                                if (fileExtension === 'pdf') {
                                    showPdfPreview(response);
                                } else if (fileExtension === 'xlsx' || fileExtension === 'xls') {
                                    showExcelPreview(response);
                                } else if (fileExtension === 'csv') {
                                    showCsvPreview(response);
                                } else {
                                    showPreviewError('Unsupported file format for preview. Please download the file.');
                                }
                                
                                // Show file info
                                $('#previewLoading').hide();
                                $('#fileInfo').show();
                            } else {
                                showPreviewError(response.message || 'Unable to load file');
                            }
                        },
                        error: function(xhr, status, error) {
                            let errorMessage = 'Error loading file preview';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            showPreviewError(errorMessage);
                        },
                        complete: function() {
                            // Show the preview modal
                            const previewModalElement = document.getElementById('backupPreviewModal');
                            const previewModal = new bootstrap.Modal(previewModalElement, {
                                backdrop: 'static',
                                keyboard: true
                            });
                            previewModal.show();
                        }
                    });
                }
                
                // Show PDF preview
                function showPdfPreview(response) {
                    try {
                        if (response.content) {
                            const pdfDataUrl = 'data:application/pdf;base64,' + response.content;
                            $('#pdfPreviewFrame').attr('src', pdfDataUrl + '#toolbar=1&navpanes=1&scrollbar=1');
                            $('#pdfPreviewSection').show();
                        } else {
                            showPreviewError('No content available for PDF preview');
                        }
                    } catch (e) {
                        console.error('Error showing PDF preview:', e);
                        showPreviewError('Error displaying PDF file');
                    }
                }
                
                // Show Excel preview (table view)
                function showExcelPreview(response) {
                    try {
                        $('#excelPreviewTable').empty();
                        
                        if (response.content && Array.isArray(response.content)) {
                            let tableHTML = '';
                            
                            if (response.content.length > 0) {
                                tableHTML += '<thead><tr>';
                                response.content[0].forEach(cell => {
                                    tableHTML += `<th>${escapeHtml(cell || '')}</th>`;
                                });
                                tableHTML += '</tr></thead><tbody>';
                                
                                const startRow = response.hasHeader ? 1 : 0;
                                for (let i = startRow; i < response.content.length; i++) {
                                    tableHTML += '<tr>';
                                    response.content[i].forEach(cell => {
                                        tableHTML += `<td>${escapeHtml(cell || '')}</td>`;
                                    });
                                    tableHTML += '</tr>';
                                }
                                tableHTML += '</tbody>';
                            } else {
                                tableHTML = '<tbody><tr><td colspan=\"10\" class=\"text-center text-muted\">No data found</td></tr></tbody>';
                            }
                            
                            $('#excelPreviewTable').html(tableHTML);
                            $('#excelPreviewSection').show();
                        } else {
                            showPreviewError('Cannot preview this Excel file. Please download it.');
                        }
                    } catch (e) {
                        console.error('Error showing Excel preview:', e);
                        showPreviewError('Error displaying Excel file');
                    }
                }
                
                // Show CSV preview (table view)
                function showCsvPreview(response) {
                    try {
                        $('#csvPreviewTable').empty();
                        
                        if (response.content && typeof response.content === 'string') {
                            let tableHTML = '';
                            const lines = response.content.trim().split('\n');
                            
                            if (lines.length > 0) {
                                // First row as header
                                tableHTML += '<thead><tr>';
                                const headerCells = lines[0].split(',').map(cell => cell.trim().replace(/^\"|\"$/g, ''));
                                headerCells.forEach(cell => {
                                    tableHTML += `<th>${escapeHtml(cell)}</th>`;
                                });
                                tableHTML += '</tr></thead><tbody>';
                                
                                // Data rows
                                for (let i = 1; i < lines.length; i++) {
                                    const cells = lines[i].split(',').map(cell => cell.trim().replace(/^\"|\"$/g, ''));
                                    tableHTML += '<tr>'
                                    cells.forEach(cell => {
                                        tableHTML += `<td>${escapeHtml(cell)}</td>`;
                                    });
                                    tableHTML += '</tr>';
                                }
                                tableHTML += '</tbody>';
                            } else {
                                tableHTML = '<tbody><tr><td colspan=\"10\" class=\"text-center text-muted\">No data found</td></tr></tbody>';
                            }
                            
                            $('#csvPreviewTable').html(tableHTML);
                            $('#csvPreviewSection').show();
                        } else {
                            showPreviewError('Cannot preview this CSV file. Please download it.');
                        }
                    } catch (e) {
                        console.error('Error showing CSV preview:', e);
                        showPreviewError('Error displaying CSV file');
                    }
                }
                
                // Escape HTML to prevent XSS
                function escapeHtml(text) {
                    const map = {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    };
                    return text.replace(/[&<>"']/g, m => map[m]);
                }
                
                function showPreviewError(errorMessage) {
                    $('#previewLoading').hide();
                    $('#pdfPreviewSection').hide();
                    $('#excelPreviewSection').hide();
                    $('#csvPreviewSection').hide();
                    $('#fileInfo').hide();
                    $('#previewError').show();
                    $('#previewErrorMessage').text(errorMessage);
                }
                
                // Default button HTML for delete confirmation
                const ORIGINAL_DELETE_BTN_HTML = '<i class="fas fa-trash-alt me-1"></i> Delete';
                
                function showDeleteConfirmation(backupId, fileName) {
                    // Set modal title for backup deletion
                    $('#deleteModalTitle').text('Delete Backup File');
                    $('#deleteFileName').text(fileName);
                    $('#confirmDeleteBtn').data('id', backupId);
                    
                    // Reset button to original state before showing
                    const deleteBtn = $('#confirmDeleteBtn');
                    deleteBtn.html(ORIGINAL_DELETE_BTN_HTML);
                    deleteBtn.prop('disabled', false);
                    
                    // Remove any existing backdrops
                    const existingBackdrops = document.querySelectorAll('.modal-backdrop');
                    existingBackdrops.forEach(backdrop => {
                        backdrop.style.zIndex = '1049';
                    });
                    
                    // Create and show delete modal
                    const deleteModalElement = document.getElementById('deleteConfirmationModal');
                    const deleteModal = new bootstrap.Modal(deleteModalElement, {
                        backdrop: 'static',
                        keyboard: true
                    });
                    
                    deleteModal.show();
                    
                    // Adjust backdrop z-index
                    setTimeout(() => {
                        const deleteBackdrop = document.querySelectorAll('.modal-backdrop');
                        if (deleteBackdrop.length > 1) {
                            deleteBackdrop[deleteBackdrop.length - 1].style.zIndex = '1050';
                        }
                    }, 10);
                    
                    // Handle confirm delete button click
                    deleteBtn.off('click').on('click', function() {
                        const idToDelete = $(this).data('id');
                        deleteBackupFile(idToDelete, deleteModal);
                    });
                    
                    // Handle delete modal close
                    $(deleteModalElement).off('hidden.bs.modal').on('hidden.bs.modal', function() {
                        // Reset button state
                        deleteBtn.html(ORIGINAL_DELETE_BTN_HTML);
                        deleteBtn.prop('disabled', false);
                        
                        // Remove delete modal backdrop
                        const deleteBackdrop = document.querySelectorAll('.modal-backdrop');
                        if (deleteBackdrop.length > 0) {
                            deleteBackdrop[deleteBackdrop.length - 1].remove();
                        }
                        
                        // Re-show backup logs modal
                        const backupLogsModalElement = document.getElementById('backupLogsModal');
                        const backupLogsModal = new bootstrap.Modal(backupLogsModalElement);
                        setTimeout(() => {
                            backupLogsModal.show();
                        }, 50);
                    });
                }
                
                function deleteBackupFile(backupId, modalInstance) {
                    const deleteBtn = $('#confirmDeleteBtn');
                    deleteBtn.html('<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');
                    deleteBtn.prop('disabled', true);
                    
                    $.ajax({
                        url: '<?php echo e(route("admin.walkins.delete.backup", "")); ?>/' + backupId,
                        type: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showToast('success', 'Success', 'Backup file deleted successfully.');
                                modalInstance.hide();
                                loadBackupLogs();
                            } else {
                                showToast('danger', 'Error', response.message || 'Failed to delete backup file.');
                                deleteBtn.html(ORIGINAL_DELETE_BTN_HTML);
                                deleteBtn.prop('disabled', false);
                            }
                        },
                        error: function(xhr, status, error) {
                            let errorMessage = 'Error deleting backup file';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            showToast('danger', 'Error', errorMessage);
                            deleteBtn.html(ORIGINAL_DELETE_BTN_HTML);
                            deleteBtn.prop('disabled', false);
                        }
                    });
                }

                // ============== DELETE HANDLER ==============
                var pendingDelete = { id: null, source: null, $button: null };
                $(document).on('click', '.delete-walkin-btn', function(e) {
                    e.preventDefault();
                    var $btn = $(this);
                    var id = $btn.data('id');
                    var name = $btn.data('name');
                    var source = $btn.data('source') || 'diffun';

                    pendingDelete.id = id;
                    pendingDelete.source = source;
                    pendingDelete.$button = $btn;
                    pendingDelete.isWalkin = true;

                    // Set modal content for walk-in deletion
                    $('#deleteModalTitle').text('Delete Walk-in Record');
                    $('#deleteFileName').text(name || '');

                    var $confirm = $('#confirmDeleteBtn');
                    $confirm.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Delete');

                    var modalEl = document.getElementById('deleteConfirmationModal');
                    var bsModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: true });
                    bsModal.show();

                    $confirm.off('click').on('click', function() {
                        $confirm.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Deleting...');

                        $.ajax({
                            url: '/walkins/delete/' + pendingDelete.id,
                            type: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            data: { source: pendingDelete.source },
                            dataType: 'json'
                        }).done(function(resp) {
                            if (resp && resp.success) {
                                showToast('success', 'Deleted', 'Walk-in deleted');
                                try { table.row(pendingDelete.$button.closest('tr')).remove().draw(); } catch(e) {}
                                bsModal.hide();
                            } else {
                                showToast('danger', 'Error', (resp && resp.message) || 'Failed to delete');
                                $confirm.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Delete');
                            }
                        }).fail(function(xhr) {
                            var msg = 'Error deleting record';
                            try { if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message; } catch(e){}
                            showToast('danger', 'Error', msg);
                            $confirm.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Delete');
                        });
                    });

                    $(modalEl).off('hidden.bs.modal').on('hidden.bs.modal', function() {
                        pendingDelete.id = null; pendingDelete.source = null; pendingDelete.$button = null; pendingDelete.isWalkin = false;
                        $confirm.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Delete');
                    });
                });
            } catch (e) {
                console.error('init error', e);
            }
        }

        function waitUntilReady(cb) {
            var attempts = 0;
            var iv = setInterval(function() {
                attempts++;
                if (document.readyState === 'complete' || document.readyState === 'interactive') {
                    if (typeof $ !== 'undefined' && window.bootstrap) {
                        clearInterval(iv);
                        cb();
                    }
                }
                if (attempts > 100) clearInterval(iv);
            }, 100);
        }

        waitUntilReady(init);
    })();
<?php echo $__env->make('partials.notification-badge-visibility', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>
</html><?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\adminWalkIns.blade.php ENDPATH**/ ?>