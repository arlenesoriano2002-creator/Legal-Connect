// System Chat - LegalConnect
// External JS file for admin system chat functionality

let currentConversationId = null;
let typingTimeout = null;
let adminId = null;
let chatConfig = {};
let conversationPolling = null;
let messagePolling = null;
let lastMessageId = 0;
const UPDATE_THROTTLE = 1000;
function ensureScrollableMessages() {
    const messagesContainer = document.getElementById('messages-container');
    if (!messagesContainer) return;
    
    // Force scrollable container
    messagesContainer.style.overflowY = 'auto';
    messagesContainer.style.overflowX = 'hidden';
    messagesContainer.style.flex = '1 1 auto';
    messagesContainer.style.minHeight = '0';
    messagesContainer.style.display = 'flex';
    messagesContainer.style.flexDirection = 'column';
    
    // Calculate available height
    const chatHeader = document.querySelector('.chat-header');
    const messageInputArea = document.querySelector('.message-input-area');
    const chatMain = document.querySelector('.chat-main');
    
    if (chatHeader && messageInputArea && chatMain) {
        const headerHeight = chatHeader.offsetHeight;
        const inputHeight = messageInputArea.offsetHeight;
        const mainHeight = chatMain.offsetHeight;
        
        const messagesHeight = mainHeight - headerHeight - inputHeight;
        messagesContainer.style.height = messagesHeight + 'px';
        messagesContainer.style.maxHeight = messagesHeight + 'px';
    }
    
    // Ensure scroll to bottom works
    scrollToBottom();
}

// Call this function when DOM is loaded and when window resizes
document.addEventListener('DOMContentLoaded', function() {
    ensureScrollableMessages();
    
    // Also call on window resize
    window.addEventListener('resize', ensureScrollableMessages);
    
    // Call after loading messages
    setTimeout(ensureScrollableMessages, 500);
});

// Update the scrollToBottom function to work better
function scrollToBottom() {
    const container = document.getElementById('messages-container');
    if (container) {
        // Small delay to ensure DOM is updated
        setTimeout(() => {
            container.scrollTop = container.scrollHeight;
        }, 100);
        
        // Also try with a second delay for good measure
        setTimeout(() => {
            container.scrollTop = container.scrollHeight;
        }, 300);
    }
}

// Call ensureScrollableMessages when selecting a conversation
async function selectConversation(conversationId, clientId, clientName, clientEmail) {
    try {
        // ... existing code ...
        
        // Load messages
        await loadMessages(conversationId);
        
        // Ensure messages container is scrollable
        setTimeout(ensureScrollableMessages, 100);
        
        // ... rest of existing code ...
    } catch (error) {
        console.error('Error selecting conversation:', error);
        showNotification('Failed to load conversation: ' + error.message, 'danger');
    }
}
function startConversationPolling() {
    // Stop existing polling if any
    if (conversationPolling) {
        clearInterval(conversationPolling);
    }
    
    // Poll for conversation updates every 3 seconds
    conversationPolling = setInterval(() => {
        if (!document.hidden) { // Only poll when tab is active
            updateConversations();
        }
    }, 3000);
    
    console.log('Started conversation polling every 3 seconds');
}
function stopMessagePolling() {
    if (messagePolling) {
        clearInterval(messagePolling);
        messagePolling = null;
        console.log('Stopped message polling');
    }
}
function startConversationPolling() {
    // Stop existing polling if any
    if (conversationPolling) {
        clearInterval(conversationPolling);
    }
    
    // Poll for conversation updates every 3 seconds
    conversationPolling = setInterval(() => {
        if (!document.hidden) { // Only poll when tab is active
            updateConversations();
        }
    }, 3000);
    
    console.log('Started conversation polling every 3 seconds');
}

