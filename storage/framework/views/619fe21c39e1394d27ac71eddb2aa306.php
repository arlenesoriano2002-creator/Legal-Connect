<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Legal Connect</title>
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <!-- Bootstrap CSS for logout modal -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.3/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link rel="stylesheet" href="<?php echo e(asset('css/contact.blade.css')); ?>">
    
</head>
<body>
  <?php
      $userIsClient = auth()->check() && auth()->user()->role === 'client';
      $showAuthenticatedUI = $userIsClient; // show auth elements when client session is active
  ?>
      <header>
      <a href="<?php echo e(url('/')); ?>?guest=1" class="logo">
        <img class="logo-icon" src="<?php echo e(asset('logo6.png')); ?>" alt="">
        <div class="logo-text">Legal Connect</div>
      </a>
      <button class="burger-btn" onclick="toggleNav()">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
      </button>
      <nav id="main-nav">
            <a href="<?php echo e(url('/')); ?>?guest=1" class="admin-login">Home</a>
            <a href="<?php echo e(url('/about')); ?>" class="admin-login">About Us</a>
            <a href="<?php echo e(url('/testimonial')); ?>" class="admin-login">Testimonials</a>
            <a href="<?php echo e(url('/contact')); ?>" class="admin-login active">Contact</a>
            <!-- Profile Icon with Dropdown -->
           <?php if($showAuthenticatedUI): ?>
                <div class="profile-dropdown">
                    <button type="button" onclick="toggleDropdown(event)">
                        Welcome, <?php echo e(Auth::user()->name); ?>!!
                    </button>
                    <div class="dropdown-content" id="dropdownContent">
                    <span><?php echo e(Auth::user()->name); ?> &nbsp;<i class="fas fa-user"></i></span>
                        <hr>
                        <a href="#" onclick="openAccountModal()" class="link-a">Account</a>
                        <a href="#" onclick="openEditAccountModal()" class="link-a">Edit Account</a>
                        <hr>
                       <a href="#" onclick="showLogoutModal()">Logout</a>
                        <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display: none;">
                            <?php echo csrf_field(); ?>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo e(url('/login')); ?>" class="admin-login">Login/Register</a>
            <?php endif; ?>
        </nav>
        <!-- Edit Account Modal -->
        <?php if($showAuthenticatedUI): ?>
        <div id="editAccountModal" class="edit-account-modal">
            <div class="edit-account-modal-content">
                <button type="button" class="edit-account-close" onclick="closeEditAccountModal()">&times;</button>
                
                <div class="edit-account-header">
                    <i class="fas fa-user-edit"></i>
                    <h3>Edit Account Information</h3>
                </div>

                <div class="success-message" id="editSuccessMessage"></div>
                <div class="error-message" id="editErrorMessage"></div>
                <div class="info-message" id="editInfoMessage"></div>

                <!-- Account Update Form -->
                <form id="editAccountForm" class="edit-account-form">
                    <?php echo csrf_field(); ?>
                    
                   <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" 
                            value="<?php echo e(Auth::user()->name); ?>" required>
                        <div class="form-error" id="nameError"></div>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" class="form-control" 
                            value="<?php echo e(Auth::user()->address ?? ''); ?>" placeholder="Enter your address">
                        <div class="form-error" id="addressError"></div>
                    </div>

                    <div class="form-group">
                        <label for="cp_number">Phone Number</label>
                        <input type="text" id="cp_number" name="cp_number" class="form-control" 
                            value="<?php echo e(Auth::user()->cp_number); ?>" required>
                        <div class="form-error" id="cp_numberError"></div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" 
                            value="<?php echo e(Auth::user()->email); ?>" required>
                        <div class="form-error" id="emailError"></div>
                    </div>

                    <div class="loading-spinner" id="editLoadingSpinner">
                        <i class="fas fa-spinner fa-spin"></i> Saving changes...
                    </div>

                    <center>
                    <div class="btn-group">
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <button type="button" class="btn-cancel" onclick="closeEditAccountModal()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                    </center>
                </form>

                <!-- Password Change Section -->
                <div class="change-password-link">
                    <a href="#" onclick="togglePasswordForm()">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                </div>

                <!-- Password Change Form -->
                <form id="passwordChangeForm" class="password-form">
                    <?php echo csrf_field(); ?>
                   <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="new_password" name="new_password" class="form-control" required onkeyup="checkPasswordStrength(this.value)">
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('new_password')">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                        <div class="form-error" id="new_passwordError"></div>
                        <div class="password-strength-meter">
                            <div class="password-strength-meter-bar" id="passwordStrengthMeterBar"></div>
                        </div>
                        <div class="password-strength-text" id="passwordStrengthText"></div>
                    </div>

                    <div class="form-group">
                        <label for="new_password_confirmation">Confirm New Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" required>
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('new_password_confirmation')">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="loading-spinner" id="passwordLoadingSpinner">
                        <i class="fas fa-spinner fa-spin"></i> Sending verification code...
                    </div>

                    <center>
                    <div class="btn-group">
                        <button type="submit" class="btn-save">
                            <i class="fas fa-paper-plane"></i> Send Verification Code
                        </button>
                        <button type="button" class="btn-cancel" onclick="togglePasswordForm()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                    </center>
                    <div>
                        <center style="margin-top: 10px;">
                            <p>Click the cancel or change password to go back editing other information</p>
                        </center>
                    </div>
                </form>

                <!-- OTP Verification Form -->
                <form id="otpVerificationForm" class="otp-form">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label for="otp">Verification Code</label>
                        <p style="font-size: 14px; color: #666; margin-bottom: 15px;">
                            Enter the 6-digit code sent to your email address.
                        </p>
                        <div class="otp-input-group">
                            <input type="text" id="otp" name="otp" class="form-control otp-input" 
                                maxlength="6" placeholder="000000" required>
                        </div>
                        <div class="form-error" id="otpError"></div>
                    </div>

                    <div class="resend-otp">
                        <span>Didn't receive the code? </span>
                        <a href="#" id="resendOtpLink" onclick="resendOtp()">Resend Code</a>
                        <span id="resendTimer" style="display: none;"></span>
                    </div>

                    <div class="loading-spinner" id="otpLoadingSpinner">
                        <i class="fas fa-spinner fa-spin"></i> Verifying code...
                    </div>

                    <center>
                        <div class="btn-group">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-check-circle"></i> Verify & Change Password
                            </button>
                            <button type="button" class="btn-cancel" onclick="cancelPasswordChange()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </center>
                </form>
            </div>
        </div>
        <?php endif; ?>
        <!-- Notification and Message Icons Container -->
        <div class="header-icons-container">
            <?php if($showAuthenticatedUI): ?>
                <!-- ========== NOTIFICATION ICON DROPDOWN ========== -->
                <div class="notification-icon-container" id="notificationIconContainer">
                    <!-- Notification indicator (red dot) -->
                    <div class="notification-indicator" id="notificationIndicator"></div>
                    
                    <button type="button" class="notification-icon-btn" onclick="notificationToggleDropdown(event)">
                        <i class="fas fa-bell"></i>
                        <span id="notificationUnreadBadge" class="notification-badge" style="display: none;">0</span>
                    </button>
                    
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-dropdown-header">
                            <h3><i class="fas fa-bell me-2"></i>Notifications</h3>
                            <button type="button" class="notification-close-btn" onclick="notificationCloseDropdown(event)">&times;</button>
                        </div>
                        <div class="notification-dropdown-body">
                            <ul id="notificationDropdownList">
                                <!-- Notifications will be loaded here -->
                                <li class="notification-loading">Loading notifications...</li>
                            </ul>
                            <div class="notification-dropdown-footer">
                                <button type="button" onclick="notificationMarkAllAsRead()" class="mark-all-read-btn">
                                    <i class="fas fa-check-double"></i> Mark all as read
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ========== END NOTIFICATION ICON DROPDOWN ========== -->

                <!-- ========== MESSAGE ICON DROPDOWN ========== -->
                <?php if(auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->role !== 'superadmin'): ?>
                    <div class="message-icon-container" id="messageIconContainer">
                            <!-- Notification indicator (red dot) -->
                            <div class="message-notification-indicator" id="messageNotificationIndicator"></div>
                            
                            <button type="button" class="message-icon-btn" onclick="messageToggleDropdown(event)">
                                <i class="fas fa-envelope"></i>
                                <span id="messageUnreadBadge" class="message-badge" style="display: none;">0</span>
                            </button>
                            <div class="message-dropdown" id="messageDropdown">
                                <div class="message-header">
                                    <h3><i class="fas fa-comments me-2"></i>Message Attorney</h3>
                                    <button type="button" class="message-close-btn" onclick="messageCloseDropdown(event)">&times;</button>
                                </div>
                                <div class="message-body" id="messageAdmins">
                                    <!-- Back button container (initially hidden) -->
                                    <div id="messageBackButtonContainer">
                                        <button type="button" class="message-back-btn" onclick="messageBackToAdminList()">
                                            <i class="fas fa-arrow-left"></i>
                                            <span>Back to Chat List</span>
                                        </button>
                                    </div>
                                    <!-- Admin list (initially visible) -->
                                    <div id="messageAdminsList" class="text-center text-muted py-3">
                                        <i class="fas fa-spinner fa-spin"></i> Loading...
                                    </div>
                                </div>
                                <div class="message-chat-area" id="messageChatArea" style="display: none;">
                                    <div class="message-chat-messages" id="messageChatMessages">
                                        <!-- Messages will appear here -->
                                    </div>
                                    <form id="messageChatForm">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" id="messageConversationId" value="">
                                        <input type="hidden" id="messageAdminId" value="">
                                        <div class="message-input-group">
                                            <input type="text" id="messageChatInput" class="message-chat-input" placeholder="Type your message..." autocomplete="off">
                                            <button type="button" class="message-file-btn" onclick="initiateVideoCall()" title="Start Video Call">
                                                <i class="fas fa-video"></i>
                                            </button>
                                            <button type="button" class="message-file-btn" onclick="document.getElementById('messageFileInput').click()">
                                                <i class="fas fa-paperclip"></i>
                                            </button>
                                            <input type="file" id="messageFileInput" style="display: none;" 
                                             accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar,.7z">
                                            <button type="submit" class="message-send-btn" id="messageSendBtn">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </div>
                                        <div id="messageFilePreview" class="message-file-preview" style="display: none;"></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                <?php endif; ?>
                <!-- ========== END MESSAGE ICON DROPDOWN ========== -->
            <?php endif; ?>
        </div>
    </header>
    <div class="hero">
     <img class="bg-img" src="<?php echo e(asset('d2.jpg')); ?>" alt="Hero background image" />
    <div class="container1">
      
      <div class="message">
        <span class="subtitle select-none">Legal Connect</span>
        <h1>Contact Information.</h1>
        <p>Get in touch with us for reliable legal assistance.</p>
      </div>
    </div>
  </div>
  <!-- Contact Info and Map Section -->
