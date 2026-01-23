<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.3/css/all.css">
    <link rel="stylesheet" href="{{ asset('css/welcome.blade.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
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
        <a href="#" class="logo">
            <img class="logo-icon" src="{{ asset('logo6.png')}}" alt="">
            <div class="logo-text">Legal Connect</div>
        </a>
        <button class="burger-btn" onclick="toggleNav()">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        <nav id="main-nav">
            <a href="{{ url('/welcome') }}" class="admin-login">Home</a>
            <a href="{{ url('/about') }}" class="admin-login">About Us</a>
            <a href="{{ url('/testimonial') }}" class="admin-login">Testimonials</a>
            <a href="{{ url('/contact') }}" class="admin-login">Contact</a>

            <!-- Profile Icon with Dropdown -->
            @auth
                <div class="profile-dropdown">
                    <button type="button" onclick="toggleDropdown(event)">
                        Welcome, {{ Auth::user()->name }}!!
                    </button>
                    <div class="dropdown-content" id="dropdownContent">
                        <span>{{ Auth::user()->name }} &nbsp;<i class="fas fa-user"></i></span>
                        <hr>
                        <a href="#" onclick="openNotificationModal()" class="link-a">Notification</a>
                        <a href="#" onclick="openAccountModal()" class="link-a">Account</a>
                        <hr>
                        <a href="{{ route('logout') }}"
                          onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                          Logout
                        </a>
                        @auth
                        <!-- Add this inside the profile-dropdown div 
                        <div id="chat-icon" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
                            <button class="btn btn-primary rounded-circle" style="width: 60px; height: 60px; box-shadow: 0 4px 12px rgba(0,0,0,0.2);" onclick="openChatModal()">
                                <i class="fas fa-comments fa-lg"></i>
                                <span id="unread-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">0</span>
                            </button>
                        </div>-->
                    @endauth
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                    
                </div>
            @else
                <a href="{{ url('/login') }}" class="admin-login">Login/Register</a>
            @endauth
    
        </nav>
       @auth
         @if(Auth::user()->role !== 'admin' && Auth::user()->role !== 'superadmin')
        <!-- Message Icon Dropdown -->
        <div class="message-icon-container" id="messageIconContainer">
            <!-- Notification indicator (red dot) -->
            <div class="message-notification-indicator" id="messageNotificationIndicator"></div>
            
            <button type="button" class="message-icon-btn" onclick="messageToggleDropdown(event)">
                <i class="fas fa-envelope"></i>
                <span id="messageUnreadBadge" class="message-badge" style="display: none;">0</span>
            </button>
             <div class="message-dropdown" id="messageDropdown">
                        <div class="message-header">
                            <h3><i class="fas fa-comments me-2"></i>Message Admins</h3>
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
                                <i class="fas fa-spinner fa-spin"></i> Loading admins...
                            </div>
                        </div>
                        <div class="message-chat-area" id="messageChatArea" style="display: none;">
                            <div class="message-chat-messages" id="messageChatMessages">
                                <!-- Messages will appear here -->
                            </div>
                            <form id="messageChatForm">
                                @csrf
                                <input type="hidden" id="messageConversationId" value="">
                                <input type="hidden" id="messageAdminId" value="">
                                <div class="message-input-group">
                                    <input type="text" id="messageChatInput" class="message-chat-input" placeholder="Type your message..." autocomplete="off">
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
        @endauth
          <!-- Chat Icon for Admin 
            @auth
                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'superadmin')
                    <div class="chat-icon-container">
                        <button type="button" class="chat-icon-btn" onclick="toggleChatDropdown(event)">
                            <i class="fas fa-comments"></i>
                            <span id="chatUnreadBadge" class="chat-badge" style="display: none;">0</span>
                        </button>
                        <div class="chat-dropdown" id="chatDropdown">
                            <div class="chat-header">
                                <h3><i class="fas fa-comments me-2"></i>Chat Messages</h3>
                                <button type="button" class="chat-close-btn" onclick="closeChatDropdown()">&times;</button>
                            </div>
                            <div class="chat-body" id="chatConversations">
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-comments fa-2x mb-2"></i>
                                    <p>Loading conversations...</p>
                                </div>
                            </div>
                            <div class="chat-input-area" id="chatInputArea" style="display: none;">
                                <div class="chat-messages" id="chatMessages">
                                     Messages will appear here 
                                </div>
                                <form id="chatSendForm">
                                    @csrf
                                    <input type="hidden" id="chatConversationId" value="">
                                    <div class="chat-input-group">
                                        <input type="text" id="chatMessageInput" class="chat-message-input" placeholder="Type your message..." autocomplete="off">
                                        <button type="button" class="chat-file-btn" onclick="document.getElementById('chatFileInput').click()">
                                            <i class="fas fa-paperclip"></i>
                                        </button>
                                        <input type="file" id="chatFileInput" style="display: none;" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt">
                                        <button type="submit" class="chat-send-btn" id="chatSendBtn">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                    <div id="chatFilePreview" class="chat-file-preview" style="display: none;"></div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endauth-->
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
                    @auth
                        <a href="{{ url('/Terms') }}" class="btn btn-primary">Schedule Appointment</a>
                    @endauth
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
                        <p>Easily book consultations online and connect with our attorneys at times that work for you.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">👩‍⚖️</div>
                        <h3>Verified Legal Professionals</h3>
                        <p>Connect only with trusted and verified lawyers, ensuring reliable legal advice every time.</p>
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
    </main>
        <!-- Chat Modal -->
        <div id="chatModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-comments me-2"></i>Chat Support
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="chat-container" style="height: 500px;">
                            <div class="messages-container" id="clientMessagesContainer" style="height: 400px; overflow-y: auto; padding: 20px; background: #f8f9fa;">
                                <!-- Messages will be loaded here -->
                                <div class="text-center text-muted mt-5">
                                    <i class="fas fa-comments fa-3x mb-3"></i>
                                    <p>Start a conversation with our support team</p>
                                </div>
                            </div>
                            <div class="message-input p-3 border-top">
                                <form id="clientChatForm">
                                    @csrf
                                    <div class="input-group">
                                        <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('clientFileInput').click()">
                                            <i class="fas fa-paperclip"></i>
                                        </button>
                                        <input type="file" id="clientFileInput" style="display: none;" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt,.zip,.rar">
                                        
                                        <input type="text" id="clientMessageInput" class="form-control" placeholder="Type your message..." autocomplete="off">
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                    <div id="clientFilePreview" class="mt-2" style="display: none;"></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
</div>
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
                        <li><a href="#">Family Law</a></li>
                        <li><a href="#">Personal Injury</a></li>
                        <li><a href="#">Real Estate</a></li>
                        <li><a href="#">Business Law</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Legal Connect All rights reserved.</p>
            </div>
        </div>
    </footer>