function startMessagePolling(conversationId) {
    // Stop existing message polling if any
    if (messagePolling) {
        clearInterval(messagePolling);
    }
    
    // Get last message ID for this conversation
    const messagesContainer = document.getElementById('messages-container');
    if (messagesContainer) {
        const lastMessage = messagesContainer.querySelector('.message:last-child');
        if (lastMessage && lastMessage.dataset.messageId) {
            lastMessageId = parseInt(lastMessage.dataset.messageId);
        } else {
            lastMessageId = 0;
        }
    }
    
    // Poll for new messages every 2 seconds
    messagePolling = setInterval(async () => {
        if (!document.hidden && currentConversationId === conversationId) {
            await pollNewMessages(conversationId);
        }
    }, 2000);
    
    console.log(`Started message polling for conversation ${conversationId} every 2 seconds`);
}
async function updateConversations() {
    const now = Date.now();
    
    // Throttle updates to prevent too frequent requests
    if (now - lastUpdateTime < UPDATE_THROTTLE) {
        return;
    }
    
    lastUpdateTime = now;
    
    try {
        const response = await fetch(chatConfig.routes.conversations, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            
            if (data.success) {
                updateConversationListIfNeeded(data.conversations || []);
                
                // Always update clients list
                if (data.all_clients) {
                    displayAllClients(data.all_clients);
                }
            }
        }
    } catch (error) {
        console.log('Conversation update error:', error);
    }
}
function updateConversationListIfNeeded(newConversations) {
    const container = document.getElementById('conversations-list');
    if (!container) return;
    
    // Create a map of current conversations
    const currentMap = {};
    document.querySelectorAll('.conversation-item').forEach(item => {
        const id = item.getAttribute('data-conversation-id');
        currentMap[id] = {
            element: item,
            lastMessage: item.querySelector('.last-message')?.textContent || '',
            unread: item.classList.contains('unread')
        };
    });
    
    // Check if we need to update
    let needsUpdate = false;
    
    // Check for new conversations
    if (newConversations.length !== Object.keys(currentMap).length) {
        needsUpdate = true;
    } else {
        // Check for changes in existing conversations
        for (const conv of newConversations) {
            const current = currentMap[conv.id];
            const lastMessage = conv.messages && conv.messages.length > 0 ? conv.messages[0] : null;
            const lastMessageText = lastMessage ? 
                (lastMessage.sender_id == adminId ? 'You: ' : '') + truncateText(escapeHtml(lastMessage.message || ''), 30) : 
                'No messages yet';
            const hasUnread = (conv.unread_count || 0) > 0;
            
            if (!current || 
                current.lastMessage !== lastMessageText || 
                current.unread !== hasUnread) {
                needsUpdate = true;
                break;
            }
        }
    }
    
    if (needsUpdate) {
        displayConversations(newConversations);
    }
}
function shouldUpdateConversations(newConversations) {
    // Check if any conversation has unread messages
    const currentItems = document.querySelectorAll('.conversation-item');
    
    for (let i = 0; i < newConversations.length; i++) {
        const conv = newConversations[i];
        const item = document.querySelector(`.conversation-item[data-conversation-id="${conv.id}"]`);
        
        if (!item) return true; // New conversation
        
        const currentUnread = item.classList.contains('unread');
        const newUnread = (conv.unread_count || 0) > 0;
        
        if (currentUnread !== newUnread) return true;
        
        // Check if last message changed
        const lastMsgElement = item.querySelector('.last-message');
        const lastMsgTime = item.querySelector('small');
        const newLastMessage = conv.messages && conv.messages.length > 0 ? conv.messages[0] : null;
        
        if (lastMsgElement && newLastMessage) {
            const currentText = lastMsgElement.textContent.trim();
            const newText = (newLastMessage.sender_id == adminId ? 'You: ' : '') + 
                          truncateText(escapeHtml(newLastMessage.message || ''), 30);
            
            if (currentText !== newText) return true;
        }
    }
    
    return false;
}
async function pollNewMessages(conversationId) {
    try {
        const url = chatConfig.routes.messages.replace(':conversationId', conversationId);
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            
            if (data.success && data.messages && data.messages.length > 0) {
                // Find the latest message ID
                const latestMessageId = Math.max(...data.messages.map(m => m.id));
                
                // Only update if there are new messages
                if (latestMessageId > lastMessageId) {
                    // Update last message ID
                    lastMessageId = latestMessageId;
                    
                    // Get messages that are newer than what we have
                    const newMessages = data.messages.filter(msg => msg.id > lastMessageId);
                    
                    if (newMessages.length > 0) {
                        // Append new messages to the view
                        newMessages.forEach(message => {
                            appendMessage(message);
                            
                            // Mark as read if it's from client
                            if (message.sender_id !== adminId) {
                                markAsRead(message.id);
                            }
                        });
                        
                        scrollToBottom();
                        
                        // Update conversation in sidebar
                        if (newMessages.length > 0) {
                            const lastMessage = newMessages[newMessages.length - 1];
                            updateConversationInList(conversationId, lastMessage);
                        }
                    }
                }
            }
        }
    } catch (error) {
        console.log('Message polling error:', error);
    }
}

