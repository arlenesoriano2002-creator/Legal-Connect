<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="user-id" content="<?php echo e(Auth::id()); ?>">
    <meta name="user-role" content="<?php echo e(Auth::user()->role ?? ''); ?>">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <title>Calendar Management</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="<?php echo e(asset('css/administrator.blade.css')); ?>">
    
    <style>
        /* Additional styles for refresh button */
        .view-tabs-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .view-tabs {
            display: flex;
            gap: 10px;
        }
        .refresh-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .refresh-btn-tabs {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        .refresh-btn-tabs:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        .refresh-btn-tabs:active {
            transform: translateY(0);
        }
        .refresh-btn-tabs i {
            font-size: 14px;
        }
        
        /* Floating modal/tooltip styles */
        .floating-description {
            position: fixed;
            background: white;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 9999;
            max-width: 300px;
            display: none;
        }
        .floating-description.active {
            display: block;
        }
        /* Sidebar close (×) button styling - non-invasive */
        .close-btn { align-self: flex-start; }
        .sidebar-close-btn {
            background: transparent;
            border: none;
            font-size: 24px;
            line-height: 1;
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 6px;
            color: #333;
            transition: background-color .12s ease, transform .08s ease, color .12s ease;
        }
        .sidebar-close-btn:hover {
            background: rgba(0,0,0,0.06);
            color: #000;
            transform: translateY(-1px);
        }
        .sidebar-close-btn:active { transform: translateY(0); }
        @media (max-width: 768px) {
            .sidebar-close-btn { font-size: 20px; }
        }
    </style>
</head>
<body>
    <div id="wrapper" >
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading" style="display: flex; flex-direction: column; align-items: center; justify-content: space-between; padding: 10px;">
                <!-- <div class="close-btn" style=" margin-left: 10px; margin-bottom: 19px; ">
                    <button class="sidebar-close-btn" id="sidebar-close-btn" title="Close sidebar">
                        &times;
                    </button>
                </div>-->
                <div class="head-content" style="display: flex; flex-direction: row; align-items: center;">
                <img src="<?php echo e(asset('logo6.png')); ?>" alt="LegalConnect logo" width="40" height="40" style="border-radius: 50%;">
                <span>LegalConnect</span>
                </div>
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

            <form id="logout-form" action="<?php echo e(route('custom.logout')); ?>" method="POST" style="display: none;">
                <?php echo csrf_field(); ?>
            </form>
            <button type="button" class="btn logout-btn" onclick="showLogoutModal()">
                <i class="fas fa-sign-out-alt"></i> Log out
            </button>
        </nav>

            <div class="calendar-container">
                <div class="calendar-views" style="flex: 1; min-width: 100%;">
                    <!-- Updated View Tabs with Refresh Button -->
                    <div class="view-tabs-container">
                        <div class="refresh-container">
                            <button id="refreshCalendars" class="refresh-btn-tabs" title="Refresh Both Calendars">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    
                    <div class="view-content">
                        <!-- Month View -->
                        <div id="monthView" class="view-pane">
                            <div class="month-calendar">
                                <div class="calendar-header">
                                    <button class="nav-btn" id="prevMonth">&lt;</button>
                                    <h3 id="currentMonthYear"><?php echo e(date('F Y')); ?></h3>
                                    <button class="nav-btn" id="nextMonth">&gt;</button>
                                </div>
                                
                                <div class="calendar-office-display" style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                                    <span style="background: #0d6efd; color: white; padding: 6px 12px; border-radius: 4px; font-weight: 500; font-size: 13px;">
                                        📍 <?php echo e(Auth::user()->law_office_id && Auth::user()->lawOffice ? Auth::user()->lawOffice->law_office : 'Administrator - All Offices'); ?>

                                    </span>
                                    <?php if(in_array(Auth::user()->role, ['admin', 'superadmin'])): ?>
                                    <select id="adminOfficeSelector" class="form-select form-select-sm" style="max-width: 200px;" onchange="setAdminOffice(this.value)">
                                        <option value="">Select Office</option>
                                        <?php
                                            $offices = \App\Models\LawOffice::all();
                                        ?>
                                        <?php $__currentLoopData = $offices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $office): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($office->id); ?>" <?php echo e(session('law_office_id') == $office->id ? 'selected' : ''); ?>>
                                            <?php echo e($office->law_office); ?>

                                        </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php endif; ?>
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
                                
                                <div class="days-grid" id="monthGrid"></div>
                            </div>
                        </div>
                        
                        <div id="cordonView" class="view-pane" style="display: none;">
                            <div class="month-calendar">
                                <div class="calendar-header">
                                    <button class="nav-btn" id="cordonPrevMonth">&lt;</button>
                                    <h3 id="cordonCurrentMonthYear"><?php echo e(date('F Y')); ?></h3>
                                    <button class="nav-btn" id="cordonNextMonth">&gt;</button>
                                </div>
                                
                                <div class="calendar-office-display" style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                                    <span style="background: #0d6efd; color: white; padding: 6px 12px; border-radius: 4px; font-weight: 500; font-size: 13px;">
                                        📍 Cordon Branch
                                    </span>
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
                                
                                <div class="days-grid" id="cordonMonthGrid"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="messageContainer"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Color Selection Modal -->
    <div class="modal fade" id="colorSelectionModal" tabindex="-1" aria-labelledby="colorSelectionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="colorSelectionModalLabel">
                        <i class="fas fa-calendar-day"></i> 
                        Calendar Settings for <span id="modalDateDisplay"></span>
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
                            <label for="dateDescriptionInput" class="form-label">
                                <i class="fas fa-sticky-note"></i> Date Description
                            </label>
                            <textarea class="form-control" id="dateDescriptionInput" rows="2" 
                                      placeholder="Add description for the entire date (e.g., 'Public holiday', 'Office closed')"></textarea>
                        </div>
                    </div>

                    <div class="modal-section">
                        <h6><i class="fas fa-clock"></i> Time Slot Management</h6>
                                <p class="text-muted small mb-3">
                                    Set time slots availability. Maximum capacity per slot is 4 appointments.
                                </p>
                                <div class="mb-3">
                                    <label for="setAllAvailability" class="form-label">Set All Availability</label>
                                    <select class="form-select form-select-sm" id="setAllAvailability" style="max-width: 260px;">
                                        <option value="">Select availability</option>
                                        <option value="green">Available</option>
                                        <option value="red">Not Available</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">
                                        Applies the selected availability to all editable time slots and sets each slot capacity to 4 or 0 automatically.
                                    </small>
                                </div>
                        
                        <div class="time-slots-container">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Time Slot</th>
                                            <th width="80">Slot # (Max 4)</th>
                                            <th>Description</th>
                                            <th width="150">Availability</th>
                                        </tr>
                                    </thead>
                                    <tbody id="timeSlotsTableBody">
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
                    <button type="button" class="btn btn-primary" id="saveModalChanges">
                        <i class="fas fa-save"></i> Save Changes
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

   <!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!-- Set current user globally for WebRTC and other scripts -->
