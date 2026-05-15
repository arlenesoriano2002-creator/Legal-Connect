<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <title>Message Inquiries - LegalConnect</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/staff/staffclientstbl.blade.css')); ?>">

    <style>
        .inquiry-table thead {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .inquiry-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .action-btn {
            padding: 6px 10px;
            margin: 0 4px;
            font-size: 14px;
        }
        .email-btn {
            color: #0066cc;
            border: 1px solid #0066cc;
        }
        .email-btn:hover {
            background-color: #0066cc;
            color: white;
        }
        .sms-btn {
            color: #28a745;
            border: 1px solid #28a745;
        }
        .sms-btn:hover {
            background-color: #28a745;
            color: white;
        }
        .delete-btn {
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        .delete-btn:hover {
            background-color: #dc3545;
            color: white;
        }
        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }
        .modal-body {
            max-height: 500px;
            overflow-y: auto;
        }
        .inquiry-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .inquiry-details p {
            margin-bottom: 8px;
        }
        .inquiry-details strong {
            color: #333;
        }
        .search-inquiry-wrapper {
            position: relative;
            width: 300px;
        }
        .search-inquiry-input {
            padding-right: 38px;
            padding-left: 12px;
            font-size: 14px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }
        .search-inquiry-input:focus {
            border-color: #0066cc;
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
            outline: none;
        }
        .search-inquiry-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            pointer-events: none;
            font-size: 14px;
        }
        .search-focused .search-inquiry-icon {
            color: #0066cc;
        }
    </style>
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

                <!-- Log Out -->
                <form id="logout-form" action="<?php echo e(route('custom.logout')); ?>" method="POST" style="display: none;">
                    <?php echo csrf_field(); ?>
                </form>
                <button type="button" class="btn logout-btn" onclick="showLogoutModal()">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>
            
            <!-- Rest of your dashboard content -->
            <div class="dashboard-container">
                <div class="container-fluid">
                    <!-- Page Header -->
                    <div class="page-header mb-4">
                        <h1 class="page-title">
                            <i class="fas fa-envelope me-2"></i>Message Inquiries
                        </h1>
                        <div class="page-subtitle">
                            View and respond to client inquiries
                        </div>
                    </div>

                    <!-- Message Inquiries Table -->
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-inbox me-2"></i>All Inquiries
                                </h5>
                                <div class="search-inquiry-wrapper">
                                    <input 
                                        type="text" 
                                        id="inquirySearch" 
                                        class="form-control search-inquiry-input" 
                                        placeholder="Search inquiries..."
                                        aria-label="Search inquiries"
                                    >
                                    <i class="fas fa-search search-inquiry-icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover inquiry-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Subject</th>
                                            <th>Message</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="inquiriesTableBody">
                                        <!-- ===== DEBUG START ===== -->
                                        <!-- Total inquiries passed to view: <?php echo e($inquiries ? count($inquiries) : 'NULL/FALSE'); ?> -->
                                        <!-- ===== DEBUG END ===== -->
                                        
                                        <?php $__empty_1 = true; $__currentLoopData = $inquiries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inquiry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <!-- DEBUG ROW: ID=<?php echo e($inquiry->id); ?>, Name=<?php echo e($inquiry->name); ?>, Subject=<?php echo e($inquiry->subject ?? 'NULL'); ?>, SubjectLen=<?php echo e(strlen($inquiry->subject ?? '')); ?> -->
                                            <tr id="inquiry-row-<?php echo e($inquiry->id); ?>" data-inquiry-id="<?php echo e($inquiry->id); ?>">
                                                <td><strong><?php echo e($inquiry->name); ?></strong></td>
                                                <td><?php echo e($inquiry->phone); ?></td>
                                                <td><?php echo e($inquiry->email); ?></td>
                                                <td>
                                                    [RAW: <?php echo e($inquiry->subject ?? 'SUBJECT_PROPERTY_NULL'); ?>]
                                                    <?php if(!empty($inquiry->subject)): ?>
                                                        <span class="badge bg-info"><?php echo e(substr($inquiry->subject, 0, 30)); ?><?php echo e(strlen($inquiry->subject) > 30 ? '...' : ''); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary text-muted">â€”</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small><?php echo e(substr($inquiry->message, 0, 40)); ?><?php echo e(strlen($inquiry->message) > 40 ? '...' : ''); ?></small>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?php echo e(\Carbon\Carbon::parse($inquiry->created_at)->format('M d, Y')); ?></small>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm email-btn action-btn" onclick="openEmailModal(<?php echo e($inquiry->id); ?>, '<?php echo e($inquiry->name); ?>', '<?php echo e($inquiry->email); ?>', '<?php echo e(addslashes($inquiry->subject ?? '')); ?>')">
                                                        <i class="fas fa-envelope"></i>
                                                    </button>
                                                    <button class="btn btn-sm sms-btn action-btn" onclick="openSmsModal(<?php echo e($inquiry->id); ?>, '<?php echo e($inquiry->name); ?>', '<?php echo e($inquiry->phone); ?>')">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                    <button class="btn btn-sm delete-btn action-btn" type="button" title="Delete inquiry" onclick="openDeleteModal(<?php echo e($inquiry->id); ?>, <?php echo \Illuminate\Support\Js::from($inquiry->name)->toHtml() ?>)">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr id="emptyInquiriesRow">
                                                <td colspan="7" class="text-center py-4 text-muted">
                                                    <i class="fas fa-inbox" style="font-size: 24px; opacity: 0.5;"></i>
                                                    <p class="mt-2">No inquiries found</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <small class="text-muted">Total Inquiries: <strong id="inquiriesCount"><?php echo e(count($inquiries)); ?></strong></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Reply Modal -->
    <div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="emailModalLabel">
                        <i class="fas fa-envelope me-2"></i>Send Email Reply
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="inquiry-details" id="emailInquiryDetails"></div>
                    <form id="emailForm">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" id="emailInquiryId" name="inquiry_id">
                        <input type="hidden" id="emailAddress" name="email">
                        
                        <div class="mb-3">
                            <label for="emailSubject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="emailSubject" name="subject" required>
                            <small class="text-muted">Will be prefixed with "Re: "</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="emailMessage" class="form-label">Message</label>
                            <textarea class="form-control" id="emailMessage" name="message" rows="6" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="sendEmail()">
                        <i class="fas fa-send me-2"></i>Send Email
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- SMS Reply Modal -->
    <div class="modal fade" id="smsModal" tabindex="-1" aria-labelledby="smsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="smsModalLabel">
                        <i class="fas fa-comment-dots me-2"></i>Send SMS Reply
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="inquiry-details" id="smsInquiryDetails"></div>
                    <form id="smsForm">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" id="smsInquiryId" name="inquiry_id">
                        <input type="hidden" id="smsPhone" name="phone">
                        
                        <div class="mb-3">
                            <label for="smsMessage" class="form-label">Message (Max 160 characters)</label>
                            <textarea class="form-control" id="smsMessage" name="message" rows="4" maxlength="160" required></textarea>
                            <small class="text-muted d-flex justify-content-between mt-2">
                                <span>Characters remaining: <strong id="charCount">160</strong></span>
                            </small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="sendSms()">
                        <i class="fas fa-paper-plane me-2"></i>Send SMS
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteInquiryModal" tabindex="-1" aria-labelledby="deleteInquiryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteInquiryModalLabel">
                        <i class="fas fa-trash-alt me-2"></i>Delete Inquiry
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div style="font-size: 48px; color: #dc3545; margin-bottom: 15px;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h4 class="mb-3">Confirm Delete</h4>
                    <p class="mb-0">Are you sure you want to delete the inquiry from <strong id="deleteInquiryName"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteInquiryBtn" onclick="confirmDeleteInquiry()">
                        <i class="fas fa-trash-alt me-1"></i> Delete
                    </button>
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

    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo e(asset('js/diffun_staff/sidebar-toggle.js')); ?>"></script>
        <script src="<?php echo e(asset('js/staff/dashboardStaff.js')); ?>"></script>
    <script src="<?php echo e(asset('js/staff/diffunNotifications.js')); ?>"></script>
    <script src="<?php echo e(asset('js/message_inquiries.js')); ?>"></script>


    <script>
        const emailModal = new bootstrap.Modal(document.getElementById('emailModal'));
        const smsModal = new bootstrap.Modal(document.getElementById('smsModal'));
        const deleteInquiryModal = new bootstrap.Modal(document.getElementById('deleteInquiryModal'));
        const deleteInquiryRouteTemplate = <?php echo json_encode(route('diffun.message.inquiries.destroy', ['id' => '__ID__']), 512) ?>;
        let inquiryIdToDelete = null;

        function openEmailModal(inquiryId, name, email, subject) {
            document.getElementById('emailInquiryId').value = inquiryId;
            document.getElementById('emailAddress').value = email;
            document.getElementById('emailSubject').value = subject;
            document.getElementById('emailMessage').value = '';
            
            document.getElementById('emailInquiryDetails').innerHTML = `
                <p><strong>From:</strong> ${name} (${email})</p>
                <p><strong>Subject:</strong> ${subject}</p>
            `;
            
            emailModal.show();
        }

        function openSmsModal(inquiryId, name, phone) {
            document.getElementById('smsInquiryId').value = inquiryId;
            document.getElementById('smsPhone').value = phone;
            document.getElementById('smsMessage').value = '';
            document.getElementById('charCount').innerText = '160';
            
            document.getElementById('smsInquiryDetails').innerHTML = `
                <p><strong>To:</strong> ${name} (${phone})</p>
            `;
            
            smsModal.show();
        }

        function openDeleteModal(inquiryId, name) {
            inquiryIdToDelete = inquiryId;
            document.getElementById('deleteInquiryName').textContent = name;

            const deleteButton = document.getElementById('confirmDeleteInquiryBtn');
            deleteButton.disabled = false;
            deleteButton.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete';

            deleteInquiryModal.show();
        }

        document.getElementById('smsMessage').addEventListener('input', function() {
            const remaining = 160 - this.value.length;
            document.getElementById('charCount').innerText = remaining;
        });

        function sendEmail() {
            const form = document.getElementById('emailForm');
            const formData = new FormData(form);

            fetch('<?php echo e(route("diffun.message.inquiries.send.email")); ?>', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    emailModal.hide();
                    showToast('Email sent successfully!', 'success');
                } else {
                    showToast(data.error || 'Failed to send email', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while sending the email', 'danger');
            });
        }

        function sendSms() {
            const form = document.getElementById('smsForm');
            const formData = new FormData(form);

            fetch('<?php echo e(route("diffun.message.inquiries.send.sms")); ?>', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    smsModal.hide();
                    showToast('SMS sent successfully!', 'success');
                } else {
                    showToast(data.error || 'Failed to send SMS', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while sending the SMS', 'danger');
            });
        }

        function confirmDeleteInquiry() {
            if (!inquiryIdToDelete) {
                return;
            }

            const deleteButton = document.getElementById('confirmDeleteInquiryBtn');
            const deleteUrl = deleteInquiryRouteTemplate.replace('__ID__', inquiryIdToDelete);

            deleteButton.disabled = true;
            deleteButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Deleting...';

            fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(async response => {
                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'Failed to delete inquiry');
                }

                const row = document.getElementById(`inquiry-row-${inquiryIdToDelete}`);
                if (row) {
                    row.remove();
                }

                inquiryIdToDelete = null;
                deleteInquiryModal.hide();
                syncInquiryTableState();
                showToast('Inquiry deleted successfully!', 'success');
            })
            .catch(error => {
                console.error('Error:', error);
                showToast(error.message || 'An error occurred while deleting the inquiry', 'danger');
            })
            .finally(() => {
                deleteButton.disabled = false;
                deleteButton.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete';
            });
        }

        function syncInquiryTableState() {
            const tableBody = document.getElementById('inquiriesTableBody');
            const dataRows = tableBody.querySelectorAll('tr[data-inquiry-id]');
            const emptyStateRow = document.getElementById('emptyInquiriesRow');
            const noResultsRow = tableBody.querySelector('.no-results');
            const searchTerm = document.getElementById('inquirySearch')?.value?.trim();

            if (dataRows.length === 0) {
                if (noResultsRow) {
                    noResultsRow.remove();
                }

                if (!emptyStateRow) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.id = 'emptyInquiriesRow';
                    emptyRow.innerHTML = `
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox" style="font-size: 24px; opacity: 0.5;"></i>
                            <p class="mt-2">No inquiries found</p>
                        </td>
                    `;
                    tableBody.appendChild(emptyRow);
                }
            } else if (emptyStateRow) {
                emptyStateRow.remove();
            }

            const visibleRows = Array.from(tableBody.querySelectorAll('tr[data-inquiry-id]')).filter(row => row.style.display !== 'none').length;

            if (visibleRows === 0 && searchTerm) {
                if (!noResultsRow) {
                    const searchEmptyRow = document.createElement('tr');
                    searchEmptyRow.className = 'no-results';
                    searchEmptyRow.innerHTML = `
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-search" style="font-size: 24px; opacity: 0.5;"></i>
                            <p class="mt-2">No inquiries match your search</p>
                        </td>
                    `;
                    tableBody.appendChild(searchEmptyRow);
                }
            } else if (noResultsRow) {
                noResultsRow.remove();
            }

            document.getElementById('inquiriesCount').textContent = visibleRows;
        }

        function showLogoutModal() {
            const modal = new bootstrap.Modal(document.getElementById('logoutConfirmationModal'));
            modal.show();
        }

        function showToast(message, type = 'info') {
            const toastHtml = `
                <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            
            const container = document.getElementById('toastContainer');
            const toastElement = document.createElement('div');
            toastElement.innerHTML = toastHtml;
            container.appendChild(toastElement);
            
            const toast = new bootstrap.Toast(toastElement.querySelector('.toast'));
            toast.show();
            
            setTimeout(() => toastElement.remove(), 5000);
        }
    </script>
<?php echo $__env->make('partials.notification-badge-visibility', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\staff\message_inquiries.blade.php ENDPATH**/ ?>