async function pollMessages(conversationId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || chatConfig.csrfToken;
        
        const response = await fetch(chatConfig.routes.pollMessages, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                conversation_id: conversationId,
                last_message_id: lastMessageId
            })
        });
        
        if (response.ok) {
            const data = await response.json();
            
            if (data.success && data.messages && data.messages.length > 0) {
                // Update last message ID
                lastMessageId = data.last_message_id;
                
                // Append new messages
                data.messages.forEach(message => {
                    appendMessage(message);
                });
                
                // Scroll to bottom
                scrollToBottom();
                
                // Mark as read
                data.messages.forEach(message => {
                    if (message.sender_id !== adminId) {
                        markAsRead(message.id);
                    }
                });
            }
        }
    } catch (error) {
        console.log('Polling error:', error);
    }
}
// Initialize chat when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing chat...');
    
    // Initialize sidebar toggle
    const menuToggle = document.getElementById('menu-toggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            const wrapper = document.getElementById('wrapper');
            if (wrapper) wrapper.classList.toggle('toggled');
        });
    }

    // Load configuration from global variable
    if (typeof window.chatConfig !== 'undefined') {
        chatConfig = window.chatConfig;
        adminId = chatConfig.adminId;
        
        // Load conversations and clients
        loadConversations();
        
        // Start polling for conversation updates
        startConversationPolling();
    } else {
        console.error('chatConfig is not defined');
        showNotification('Failed to initialize chat: Configuration missing', 'danger');
        return;
    }

    // Initialize form submission
    const messageForm = document.getElementById('message-form');
    if (messageForm) {
        messageForm.addEventListener('submit', sendMessage);
    }

    // Initialize file input
    const fileInput = document.getElementById('file-input');
    if (fileInput) {
        fileInput.addEventListener('change', handleFileSelect);
    }

    // Initialize search
    const searchInput = document.getElementById('search-clients');
    if (searchInput) {
        searchInput.addEventListener('input', searchClients);
    }

    // Initialize typing indicator
    const messageInput = document.getElementById('message-input');
    if (messageInput) {
        messageInput.addEventListener('input', handleTyping);
    }

    // Make sure input area is visible
    ensureInputAreaVisible();
    
    // Load initial data if we have a conversation ID in URL or localStorage
    const urlParams = new URLSearchParams(window.location.search);
    const conversationId = urlParams.get('conversation') || localStorage.getItem('lastConversationId');
    if (conversationId) {
        // We'll need to load the conversation from the list
        // This will be handled when conversations load
        setTimeout(() => {
            const conversationItem = document.querySelector(`[data-conversation-id="${conversationId}"]`);
            if (conversationItem) {
                conversationItem.click();
            }
        }, 1000);
    }
});

function ensureInputAreaVisible() {
    const inputArea = document.querySelector('.message-input-area');
    if (inputArea) {
        inputArea.style.display = 'block';
        inputArea.style.visibility = 'visible';
        inputArea.style.opacity = '1';
        inputArea.style.position = 'sticky';
        inputArea.style.bottom = '0';
        inputArea.style.zIndex = '1000';
    }
}

function initializePusher() {
    if (!chatConfig.pusherKey || !chatConfig.pusherCluster) {
        console.error('Pusher configuration missing');
        return;
    }

    try {
        pusher = new Pusher(chatConfig.pusherKey, {
            cluster: chatConfig.pusherCluster,
            forceTLS: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            },
            enabledTransports: ['ws', 'wss'] // Force WebSocket for real-time
        });

        console.log('Pusher initialized for admin ID:', adminId);

        // Subscribe to admin channel for all admin-related events
        const adminChannel = pusher.subscribe('private-admin.' + adminId);
        
        // Listen for new conversation created
        adminChannel.bind('App\\Events\\ChatMessageSent', function(data) {
            console.log('ChatMessageSent event received:', data);
            handleRealTimeMessage(data);
        });

        adminChannel.bind('App\\Events\\ChatTyping', function(data) {
            console.log('ChatTyping event received:', data);
            if (data.conversation_id === currentConversationId && data.user_id !== adminId) {
                showTypingIndicator(data.user_name || 'Client');
            }
        });

        adminChannel.bind('conversation.created', function(data) {
            console.log('New conversation created:', data);
            loadConversations();
        });

        // Bind to Pusher connection state changes
        pusher.connection.bind('state_change', function(states) {
            console.log('Pusher connection state changed:', states.current);
        });

        pusher.connection.bind('connected', function() {
            console.log('Pusher connected successfully');
        });

        pusher.connection.bind('error', function(err) {
            console.error('Pusher connection error:', err);
        });

    } catch (error) {
        console.error('Failed to initialize Pusher:', error);
        showNotification('Real-time features may not work. Please refresh the page.', 'warning');
    }
}

function handleRealTimeMessage(data) {
    console.log('Processing real-time message:', data);
    
    if (!data.message) {
        console.error('No message data in event');
        return;
    }

    const message = data.message;
    const conversationId = message.conversation_id;
    
    // Update conversation in list
    updateConversationInList(conversationId, message);
    
    // If this conversation is currently open, append the message
    if (currentConversationId === conversationId) {
        appendMessage(message);
        scrollToBottom();
        
        // Mark as read if message is from client
        if (message.sender_id !== adminId) {
            markAsRead(message.id);
        }
    } else {
        // Mark conversation as unread in the list
        const conversationItem = document.querySelector(`.conversation-item[data-conversation-id="${conversationId}"]`);
        if (conversationItem) {
            conversationItem.classList.add('unread');
            
            // Update unread count
            let unreadCount = conversationItem.querySelector('.unread-count');
            if (!unreadCount) {
                unreadCount = document.createElement('div');
                unreadCount.className = 'unread-count';
                const flexDiv = conversationItem.querySelector('.d-flex');
                if (flexDiv) {
                    flexDiv.appendChild(unreadCount);
                }
            }
            const currentCount = parseInt(unreadCount.textContent) || 0;
            unreadCount.textContent = currentCount + 1;
        }
    }
}