<script>
    window.currentUser = {
        id: <?php echo e(Auth::id()); ?>,
        role: '<?php echo e(Auth::user()->role ?? "admin"); ?>',
        law_office_id: <?php echo e(Auth::user()->law_office_id ?? 'null'); ?>,
        law_office: '<?php echo e(Auth::user()->law_office_id && Auth::user()->lawOffice ? Auth::user()->lawOffice->law_office : "Administrator - All Offices"); ?>'
    };
    
    // Store in localStorage for cross-tab communication
    try {
        localStorage.setItem('currentUserId', window.currentUser.id);
        localStorage.setItem('currentUserLawOfficeId', window.currentUser.law_office_id);
        localStorage.setItem('currentUserLawOffice', window.currentUser.law_office);
    } catch (e) {
        console.warn('Could not store user data in localStorage');
    }
    
    // Set sessionStorage for selected office if available
    <?php if(session('law_office_id')): ?>
        sessionStorage.setItem('selectedOfficeId', '<?php echo e(session('law_office_id')); ?>');
    <?php endif; ?>
    
    // Update notification time display
    function updateNotificationTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        });
        const timeElements = document.querySelectorAll('#cordonNotificationTime');
        timeElements.forEach(element => {
            element.textContent = timeString;
        });
    }
    
    // Update time every second
    setInterval(updateNotificationTime, 1000);
    updateNotificationTime(); // Initial call
    
    // Function to set admin office selection
    function setAdminOffice(officeId) {
        if (!officeId) return;
        
        // Send AJAX request to set the office in session
        fetch('/admin/set-office', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ office_id: officeId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Also set in sessionStorage for JavaScript
                sessionStorage.setItem('selectedOfficeId', officeId);
                // Update the display
                location.reload(); // Simple reload to update the display
            } else {
                alert('Error setting office: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error setting office');
        });
    }