@auth
    @if(Auth::user()->role !== 'admin' && Auth::user()->role !== 'superadmin')
        <script>
            // ==================== CLIENT CHAT FUNCTIONALITY ====================
            // This block only loads for authenticated non-admin users
        // Chat form submission handler
        const clientChatForm = document.getElementById('clientChatForm');
        if (clientChatForm) {
            clientChatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const messageInput = document.getElementById('clientMessageInput');
                const message = messageInput.value.trim();
                const fileInput = document.getElementById('clientFileInput');
                const formData = new FormData();
                
                formData.append('message', message);
                
                if (fileInput && fileInput.files.length > 0) {
                    formData.append('file', fileInput.files[0]);
                    const preview = document.getElementById('clientFilePreview');
                    if (preview) {
                        preview.style.display = 'none';
                    }
                    fileInput.value = '';
                }
                
                if (!message && (!fileInput || fileInput.files.length === 0)) {
                    return;
                }
                
                fetch('{{ route("client.chat.send") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (messageInput) {
                            messageInput.value = '';
                            messageInput.focus();
                        }
                        
                        // Append the sent message
                        if (typeof appendClientMessage === 'function') {
                            appendClientMessage(data.message);
                        }
                        if (typeof scrollClientToBottom === 'function') {
                            scrollClientToBottom();
                        }
                    }
                })
                .catch(error => console.error('Error sending message:', error));
            });
        }
        
        // File input change handler
        const clientFileInput = document.getElementById('clientFileInput');
        if (clientFileInput) {
            clientFileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const preview = document.getElementById('clientFilePreview');
                    if (preview) {
                        preview.innerHTML = `
                            <div class="d-flex align-items-center bg-light rounded p-2">
                                <i class="fas fa-file text-primary me-2"></i>
                                <div class="flex-grow-1">
                                    <div class="small fw-bold">${file.name}</div>
                                    <div class="small text-muted">${formatFileSize(file.size)}</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearClientFile()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                        preview.style.display = 'block';
                    }
                }
            });
        }
        
        // Clear file function (only for chat users)
        function clearClientFile() {
            const clientFileInput = document.getElementById('clientFileInput');
            const clientFilePreview = document.getElementById('clientFilePreview');
            
            if (clientFileInput) {
                clientFileInput.value = '';
            }
            
            if (clientFilePreview) {
                clientFilePreview.style.display = 'none';
                clientFilePreview.innerHTML = '';
            }
        }
        </script>
    @endif
@endauth
    <!-- Success Modal -->
    @if(session('success'))
    <div id="successModal" class="modal-overlay">
        <div class="modal-box">
            <p>{{ session('success') }}</p>
            <button onclick="closeModal()">OK</button>
        </div>
    </div>
    @endif

    <!-- Notification Modal -->
   <div id="notificationModal" class="modal">
    <div class="modal-content">
       
        <h2>Notifications</h2>
        <hr>
        <ul id="notificationList">
            <!-- Notifications will be loaded here -->
        </ul>
    </div>
</div>

    <!-- Account Modal -->
    <div id="accountModal" class="modal">
        <div class="modal-content">
            <div id="accountInfo">
                <!-- Account information will be loaded here -->
            </div>
        </div>
    </div>

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

    // Function to get status message
    function getStatusMessage(notification) {
        const status = notification.approval_appointment?.toLowerCase();
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
                return `📅 Appointment status updated for ${fullname}: ${notification.approval_appointment}${datetime}`;
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

    // Auto-refresh interval variable
    let notificationInterval = null;

    function openNotificationModal() {
        console.log('Opening notification modal');
        const modal = document.getElementById('notificationModal');
        const notificationList = document.getElementById('notificationList');
        
        // Show loading state
        notificationList.innerHTML = '<li style="padding: 20px; text-align: center;">Loading approval history...</li>';

        // Fetch and display notifications
        fetchApprovalHistory()
        .then(data => {
            console.log('Approval history data:', data);
            renderApprovalHistory(data.notifications);
        })
        .catch(error => {
            console.error('Error fetching approval history:', error);
            notificationList.innerHTML = '<li style="padding: 20px; text-align: center; color: red;">Error loading approval history. Please try again.</li>';
        });

        modal.style.display = 'block';

        // Start auto-refresh every 5 seconds when modal is open
        notificationInterval = setInterval(() => {
            console.log('Auto-refreshing approval history...');
            fetchApprovalHistory()
            .then(data => {
                renderApprovalHistory(data.notifications);
                showUpdateIndicator();
            })
            .catch(error => {
                console.error('Error auto-refreshing approval history:', error);
            });
        }, 5000); // Refresh every 5 seconds
    }

    // Function to fetch approval history
    function fetchApprovalHistory() {
        return fetch('{{ route("notifications.approval-history") }}', {
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

    // Function to render approval history
    function renderApprovalHistory(notifications) {
        const notificationList = document.getElementById('notificationList');
        notificationList.innerHTML = '';

        if (notifications && notifications.length > 0) {
            notifications.forEach(notification => {
                const li = document.createElement('li');
                const status = notification.approval_appointment?.toLowerCase();
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

                li.innerHTML = `
                    <div class="notification-item" style="padding: 15px; border-bottom: 1px solid #eee; background: ${status === 'approved' ? '#f0fff0' : status === 'denied' ? '#fff0f0' : '#fff9e6'};">
                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                            <div style="font-size: 18px;">${statusIcon}</div>
                            <div style="flex: 1;">
                                <p style="margin: 0 0 8px 0; font-weight: 500;">${message}</p>
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #666;">
                                    <small>${formattedDate}</small>
                                    <span style="color: ${statusColor}; font-weight: bold; text-transform: uppercase; padding: 2px 8px; border: 1px solid ${statusColor}; border-radius: 12px;">
                                        ${notification.approval_appointment || 'Unknown'}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                notificationList.appendChild(li);
            });
        } else {
            notificationList.innerHTML = `
                <li style="padding: 40px 20px; text-align: center; color: #666;">
                    <div style="font-size: 48px; margin-bottom: 10px;">📝</div>
                    <p style="margin: 0;">No approval history found.</p>
                    <small>Your appointment approval history will appear here.</small>
                </li>
            `;
        }
    }

    function closeNotificationModal(event) {
        if (event) event.stopPropagation();
        console.log('Closing notification modal');
        document.getElementById('notificationModal').style.display = 'none';
        
        // Stop auto-refresh when modal is closed
        if (notificationInterval) {
            clearInterval(notificationInterval);
            notificationInterval = null;
        }
    }

    function openAccountModal() {
        console.log('Opening account modal');
        
        const userData = {
            name: "{{ Auth::user()->name ?? 'User' }}",
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

    // Visual indicator for updates
    function showUpdateIndicator() {
        const modalContent = document.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.boxShadow = '0 0 15px rgba(0,150,255,0.5)';
            setTimeout(() => {
                modalContent.style.boxShadow = '';
            }, 1000);
        }
    }

    // Close dropdown and modal when clicking outside
    window.onclick = function(event) {
        var notificationModal = document.getElementById('notificationModal');
        var accountModal = document.getElementById('accountModal');

        // Close modal when clicking outside
        if (event.target == notificationModal) {
            closeNotificationModal(event);
        }
        if (event.target == accountModal) {
            closeAccountModal();
        }
    };

    // Add event listener for Escape key to close modals
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeNotificationModal();
            closeAccountModal();
        }
    });

    let clientPusher = null;