async function loadConversations() {
    try {
        console.log('Loading conversations...');
        const response = await fetch(chatConfig.routes.conversations, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            if (data.conversations && data.conversations.length > 0) {
                displayConversations(data.conversations);
                
                // If we have a current conversation but it's not in the list, clear it
                if (currentConversationId && !data.conversations.find(c => c.id == currentConversationId)) {
                    clearCurrentConversation();
                    stopMessagePolling();
                }
            } else {
                displayConversations([]);
                clearCurrentConversation();
                stopMessagePolling();
            }
            
            if (data.all_clients && data.all_clients.length > 0) {
                displayAllClients(data.all_clients);
            } else {
                displayAllClients([]);
            }
            
            // Initialize dropdowns with counts
            const convCount = data.conversations ? data.conversations.length : 0;
            const clientCount = data.all_clients ? data.all_clients.length : 0;
            initializeDropdowns(convCount, clientCount);
        } else {
            console.error('Failed to load conversations:', data.message);
            showNotification('Failed to load conversations: ' + data.message, 'danger');
        }
    } catch (error) {
        console.error('Error loading conversations:', error);
        showNotification('Failed to load conversations and clients', 'danger');
        
        // Show empty states
        displayConversations([]);
        displayAllClients([]);
        initializeDropdowns(0, 0);
    }
}


function clearCurrentConversation() {
    currentConversationId = null;
    lastMessageId = 0;
    document.getElementById('current-client-name').textContent = 'Select a conversation';
    document.getElementById('current-client-email').textContent = '';
    document.getElementById('conversation-id').value = '';
    document.getElementById('client-id').value = '';
    
    const messagesContainer = document.getElementById('messages-container');
    if (messagesContainer) {
        const statusElement = document.getElementById('chat-status');
        if (statusElement) {
            statusElement.innerHTML = 'Select a conversation from the sidebar to start chatting';
            statusElement.style.display = 'flex';
        }
        
        // Clear messages
        const existingMessages = messagesContainer.querySelectorAll('.message');
        existingMessages.forEach(msg => msg.remove());
    }
    
    stopMessagePolling();
}

function displayConversations(conversations) {
    const container = document.getElementById('conversations-list');
    if (!container) return;
    
    if (!conversations || conversations.length === 0) {
        container.innerHTML = '<div class="no-conversations">No active conversations yet</div>';
        return;
    }

    let html = '';
    conversations.forEach(conv => {
        const lastMessage = conv.messages && conv.messages.length > 0 ? conv.messages[0] : null;
        const unreadCount = conv.unread_count || 0;
        const clientName = conv.client ? conv.client.name : 'Unknown Client';
        const clientEmail = conv.client ? conv.client.email : '';
        
        html += `
            <div class="conversation-item ${currentConversationId == conv.id ? 'active' : ''} ${unreadCount > 0 ? 'unread' : ''}" 
                  data-conversation-id="${conv.id}"
                  data-client-id="${conv.client_id}"
                  data-client-name="${clientName}"
                  data-client-email="${clientEmail}">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="client-name">${escapeHtml(clientName)}</div>
                    ${unreadCount > 0 ? `<div class="unread-count">${unreadCount}</div>` : ''}
                </div>
                <div class="last-message">
                    ${lastMessage ? (lastMessage.sender_id == adminId ? 'You: ' : '') + truncateText(escapeHtml(lastMessage.message || ''), 30) : 'No messages yet'}
                </div>
                ${lastMessage ? `<small>${formatTime(lastMessage.created_at)}</small>` : ''}
            </div>
        `;
    });

    container.innerHTML = html;
    
    // Add click event listeners to conversation items
    container.querySelectorAll('.conversation-item').forEach(item => {
        item.addEventListener('click', function() {
            const conversationId = this.getAttribute('data-conversation-id');
            const clientId = this.getAttribute('data-client-id');
            const clientName = this.getAttribute('data-client-name');
            const clientEmail = this.getAttribute('data-client-email');
            
            selectConversation(conversationId, clientId, clientName, clientEmail);
            
            // Highlight selected conversation
            document.querySelectorAll('.conversation-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            
            // Store last conversation in localStorage
            localStorage.setItem('lastConversationId', conversationId);
        });
    });
}

async function selectConversation(conversationId, clientId, clientName, clientEmail) {
    try {
        console.log('Selecting conversation:', conversationId);
        
        // Update current conversation
        currentConversationId = conversationId;
        
        // Update chat header
        document.getElementById('current-client-name').textContent = clientName;
        document.getElementById('current-client-email').textContent = clientEmail;
        
        // Set hidden inputs
        document.getElementById('conversation-id').value = conversationId;
        document.getElementById('client-id').value = clientId;
        
        // Start polling for messages in this conversation
        startMessagePolling(conversationId);
        
        // Load messages
        await loadMessages(conversationId);
        
        // Mark as read
        markConversationAsRead(conversationId);
        
        // Ensure input area is visible
        ensureInputAreaVisible();
        
        // Scroll to bottom
        scrollToBottom();
        
        // Remove unread status from this conversation
        const conversationItem = document.querySelector(`.conversation-item[data-conversation-id="${conversationId}"]`);
        if (conversationItem) {
            conversationItem.classList.remove('unread');
            const unreadCount = conversationItem.querySelector('.unread-count');
            if (unreadCount) {
                unreadCount.remove();
            }
        }
        
    } catch (error) {
        console.error('Error selecting conversation:', error);
        showNotification('Failed to load conversation: ' + error.message, 'danger');
    }
}

async function selectNewClient(clientId, clientName, clientEmail) {
    try {
        console.log('Starting conversation with client:', clientId, clientName, clientEmail);
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || chatConfig.csrfToken;
        
        const response = await fetch(chatConfig.routes.startConversation, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                client_id: clientId,
                message: 'Hello! I would like to start a conversation with you.'
            })
        });
        
        const data = await response.json();
        console.log('Start conversation response:', data);
        
        if (data.success) {
            // Success - load the conversation
            await loadConversations(); // Refresh the conversation list
            
            // If we got a conversation back, select it
            if (data.conversation) {
                selectConversation(data.conversation.id, data.conversation.client_id, clientName, clientEmail);
            } else if (data.id) {
                selectConversation(data.id, clientId, clientName, clientEmail);
            }
            
            // Make typing bar visible
            ensureInputAreaVisible();
            
            showNotification('Conversation started with ' + clientName, 'success');
        } else {
            throw new Error(data.message || 'Failed to start conversation');
        }
        
    } catch (error) {
        console.error('Error starting new conversation:', error);
        showNotification('Failed to start conversation: ' + error.message, 'danger');
    }
}