</script>

<!-- Load cordon-calendar.js FIRST to ensure cordonCalendar is available -->
<script src="<?php echo e(asset('js/cordon-calendar.js')); ?>"></script>
<script src="<?php echo e(asset('js/administrator.js')); ?>"></script>
  <script src="<?php echo e(asset('js/admin-calendar.js')); ?>"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Close button for sidebar (non-destructive, mirrors admindashboard behavior)
    const sidebarCloseBtn = document.getElementById('sidebar-close-btn');
    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const wrapper = document.getElementById('wrapper');
            if (wrapper) wrapper.classList.remove('toggled');
            document.body.classList.remove('sidebar-open');
        });
    }

    // Close sidebar when clicking outside on mobile (does not modify menu-toggle behavior)
    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('wrapper');
        const sidebar = document.getElementById('sidebar-wrapper');
        const menuToggle = document.getElementById('menu-toggle');

        if (window.innerWidth <= 768 &&
            wrapper && wrapper.classList.contains('toggled') &&
            sidebar && !sidebar.contains(e.target) &&
            (!menuToggle || !menuToggle.contains(e.target)) &&
            e.target !== sidebar) {

            wrapper.classList.remove('toggled');
            document.body.classList.remove('sidebar-open');
        }
    });

    const bulkAvailabilitySelect = document.getElementById('setAllAvailability');
    const colorSelectionModal = document.getElementById('colorSelectionModal');
    const timeSlotsTableBody = document.getElementById('timeSlotsTableBody');

    const getBulkSlotCapacity = (availability, slotNumberInput) => {
        if (availability === 'red') {
            return 0;
        }

        const maxCapacity = parseInt(slotNumberInput?.getAttribute('max') || '4', 10);
        return Number.isNaN(maxCapacity) ? 4 : maxCapacity;
    };

    const applyBulkAvailability = (availability) => {
        if (!availability) {
            return;
        }

        document.querySelectorAll('#timeSlotsTableBody .time-slot-row').forEach((row) => {
            if (row.classList.contains('past-time-slot')) {
                return;
            }

            const availabilitySelect = row.querySelector('.time-slot-availability');
            const slotNumberInput = row.querySelector('.time-slot-number');

            if (!availabilitySelect || availabilitySelect.disabled || !slotNumberInput || slotNumberInput.disabled) {
                return;
            }

            availabilitySelect.value = availability;
            slotNumberInput.value = String(getBulkSlotCapacity(availability, slotNumberInput));
            row.classList.remove('color-red', 'color-green');
            row.classList.add(`color-${availability}`);

            availabilitySelect.dispatchEvent(new Event('change', { bubbles: true }));
            slotNumberInput.dispatchEvent(new Event('input', { bubbles: true }));
        });
    };

    if (bulkAvailabilitySelect) {
        bulkAvailabilitySelect.addEventListener('change', function() {
            applyBulkAvailability(this.value);
        });
    }

    if (bulkAvailabilitySelect && timeSlotsTableBody) {
        const tableObserver = new MutationObserver(() => {
            if (bulkAvailabilitySelect.value) {
                applyBulkAvailability(bulkAvailabilitySelect.value);
            }
        });

        tableObserver.observe(timeSlotsTableBody, { childList: true });
    }

    if (colorSelectionModal) {
        colorSelectionModal.addEventListener('hidden.bs.modal', function() {
            if (bulkAvailabilitySelect) {
                bulkAvailabilitySelect.value = '';
            }
        });
    }
});
</script>

</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\administrator.blade.php ENDPATH**/ ?>