<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Email Chat | LegalConnect</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/email-chat.blade.css') }}">
    <style>
        /* Compose Form Header Styles */
        .compose-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            cursor: pointer;
        }

        .compose-header h5 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
        }

        .compose-header-actions {
            display: flex;
            gap: 8px;
        }

        .compose-header-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background-color 0.2s;
            font-size: 12px;
            color: white !important;
        }

        .compose-header-btn:hover {
            background: none;
        }

        .compose-form-content {
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-top: none;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .compose-form.minimized .compose-form-content {
            display: none;
        }

        .compose-form.minimized {
            margin-bottom: 0;
        }

        /* Minimized state indicator */
        .minimized-indicator {
            display: none;
            padding: 8px 15px;
            background: #e9ecef;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 12px;
            color: #ffffffff;
            cursor: pointer;
            margin: 10px 15px;
        }

        .compose-form.minimized + .minimized-indicator {
            display: block;
        }
    </style>
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
                <a href="{{ url('/admindashboard') }}" class="list-group-item list-group-item-action {{ request()->is('admindashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="{{ url('/administrator') }}" class="list-group-item list-group-item-action {{ request()->is('administrator') ? 'active' : '' }}">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Set Time</span>
                </a>
                 <a href="{{ url('/appointments') }}" class="list-group-item list-group-item-action {{ request()->is('appointments') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Logs Requests</span>
                </a>
               <a href="{{ url('/email-chat') }}" class="list-group-item list-group-item-action {{ request()->is('email-chat') ? 'active' : '' }}">
                    <i class="fas fa-envelope"></i>
                    <span>Email Chat</span>
                </a>

                <a href="#requestsSubmenu" class="list-group-item list-group-item-action {{ request()->is('clientstbl') || request()->is('adminAcceptedRequest') || request()->is('adminDeniedRequest') ? 'active' : '' }}" data-bs-toggle="collapse" aria-expanded="{{ request()->is('clientstbl') || request()->is('adminAcceptedRequest') || request()->is('adminDeniedRequest') ? 'true' : 'false' }}">
                    <i class="fas fa-list-alt"></i>
                    <span>Appointment Requests</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu collapse {{ request()->is('clientstbl') || request()->is('adminAcceptedRequest') || request()->is('adminDeniedRequest') ? 'show' : '' }} list-group" id="requestsSubmenu">
                    <a href="{{ url('/clientstbl') }}" class="list-group-item list-group-item-action {{ request()->is('clientstbl') ? 'active' : '' }}">
                        <i class="fas fa-clock"></i>
                        <span>Pending Requests</span>
                    </a>
                    <a href="{{ url('/adminAcceptedRequest') }}" class="list-group-item list-group-item-action {{ request()->is('adminAcceptedRequest') ? 'active' : '' }}">
                        <i class="fas fa-check-circle"></i>
                        <span>Accepted Requests</span>
                    </a>
                    <a href="{{ url('/adminDeniedRequest') }}" class="list-group-item list-group-item-action {{ request()->is('adminDeniedRequest') ? 'active' : '' }}">
                        <i class="fas fa-times-circle"></i>
                        <span>Denied Requests</span>
                    </a>
                </div>

                <a href="{{ url('/adminAccount') }}" class="list-group-item list-group-item-action {{ request()->is('adminAccount') ? 'active' : '' }}">
                    <i class="fas fa-user-cog"></i>
                    <span>All Accounts</span>
                </a>
            </div>
        </div>
        
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="top-bar" role="banner">
                <div class="burger-menu">
                    <button class="btn btn-primary" id="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                
                <div class="top-bar-spacer"></div>

                <!-- Log Out -->
                <form id="logout-form" action="{{ route('custom.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <button type="button" class="btn logout-btn" aria-label="Log out" onclick="document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>

            <div class="email-chat-container">
                <!-- Sidebar -->
                <div class="email-sidebar">
                    <div style="padding: 15px; border-bottom: 1px solid #dee2e6;">
                        <h3>Email Chat</h3>
                        <button onclick="fetchNewEmails()" style="padding: 5px 10px; background: #28a745; color: white; border: none; border-radius: 4px; margin-bottom: 10px;">
                            Check New Emails
                        </button>
                        <button onclick="startNewEmail()" style="padding: 5px 10px; background: #17a2b8; color: white; border: none; border-radius: 4px; width: 100%;">
                            + New Email
                        </button>
                    </div>

                    <!-- Registered Users Section -->
                    <div class="section-header">Registered Users</div>
                    <div id="users-list">
                        @if($users->count() > 0)
                            @foreach($users as $user)
                            <div class="contact-item" onclick="selectUser('{{ $user->email }}', '{{ $user->name }}')">
                                <div class="contact-name">{{ $user->name }}</div>
                                <div class="contact-email">{{ $user->email }}</div>
                            </div>
                            @endforeach
                        @else
                            <div class="no-contacts">No users found</div>
                        @endif
                    </div>

                    <!-- Email Conversations Section -->
                    @if($emailConversations->count() > 0)
                    <div class="section-header">Email Conversations</div>
                    <div id="email-conversations-list">
                        @foreach($emailConversations as $emailAddress => $conversation)
                        <div class="contact-item" onclick="selectEmail('{{ $emailAddress }}', '{{ $conversation->first()->sender_name ?? $emailAddress }}')">
                            <div class="contact-name">{{ $conversation->first()->sender_name ?? $emailAddress }}</div>
                            <div class="contact-email">{{ $emailAddress }}</div>
                            <div style="font-size: 0.8em; margin-top: 4px; opacity: 0.7;">
                                {{ Str::limit($conversation->first()->subject, 30) }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                
                <!-- Chat Area -->
                <div class="email-chat-area">
                    <div class="chat-header">
                        <h4 id="current-contact">Select a contact to start chatting</h4>
                        <div id="contact-email" style="font-size: 0.9em; opacity: 0.9;"></div>
                    </div>
                    
                    <div class="messages-container" id="messages-container">
                        <div style="text-align: center; padding: 40px; color: #6c757d;">
                            <p>Select a contact from the sidebar to view messages</p>
                        </div>
                    </div>
                    
                    <!-- Compose Form with Minimize/Hide Functionality -->
                    <div class="compose-form" id="compose-form" style="display: none;">
                        <div class="compose-header" id="compose-header">
                            <h5><i class="fas fa-edit me-2"></i>Compose Message</h5>
                            <div class="compose-header-actions">
                                <button class="compose-header-btn" id="minimize-compose" title="Minimize">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button class="compose-header-btn"  onclick="expandComposeForm()" style="color: #6c757d; font-size: 12px;">
                                    <i class="fas fa-expand me-1"></i> Expand
                                </button>
                                <!--<button class="compose-header-btn" id="close-compose" title="Close" style="display: none;">
                                    <i class="fas fa-times"></i>
                                </button>-->
                            </div>
                        </div>
                        <div class="compose-form-content">
                            <form id="reply-form">
                                @csrf
                                <input type="hidden" id="reply-to-email">
                                
                                <div style="margin-bottom: 10px;">
                                    <input type="text" id="subject" placeholder="Subject" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
                                </div>
                                
                                <div style="margin-bottom: 10px;">
                                    <textarea id="message" placeholder="Type your message..." rows="4" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required></textarea>
                                </div>
                                
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <button type="submit" style="padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px;">
                                        <i class="fas fa-paper-plane me-1"></i> Send Email
                                    </button>
                                    <button type="button" id="cancel-compose" style="padding: 8px 15px; background: #6c757d; color: white; border: none; border-radius: 4px;">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Minimized State Indicator 
                    <div class="minimized-indicator" id="minimized-indicator" style="display: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span>
                                <i class="fas fa-edit me-2"></i>
                                Compose message to <strong id="minimized-contact"></strong>
                            </span>
                            <button class="compose-header-btn" onclick="expandComposeForm()" style="color: #6c757d; font-size: 12px;">
                                <i class="fas fa-expand me-1"></i> Expand
                            </button>
                        </div>
                    </div>-->
                </div>
            </div>

            <!-- New Email Modal -->
            <div id="new-email-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; width: 400px;">
                    <h3>New Email</h3>
                    <form id="new-email-form">
                        @csrf
                        <div style="margin-bottom: 10px;">
                            <label>To:</label>
                            <input type="email" id="new-to-email" placeholder="Recipient email" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <label>Subject:</label>
                            <input type="text" id="new-subject" placeholder="Subject" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label>Message:</label>
                            <textarea id="new-message" placeholder="Type your message..." rows="4" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required></textarea>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" style="padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px;">Send</button>
                            <button type="button" onclick="closeNewEmail()" style="padding: 8px 15px; background: #6c757d; color: white; border: none; border-radius: 4px;">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    let currentEmail = '';
    let currentName = '';
    let currentUserEmail = '{{ Auth::user()->email }}';
    let isComposeMinimized = false;

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize sidebar functionality
        initializeSidebar();
        
        // Initialize compose form functionality
        initializeComposeForm();
        
        // Load Gmail messages on page load
        setTimeout(() => {
            fetchNewEmails();
        }, 1000);
        
        // Auto-fetch new emails every 2 minutes
        setInterval(fetchNewEmails, 120000);
    });

    function initializeSidebar() {
        // Toggle sidebar
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('wrapper').classList.toggle('toggled');
        });
        
        // Close other submenus when opening a new one
        const menuItems = document.querySelectorAll('.list-group-item[data-bs-toggle="collapse"]');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                const targetId = this.getAttribute('href');
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                
                if (isExpanded) return;
                
                menuItems.forEach(otherItem => {
                    if (otherItem !== this) {
                        const otherTargetId = otherItem.getAttribute('href');
                        const otherTarget = document.querySelector(otherTargetId);
                        if (otherTarget && otherTarget.classList.contains('show')) {
                            const bsCollapse = new bootstrap.Collapse(otherTarget);
                            bsCollapse.hide();
                        }
                    }
                });
            });
        });
        
        // Set active menu item on click
        const allMenuItems = document.querySelectorAll('.list-group-item');
        allMenuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                if (this.hasAttribute('data-bs-toggle') && 
                    this.getAttribute('data-bs-toggle') === 'collapse') {
                    return;
                }
                
                allMenuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    function initializeComposeForm() {
        // Minimize compose form
        document.getElementById('minimize-compose').addEventListener('click', function() {
            minimizeComposeForm();
        });

        // Close compose form
        document.getElementById('close-compose').addEventListener('click', function() {
            closeComposeForm();
        });

        // Cancel compose form
        document.getElementById('cancel-compose').addEventListener('click', function() {
            closeComposeForm();
        });

        // Click on minimized indicator to expand
        document.getElementById('minimized-indicator').addEventListener('click', function() {
            expandComposeForm();
        });
    }

    function minimizeComposeForm() {
        const composeForm = document.getElementById('compose-form');
        const minimizedIndicator = document.getElementById('minimized-indicator');
        const minimizeBtn = document.getElementById('minimize-compose');
        const closeBtn = document.getElementById('close-compose');

        composeForm.classList.add('minimized');
        minimizedIndicator.style.display = 'block';
        minimizeBtn.style.display = 'none';
        closeBtn.style.display = 'inline-block';
        isComposeMinimized = true;

        // Update minimized contact info
        document.getElementById('minimized-contact').textContent = currentName || currentEmail;
    }

    function expandComposeForm() {
        const composeForm = document.getElementById('compose-form');
        const minimizedIndicator = document.getElementById('minimized-indicator');
        const minimizeBtn = document.getElementById('minimize-compose');
        const closeBtn = document.getElementById('close-compose');

        composeForm.classList.remove('minimized');
        minimizedIndicator.style.display = 'none';
        minimizeBtn.style.display = 'inline-block';
        closeBtn.style.display = 'none';
        isComposeMinimized = false;
    }

    function closeComposeForm() {
        const composeForm = document.getElementById('compose-form');
        const minimizedIndicator = document.getElementById('minimized-indicator');
        const minimizeBtn = document.getElementById('minimize-compose');
        const closeBtn = document.getElementById('close-compose');

        composeForm.style.display = 'none';
        minimizedIndicator.style.display = 'none';
        composeForm.classList.remove('minimized');
        minimizeBtn.style.display = 'inline-block';
        closeBtn.style.display = 'none';
        isComposeMinimized = false;

        // Clear form
        document.getElementById('subject').value = '';
        document.getElementById('message').value = '';
    }

    // ... rest of your existing JavaScript functions (fetchNewEmails, selectUser, selectEmail, updateChatHeader, loadConversation, etc.) remain the same ...

    async function fetchNewEmails() {
        console.log('📧 Fetching new emails...');
        showNotification('Checking for emails...', 'info');
        
        try {
            const response = await fetch('{{ route('emails.fetch') }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                timeout: 30000 // 30 second timeout
            });

            if (!response.ok) {
                if (response.status >= 500) {
                    throw new Error(`Server error: ${response.status}`);
                }
                const errorText = await response.text();
                console.error('Server error:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                showNotification('❌ Email server is taking too long to respond. Please try again later.', 'error');
                return;
            }

            const data = await response.json();
            console.log('Email fetch response:', data);
            
            if (data.status === 'success') {
                showNotification(`✅ ${data.message}`, 'success');
                if (currentEmail) {
                    setTimeout(() => loadConversation(currentEmail), 1000);
                }
            } else {
                showNotification(`❌ ${data.message}`, 'error');
            }
        } catch (error) {
            console.error('Fetch error:', error);
            if (error.name === 'TimeoutError' || error.message.includes('timeout')) {
                showNotification('❌ Email fetch timed out. Please try again.', 'error');
            } else {
                showNotification(`❌ Error fetching emails: ${error.message}`, 'error');
            }
        }
    }

    function selectUser(email, name) {
        currentEmail = email;
        currentName = name;
        updateChatHeader(name, email);
        loadConversation(email);
        showComposeForm();
    }

    function selectEmail(email, name) {
        currentEmail = email;
        currentName = name || email;
        updateChatHeader(name || email, email);
        loadConversation(email);
        showComposeForm();
    }

    function updateChatHeader(name, email) {
        document.getElementById('current-contact').textContent = name;
        document.getElementById('contact-email').textContent = email;
        document.getElementById('reply-to-email').value = email;
        
        // Show user context
        const contactEmailElement = document.getElementById('contact-email');
        contactEmailElement.innerHTML = `${email} <br><small>Your email: ${currentUserEmail}</small>`;
    }

    function showComposeForm() {
        const composeForm = document.getElementById('compose-form');
        const minimizedIndicator = document.getElementById('minimized-indicator');
        
        // Reset form state
        composeForm.style.display = 'block';
        composeForm.classList.remove('minimized');
        minimizedIndicator.style.display = 'none';
        
        // Reset buttons
        document.getElementById('minimize-compose').style.display = 'inline-block';
        document.getElementById('close-compose').style.display = 'none';
        isComposeMinimized = false;

        // Set default subject for replies
        document.getElementById('subject').value = 'Re: ';
        document.getElementById('subject').focus();
    }

    // ... rest of your existing functions (loadConversation, displayMessages, createMessageElement, etc.) ...

    async function loadConversation(email) {
        if (!email) {
            console.log('No email selected');
            return;
        }
        
        console.log(`Loading conversation for: ${email} (current user: ${currentUserEmail})`);
        showLoading(true);
        
        try {
            const response = await fetch(`{{ route('email.conversation', '') }}/${encodeURIComponent(email)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server response:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Conversation data for user:', data);
            
            if (data.status === 'success') {
                displayMessages(data);
            } else {
                throw new Error(data.message || 'Failed to load conversation');
            }
            
        } catch (error) {
            console.error('Error loading conversation:', error);
            showNotification(`❌ Error loading conversation: ${error.message}`, 'error');
            
            // Show empty state
            const container = document.getElementById('messages-container');
            container.innerHTML = `
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <p>Error loading messages. Please try again.</p>
                    <button onclick="loadConversation('${email}')" style="padding: 5px 10px; background: #007bff; color: white; border: none; border-radius: 4px; margin-top: 10px;">
                        Retry
                    </button>
                </div>
            `;
        } finally {
            showLoading(false);
        }
    }

    function displayMessages(data) {
        const container = document.getElementById('messages-container');
        
        console.log('Displaying messages with data:', data);
        
        if (data.status !== 'success' || !data.conversation || data.conversation.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <p>No messages yet. Start a conversation!</p>
                    <small>Messages between you and this contact will appear here.</small>
                </div>
            `;
            return;
        }

        container.innerHTML = '';
        
        const messages = data.conversation;
        
        console.log(`Displaying ${messages.length} messages in chronological order:`);
        messages.forEach((msg, index) => {
            console.log(`Message ${index}: ${msg.created_at} -> ${msg.created_at_formatted}`);
        });

        messages.forEach((msg) => {
            const messageDiv = createMessageElement(msg);
            container.appendChild(messageDiv);
        });
        
        container.scrollTop = container.scrollHeight;
    }

    function createMessageElement(msg) {
        const messageDiv = document.createElement('div');
        
        const isIncoming = msg.sender_email !== currentUserEmail;
        const messageType = isIncoming ? 'incoming' : 'outgoing';
        const senderName = isIncoming ? 
            (msg.sender_name || msg.sender_email || 'Unknown Sender') : 
            'You';
        
        const displayTime = msg.created_at_formatted || formatDatabaseTime(msg.created_at);
        
        messageDiv.className = `message ${messageType}`;
        messageDiv.setAttribute('data-message-id', msg.id);
        messageDiv.setAttribute('data-timestamp', msg.created_at);
        
        messageDiv.innerHTML = `
            <div class="message-header">
                <span class="sender-name">${escapeHtml(senderName)}</span>
                <span class="timestamp">${displayTime}</span>
            </div>
            ${msg.subject && msg.subject !== 'No Subject' ? 
                `<div class="message-subject"><strong>${escapeHtml(msg.subject)}</strong></div>` : ''}
            <div class="message-text">${escapeHtml(msg.message)}</div>
            <div class="message-footer">
                <span class="message-type">${isIncoming ? '📧 Email' : '💬 Sent'}</span>
                <span class="message-email">
                    ${isIncoming ? 
                        `From: ${msg.sender_email}` : 
                        `To: ${msg.receiver_email}`}
                </span>
            </div>
        `;
        
        return messageDiv;
    }

    function formatDatabaseTime(timestamp) {
        if (!timestamp) return 'Unknown time';
        
        try {
            const date = new Date(timestamp);
            
            if (isNaN(date.getTime())) {
                console.error('Invalid timestamp:', timestamp);
                return 'Invalid time';
            }
            
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
        } catch (e) {
            console.error('Error formatting database timestamp:', e, timestamp);
            return 'Invalid time';
        }
    }

    function showLoading(show) {
        const container = document.getElementById('messages-container');
        if (show) {
            container.innerHTML = `
                <div style="text-align: center; padding: 20px; color: #6c757d;">
                    <div class="spinner-border spinner-border-sm" role="status"></div>
                    Loading messages...
                </div>
            `;
        }
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        `;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    // Utility functions
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // New Email Modal Functions
    function startNewEmail() {
        document.getElementById('new-email-modal').style.display = 'block';
    }

    function closeNewEmail() {
        document.getElementById('new-email-modal').style.display = 'none';
        document.getElementById('new-email-form').reset();
    }

    // Form submission handlers
    document.getElementById('new-email-form').addEventListener('submit', function(e) {
        e.preventDefault();
        sendEmail(
            document.getElementById('new-to-email').value,
            document.getElementById('new-subject').value,
            document.getElementById('new-message').value,
            true // isNewEmail
        );
    });

    document.getElementById('reply-form').addEventListener('submit', function(e) {
        e.preventDefault();
        if (!currentEmail) {
            showNotification('Please select a contact first', 'warning');
            return;
        }
        
        sendEmail(
            currentEmail,
            document.getElementById('subject').value,
            document.getElementById('message').value,
            false // isReply
        );
    });

    async function sendEmail(toEmail, subject, message, isNewEmail = false) {
        const formData = new FormData();
        formData.append('to_email', toEmail);
        formData.append('subject', subject);
        formData.append('message', message);
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        
        try {
            showNotification('Sending email...', 'info');
            
            const response = await fetch('{{ route('email.send.chat') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server error:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const contentType = response.headers.get('content-type');
            let data;
            
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                throw new Error(`Server returned: ${text.substring(0, 100)}`);
            }

            if (data.status === 'success') {
                showNotification('✅ Email sent successfully!', 'success');
                
                if (isNewEmail) {
                    closeNewEmail();
                } else {
                    document.getElementById('message').value = '';
                    // Don't clear subject for replies to maintain conversation context
                }
                
                if (toEmail === currentEmail) {
                    setTimeout(() => loadConversation(currentEmail), 1000);
                }
            } else {
                showNotification(`❌ ${data.message || data.error || 'Failed to send email'}`, 'error');
            }
        } catch (error) {
            console.error('Error sending email:', error);
            showNotification(`❌ Error sending email: ${error.message}`, 'error');
        }
    }
    </script>
</body>
</html>