function joinConversationChannel(conversationId) {
    if (!pusher) {
        console.error('Pusher not initialized');
        return;
    }
    
    // Unsubscribe from previous channel if exists
    if (currentChannel) {
        console.log('Unsubscribing from channel:', currentChannel.name);
        pusher.unsubscribe(currentChannel.name);
    }
    
    try {
        // Subscribe to conversation channel
        currentChannel = pusher.subscribe('private-conversation.' + conversationId);
        console.log('Subscribed to conversation channel:', conversationId);
        
        currentChannel.bind('App\\Events\\ChatMessageSent', function(data) {
            console.log('Message received in conversation channel:', data);
            if (data.message && data.message.conversation_id == conversationId) {
                appendMessage(data.message);
                scrollToBottom();
                
                // Mark as read if message is from client
                if (data.message.sender_id !== adminId) {
                    markAsRead(data.message.id);
                }
            }
        });

        currentChannel.bind('App\\Events\\ChatTyping', function(data) {
            console.log('Typing event in conversation channel:', data);
            if (data.user_id !== adminId) {
                showTypingIndicator(data.user_name || 'Client');
            }
        });

        currentChannel.bind('pusher:subscription_succeeded', function() {
            console.log('Successfully subscribed to conversation channel:', conversationId);
        });

        currentChannel.bind('pusher:subscription_error', function(status) {
            console.error('Failed to subscribe to conversation channel:', status);
        });
    } catch (error) {
        console.error('Error joining conversation channel:', error);
    }
}

async function loadMessages(conversationId) {
    const container = document.getElementById('messages-container');
    if (!container) return;
    
    try {
        // Show loading
        const statusElement = document.getElementById('chat-status');
        if (statusElement) {
            statusElement.innerHTML = '<div class="loading-dots"><span></span><span></span><span></span></div>';
            statusElement.style.display = 'flex';
        }
        
        const url = chatConfig.routes.messages.replace(':conversationId', conversationId);
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            displayMessages(data.messages);
            
            // Set last message ID
            if (data.messages && data.messages.length > 0) {
                lastMessageId = Math.max(...data.messages.map(m => m.id));
            } else {
                lastMessageId = 0;
            }
        } else {
            throw new Error(data.message || 'Failed to load messages');
        }
    } catch (error) {
        console.error('Error loading messages:', error);
        const statusElement = document.getElementById('chat-status');
        if (statusElement) {
            statusElement.innerHTML = 'Error loading messages';
            statusElement.style.display = 'flex';
        }
    }
}

function displayMessages(messages) {
    const container = document.getElementById('messages-container');
    const statusElement = document.getElementById('chat-status');
    if (!container || !statusElement) return;
    
    // Clear existing messages
    const existingMessages = container.querySelectorAll('.message');
    existingMessages.forEach(msg => msg.remove());
    
    if (!messages || messages.length === 0) {
        // Show status message
        statusElement.innerHTML = 'No messages yet. Start the conversation!';
        statusElement.style.display = 'flex';
        return;
    }

    // Hide status
    statusElement.style.display = 'none';
    
    let html = '';
    messages.forEach(message => {
        const isOutgoing = message.sender_id == adminId;
        const messageClass = isOutgoing ? 'message-outgoing' : 'message-incoming';
        const senderName = isOutgoing ? 'You' : (message.sender?.name || 'Client');
        const downloadUrl = chatConfig.routes.downloadFile.replace(':messageId', message.id);
        
        html += `
            <div class="message ${messageClass}" data-message-id="${message.id}">
                ${!isOutgoing ? `<div class="message-sender">${escapeHtml(senderName)}</div>` : ''}
                <div class="message-bubble">
                    ${escapeHtml(message.message || '')}
                    ${message.message_type === 'file' ? `
                        <div class="file-message">
                            <div><i class="fas fa-file"></i> ${escapeHtml(message.file_name)}</div>
                            <a href="${downloadUrl}" class="file-download-btn" target="_blank">
                                <i class="fas fa-download"></i> Download (${formatFileSize(message.file_size)})
                            </a>
                        </div>
                    ` : ''}
                    <div class="message-time">${formatTime(message.created_at)}</div>
                </div>
            </div>
        `;
    });

    // Insert messages before the status element
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    while (tempDiv.firstChild) {
        container.insertBefore(tempDiv.firstChild, statusElement);
    }
    
    scrollToBottom();
}