let clientChannel = null;
let clientConversationId = null;
let clientLastMessageId = 0;

// Show chat icon when logged in
document.addEventListener('DOMContentLoaded', function() {
    @auth
        const chatIcon = document.getElementById('chat-icon');
        if (chatIcon) {
            chatIcon.style.display = 'block';
            checkUnreadMessages();
            
            // Check for new messages every 30 seconds
            setInterval(checkUnreadMessages, 30000);
        }
    @endauth
});

function openChatModal() {
    const modal = new bootstrap.Modal(document.getElementById('chatModal'));
    modal.show();
    loadClientMessages();
    initializeClientPusher();
}

function initializeClientPusher() {
    if (!clientPusher) {
        clientPusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
            cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
            forceTLS: true
        });
    }
    
    // Load conversation first to get conversation ID
    fetch('{{ route("client.chat.conversation") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.conversation) {
                clientConversationId = data.conversation.id;
                joinClientConversationChannel();
            }
        });
}

function joinClientConversationChannel() {
    if (clientChannel) {
        clientPusher.unsubscribe('private-chat.' + clientConversationId);
    }
    
    clientChannel = clientPusher.subscribe('private-chat.' + clientConversationId);
    clientChannel.bind('App\\Events\\ChatMessageSent', function(data) {
        handleClientNewMessage(data.message);
    });
}

function handleClientNewMessage(message) {
    if (message.conversation_id == clientConversationId) {
        appendClientMessage(message);
        scrollClientToBottom();
        markClientMessageAsRead(message.id);
    }
    
    // Update unread badge
    checkUnreadMessages();
}

function loadClientMessages() {
    fetch('{{ route("client.chat.conversation") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayClientMessages(data.messages || []);
                scrollClientToBottom();
            }
        })
        .catch(error => console.error('Error loading messages:', error));
}

