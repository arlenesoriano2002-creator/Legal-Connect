<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Admin Dashboard</title>
    
    <!-- Remove the Tailwind CDN and use only Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    {{-- Global Error Handler --}}
    @include('partials.global-error-handler')
    
    <link rel="stylesheet" href="{{ asset('css/staff/staffAccountSetting.blade.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-account-setting/adminAccountSetting.css') }}">

</head>
<body>
    <div id="wrapper">
       <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">
                <img src="{{ asset('logo6.png') }}" alt="LegalConnect logo" width="40" height="40" style="border-radius: 50%;">
                <span>LegalConnect</span>
            </div>
            <div class="list-group list-group-flush">
                <!-- Dashboard link - Already correct -->
                <a href="{{ route('dashboardStaff') }}" class="list-group-item list-group-item-action {{ request()->routeIs('dashboardStaff') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                
                <!-- Set Time link - Already correct -->
                <a href="{{ route('staff') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff') ? 'active' : '' }}">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Set Time</span>
                </a>
                
                <!-- Walk-ins logs - Need to create route in web.php -->
                <a href="{{ route('staff.walkins.logs') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.walkins.logs') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard" style="color: #cdd3df;"></i>
                    <span>Walk-ins logs</span>
                </a>
                
                <!-- Feedbacks - Need to create route in web.php -->
                <a href="{{ route('staff.feedback.reports') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.feedback.reports') ? 'active' : '' }}">
                    <i class="fa-solid fa-comments" style="color: #d7dae0;"></i>
                    <span>Feedbacks</span>
                </a>
                
                <!-- Pending Requests - Already has route -->
                <a href="{{ route('staff.clients.pending') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.clients.pending') ? 'active' : '' }}">
                    <i class="fas fa-clock"></i>
                    <span>Pending Requests</span>
                </a>
                
                <!-- Accepted Requests - Already has route -->
                <a href="{{ route('staff.acceptedRequests') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.acceptedRequests') ? 'active' : '' }}">
                    <i class="fas fa-check-circle"></i>
                    <span>Accepted Requests</span>
                </a>
                
                <!-- Denied Requests - Already has route -->
                <a href="{{ route('staff.deniedRequests') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.deniedRequests') ? 'active' : '' }}">
                    <i class="fas fa-times-circle"></i>
                    <span>Denied Requests</span>
                </a>
                <a href="{{ route('diffun.message.inquiries') }}" class="list-group-item list-group-item-action {{ request()->routeIs('diffun.message.inquiries') ? 'active' : '' }}">
                    <i class="fas fa-envelope"></i>
                    <span>Message Inquiries</span>
                </a>

                <!-- Account Setting - Need to create route in web.php -->
                <a href="{{ route('staff.account.settings') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.account.settings') ? 'active' : '' }}">
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
                {{--
                <div class="notification-container">
                    <button class="notification-btn" id="messageNotificationBtn">
                        <img src="{{ asset('notification-bell.png') }}" alt="Messages" width="20" height="20">
                        <span class="badge" id="messageNotificationBadge">0</span>
                    </button>
                    
                    <div class="notification-dropdown" id="messageNotificationDropdown">
                        <div class="notification-header">
                            <h4>Message Notifications</h4>
                            <div class="notification-actions">
                                <button class="btn btn-sm btn-link" id="markAllMessageReadBtn">Mark all as read</button>
                                <button class="btn btn-sm btn-link" id="refreshMessageNotificationsBtn">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="notification-list" id="messageNotificationList">
                            <div class="notification-empty">
                                <i class="fas fa-bell-slash"></i>
                                <p>No new message notifications</p>
                            </div>
                        </div>
                        
                        <div class="notification-footer">
                            <div class="d-flex gap-2">
                                <a href="{{ route('messages.email') }}" class="btn btn-sm btn-primary flex-fill">
                                    <i class="fas fa-envelope me-1"></i> Email
                                </a>
                                <a href="{{ route('admin.system-chat') }}" class="btn btn-sm btn-info flex-fill">
                                    <i class="fas fa-comments me-1"></i> Chat
                                </a>
                                <a href="{{ route('messages.sms') }}" class="btn btn-sm btn-success flex-fill">
                                    <i class="fas fa-sms me-1"></i> SMS
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                --}}-->

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
                            <a href="{{ route('clientstbl') }}" class="btn btn-sm btn-primary w-100">
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
                    {{-- <button onclick="testMessageNotification()" class="btn btn-warning btn-sm">
                        Test Message Notif
                    </button> --}}
                </div>-->
                <!-- Log Out -->
                <form id="logout-form" action="{{ route('custom.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <button type="button" class="btn logout-btn" onclick="showLogoutModal()">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>
            
            <!-- Rest of your dashboard content remains the same -->
            <div class="dashboard-container">
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
                            <form method="POST" action="{{ route('staff.account.settings.update') }}" enctype="multipart/form-data" id="profileForm">
                                @csrf
                                @method('PUT')
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
                                                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                                                            @error('name')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label class="form-label fw-semibold">Email Address</label>
                                                        <div class="input-wrapper">
                                                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                                                            @error('email')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="second-left-section">
                                                        <div class="form-group mb-3">
                                                            <label class="form-label fw-semibold">Phone Number</label>
                                                            <div class="input-wrapper">
                                                                <input type="text" name="cp_number" class="form-control" value="{{ old('cp_number', $user->cp_number) }}" placeholder="+639xxxxxxxxx">
                                                                @error('cp_number')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <!-- Password Display Section -->

                                                        <div class="form-group mb-3">
                                                            <label class="form-label fw-semibold">Current Password</label>
                                                            <div class="password-input-group">
                                                                <input type="password" id="passwordPlaceholder" readonly value="••••••••">
                                                                <!--<button type="button" class="password-toggle" onclick="togglePlaceholderPassword()">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>-->
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
                                                    @php
                                                        // Check if image exists in different possible locations
                                                        $imageFound = false;
                                                        $imagePath = null;
                                                        
                                                        // First check if image is stored with staff_images/ prefix
                                                        if ($user->image) {
                                                            if (strpos($user->image, 'staff_images/') === 0) {
                                                                // Already has prefix
                                                                $imagePath = $user->image;
                                                            } else {
                                                                // Add prefix if not present
                                                                $imagePath = 'staff_images/' . $user->image;
                                                            }
                                                            
                                                            if (file_exists(public_path($imagePath))) {
                                                                $imageFound = true;
                                                            } elseif (file_exists(public_path($user->image))) {
                                                                // Try the raw value
                                                                $imagePath = $user->image;
                                                                $imageFound = true;
                                                            }
                                                        }
                                                    @endphp
                                                    
                                                    @if($imageFound)
                                                        <img src="{{ asset($imagePath) }}" 
                                                            alt="{{ $user->name }}" 
                                                            id="avatarImage" 
                                                            class="img-fluid rounded-circle">
                                                    @else
                                                        <div class="default-avatar">
                                                            <i class="fas fa-user fa-4x text-muted"></i>
                                                        </div>
                                                    @endif
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
                    <form method="POST" action="{{ route('staff.account.settings.update.password') }}" id="passwordForm">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <!-- Display form errors -->
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <div class="settings-card">
                                <div class="form-group mb-3">
                                    <label class="form-label">Current Password</label>
                                    <div class="input-wrapper">
                                        <input type="password" name="current_password" class="form-control" id="currentPasswordInput" required>
                                        <button type="button" class="password-toggle-btn">
                                            <i class="fas fa-eye-slash"></i>
                                        </button>
                                        @error('current_password')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="mt-2">
                                        <a href="{{ route('staff.account.settings.forgot-password.email') }}" class="text-decoration-none small fw-semibold">
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
                                        @error('new_password')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
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
    
    <!-- Per-Tab Authentication Manager (sends tab_token header on all requests) -->
    <script src="{{ asset('js/per-tab-auth-manager.js') }}"></script>
  <script src="{{ asset('js/staff/staffAccountSetting.js') }}"></script>
  @php
      $resolvedImagePath = null;
      if (!empty($user->image)) {
          $candidate = $user->image;
          if (strpos($candidate, 'staff_images/') !== 0) {
              $candidate = 'staff_images/' . $candidate;
          }
          if (file_exists(public_path($candidate))) {
              $resolvedImagePath = $candidate;
          } elseif (file_exists(public_path($user->image))) {
              $resolvedImagePath = $user->image;
          }
      }
      $initialAvatarUrl = $resolvedImagePath ? asset($resolvedImagePath) : null;
  @endphp
  <div id="pageMeta"
       data-initial-avatar-url="{{ $initialAvatarUrl }}"
       data-user-id="{{ $user->id }}"></div>
   <script>