<section class="container contact-section" aria-label="Contact information and location map">
  <div class="contact-content">
    <div class="contact-left">
      <h2>Get in touch</h2>
      <p class="description">Get in touch with us today for expert legal advice, consultation, and trusted notarial services near you.</p>

      <div class="contact-item">
        <div class="icon-box" aria-hidden="true">
          <i class="fas fa-map-marker-alt"></i>
        </div>
        <div class="contact-text">
          <h3>Head Office</h3>
          <p>Diffun, Quirino, Philippines</p>
        </div>
      </div>

      <div class="contact-item">
        <div class="icon-box" aria-hidden="true">
          <i class="fas fa-envelope"></i>
        </div>
        <div class="contact-text">
          <h3>Email us</h3>
          <p>LegalConnect@gmail.com</p>
        </div>
      </div>

      <div class="contact-item">
        <div class="icon-box" aria-hidden="true">
          <i class="fas fa-phone-alt"></i>
        </div>
        <div class="contact-text">
          <h3>Call us</h3>
          <p>Phone : +6221.2002.2012,<br />Fax : +6221.2002.2913</p>
        </div>
      </div>
    </div>

    <div class= "maps">
      <div class="contact-right" aria-label="Office building photo and location map">
      <img src="<?php echo e(asset('maps.png')); ?>" alt="Hero backgroung image"/>
    </div>
      <div class="contact-right1" aria-label="Office building photo and location map">
        <img src="<?php echo e(asset('location1.2.jpg')); ?>" alt="Hero backgroung image"/>
      </div>
    </div>
  </div>
</section>