function displayClientMessages(messages) {
    const container = document.getElementById('clientMessagesContainer');
    container.innerHTML = '';
    
    if (messages.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted mt-5">
                <i class="fas fa-comments fa-3x mb-3"></i>
                <p>Start a conversation with our support team</p>
            </div>
        `;
        return;
    }
    
    messages.forEach(message => {
        appendClientMessage(message);
    });
    
    if (messages.length > 0) {
        clientLastMessageId = messages[messages.length - 1].id;
    }
}

function appendClientMessage(message) {
    const container = document.getElementById('clientMessagesContainer');
    const isSent = message.sender_id === {{ Auth::id() }};
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `d-flex mb-3 ${isSent ? 'justify-content-end' : 'justify-content-start'}`;
    
    if (message.message_type === 'file') {
        // CORRECTED: Use chat.messages.download instead of chat.download
        const downloadUrl = `{{ route('chat.messages.download', '') }}/${message.id}`;
        messageDiv.innerHTML = `
            <div class="${isSent ? 'bg-primary text-white' : 'bg-white'} rounded p-3" style="max-width: 70%;">
                ${isSent ? '' : `<div class="small text-muted mb-1">${message.sender?.name || 'Admin'}</div>`}
                <div class="mb-2">${message.message}</div>
                <div class="d-flex align-items-center bg-${isSent ? 'light' : 'light'} rounded p-2">
                    <i class="fas fa-file ${isSent ? 'text-primary' : 'text-secondary'} me-2"></i>
                    <div class="flex-grow-1">
                        <div class="small fw-bold">${message.file_name}</div>
                        <div class="small text-muted">${formatFileSize(message.file_size)}</div>
                    </div>
                    <a href="${downloadUrl}" class="text-decoration-none">
                        <i class="fas fa-download ${isSent ? 'text-primary' : 'text-secondary'}"></i>
                    </a>
                </div>
                <div class="small text-${isSent ? 'white-50' : 'muted'} mt-2">
                    ${new Date(message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                </div>
            </div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div class="${isSent ? 'bg-primary text-white' : 'bg-white'} rounded p-3" style="max-width: 70%;">
                ${isSent ? '' : `<div class="small text-muted mb-1">${message.sender?.name || 'Admin'}</div>`}
                <div class="mb-1">${message.message}</div>
                <div class="small text-${isSent ? 'white-50' : 'muted'}">
                    ${new Date(message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                </div>
            </div>
        `;
    }
    
    container.appendChild(messageDiv);
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function scrollClientToBottom() {
    const container = document.getElementById('clientMessagesContainer');
    container.scrollTop = container.scrollHeight;
}


function checkUnreadMessages() {
    fetch('{{ route("chat.unread-count") }}')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('unread-badge');
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'block';
                
                // Flash notification for new messages
                if (data.count > parseInt(badge.textContent || 0)) {
                    flashChatIcon();
                }
            } else {
                badge.style.display = 'none';
            }
        });
}

function flashChatIcon() {
    const icon = document.getElementById('chat-icon').querySelector('button');
    icon.classList.add('animate__animated', 'animate__pulse', 'animate__infinite');
    setTimeout(() => {
        icon.classList.remove('animate__animated', 'animate__pulse', 'animate__infinite');
    }, 3000);
}

function markClientMessageAsRead(messageId) {
    fetch(`/chat/messages/${messageId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    });
}

// Load messages when modal is shown
document.getElementById('chatModal').addEventListener('shown.bs.modal', function() {
    loadClientMessages();
});

// Clear unread badge when opening chat
document.getElementById('chatModal').addEventListener('show.bs.modal', function() {
    document.getElementById('unread-badge').style.display = 'none';
});
    </script>
    <script>
        // Chat Dropdown Functions
let chatPusher = null;
let chatChannel = null;
let currentChatConversationId = null;

// Toggle chat dropdown
function toggleChatDropdown(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('chatDropdown');
    dropdown.classList.toggle('active');
    
    if (dropdown.classList.contains('active')) {
        loadConversations();
    }
}

// Close chat dropdown
function closeChatDropdown() {
    const dropdown = document.getElementById('chatDropdown');
    dropdown.classList.remove('active');
    resetChatView();
}

// Reset chat view to conversations list
function resetChatView() {
    document.getElementById('chatConversations').style.display = 'block';
    document.getElementById('chatInputArea').style.display = 'none';
    document.getElementById('chatConversationId').value = '';
    currentChatConversationId = null;
}

// Load conversations for dropdown

function loadConversations() {
    const conversationsDiv = document.getElementById('chatConversations');
    
    // Show loading state
    conversationsDiv.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Loading conversations...</div>';
    
    fetch('{{ route("chat.recent-conversations") }}', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            displayConversations(data.conversations);
        } else {
            conversationsDiv.innerHTML = '<div class="text-center text-muted py-3">' + (data.message || 'No conversations found') + '</div>';
        }
    })
    .catch(error => {
        console.error('Error loading conversations:', error);
        conversationsDiv.innerHTML = '<div class="text-center text-muted py-3">Error loading conversations</div>';
    });
}

// Display conversations list
function displayConversations(conversations) {
    const conversationsDiv = document.getElementById('chatConversations');
    
    if (conversations.length === 0) {
        conversationsDiv.innerHTML = '<div class="text-center text-muted py-3">No active conversations</div>';
        return;
    }
    
    let html = '';
    conversations.forEach(conversation => {
        const unreadClass = conversation.unread_count > 0 ? 'unread' : '';
        const unreadBadge = conversation.unread_count > 0 ? 
            `<span class="chat-unread-count">${conversation.unread_count}</span>` : '';
        
        html += `
            <div class="chat-conversation-item ${unreadClass}" onclick="openConversation(${conversation.id}, '${conversation.client.name}')">
                <div class="chat-conversation-info">
                    <div>
                        <span class="chat-client-name">${conversation.client.name}</span>
                        ${unreadBadge}
                    </div>
                    <span class="chat-time">${formatTime(conversation.last_message_time)}</span>
                </div>
                <div class="chat-preview">${conversation.last_message || 'No messages yet'}</div>
            </div>
        `;
    });
    
    conversationsDiv.innerHTML = html;
}

// Open conversation
function openConversation(conversationId, clientName) {
    currentChatConversationId = conversationId;
    document.getElementById('chatConversationId').value = conversationId;
    document.getElementById('chatConversations').style.display = 'none';
    document.getElementById('chatInputArea').style.display = 'block';
    
    // Update header with client name
    const header = document.querySelector('.chat-header h3');
    header.innerHTML = `<i class="fas fa-comments me-2"></i>Chat with ${clientName}`;
    
    // Load messages
    loadChatMessages(conversationId);
    initializeChatPusher(conversationId);
}

// Load chat messages
function loadChatMessages(conversationId) {
    const messagesDiv = document.getElementById('chatMessages');
    messagesDiv.innerHTML = '<div class="text-center text-muted py-3">Loading messages...</div>';
    
    fetch(`/chat/admin/messages/${conversationId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayChatMessages(data.messages);
            scrollChatToBottom();
        }
    });
}

// Display chat messages
function displayChatMessages(messages) {
    const messagesDiv = document.getElementById('chatMessages');
    messagesDiv.innerHTML = '';
    
    if (messages.length === 0) {
        messagesDiv.innerHTML = '<div class="text-center text-muted py-3">No messages yet</div>';
        return;
    }
    
    messages.forEach(message => {
        const isSent = message.sender_id === {{ Auth::id() }};
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${isSent ? 'sent' : 'received'}`;
        
        if (message.message_type === 'file') {
            const downloadUrl = `{{ route('chat.download', '') }}/${message.id}`;
            messageDiv.innerHTML = `
                <div>${message.message}</div>
                <div class="chat-file-preview">
                    <i class="fas fa-file"></i>
                    <span class="chat-file-name">${message.file_name}</span>
                    <a href="${downloadUrl}" target="_blank"><i class="fas fa-download"></i></a>
                </div>
                <div class="chat-message-time">${formatTime(message.created_at)}</div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div>${message.message}</div>
                <div class="chat-message-time">${formatTime(message.created_at)}</div>
            `;
        }
        
        messagesDiv.appendChild(messageDiv);
    });
}

// Initialize Pusher for chat
function initializeChatPusher(conversationId) {
    if (chatPusher) {
        chatPusher.disconnect();
    }
    
    chatPusher = new Pusher('{{ config("broadcasting.connections.pusher.key") }}', {
        cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}',
        forceTLS: true
    });
    
    chatChannel = chatPusher.subscribe('private-chat.' + conversationId);
    chatChannel.bind('App\\Events\\ChatMessageSent', function(data) {
        if (currentChatConversationId === data.message.conversation_id) {
            appendChatMessage(data.message);
            scrollChatToBottom();
        }
        updateUnreadBadge();
    });
}

