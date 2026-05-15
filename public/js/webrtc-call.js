/**
 * WebRTC Call Manager
 * Handles call initiation from messaging interface
 * Opens a new tab/window for the call session
 */

class WebRTCCallManager {
    constructor() {
        this.callWindow = null;
        this.currentAdminId = null;
        this.currentUserId = this.getCurrentUserId();
    }

    /**
     * Get current user ID from page context or localStorage
     */
    getCurrentUserId() {
        // Try to get from window.currentUser (set in Blade template)
        if (window.currentUser && window.currentUser.id) {
            return window.currentUser.id;
        }

        // Try to get from data attribute
        const userIdAttr = document.documentElement.getAttribute('data-user-id');
        if (userIdAttr) return parseInt(userIdAttr);

        // Try to get from localStorage
        const stored = localStorage.getItem('currentUserId');
        if (stored) return parseInt(stored);

        // Try to get from meta tag
        const metaUser = document.querySelector('meta[name="user-id"]');
        if (metaUser) return parseInt(metaUser.getAttribute('content'));

        // Try to extract from page source
        try {
            const scriptTag = document.querySelector('script:contains("window.currentUser")');
            if (scriptTag) {
                const match = scriptTag.textContent.match(/id:\s*(\d+)/);
                if (match) return parseInt(match[1]);
            }
        } catch (e) {
            console.log('Could not extract user ID from script tag');
        }

        return null;
    }

    /**
     * Store admin ID for use in call handler
     */
    setCurrentAdminId(adminId) {
        this.currentAdminId = adminId;
        logDebug('Call recipient set to:', adminId);
    }

    /**
     * Get admin/receiver ID from the current context
     */
    getCurrentAdminId() {
        if (this.currentAdminId) return this.currentAdminId;

        // Try to get from SMS chat hidden input (admin page)
        const toUserIdInput = document.getElementById('to-user-id');
        if (toUserIdInput && toUserIdInput.value) {
            return parseInt(toUserIdInput.value);
        }

        // Try to get from message chat hidden input (client pages)
        const adminIdInput = document.getElementById('messageAdminId');
        if (adminIdInput && adminIdInput.value) {
            return parseInt(adminIdInput.value);
        }

        // Try to get from data attributes
        const adminIdAttr = document.documentElement.getAttribute('data-admin-id');
        if (adminIdAttr) return parseInt(adminIdAttr);

        return null;
    }

    /**
     * Initiate a video call
     */
    initiateCall() {
        try {
            const receiverId = this.getCurrentAdminId();
            const currentUserId = this.getCurrentUserId();

            logDebug('Call initiation - Receiver ID:', receiverId, 'Current User ID:', currentUserId);

            if (!receiverId) {
                alert('Please select a contact to call');
                return;
            }

            if (!currentUserId) {
                alert('User authentication required. Please log in again.');
                return;
            }

            if (receiverId === currentUserId) {
                alert('You cannot call yourself');
                return;
            }

            // Open call page in a new window/tab
            const callUrl = `/call/${receiverId}?initiate=true`;
            logDebug('Opening call URL:', callUrl);

            this.callWindow = window.open(callUrl, 'WebRTC_Call', 
                'width=1200,height=800,resizable=yes,scrollbars=no,toolbar=no,menubar=no'
            );

            if (!this.callWindow) {
                alert('Unable to open call window. Please check your browser popup settings.');
                return;
            }

            // Monitor call window status
            const checkInterval = setInterval(() => {
                if (this.callWindow && this.callWindow.closed) {
                    clearInterval(checkInterval);
                    this.onCallEnded();
                }
            }, 1000);

        } catch (error) {
            console.error('Error initiating call:', error);
            alert('Failed to initiate call: ' + error.message);
        }
    }

    /**
     * Called when the call window is closed
     */
    onCallEnded() {
        console.log('Call window closed');
        this.callWindow = null;
        
        // Optional: Show notification or perform cleanup
        if (typeof showToast === 'function') {
            showToast('Call session ended', 'info');
        }
    }

    /**
     * Check if call window is open
     */
    isCallActive() {
        return this.callWindow && !this.callWindow.closed;
    }

    /**
     * Close active call window
     */
    closeCallWindow() {
        if (this.callWindow && !this.callWindow.closed) {
            this.callWindow.close();
        }
    }
}

/**
 * Debug logging function
 */
function logDebug(...args) {
    if (window.DEBUG_WEBRTC_CALLS) {
        console.log('[WebRTC Call Manager]', ...args);
    }
}

// Initialize WebRTC Call Manager
let webRTCCallManager = null;

/**
 * Initialize call manager when document is ready
 * Handles both DOMContentLoaded and if script is loaded after DOM is ready
 */
function initializeCallManager() {
    if (!webRTCCallManager) {
        webRTCCallManager = new WebRTCCallManager();
        logDebug('WebRTC Call Manager initialized. Current User ID:', webRTCCallManager.currentUserId);
    }
}

// Try to initialize immediately (in case script loads after DOM is ready)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCallManager);
} else {
    initializeCallManager();
}

/**
 * Function called from messaging UI - Initiate Video Call
 * Accessible from onclick handlers
 */
function initiateVideoCall() {
    if (!webRTCCallManager) {
        webRTCCallManager = new WebRTCCallManager();
    }
    logDebug('initiateVideoCall called');
    webRTCCallManager.initiateCall();
}

/**
 * Function called from admin SMS chat - Initiate Video Call
 * Admin version
 */
function initiateVideoCallAdmin() {
    if (!webRTCCallManager) {
        webRTCCallManager = new WebRTCCallManager();
    }
    
    logDebug('initiateVideoCallAdmin called');

    const videoCallButton = document.getElementById('video-call-btn');
    if (videoCallButton && videoCallButton.disabled) {
        logDebug('Video call blocked because the selected client is offline or not selected');
        return;
    }
    
    // Get the selected user from the SMS chat interface
    const toUserIdInput = document.getElementById('to-user-id');
    if (toUserIdInput && toUserIdInput.value) {
        const userId = parseInt(toUserIdInput.value);
        logDebug('Setting call recipient to:', userId);
        webRTCCallManager.setCurrentAdminId(userId);
    } else {
        // Fallback for admin system chat
        const clientIdInput = document.getElementById('client-id');
        if (clientIdInput && clientIdInput.value) {
            const userId = parseInt(clientIdInput.value);
            logDebug('Setting call recipient from system chat client-id:', userId);
            webRTCCallManager.setCurrentAdminId(userId);
        } else {
            logDebug('No user selected in to-user-id or client-id field');
        }
    }
    
    webRTCCallManager.initiateCall();
}

/**
 * Store current admin ID when switching conversations
 * Call this from your message UI when switching admins
 */
function setCallRecipient(adminId) {
    if (!webRTCCallManager) {
        webRTCCallManager = new WebRTCCallManager();
    }
    logDebug('setCallRecipient called with:', adminId);
    webRTCCallManager.setCurrentAdminId(adminId);
}

/**
 * Close any active call window
 * Useful for cleanup on logout or navigation
 */
function closeActiveCallWindow() {
    if (webRTCCallManager) {
        logDebug('closeActiveCallWindow called');
        webRTCCallManager.closeCallWindow();
    }
}

/**
 * Enable debug logging
 */
window.DEBUG_WEBRTC_CALLS = false; // Set to true to enable console logging

// Export for use in modules if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = WebRTCCallManager;
}
