<?php
    use Illuminate\Support\Facades\Storage;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <title>Admin Account</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    

    <link rel="stylesheet" href="<?php echo e(asset('css/adminAccount.blade.css')); ?>">
    <style>
        /* Smooth show animation for modals (create, edit, delete) */
        #createStaffModal, #editStaffModal, #deleteModal {
            opacity: 0;
            transform: translateY(-10px) scale(0.995);
            transition: opacity 260ms ease, transform 260ms ease;
            -webkit-transition: opacity 260ms ease, -webkit-transform 260ms ease;
        }
        #createStaffModal.modal-show, #editStaffModal.modal-show, #deleteModal.modal-show {
            opacity: 1 !important;
            transform: translateY(0) scale(1) !important;
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
                <button class="toggle-btn" id="menu-toggle">
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

                
                <!-- Log Out -->
                <form id="logout-form" action="<?php echo e(route('custom.logout')); ?>" method="POST" style="display: none;">
                    <?php echo csrf_field(); ?>
                </form>
                <button type="button" class="btn logout-btn" onclick="showLogoutModal()">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>
            <!-- BOOTSTRAP LOGOUT CONFIRMATION MODAL -->
            <div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-container">

                        <div class="title-header d-flex justify-content-between align-items-center p-3 border-bottom">
                            <h5 class="modal-title" id="logoutModalLabel">
                                <i class="fas fa-sign-out-alt me-2"></i>Confirm Logout
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body text-center">
                            <div style="font-size: 48px; color: #ffc107; margin-bottom: 15px;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>

                            <h4 class="mb-3">Confirm Logout</h4>
                            <p>Are you sure you want to log out?<br>You will be redirected to the login page.</p>
                        </div>

                        <div class="modal-footer d-flex justify-content-between">
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
            
            <div class="dashboard-container">
                <!-- Header Section -->
                <div class="mb-4 mt-5">
                    <h1 class="text-3xl font-bold text-gray-800">Account Management</h1>
                    <p class="text-gray-600 mt-2">Manage your  all staff accounts</p>
                </div>

                <!-- Flash Message Modal -->
                <div class="modal fade" id="flashMessageModal" tabindex="-1" aria-labelledby="flashMessageModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="flashMessageModalLabel">Message</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?php if(session('success')): ?>
                                    <div class="text-success"><?php echo e(session('success')); ?></div>
                                <?php endif; ?>
                                <?php if(session('error')): ?>
                                    <div class="text-danger"><?php echo e(session('error')); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if(session('success') || session('error')): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var flashEl = document.getElementById('flashMessageModal');
                        if (flashEl) {
                            var modal = new bootstrap.Modal(flashEl, { backdrop: 'static', keyboard: true });
                            modal.show();
                        }
                    });
                </script>
                <?php endif; ?>

                <!-- Staff Users Table -->
                <div class="card">
                            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Staff Accounts</h5>
                                <div class="d-flex gap-2">
                                    <!-- Search Form -->
                                    <form action="<?php echo e(route('adminAccount.search')); ?>" method="GET" class="d-flex">
                                        <input type="text" name="search" class="form-control form-control-sm" 
                                               placeholder="Search staff..." value="<?php echo e(request('search')); ?>">
                                        <button type="submit" class="btn btn-light btn-sm ms-2">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </form>
                                    <!-- Create Button -->
                                    <button onclick="openCreateStaffModal()" class="btn btn-success btn-sm">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                       <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Contact</th>
                                                <th>Role</th> <!-- Column 5 -->
                                                <th>Law Office</th> <!-- Column 6 -->
                                                <th>Created</th> <!-- Column 7 -->
                                                <th>Actions</th> <!-- Column 8 -->
                                            </tr>
                                        </thead>

                                        <!-- In the table body section -->
                                        <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $staffUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $staff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td>
                                                        <?php
                                                            // Always use staff_images path for display
                                                            $imagePath = $staff->image;
                                                            
                                                            // If image is not in staff_images, redirect it there
                                                            if ($imagePath && !str_contains($imagePath, 'staff_images/')) {
                                                                $imagePath = 'staff_images/default-avatar.png';
                                                            }
                                                            
                                                            // Final check for file existence
                                                            if (!$imagePath || !file_exists(public_path($imagePath))) {
                                                                $imagePath = 'staff_images/default-avatar.png';
                                                            }
                                                        ?>
                                                        
                                                        <img src="<?php echo e(asset($imagePath)); ?>" 
                                                            alt="Staff Image" 
                                                            class="rounded-circle staff-image" 
                                                            width="40" 
                                                            height="40"
                                                            style="object-fit: cover;"
                                                            data-user-id="<?php echo e($staff->id); ?>"
                                                            onerror="this.onerror=null; this.src='<?php echo e(asset('staff_images/default-avatar.png')); ?>';">
                                                    </td>
                                                    <td><?php echo e($staff->name); ?></td>
                                                    <td><?php echo e($staff->email); ?></td>
                                                    <td><?php echo e($staff->cp_number ?? 'N/A'); ?></td>
                                                    <td>
                                                        <?php if($staff->role === 'secretary'): ?>
                                                            <span class="badge bg-primary">Secretary</span>
                                                        <?php elseif($staff->role === 'clerk'): ?>
                                                            <span class="badge bg-warning text-dark">Clerk</span>
                                                        <?php elseif($staff->role === 'staff'): ?>
                                                            <span class="badge bg-info text-white">Staff</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary"><?php echo e(ucfirst(str_replace('_', ' ', $staff->role ?? 'No role'))); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo e($staff->law_office ?? 'No Office Assigned'); ?></td>
                                                    <td><?php echo e($staff->created_at->format('M d, Y')); ?></td> <!-- This should be CREATED date -->
                                                    <td>
                                                        <button onclick='openEditStaffModal(<?php echo json_encode($staff, 15, 512) ?>)' 
                                                                class="btn btn-warning btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button onclick="confirmDelete(<?php echo e($staff->id); ?>)" 
                                                                class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted">No staff users found.</td>
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

    

    <!-- Create Staff Modal -->
    <div id="createStaffModal" class="modal">
        <div class="modal-content">
            <h3>Create Staff User</h3>
            
            <?php if($errors->any()): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Validation Errors:</strong>
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo e(route('adminAccount.staff.create')); ?>" method="POST" enctype="multipart/form-data" id="createStaffForm">
                <?php echo csrf_field(); ?>
                
                <div class="mb-3">
                    <label for="staff_image" class="form-label">Profile Image:</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Name:</label>
                    <input type="text" name="name" required class="form-control">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" name="email" required class="form-control">
                </div>

                <div class="mb-3">
                    <label for="create_cp_number" class="form-label">Contact Number:</label>
                    <input type="text" name="cp_number" id="create_cp_number" required class="form-control" 
                       pattern="\d{11}" 
                       title="Contact number must be exactly 11 digits" 
                       maxlength="11" 
                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);">
                </div>

                <!-- Role dropdown -->
                <div class="mb-3">
                    <label for="create_role" class="form-label">Role:</label>
                    <select name="role" class="form-control" id="create_role" required>
                        <option value="secretary">Secretary</option>
                        <option value="clerk">Clerk</option>
                        <option value="staff">Staff</option>
                    </select>
                    <small class="text-muted">Select the staff member's role</small>
                </div>

                <input type="hidden" name="law_office_id" value="<?php echo e($user->law_office_id ?? ''); ?>">
                <div class="mb-3">
                    <label class="form-label">Law Office</label>
                    <div class="form-control-plaintext text-muted">
                        <?php echo e($user->law_office ?? 'No Office Assigned'); ?>

                    </div>
                    <small class="text-muted">The staff user will inherit the office of the current logged-in user.</small>
                </div>

                <!-- Password field with toggle and strength meter -->
                <div class="mb-3">
                    <label for="create_password" class="form-label">Password:</label>
                    <div class="input-group">
                        <input type="password" name="password" id="create_password" required class="form-control">
                        <button class="password-toggle-btn" type="button" id="toggleCreatePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    
                    <!-- Password strength meter -->
                    <div class="password-strength mt-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="form-text">Password strength:</small>
                            <small id="create_password_strength_text" class="form-text">No password</small>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar" id="create_password_strength_bar" role="progressbar" 
                                style="width: 0%; background-color: #ddd;"></div>
                        </div>
                        <small id="create_password_requirements" class="form-text text-muted">
                            <i class="fas fa-check-circle text-success" style="display: none;"></i>
                            <i class="fas fa-times-circle text-danger"></i> At least 8 characters
                        </small>
                        <small id="create_password_requirements2" class="form-text text-muted">
                            <i class="fas fa-times-circle text-danger"></i> Contains uppercase letter
                        </small>
                        <small id="create_password_requirements3" class="form-text text-muted">
                            <i class="fas fa-times-circle text-danger"></i> Contains lowercase letter
                        </small>
                        <small id="create_password_requirements4" class="form-text text-muted">
                            <i class="fas fa-times-circle text-danger"></i> Contains number
                        </small>
                        <small id="create_password_requirements5" class="form-text text-muted">
                            <i class="fas fa-times-circle text-danger"></i> Contains special character
                        </small>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">Create Staff</button>
                    <button type="button" onclick="closeCreateStaffModal()" class="btn btn-secondary">Cancel</button>
                </div>
                <!--<input type="hidden" name="debug_role" value="test">-->
            </form>
        </div>
    </div>

    <!-- Edit Staff Modal - Fix the password toggle button -->
    <div id="editStaffModal" class="modal">
        <div class="modal-content">
            <h3>Edit Staff User</h3>
            <form id="editStaffForm" method="POST" enctype="multipart/form-data" action="<?php echo e(route('adminAccount.staff.update', ['id' => '__ID__'])); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                
                <div class="mb-3">
                    <label for="edit_staff_image" class="form-label">Profile Image:</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <div class="mb-3">
                    <label for="edit_name" class="form-label">Name:</label>
                    <input type="text" name="name" id="edit_name" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="edit_email" class="form-label">Email:</label>
                    <input type="email" name="email" id="edit_email" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="edit_cp_number" class="form-label">Contact Number:</label>
                    <input type="text" name="cp_number" id="edit_cp_number" class="form-control" 
                       pattern="\d{11}" 
                       title="Contact number must be exactly 11 digits" 
                       maxlength="11" 
                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);">
                </div>

                <!-- Role dropdown -->
                <div class="mb-3">
                    <label for="edit_role" class="form-label">Role:</label>
                    <select name="role" id="edit_role" class="form-control">
                        <option value="secretary">Secretary</option>
                        <option value="clerk">Clerk</option>
                        <option value="staff">Staff</option>
                    </select>
                    <small class="text-muted">Select the staff member's role</small>
                </div>

                <input type="hidden" name="law_office_id" value="<?php echo e($user->law_office_id ?? ''); ?>">
                <div class="mb-3">
                    <label class="form-label">Law Office</label>
                    <div class="form-control-plaintext text-muted">
                        <?php echo e($user->law_office ?? 'No Office Assigned'); ?>

                    </div>
                    <small class="text-muted">The staff user will keep the office of the current logged-in user.</small>
                </div>

                <!-- Password field with toggle and strength meter -->
                <div class="mb-3">
                    <label for="edit_password" class="form-label">Password:</label>
                    <div class="input-group">
                        <input type="password" name="password" id="edit_password" placeholder="Leave blank to keep current" class="form-control">
                        <!-- FIXED: Changed id from "toggleCreatePassword" to "toggleEditPassword" -->
                        <button class="password-toggle-btn" type="button" id="toggleEditPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    
                    <!-- Password strength meter -->
                    <div class="password-strength mt-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="form-text">Password strength:</small>
                            <small id="edit_password_strength_text" class="form-text">No password</small>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar" id="edit_password_strength_bar" role="progressbar" 
                                style="width: 0%; background-color: #ddd;"></div>
                        </div>
                        <small id="edit_password_requirements" class="form-text text-muted">
                            <i class="fas fa-check-circle text-success" style="display: none;"></i>
                            <i class="fas fa-times-circle text-danger"></i> At least 8 characters
                        </small>
                        <small id="edit_password_requirements2" class="form-text text-muted">
                            <i class="fas fa-times-circle text-danger"></i> Contains uppercase letter
                        </small>
                        <small id="edit_password_requirements3" class="form-text text-muted">
                            <i class="fas fa-times-circle text-danger"></i> Contains lowercase letter
                        </small>
                        <small id="edit_password_requirements4" class="form-text text-muted">
                            <i class="fas fa-times-circle text-danger"></i> Contains number
                        </small>
                        <small id="edit_password_requirements5" class="form-text text-muted">
                            <i class="fas fa-times-circle text-danger"></i> Contains special character
                        </small>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">Update Staff</button>
                    <button type="button" onclick="closeEditStaffModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Delete</h3>
            <center>
            <p>Are you sure you want to delete this staff user?</p>
            </center>
            <form id="deleteForm" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger">Delete</button>
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menu-toggle');
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    document.getElementById('wrapper').classList.toggle('toggled');
                });
            }
        });

        // Modal functions for Admin
        function openAdminModal() {
            document.getElementById('adminModal').style.display = 'block';
        }

        function closeAdminModal() {
            document.getElementById('adminModal').style.display = 'none';
        }

        // Modal functions for Create Staff
        function openCreateStaffModal() {
            const modal = document.getElementById('createStaffModal');
            modal.style.display = 'block';
            setTimeout(() => modal.classList.add('modal-show'), 20);
        }

        function closeCreateStaffModal() {
            const modal = document.getElementById('createStaffModal');
            modal.classList.remove('modal-show');
            setTimeout(() => { modal.style.display = 'none'; }, 260);
        }

        // Modal functions for Edit Staff
        function openEditStaffModal(staff) {
            const modal = document.getElementById('editStaffModal');
            modal.style.display = 'block';
            // allow CSS transition
            setTimeout(() => modal.classList.add('modal-show'), 20);
            document.getElementById('edit_name').value = staff.name || '';
            document.getElementById('edit_email').value = staff.email || '';
            document.getElementById('edit_cp_number').value = staff.cp_number || '';
            
            // Populate role dropdown
            const roleSelect = document.getElementById('edit_role');
            roleSelect.value = staff.role || 'staff'; // Default to 'staff' if null
            
            // Clear password field on modal open
            document.getElementById('edit_password').value = '';
            
            // Reset password strength display
            document.getElementById('edit_password_strength_bar').style.width = '0%';
            document.getElementById('edit_password_strength_text').textContent = 'No password';
            
            // Set form action using Laravel route
            const form = document.getElementById('editStaffForm');
            form.action = '<?php echo e(route("adminAccount.staff.update", ["id" => "__ID__"])); ?>'.replace('__ID__', staff.id);
        }

        function closeEditStaffModal() {
            const modal = document.getElementById('editStaffModal');
            modal.classList.remove('modal-show');
            setTimeout(() => { modal.style.display = 'none'; }, 260);
        }

        // Modal functions for Delete Confirmation
        function confirmDelete(staffId) {
            const modal = document.getElementById('deleteModal');
            modal.style.display = 'block';
            // allow CSS transition to run
            setTimeout(() => modal.classList.add('modal-show'), 20);
            const form = document.getElementById('deleteForm');
            form.action = '<?php echo e(route("adminAccount.staff.delete", ["id" => "__ID__"])); ?>'.replace('__ID__', staffId);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            // play hide animation then remove from display
            modal.classList.remove('modal-show');
            setTimeout(() => { modal.style.display = 'none'; }, 260);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = ['adminModal', 'createStaffModal', 'editStaffModal', 'deleteModal'];
            modals.forEach(modalId => {
                let modal = document.getElementById(modalId);
                if (event.target == modal) {
                    if (modalId === 'createStaffModal') closeCreateStaffModal();
                    else if (modalId === 'editStaffModal') closeEditStaffModal();
                    else if (modalId === 'deleteModal') closeDeleteModal();
                    else modal.style.display = "none";
                }
            });
        }

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Password strength checker function
        function checkPasswordStrength(password) {
            let strength = 0;
            let requirements = {
                length: false,
                uppercase: false,
                lowercase: false,
                number: false,
                special: false
            };
            
            // Check length
            if (password.length >= 8) {
                strength += 1;
                requirements.length = true;
            }
            
            // Check uppercase letters
            if (/[A-Z]/.test(password)) {
                strength += 1;
                requirements.uppercase = true;
            }
            
            // Check lowercase letters
            if (/[a-z]/.test(password)) {
                strength += 1;
                requirements.lowercase = true;
            }
            
            // Check numbers
            if (/[0-9]/.test(password)) {
                strength += 1;
                requirements.number = true;
            }
            
            // Check special characters
            if (/[^A-Za-z0-9]/.test(password)) {
                strength += 1;
                requirements.special = true;
            }
            
            return { strength, requirements };
        }
        // Update password strength display
        function updatePasswordStrength(password, prefix = 'create') {
            const { strength, requirements } = checkPasswordStrength(password);
            const bar = document.getElementById(`${prefix}_password_strength_bar`);
            const text = document.getElementById(`${prefix}_password_strength_text`);
            
            // Update progress bar
            const percentage = (strength / 5) * 100;
            bar.style.width = `${percentage}%`;
            
            // Update color and text based on strength
            let color, strengthText;
            if (strength === 0) {
                color = '#ddd';
                strengthText = 'No password';
            } else if (strength <= 2) {
                color = '#dc3545'; // Red
                strengthText = 'Weak';
            } else if (strength <= 3) {
                color = '#ffc107'; // Yellow
                strengthText = 'Fair';
            } else if (strength <= 4) {
                color = '#28a745'; // Green
                strengthText = 'Good';
            } else {
                color = '#17a2b8'; // Blue/Teal
                strengthText = 'Strong';
            }
            
            bar.style.backgroundColor = color;
            text.textContent = strengthText;
            text.style.color = color;
            
            // Update requirement checkmarks
            const icons = {
                length: document.getElementById(`${prefix}_password_requirements`),
                uppercase: document.getElementById(`${prefix}_password_requirements2`),
                lowercase: document.getElementById(`${prefix}_password_requirements3`),
                number: document.getElementById(`${prefix}_password_requirements4`),
                special: document.getElementById(`${prefix}_password_requirements5`)
            };
            
            // Update each requirement icon
            if (requirements.length) {
                icons.length.innerHTML = '<i class="fas fa-check-circle text-success"></i> At least 8 characters';
            } else {
                icons.length.innerHTML = '<i class="fas fa-times-circle text-danger"></i> At least 8 characters';
            }
            
            if (requirements.uppercase) {
                icons.uppercase.innerHTML = '<i class="fas fa-check-circle text-success"></i> Contains uppercase letter';
            } else {
                icons.uppercase.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Contains uppercase letter';
            }
            
            if (requirements.lowercase) {
                icons.lowercase.innerHTML = '<i class="fas fa-check-circle text-success"></i> Contains lowercase letter';
            } else {
                icons.lowercase.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Contains lowercase letter';
            }
            
            if (requirements.number) {
                icons.number.innerHTML = '<i class="fas fa-check-circle text-success"></i> Contains number';
            } else {
                icons.number.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Contains number';
            }
            
            if (requirements.special) {
                icons.special.innerHTML = '<i class="fas fa-check-circle text-success"></i> Contains special character';
            } else {
                icons.special.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Contains special character';
            }
        }
        // Toggle password visibility
        function togglePasswordVisibility(inputId, buttonId) {
            const passwordInput = document.getElementById(inputId);
            const toggleButton = document.getElementById(buttonId);
            const icon = toggleButton.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                // When password is visible (text), show EYE icon (no slash)
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                toggleButton.setAttribute('aria-label', 'Hide password');
            } else {
                passwordInput.type = 'password';
                // When password is hidden (dotted), show EYE-SLASH icon (with slash)
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                toggleButton.setAttribute('aria-label', 'Show password');
            }
        }


        // Initialize password strength and toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Create staff modal
            const createPasswordInput = document.getElementById('create_password');
            const toggleCreatePasswordBtn = document.getElementById('toggleCreatePassword');
            
            if (createPasswordInput && toggleCreatePasswordBtn) {
                createPasswordInput.addEventListener('input', function() {
                    updatePasswordStrength(this.value, 'create');
                });
                
                toggleCreatePasswordBtn.addEventListener('click', function() {
                    togglePasswordVisibility('create_password', 'toggleCreatePassword');
                });
                
                // Initialize with EYE-SLASH icon (with slash) - password is hidden by default
                const createIcon = toggleCreatePasswordBtn.querySelector('i');
                createIcon.classList.remove('fa-eye');
                createIcon.classList.add('fa-eye-slash');
            }
            
            // Edit staff modal
            const editPasswordInput = document.getElementById('edit_password');
            const toggleEditPasswordBtn = document.getElementById('toggleEditPassword');
            
            if (editPasswordInput && toggleEditPasswordBtn) {
                editPasswordInput.addEventListener('input', function() {
                    updatePasswordStrength(this.value, 'edit');
                });
                
                toggleEditPasswordBtn.addEventListener('click', function() {
                    togglePasswordVisibility('edit_password', 'toggleEditPassword');
                });
                
                // Initialize with EYE-SLASH icon (with slash) - password is hidden by default
                const editIcon = toggleEditPasswordBtn.querySelector('i');
                editIcon.classList.remove('fa-eye');
                editIcon.classList.add('fa-eye-slash');
            }
        });

    </script>
    <script>
