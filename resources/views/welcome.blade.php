<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.3/css/all.css">
    <link rel="stylesheet" href="{{ asset('css/welcome.blade.css') }}">
    <link rel="stylesheet" href="{{ asset('css/edit-account.css') }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    
    {{-- Global Error Handler --}}
    @include('partials.global-error-handler')
    
    <title>Legal Connect - Online Legal Appointments</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <meta name="description" content="Book online legal appointments with experienced attorneys at Legal Connect">
    @endif
</head>

<body>
    <!-- Header without container wrapper -->
    <header>
        <a href="{{ url('/') }}?guest=1" class="logo">
            <img class="logo-icon" src="{{ asset('logo6.png')}}" alt="">
            <div class="logo-text">Legal Connect</div>
        </a>
        <button class="burger-btn" onclick="toggleNav()">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        <nav id="main-nav">
            <a href="{{ url('/') }}?guest=1" class="admin-login">Home</a>
            <a href="{{ url('/about') }}" class="admin-login">About Us</a>
            <a href="{{ url('/testimonial') }}" class="admin-login">Testimonials</a>
            <a href="{{ url('/contact') }}" class="admin-login">Contact</a>

            <!-- Profile Icon with Dropdown - Only for Client Users -->
            @php
                $userIsClient = auth()->check() && auth()->user()->role === 'client';
                $showAuthenticatedUI = $userIsClient; // show auth UI based on Laravel session auth
            @endphp
            
            @if($showAuthenticatedUI)
                <div class="profile-dropdown">
                    <button type="button" onclick="toggleDropdown(event)">
                        Welcome, {{ auth()->user()->name }}!!
                    </button>
                    <div class="dropdown-content" id="dropdownContent">
                        <span>{{ auth()->user()->name }} &nbsp;<i class="fas fa-user"></i></span>
                        <hr>
                        <a href="#" onclick="openAccountModal()" class="link-a">Account</a>
                        <a href="#" onclick="openEditAccountModal()" class="link-a">Edit Account</a>
                        <hr>
                        <a href="#" onclick="showLogoutModal()">Logout</a>
                    </div>
                </div>
            @else
                <a href="{{ url('/login') }}" class="admin-login">Login/Register</a>
            @endif
        </nav>
        
        <!-- Notification and Message Icons Container - Only for Client Users -->
        <div class="header-icons-container">
            @if($showAuthenticatedUI)
                <!-- ========== NOTIFICATION ICON DROPDOWN ========== -->
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
                <!-- ========== END NOTIFICATION ICON DROPDOWN ========== -->

                <!-- ========== MESSAGE ICON DROPDOWN (only for non-admin users) ========== -->
                @if(auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->role !== 'superadmin')
                    <div class="message-icon-container" id="messageIconContainer">
                        <div class="message-notification-indicator" id="messageNotificationIndicator"></div>
                        <button type="button" class="message-icon-btn" onclick="messageToggleDropdown(event)">
                            <i class="fas fa-envelope"></i>
                            <span id="messageUnreadBadge" class="message-badge" style="display: none;">0</span>
                        </button>
                        <div class="message-dropdown" id="messageDropdown">
                            <div class="message-header">
                                <h3><i class="fas fa-comments me-2"></i>Message Attorney</h3>
                                <button type="button" class="message-law-office-btn" onclick="selectLawOffice()" title="Select Law Office">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button type="button" class="message-close-btn" onclick="messageCloseDropdown(event)">&times;</button>
                            </div>
                            <div id="lawOfficeSelector" class="law-office-selector" style="display: none;">
                                <div class="law-office-selector-header">
                                    <span>Select Law Office</span>
                                    <button type="button" onclick="closeLawOfficeSelector()" class="law-office-close-btn">&times;</button>
                                </div>
                                <div id="lawOfficeList" class="law-office-list">
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-spinner fa-spin"></i> Loading law offices...
                                    </div>
                                </div>
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
                @endif
                <!-- ========== END MESSAGE ICON DROPDOWN ========== -->
            @endif
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="container1">
                <div class="message">
                    <span class="subtitle select-none">Legal Connect</span>
                    <h1>Legal Expertise When You Need It</h1>
                    <p>Schedule a consultation with our experienced attorneys to discuss your legal needs and get the expert advice you deserve.</p>
                </div>

                <div class="btn-group">
                    @if($showAuthenticatedUI)
                        <a href="{{ url('/Terms') }}" class="btn btn-primary">Schedule Appointment</a>
                    @endif
                    <a href="{{ url('/about') }}" class="btn btn-outline">Learn More</a>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features">
            <div class="container">
                <h2>Why Choose Legal Connect</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">✓</div>
                        <h3>Experienced Attorneys</h3>
                        <p>Our team has decades of combined experience handling complex legal matters across various practice areas.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">⚖️</div>
                        <h3>Client-Centered Approach</h3>
                        <p>We prioritize your needs and work diligently to achieve the best possible outcomes for your case.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🕒</div>
                        <h3>Convenient Scheduling</h3>
                        <p>Easily book consultations online and connect with our attorney at times that work for you.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">👩‍⚖️</div>
                        <h3>Verified Legal Professionals</h3>
                        <p>Connect only with trusted and verified lawyer, ensuring reliable legal advice every time.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action Section -->
        <section class="cta-section">
            <div class="container2">
                <h2>Ready to Get Started?</h2>
                <p>Our attorneys are ready to help with your legal matters. Schedule a consultation today and take the first step toward resolving your legal issues.</p>
                <a href="{{ url('/contact') }}" class="btn btn-primary1">Contact Us Now</a>
            </div>
        </section>
        <footer>
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-column">
                        <h3>Legal Connect</h3>
                        <ul class="footer-links">
                            <li><a href="{{ url('/welcome') }}">Home</a></li>
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
                                <!-- Fallback in case no categories exist in database -->
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

    <!-- Success Modal -->
    @if(session('success'))
    <div id="successModal" class="modal-overlay">
        <div class="modal-box">
            <p>{{ session('success') }}</p>
            <button onclick="closeModal()">OK</button>
        </div>
    </div>
    @endif

    @auth
    @if(Auth::user()->role === 'client')
    <!-- Account Modal -->
    <div id="accountModal" class="modal">
        <div class="modal-content">
            <div id="accountInfo"></div>
        </div>
    </div>
    @endif
    @endauth

    @auth
    @if(Auth::user()->role === 'client')
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

            <!-- Account Update Form -->
            <form id="editAccountForm" class="edit-account-form">
                @csrf
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" 
                        value="{{ Auth::user()->name }}" required>
                    <div class="form-error" id="nameError"></div>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" class="form-control" 
                        value="{{ Auth::user()->address ?? '' }}" placeholder="Enter your address">
                    <div class="form-error" id="addressError"></div>
                </div>

                <div class="form-group">
                    <label for="cp_number">Phone Number</label>
                    <input type="text" id="cp_number" name="cp_number" class="form-control" 
                        value="{{ Auth::user()->cp_number }}" required>
                    <div class="form-error" id="cp_numberError"></div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                        value="{{ Auth::user()->email }}" required>
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
    @endauth

    <!-- ==================== LOGOUT CONFIRMATION MODAL ==================== -->
    <div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-labelledby="logoutModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">
                        <i class="fas fa-sign-out-alt me-2"></i>Confirm Logout
                    </h5>
                    <button type="button" class="closeBtnLogout" data-bs-dismiss="modal" aria-label="Close">&times</button>
                </div>
                <div class="modal-body">
                    <div style="font-size: 48px; color: #ffc107; margin-bottom: 15px; text-align: center;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h4 class="text-center mb-3">Confirm Logout</h4>
                    <p class="text-center">Are you sure you want to log out?</p>
                </div>
                <div class="modal-footer">
                   <center>
                     <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn-danger" onclick="performLogout()">
                        <i class="fas fa-sign-out-alt me-1"></i> Log Out
                    </button>
                   </center>
                </div>
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

    <!-- Logout form (keep the existing one) -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Per-Tab Session Management Script -->
    <script>
    /**
     * ==================== PER-TAB SESSION MANAGEMENT ====================
     * This system ensures that each browser tab maintains its own independent login session.
     * When you open a new tab, it will be in guest mode even if another tab is logged in.
     * Each tab must explicitly login to gain authenticated access.
     * This behavior persists across device restarts.
     */

    // PER-TAB SESSION MANAGER CLASS
    class TabSessionManager {
        static STORAGE_KEY = 'legal_connect_tab_session';
        static TAB_ID_KEY = 'legal_connect_tab_id';
        static TAB_EXPIRY_KEY = 'legal_connect_tab_expiry';
        static ACTIVE_TABS_KEY = 'legal_connect_active_tabs';

        /**
         * Initialize the tab session on page load
         */
        static initialize() {
            // Generate or retrieve this tab's ID
            const tabId = this.getOrCreateTabId();
            
            // Store tab ID for this session
            sessionStorage.setItem(this.TAB_ID_KEY, tabId);
            
            // Send tab ID to server with each request
            this.setupRequestInterceptor(tabId);
            
            // Initialize request headers
            this.setupFetchInterceptor(tabId);
            
            // Mark this tab as active
            this.markTabActive(tabId);
        }

        /**
         * Generate a unique tab ID or retrieve existing one from sessionStorage
         */
        static getOrCreateTabId() {
            let tabId = sessionStorage.getItem(this.TAB_ID_KEY);
            
            if (!tabId) {
                // Generate new unique tab ID: timestamp + random UUID
                tabId = 'tab_' + Date.now() + '_' + this.generateUUID();
                sessionStorage.setItem(this.TAB_ID_KEY, tabId);
            }
            
            return tabId;
        }

        /**
         * Generate a UUID v4 string
         */
        static generateUUID() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                const r = Math.random() * 16 | 0;
                const v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }

        /**
         * Setup Fetch API interceptor to add tab token to all requests
         */
        static setupFetchInterceptor(tabId) {
            const originalFetch = window.fetch;
            
            window.fetch = function(...args) {
                let config = args[1] || {};
                
                // Get current tab token
                const tabToken = sessionStorage.getItem(TabSessionManager.STORAGE_KEY);
                const tabIdHeader = sessionStorage.getItem(TabSessionManager.TAB_ID_KEY);
                
                // Add headers to all requests
                if (!config.headers) {
                    config.headers = {};
                }
                
                if (tabToken) {
                    config.headers['X-Tab-Token'] = tabToken;
                }
                
                if (tabIdHeader) {
                    config.headers['X-Tab-ID'] = tabIdHeader;
                }
                
                args[1] = config;
                
                return originalFetch.apply(this, args);
            };
        }

        /**
         * Setup XMLHttpRequest interceptor (for jQuery and other AJAX requests)
         */
        static setupRequestInterceptor(tabId) {
            const originalXhrOpen = XMLHttpRequest.prototype.open;
            
            XMLHttpRequest.prototype.open = function(...args) {
                // Get current tab token
                const tabToken = sessionStorage.getItem(TabSessionManager.STORAGE_KEY);
                const tabIdHeader = sessionStorage.getItem(TabSessionManager.TAB_ID_KEY);
                
                // Store original setRequestHeader
                const originalSetHeader = this.setRequestHeader;
                
                this.setRequestHeader = function(header, value) {
                    // Add our custom headers
                    if (header === 'X-Tab-Token' && tabToken) {
                        originalSetHeader.call(this, header, tabToken);
                    } else if (header === 'X-Tab-ID' && tabIdHeader) {
                        originalSetHeader.call(this, header, tabIdHeader);
                    } else {
                        originalSetHeader.call(this, header, value);
                    }
                };
                
                // Call original open
                originalXhrOpen.apply(this, args);
                
                // Add headers after open
                if (tabToken) {
                    this.setRequestHeader('X-Tab-Token', tabToken);
                }
                if (tabIdHeader) {
                    this.setRequestHeader('X-Tab-ID', tabIdHeader);
                }
                
                this.setRequestHeader = originalSetHeader;
            };
        }

        /**
         * Store a tab token after successful login
         * @param {string} token - The tab session token from server
         * @param {string} expiresAt - Expiry timestamp for the token, if provided
         */
        static storeTabToken(token, expiresAt = null) {
            const tabId = this.getOrCreateTabId();

            sessionStorage.setItem(this.STORAGE_KEY, token);
            sessionStorage.setItem(this.TAB_ID_KEY, tabId);

            if (expiresAt) {
                sessionStorage.setItem(this.TAB_EXPIRY_KEY, expiresAt);
            }

            // Also store in localStorage for persistence tracking
            const activeTabs = JSON.parse(localStorage.getItem(this.ACTIVE_TABS_KEY) || '{}');
            activeTabs[tabId] = {
                token: token,
                expiresAt: expiresAt || '',
                loginTime: new Date().toISOString(),
            };
            localStorage.setItem(this.ACTIVE_TABS_KEY, JSON.stringify(activeTabs));
        }

        // Alias for compatibility with existing code that calls setTabToken
        static setTabToken(token, expiresAt) {
            return this.storeTabToken(token, expiresAt);
        }

        /**
         * Get the current tab's token
         */
        static getTabToken() {
            return sessionStorage.getItem(this.STORAGE_KEY);
        }

        /**
         * Get the current tab's expiry
         */
        static getTabExpiry() {
            return sessionStorage.getItem(this.TAB_EXPIRY_KEY);
        }

        /**
         * Check if stored tab token is expired
         */
        static isTokenExpired() {
            const expiry = this.getTabExpiry();
            if (!expiry) return true;
            return new Date() > new Date(expiry);
        }

        /**
         * Clear the tab session (on logout)
         */
        static clearTabSession() {
            const tabId = sessionStorage.getItem(this.TAB_ID_KEY);
            
            sessionStorage.removeItem(this.STORAGE_KEY);
            sessionStorage.removeItem(this.TAB_ID_KEY);
            sessionStorage.removeItem(this.TAB_EXPIRY_KEY);
            
            // Remove from active tabs in localStorage
            if (tabId) {
                const activeTabs = JSON.parse(localStorage.getItem(this.ACTIVE_TABS_KEY) || '{}');
                delete activeTabs[tabId];
                localStorage.setItem(this.ACTIVE_TABS_KEY, JSON.stringify(activeTabs));
            }
        }

        /**
         * Mark this tab as active in localStorage
         */
        static markTabActive(tabId) {
            try {
                const activeTabs = JSON.parse(localStorage.getItem(this.ACTIVE_TABS_KEY) || '{}');
                activeTabs[tabId] = activeTabs[tabId] || { loginTime: new Date().toISOString() };
                localStorage.setItem(this.ACTIVE_TABS_KEY, JSON.stringify(activeTabs));
            } catch (e) {
                // Silently fail if localStorage is not available
            }
        }

        /**
         * Check if this tab should be showing guest view
         * Returns true if no valid tab token exists
         */
        static isGuestTab() {
            return !this.getTabToken();
        }
    }

    // Initialize tab session management on page load
    document.addEventListener('DOMContentLoaded', function() {
        TabSessionManager.initialize();
    });

    // Also initialize immediately in case DOM is already loaded
    if (document.readyState === 'loading') {
        // DOM is still loading, wait for DOMContentLoaded
    } else {
        // DOM is already ready
        TabSessionManager.initialize();
    }
    </script>

    <script>
    // ==================== BASIC FUNCTIONS ====================
    function toggleDropdown(event) {
        event.stopPropagation();
        var dropdown = document.getElementById("dropdownContent");
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }

    function toggleNav() {
        const nav = document.getElementById('main-nav');
        nav.classList.toggle('active');
    }

    // ==================== LOGOUT MODAL FUNCTIONS ====================
    function showLogoutModal() {
        // Create modal instance using Bootstrap
        const modalElement = document.getElementById('logoutConfirmationModal');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: true
        });
        modal.show();
    }

    function performLogout() {
        // Submit the logout form
        document.getElementById('logout-form').submit();
    }

    async function performLogoutAndRedirect() {
        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch('{{ route("logout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({})
            });
            if (response.ok) {
                window.location.href = '{{ url("/login") }}';
            } else {
                // fallback to form submit if fetch failed
                document.getElementById('logout-form').submit();
            }
        } catch (e) {
            // fallback to form submit on error
            document.getElementById('logout-form').submit();
        }
    }

    // Close dropdown when logout modal opens
    document.addEventListener('DOMContentLoaded', function() {
        const logoutModal = document.getElementById('logoutConfirmationModal');
        if (logoutModal) {
            logoutModal.addEventListener('show.bs.modal', function () {
                // Close any open dropdowns
                const dropdown = document.getElementById('dropdownContent');
                if (dropdown) {
                    dropdown.style.display = 'none';
                }
            });
        }
    });

    // Rest of your existing JavaScript code remains the same...
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
        document.getElementById('dropdownContent').style.display = 'none';
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
        // DON'T add email to form data - the server will use authenticated user's email
        
        try {
            const response = await fetch('{{ route("account.request.password.change") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
                    
                    // Don't add email input to OTP form - server uses authenticated user
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

        // Get the email from the email input field
        const email = document.getElementById('email').value;
        
        const formData = new FormData(this);
        formData.append('email', email); // Add email to form data
        
        try {
            const response = await fetch('{{ route("account.verify.otp.password") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
        const response = await fetch('{{ route("account.resend.otp") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({}) // Send empty object since we don't need email
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

    // ==================== END: EDIT ACCOUNT MODAL FUNCTIONS ====================

    // ==================== START: NOTIFICATION DROPDOWN FUNCTIONS ====================
    let notificationDropdownOpen = false;
    let notificationInterval = null;
    let notificationsLoaded = false;
    let badgeHidden = false;

    function notificationToggleDropdown(event) {
        event.stopPropagation();
        const dropdown = document.getElementById('notificationDropdown');
        dropdown.classList.toggle('active');
        
        if (dropdown.classList.contains('active')) {
            notificationDropdownOpen = true;
            // Hide badge when opening dropdown and mark as read
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
                    <div class="notification-empty-icon">📝</div>
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

    // ==================== ACCOUNT MODAL FUNCTIONS ====================
    function openAccountModal() {
        console.log('Opening account modal');
        
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

    function closeAccountModal() {
        console.log('Closing account modal');
        document.getElementById('accountModal').style.display = 'none';
    }

    function closeModal() {
        document.getElementById('successModal').style.display = 'none';
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        var accountModal = document.getElementById('accountModal');
        var editAccountModal = document.getElementById('editAccountModal');
        var logoutModal = document.getElementById('logoutConfirmationModal');
        
        if (event.target == accountModal) {
            closeAccountModal();
        }
        if (event.target == editAccountModal) {
            closeEditAccountModal();
        }
        if (event.target == logoutModal) {
            // Bootstrap will handle closing via backdrop click
        }
    };

    // Escape key to close modals
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeAccountModal();
            closeEditAccountModal();
            // Bootstrap modal will handle Escape key for logout modal
        }
    });
    // ==================== END ACCOUNT MODAL FUNCTIONS ====================
    </script>

    <!-- ==================== MESSAGE DROPDOWN JAVASCRIPT ==================== -->
    <script>
// ==================== MESSAGE DROPDOWN FUNCTIONS ====================
let messagePusher = null;
let messageChannel = null;
let currentMessageConversationId = null;

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
    
    // Close law office selector
    closeLawOfficeSelector();
    
    document.getElementById('messageConversationId').value = '';
    document.getElementById('messageAdminId').value = '';
}

async function messageLoadAdmins(lawOfficeId = null) {
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
        let url = '/api/admins';
        if (lawOfficeId) {
            url += `?law_office_id=${lawOfficeId}`;
        }
        
        const response = await fetch(url, {
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
    
    // Close law office selector if open
    closeLawOfficeSelector();
    
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
        const roleLabel = admin.role === 'secretary' ? 'Secretary' : 'Attorney';
        html += `
            <div class="message-admin-item" onclick="messageOpenChat(${admin.id}, '${admin.name.replace(/'/g, "\\'")}', ${admin.is_online}, '${admin.role}')">
                <div style="position: relative; display: inline-block;">
                    <img src="${imageUrl}" alt="${admin.name}" onerror="this.src='{{ asset('default-user.png') }}'">
                    <div class="admin-status-indicator ${statusClass}" title="${statusText}"></div>
                </div>
                <div class="message-admin-info">
                    <div class="message-admin-name">${admin.name}</div>
                    <div class="message-admin-email">${admin.email}</div>
                    <div class="message-admin-role" style="font-size: 0.85rem; color: #6c757d; margin-top: 2px;">${roleLabel}</div>
                </div>
            </div>
        `;
    });
    
    adminsList.innerHTML = html;
}

function messageOpenChat(adminId, adminName, isOnline = false, adminRole = 'admin') {
    if (!messageDropdownExists()) return;
    
    document.getElementById('messageAdminId').value = adminId;
    // Store admin online status and role for later use
    document.getElementById('messageAdminId').setAttribute('data-is-online', isOnline ? 'true' : 'false');
    document.getElementById('messageAdminId').setAttribute('data-role', adminRole);
    
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
        const statusBadge = isOnline ? '<span style="color: #28a745; font-size: 0.9rem; font-weight: normal;">● Online</span>' : '<span style="color: #dc3545; font-size: 0.9rem; font-weight: normal;">● Offline</span>';
        const roleLabel = adminRole === 'secretary' ? '<span style="color: #0066cc; font-weight: 500; margin-left: 8px; padding: 2px 6px; background: #e7f3ff; border-radius: 3px; font-size: 0.85rem;">Secretary</span>' : '';
        header.innerHTML = `<i class="fas fa-comments me-2"></i>Chat with ${adminName} ${roleLabel} ${statusBadge}`;
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
            const role = adminRole === 'secretary' ? 'Secretary' : 'Attorney';
            videoCallBtn.disabled = true;
            videoCallBtn.title = `${role} is offline - Video call unavailable`;
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
        
        .law-office-selector {
            position: relative;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 10px;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .law-office-selector-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
            border-radius: 8px 8px 0 0;
            font-weight: 500;
        }
        
        .law-office-close-btn {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .law-office-close-btn:hover {
            color: #333;
        }
        
        .law-office-list {
            padding: 10px;
        }
        
        .law-office-item {
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            margin-bottom: 5px;
        }
        
        .law-office-item:hover {
            background-color: #f8f9fa;
        }
        
        .law-office-item:last-child {
            margin-bottom: 0;
        }
        
        .message-law-office-btn {
            background: #007bff;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            transition: background-color 0.2s ease;
        }
        
        .message-law-office-btn:hover {
            background: #0056b3;
        }
        .message-admin-role {
            margin-top: 4px;
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
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

// ==================== LAW OFFICE SELECTOR FUNCTIONS ====================

function selectLawOffice() {
    const selector = document.getElementById('lawOfficeSelector');
    if (selector) {
        if (selector.style.display === 'none' || selector.style.display === '') {
            selector.style.display = 'block';
            loadLawOffices();
        } else {
            selector.style.display = 'none';
        }
    }
}

function closeLawOfficeSelector() {
    const selector = document.getElementById('lawOfficeSelector');
    if (selector) {
        selector.style.display = 'none';
    }
}

async function loadLawOffices() {
    const lawOfficeList = document.getElementById('lawOfficeList');
    if (!lawOfficeList) return;
    
    lawOfficeList.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Loading law offices...</div>';
    
    try {
        const response = await fetch('/api/law-offices', {
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
        if (data.status === 'success') {
            displayLawOffices(data.data);
        } else {
            lawOfficeList.innerHTML = '<div class="text-center text-muted py-3">No law offices available</div>';
        }
    } catch (error) {
        console.error('Error loading law offices:', error);
        lawOfficeList.innerHTML = '<div class="text-center text-muted py-3">Failed to load law offices</div>';
    }
}

function displayLawOffices(offices) {
    const lawOfficeList = document.getElementById('lawOfficeList');
    if (!lawOfficeList) return;
    
    if (!offices || offices.length === 0) {
        lawOfficeList.innerHTML = '<div class="text-center text-muted py-3">No law offices available</div>';
        return;
    }
    
    let html = '';
    offices.forEach(office => {
        html += `
            <div class="law-office-item" onclick="selectLawOfficeItem(${office.id}, '${office.law_office.replace(/'/g, "\\'")}')">
                <i class="fas fa-building me-2"></i>
                <span>${office.law_office}</span>
            </div>
        `;
    });
    
    lawOfficeList.innerHTML = html;
}

function selectLawOfficeItem(officeId, officeName) {
    // Store selected law office
    window.selectedLawOfficeId = officeId;
    window.selectedLawOfficeName = officeName;
    
    // Reload admins filtered by law office
    messageLoadAdmins(officeId);
    
    // Update header to show selected office
    const header = document.querySelector('.message-header h3');
    if (header) {
        header.innerHTML = `<i class="fas fa-comments me-2"></i>Message Office Staff - ${officeName}`;
    }
    
    // Show back button to return to all offices
    showLawOfficeBackButton();
    
    closeLawOfficeSelector();
}

function showLawOfficeBackButton() {
    const backButtonContainer = document.getElementById('messageBackButtonContainer');
    if (backButtonContainer) {
        const backButton = backButtonContainer.querySelector('.message-back-btn');
        if (backButton) {
            backButton.innerHTML = `
                <i class="fas fa-arrow-left"></i>
                <span>Back to All Offices</span>
            `;
            backButton.onclick = messageBackToAllOffices;
        }
        backButtonContainer.style.display = 'block';
    }
}

function messageBackToAllOffices() {
    // Clear selected law office
    window.selectedLawOfficeId = null;
    window.selectedLawOfficeName = null;
    
    // Reload all admins
    messageLoadAdmins();
    
    // Reset header
    const header = document.querySelector('.message-header h3');
    if (header) {
        header.innerHTML = `<i class="fas fa-comments me-2"></i>Message Attorney`;
    }
    
    // Hide back button
    const backButtonContainer = document.getElementById('messageBackButtonContainer');
    if (backButtonContainer) {
        backButtonContainer.style.display = 'none';
    }
}

    </script>

    <!-- Tab Session Manager is already defined above, no need to duplicate -->

    <!-- ==================== USER DATA SCRIPT ==================== -->
    @auth
    @if(Auth::user()->role === 'client')
    <script>
        window.currentUser = {
            id: {{ Auth::id() }},
            name: "{{ Auth::user()->name }}",
            email: "{{ Auth::user()->email }}",
            role: "{{ Auth::user()->role }}"
        };
        
        // If this is an authenticated session, store the tab token if provided in session
        @if(session()->has('tab_session'))
        const tabSession = @json(session('tab_session'));
        TabSessionManager.setTabToken(tabSession.tab_token, tabSession.expires_at);
        console.log('Loaded per-tab token from server session');
        @endif
    </script>
    @endif
    @endauth

    <!-- WebRTC Call Manager -->
    <script src="{{ asset('js/webrtc-call.js') }}"></script>
</body>
</html>
