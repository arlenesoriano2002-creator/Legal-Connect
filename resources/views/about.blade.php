<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
<title>Legal Connect - About Us</title>
<link rel="stylesheet" href="{{ asset('css/about.blade.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display&family=Roboto+Condensed&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.3/css/all.css">
</head>
<body>
    @php
        $userIsClient = auth()->check() && auth()->user()->role === 'client';
        $showAuthenticatedUI = $userIsClient; // Keep client UI if Laravel session is authenticated
    @endphp

    <header>
        <a href="{{ url('/') }}?guest=1" class="logo">
            <img class="logo-icon" src="{{ asset('logo6.png')}}" alt="Legal Connect Logo">
            <div class="logo-text">Legal Connect</div>
        </a>
        <button class="burger-btn" onclick="toggleNav()">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        <nav id="main-nav">
            <a href="{{ url('/') }}?guest=1" class="admin-login">Home</a>
            <a href="{{ url('/about') }}" class="admin-login active">About Us</a>
            <a href="{{ url('/testimonial') }}" class="admin-login">Testimonials</a>
            <a href="{{ url('/contact') }}" class="admin-login">Contact</a>
            
            @if($showAuthenticatedUI)
                <div class="profile-dropdown">
                    <button type="button" onclick="toggleDropdown(event)">
                        Welcome, {{ Auth::user()->name }}!!
                    </button>
                    <div class="dropdown-content" id="dropdownContent">
                        <span>{{ Auth::user()->name }} &nbsp;<i class="fas fa-user"></i></span>
                        <hr>
                        <a href="#" onclick="openAccountModal()" class="link-a">Account</a>
                        <a href="#" onclick="openEditAccountModal()" class="link-a">Edit Account</a>
                        <hr>
                        <a href="#" onclick="showLogoutModal()">Logout</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ url('/login') }}" class="admin-login">Login/Register</a>
            @endif
        </nav>
        
        <div class="header-icons-container">
            @if($showAuthenticatedUI)
                <!-- Notification Dropdown -->
                <div class="notification-icon-container" id="notificationIconContainer">
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

                @if(auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->role !== 'superadmin')
                    <!-- Message Dropdown -->
                    <div class="message-icon-container" id="messageIconContainer">
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
                                <div id="messageBackButtonContainer">
                                    <button type="button" class="message-back-btn" onclick="messageBackToAdminList()">
                                        <i class="fas fa-arrow-left"></i>
                                        <span>Back to Chat List</span>
                                    </button>
                                </div>
                                <div id="messageAdminsList" class="text-center text-muted py-3">
                                    <i class="fas fa-spinner fa-spin"></i> Loading...
                                </div>
                            </div>
                            <div class="message-chat-area" id="messageChatArea" style="display: none;">
                                <div class="message-chat-messages" id="messageChatMessages"></div>
                                <form id="messageChatForm">
                                    @csrf
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
                                        <input type="file" id="messageFileInput" style="display: none;" accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar,.7z">
                                        <button type="submit" class="message-send-btn" id="messageSendBtn">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                    <div id="messageFilePreview" class="message-file-preview" style="display: none;"></div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="message">
                <span class="subtitle select-none">Legal Connect</span>
                <h1>About Us.</h1>
                <p>Legal Connect genuinely cares, guiding clients with trust and personalized support.</p>
            </div>
        </section>
        
        <section class="section-message">
            <div class="text-content">
                <span class="pretitle select-none">THE MESSAGE</span>
                <h2>We take care of<br />our clients</h2>
                <p>
                    At Legal Connect, we believe that every client deserves not only expert legal advice but also genuine care and respect throughout their journey. We understand that legal matters often come with stress, uncertainty, and high stakes. That's why we prioritize attentive communication, transparency, and personalized support. Our team is committed to listening closely, understanding each client's unique needs, and ensuring they feel informed and empowered at every step.
                    <br /><br />
                    What sets Legal Connect apart is our client-first philosophy. We don't just handle cases — we build trust. Whether it's providing timely updates, explaining legal jargon in plain language, or being available when you need us most, we go beyond expectations to make our clients feel valued and protected. Your peace of mind is our priority, and we work tirelessly to earn and keep your confidence.
                </p>
            </div>
            <div class="image-content">
                <img src="https://storage.googleapis.com/a1aa/image/97e8cb04-ebfa-4c2f-5485-f4207a2e3658.jpg" alt="Lady Justice Statue" />
            </div>
        </section>
        
        <!--<section class="section-james">
            <div class="image-wrapper">
                <img src="{{ asset('clients image.jpg') }}" alt="Atty Karen Guillermo" />
            </div>
            <div class="content-wrapper">
                <div class="full-span">
                    <h3>Atty Karen Guillermo</h3>
                    <div class="underline"></div>
                </div>
                <p><span class="dropcap">L</span>aw is not just about rules and procedures— it is about people, fairness, and justice. As a practicing attorney, I am committed to guiding my clients with clarity, integrity, and compassion, ensuring that every concern is heard and every right is protected with diligence and care.</p>
                <div class="highlight">
                    At Legal Connect Group our<br>
                    main goal is benefit and<br>
                    happiness of our clients.
                </div>
                <p>With experience across various legal matters, I strive to provide practical solutions and sound legal advice tailored to each client's unique situation. My approach is rooted in trust, transparency, and dedication—working tirelessly to achieve outcomes that serve both justice and peace of mind.</p>
            </div>
        </section>
        
        <section id="practice-areas" class="practice-areas">
            <div class="container">
                <h2>Our Practice Areas</h2>
                <div class="areas-grid">
                    @if(isset($groupedCases) && count($groupedCases) > 0)
                        @foreach($groupedCases as $category => $caseNames)
                            <div class="area-card">
                                <h3>{{ $category }}</h3>
                                <p>{{ implode(', ', $caseNames) }}</p>
                            </div>
                        @endforeach
                    @else
                        <div class="area-card"><h3>Family Law</h3><p>Divorce, child custody, and family matters.</p></div>
                        <div class="area-card"><h3>Personal Injury</h3><p>Accidents and seeking compensation.</p></div>
                        <div class="area-card"><h3>Real Estate</h3><p>Property transactions and disputes.</p></div>
                        <div class="area-card"><h3>Business Law</h3><p>Contracts and compliance.</p></div>
                        <div class="area-card"><h3>Criminal Law</h3><p>Defense for criminal charges.</p></div>
                        <div class="area-card"><h3>Human Rights Law</h3><p>Protection of fundamental rights.</p></div>
                    @endif
                </div>
            </div>
        </section>-->

        <footer>
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-column">
                        <h3>Legal Connect</h3>
                        <ul class="footer-links">
                            <li><a href="{{ url('/') }}?guest=1">Home</a></li>
                            <li><a href="{{ url('/about') }}">About</a></li>
                            <li><a href="{{ url('/testimonial') }}">Testimonials</a></li>
                            <li><a href="{{ url('/contact') }}">Contact</a></li>
                        </ul>
                    </div>
                    <div class="footer-column">
                        <h3>Services</h3>
                        <ul class="footer-links">
                            @if(isset($categories) && $categories->count() > 0)
                                @foreach($categories as $category)
                                    <li><a href="{{ url('/about') }}#practice-areas">{{ $category }}</a></li>
                                @endforeach
                            @else
                                <li><a href="{{ url('/about') }}#practice-areas">Family Law</a></li>
                                <li><a href="{{ url('/about') }}#practice-areas">Personal Injury</a></li>
                                <li><a href="{{ url('/about') }}#practice-areas">Real Estate</a></li>
                                <li><a href="{{ url('/about') }}#practice-areas">Business Law</a></li>
                                <li><a href="{{ url('/about') }}#practice-areas">Criminal Law</a></li>
                                <li><a href="{{ url('/about') }}#practice-areas">Human Rights Law</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; 2025 Legal Connect All rights reserved.</p>
                </div>
            </div>
        </footer>
    </main>

    <!-- Modals -->
    @if($showAuthenticatedUI)
        <!-- Account Info Modal -->
        <div id="accountModal" class="modal">
            <div class="modal-content">
            <!-- <span class="close" onclick="closeAccountModal(event)">&times;</span>-->
                <div id="accountInfo">
                    <!-- Account information will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Edit Account Modal -->
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
                <form id="editAccountForm" class="edit-account-form">
                    @csrf
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ Auth::user()->name }}" required>
                        <div class="form-error" id="nameError"></div>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" class="form-control" value="{{ Auth::user()->address ?? '' }}" placeholder="Enter your address">
                        <div class="form-error" id="addressError"></div>
                    </div>
                    <div class="form-group">
                        <label for="cp_number">Phone Number</label>
                        <input type="text" id="cp_number" name="cp_number" class="form-control" value="{{ Auth::user()->cp_number }}" required>
                        <div class="form-error" id="cp_numberError"></div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ Auth::user()->email }}" required>
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
                <div class="change-password-link">
                    <a href="#" onclick="togglePasswordForm()">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                </div>

                <!-- Password Change Form -->
                <form id="passwordChangeForm" class="password-form">
                    @csrf
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
                    @csrf
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
    @endif

    <!-- Image Preview Modal -->
    <div id="imagePreviewModal" class="modal" style="display: none;">
        <div class="modal-content image-modal-content">
            <div class="modal-header">
                <h3 id="imageModalFileName"></h3>
                <button class="close-btn" onclick="messageCloseImageModal()">&times;</button>
            </div>
            <div class="modal-body text-center">
                <img src="" alt="" id="fullSizeImage" style="max-width:100%">
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-primary" id="imageDownloadLink" download>Download</a>
                <button class="btn btn-secondary" onclick="messageCloseImageModal()">Close</button>
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

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-sign-out-alt me-2"></i>Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3" style="font-size: 48px; color: #ffc107;"><i class="fas fa-exclamation-triangle"></i></div>
                    <h4>Are you sure?</h4>
                    <p>You will be logged out of your session.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="performLogout()">Log Out</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <script>
        function toggleNav() {
            document.getElementById('main-nav').classList.toggle('active');
        }
        function toggleDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById("dropdownContent");
            if (dropdown) dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        }
        function showLogoutModal() {
            const modal = new bootstrap.Modal(document.getElementById('logoutConfirmationModal'));
            modal.show();
        }
        function performLogout() {
            document.getElementById('logout-form').submit();
        }
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('dropdownContent');
            if (dropdown && !dropdown.contains(e.target)) dropdown.style.display = 'none';
        });
        
        // Click outside handlers for notification and message dropdowns
        document.addEventListener('click', function(event) {
            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationIcon = document.querySelector('.notification-icon-btn');
            
            if (notificationDropdown && !notificationDropdown.contains(event.target) && !notificationIcon.contains(event.target)) {
                notificationCloseDropdown();
            }
            
            const messageDropdown = document.getElementById('messageDropdown');
            const messageIcon = document.querySelector('.message-icon-btn');
            
            if (messageDropdown && !messageDropdown.contains(event.target) && !messageIcon.contains(event.target)) {
                messageCloseDropdown();
            }
        });
        
        // Add more common JS here as needed
    </script>

    @if($showAuthenticatedUI)
    <script>
        // Notification, Message, and Account scripts would go here
        // Re-injecting essential logic from welcome.blade.php
        function openAccountModal() {
            const userData = {
                name: "{{ Auth::user()->name ?? 'User' }}",
                address: "{{ Auth::user()->address ?? 'None' }}",
                email: "{{ Auth::user()->email ?? 'user@example.com' }}",
                cp_number: "{{ Auth::user()->cp_number ?? 'Not provided' }}",
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
        function closeAccountModal() { document.getElementById('accountModal').style.display = 'none'; }
        
        // Close modals when clicking outside
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

        // Escape key to close modals
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAccountModal();
                closeEditAccountModal();
            }
        });
        
        function openEditAccountModal() { document.getElementById('editAccountModal').style.display = 'block'; }
        function closeEditAccountModal() { document.getElementById('editAccountModal').style.display = 'none'; }

        const editAccountForm = document.getElementById('editAccountForm');
        if (editAccountForm) {
            editAccountForm.addEventListener('submit', async function(event) {
                event.preventDefault();

                const loadingSpinner = document.getElementById('editLoadingSpinner');
                const successMessage = document.getElementById('editSuccessMessage');
                const errorMessage = document.getElementById('editErrorMessage');

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
                    const response = await fetch('{{ route("account.update") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                                const errorElement = document.getElementById(field + 'Error');
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
        }

        // ==================== START: NOTIFICATION DROPDOWN FUNCTIONS ====================
        var notificationDropdownOpen = false;
        var notificationsLoaded = false;
        var badgeHidden = false;
        var notificationInterval = null;

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

        function notificationCloseDropdown(event) {
            if (event) event.stopPropagation();
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.remove('active');
            notificationDropdownOpen = false;
            clearNotificationInterval();
            // Hide badge and mark as read when closing
            badgeHidden = true;
            updateNotificationBadge(0);
            notificationMarkAllAsRead();
        }

        function clearNotificationInterval() {
            if (notificationInterval) {
                clearInterval(notificationInterval);
                notificationInterval = null;
            }
        }

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

            notificationInterval = setInterval(() => {
                console.log('Auto-refreshing notifications...');
                fetchApprovalHistory()
                    .then(data => {
                        renderNotificationDropdown(data.notifications);
                    })
                    .catch(error => {
                        console.error('Error auto-refreshing notifications:', error);
                    });
            }, 5000);
        }

       function fetchApprovalHistory() {
            return fetch('/notifications/approval-history', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            });
        }

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

        function updateNotificationBadge(count) {
            const badge = document.getElementById('notificationUnreadBadge');
            const indicator = document.getElementById('notificationIndicator');
            const container = document.getElementById('notificationIconContainer');
            
            if (badge && indicator && container) {
                if (count > 0) {
                    badge.textContent = count > 9 ? '9+' : count;
                    badge.style.display = 'flex';
                    indicator.style.display = 'block';
                    container.classList.add('has-unread');
                    
                    if (count > 5) {
                        container.classList.add('many-unread');
                    } else {
                        container.classList.remove('many-unread');
                    }
                    
                    const bellIcon = document.querySelector('.notification-icon-btn .fa-bell');
                    if (bellIcon) {
                        bellIcon.classList.remove('far');
                        bellIcon.classList.add('fas');
                        bellIcon.style.color = '#ff4757';
                    }
                } else {
                    badge.style.display = 'none';
                    indicator.style.display = 'none';
                    container.classList.remove('has-unread', 'many-unread');
                    
                    const bellIcon = document.querySelector('.notification-icon-btn .fa-bell');
                    if (bellIcon) {
                        bellIcon.classList.remove('fas');
                        bellIcon.classList.add('far');
                        bellIcon.style.color = '';
                    }
                }
            }
        }

        function notificationMarkAllAsRead() {
            fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationBadge(0);
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

        function getStatusColor(status) {
            switch (status?.toLowerCase()) {
                case 'approved': return 'green';
                case 'denied': return 'red';
                case 'pending': return 'orange';
                default: return 'blue';
            }
        }

        function getStatusIcon(status) {
            switch (status?.toLowerCase()) {
                case 'approved': return '✅';
                case 'denied': return '❌';
                case 'pending': return '⏳';
                default: return '📅';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            fetchApprovalHistory()
                .then(data => {
                    updateNotificationBadge(data.notifications.length);
                })
                .catch(error => console.error('Error loading notification count:', error));
        });

        document.addEventListener('click', function(event) {
            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationIcon = document.querySelector('.notification-icon-btn');
            
            if (notificationDropdown && !notificationDropdown.contains(event.target) && !notificationIcon.contains(event.target)) {
                notificationCloseDropdown();
            }
        });
        // ==================== END: NOTIFICATION DROPDOWN FUNCTIONS ====================

        // ==================== MESSAGE DROPDOWN FUNCTIONS ====================
        var messagePusher = null;
        var messageChannel = null;
        var currentMessageConversationId = null;

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
                
                if (indicator) {
                    indicator.style.display = 'none';
                }
                if (container) {
                    container.classList.remove('has-unread', 'many-unread');
                }
                
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
            fetch('{{ route("chat.mark-all-read") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const badge = document.getElementById('messageUnreadBadge');
                    if (badge) {
                        badge.style.display = 'none';
                    }
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
        }

        async function messageLoadAdmins() {
            if (!messageDropdownExists()) return;
            
            const adminsDiv = document.getElementById('messageAdmins');
            if (!adminsDiv) return;
            
            adminsDiv.innerHTML = `
                <div id="messageBackButtonContainer" style="margin-bottom: 15px; display: none;">
                    <button type="button" class="message-back-btn" onclick="messageBackToAdminList()" style="background: #f8f9fa; border: 1px solid #ddd; border-radius: 5px; padding: 8px 15px; cursor: pointer; display: flex; align-items: center; gap: 8px; color: #555; font-size: 14px; width: 100%;">
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
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
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

        function showMessageBackButton() {
            const backButtonContainer = document.getElementById('messageBackButtonContainer');
            if (backButtonContainer) {
                backButtonContainer.style.display = 'block';
            }
        }

        function hideMessageBackButton() {
            const backButtonContainer = document.getElementById('messageBackButtonContainer');
            if (backButtonContainer) {
                backButtonContainer.style.display = 'none';
            }
        }

        function messageBackToAdminList() {
            const chatArea = document.getElementById('messageChatArea');
            const chatMessages = document.getElementById('messageChatMessages');
            
            if (chatArea) {
                chatArea.style.display = 'none';
            }
            
            if (chatMessages) {
                chatMessages.innerHTML = '';
            }
            
            const adminsList = document.getElementById('messageAdminsList');
            const adminsDiv = document.getElementById('messageAdmins');
            
            if (adminsList) {
                adminsList.style.display = 'block';
            }
            
            if (adminsDiv) {
                adminsDiv.style.display = 'block';
            }
            
            hideMessageBackButton();
            
            currentMessageConversationId = null;
            document.getElementById('messageConversationId').value = '';
            document.getElementById('messageAdminId').value = '';
            
            const messageInput = document.getElementById('messageChatInput');
            if (messageInput) {
                messageInput.value = '';
            }
            
            const fileInput = document.getElementById('messageFileInput');
            if (fileInput) {
                fileInput.value = '';
            }
            
            const filePreview = document.getElementById('messageFilePreview');
            if (filePreview) {
                filePreview.style.display = 'none';
                filePreview.innerHTML = '';
            }
            
            console.log('Returned to admin list view');
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
                const imageUrl = admin.image ? "{{ asset('storage/ids/') }}/" + admin.image : `https://ui-avatars.com/api/?name=${encodeURIComponent(admin.name)}&background=random&color=fff&size=100`;
                const statusClass = admin.is_online ? 'online' : 'offline';
                const statusText = admin.is_online ? 'online' : 'offline';
                html += `
                    <div class="message-admin-item" onclick="messageOpenChat(${admin.id}, '${admin.name.replace(/'/g, "\\'")}', ${admin.is_online})">
                        <div style="position: relative; display: inline-block;">
                            <img src="${imageUrl}" alt="${admin.name}" onerror="this.src='{{ asset('default-user.png') }}'">
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
            
            const adminsList = document.getElementById('messageAdminsList');
            if (adminsList) {
                adminsList.style.display = 'none';
            }
            
            const chatArea = document.getElementById('messageChatArea');
            if (chatArea) {
                chatArea.style.display = 'block';
            }
            
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
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
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
            
            messages.forEach(message => {
                messageAppendMessage(message);
            });
            
            messageScrollToBottom();
        }

        const messageBackButtonCSS = `
            <style>
                .message-back-btn {
                    background: #f8f9fa;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    padding: 8px 15px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    color: #555;
                    font-size: 14px;
                    font-weight: 500;
                    width: 100%;
                    transition: all 0.2s ease;
                    margin-bottom: 15px;
                }
                
                .message-back-btn:hover {
                    background: #e9ecef;
                    border-color: #bbb;
                    color: #333;
                }
                
                .message-back-btn i {
                    font-size: 12px;
                }
                
                #messageBackButtonContainer {
                    margin-bottom: 15px;
                    display: none;
                }
                
                #messageAdminsList {
                    transition: opacity 0.3s ease;
                }
            </style>
        `;

        document.head.insertAdjacentHTML('beforeend', messageBackButtonCSS);

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
            if (messagesDiv) {
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }
        }

        function messageMarkAsRead(conversationId) {
            fetch(`/chat/conversations/${conversationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
            
            if (!message && (!fileInput || fileInput.files.length === 0)) {
                return;
            }
            
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
                const response = await fetch('{{ route("client.chat.send") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
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
            
            const isSent = message.sender_id === {{ Auth::id() }};
            const messageDiv = document.createElement('div');
            messageDiv.className = `message-chat-message ${isSent ? 'sent' : 'received'}`;
            
            if (message.message_type === 'file') {
                const downloadUrl = `{{ route('chat.messages.download', '') }}/${message.id}`;
                const fileSize = message.file_size ? messageFormatFileSize(message.file_size) : '';
                const fileIcon = messageGetFileIcon(message.file_name, message.file_mime);
                const isImage = message.file_mime && message.file_mime.startsWith('image/');
                const imageUrl = isImage ? downloadUrl : null;
                
                messageDiv.innerHTML = `
                    <div class="message-content">
                        ${message.message && !message.message.startsWith('Sent a file:') ? 
                            `<div class="message-text">${message.message.replace(/\[File:.*?\]/g, '')}</div>` : ''}
                        <div class="message-file-container">
                            ${isImage ? `
                                <div class="message-image-preview">
                                    <img src="${imageUrl}" alt="${message.file_name}" 
                                         onclick="messageOpenImageModal('${imageUrl}', '${message.file_name}')"
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
                                            <a href="${imageUrl}" class="message-image-action" download="${message.file_name}" 
                                               title="Download image">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button type="button" class="message-image-action" 
                                                    onclick="messageOpenImageModal('${imageUrl}', '${message.file_name}')"
                                                    title="View full size">
                                                <i class="fas fa-expand"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            ` : `
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
                            `}
                        </div>
                    </div>
                    <div class="message-time ${isSent ? 'sent' : ''}">${messageFormatTime(message.created_at)}</div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="message-content">
                        <div class="message-text">${message.message}</div>
                    </div>
                    <div class="message-time ${isSent ? 'sent' : ''}">${messageFormatTime(message.created_at)}</div>
                `;
            }
            
            messagesDiv.appendChild(messageDiv);
        }

        function messageOpenImageModal(imageUrl, fileName) {
            let modal = document.getElementById('imagePreviewModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'imagePreviewModal';
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-content image-modal-content">
                        <div class="modal-header">
                            <h3>${fileName}</h3>
                            <button class="close-btn" onclick="messageCloseImageModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <img src="${imageUrl}" alt="${fileName}" id="fullSizeImage">
                        </div>
                        <div class="modal-footer">
                            <a href="${imageUrl}" download="${fileName}" class="btn-download">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <button class="btn-close" onclick="messageCloseImageModal()">Close</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
                
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        messageCloseImageModal();
                    }
                });
                
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && modal.style.display === 'block') {
                        messageCloseImageModal();
                    }
                });
            } else {
                modal.querySelector('#fullSizeImage').src = imageUrl;
                modal.querySelector('.modal-header h3').textContent = fileName;
                modal.querySelector('.btn-download').href = imageUrl;
                modal.querySelector('.btn-download').download = fileName;
            }
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function messageCloseImageModal() {
            const modal = document.getElementById('imagePreviewModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        function messageGetFileIcon(fileName, mimeType) {
            const ext = fileName ? fileName.split('.').pop().toLowerCase() : '';
            
            if (mimeType && mimeType.startsWith('image/')) {
                return '<i class="fas fa-image"></i>';
            }
            else if (ext === 'pdf' || (mimeType && mimeType.includes('pdf'))) {
                return '<i class="fas fa-file-pdf"></i>';
            }
            else if (['doc', 'docx'].includes(ext) || (mimeType && mimeType.includes('word'))) {
                return '<i class="fas fa-file-word"></i>';
            }
            else if (['xls', 'xlsx'].includes(ext) || (mimeType && mimeType.includes('excel'))) {
                return '<i class="fas fa-file-excel"></i>';
            }
            else if (['zip', 'rar', '7z'].includes(ext) || (mimeType && mimeType.includes('zip'))) {
                return '<i class="fas fa-file-archive"></i>';
            }
            else if (ext === 'txt' || (mimeType && mimeType.includes('text/'))) {
                return '<i class="fas fa-file-alt"></i>';
            }
            else {
                return '<i class="fas fa-file"></i>';
            }
        }

        function messageFormatFileSize(bytes) {
            if (!bytes) return '';
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
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
                const response = await fetch('{{ route("chat.unread-count") }}');
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
                        
                        if (data.count > 5) {
                            container.classList.add('many-unread');
                        } else {
                            container.classList.remove('many-unread');
                        }
                        
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
            } else {
                console.warn('messageChatForm not found - user may be admin');
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

        document.addEventListener('DOMContentLoaded', function() {
            @auth
                @if(Auth::user()->role === 'client')
                    @if(Auth::user()->role !== 'admin' && Auth::user()->role !== 'superadmin')
                        initMessageEventListeners();
                        messageUpdateUnreadBadge();
                        
                        setInterval(messageUpdateUnreadBadge, 30000);
                        
                        document.addEventListener('visibilitychange', function() {
                            if (!document.hidden) {
                                messageUpdateUnreadBadge();
                            }
                    });
                    @endif
                @endif
            @endauth
        });

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

        // ==================== END MESSAGE DROPDOWN FUNCTIONS ====================
    </script>
    @endif
    
    <script src="{{ asset('js/webrtc-call.js') }}"></script>
@include('partials.notification-badge-visibility')
</body>
</html>