function showLogoutModal() {
    const modalElement = document.getElementById('logoutConfirmationModal');
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: true
    });
    modal.show();
}
</script>
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

// Function to load law offices into dropdowns
async function loadLawOffices() {
    try {
        const response = await fetch('/api/law-offices', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            }
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const data = await response.json();
        if (data.status === 'success') {
            populateLawOfficeDropdowns(data.data);
        } else {
            console.error('Failed to load law offices:', data);
        }
    } catch (error) {
        console.error('Error loading law offices:', error);
    }
}

// Function to populate law office dropdowns
function populateLawOfficeDropdowns(offices) {
    const createDropdown = document.getElementById('create_law_office');
    const editDropdown = document.getElementById('edit_law_office');
    
    // Clear existing options except the placeholder and "Add New" option
    if (createDropdown) {
        createDropdown.innerHTML = '<option value="">Select Law Office</option><option value="new">+ Add New Law Office</option>';
        offices.forEach(office => {
            const option = document.createElement('option');
            option.value = office.id;
            option.textContent = office.law_office;
            createDropdown.appendChild(option);
        });
    }
    
    if (editDropdown) {
        editDropdown.innerHTML = '<option value="">Select Law Office</option><option value="new">+ Add New Law Office</option>';
        offices.forEach(office => {
            const option = document.createElement('option');
            option.value = office.id;
            option.textContent = office.law_office;
            editDropdown.appendChild(option);
        });
    }
}