<!-- Location Map Section -->
<section class="location-map-section" aria-label="Branch office locations with interactive maps">
  <div class="container">
    <h2>Our Branch Offices</h2>
    <p>Visit us at one of our conveniently located branch offices</p>
    
    <!-- Branch Navigation Tabs -->
    <div class="branch-navigation">
      <button class="branch-btn active" data-branch="diffun" onclick="switchBranch('diffun', event)">
        <i class="fas fa-map-marker-alt"></i> Diffun Branch Office
      </button>
      <button class="branch-btn" data-branch="cordon" onclick="switchBranch('cordon', event)">
        <i class="fas fa-map-marker-alt"></i> Cordon Branch Office
      </button>
    </div>

    <!-- Maps Container -->
    <div class="maps-container">
      <!-- Diffun Map -->
      <div id="diffun-map" class="branch-map active">
        <div class="zoom-controls">
          <button class="zoom-btn zoom-in" onclick="zoomMap('diffun', 1)" title="Zoom In">
            <i class="fas fa-plus"></i>
          </button>
          <button class="zoom-btn zoom-out" onclick="zoomMap('diffun', -1)" title="Zoom Out">
            <i class="fas fa-minus"></i>
          </button>
          <button class="zoom-btn zoom-reset" onclick="resetZoom('diffun')" title="Reset Zoom">
            <i class="fas fa-redo"></i>
          </button>
        </div>
        <div class="map-wrapper" id="diffun-wrapper">
          <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d218.44118018555275!2d121.5048657657858!3d16.59390672932564!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e1!3m2!1sen!2sph!4v1775860471374!5m2!1sen!2sph" 
            width="100%" height="450" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <div class="branch-info">
          <h3>Diffun Branch Office</h3>
          <p><strong>Coordinates:</strong> 16.593850, 121.505076</p>
          <p><strong>Address:</strong> Diffun, Ifugao</p>
          <a href="https://maps.app.goo.gl/6YojvLRkU3vPgH6B9" target="_blank" class="view-map-link">
            <i class="fas fa-external-link-alt"></i> View on Google Maps
          </a>
        </div>
      </div>

      <!-- Cordon Map -->
      <div id="cordon-map" class="branch-map">
        <div class="zoom-controls">
          <button class="zoom-btn zoom-in" onclick="zoomMap('cordon', 1)" title="Zoom In">
            <i class="fas fa-plus"></i>
          </button>
          <button class="zoom-btn zoom-out" onclick="zoomMap('cordon', -1)" title="Zoom Out">
            <i class="fas fa-minus"></i>
          </button>
          <button class="zoom-btn zoom-reset" onclick="resetZoom('cordon')" title="Reset Zoom">
            <i class="fas fa-redo"></i>
          </button>
        </div>
        <div class="map-wrapper" id="cordon-wrapper">
          <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d2077.310385559005!2d121.46327263496076!3d16.673661997165613!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e1!3m2!1sen!2sph!4v1775860409813!5m2!1sen!2sph" 
            width="100%" height="450" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <div class="branch-info">
          <h3>Cordon Branch Office</h3>
          <p><strong>Coordinates:</strong> 16.673552, 121.464370</p>
          <p><strong>Address:</strong> Cordon, Ifugao</p>
          <a href="https://maps.app.goo.gl/7Lad7He2Whd9hNq98" target="_blank" class="view-map-link">
            <i class="fas fa-external-link-alt"></i> View on Google Maps
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- JavaScript for Branch Map Switching and Zoom Controls -->
<script>
  // Store zoom levels for each map
  const mapZoomLevels = {
    diffun: 1,
    cordon: 1
  };

  const ZOOM_STEP = 0.1;
  const MIN_ZOOM = 1;
  const MAX_ZOOM = 2;

  function switchBranch(branch, event) {
    event.preventDefault();
    
    // Hide all maps
    const maps = document.querySelectorAll('.branch-map');
    maps.forEach(map => map.classList.remove('active'));
    
    // Remove active class from all buttons
    const buttons = document.querySelectorAll('.branch-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    // Show selected map
    const selectedMap = document.getElementById(branch + '-map');
    if (selectedMap) {
      selectedMap.classList.add('active');
    }
    
    // Activate selected button
    event.target.closest('.branch-btn').classList.add('active');
  }

  function zoomMap(branch, direction) {
    // Calculate new zoom level
    let newZoom = mapZoomLevels[branch] + (ZOOM_STEP * direction);
    
    // Apply zoom limits
    newZoom = Math.max(MIN_ZOOM, Math.min(MAX_ZOOM, newZoom));
    
    // Update stored zoom level
    mapZoomLevels[branch] = newZoom;
    
    // Apply transform to map wrapper
    const wrapper = document.getElementById(branch + '-wrapper');
    if (wrapper) {
      wrapper.style.transform = `scale(${newZoom})`;
    }
  }

  function resetZoom(branch) {
    // Reset to original zoom level
    mapZoomLevels[branch] = 1;
    
    const wrapper = document.getElementById(branch + '-wrapper');
    if (wrapper) {
      wrapper.style.transform = 'scale(1)';
    }
  }
</script>

<!-- Updated Contact Section continues below -->
  </div>
</section>
<section class="message-section" aria-label="Send us a message form">
  <h2>Send us a message</h2>
  <p>Have questions or concerns? Send us a message anytime below.</p>

  <form class="message-form" action="<?php echo e(route('message.store')); ?>" method="POST" novalidate>
    <?php echo csrf_field(); ?>

    <div class="form-row">
      <div class="form-group">
        <label for="name">Name <span style="color: red;">*</span></label>
        <input class="textInput" id="name" name="name" type="text" placeholder="Name" value="<?php echo e(old('name')); ?>" required />
        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <span class="error-message" style="color: #dc3545; font-size: 0.875rem; display: block; margin-top: 5px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo e($message); ?>

          </span>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
          <label for="phone">Phone</label>
          <input class="textInput" 
                id="phone" 
                name="phone" 
                type="tel" 
                pattern="[0-9]{11}" 
                maxlength="11" 
                inputmode="numeric" 
                placeholder="Phone" 
                value="<?php echo e(old('phone')); ?>" 
                title="Please enter exactly 11 digits (numbers only)"
                onkeypress="return event.charCode >= 48 && event.charCode <= 57" />
          <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <span class="error-message" style="color: #dc3545; font-size: 0.875rem; display: block; margin-top: 5px;">
                  <i class="fas fa-exclamation-circle"></i> <?php echo e($message); ?>

              </span>
          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>
      <div class="form-group">
        <label for="email">Email <span style="color: red;">*</span></label>
        <input id="email" name="email" type="email" placeholder="Email" value="<?php echo e(old('email')); ?>" required />
        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <span class="error-message" style="color: #dc3545; font-size: 0.875rem; display: block; margin-top: 5px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo e($message); ?>

          </span>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group full-width">
        <label for="subject">Subject</label>
        <input class="textInput" id="subject" name="subject" type="text" placeholder="Subject" value="<?php echo e(old('subject')); ?>" />
        <?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <span class="error-message" style="color: #dc3545; font-size: 0.875rem; display: block; margin-top: 5px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo e($message); ?>

          </span>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group full-width">
        <label for="message">Message <span style="color: red;">*</span></label>
        <textarea id="message" name="message" placeholder="Message" rows="4"><?php echo e(old('message')); ?></textarea>
        <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <span class="error-message" style="color: #dc3545; font-size: 0.875rem; display: block; margin-top: 5px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo e($message); ?>

          </span>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>
    </div>

    <div class="form-row" style="margin-top: 15px;">
      <div class="form-group full-width">
        <small style="color: #6c757d; display: block; margin-bottom: 10px;">
          <i class="fas fa-info-circle"></i> Fields marked with <span style="color: red;">*</span> are required.
        </small>
      </div>
    </div>

    <button type="submit" class="send-button" aria-label="Send message">
      <i class="fas fa-paper-plane" aria-hidden="true"></i> SEND MESSAGE
    </button>
  </form>
</section>

<!-- Social Media Section 
<section class="social-section" aria-label="Follow our social media">
  <p>Follow our social media</p>
  <div class="social-icons">
    <a href="#" aria-label="Facebook" title="Facebook"><i class="fab fa-facebook-f"></i></a>
    <a href="#" aria-label="Instagram" title="Instagram"><i class="fab fa-instagram"></i></a>
    <a href="#" aria-label="Twitter" title="Twitter"><i class="fab fa-twitter"></i></a>
    <a href="#" aria-label="YouTube" title="YouTube"><i class="fab fa-youtube"></i></a>
  </div>
</section>-->

<footer>
    <div class="container">
        <div class="footer-grid">
            <div class="footer-column">
                <h3>Legal Connect</h3>
                <ul class="footer-links">
                    <li><a href="<?php echo e(url('/welcome')); ?>">Home</a></li>
                    <li><a href="<?php echo e(url('/about')); ?>">About</a></li>
                    <li><a href="<?php echo e(url('/testimonial')); ?>">Testimonials</a></li>
                    <li><a href="<?php echo e(url('/contact')); ?>">Contact</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Services</h3>
                <ul class="footer-links">
                    <?php if(isset($categories) && $categories->count() > 0): ?>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><a href="<?php echo e(url('/about')); ?>#practice-areas"><?php echo e($category); ?></a></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <!-- Fallback in case no categories exist in database -->
                        <li><a href="<?php echo e(url('/about')); ?>#practice-areas">Family Law</a></li>
                        <li><a href="<?php echo e(url('/about')); ?>#practice-areas">Personal Injury</a></li>
                        <li><a href="<?php echo e(url('/about')); ?>#practice-areas">Real Estate</a></li>
                        <li><a href="<?php echo e(url('/about')); ?>#practice-areas">Business Law</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Legal Connect All rights reserved.</p>
        </div>
    </div>
</footer>
  <!-- Image Preview Modal -->
<div id="imagePreviewModal" class="modal" style="display: none;">
    <div class="modal-content image-modal-content">
        <div class="modal-header">
            <h3 id="imageModalFileName"></h3>
            <button class="close-btn" onclick="messageCloseImageModal()">&times;</button>
        </div>
        <div class="modal-body">
            <img src="" alt="" id="fullSizeImage">
        </div>
        <div class="modal-footer">
            <a href="#" class="btn-download" id="imageDownloadLink">
                <i class="fas fa-download"></i> Download
            </a>
            <button class="btn-close" onclick="messageCloseImageModal()">Close</button>
        </div>
    </div>
</div>

<!-- Offline Notification Modal -->
<div class="modal fade" id="adminOfflineModal" tabindex="-1" aria-labelledby="adminOfflineModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adminOfflineModalLabel">
                    <i class="fas fa-exclamation-circle me-2"></i>Attorney Unavailable
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div style="font-size: 48px; color: #ffc107; margin-bottom: 15px;">
                    <i class="fas fa-user-slash"></i>
                </div>
                <h5>The attorney is currently offline.</h5>
                <p class="text-muted">Video calls are not available at this time. Please use the <strong>Contact page</strong> to send them a message and they will get back to you as soon as possible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== LOGOUT CONFIRMATION MODAL ==================== -->

<div id="logoutConfirmationModal" class="simple-modal" style="display: none;">
    <div class="simple-modal-overlay" onclick="closeLogoutModal()"></div>
    <div class="simple-modal-content">
        <div class="simple-modal-header">
            <h5 class="simple-modal-title">
                <i class="fas fa-sign-out-alt me-2"></i>Confirm Logout
            </h5>
            <button type="button" class="simple-modal-close" onclick="closeLogoutModal()">&times;</button>
        </div>
        <div class="simple-modal-body">
            <div style="font-size: 48px; color: #ffc107; margin-bottom: 15px; text-align: center;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h4 class="text-center mb-3">Confirm Logout</h4>
            <p class="text-center">Are you sure you want to log out?</p>
        </div>
        <div class="simple-modal-footer">
            <center>
                <button type="button" class="btn btn-secondary" onclick="closeLogoutModal()">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" onclick="performLogout()">
                    <i class="fas fa-sign-out-alt me-1"></i> Log Out
                </button>
            </center>
        </div>
    </div>
</div>
<!-- Font Awesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
 <script>
 // Simple, clean functions without complex parameters
    function toggleDropdown(event) {
        event.stopPropagation();
        var dropdown = document.getElementById("dropdownContent");
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }

    function toggleNav() {
        const nav = document.getElementById('main-nav');
        nav.classList.toggle('active');
    }

    // ==================== START: NOTIFICATION DROPDOWN FUNCTIONS ====================
    // This section handles the notification icon dropdown functionality
    var notificationDropdownOpen = false;
    var notificationInterval = null;
    var notificationsLoaded = false;

    // Toggle notification dropdown
    function notificationToggleDropdown(event) {
        event.stopPropagation();
        const dropdown = document.getElementById('notificationDropdown');
        dropdown.classList.toggle('active');
        
        if (dropdown.classList.contains('active')) {
            notificationDropdownOpen = true;
            // Hide badge when opening dropdown
            updateNotificationBadge(0);
            notificationMarkAllAsRead();
            if (!notificationsLoaded) {
                loadNotificationDropdown();
            }
        } else {
            notificationDropdownOpen = false;
            clearNotificationInterval();
        }
    }

    // Close notification dropdown
    function notificationCloseDropdown(event) {
        if (event) event.stopPropagation();
        const dropdown = document.getElementById('notificationDropdown');
        dropdown.classList.remove('active');
        notificationDropdownOpen = false;
        clearNotificationInterval();
        // Hide badge and mark as read when closing
        updateNotificationBadge(0);
        notificationMarkAllAsRead();
    }

    // Clear notification refresh interval
    function clearNotificationInterval() {
        if (notificationInterval) {
            clearInterval(notificationInterval);
            notificationInterval = null;
        }
    }

    // Load notifications into dropdown
    function loadNotificationDropdown() {
        const notificationList = document.getElementById('notificationDropdownList');
        notificationList.innerHTML = '<li class="notification-loading">Loading notifications...</li>';

        fetchApprovalHistory()
            .then(data => {
                console.log('Approval history data:', data);
                renderNotificationDropdown(data.notifications);
                updateNotificationBadge(data.notifications.length);
                notificationsLoaded = true;
            })
            .catch(error => {
                console.error('Error fetching approval history:', error);
                notificationList.innerHTML = '<li class="notification-error">Error loading notifications. Please try again.</li>';
            });

        // Start auto-refresh every 5 seconds when dropdown is open
        notificationInterval = setInterval(() => {
            console.log('Auto-refreshing notifications...');
            fetchApprovalHistory()
                .then(data => {
                    renderNotificationDropdown(data.notifications);
                    updateNotificationBadge(data.notifications.length);
                })
                .catch(error => {
                    console.error('Error auto-refreshing notifications:', error);
                });
        }, 5000);
    }

    // Function to fetch approval history
   function fetchApprovalHistory() {
        return fetch('/notifications/approval-history', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        });
    }

    // Render notifications in dropdown
    function renderNotificationDropdown(notifications) {
        const notificationList = document.getElementById('notificationDropdownList');
        notificationList.innerHTML = '';

        if (notifications && notifications.length > 0) {
            notifications.forEach(notification => {
                const li = document.createElement('li');
                const status = (notification.appointment_approval || '').toLowerCase();
                const statusColor = getStatusColor(status);
                const statusIcon = getStatusIcon(status);
                const message = getStatusMessage(notification);
                
                // Format the date
                const createdDate = new Date(notification.created_at);
                const formattedDate = createdDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                li.className = 'notification-dropdown-item';
                li.innerHTML = `
                    <div class="notification-dropdown-content" style="background: ${status === 'approved' ? '#f0fff0' : status === 'denied' ? '#fff0f0' : '#fff9e6'};">
                        <div class="notification-dropdown-icon">${statusIcon}</div>
                        <div class="notification-dropdown-details">
                            <div class="notification-dropdown-message">${message}</div>
                            <div class="notification-dropdown-meta">
                                <span class="notification-dropdown-time">${formattedDate}</span>
                                <span class="notification-dropdown-status" style="color: ${statusColor}; border-color: ${statusColor};">
                                    ${(notification.appointment_approval || 'Pending').charAt(0).toUpperCase() + (notification.appointment_approval || 'Pending').slice(1)}
                                </span>
                            </div>
                        </div>
                    </div>
                `;
                notificationList.appendChild(li);
            });
        } else {
            notificationList.innerHTML = `
                <li class="notification-empty">
                    <div class="notification-empty-icon">📭</div>
                    <div class="notification-empty-text">
                        <p>No notifications found</p>
                        <small>Your appointment notifications will appear here</small>
                    </div>
                </li>
            `;
        }
    }

    // Update notification badge
    function updateNotificationBadge(count) {
        const badge = document.getElementById('notificationUnreadBadge');
        const indicator = document.getElementById('notificationIndicator');
        const container = document.getElementById('notificationIconContainer');
        
        if (badge && indicator && container) {
            if (count > 0) {
                // Show badge with count
                badge.textContent = count > 9 ? '9+' : count;
                badge.style.display = 'flex';
                
                // Show notification indicator
                indicator.style.display = 'block';
                
                // Add class to container for styling
                container.classList.add('has-unread');
                
                // Add animation class for many unread
                if (count > 5) {
                    container.classList.add('many-unread');
                } else {
                    container.classList.remove('many-unread');
                }
                
                // Change bell icon color to indicate unread
                const bellIcon = document.querySelector('.notification-icon-btn .fa-bell');
                if (bellIcon) {
                    bellIcon.classList.remove('far');
                    bellIcon.classList.add('fas');
                    bellIcon.style.color = '#ff4757';
                }
            } else {
                // Hide both badge and indicator
                badge.style.display = 'none';
                indicator.style.display = 'none';
                container.classList.remove('has-unread', 'many-unread');
                
                // Reset bell icon
                const bellIcon = document.querySelector('.notification-icon-btn .fa-bell');
                if (bellIcon) {
                    bellIcon.classList.remove('fas');
                    bellIcon.classList.add('far');
                    bellIcon.style.color = '';
                }
            }
        }
    }

    // Mark all notifications as read
    function notificationMarkAllAsRead() {
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update badge count to 0
                updateNotificationBadge(0);
                // Reload notifications to show them as read
                loadNotificationDropdown();
            }
        })
        .catch(error => console.error('Error marking notifications as read:', error));
    }

    // Global polling for badge updates
    setInterval(() => {
        if (!notificationDropdownOpen) {
            fetchApprovalHistory()
                .then(data => {
                    if (data.notifications.length > 0 && badgeHidden) {
                        badgeHidden = false;
                        updateNotificationBadge(data.notifications.length);
                    } else if (!badgeHidden) {
                        updateNotificationBadge(data.notifications.length);
                    }
                })
                .catch(error => console.error('Polling error:', error));
        }
    }, 10000);

    // Function to get status message
    function getStatusMessage(notification) {
        const status = (notification.appointment_approval || '').toLowerCase();
        const fullname = notification.fullname;
        const date = notification.appointment_date;
        const time = notification.appointment_time;
        
        let datetime = '';
        if (date) {
            const dateObj = new Date(date);
            datetime = " scheduled on " + dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            if (time) {
                datetime += " at " + time;
            }
        }
        
        switch (status) {
            case 'approved':
                return `🎉 Your appointment request for ${fullname} has been approved!${datetime}`;
            case 'denied':
                return `❌ Your appointment request for ${fullname} has been denied.${datetime}`;
            case 'pending':
                return `⏳ Your appointment request for ${fullname} is pending review.${datetime}`;
            default:
                return `📅 Appointment status updated for ${fullname}: ${notification.appointment_approval || 'Pending'}${datetime}`;
        }
    }

    // Function to get status color
    function getStatusColor(status) {
        switch (status?.toLowerCase()) {
            case 'approved': return 'green';
            case 'denied': return 'red';
            case 'pending': return 'orange';
            default: return 'blue';
        }
    }

    // Function to get status icon
    function getStatusIcon(status) {
        switch (status?.toLowerCase()) {
            case 'approved': return '✅';
            case 'denied': return '❌';
            case 'pending': return '⏳';
            default: return '📅';
        }
    }

    // Load notification badge on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Load initial notification count
        fetchApprovalHistory()
            .then(data => {
                updateNotificationBadge(data.notifications.length);
            })
            .catch(error => console.error('Error loading notification count:', error));
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationIcon = document.querySelector('.notification-icon-btn');
        
        if (notificationDropdown && !notificationDropdown.contains(event.target) && !notificationIcon.contains(event.target)) {
            notificationCloseDropdown();
        }
    });
    // ==================== END: NOTIFICATION DROPDOWN FUNCTIONS ====================

    // ==================== ACCOUNT MODAL FUNCTIONS ====================
    function openAccountModal() {
        console.log('Opening account modal');
        
        const userData = {
            name: "<?php echo e(Auth::user()->name ?? 'User'); ?>",
            address: "<?php echo e(Auth::user()->address ?? 'None'); ?>",
            email: "<?php echo e(Auth::user()->email ?? 'user@example.com'); ?>",
            cp_number: "<?php echo e(Auth::user()->cp_number ?? 'Not provided'); ?>",
            password: "••••••••"
        };

        const modal = document.getElementById('accountModal');
        const accountInfo = document.getElementById('accountInfo');
        accountInfo.innerHTML = `
            <div class="account-details">
                <div class="account-header">
                    <i class="fas fa-user-circle account-icon"></i>
                    <h3>Account Information</h3>
                </div>
                <table class="account-table">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>${userData.name}</td>
                    </tr>
                    <tr>
                        <td><strong>Address:</strong></td>
                        <td>${userData.address}</td>
                    </tr>
                    <tr>
                        <td><strong>Phone Number:</strong></td>
                        <td>${userData.cp_number}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>${userData.email}</td>
                    </tr>
                    <tr>
                        <td><strong>Password:</strong></td>
                        <td>${userData.password}</td>
                    </tr>
                </table>
            </div>
        `;
        modal.style.display = 'block';
    }

    function closeAccountModal() {
        console.log('Closing account modal');
        document.getElementById('accountModal').style.display = 'none';
    }

    function closeModal() {
        document.getElementById('successModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        var accountModal = document.getElementById('accountModal');
        if (event.target == accountModal) {
            closeAccountModal();
        }
    };

    // Add event listener for Escape key to close modals
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeAccountModal();
        }
    });
    // ==================== END ACCOUNT MODAL FUNCTIONS ====================
    </script>