// Append new message to chat
function appendChatMessage(message) {
    const messagesDiv = document.getElementById('chatMessages');
    const isSent = message.sender_id === {{ Auth::id() }};
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message ${isSent ? 'sent' : 'received'}`;
    
    if (message.message_type === 'file') {
        const downloadUrl = `{{ route('chat.download', '') }}/${message.id}`;
        messageDiv.innerHTML = `
            <div>${message.message}</div>
            <div class="chat-file-preview">
                <i class="fas fa-file"></i>
                <span class="chat-file-name">${message.file_name}</span>
                <a href="${downloadUrl}" target="_blank"><i class="fas fa-download"></i></a>
            </div>
            <div class="chat-message-time">${formatTime(message.created_at)}</div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div>${message.message}</div>
            <div class="chat-message-time">${formatTime(message.created_at)}</div>
        `;
    }
    
    messagesDiv.appendChild(messageDiv);
}

// Scroll chat to bottom
function scrollChatToBottom() {
    const messagesDiv = document.getElementById('chatMessages');
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

// Format time
function formatTime(dateString) {
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

// Update unread badge
function updateUnreadBadge() {
    fetch('{{ route("chat.unread-count") }}')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('chatUnreadBadge');
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        });
}

// Handle send message form
document.getElementById('chatSendForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const conversationId = document.getElementById('chatConversationId').value;
    const messageInput = document.getElementById('chatMessageInput');
    const message = messageInput.value.trim();
    const fileInput = document.getElementById('chatFileInput');
    const formData = new FormData();
    
    formData.append('message', message);
    
    if (fileInput.files.length > 0) {
        formData.append('file', fileInput.files[0]);
        document.getElementById('chatFilePreview').style.display = 'none';
        fileInput.value = '';
    }
    
    if (!message && fileInput.files.length === 0) {
        return;
    }
    
    const sendBtn = document.getElementById('chatSendBtn');
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch(`/chat/admin/send/${conversationId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            messageInput.focus();
        }
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
    })
    .catch(error => {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
    });
});

// Handle file input change
document.getElementById('chatFileInput').addEventListener('change', function() {
    if (this.files.length > 0) {
        const file = this.files[0];
        const preview = document.getElementById('chatFilePreview');
        preview.innerHTML = `
            <i class="fas fa-file"></i>
            <span class="chat-file-name">${file.name}</span>
            <button type="button" class="chat-file-remove" onclick="removeChatFile()">
                <i class="fas fa-times"></i>
            </button>
        `;
        preview.style.display = 'flex';
    }
});

// Remove file from chat
function removeChatFile() {
    document.getElementById('chatFileInput').value = '';
    document.getElementById('chatFilePreview').style.display = 'none';
}