async function sendMessage(e) {
    e.preventDefault();
    
    const messageInput = document.getElementById('message-input');
    const message = messageInput.value.trim();
    const conversationId = document.getElementById('conversation-id').value;
    const fileInput = document.getElementById('file-input');
    
    if (!conversationId) {
        showNotification('Please select a conversation first', 'warning');
        return;
    }

    if (!message && (!fileInput || fileInput.files.length === 0)) {
        return;
    }

    const formData = new FormData();
    if (message) {
        formData.append('message', message);
    }
    formData.append('conversation_id', conversationId);
    
    if (fileInput && fileInput.files.length > 0) {
        formData.append('file', fileInput.files[0]);
    }

    try {
        // Show sending indicator
        messageInput.disabled = true;
        const submitBtn = document.querySelector('#message-form button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            submitBtn.disabled = true;
        }
        
        const url = chatConfig.routes.sendMessage.replace(':conversationId', conversationId);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || chatConfig.csrfToken;
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await response.json();
        
        if (data.success) {
            // Clear input
            if (messageInput) messageInput.value = '';
            
            const filePreview = document.getElementById('file-preview');
            if (filePreview) filePreview.style.display = 'none';
            
            if (fileInput) fileInput.value = '';
            
            // Clear typing indicator
            clearTyping();
            
            // Immediately append the sent message to the UI
            if (data.message) {
                appendMessage(data.message);
                scrollToBottom();
                
                // Update conversation in sidebar
                updateConversationInList(conversationId, data.message);
                
                // Update last message ID
                lastMessageId = data.message.id;
            }
            
            showNotification('Message sent successfully', 'success');
        } else {
            throw new Error(data.message || 'Failed to send message');
        }
        
    } catch (error) {
        console.error('Error sending message:', error);
        showNotification('Failed to send message: ' + error.message, 'danger');
    } finally {
        // Re-enable inputs
        messageInput.disabled = false;
        const submitBtn = document.querySelector('#message-form button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            submitBtn.disabled = false;
        }
    }
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    const preview = document.getElementById('file-preview');
    if (!preview) return;
    
    preview.innerHTML = `
        <div class="d-flex align-items-center bg-light rounded p-2">
            <i class="fas fa-file text-primary me-2"></i>
            <div class="flex-grow-1">
                <div class="small fw-bold">${escapeHtml(file.name)}</div>
                <div class="small text-muted">${formatFileSize(file.size)}</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFile()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    preview.style.display = 'block';
}

function clearFile() {
    const fileInput = document.getElementById('file-input');
    const filePreview = document.getElementById('file-preview');
    
    if (fileInput) fileInput.value = '';
    if (filePreview) filePreview.style.display = 'none';
}

function handleTyping() {
    if (!currentConversationId) return;

    // Clear existing timeout
    if (typingTimeout) {
        clearTimeout(typingTimeout);
    }

    // Send typing event
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || chatConfig.csrfToken;
    
    fetch(chatConfig.routes.typing, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            conversation_id: currentConversationId,
            is_typing: true
        })
    }).catch(error => {
        console.log('Typing endpoint not available or error:', error);
    });

    // Set timeout to clear typing
    typingTimeout = setTimeout(() => {
        fetch(chatConfig.routes.typing, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                conversation_id: currentConversationId,
                is_typing: false
            })
        }).catch(error => {
            console.log('Typing endpoint not available or error:', error);
        });
    }, 2000); // Clear after 2 seconds of inactivity
}

function showTypingIndicator(clientName) {
    const indicator = document.getElementById('typing-indicator');
    if (!indicator) return;
    
    indicator.textContent = `${clientName} is typing...`;
    indicator.style.display = 'block';
    indicator.classList.add('animate__animated', 'animate__fadeIn');

    // Clear after 3 seconds
    setTimeout(() => {
        clearTyping();
    }, 3000);
}

function clearTyping() {
    const indicator = document.getElementById('typing-indicator');
    if (indicator) {
        indicator.classList.remove('animate__fadeIn');
        indicator.classList.add('animate__fadeOut');
        setTimeout(() => {
            indicator.style.display = 'none';
            indicator.classList.remove('animate__fadeOut');
        }, 300);
    }
}

function appendMessage(message) {
    const container = document.getElementById('messages-container');
    const statusElement = document.getElementById('chat-status');
    if (!container || !statusElement) return;
    
    // Hide status if showing
    if (statusElement.style.display !== 'none') {
        statusElement.style.display = 'none';
    }

    const isOutgoing = message.sender_id == adminId;
    const messageClass = isOutgoing ? 'message-outgoing' : 'message-incoming';
    const senderName = isOutgoing ? 'You' : (message.sender?.name || 'Client');
    const downloadUrl = chatConfig.routes.downloadFile.replace(':messageId', message.id);
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${messageClass} animate__animated animate__fadeInUp`;
    messageDiv.setAttribute('data-message-id', message.id);
    messageDiv.innerHTML = `
        ${!isOutgoing ? `<div class="message-sender">${escapeHtml(senderName)}</div>` : ''}
        <div class="message-bubble">
            ${escapeHtml(message.message)}
            ${message.message_type === 'file' ? `
                <div class="file-message">
                    <div><i class="fas fa-file"></i> ${escapeHtml(message.file_name)}</div>
                    <a href="${downloadUrl}" class="file-download-btn" target="_blank">
                        <i class="fas fa-download"></i> Download (${formatFileSize(message.file_size)})
                    </a>
                </div>
            ` : ''}
            <div class="message-time">${formatTime(message.created_at)}</div>
        </div>
    `;
    
    container.insertBefore(messageDiv, statusElement);
    scrollToBottom();
}
async function pollNewMessagesEfficient(conversationId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || chatConfig.csrfToken;
        
        const response = await fetch(chatConfig.routes.pollMessages, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                conversation_id: conversationId,
                last_message_id: lastMessageId
            })
        });
        
        if (response.ok) {
            const data = await response.json();
            
            if (data.success && data.messages && data.messages.length > 0) {
                // Update last message ID
                lastMessageId = data.last_message_id;
                
                // Append new messages
                data.messages.forEach(message => {
                    appendMessage(message);
                    
                    // Mark as read if it's from client
                    if (message.sender_id !== adminId) {
                        markAsRead(message.id);
                    }
                });
                
                scrollToBottom();
                
                // Update conversation in sidebar
                if (data.messages.length > 0) {
                    const lastMessage = data.messages[data.messages.length - 1];
                    updateConversationInList(conversationId, lastMessage);
                }
            }
        }
    } catch (error) {
        console.log('Message polling error:', error);
    }
}
function scrollToBottom() {
    const container = document.getElementById('messages-container');
    if (container) {
        setTimeout(() => {
            container.scrollTop = container.scrollHeight;
        }, 100);
    }
}