const _metaEl = document.getElementById('pageMeta');
const INITIAL_AVATAR_URL = _metaEl ? (_metaEl.dataset.initialAvatarUrl || null) : null;
document.addEventListener('DOMContentLoaded', function () {

    // Initialize password toggle functionality using event delegation
    document.addEventListener('click', function(event) {

        // Handle password toggle buttons in the change password modal
        if (event.target.closest('#changePasswordModal .password-toggle-btn')) {
            const button = event.target.closest('#changePasswordModal .password-toggle-btn');
            togglePasswordVisibility(button);
        }

        // Handle the main form's password toggle (non-modal)
        if (event.target.closest('.password-toggle') && !event.target.closest('#changePasswordModal')) {
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
                    input.value = '••••••••';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        }
    });

    // Password strength & matching listeners
    const newPasswordInput = document.getElementById('newPasswordInput');
    const confirmPasswordInput = document.getElementById('confirmPasswordInput');

    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function () {
            updatePasswordStrength(this.value);
            checkPasswordMatch();
            validatePasswordForm();
        });
    }

    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function () {
            checkPasswordMatch();
            validatePasswordForm();
        });
    }

    // Final validation before password form submission
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function (event) {
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            const isStrongEnough = updatePasswordStrength(newPassword) >= 3;
            const passwordsMatch = checkPasswordMatch();

            if (!isStrongEnough) {
                event.preventDefault();
                alert('Please use a stronger password.');
                return;
            }

            if (!passwordsMatch) {
                event.preventDefault();
                alert('Passwords do not match.');
            }
        });
    }
});

