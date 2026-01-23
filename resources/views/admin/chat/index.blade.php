<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>System Chat | LegalConnect</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="{{ asset('css/system-chat.blade.css') }}">
    
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
               
                <a href="#messagesSubmenu" 
                class="list-group-item list-group-item-action {{ request()->is('email-chat') || request()->is('admin/system-chat') ? 'active' : '' }}"
                data-bs-toggle="collapse" 
                aria-expanded="{{ request()->is('email-chat') || request()->is('admin/system-chat') ? 'true' : 'false' }}">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu collapse {{ request()->is('email-chat') || request()->is('admin/system-chat') ? 'show' : '' }} list-group" id="messagesSubmenu">
                    <a href="{{ route('messages.email') }}" class="list-group-item list-group-item-action {{ request()->is('email-chat') ? 'active' : '' }}">
                        <i class="fas fa-envelope"></i>
                        <span>Email</span>
                    </a>
                    <a href="{{ route('messages.sms') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-sms"></i>
                        <span>SMS</span>
                    </a>
                    <a href="{{ route('admin.system-chat') }}" class="list-group-item list-group-item-action {{ request()->is('admin/system-chat') ? 'active' : '' }}">
                        <i class="fas fa-comments"></i>
                        <span>System Chatting</span>
                    </a>
                </div>
                <a href="{{ url('/practice-areas') }}" class="list-group-item list-group-item-action {{ request()->is('practice-areas') ? 'active' : '' }}">
                    <i class="fa-solid fa-suitcase"></i>
                    <span>Practice Areas</span>
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
                <button type="button" class="btn logout-btn" aria-label="Log out" onclick="showLogoutConfirmation()">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>

            <!-- Main Chat Container -->
            <div class="container-fluid mt-4">
                <div class="row chat-container">
                    <!-- Sidebar with Conversations -->
                    <div class="col-md-4 chat-sidebar">
                        <div class="chat-sidebar-inner">
                            <div class="chat-sidebar-header">
                                <h4 class="mb-3">System Chat</h4>
                                <div class="input-group mb-3">
                                    <input type="text" id="search-clients" class="form-control" placeholder="Search clients...">
                                    <button class="btn btn-light" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <button class="btn btn-light btn-sm w-100" onclick="refreshConversations()">
                                    <i class="fas fa-sync-alt"></i> Refresh Conversations
                                </button>
                            </div>
                            
                            <div class="chat-sidebar-content">
                                <!-- Dropdown for Active Conversations -->
                                <div class="dropdown-section">
                                    <div class="dropdown-header" data-bs-toggle="collapse" data-bs-target="#activeConversationsCollapse" 
                                        aria-expanded="true" aria-controls="activeConversationsCollapse">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-comments me-2"></i>
                                                <strong>Active Conversations</strong>
                                            </div>
                                            <div class="dropdown-indicator">
                                                <i class="fas fa-chevron-down"></i>
                                            </div>
                                        </div>
                                        <small class="text-light opacity-75">Click to expand/collapse</small>
                                    </div>
                                    
                                    <div class="collapse show" id="activeConversationsCollapse">
                                        <div class="dropdown-body">
                                            <div id="conversations-list" class="conversations-list">
                                                <div class="loading-dots">
                                                    <span></span>
                                                    <span></span>
                                                    <span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Dropdown for All Registered Clients -->
                                <div class="dropdown-section">
                                    <div class="dropdown-header" data-bs-toggle="collapse" data-bs-target="#allClientsCollapse" 
                                        aria-expanded="true" aria-controls="allClientsCollapse">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-users me-2"></i>
                                                <strong>All Registered Clients</strong>
                                            </div>
                                            <div class="dropdown-indicator">
                                                <i class="fas fa-chevron-down"></i>
                                            </div>
                                        </div>
                                        <small class="text-light opacity-75">Click to start conversation</small>
                                    </div>
                                    
                                    <div class="collapse show" id="allClientsCollapse">
                                        <div class="dropdown-body">
                                            <div id="all-clients-list" class="all-clients-list">
                                                <!-- Clients will be loaded here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Main Chat Area -->
                    <div class="col-md-8 p-0 d-flex flex-column chat-main">
                        <!-- Chat Header -->
                        <div class="chat-header">
                            <div>
                                <h5 id="current-client-name">Select a conversation</h5>
                                <small id="current-client-email"></small>
                            </div>
                            <div id="typing-indicator" class="typing-indicator" style="display: none;"></div>
                        </div>
                        
                        <!-- Messages Container -->
                        <div class="messages-container" id="messages-container">
                            <!-- Status message will be shown here -->
                            <div class="chat-status" id="chat-status">
                                Select a conversation from the sidebar to start chatting
                            </div>
                            <!-- Messages will be loaded here -->
                        </div>
                                            
                        <!-- Message Input Area (Always visible) -->
                         <div class="message-input-area">
                            <form id="message-form">
                                @csrf
                                <input type="hidden" id="conversation-id">
                                <input type="hidden" id="client-id">
                                
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('file-input').click()">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                    <input type="file" id="file-input" style="display: none;" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt">
                                    
                                    <input type="text" id="message-input" class="form-control" placeholder="Type your message..." autocomplete="off">
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                                
                                <div id="file-preview" class="mt-2" style="display: none;"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Modal for Logout Confirmation -->
<div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">
                    <i class="fas fa-sign-out-alt me-2"></i>Confirm Logout
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div style="font-size: 48px; color: #ffc107; margin-bottom: 15px;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h4 class="mb-3">Confirm Logout</h4>
                <p>Are you sure you want to log out?<br>You will be redirected to the login page.</p>
            </div>
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
    <!-- Pusher for real-time -->
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <script src="{{ asset('js/system-chat.js') }}"></script>
    <script>
window.chatConfig = {
    routes: {
        conversations: "{{ route('admin.chat.conversations') }}",
        startConversation: "{{ route('admin.chat.start') }}",
        sendMessage: "{{ route('admin.chat.send', ':conversationId') }}",
        messages: "{{ route('admin.chat.messages', ':conversationId') }}",
        markConversationAsRead: "{{ route('admin.chat.conversation.read', ':conversationId') }}",
        markMessageAsRead: "{{ route('chat.message.read', ':messageId') }}",
        pollMessages: "{{ route('admin.chat.poll') }}", // Add this line
        typing: "{{ route('admin.chat.typing') }}",
        downloadFile: "{{ route('admin.chat.messages.download', ':messageId') }}"
    },
    adminId: {{ Auth::id() }},
    csrfToken: "{{ csrf_token() }}"
};
</script>
</body>
</html> 