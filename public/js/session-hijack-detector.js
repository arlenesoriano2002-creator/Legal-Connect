/**
 * Session Hijacking Detection Module
 * 
 * Monitors the authenticated user and detects when another account
 * has been logged in, likely due to another tab's login action.
 * This provides real-time protection in the UI layer.
 * 
 * Usage:
 * 1. Initialize on page load: SessionHijackDetector.init(currentUserId)
 * 2. Call refresh() to validate current user (optional, auto-runs every 30 seconds)
 * 3. Configure callbacks via .on('hijacked', callback)
 */

const SessionHijackDetector = {
    /**
     * Current user ID from the server (Blade template context)
     */
    currentUserId: null,

    /**
     * Tab token stored in sessionStorage
     */
    tabToken: null,

    /**
     * Polling interval (30 seconds)
     */
    pollInterval: 30000,

    /**
     * Interval handle for cleanup
     */
    intervalHandle: null,

    /**
     * Event listeners
     */
    listeners: {
        hijacked: [],
        validated: [],
        error: []
    },

    /**
     * Initialize session hijack detection
     * 
     * @param {number|string} userId - Current authenticated user ID from server
     */
    init(userId) {
        this.currentUserId = userId;
        this.tabToken = sessionStorage.getItem('tab_token') || null;

        // Start polling immediately on init
        this.validate();

        // Set up periodic validation
        this.intervalHandle = setInterval(() => {
            this.validate();
        }, this.pollInterval);

        console.log('[SessionHijackDetector] Initialized for user ID:', userId);
    },

    /**
     * Stop monitoring for session hijacks
     */
    destroy() {
        if (this.intervalHandle) {
            clearInterval(this.intervalHandle);
            this.intervalHandle = null;
        }
        console.log('[SessionHijackDetector] Monitoring stopped');
    },

    /**
     * Validate that current user hasn't changed
     * Makes AJAX call to /tab-session/info to verify tab token ownership
     */
    validate() {
        if (!this.tabToken) {
            // No tab token - might be old session, allow but warn
            console.warn('[SessionHijackDetector] No tab token found');
            return;
        }

        // Make AJAX request to validate tab ownership
        fetch('/tab-session/info', {
            method: 'GET',
            headers: {
                'X-Tab-Token': this.tabToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
        })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Tab token invalid - session expired or hijacked
                    this._handleHijack('Session expired or invalid', data.code);
                    return;
                }

                // Verify tab token's user_id matches the authenticated user
                if (parseInt(data.user_id) !== parseInt(this.currentUserId)) {
                    // Different user ID - session was hijacked
                    this._handleHijack(
                        `Another account (ID: ${data.user_id}) was logged in`,
                        'USER_MISMATCH'
                    );
                    return;
                }

                // All good - emit validated event
                this._fireEvent('validated', {
                    user_id: data.user_id,
                    expires_at: data.expires_at,
                    tab_id: data.tab_id
                });
            })
            .catch(error => {
                console.error('[SessionHijackDetector] Validation error:', error);
                this._fireEvent('error', { error });
            });
    },

    /**
     * Handle detected session hijack
     * 
     * @private
     * @param {string} message - Description of what happened
     * @param {string} code - Error code (SESSION_EXPIRED, SESSION_INVALID, USER_MISMATCH)
     */
    _handleHijack(message, code) {
        console.warn('[SessionHijackDetector] Session hijack detected:', message);

        // Stop further monitoring
        this.destroy();

        // Fire hijacked event
        this._fireEvent('hijacked', { message, code });

        // Show user-friendly notification
        this._showHijackNotification(message);
    },

    /**
     * Display hijack notification to user
     * 
     * @private
     * @param {string} message
     */
    _showHijackNotification(message) {
        // Create notification container if it doesn't exist
        let container = document.getElementById('sessionHijackNotificationContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'sessionHijackNotificationContainer';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
            `;
            document.body.appendChild(container);
        }

        // Create alert element
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.setAttribute('role', 'alert');
        alert.innerHTML = `
            <strong><i class="fas fa-exclamation-circle me-2"></i>Session Compromised</strong><br>
            <small>${message}</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        container.appendChild(alert);

        // Remove after 10 seconds or when closed
        const removeAlert = () => {
            alert.remove();
            if (container.children.length === 0) {
                container.remove();
            }
        };

        setTimeout(removeAlert, 10000);
        alert.addEventListener('closed.bs.alert', removeAlert);

        // Offer to redirect to login or refresh page
        setTimeout(() => {
            const userChoice = confirm(
                'Your session may have been compromised by another tab login.\n\n' +
                'Click "OK" to log in again (you will be redirected to login page).'
            );
            if (userChoice) {
                window.location.href = '/login';
            } else {
                // Refresh page to re-establish authentication
                window.location.reload();
            }
        }, 2000);
    },

    /**
     * Register an event listener
     * 
     * @param {string} event - 'hijacked', 'validated', or 'error'
     * @param {Function} callback
     * @returns {Function} Unsubscribe function
     */
    on(event, callback) {
        if (!this.listeners[event]) {
            console.warn('[SessionHijackDetector] Unknown event:', event);
            return () => { };
        }

        this.listeners[event].push(callback);

        // Return unsubscribe function
        return () => {
            this.listeners[event] = this.listeners[event].filter(cb => cb !== callback);
        };
    },

    /**
     * Fire an event to all registered listeners
     * 
     * @private
     * @param {string} event
     * @param {*} data
     */
    _fireEvent(event, data) {
        if (!this.listeners[event]) return;

        this.listeners[event].forEach(callback => {
            try {
                callback(data);
            } catch (error) {
                console.error('[SessionHijackDetector] Error in listener:', error);
            }
        });
    }
};

// Export for use in Blade templates
if (typeof window !== 'undefined') {
    window.SessionHijackDetector = SessionHijackDetector;
}