<!-- Account Modal -->
<div id="accountModal" class="modal">
    <div class="modal-content">
    <!--<span class="close" onclick="closeAccountModal(event)">&times;</span>-->
        <div id="accountInfo">
            <!-- Account information will be loaded here -->
        </div>
    </div>
</div>



<script>
// ==================== MESSAGE DROPDOWN FUNCTIONS ====================
var messagePusher = null;
var messageChannel = null;
var currentMessageConversationId = null;

// FIX 2: Add missing messageFormatFileSize function
function messageFormatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Add missing messageGetFileIcon function
function messageGetFileIcon(fileName, fileMime) {
    const ext = fileName.split('.').pop().toLowerCase();
    if (fileMime && fileMime.startsWith('image/')) return '<i class="fas fa-file-image"></i>';
    if (ext === 'pdf') return '<i class="fas fa-file-pdf"></i>';
    if (['doc', 'docx'].includes(ext)) return '<i class="fas fa-file-word"></i>';
    if (['xls', 'xlsx'].includes(ext)) return '<i class="fas fa-file-excel"></i>';
    if (['ppt', 'pptx'].includes(ext)) return '<i class="fas fa-file-powerpoint"></i>';
    if (['zip', 'rar', '7z'].includes(ext)) return '<i class="fas fa-file-archive"></i>';
    if (['txt', 'log'].includes(ext)) return '<i class="fas fa-file-alt"></i>';
    return '<i class="fas fa-file"></i>';
}

// Add missing image modal functions
function messageOpenImageModal(imageUrl, fileName) {
    const modal = document.getElementById('imagePreviewModal');
    const fullSizeImage = document.getElementById('fullSizeImage');
    const imageModalFileName = document.getElementById('imageModalFileName');
    const imageDownloadLink = document.getElementById('imageDownloadLink');
    
    if (fullSizeImage) fullSizeImage.src = imageUrl;
    if (imageModalFileName) imageModalFileName.textContent = fileName;
    if (imageDownloadLink) {
        imageDownloadLink.href = imageUrl;
        imageDownloadLink.download = fileName;
    }
    
    if (modal) modal.style.display = 'block';
}

function messageCloseImageModal() {
    const modal = document.getElementById('imagePreviewModal');
    if (modal) modal.style.display = 'none';
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('imagePreviewModal');
    if (modal && event.target === modal) {
        messageCloseImageModal();
    }
});

function messageDropdownExists() {
    return document.getElementById('messageDropdown') !== null;
}

function messageToggleDropdown(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('messageDropdown');
    dropdown.classList.toggle('active');
    
    if (dropdown.classList.contains('active')) {
        messageLoadAdmins();
        
        const indicator = document.getElementById('messageNotificationIndicator');
        const container = document.getElementById('messageIconContainer');
        
        if (indicator) indicator.style.display = 'none';
        if (container) container.classList.remove('has-unread', 'many-unread');
        
        const envelopeIcon = document.querySelector('.message-icon-btn .fa-envelope');
        if (envelopeIcon) {
            envelopeIcon.classList.remove('fas');
            envelopeIcon.classList.add('far');
            envelopeIcon.style.color = '';
        }
        
        messageMarkAllAsRead();
    }
}

function messageMarkAllAsRead() {
    fetch('<?php echo e(route("chat.mark-all-read")); ?>', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Content-Type': 'application/json'
        }
    })
    .catch(error => console.error('Error marking messages as read:', error));
}

function messageCloseDropdown(event) {
    if (!messageDropdownExists()) return;
    
    if (event) event.stopPropagation();
    const dropdown = document.getElementById('messageDropdown');
    dropdown.classList.remove('active');
    messageResetView();
}

function messageResetView() {
    if (!messageDropdownExists()) return;
    
    const adminsDiv = document.getElementById('messageAdmins');
    const chatArea = document.getElementById('messageChatArea');
    
    if (adminsDiv) adminsDiv.style.display = 'block';
    if (chatArea) chatArea.style.display = 'none';
    
    document.getElementById('messageConversationId').value = '';
    document.getElementById('messageAdminId').value = '';
    
    // Also, hide the back button
    hideMessageBackButton();
}

async function messageLoadAdmins() {
    if (!messageDropdownExists()) return;
    
    const adminsDiv = document.getElementById('messageAdmins');
    if (!adminsDiv) return;
    
    // Hide back button when loading admins
    hideMessageBackButton();
    
    // Show loading state
    adminsDiv.innerHTML = `
        <div id="messageBackButtonContainer">
            <button type="button" class="message-back-btn" onclick="messageBackToAdminList()">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Chat List</span>
            </button>
        </div>
        <div id="messageAdminsList" class="text-center text-muted py-3">
            <i class="fas fa-spinner fa-spin"></i> Loading...
        </div>
    `;
    
    try {
        const response = await fetch('/api/admins', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            }
        });
        
        if (!response.ok) throw new Error('Network response was not ok');
        
        const data = await response.json();
        if (data.success) {
            messageDisplayAdmins(data.admins);
        } else {
            document.getElementById('messageAdminsList').innerHTML = '<div class="text-center text-muted py-3">No admins available</div>';
        }
    } catch (error) {
        console.error('Error loading admins:', error);
        document.getElementById('messageAdminsList').innerHTML = '<div class="text-center text-muted py-3">Error loading admins. Please try again.</div>';
    }
}
function messageDisplayAdmins(admins) {
    const adminsList = document.getElementById('messageAdminsList');
    if (!adminsList) return;
    
    if (!admins || admins.length === 0) {
        adminsList.innerHTML = '<div class="text-center text-muted py-3">No admins available</div>';
        return;
    }
    
    let html = '';
    admins.forEach(admin => {
        const imageUrl = admin.image ? "<?php echo e(asset('storage/ids/')); ?>/" + admin.image : `https://ui-avatars.com/api/?name=${encodeURIComponent(admin.name)}&background=random&color=fff&size=100`;
        const statusClass = admin.is_online ? 'online' : 'offline';
        const statusText = admin.is_online ? 'online' : 'offline';
        html += `
            <div class="message-admin-item" onclick="messageOpenChat(${admin.id}, '${admin.name.replace(/'/g, "\\'")}', ${admin.is_online})">
                <div style="position: relative; display: inline-block;">
                    <img src="${imageUrl}" alt="${admin.name}" onerror="this.src='<?php echo e(asset('default-user.png')); ?>'">
                    <div class="admin-status-indicator ${statusClass}" title="${statusText}"></div>
                </div>
                <div class="message-admin-info">
                    <div class="message-admin-name">${admin.name}</div>
                    <div class="message-admin-email">${admin.email}</div>
                </div>
            </div>
        `;
    });
    
    adminsList.innerHTML = html;
}

