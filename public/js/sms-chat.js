    let currentUserId = '';
    let currentName = '';
    let currentPhone = '';
    let currentUserPhone = '{{ Auth::user()->cp_number ?? "" }}';

    document.addEventListener('DOMContentLoaded', function() {
        initializeSidebar();
        initializeSMSForm();
        
        // Auto-check delivery status every 30 seconds for sent messages
        setInterval(checkDeliveryStatus, 30000);
    });

    function initializeSidebar() {
        // Toggle sidebar
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('wrapper').classList.toggle('toggled');
        });
        
        // Initialize section dropdowns
        const usersSectionExpanded = localStorage.getItem('sms-users-section-expanded') === 'true';
        const conversationsSectionExpanded = localStorage.getItem('sms-conversations-section-expanded') === 'true';
        
        setSectionState('users-section', usersSectionExpanded);
        setSectionState('conversations-section', conversationsSectionExpanded);
    }

    function initializeSMSForm() {
        // Character count
        const messageInput = document.getElementById('sms-message');
        const charCount = document.getElementById('char-count');
        
        if (messageInput) {
            messageInput.addEventListener('input', function() {
                const length = this.value.length;
                charCount.textContent = `${length}/160`;
                
                charCount.className = 'character-count';
                if (length > 140) {
                    charCount.classList.add('warning');
                }
                if (length > 155) {
                    charCount.classList.add('danger');
                }
            });
        }

        // Cancel button
        document.getElementById('cancel-sms').addEventListener('click', function() {
            document.getElementById('compose-form').style.display = 'none';
            document.getElementById('sms-message').value = '';
            document.getElementById('char-count').textContent = '0/160';
            document.getElementById('char-count').className = 'character-count';
        });

        // Form submission
        document.getElementById('sms-form').addEventListener('submit', function(e) {
            e.preventDefault();
            sendSms();
        });
    }

    function toggleSection(sectionId) {
        const dropdown = document.querySelector(`.section-dropdown[onclick*="${sectionId}"]`);
        const content = document.getElementById(`${sectionId}-content`);
        
        if (!dropdown || !content) return;
        
        const isCurrentlyExpanded = dropdown.classList.contains('expanded');
        
        if (isCurrentlyExpanded) {
            dropdown.classList.remove('expanded');
            dropdown.classList.add('collapsed');
            content.classList.remove('expanded');
            content.classList.add('collapsed');
        } else {
            dropdown.classList.remove('collapsed');
            dropdown.classList.add('expanded');
            content.classList.remove('collapsed');
            content.classList.add('expanded');
        }
        
        localStorage.setItem(`sms-${sectionId}-expanded`, !isCurrentlyExpanded);
    }

    function setSectionState(sectionId, expanded) {
        const dropdown = document.querySelector(`.section-dropdown[onclick*="${sectionId}"]`);
        const content = document.getElementById(`${sectionId}-content`);
        
        if (!dropdown || !content) return;
        
        if (expanded) {
            dropdown.classList.remove('collapsed');
            dropdown.classList.add('expanded');
            content.classList.remove('collapsed');
            content.classList.add('expanded');
        } else {
            dropdown.classList.remove('expanded');
            dropdown.classList.add('collapsed');
            content.classList.remove('expanded');
            content.classList.add('collapsed');
        }
    }

    function selectUser(userId, name, phone) {
        currentUserId = userId;
        currentName = name;
        currentPhone = phone;
        updateChatHeader(name, phone);
        loadConversation(userId);
        showComposeForm();
    }

    function updateChatHeader(name, phone) {
        document.getElementById('current-contact').textContent = name;
        document.getElementById('contact-phone').textContent = formatPhoneForDisplay(phone);
        document.getElementById('to-user-id').value = currentUserId;
    }

    function showComposeForm() {
        const composeForm = document.getElementById('compose-form');
        composeForm.style.display = 'block';
        document.getElementById('sms-message').focus();
    }

    async function loadConversation(userId) {
        if (!userId) {
            console.log('No user selected');
            return;
        }
        
        showLoading(true);
        
        try {
            const response = await fetch(`/admin/sms-conversation/${userId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.status === 'success') {
                displayMessages(data);
            } else {
                throw new Error(data.message || 'Failed to load conversation');
            }
            
        } catch (error) {
            console.error('Error loading conversation:', error);
            showNotification(`❌ Error loading conversation: ${error.message}`, 'error');
            
            const container = document.getElementById('messages-container');
            container.innerHTML = `
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <p>Error loading messages. Please try again.</p>
                    <button onclick="loadConversation('${userId}')" style="padding: 5px 10px; background: #007bff; color: white; border: none; border-radius: 4px; margin-top: 10px;">
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
        
        if (data.status !== 'success' || !data.conversation || data.conversation.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <p><i class="fas fa-comment-slash fa-2x mb-3"></i></p>
                    <p>No SMS messages yet. Start a conversation!</p>
                    <small>Type your message below and click Send SMS</small>
                </div>
            `;
            return;
        }

        container.innerHTML = '';
        
        data.conversation.forEach((msg) => {
            const messageDiv = createMessageElement(msg);
            container.appendChild(messageDiv);
        });
        
        container.scrollTop = container.scrollHeight;
    }

    function createMessageElement(msg) {
        const messageDiv = document.createElement('div');
        
        const messageType = msg.is_incoming ? 'incoming' : 'outgoing';
        const senderName = msg.is_incoming ? msg.sender_name : 'You';
        
        messageDiv.className = `message ${messageType}`;
        messageDiv.setAttribute('data-message-id', msg.id);
        
        messageDiv.innerHTML = `
            <div class="message-header">
                <span class="sender-name">${escapeHtml(senderName)}</span>
                <span class="timestamp">${msg.created_at_formatted}</span>
            </div>
            <div class="message-text">${escapeHtml(msg.message)}</div>
            <div class="message-footer">
                <span class="message-status">${msg.status}</span>
                <span class="message-phone">
                    ${msg.is_incoming ? 
                        `From: ${msg.formatted_phone}` : 
                        `To: ${msg.formatted_phone}`}
                </span>
            </div>
        `;
        
        return messageDiv;
    }

    async function sendSms() {
    const message = document.getElementById('sms-message').value.trim();
    
    if (!message) {
        showNotification('Please enter a message', 'warning');
        return;
    }
    
    if (!currentUserId) {
        showNotification('Please select a contact first', 'warning');
        return;
    }
    
    console.log('Sending SMS to user ID:', currentUserId);
    console.log('Message:', message);
    
    const formData = new FormData();
    formData.append('to_user_id', currentUserId);
    formData.append('message', message);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    try {
        showNotification('Sending SMS...', 'info');
        
        const response = await fetch('/admin/sms-send', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        console.log('Response status:', response.status);
        
        const responseText = await response.text();
        console.log('Response text:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            showNotification('❌ Server returned invalid response', 'error');
            return;
        }

        if (data.status === 'success') {
            showNotification('✅ SMS sent successfully!', 'success');
            document.getElementById('sms-message').value = '';
            document.getElementById('char-count').textContent = '0/160';
            document.getElementById('char-count').className = 'character-count';
            
            // Reload conversation
            setTimeout(() => loadConversation(currentUserId), 1000);
        } else {
            showNotification(`❌ ${data.message || 'Failed to send SMS'}`, 'error');
            console.error('API Error:', data);
        }
    } catch (error) {
        console.error('Network Error:', error);
        showNotification(`❌ Network error: ${error.message}`, 'error');
    }
}
    async function checkDeliveryStatus() {
        // Get all sent messages and check their status
        const sentMessages = document.querySelectorAll('.message.outgoing[data-message-id]');
        
        for (const msgElement of sentMessages) {
            const messageId = msgElement.getAttribute('data-message-id');
            
            try {
                const response = await fetch(`/admin/sms-status/${messageId}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.status === 'success') {
                        const statusElement = msgElement.querySelector('.message-status');
                        if (statusElement) {
                            statusElement.textContent = data.delivery_status;
                        }
                    }
                }
            } catch (error) {
                console.error('Error checking status:', error);
            }
        }
    }

    function startNewSms() {
        // This could open a modal to select a user or clear current selection
        showNotification('Please select a user from the sidebar to send an SMS', 'info');
    }

    function showLoading(show) {
        const container = document.getElementById('messages-container');
        if (show && container) {
            container.innerHTML = `
                <div style="text-align: center; padding: 20px; color: #6c757d;">
                    <div class="spinner-border spinner-border-sm" role="status"></div>
                    Loading messages...
                </div>
            `;
        }
    }

    function showNotification(message, type = 'info') {
        const alertClass = type === 'error' ? 'danger' : type;
        const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : type === 'warning' ? '⚠️' : 'ℹ️';
        
        const notification = document.createElement('div');
        notification.className = `alert alert-${alertClass} alert-dismissible fade show`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        `;
        notification.innerHTML = `
            ${icon} ${message}
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

    function formatPhoneForDisplay(phone) {
        if (!phone) return '';
        
        // Remove non-numeric characters
        phone = phone.replace(/\D/g, '');
        
        if (phone.length === 10) {
            return '(' + phone.substring(0, 3) + ') ' + phone.substring(3, 6) + '-' + phone.substring(6);
        } else if (phone.length === 11 && phone.startsWith('0')) {
            return '(' + phone.substring(1, 4) + ') ' + phone.substring(4, 7) + '-' + phone.substring(7);
        } else if (phone.length === 12 && phone.startsWith('63')) {
            return '+63 ' + phone.substring(2, 5) + ' ' + phone.substring(5, 8) + ' ' + phone.substring(8);
        }
        
        return phone;
    }
///////
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

function formatPhoneForDisplay(phone) {
    if (!phone) return '';
    
    // Remove non-numeric characters
    phone = phone.replace(/\D/g, '');
    
    if (phone.length === 10) {
        return '(' + phone.substring(0, 3) + ') ' + phone.substring(3, 6) + '-' + phone.substring(6);
    } else if (phone.length === 11 && phone.startsWith('0')) {
        return '(' + phone.substring(1, 4) + ') ' + phone.substring(4, 7) + '-' + phone.substring(7);
    } else if (phone.length === 12 && phone.startsWith('63')) {
        return '+63 ' + phone.substring(2, 5) + ' ' + phone.substring(5, 8) + ' ' + phone.substring(8);
    }
    
    return phone;
}

// Optional: Add keyboard shortcut (Ctrl+Q) for logout
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'q') {
        e.preventDefault();
        // Use Bootstrap's modal directly
        const logoutModal = new bootstrap.Modal(document.getElementById('logoutConfirmationModal'));
        logoutModal.show();
    }
});