// Load law offices when page loads and update edit modal
document.addEventListener('DOMContentLoaded', function() {
    loadLawOffices();
    
    // Handle law office dropdown change for create form
    const createLawOfficeDropdown = document.getElementById('create_law_office');
    const newLawOfficeContainer = document.getElementById('newLawOfficeContainer');
    const newLawOfficeInput = document.getElementById('new_law_office');
    
    if (createLawOfficeDropdown) {
        createLawOfficeDropdown.addEventListener('change', function() {
            if (this.value === 'new') {
                newLawOfficeContainer.style.display = 'block';
                newLawOfficeInput.required = true;
                newLawOfficeInput.focus();
            } else {
                newLawOfficeContainer.style.display = 'none';
                newLawOfficeInput.required = false;
                newLawOfficeInput.value = '';
            }
        });
    }
    
    // Handle law office dropdown change for edit form
    const editLawOfficeDropdown = document.getElementById('edit_law_office');
    const editNewLawOfficeContainer = document.getElementById('editNewLawOfficeContainer');
    const editNewLawOfficeInput = document.getElementById('edit_new_law_office');
    
    if (editLawOfficeDropdown) {
        editLawOfficeDropdown.addEventListener('change', function() {
            if (this.value === 'new') {
                editNewLawOfficeContainer.style.display = 'block';
                editNewLawOfficeInput.required = true;
                editNewLawOfficeInput.focus();
            } else {
                editNewLawOfficeContainer.style.display = 'none';
                editNewLawOfficeInput.required = false;
                editNewLawOfficeInput.value = '';
            }
        });
    }
});
// Add this to your existing script section
document.addEventListener('DOMContentLoaded', function() {
    // Handle image loading errors
    document.querySelectorAll('.staff-image').forEach(img => {
        img.onerror = function() {
            console.log('Image failed to load:', this.src);
            // Force reload with staff_images path
            const currentSrc = this.src;
            if (!currentSrc.includes('staff_images/')) {
                const filename = currentSrc.split('/').pop();
                this.src = '<?php echo e(url("staff_images")); ?>/' + filename;
            } else {
                this.src = '<?php echo e(asset("staff_images/default-avatar.png")); ?>';
            }
        };
    });
    
    // Also check for images that might be broken on page load
    setTimeout(function() {
        document.querySelectorAll('.staff-image').forEach(img => {
            // Create a test image to check if the src loads
            const testImg = new Image();
            testImg.onload = function() {
                // Image loads fine
            };
            testImg.onerror = function() {
                // Image failed to load
                img.src = '<?php echo e(asset("staff_images/default-avatar.png")); ?>';
            };
            testImg.src = img.src;
        });
    }, 1000);
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Debug: Check table structure
        console.log('Table rows:', document.querySelectorAll('tbody tr').length);
        
        // Check if badges are in the right place
        document.querySelectorAll('td .badge').forEach((badge, index) => {
            const td = badge.closest('td');
            const columnIndex = Array.from(td.parentNode.children).indexOf(td);
            console.log(`Badge ${index}: Column ${columnIndex}, Text: ${badge.textContent}`);
        });
    });
</script>
<script>
// Removed verification modal auto-open logic
</script>
<?php echo $__env->make('partials.notification-badge-visibility', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\adminAccount.blade.php ENDPATH**/ ?>