// Toggle password visibility
function togglePasswordVisibility(button) {
    const input = button.parentElement.querySelector('input');
    const icon = button.querySelector('i');

    if (!input) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }
}

// Password strength checker
function checkPasswordStrength(password) {
    let strength = 0;

    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    return strength;
}

// Update password strength UI
function updatePasswordStrength(password) {
    const strengthBar = document.getElementById('passwordStrengthBar');
    const strengthText = document.getElementById('passwordStrengthText');

    if (!password) {
        strengthBar.style.width = '0%';
        strengthText.textContent = 'Password strength: None';
        return 0;
    }

    const strength = checkPasswordStrength(password);

    if (strength <= 1) {
        strengthBar.style.width = '25%';
        strengthText.textContent = 'Password strength: Weak';
    } else if (strength === 2) {
        strengthBar.style.width = '50%';
        strengthText.textContent = 'Password strength: Fair';
    } else if (strength === 3) {
        strengthBar.style.width = '75%';
        strengthText.textContent = 'Password strength: Good';
    } else {
        strengthBar.style.width = '100%';
        strengthText.textContent = 'Password strength: Strong';
    }

    return strength;
}

// Check password match
function checkPasswordMatch() {
    const newPassword = document.getElementById('newPasswordInput').value;
    const confirmPassword = document.getElementById('confirmPasswordInput').value;
    const mismatch = document.getElementById('passwordMismatchError');
    const success = document.getElementById('passwordMatchSuccess');

    if (!newPassword || !confirmPassword) {
        mismatch.style.display = 'none';
        success.style.display = 'none';
        return false;
    }

    if (newPassword === confirmPassword) {
        mismatch.style.display = 'none';
        success.style.display = 'block';
        return true;
    } else {
        mismatch.style.display = 'block';
        success.style.display = 'none';
        return false;
    }
}

// Enable / disable save button
function validatePasswordForm() {
    const saveBtn = document.getElementById('savePasswordBtn');
    if (!saveBtn) return;

    const strength = updatePasswordStrength(
        document.getElementById('newPasswordInput').value
    );

    saveBtn.disabled = !(strength >= 3 && checkPasswordMatch());
}

// Open change password modal
function showChangePasswordModal() {
    const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
    modal.show();
}

// Avatar preview
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById('avatarPreview').innerHTML =
            `<img src="${e.target.result}" class="img-fluid rounded-circle">`;
    };
    reader.readAsDataURL(event.target.files[0]);
}
// Reset form to original values
function resetForm() {
    const form = document.getElementById('profileForm');
    form.reset();
    
    const avatarPreview = document.getElementById('avatarPreview');
    if (INITIAL_AVATAR_URL) {
        avatarPreview.innerHTML = `<img src="${INITIAL_AVATAR_URL}" alt="{{ $user->name }}" id="avatarImage" class="img-fluid rounded-circle">`;
    } else {
        avatarPreview.innerHTML = `<div class="default-avatar">
            <i class="fas fa-user fa-4x text-muted"></i>
        </div>`;
    }
    
    // Clear file input
    document.getElementById('imageUpload').value = '';
    
    // Show success message
    showToast('Form has been reset to original values', 'info');
}

// Toast notification function (if not already defined)
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer');
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Initialize Bootstrap toast
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    
    // Remove toast after it hides
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}
</script>

<!-- Session Hijack Detection -->
<script src="{{ asset('js/session-hijack-detector.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const metaEl = document.getElementById('pageMeta');
    const uid = metaEl ? Number(metaEl.dataset.userId || 0) : 0;
    if (window.SessionHijackDetector && uid) {
        SessionHijackDetector.init(uid);
    }
});
</script>

<script src="{{ asset('js/staff/diffunNotifications.js') }}"></script>

</body>
</html>