function messageOpenChat(adminId, adminName, isOnline = false) {
    if (!messageDropdownExists()) return;
    
    document.getElementById('messageAdminId').value = adminId;
    // Store admin online status for later use
    document.getElementById('messageAdminId').setAttribute('data-is-online', isOnline ? 'true' : 'false');
    
    // Hide the admin list
    const adminsList = document.getElementById('messageAdminsList');
    if (adminsList) {
        adminsList.style.display = 'none';
    }
    
    // Show the chat area
    const chatArea = document.getElementById('messageChatArea');
    if (chatArea) {
        chatArea.style.display = 'block';
    }
    
    // Update header
    const header = document.querySelector('.message-header h3');
    if (header) {
        const statusBadge = isOnline ? '<span style="color: #28a745; font-size: 0.9rem; font-weight: normal;">• Online</span>' : '<span style="color: #dc3545; font-size: 0.9rem; font-weight: normal;">• Offline</span>';
        header.innerHTML = `<i class="fas fa-comments me-2"></i>Chat with ${adminName} ${statusBadge}`;
    }
    
    // Update video call button state
    const videoCallBtn = document.querySelector('button[onclick="initiateVideoCall()"]');
    if (videoCallBtn) {
        if (isOnline) {
            videoCallBtn.disabled = false;
            videoCallBtn.title = 'Start Video Call';
            videoCallBtn.style.opacity = '1';
            videoCallBtn.style.cursor = 'pointer';
        } else {
            videoCallBtn.disabled = true;
            videoCallBtn.title = 'Attorney is offline - Video call unavailable';
            videoCallBtn.style.opacity = '0.5';
            videoCallBtn.style.cursor = 'not-allowed';
        }
    }
    
    // Show the back button when in chat view
    showMessageBackButton();
    
    messageLoadConversation(adminId);
}

async function messageLoadConversation(adminId) {
    const messagesDiv = document.getElementById('messageChatMessages');
    if (!messagesDiv) return;
    
    messagesDiv.innerHTML = '<div class="text-center text-muted py-3">Loading messages...</div>';
    
    try {
        const response = await fetch(`/api/conversation/admin/${adminId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            }
        });
        
        if (!response.ok) throw new Error('Network response was not ok');
        
        const data = await response.json();
        if (data.success) {
            document.getElementById('messageConversationId').value = data.conversation.id;
            currentMessageConversationId = data.conversation.id;
            messageDisplayMessages(data.messages);
            messageScrollToBottom();
            messageMarkAsRead(data.conversation.id);
        }
    } catch (error) {
        console.error('Error loading conversation:', error);
        messagesDiv.innerHTML = '<div class="text-center text-muted py-3">Error loading messages</div>';
    }
}

function messageDisplayMessages(messages) {
    const messagesDiv = document.getElementById('messageChatMessages');
    if (!messagesDiv) return;
    
    messagesDiv.innerHTML = '';
    
    if (!messages || messages.length === 0) {
        messagesDiv.innerHTML = '<div class="text-center text-muted py-3">No messages yet. Start the conversation!</div>';
        return;
    }
    
    messages.forEach(message => messageAppendMessage(message));
    messageScrollToBottom();
}

function messageFormatTime(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const diffMinutes = Math.floor(diff / 60000);
    const diffHours = Math.floor(diff / 3600000);
    
    if (diffMinutes < 1) return 'Just now';
    if (diffMinutes < 60) return `${diffMinutes}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function messageScrollToBottom() {
    const messagesDiv = document.getElementById('messageChatMessages');
    if (messagesDiv) messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function messageMarkAsRead(conversationId) {
    fetch(`/chat/conversations/${conversationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Content-Type': 'application/json'
        }
    }).catch(error => console.error('Error marking as read:', error));
}

async function messageSend() {
    const conversationId = document.getElementById('messageConversationId').value;
    const adminId = document.getElementById('messageAdminId').value;
    const messageInput = document.getElementById('messageChatInput');
    const message = messageInput.value.trim();
    const fileInput = document.getElementById('messageFileInput');
    
    if (!conversationId || !adminId) {
        alert('Please select an admin to chat with');
        return;
    }
    
    if (!message && (!fileInput || fileInput.files.length === 0)) return;
    
    const formData = new FormData();
    formData.append('message', message);
    formData.append('conversation_id', conversationId);
    formData.append('admin_id', adminId);
    
    if (fileInput && fileInput.files.length > 0) {
        formData.append('file', fileInput.files[0]);
    }
    
    const sendBtn = document.getElementById('messageSendBtn');
    if (sendBtn) {
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }
    
    try {
        const response = await fetch('<?php echo e(route("client.chat.send")); ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' }
        });
        
        const data = await response.json();
        if (data.success) {
            messageInput.value = '';
            if (fileInput) fileInput.value = '';
            const preview = document.getElementById('messageFilePreview');
            if (preview) preview.style.display = 'none';
            
            messageAppendMessage(data.message);
            messageScrollToBottom();
        } else {
            alert(data.message || 'Failed to send message');
        }
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Failed to send message. Please try again.');
    } finally {
        if (sendBtn) {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        }
    }
}

function messageAppendMessage(message) {
    const messagesDiv = document.getElementById('messageChatMessages');
    if (!messagesDiv) return;
    
    const isSent = message.sender_id === <?php echo e(Auth::id()); ?>;
    const messageDiv = document.createElement('div');
    messageDiv.className = `message-chat-message ${isSent ? 'sent' : 'received'}`;
    
    if (message.message_type === 'file') {
        const downloadUrl = `<?php echo e(route('chat.messages.download', '')); ?>/${message.id}`;
        const fileSize = message.file_size ? messageFormatFileSize(message.file_size) : '';
        
        // Determine file icon based on file type
        const fileIcon = messageGetFileIcon(message.file_name, message.file_mime);
        
        // Check if file is an image
        const isImage = message.file_mime && message.file_mime.startsWith('image/');
        
        if (isImage) {
            messageDiv.innerHTML = `
                <div class="message-content">
                    ${message.message && !message.message.startsWith('Sent a file:') ? 
                        `<div class="message-text">${message.message.replace(/\\[File:.*?\\]/g, '')}</div>` : ''}
                    <div class="message-file-container">
                        <div class="message-image-preview">
                            <img src="${downloadUrl}" alt="${message.file_name}" 
                                 onclick="messageOpenImageModal('${downloadUrl}', '${message.file_name}')"
                                 class="message-image-thumbnail">
                            <div class="message-image-overlay">
                                <div class="message-image-info">
                                    <div class="message-file-icon">${fileIcon}</div>
                                    <div class="message-file-details">
                                        <div class="message-file-name">${message.file_name}</div>
                                        ${fileSize ? `<div class="message-file-size">${fileSize}</div>` : ''}
                                    </div>
                                </div>
                                <div class="message-image-actions">
                                    <a href="${downloadUrl}" class="message-image-action" download="${message.file_name}" 
                                       title="Download image">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button type="button" class="message-image-action" 
                                            onclick="messageOpenImageModal('${downloadUrl}', '${message.file_name}')"
                                            title="View full size">
                                        <i class="fas fa-expand"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="message-time ${isSent ? 'sent' : ''}">${messageFormatTime(message.created_at)}</div>
            `;
        } else {
            // Non-image file handling (your existing code)
            messageDiv.innerHTML = `
                <div class="message-content">
                    ${message.message ? `<div class="message-text">${message.message}</div>` : ''}
                    <div class="message-file-container">
                        <div class="message-file-info">
                            <div class="message-file-icon">${fileIcon}</div>
                            <div class="message-file-details">
                                <div class="message-file-name">${message.file_name}</div>
                                ${fileSize ? `<div class="message-file-size">${fileSize}</div>` : ''}
                            </div>
                        </div>
                        <a href="${downloadUrl}" class="message-file-download" target="_blank" download>
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                </div>
                <div class="message-time ${isSent ? 'sent' : ''}">${messageFormatTime(message.created_at)}</div>
            `;
        }
    } else {
        // Text message handling (your existing code)
        messageDiv.innerHTML = `
            <div class="message-content">
                <div class="message-text">${message.message}</div>
            </div>
            <div class="message-time ${isSent ? 'sent' : ''}">${messageFormatTime(message.created_at)}</div>
        `;
    }
    
    messagesDiv.appendChild(messageDiv);
}

function messageRemoveFile() {
    const fileInput = document.getElementById('messageFileInput');
    const preview = document.getElementById('messageFilePreview');
    
    if (fileInput) fileInput.value = '';
    if (preview) {
        preview.style.display = 'none';
        preview.innerHTML = '';
    }
}

async function messageUpdateUnreadBadge() {
    try {
        const response = await fetch('<?php echo e(route("chat.unread-count")); ?>');
        const data = await response.json();
        const badge = document.getElementById('messageUnreadBadge');
        const indicator = document.getElementById('messageNotificationIndicator');
        const container = document.getElementById('messageIconContainer');
        
        if (badge && indicator && container) {
            if (data.count > 0) {
                badge.textContent = data.count > 9 ? '9+' : data.count;
                badge.style.display = 'flex';
                indicator.style.display = 'block';
                container.classList.add('has-unread');
                
                if (data.count > 5) container.classList.add('many-unread');
                else container.classList.remove('many-unread');
                
                const envelopeIcon = document.querySelector('.message-icon-btn .fa-envelope');
                if (envelopeIcon) {
                    envelopeIcon.classList.remove('far');
                    envelopeIcon.classList.add('fas');
                    envelopeIcon.style.color = '#ff4757';
                }
            } else {
                badge.style.display = 'none';
                indicator.style.display = 'none';
                container.classList.remove('has-unread', 'many-unread');
                
                const envelopeIcon = document.querySelector('.message-icon-btn .fa-envelope');
                if (envelopeIcon) {
                    envelopeIcon.classList.remove('fas');
                    envelopeIcon.classList.add('far');
                    envelopeIcon.style.color = '';
                }
            }
        }
    } catch (error) {
        console.error('Error updating badge:', error);
    }
}

function initMessageEventListeners() {
    const messageForm = document.getElementById('messageChatForm');
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            messageSend();
        });
    }
    
    const fileInput = document.getElementById('messageFileInput');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                const preview = document.getElementById('messageFilePreview');
                if (preview) {
                    preview.innerHTML = `
                        <i class="fas fa-file"></i>
                        <span class="message-file-name">${file.name}</span>
                        <button type="button" class="message-file-remove" onclick="messageRemoveFile()">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    preview.style.display = 'flex';
                }
            }
        });
    }
    
    const messageIconContainer = document.querySelector('.message-icon-container');
    if (messageIconContainer) {
        messageUpdateUnreadBadge();
        setInterval(messageUpdateUnreadBadge, 30000);
    }
}