function markConversationAsRead(conversationId) {
    const url = chatConfig.routes.markConversationAsRead.replace(':conversationId', conversationId);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || chatConfig.csrfToken;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    }).catch(error => console.log('Error marking conversation as read:', error));
}

function markAsRead(messageId) {
    const url = chatConfig.routes.markMessageAsRead.replace(':messageId', messageId);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || chatConfig.csrfToken;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    }).catch(error => console.log('Error marking message as read:', error));
}

function searchClients(event) {
    const searchTerm = event.target.value.toLowerCase().trim();
    
    if (!searchTerm) {
        // Show all items
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.style.display = 'block';
        });
        document.querySelectorAll('.client-item').forEach(item => {
            item.style.display = 'block';
        });
        return;
    }
    
    // Search in conversations
    const conversationItems = document.querySelectorAll('.conversation-item');
    conversationItems.forEach(item => {
        const text = item.textContent.toLowerCase();
        const clientName = item.getAttribute('data-client-name')?.toLowerCase() || '';
        const clientEmail = item.getAttribute('data-client-email')?.toLowerCase() || '';
        
        const isVisible = text.includes(searchTerm) || 
                         clientName.includes(searchTerm) || 
                         clientEmail.includes(searchTerm);
        item.style.display = isVisible ? 'block' : 'none';
    });
    
    // Search in all clients
    const clientItems = document.querySelectorAll('.client-item');
    clientItems.forEach(item => {
        const text = item.textContent.toLowerCase();
        const clientName = item.getAttribute('data-client-name')?.toLowerCase() || '';
        const clientEmail = item.getAttribute('data-client-email')?.toLowerCase() || '';
        
        const isVisible = text.includes(searchTerm) || 
                         clientName.includes(searchTerm) || 
                         clientEmail.includes(searchTerm);
        item.style.display = isVisible ? 'block' : 'none';
    });
}

function refreshConversations() {
    loadConversations();
    showNotification('Conversations refreshed', 'success');
}

function showNotification(message, type) {
    // Remove existing notifications
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;';
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
        ${escapeHtml(message)}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(alert);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.classList.remove('show');
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 300);
        }
    }, 3000);
}