// Load unread badge on page load
document.addEventListener('DOMContentLoaded', function() {
    updateUnreadBadge();
    
    // Update badge every 30 seconds
    setInterval(updateUnreadBadge, 30000);
});

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('chatDropdown');
    const icon = document.querySelector('.chat-icon-btn');
    
    if (!dropdown.contains(event.target) && !icon.contains(event.target)) {
        dropdown.classList.remove('active');
        resetChatView();
    }
});

    </script>

    <script>
// ==================== MESSAGE DROPDOWN FUNCTIONS ====================
let messagePusher = null;            // Pusher instance for real-time messaging
let messageChannel = null;            // Pusher channel for message updates
let currentMessageConversationId = null; // ID of the current active conversation

// Check if message dropdown elements exist in the DOM
function messageDropdownExists() {
    return document.getElementById('messageDropdown') !== null;
}

// Toggle message dropdown visibility
// Shows/hides the message dropdown and loads admin list when opened
function messageToggleDropdown(event) {
    event.stopPropagation(); // Prevent event from bubbling up
    const dropdown = document.getElementById('messageDropdown');
    dropdown.classList.toggle('active');
    
    if (dropdown.classList.contains('active')) {
        messageLoadAdmins(); // Load list of admins when dropdown opens
        
        // Clear notification indicator when dropdown is opened
        const indicator = document.getElementById('messageNotificationIndicator');
        const container = document.getElementById('messageIconContainer');
        
        if (indicator) {
            indicator.style.display = 'none';
        }
        if (container) {
            container.classList.remove('has-unread', 'many-unread');
        }
        
        // Reset envelope icon to empty state
        const envelopeIcon = document.querySelector('.message-icon-btn .fa-envelope');
        if (envelopeIcon) {
            envelopeIcon.classList.remove('fas');
            envelopeIcon.classList.add('far');
            envelopeIcon.style.color = '';
        }
        
        // Mark all messages as read when opening dropdown
        messageMarkAllAsRead();
    }
}

// Mark all messages as read for the current user
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
            // Update badge count to 0
            const badge = document.getElementById('messageUnreadBadge');
            if (badge) {
                badge.style.display = 'none';
            }
        }
    })
    .catch(error => console.error('Error marking messages as read:', error));
}

/* 
// COMMENTED OUT: Initialize message notification system
// This function sets up Pusher for real-time message notifications
function initializeMessageNotifications() {
    // Check if user is logged in and not admin
    @auth
        @if(Auth::user()->role !== 'admin' && Auth::user()->role !== 'superadmin')
            if (typeof Pusher !== 'undefined') {
                const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
                    cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
                    forceTLS: true
                });
                
                // Subscribe to user's private channel for message notifications
                const channel = pusher.subscribe('private-user.{{ Auth::id() }}');
                
                // Listen for new message events
                channel.bind('App\\Events\\NewMessageNotification', function(data) {
                    console.log('New message notification received:', data);
                    
                    // Update badge immediately
                    messageUpdateUnreadBadge();
                    
                    // Show desktop notification if allowed
                    if (Notification.permission === 'granted' && !document.hasFocus()) {
                        showDesktopNotification(data.message);
                    }
                    
                    // Flash the message icon
                    flashMessageIcon();
                });
                
                // Listen for message read events
                channel.bind('App\\Events\\MessageRead', function(data) {
                    console.log('Message read event received:', data);
                    messageUpdateUnreadBadge();
                });
            }
        @endif
    @endauth
}

// COMMENTED OUT: Show desktop notification for new messages
function showDesktopNotification(message) {
    const notification = new Notification('New Message', {
        body: `You have a new message from ${message.sender_name || 'Admin'}`,
        icon: '{{ asset("KG2025 (2).png") }}',
        tag: 'new-message'
    });
    
    notification.onclick = function() {
        window.focus();
        messageToggleDropdown(event);
        notification.close();
    };
}

// COMMENTED OUT: Flash the message icon to get attention
function flashMessageIcon() {
    const iconBtn = document.querySelector('.message-icon-btn');
    const container = document.getElementById('messageIconContainer');
    
    if (iconBtn && container) {
        // Add flashing animation
        iconBtn.style.animation = 'none';
        container.style.animation = 'none';
        
        setTimeout(() => {
            iconBtn.style.animation = 'flash 1s 3';
            container.style.animation = 'shake 0.5s 2';
        }, 10);
        
        // Remove animation after it completes
        setTimeout(() => {
            iconBtn.style.animation = '';
            container.style.animation = '';
        }, 4000);
    }
}

// COMMENTED OUT: Add CSS animations for notification effects
const style = document.createElement('style');
style.textContent = `
    @keyframes flash {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-3px); }
        20%, 40%, 60%, 80% { transform: translateX(3px); }
    }
`;
document.head.appendChild(style);

// COMMENTED OUT: Request notification permission from browser
function requestNotificationPermission() {
    if ("Notification" in window) {
        if (Notification.permission === "default") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    console.log("Notification permission granted");
                }
            });
        }
    }
}
*/

// Close message dropdown
function messageCloseDropdown(event) {
    if (!messageDropdownExists()) return;
    
    if (event) event.stopPropagation();
    const dropdown = document.getElementById('messageDropdown');
    dropdown.classList.remove('active');
    messageResetView();
}

// Reset message view to show admin list
function messageResetView() {
    if (!messageDropdownExists()) return;
    
    const adminsDiv = document.getElementById('messageAdmins');
    const chatArea = document.getElementById('messageChatArea');
    
    if (adminsDiv) adminsDiv.style.display = 'block';
    if (chatArea) chatArea.style.display = 'none';
    
    document.getElementById('messageConversationId').value = '';
    document.getElementById('messageAdminId').value = '';
}