// Initialize message functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    <?php if($showAuthenticatedUI): ?>
            initMessageEventListeners();
            messageUpdateUnreadBadge();
            setInterval(messageUpdateUnreadBadge, 30000);
            
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) messageUpdateUnreadBadge();
            });
        <?php endif; ?>
});
// Show the back button
function showMessageBackButton() {
    const backButtonContainer = document.getElementById('messageBackButtonContainer');
    if (backButtonContainer) {
        backButtonContainer.style.display = 'block';
    }
}

// Hide the back button
function hideMessageBackButton() {
    const backButtonContainer = document.getElementById('messageBackButtonContainer');
    if (backButtonContainer) {
        backButtonContainer.style.display = 'none';
    }
}

// Go back to admin list from chat view
function messageBackToAdminList() {
    // Hide the chat area
    const chatArea = document.getElementById('messageChatArea');
    if (chatArea) {
        chatArea.style.display = 'none';
    }
    
    // Show the admin list
    const adminsList = document.getElementById('messageAdminsList');
    const adminsDiv = document.getElementById('messageAdmins');
    
    if (adminsList) {
        adminsList.style.display = 'block';
    }
    
    if (adminsDiv) {
        adminsDiv.style.display = 'block';
    }
    
    // Hide the back button when in admin list view
    hideMessageBackButton();
    
    // Reset conversation tracking
    currentMessageConversationId = null;
    document.getElementById('messageConversationId').value = '';
    document.getElementById('messageAdminId').value = '';
    
    // Clear the message input
    const messageInput = document.getElementById('messageChatInput');
    if (messageInput) {
        messageInput.value = '';
    }
    
    // Clear file input if any
    const fileInput = document.getElementById('messageFileInput');
    if (fileInput) {
        fileInput.value = '';
    }
    
    // Hide file preview
    const filePreview = document.getElementById('messageFilePreview');
    if (filePreview) {
        filePreview.style.display = 'none';
        filePreview.innerHTML = '';
    }
    
    console.log('Returned to admin list view');
}

// Message Modal Functions
function showMessageModal(type, message, errors = []) {
    if (type === 'success') {
        const modal = document.getElementById('messageSuccessModal');
        const textElement = document.getElementById('successMessageText');
        
        if (textElement) textElement.textContent = message;
        if (modal) {
            modal.style.display = 'block';
            modal.querySelector('.message-modal-content').style.animation = 'none';
            setTimeout(() => {
                modal.querySelector('.message-modal-content').style.animation = '';
            }, 10);
        }
        
        // Auto-close after 5 seconds
        setTimeout(() => closeMessageModal('success'), 5000);
        
    } else if (type === 'error') {
        const modal = document.getElementById('messageErrorModal');
        const textElement = document.getElementById('errorMessageText');
        
        if (textElement) textElement.textContent = message;
        if (modal) {
            modal.style.display = 'block';
            modal.querySelector('.message-modal-content').style.animation = 'none';
            setTimeout(() => {
                modal.querySelector('.message-modal-content').style.animation = '';
            }, 10);
        }
        
        // Auto-close after 6 seconds
        setTimeout(() => closeMessageModal('error'), 6000);
        
    } else if (type === 'validation' && errors.length > 0) {
        const modal = document.getElementById('validationErrorsModal');
        const errorsList = document.getElementById('validationErrorsList');
        
        if (errorsList) {
            errorsList.innerHTML = '';
            errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                errorsList.appendChild(li);
            });
        }
        
        if (modal) {
            modal.style.display = 'block';
            modal.querySelector('.message-modal-content').style.animation = 'none';
            setTimeout(() => {
                modal.querySelector('.message-modal-content').style.animation = '';
            }, 10);
        }
        
        // Don't auto-close validation errors
    }
}

function closeMessageModal(type) {
    let modal;
    
    switch(type) {
        case 'success':
            modal = document.getElementById('messageSuccessModal');
            break;
        case 'error':
            modal = document.getElementById('messageErrorModal');
            break;
        case 'validation':
            modal = document.getElementById('validationErrorsModal');
            break;
    }
    
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modals = ['messageSuccessModal', 'messageErrorModal', 'validationErrorsModal'];
    
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal && event.target === modal) {
            modal.style.display = 'none';
        }
    });
});

// Check for session messages on page load
document.addEventListener('DOMContentLoaded', function() {
    // Small delay to ensure DOM is fully loaded
    setTimeout(() => {
        <?php if(session('success')): ?>
            showMessageModal('success', '<?php echo e(session('success')); ?>');
        <?php endif; ?>
        
        <?php if(session('error')): ?>
            showMessageModal('error', '<?php echo e(session('error')); ?>');
        <?php endif; ?>
        
        // Check for validation errors
        <?php if($errors->any()): ?>
            const errors = [];
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                errors.push('<?php echo e($error); ?>');
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            showMessageModal('validation', '', errors);
        <?php endif; ?>
    }, 300);
});
// Also add keyboard support
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeMessageModal('success');
        closeMessageModal('error');
        closeMessageModal('validation');
    }
});