// Utility functions
function formatTime(timestamp) {
    if (!timestamp) return '';
    const date = new Date(timestamp);
    const now = new Date();
    
    // If today, show time only
    if (date.toDateString() === now.toDateString()) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    
    // If yesterday
    const yesterday = new Date(now);
    yesterday.setDate(yesterday.getDate() - 1);
    if (date.toDateString() === yesterday.toDateString()) {
        return 'Yesterday ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    
    // Otherwise show date and time
    return date.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ' ' + 
           date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function formatFileSize(bytes) {
    if (!bytes || bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function truncateText(text, maxLength) {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function updateConversationInList(conversationId, message) {
    const item = document.querySelector(`.conversation-item[data-conversation-id="${conversationId}"]`);
    if (item) {
        const lastMessage = item.querySelector('.last-message');
        const timeElement = item.querySelector('small');
        const unreadCount = item.querySelector('.unread-count');
        
        if (lastMessage) {
            const prefix = message.sender_id == adminId ? 'You: ' : '';
            lastMessage.textContent = prefix + truncateText(message.message, 30);
        }
        
        if (timeElement && message.created_at) {
            timeElement.textContent = formatTime(message.created_at);
        }
        
        // Add unread badge if message is from client and conversation is not active
        if (message.sender_id != adminId && currentConversationId != conversationId) {
            item.classList.add('unread');
            
            if (!unreadCount) {
                const newUnreadCount = document.createElement('div');
                newUnreadCount.className = 'unread-count';
                newUnreadCount.textContent = '1';
                const flexDiv = item.querySelector('.d-flex');
                if (flexDiv) {
                    flexDiv.appendChild(newUnreadCount);
                }
            } else {
                const currentCount = parseInt(unreadCount.textContent) || 0;
                unreadCount.textContent = currentCount + 1;
            }
        }
    }
}

function displayAllClients(clients) {
    const container = document.getElementById('all-clients-list');
    if (!container) return;
    
    if (!clients || clients.length === 0) {
        container.innerHTML = '<div class="no-clients">No client users found</div>';
        return;
    }

    let html = '';
    clients.forEach(client => {
        html += `
            <div class="client-item" 
                 data-client-id="${client.id}"
                 data-client-name="${escapeHtml(client.name || 'Unknown')}"
                 data-client-email="${escapeHtml(client.email || '')}">
                <div class="client-name-display">${escapeHtml(client.name || 'Unknown Client')}</div>
                <div class="client-email-display">${escapeHtml(client.email || 'No email')}</div>
                <span class="client-role-badge">${escapeHtml(client.role || 'Client')}</span>
            </div>
        `;
    });

    container.innerHTML = html;
    
    // Add click event listeners to client items
    container.querySelectorAll('.client-item').forEach(item => {
        item.addEventListener('click', function() {
            const clientId = this.getAttribute('data-client-id');
            const clientName = this.getAttribute('data-client-name');
            const clientEmail = this.getAttribute('data-client-email');
            
            selectNewClient(clientId, clientName, clientEmail);
            
            // Highlight selected client
            document.querySelectorAll('.client-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

function initializeDropdowns(conversationsCount, clientsCount) {
    console.log('Initializing dropdowns with counts:', conversationsCount, clientsCount);
    
    // Update conversation count badge
    const conversationsHeader = document.querySelector('.dropdown-header[data-bs-target="#activeConversationsCollapse"]');
    if (conversationsHeader) {
        let countBadge = conversationsHeader.querySelector('.conversation-count-badge');
        if (!countBadge) {
            countBadge = document.createElement('span');
            countBadge.className = 'conversation-count-badge ms-2';
            const dFlexDiv = conversationsHeader.querySelector('.d-flex div');
            if (dFlexDiv) dFlexDiv.appendChild(countBadge);
        }
        countBadge.textContent = conversationsCount;
        
        // Ensure collapsed state on load
        const collapseElement = document.getElementById('activeConversationsCollapse');
        if (collapseElement) {
            collapseElement.classList.remove('show');
            conversationsHeader.setAttribute('aria-expanded', 'false');
        }
    }
    
    // Update clients count badge
    const clientsHeader = document.querySelector('.dropdown-header[data-bs-target="#allClientsCollapse"]');
    if (clientsHeader) {
        let countBadge = clientsHeader.querySelector('.clients-count-badge');
        if (!countBadge) {
            countBadge = document.createElement('span');
            countBadge.className = 'clients-count-badge ms-2';
            const dFlexDiv = clientsHeader.querySelector('.d-flex div');
            if (dFlexDiv) dFlexDiv.appendChild(countBadge);
        }
        countBadge.textContent = clientsCount;
        
        // Ensure collapsed state on load
        const collapseElement = document.getElementById('allClientsCollapse');
        if (collapseElement) {
            collapseElement.classList.remove('show');
            clientsHeader.setAttribute('aria-expanded', 'false');
        }
    }
    
    // Initialize Bootstrap collapse if available
    if (typeof bootstrap !== 'undefined') {
        const collapseElements = document.querySelectorAll('.collapse');
        collapseElements.forEach(collapse => {
            new bootstrap.Collapse(collapse, {
                toggle: false
            });
        });
    }
}

// Reconnect Pusher if disconnected
function checkPusherConnection() {
    if (pusher && pusher.connection.state !== 'connected') {
        console.log('Pusher disconnected, reconnecting...');
        initializePusher();
    }
}

// Check connection every 30 seconds
setInterval(checkPusherConnection, 30000);

// Make functions available globally if needed
window.refreshConversations = refreshConversations;
window.clearFile = clearFile;
window.selectNewClient = selectNewClient;
window.checkPusherConnection = checkPusherConnection;