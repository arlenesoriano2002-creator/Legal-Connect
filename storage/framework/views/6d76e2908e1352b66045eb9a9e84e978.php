<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <title>Admin Dashboard</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="<?php echo e(asset('css/admin-account-setting/adminAccountSetting.css')); ?>">
    
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
                    class="list-group-item list-group-item-action <?php echo e(request()->is('email-chat') || request()->is('sms-chat') || request()->is('messages/*') ? 'active' : ''); ?>"
                    data-bs-toggle="collapse" 
                    aria-expanded="<?php echo e(request()->is('email-chat') || request()->is('sms-chat') || request()->is('messages/*') ? 'true' : 'false'); ?>">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu collapse <?php echo e(request()->is('email-chat') || request()->is('sms-chat') || request()->is('messages/*') ? 'show' : ''); ?> list-group" id="messagesSubmenu">
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
                 <!-- Log Out -->
                <form id="logout-form" action="<?php echo e(route('custom.logout')); ?>" method="POST" style="display: none;">
                    <?php echo csrf_field(); ?>
                </form>
                    <button type="button" class="btn logout-btn" onclick="showLogoutModal()">
                        <i class="fas fa-sign-out-alt"></i> Log out
                    </button>
            </nav>
            
            <!-- Account Settings Content -->
            <div class="container-fluid mt-4">
                 <!-- Toast Notification Container -->
                     <div id="toastContainer" style="position: fixed; top: 80px; right: 20px; z-index: 9999;"></div>
                <div class="page-description">
                    <h4 class="fw-bold">Account Settings</h4>
                    <p class="text-muted">Edit your name, avatar etc.</p>
                </div>
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-xl-8">
                        <div class="settings-wrapper">
                            <div class="settings-header mb-4">
                                <!-- Session messages will now appear as toasts -->
                            </div>

                            <!-- Profile Update Form -->
                            <form method="POST" action="<?php echo e(route('admin.account.settings.update.profile')); ?>" enctype="multipart/form-data" id="profileForm">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <!-- Test button (add temporarily to verify) 
                                <button type="button" onclick="showToast('Test message!', 'success')" class="btn btn-info btn-sm mt-2">
                                    Test Toast Notification
                                </button>-->
                                    <div class="settings-card mb-4">
                                    <div class="row">
                                        <!-- LEFT: Form Fields -->
                                        <div class="col-md-8">
                                            <div class="first-left-content">
                                                <div class="first-left-section">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label fw-semibold">Your Name</label>
                                                        <div class="input-wrapper">
                                                            <input type="text" name="name" class="form-control" value="<?php echo e(old('name', $user->name)); ?>" required>
                                                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                                <span class="text-danger"><?php echo e($message); ?></span>
                                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                        </div>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label class="form-label fw-semibold">Email Address</label>
                                                        <div class="input-wrapper">
                                                            <input type="email" name="email" class="form-control" value="<?php echo e(old('email', $user->email)); ?>" required>
                                                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                                <span class="text-danger"><?php echo e($message); ?></span>
                                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="second-left-section">
                                                        <div class="form-group mb-3">
                                                            <label class="form-label fw-semibold">Phone Number</label>
                                                            <div class="input-wrapper">
                                                                <input type="text" name="cp_number" class="form-control" value="<?php echo e(old('cp_number', $user->cp_number)); ?>" placeholder="+639xxxxxxxxx">
                                                                <?php $__errorArgs = ['cp_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                                    <span class="text-danger"><?php echo e($message); ?></span>
                                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                            </div>
                                                        </div>

                                                        <!-- Password Display Section -->

                                                        <div class="form-group mb-3">
                                                            <label class="form-label fw-semibold">Current Password</label>
                                                            <div class="password-input-group">
                                                                <input type="password" id="passwordPlaceholder" readonly value="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢">
                                                                <button type="button" class="password-toggle" onclick="togglePlaceholderPassword()">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                            </div>
                                                            <small class="text-muted">Passwords are encrypted for security. Click "Change Password" to update.</small>
                                                        </div>
                                                </div>
                                            </div>
                                            
                                            
                                            

                                            <!-- Password Update Section -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-semibold">Update Password</label>
                                                <div class="password-status">
                                                    <span class="password-indicator">
                                                        <i class="fas fa-shield-alt"></i> Password Active
                                                    </span>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="showChangePasswordModal()">
                                                        <i class="fas fa-key me-1"></i>Change Password
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="actions d-flex gap-2">
                                                <button type="button" class="btn btn-secondary btn-lg w-50" onclick="resetForm()">Cancel</button>
                                                <button type="submit" class="btn btn-save btn-lg w-50">Update Profile</button>
                                            </div>
                                        </div>

                                        <!-- RIGHT: Avatar Section -->
                                        <div class="col-md-4 text-center">
                                            <div class="avatar-section">
                                                <div class="avatar mb-3" id="avatarPreview">
                                                    <?php if($user->image): ?>
                                                        <img src="<?php echo e(asset('storage/' . $user->image)); ?>" alt="<?php echo e($user->name); ?>" id="avatarImage" class="img-fluid rounded-circle">
                                                    <?php else: ?>
                                                        <div class="default-avatar">
                                                            <i class="fas fa-user fa-4x text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <input type="file" name="image" id="imageUpload" accept="image/*" style="display: none;" onchange="previewImage(event)">
                                                <button type="button" class="btn btn-outline-primary w-100" onclick="document.getElementById('imageUpload').click()">
                                                    <i class="fas fa-upload me-2"></i>Upload a picture
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
        <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="changePasswordModalLabel">
                            <i class="fas fa-key me-2"></i>Change Password
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="<?php echo e(route('admin.account.settings.update.password')); ?>" id="passwordForm">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <div class="modal-body">
                            <!-- Display form errors -->
                            <?php if($errors->any()): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li><?php echo e($error); ?></li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <div class="settings-card">
                                <div class="form-group mb-3">
                                    <label class="form-label">Current Password</label>
                                    <div class="input-wrapper">
                                        <input type="password" name="current_password" class="form-control" id="currentPasswordInput" required>
                                        <button type="button" class="password-toggle-btn">
                                            <i class="fas fa-eye-slash"></i>
                                        </button>
                                        <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="text-danger"><?php echo e($message); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="mt-2">
                                        <a href="<?php echo e(route('admin.account.settings.forgot-password.email')); ?>" class="text-decoration-none small fw-semibold">
                                            Forgot Password?
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">New Password</label>
                                    <div class="input-wrapper">
                                        <input type="password" name="new_password" class="form-control" id="newPasswordInput" required>
                                        <button type="button" class="password-toggle-btn">
                                            <i class="fas fa-eye-slash"></i>
                                        </button>
                                        <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="text-danger"><?php echo e($message); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <!-- Password Strength Indicator -->
                                    <div class="password-strength mt-2" id="passwordStrength">
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar" id="passwordStrengthBar" role="progressbar" style="width: 0%; transition: width 0.3s;"></div>
                                        </div>
                                        <small class="text-muted d-block mt-1" id="passwordStrengthText">Password strength: None</small>
                                        <small class="text-muted d-block mt-1" id="passwordRequirements">
                                            <i class="fas fa-info-circle"></i> Must be at least 8 characters with uppercase, lowercase, number, and special character
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-4">
                                    <label class="form-label">Confirm New Password</label>
                                    <div class="input-wrapper">
                                        <input type="password" name="new_password_confirmation" class="form-control" id="confirmPasswordInput" required>
                                        <button type="button" class="password-toggle-btn">
                                            <i class="fas fa-eye-slash"></i>
                                        </button>
                                        <!-- Password Match Indicator -->
                                        <div class="password-match-feedback mt-2" id="passwordMatchFeedback">
                                            <small class="text-danger" id="passwordMismatchError" style="display: none;">
                                                <i class="fas fa-times-circle"></i> Passwords do not match
                                            </small>
                                            <small class="text-success" id="passwordMatchSuccess" style="display: none;">
                                                <i class="fas fa-check-circle"></i> Passwords match
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-save" id="savePasswordBtn" disabled>Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <!-- Logout Confirmation Modal -->
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Per-Tab Authentication Manager (sends tab_token header on all requests) -->
    <script src="<?php echo e(asset('js/per-tab-auth-manager.js')); ?>"></script>
    
    <script>
    // Toggle functionality
    document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.getElementById('wrapper');
        const menuToggle = document.getElementById('menu-toggle');
        const topBar = document.querySelector('.top-bar');

        function updateTopBarPosition() {
            if (!wrapper || !topBar) return;
            topBar.style.left = wrapper.classList.contains('toggled') ? '70px' : '220px';
        }

        if (menuToggle && wrapper) {
            menuToggle.addEventListener('click', function (e) {
                e.preventDefault();
                wrapper.classList.toggle('toggled');
                updateTopBarPosition();
            });
        }

        updateTopBarPosition();
        window.addEventListener('resize', updateTopBarPosition);
        
        // Initialize password toggle functionality using event delegation
        document.addEventListener('click', function(event) {
            // Handle password toggle buttons in the modal
            if (event.target.closest('#changePasswordModal .password-toggle-btn')) {
                event.preventDefault();
                event.stopPropagation();
                const button = event.target.closest('#changePasswordModal .password-toggle-btn');
                togglePasswordVisibility(button);
            }
            
            // Handle the main form's password toggle
            if (event.target.closest('.password-toggle') && !event.target.closest('#changePasswordModal')) {
                event.preventDefault();
                event.stopPropagation();
                const button = event.target.closest('.password-toggle');
                const input = button.parentElement.querySelector('input');
                const icon = button.querySelector('i');
                
                if (input && icon) {
                    if (input.type === 'password') {
                        input.type = 'text';
                        input.value = 'Password is encrypted';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        input.value = 'â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }
            }
        });
        
        // Set up password strength and matching listeners
        const newPasswordInput = document.getElementById('newPasswordInput');
        const confirmPasswordInput = document.getElementById('confirmPasswordInput');
        
        if (newPasswordInput) {
            newPasswordInput.addEventListener('input', function() {
                updatePasswordStrength(this.value);
                checkPasswordMatch();
                validatePasswordForm();
            });
        }
        
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                checkPasswordMatch();
                validatePasswordForm();
            });
        }
        
        // Form submission validation
        const passwordForm = document.getElementById('passwordForm');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(event) {
                const newPassword = document.getElementById('newPasswordInput').value;
                const confirmPassword = document.getElementById('confirmPasswordInput').value;
                
                // Final validation before submission
                const isStrongEnough = updatePasswordStrength(newPassword) >= 3;
                const passwordsMatch = checkPasswordMatch();
                
                if (!isStrongEnough) {
                    event.preventDefault();
                    alert('Please use a stronger password (at least 8 characters with uppercase, lowercase, number, and special character).');
                    return;
                }
                
                if (!passwordsMatch) {
                    event.preventDefault();
                    alert('Passwords do not match. Please make sure both password fields are identical.');
                    return;
                }
            });
        }
    });

    // Toggle password visibility - Simple version
    function togglePasswordVisibility(button) {
        const input = button.parentElement.querySelector('input');
        const icon = button.querySelector('i');
        
        if (!input) {
            console.error('Input not found for toggle button');
            return;
        }
        
        if (!icon) {
            console.error('Icon not found for toggle button');
            return;
        }
        
        if (input.type === 'password') {
            // Show password: change to text and show regular eye (no slash)
            input.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
            // Add visual feedback
            button.style.color = '#19aa8d';
            setTimeout(() => {
                button.style.color = '#6c757d';
            }, 300);
        } else {
            // Hide password: change to password and show eye with slash
            input.type = 'password';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
            // Add visual feedback
            button.style.color = '#6c757d';
        }
    }

    // Check password strength
    function checkPasswordStrength(password) {
        let strength = 0;
        
        // Length check
        if (password.length >= 8) {
            strength += 1;
        }
        
        // Contains uppercase
        if (/[A-Z]/.test(password)) {
            strength += 1;
        }
        
        // Contains lowercase
        if (/[a-z]/.test(password)) {
            strength += 1;
        }
        
        // Contains numbers
        if (/[0-9]/.test(password)) {
            strength += 1;
        }
        
        // Contains special characters
        if (/[^A-Za-z0-9]/.test(password)) {
            strength += 1;
        }
        
        return strength;
    }

    // Update password strength UI
    function updatePasswordStrength(password) {
        const strengthBar = document.getElementById('passwordStrengthBar');
        const strengthText = document.getElementById('passwordStrengthText');
        
        if (!password) {
            strengthBar.className = 'progress-bar';
            strengthBar.style.width = '0%';
            strengthText.textContent = 'Password strength: None';
            return 0;
        }
        
        const strength = checkPasswordStrength(password);
        
        // Update progress bar
        strengthBar.className = 'progress-bar';
        
        if (strength <= 1) {
            strengthBar.classList.add('strength-weak');
            strengthBar.style.width = '25%';
            strengthText.textContent = 'Password strength: Weak';
            strengthText.className = 'text-danger';
        } else if (strength === 2) {
            strengthBar.classList.add('strength-fair');
            strengthBar.style.width = '50%';
            strengthText.textContent = 'Password strength: Fair';
            strengthText.className = 'text-warning';
        } else if (strength === 3) {
            strengthBar.classList.add('strength-good');
            strengthBar.style.width = '75%';
            strengthText.textContent = 'Password strength: Good';
            strengthText.className = 'text-info';
        } else if (strength >= 4) {
            strengthBar.classList.add('strength-strong');
            strengthBar.style.width = '100%';
            strengthText.textContent = 'Password strength: Strong';
            strengthText.className = 'text-success';
        }
        
        return strength;
    }

    // Check if passwords match
    function checkPasswordMatch() {
        const newPassword = document.getElementById('newPasswordInput').value;
        const confirmPassword = document.getElementById('confirmPasswordInput').value;
        const mismatchError = document.getElementById('passwordMismatchError');
        const matchSuccess = document.getElementById('passwordMatchSuccess');
        
        if (!newPassword || !confirmPassword) {
            if (mismatchError) mismatchError.style.display = 'none';
            if (matchSuccess) matchSuccess.style.display = 'none';
            return false;
        }
        
        if (newPassword === confirmPassword) {
            if (mismatchError) mismatchError.style.display = 'none';
            if (matchSuccess) matchSuccess.style.display = 'block';
            return true;
        } else {
            if (mismatchError) mismatchError.style.display = 'block';
            if (matchSuccess) matchSuccess.style.display = 'none';
            return false;
        }
    }

    // Validate form before submission
    function validatePasswordForm() {
        const newPassword = document.getElementById('newPasswordInput').value;
        const confirmPassword = document.getElementById('confirmPasswordInput').value;
        const saveBtn = document.getElementById('savePasswordBtn');
        
        if (!saveBtn) return false;
        
        const strength = updatePasswordStrength(newPassword);
        const passwordsMatch = checkPasswordMatch();
        
        // Enable save button only if:
        // 1. Password is strong enough (at least "Good" level, which is 3+)
        // 2. Passwords match
        // 3. Both fields have values
        if (strength >= 3 && passwordsMatch && newPassword && confirmPassword) {
            saveBtn.disabled = false;
            return true;
        } else {
            saveBtn.disabled = true;
            return false;
        }
    }

    // Show change password modal
    function showChangePasswordModal() {
        const changePasswordModal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
        changePasswordModal.show();
        
        // Clear previous errors when modal opens
        const errorMessages = document.querySelectorAll('#changePasswordModal .text-danger');
        errorMessages.forEach(el => el.textContent = '');
        
        // Clear form fields
        const form = document.getElementById('passwordForm');
        if (form) {
            form.reset();
            
            // Reset UI elements
            const strengthBar = document.getElementById('passwordStrengthBar');
            const strengthText = document.getElementById('passwordStrengthText');
            const mismatchError = document.getElementById('passwordMismatchError');
            const matchSuccess = document.getElementById('passwordMatchSuccess');
            const saveBtn = document.getElementById('savePasswordBtn');
            
            if (strengthBar) {
                strengthBar.className = 'progress-bar';
                strengthBar.style.width = '0%';
            }
            if (strengthText) strengthText.textContent = 'Password strength: None';
            if (mismatchError) mismatchError.style.display = 'none';
            if (matchSuccess) matchSuccess.style.display = 'none';
            if (saveBtn) saveBtn.disabled = true;
            
            // Reset all password inputs to type="password" and set icon to fa-eye-slash
            const passwordInputs = form.querySelectorAll('input');
            passwordInputs.forEach(input => {
                if (input.name.includes('password')) {
                    input.type = 'password';
                    const toggleBtn = input.parentElement.querySelector('.password-toggle-btn');
                    if (toggleBtn) {
                        const icon = toggleBtn.querySelector('i');
                        if (icon) {
                            icon.classList.remove('fa-eye');
                            icon.classList.add('fa-eye-slash');
                        }
                    }
                }
            });
        }
    }

    // Preview image before upload
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById('avatarPreview');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" id="avatarImage" class="img-fluid rounded-circle" alt="Preview">`;
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Logout modal function
    function showLogoutModal() {
        const logoutModal = new bootstrap.Modal(document.getElementById('logoutConfirmationModal'));
        logoutModal.show();
    }

    // Keyboard shortcut for logout (Ctrl+Q)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'q') {
            e.preventDefault();
            showLogoutModal();
        }
    });
    
    // Simple function for toggling placeholder password
    function togglePlaceholderPassword() {
        const input = document.getElementById('passwordPlaceholder');
        const icon = event.currentTarget.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            input.value = 'Password is encrypted';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            input.value = 'â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Reset form to original state
    function resetForm() {
        // Get the original values from the server (stored in data attributes)
        const originalName = '<?php echo e(old("name", $user->name)); ?>';
        const originalEmail = '<?php echo e(old("email", $user->email)); ?>';
        const originalPhone = '<?php echo e(old("cp_number", $user->cp_number)); ?>';
        
        // Reset text inputs to original values
        const nameInput = document.querySelector('input[name="name"]');
        const emailInput = document.querySelector('input[name="email"]');
        const phoneInput = document.querySelector('input[name="cp_number"]');
        
        if (nameInput) nameInput.value = originalName;
        if (emailInput) emailInput.value = originalEmail;
        if (phoneInput) phoneInput.value = originalPhone;
        
        // Reset the image upload input
        const imageUpload = document.getElementById('imageUpload');
        if (imageUpload) imageUpload.value = '';
        
        // Reset avatar preview to original image
        const avatarPreview = document.getElementById('avatarPreview');
        <?php if($user->image): ?>
            if (avatarPreview) {
                avatarPreview.innerHTML = '<img src="<?php echo e(asset("storage/" . $user->image)); ?>" alt="<?php echo e($user->name); ?>" id="avatarImage" class="img-fluid rounded-circle">';
            }
        <?php else: ?>
            if (avatarPreview) {
                avatarPreview.innerHTML = '<div class="default-avatar"><i class="fas fa-user fa-4x text-muted"></i></div>';
            }
        <?php endif; ?>
        
        // Clear validation errors
        const errorSpans = document.querySelectorAll('.text-danger');
        errorSpans.forEach(span => span.textContent = '');
        
        // Clear success/error messages
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => alert.remove());
        
        // Show success message that form was reset
        const settingsHeader = document.querySelector('.settings-header');
        if (settingsHeader) {
            // Show success message as toast
            showToast('Form has been reset to original values.', 'info');
        }
    }

// Show toast notification (with 15-second timeout, no sliding)
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer');
    
    if (!toastContainer) {
        console.error('Toast container not found.');
        return;
    }
    
    // Remove any existing toasts first
    const existingToasts = toastContainer.querySelectorAll('.toast-alert');
    existingToasts.forEach(toast => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    });
    
    // Create toast element
    const toastEl = document.createElement('div');
    toastEl.className = `toast-alert alert alert-${type} alert-dismissible fade show`;
    toastEl.setAttribute('role', 'alert');
    
    // Set toast content
    toastEl.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="font-size: 12px;"></button>
    `;
    
    // Append to container
    toastContainer.appendChild(toastEl);
    
    // Auto-dismiss after 15 seconds (15000 milliseconds)
    const dismissTimeout = setTimeout(() => {
        if (toastEl.parentNode === toastContainer) {
            toastEl.classList.remove('show');
            setTimeout(() => {
                if (toastEl.parentNode === toastContainer) {
                    toastEl.remove();
                }
            }, 300);
        }
    }, 5000); // 15 seconds
    
    // Clear timeout if user closes toast manually
    const closeBtn = toastEl.querySelector('.btn-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            clearTimeout(dismissTimeout);
        });
    }
    
    // Also clear timeout when Bootstrap dismisses it
    toastEl.addEventListener('closed.bs.alert', () => {
        clearTimeout(dismissTimeout);
    });
}

// Remove the progress bar style creation (delete these lines):
// const style = document.createElement('style');
// style.textContent = `
//     @keyframes progressBar {
//         from { transform: translateX(0%); }
//         to { transform: translateX(-100%); }
//     }
// `;
// document.head.appendChild(style);

    /// Check for session messages on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check for Laravel session messages
    <?php if(session('success')): ?>
        showToast('<?php echo e(session('success')); ?>', 'success');
    <?php endif; ?>
    
    <?php if(session('error')): ?>
        showToast('<?php echo e(session('error')); ?>', 'danger');
    <?php endif; ?>
    
    // Also check for form submission success (if you have validation errors, etc.)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        showToast('Profile updated successfully!', 'success');
    }
});
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
    
    // Initialize session hijack detection
    SessionHijackDetector.init(<?php echo e(auth()->id()); ?>);
    
    // Existing code continues...
});
</script>

<!-- Session Hijack Detection -->
<script src="<?php echo e(asset('js/session-hijack-detector.js')); ?>"></script>

<?php echo $__env->make('partials.notification-badge-visibility', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\admin-account-setting\adminAccountSetting.blade.php ENDPATH**/ ?>