// Form validation with modal feedback
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.message-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Clear previous field errors
            const errorSpans = form.querySelectorAll('.error-message');
            errorSpans.forEach(span => span.remove());
            
            const inputs = form.querySelectorAll('input[required], textarea[required]');
            let hasErrors = false;
            const errors = [];
            
            // Check required fields
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    hasErrors = true;
                    const fieldName = input.previousElementSibling.textContent.replace('*', '').trim();
                    errors.push(`${fieldName} is required`);
                    
                    // Add inline error
                    const errorSpan = document.createElement('span');
                    errorSpan.className = 'error-message';
                    errorSpan.style.cssText = 'color: #dc3545; font-size: 0.875rem; display: block; margin-top: 5px;';
                    errorSpan.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${fieldName} is required`;
                    input.parentNode.appendChild(errorSpan);
                    
                    // Add visual feedback
                    input.style.borderColor = '#dc3545';
                    input.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.1)';
                } else {
                    input.style.borderColor = '';
                    input.style.boxShadow = '';
                }
            });
            
            // Email validation
            const emailInput = document.getElementById('email');
            if (emailInput && emailInput.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value.trim())) {
                    hasErrors = true;
                    errors.push('Please enter a valid email address');
                    
                    const errorSpan = document.createElement('span');
                    errorSpan.className = 'error-message';
                    errorSpan.style.cssText = 'color: #dc3545; font-size: 0.875rem; display: block; margin-top: 5px;';
                    errorSpan.innerHTML = `<i class="fas fa-exclamation-circle"></i> Please enter a valid email address`;
                    emailInput.parentNode.appendChild(errorSpan);
                    
                    emailInput.style.borderColor = '#dc3545';
                    emailInput.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.1)';
                }
            }
            
            // Message length validation
            const messageInput = document.getElementById('message');
            if (messageInput && messageInput.value.trim()) {
                if (messageInput.value.trim().length < 10) {
                    hasErrors = true;
                    errors.push('Message must be at least 10 characters long');
                    
                    const errorSpan = document.createElement('span');
                    errorSpan.className = 'error-message';
                    errorSpan.style.cssText = 'color: #dc3545; font-size: 0.875rem; display: block; margin-top: 5px;';
                    errorSpan.innerHTML = `<i class="fas fa-exclamation-circle"></i> Message must be at least 10 characters`;
                    messageInput.parentNode.appendChild(errorSpan);
                    
                    messageInput.style.borderColor = '#dc3545';
                    messageInput.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.1)';
                }
            }
            
            if (hasErrors) {
                e.preventDefault();
                showMessageModal('validation', '', errors);
                
                // Scroll to first error
                const firstError = form.querySelector('.error-message');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    const input = firstError.parentNode.querySelector('input, textarea');
                    if (input) input.focus();
                }
            }
        });
        
        // Remove error styling when user starts typing
        const inputs = form.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = '';
                this.style.boxShadow = '';
                const errorSpan = this.parentNode.querySelector('.error-message');
                if (errorSpan) errorSpan.remove();
            });
        });
    }
});
</script>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ==================== LOGOUT MODAL FUNCTIONS ====================
// Create style element for animations
const modalAnimationStyles = document.createElement('style');
modalAnimationStyles.textContent = `
    
    
    .modal-click-feedback {
        animation: modalClickFeedback 0.3s ease-in-out;
    }
    
    @keyframes modalEntrance {
        0% {
            opacity: 0;
            transform: scale(0.95) translateY(-10px);
        }
        50% {
            opacity: 0.5;
            transform: scale(1.02) translateY(-2px);
        }
        100% {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
    
    @keyframes modalClickFeedback {
        0% { transform: scale(1); }
        50% { transform: scale(1.03); }
        100% { transform: scale(1); }
    }
`;

// Add styles to document head
document.head.appendChild(modalAnimationStyles);

// Flag to track modal state
let isModalAnimating = false;

// Modified function to close the logout confirmation modal (only via close button or cancel)
function closeLogoutModal() {
    const modal = document.getElementById('logoutConfirmationModal');
    if (modal) {
        modal.style.display = 'none';
        
        // Remove animation classes
        const modalContent = modal.querySelector('.simple-modal-content');
        if (modalContent) {
            modalContent.classList.remove('modal-entrance-animation', 'modal-click-feedback');
        }
    }
}

// Modified function to show logout modal with entrance animation
function showLogoutModal(event) {
    event.preventDefault(); // This line was causing the error
    event.stopPropagation();
    
    // Close profile dropdown if open
    const dropdown = document.getElementById("dropdownContent");
    if (dropdown) dropdown.style.display = "none";
    
    // Show the logout modal
    const modal = document.getElementById('logoutConfirmationModal');
    if (modal) {
        modal.style.display = 'block';
        
        // Reset and apply entrance animation
        const modalContent = modal.querySelector('.simple-modal-content');
        if (modalContent) {
            // Remove any existing animation classes
            modalContent.classList.remove('modal-entrance-animation', 'modal-click-feedback');
            
            // Force reflow to restart animation
            void modalContent.offsetWidth;
            
            // Add entrance animation with delay
            setTimeout(() => {
                modalContent.classList.add('modal-entrance-animation');
                isModalAnimating = false;
            }, 50);
        }
    }
}

// Function to handle outside clicks with animation feedback
function handleOutsideClick(event) {
    const modal = document.getElementById('logoutConfirmationModal');
    const modalContent = modal?.querySelector('.simple-modal-content');
    const overlay = modal?.querySelector('.simple-modal-overlay');
    
    // Check if click is outside the modal content but inside the overlay
    if (modal && modal.style.display === 'block' && 
        modalContent && overlay && 
        overlay.contains(event.target) && 
        !modalContent.contains(event.target)) {
        
        // Prevent modal from closing
        event.stopPropagation();
        
        // Only trigger animation if not already animating
        if (!isModalAnimating && modalContent) {
            isModalAnimating = true;
            
            // Add click feedback animation
            modalContent.classList.remove('modal-click-feedback');
            void modalContent.offsetWidth; // Force reflow
            modalContent.classList.add('modal-click-feedback');
            
            // Reset animation flag after animation completes
            setTimeout(() => {
                isModalAnimating = false;
                modalContent.classList.remove('modal-click-feedback');
            }, 300);
        }
    }
}

// Modified performLogout function
function performLogout() {
    // Remove animation classes before submission
    const modal = document.getElementById('logoutConfirmationModal');
    if (modal) {
        const modalContent = modal.querySelector('.simple-modal-content');
        if (modalContent) {
            modalContent.classList.remove('modal-entrance-animation', 'modal-click-feedback');
        }
    }
    
    // Submit the logout form
    const form = document.getElementById('logout-form');
    if (form) form.submit();
}

// Setup event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // 1. Add click event listener for outside clicks
    document.addEventListener('click', handleOutsideClick);
    
    // 2. Prevent overlay click from closing modal
    const overlay = document.querySelector('.simple-modal-overlay');
    if (overlay) {
        // Remove existing onclick handler
        overlay.onclick = null;
        
        // Add new handler that only prevents closing
        overlay.addEventListener('click', function(event) {
            event.stopPropagation();
            handleOutsideClick(event);
        });
    }
    
    // 3. Ensure Cancel button properly closes modal
    const cancelBtn = document.querySelector('.simple-modal-footer .btn-secondary');
    if (cancelBtn) {
        cancelBtn.onclick = closeLogoutModal;
    }
    
    // 4. Ensure Close button (Ã—) properly closes modal
    const closeBtn = document.querySelector('.simple-modal-close');
    if (closeBtn) {
        closeBtn.onclick = closeLogoutModal;
    }
    
    // 5. Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        const modal = document.getElementById('logoutConfirmationModal');
        if (event.key === 'Escape' && modal && modal.style.display === 'block') {
            closeLogoutModal();
        }
    });
    
    // 6. Close any open dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('dropdownContent');
        if (dropdown && !event.target.closest('.profile-dropdown')) {
            dropdown.style.display = 'none';
        }
    });
});
// ==================== END LOGOUT MODAL FUNCTIONS ====================

// ==================== START: EDIT ACCOUNT MODAL FUNCTIONS ====================
let resendTimer = null;
let canResend = true;
let resendCooldown = 60; // 60 seconds cooldown

// Toggle password visibility with eye icon
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');
    
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

function openEditAccountModal() {
    console.log('Opening edit account modal');
    const modal = document.getElementById('editAccountModal');
    
    // Reset form and messages
    document.getElementById('editSuccessMessage').style.display = 'none';
    document.getElementById('editErrorMessage').style.display = 'none';
    document.getElementById('editInfoMessage').style.display = 'none';
    document.getElementById('passwordChangeForm').style.display = 'none';
    document.getElementById('otpVerificationForm').style.display = 'none';
    
    // Clear all error messages
    const errorElements = document.querySelectorAll('.form-error');
    errorElements.forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    // Clear form inputs
    document.getElementById('new_password').value = '';
    document.getElementById('new_password_confirmation').value = '';
    document.getElementById('otp').value = '';
    
    // Reset password fields to password type
    document.getElementById('new_password').type = 'password';
    document.getElementById('new_password_confirmation').type = 'password';
    
    // Reset eye icons
    const eyeIcons = document.querySelectorAll('.password-toggle i');
    eyeIcons.forEach(icon => {
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    });
    
    modal.style.display = 'block';
    
    // Close dropdown if open
    const dropdown = document.getElementById('dropdownContent');
    if (dropdown) {
        dropdown.style.display = 'none';
    }
}

function closeEditAccountModal() {
    console.log('Closing edit account modal');
    document.getElementById('editAccountModal').style.display = 'none';
    
    // Clear any timers
    if (resendTimer) {
        clearInterval(resendTimer);
        resendTimer = null;
    }
}

function togglePasswordForm() {
    const passwordForm = document.getElementById('passwordChangeForm');
    const mainForm = document.getElementById('editAccountForm');
    const otpForm = document.getElementById('otpVerificationForm');
    
    if (passwordForm.style.display === 'block') {
        passwordForm.style.display = 'none';
        mainForm.style.display = 'block';
    } else {
        passwordForm.style.display = 'block';
        mainForm.style.display = 'none';
        otpForm.style.display = 'none';
        
        // Clear any previous messages
        document.getElementById('editSuccessMessage').style.display = 'none';
        document.getElementById('editErrorMessage').style.display = 'none';
        document.getElementById('editInfoMessage').style.display = 'none';
    }
}

function cancelPasswordChange() {
    document.getElementById('passwordChangeForm').style.display = 'none';
    document.getElementById('otpVerificationForm').style.display = 'none';
    document.getElementById('editAccountForm').style.display = 'block';
    
    // Clear inputs
    document.getElementById('new_password').value = '';
    document.getElementById('new_password_confirmation').value = '';
    document.getElementById('otp').value = '';
    
    // Reset password fields to password type
    document.getElementById('new_password').type = 'password';
    document.getElementById('new_password_confirmation').type = 'password';
    
    // Reset eye icons
    const eyeIcons = document.querySelectorAll('.password-toggle i');
    eyeIcons.forEach(icon => {
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    });
    
    // Clear timers
    if (resendTimer) {
        clearInterval(resendTimer);
        resendTimer = null;
    }
}

// Handle account form submission (name, cp_number, email)
document.getElementById('editAccountForm').addEventListener('submit', async function(event) {
    event.preventDefault();

    const loadingSpinner = document.getElementById('editLoadingSpinner');
    const successMessage = document.getElementById('editSuccessMessage');
    const errorMessage = document.getElementById('editErrorMessage');
    const errorIdMap = {
        name: 'edit_nameError',
        cp_number: 'cp_numberError',
        email: 'edit_emailError'
    };

    loadingSpinner.style.display = 'block';
    successMessage.style.display = 'none';
    errorMessage.style.display = 'none';

    const errorElements = document.querySelectorAll('.form-error');
    errorElements.forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });

    const formData = new FormData(this);

    try {
        const response = await fetch('<?php echo e(route("account.update")); ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const contentType = response.headers.get('content-type') || '';
        const data = contentType.includes('application/json')
            ? await response.json()
            : {
                success: false,
                message: 'Unexpected server response. Please try again.'
            };

        if (response.ok && data.success) {
            successMessage.textContent = data.message;
            successMessage.style.display = 'block';

            if (data.user) {
                const profileButton = document.querySelector('.profile-dropdown button');
                if (profileButton) {
                    profileButton.textContent = 'Welcome, ' + data.user.name + '!!';
                }

                const dropdownName = document.querySelector('.dropdown-content span');
                if (dropdownName) {
                    dropdownName.innerHTML = data.user.name + ' &nbsp;<i class="fas fa-user"></i>';
                }
            }

            setTimeout(() => {
                closeEditAccountModal();
            }, 2000);
        } else {
            if (data.errors) {
                for (const field in data.errors) {
                    const errorElement = document.getElementById(errorIdMap[field] || (field + 'Error'));
                    if (errorElement) {
                        errorElement.textContent = data.errors[field][0];
                        errorElement.style.display = 'block';
                    }
                }
            }

            errorMessage.textContent = data.message || 'An error occurred. Please try again.';
            errorMessage.style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        errorMessage.textContent = 'Network error. Please check your connection and try again.';
        errorMessage.style.display = 'block';
    } finally {
        loadingSpinner.style.display = 'none';
    }
});

// Handle password change request (send OTP)
document.getElementById('passwordChangeForm').addEventListener('submit', async function(event) {
    event.preventDefault();
    
    // Show loading spinner
    document.getElementById('passwordLoadingSpinner').style.display = 'block';
    
    // Hide messages
    document.getElementById('editSuccessMessage').style.display = 'none';
    document.getElementById('editErrorMessage').style.display = 'none';
    document.getElementById('editInfoMessage').style.display = 'none';
    
    // Clear previous errors
    const errorElements = document.querySelectorAll('.form-error');
    errorElements.forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });

    const formData = new FormData(this);
    
    try {
        const response = await fetch('/account/request-password-change', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();
        
        if (data.success) {
            if (data.requires_otp) {
                // Show OTP verification form
                document.getElementById('passwordChangeForm').style.display = 'none';
                document.getElementById('otpVerificationForm').style.display = 'block';
                
                // Show info message
                const infoMessage = document.getElementById('editInfoMessage');
                infoMessage.textContent = data.message;
                infoMessage.style.display = 'block';
                
                // Start resend timer
                startResendTimer();
            }
            
            // Hide loading spinner
            document.getElementById('passwordLoadingSpinner').style.display = 'none';
            
        } else {
            // Handle validation errors
            if (data.errors) {
                for (const field in data.errors) {
                    const errorElement = document.getElementById(field + 'Error');
                    if (errorElement) {
                        errorElement.textContent = data.errors[field][0];
                        errorElement.style.display = 'block';
                    }
                }
            } else {
                // Show general error
                const errorMessage = document.getElementById('editErrorMessage');
                errorMessage.textContent = data.message || 'An error occurred. Please try again.';
                errorMessage.style.display = 'block';
            }
            
            // Hide loading spinner
            document.getElementById('passwordLoadingSpinner').style.display = 'none';
        }
        
    } catch (error) {
        console.error('Error:', error);
        const errorMessage = document.getElementById('editErrorMessage');
        errorMessage.textContent = 'Network error. Please check your connection and try again.';
        errorMessage.style.display = 'block';
        
        // Hide loading spinner
        document.getElementById('passwordLoadingSpinner').style.display = 'none';
    }
});

// Handle OTP verification and password change
document.getElementById('otpVerificationForm').addEventListener('submit', async function(event) {
    event.preventDefault();
    
    // Show loading spinner
    document.getElementById('otpLoadingSpinner').style.display = 'block';
    
    // Hide messages
    document.getElementById('editSuccessMessage').style.display = 'none';
    document.getElementById('editErrorMessage').style.display = 'none';
    document.getElementById('editInfoMessage').style.display = 'none';
    
    // Clear previous errors
    document.getElementById('otpError').style.display = 'none';
    document.getElementById('otpError').textContent = '';

    const formData = new FormData(this);
    
    try {
        const response = await fetch('/account/verify-otp-password', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();
        
        if (data.success) {
            // Show success message
            const successMessage = document.getElementById('editSuccessMessage');
            successMessage.textContent = data.message;
            successMessage.style.display = 'block';
            
            // Clear timers
            if (resendTimer) {
                clearInterval(resendTimer);
                resendTimer = null;
            }
            
            // Hide loading spinner
            document.getElementById('otpLoadingSpinner').style.display = 'none';
            
            // Auto-close modal after 3 seconds
            setTimeout(() => {
                closeEditAccountModal();
            }, 3000);
            
        } else {
            // Handle validation errors
            if (data.errors) {
                for (const field in data.errors) {
                    const errorElement = document.getElementById(field + 'Error');
                    if (errorElement) {
                        errorElement.textContent = data.errors[field][0];
                        errorElement.style.display = 'block';
                    }
                }
            } else {
                // Show general error
                const errorMessage = document.getElementById('editErrorMessage');
                errorMessage.textContent = data.message || 'An error occurred. Please try again.';
                errorMessage.style.display = 'block';
            }
            
            // Hide loading spinner
            document.getElementById('otpLoadingSpinner').style.display = 'none';
        }
        
    } catch (error) {
        console.error('Error:', error);
        const errorMessage = document.getElementById('editErrorMessage');
        errorMessage.textContent = 'Network error. Please check your connection and try again.';
        errorMessage.style.display = 'block';
        
        // Hide loading spinner
        document.getElementById('otpLoadingSpinner').style.display = 'none';
    }
});

// Resend OTP function
async function resendOtp() {
    if (!canResend) return;
    
    try {
        const response = await fetch('/account/resend-otp', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        });

        const data = await response.json();
        
        if (data.success) {
            // Show info message
            const infoMessage = document.getElementById('editInfoMessage');
            infoMessage.textContent = data.message;
            infoMessage.style.display = 'block';
            
            // Start resend timer again
            startResendTimer();
        } else {
            // Show error
            const errorMessage = document.getElementById('editErrorMessage');
            errorMessage.textContent = data.message || 'Error resending verification code.';
            errorMessage.style.display = 'block';
        }
        
    } catch (error) {
        console.error('Error:', error);
        const errorMessage = document.getElementById('editErrorMessage');
        errorMessage.textContent = 'Network error. Please check your connection and try again.';
        errorMessage.style.display = 'block';
    }
}

// Start resend timer
function startResendTimer() {
    canResend = false;
    let timeLeft = resendCooldown;
    
    const resendLink = document.getElementById('resendOtpLink');
    const timerSpan = document.getElementById('resendTimer');
    
    resendLink.classList.add('disabled');
    timerSpan.style.display = 'inline';
    timerSpan.textContent = ` (${timeLeft}s)`;
    
    resendTimer = setInterval(() => {
        timeLeft--;
        timerSpan.textContent = ` (${timeLeft}s)`;
        
        if (timeLeft <= 0) {
            clearInterval(resendTimer);
            resendTimer = null;
            canResend = true;
            resendLink.classList.remove('disabled');
            timerSpan.style.display = 'none';
        }
    }, 1000);
}

// Profile dropdown toggle function
function toggleDropdown(event) {
    event.stopPropagation();
    var dropdown = document.getElementById("dropdownContent");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('dropdownContent');
    const profileButton = document.querySelector('.profile-dropdown button');
    
    if (dropdown && profileButton && !dropdown.contains(event.target) && !profileButton.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});

// Escape key to close modal
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeEditAccountModal();
    }
});

// Close modal when clicking outside
window.onclick = function(event) {
    var accountModal = document.getElementById('accountModal');
    var editAccountModal = document.getElementById('editAccountModal');
    var accountModalContent = accountModal?.querySelector('.modal-content');
    var editAccountModalContent = editAccountModal?.querySelector('.modal-content, .edit-account-modal-content');
    
    // Close accountModal if click is outside modal-content
    if (accountModal && accountModalContent && event.target === accountModal) {
        closeAccountModal();
    }
    // Close editAccountModal if click is outside modal-content
    if (editAccountModal && editAccountModalContent && event.target === editAccountModal) {
        closeEditAccountModal();
    }
};
// ==================== END: EDIT ACCOUNT MODAL FUNCTIONS ====================
// Function to handle video call initiation
function initiateVideoCall(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    const adminIdField = document.getElementById('messageAdminId');
    const isOnline = adminIdField.getAttribute('data-is-online') === 'true';
    
    if (!isOnline) {
        // Show offline modal
        const offlineModal = new bootstrap.Modal(document.getElementById('adminOfflineModal'));
        offlineModal.show();
        return false;
    } else {
        // Handle online video call (integrate with WebRTC)
        const adminId = adminIdField.value;
        console.log('Starting video call with admin:', adminId);
        // TODO: Integrate with existing WebRTC call system
        // You can call your existing video call function here
    }
    return false;
}

function checkPasswordStrength(password) {
    let strength = 0;
    let text = 'Very Weak';
    let color = '#d13636';
    let width = '10%';

    // Check length
    if (password.length >= 8) {
        strength += 1;
    }

    // Check for mixed case
    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) {
        strength += 1;
    }

    // Check for numbers
    if (password.match(/\d/)) {
        strength += 1;
    }

    // Check for special characters
    if (password.match(/[^a-zA-Z\d]/)) {
        strength += 1;
    }

    // Determine the strength level
    if (password.length === 0) {
        text = 'Very Weak';
        color = '#d13636';
        width = '10%';
    } else if (password.length < 8) {
        text = 'Too Short';
        color = '#d13636';
        width = '20%';
    } else {
        switch (strength) {
            case 1:
                text = 'Weak';
                color = '#ff6b6b';
                width = '30%';
                break;
            case 2:
                text = 'Fair';
                color = '#ffa500';
                width = '50%';
                break;
            case 3:
                text = 'Good';
                color = '#4caf50';
                width = '75%';
                break;
            case 4:
                text = 'Strong';
                color = '#2ecc71';
                width = '100%';
                break;
            default:
                text = 'Very Weak';
                color = '#d13636';
                width = '10%';
        }
    }

    // Update the meter bar and text
    const meterBar = document.getElementById('passwordStrengthMeterBar');
    const meterText = document.getElementById('passwordStrengthText');

    if (meterBar) {
        meterBar.style.width = width;
        meterBar.style.backgroundColor = color;
    }

    if (meterText) {
        meterText.textContent = text;
        meterText.style.color = color;
    }
}
</script>


<!-- Message Success Modal -->
<div id="messageSuccessModal" class="message-modal">
  <div class="message-modal-content">
    <button class="message-modal-close" onclick="closeMessageModal('success')">&times;</button>
    <div class="message-modal-icon">
      <i class="fas fa-check-circle"></i>
    </div>
    <h3 class="message-modal-title">Success</h3>
    <p id="successMessageText" class="message-modal-text"></p>
    <div class="message-modal-actions">
      <button class="message-modal-btn" onclick="closeMessageModal('success')">OK</button>
    </div>
  </div>
</div>

<!-- Message Error Modal -->
<div id="messageErrorModal" class="message-modal">
  <div class="message-modal-content">
    <button class="message-modal-close" onclick="closeMessageModal('error')">&times;</button>
    <div class="message-modal-icon">
      <i class="fas fa-exclamation-circle"></i>
    </div>
    <h3 class="message-modal-title">Error</h3>
    <p id="errorMessageText" class="message-modal-text"></p>
    <div class="message-modal-actions">
      <button class="message-modal-btn" onclick="closeMessageModal('error')">OK</button>
    </div>
  </div>
</div>

<!-- Validation Errors Modal -->
<div id="validationErrorsModal" class="message-modal">
  <div class="message-modal-content">
    <button class="message-modal-close" onclick="closeMessageModal('validation')">&times;</button>
    <div class="message-modal-icon">
      <i class="fas fa-exclamation-triangle"></i>
    </div>
    <h3 class="message-modal-title">Please fix the following:</h3>
    <div class="message-modal-text">
      <ul id="validationErrorsList" class="message-modal-list"></ul>
    </div>
    <div class="message-modal-actions">
      <button class="message-modal-btn" onclick="closeMessageModal('validation')">OK</button>
    </div>
  </div>
</div>

<!-- WebRTC Call Manager -->
<script src="<?php echo e(asset('js/webrtc-call.js')); ?>"></script>
<?php echo $__env->make('partials.notification-badge-visibility', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\contact.blade.php ENDPATH**/ ?>