// Load list of available admins from the server
async function messageLoadAdmins() {
    if (!messageDropdownExists()) return;
    
    const adminsDiv = document.getElementById('messageAdmins');
    if (!adminsDiv) return;
    
    // Add close/back button container at the top
    adminsDiv.innerHTML = `
        <div id="messageBackButtonContainer" style="margin-bottom: 15px; display: none;">
            <button type="button" class="message-back-btn" onclick="messageBackToAdminList()" style="background: #f8f9fa; border: 1px solid #ddd; border-radius: 5px; padding: 8px 15px; cursor: pointer; display: flex; align-items: center; gap: 8px; color: #555; font-size: 14px; width: 100%;">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Chat List</span>
            </button>
        </div>
        <div id="messageAdminsList" class="text-center text-muted py-3">
            <i class="fas fa-spinner fa-spin"></i> Loading admins...
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

// Function to show the back button
function showMessageBackButton() {
    const backButtonContainer = document.getElementById('messageBackButtonContainer');
    if (backButtonContainer) {
        backButtonContainer.style.display = 'block';
    }
}

// Function to hide the back button
function hideMessageBackButton() {
    const backButtonContainer = document.getElementById('messageBackButtonContainer');
    if (backButtonContainer) {
        backButtonContainer.style.display = 'none';
    }
}

// Function to go back to admin list from chat view
function messageBackToAdminList() {
    // Hide the chat area and message list
    const chatArea = document.getElementById('messageChatArea');
    const chatMessages = document.getElementById('messageChatMessages');
    
    if (chatArea) {
        chatArea.style.display = 'none';
    }
    
    if (chatMessages) {
        chatMessages.innerHTML = ''; // Clear messages but keep conversation data
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
    
    // Reset conversation tracking (optional, depends on your needs)
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

// Display admins list in the dropdown
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
        html += `
            <div class="message-admin-item" onclick="messageOpenChat(${admin.id}, '${admin.name.replace(/'/g, "\\'")}')">
                <img src="${imageUrl}" alt="${admin.name}" onerror="this.src='{{ asset('default-user.png') }}'">
                <div class="message-admin-info">
                    <div class="message-admin-name">${admin.name}</div>
                    <div class="message-admin-email">${admin.email}</div>
                </div>
            </div>
        `;
    });
    
    adminsList.innerHTML = html;
}
// Open chat with a specific admin
function messageOpenChat(adminId, adminName) {
    if (!messageDropdownExists()) return;
    
    document.getElementById('messageAdminId').value = adminId;
    
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
        header.innerHTML = `<i class="fas fa-comments me-2"></i>Chat with ${adminName}`;
    }
    
    // Show the back button when in chat view
    showMessageBackButton();
    
    messageLoadConversation(adminId);
}


// Load conversation with a specific admin
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


// Display messages in the chat area
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

// CSS to the document head
document.head.insertAdjacentHTML('beforeend', messageBackButtonCSS);

// Format timestamp for messages
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

// Scroll chat messages to the bottom
function messageScrollToBottom() {
    const messagesDiv = document.getElementById('messageChatMessages');
    if (messagesDiv) {
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }
}

// Mark all messages in a conversation as read
function messageMarkAsRead(conversationId) {
    fetch(`/chat/conversations/${conversationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).catch(error => console.error('Error marking as read:', error));
}

// Send a message to an admin
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
            
            /* 
            // COMMENTED OUT: Create notification for admin after successful message send
            await createMessageNotificationForAdmin(adminId, data.message);
            */
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

/* 
// COMMENTED OUT: Create notification for admin in the admin_message_notif table
async function createMessageNotificationForAdmin(adminId, messageData) {
    try {
        const currentUser = window.currentUser;
        if (!currentUser || !currentUser.id) {
            console.error('User not authenticated');
            return;
        }
        
        const notificationData = {
            type: 'system_chat',
            title: 'New Message from ' + currentUser.name,
            message: messageData.message || 'You have received a new message',
            sender_id: currentUser.id,
            sender_name: currentUser.name,
            sender_email: currentUser.email,
            receiver_id: adminId,
            message_id: messageData.id // Use the message ID from the response
        };
        
        const response = await fetch('{{ route("chat.create-notification") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(notificationData)
        });
        
        const data = await response.json();
        if (data.success) {
            console.log('Notification created for admin:', data.notification);
            
            // Update admin notification badge in real-time
            updateAdminNotificationBadge();
        } else {
            console.error('Failed to create notification:', data.message);
        }
    } catch (error) {
        console.error('Error creating notification:', error);
    }
}

// COMMENTED OUT: Update admin notification badge (would be called from admin dashboard)
async function updateAdminNotificationBadge() {
    // This function would typically be called from the admin dashboard
    // For now, we'll just log that a notification was created
    console.log('Admin notification created - badge should update in admin dashboard');
}
*/

// Append a single message to the chat display
function messageAppendMessage(message) {
    const messagesDiv = document.getElementById('messageChatMessages');
    if (!messagesDiv) return;
    
    const isSent = message.sender_id === {{ Auth::id() }};
    const messageDiv = document.createElement('div');
    messageDiv.className = `message-chat-message ${isSent ? 'sent' : 'received'}`;
    
    if (message.message_type === 'file') {
        const downloadUrl = `{{ route('chat.messages.download', '') }}/${message.id}`;
        const fileSize = message.file_size ? messageFormatFileSize(message.file_size) : '';
        
        // Determine file icon based on file type
        const fileIcon = messageGetFileIcon(message.file_name, message.file_mime);
        
        // Check if file is an image
        const isImage = message.file_mime && message.file_mime.startsWith('image/');
        
        // Image preview URL
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

// Open modal for full-size image preview
function messageOpenImageModal(imageUrl, fileName) {
    // Create modal if it doesn't exist
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
        
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                messageCloseImageModal();
            }
        });
        
        // Close with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.style.display === 'block') {
                messageCloseImageModal();
            }
        });
    } else {
        // Update modal content
        modal.querySelector('#fullSizeImage').src = imageUrl;
        modal.querySelector('.modal-header h3').textContent = fileName;
        modal.querySelector('.btn-download').href = imageUrl;
        modal.querySelector('.btn-download').download = fileName;
    }
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent scrolling
}

// Close image preview modal
function messageCloseImageModal() {
    const modal = document.getElementById('imagePreviewModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Get appropriate file icon based on file type
function messageGetFileIcon(fileName, mimeType) {
    const ext = fileName ? fileName.split('.').pop().toLowerCase() : '';
    
    // Image files
    if (mimeType && mimeType.startsWith('image/')) {
        return '<i class="fas fa-image"></i>';
    }
    // PDF files
    else if (ext === 'pdf' || (mimeType && mimeType.includes('pdf'))) {
        return '<i class="fas fa-file-pdf"></i>';
    }
    // Word documents
    else if (['doc', 'docx'].includes(ext) || (mimeType && mimeType.includes('word'))) {
        return '<i class="fas fa-file-word"></i>';
    }
    // Excel files
    else if (['xls', 'xlsx'].includes(ext) || (mimeType && mimeType.includes('excel'))) {
        return '<i class="fas fa-file-excel"></i>';
    }
    // Archive files
    else if (['zip', 'rar', '7z'].includes(ext) || (mimeType && mimeType.includes('zip'))) {
        return '<i class="fas fa-file-archive"></i>';
    }
    // Text files
    else if (ext === 'txt' || (mimeType && mimeType.includes('text/'))) {
        return '<i class="fas fa-file-alt"></i>';
    }
    // Default file icon
    else {
        return '<i class="fas fa-file"></i>';
    }
}

// Format file size to human readable format
function messageFormatFileSize(bytes) {
    if (!bytes) return '';
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

// Remove selected file from file input
function messageRemoveFile() {
    const fileInput = document.getElementById('messageFileInput');
    const preview = document.getElementById('messageFilePreview');
    
    if (fileInput) fileInput.value = '';
    if (preview) {
        preview.style.display = 'none';
        preview.innerHTML = '';
    }
}

// Update unread message badge count
async function messageUpdateUnreadBadge() {
    try {
        const response = await fetch('{{ route("chat.unread-count") }}');
        const data = await response.json();
        const badge = document.getElementById('messageUnreadBadge');
        const indicator = document.getElementById('messageNotificationIndicator');
        const container = document.getElementById('messageIconContainer');
        
        if (badge && indicator && container) {
            if (data.count > 0) {
                // Show badge with count
                badge.textContent = data.count > 9 ? '9+' : data.count;
                badge.style.display = 'flex';
                
                // Show notification indicator
                indicator.style.display = 'block';
                
                // Add class to container for styling
                container.classList.add('has-unread');
                
                // Add animation class for many unread
                if (data.count > 5) {
                    container.classList.add('many-unread');
                } else {
                    container.classList.remove('many-unread');
                }
                
                // Optional: Change envelope icon to indicate unread
                const envelopeIcon = document.querySelector('.message-icon-btn .fa-envelope');
                if (envelopeIcon) {
                    envelopeIcon.classList.remove('far');
                    envelopeIcon.classList.add('fas');
                    envelopeIcon.style.color = '#ff4757';
                }
            } else {
                // Hide both badge and indicator
                badge.style.display = 'none';
                indicator.style.display = 'none';
                container.classList.remove('has-unread', 'many-unread');
                
                // Reset envelope icon
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

// Initialize event listeners for message dropdown
function initMessageEventListeners() {
    const messageForm = document.getElementById('messageChatForm');
    
    // Only add event listener if form exists (non-admin users)
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            messageSend();
        });
    } else {
        console.warn('messageChatForm not found - user may be admin');
    }
    
    // Handle file input if it exists
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
    
    // Update unread badge if user is logged in and not admin
    const messageIconContainer = document.querySelector('.message-icon-container');
    if (messageIconContainer) {
        messageUpdateUnreadBadge();
        setInterval(messageUpdateUnreadBadge, 30000); // Update every 30 seconds
    }
}

// Initialize message functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize for non-admin users
    @auth
        @if(Auth::user()->role !== 'admin' && Auth::user()->role !== 'superadmin')
            initMessageEventListeners();
            messageUpdateUnreadBadge(); // Initial check
            
            /* 
            // COMMENTED OUT: Initialize notification system
            initializeMessageNotifications();
            requestNotificationPermission();
            */
            
            // Check for new messages every 30 seconds
            setInterval(messageUpdateUnreadBadge, 30000);
            
            // Also check when user comes back to the tab
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    messageUpdateUnreadBadge();
                }
            });
        @endif
    @endauth
});
// ==================== END MESSAGE DROPDOWN FUNCTIONS ====================
    </script>

    @auth
<script>
    window.currentUser = {
        id: {{ Auth::id() }},
        name: "{{ Auth::user()->name }}",
        email: "{{ Auth::user()->email }}",
        role: "{{ Auth::user()->role }}"
    };
</script>
@endauth
</body>
</html>
