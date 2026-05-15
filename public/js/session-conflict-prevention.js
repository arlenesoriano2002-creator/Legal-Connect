/**
 * Session Conflict Prevention
 * 
 * Prevents multiple active sessions for the same user account.
 * If a user is already logged in on another tab, this script will:
 * 1. Detect the existing logged-in session
 * 2. Warn the user before allowing a new login
 * 3. Automatically clear old sessions when a new login occurs
 * 4. Synchronize logout events across tabs
 */

const SessionConflictPrevention = {
    // Configuration
    STORAGE_KEYS: {
        USER_ID: 'legal_connect_current_user_id',
        EMAIL: 'legal_connect_current_user_email',
        ROLE: 'legal_connect_current_user_role',
        LOGIN_TIME: 'legal_connect_login_timestamp',
        SESSION_LOCK: 'legal_connect_session_lock'
    },
    SESSION_STATE_CACHE_TTL: 30000,
    sessionStateCache: new Map(),

    /**
     * Initialize session conflict prevention
     */
    init() {
        console.log('[SessionConflictPrevention] Initializing...');
        
        // Set up storage event listener for cross-tab communication
        window.addEventListener('storage', (e) => this.handleStorageChange(e));
        
        // Clear stale client-side markers when the database already says offline.
        this.syncStoredSessionState();

        // Detect if this tab is trying to login while user is logged in elsewhere
        this.setupLoginFormValidation();
        
        // Monitor for logout events from other tabs
        this.setupLogoutDetection();
        
        console.log('[SessionConflictPrevention] Initialized');
    },

    /**
     * Set up validation on login form to warn about duplicate sessions
     */
    setupLoginFormValidation() {
        const loginForm = document.getElementById('loginForm');
        if (!loginForm) return;
        const emailInput = document.querySelector('input[name="email"]');

        if (emailInput) {
            emailInput.addEventListener('blur', () => {
                const email = this.normalizeEmail(emailInput.value);
                if (!email) return;

                this.fetchSessionState(email).catch((error) => {
                    console.warn('[SessionConflictPrevention] Unable to prefetch session state:', error);
                });
            });
        }
    },

    /**
     * Check if user with this email is already logged in on another tab
     * @param {string} email - Email attempting to login
     */
    async checkForExistingSession(email) {
        try {
            const normalizedEmail = this.normalizeEmail(email);
            if (!normalizedEmail) {
                return false;
            }

            const existingEmail = this.normalizeEmail(sessionStorage.getItem(this.STORAGE_KEYS.EMAIL));
            const existingLoginTime = sessionStorage.getItem(this.STORAGE_KEYS.LOGIN_TIME);
            const sessionState = await this.fetchSessionState(normalizedEmail, { forceRefresh: true });

            if (!sessionState.has_active_session) {
                if (existingEmail && existingEmail === normalizedEmail) {
                    this.clearConflictMarkers();
                }

                return false;
            }

            if (sessionState.has_active_session) {
                console.warn('[SessionConflictPrevention] User already logged in on another tab', {
                    email: normalizedEmail,
                    loginTime: existingLoginTime
                });
                
                // Show warning dialog
                this.showDuplicateSessionWarning(normalizedEmail, existingLoginTime);
                return true;
            }
        } catch (error) {
            console.warn('[SessionConflictPrevention] Error checking existing session:', error);
        }

        return false;
    },

    normalizeEmail(email) {
        return String(email || '').trim().toLowerCase();
    },

    getSessionStateUrl(email) {
        const baseUrl = window.loginSessionConflictConfig?.sessionStateUrl || '/login/session-state';
        const url = new URL(baseUrl, window.location.origin);
        url.searchParams.set('email', email);
        return url.toString();
    },

    getCachedSessionState(email) {
        const cached = this.sessionStateCache.get(email);

        if (!cached) {
            return null;
        }

        if ((Date.now() - cached.timestamp) > this.SESSION_STATE_CACHE_TTL) {
            this.sessionStateCache.delete(email);
            return null;
        }

        return cached.value;
    },

    setCachedSessionState(email, value) {
        this.sessionStateCache.set(email, {
            value,
            timestamp: Date.now()
        });
    },

    async fetchSessionState(email, options = {}) {
        const normalizedEmail = this.normalizeEmail(email);
        if (!normalizedEmail) {
            return {
                active_status: 0,
                has_active_session: false
            };
        }

        const cached = options.forceRefresh ? null : this.getCachedSessionState(normalizedEmail);
        if (cached) {
            return cached;
        }

        const response = await fetch(this.getSessionStateUrl(normalizedEmail), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            cache: 'no-store'
        });

        if (!response.ok) {
            throw new Error(`Session state request failed with status ${response.status}`);
        }

        const data = await response.json();
        const state = {
            active_status: Number(data.active_status) === 1 ? 1 : 0,
            has_active_session: Number(data.active_status) === 1
        };

        this.setCachedSessionState(normalizedEmail, state);

        return state;
    },

    async syncStoredSessionState() {
        const existingEmail = this.normalizeEmail(sessionStorage.getItem(this.STORAGE_KEYS.EMAIL));

        if (!existingEmail) {
            return;
        }

        try {
            const sessionState = await this.fetchSessionState(existingEmail);

            if (!sessionState.has_active_session) {
                this.clearConflictMarkers();
            }
        } catch (error) {
            console.warn('[SessionConflictPrevention] Unable to sync stored session state:', error);
        }
    },

    /**
     * Display a warning about duplicate session attempt
     * @param {string} email - User email
     * @param {string} loginTime - When the user originally logged in
     */
    showDuplicateSessionWarning(email, loginTime) {
        const message = `
⚠️ Session Detection:

Your account (${email}) is already logged in on another tab or browser window.

When you proceed with this login:
• Your previous session will be terminated
• You will be logged in on THIS tab only
• You will need to log in again on other tabs

Do you want to continue?
        `.trim();

        // Log the warning in console
        console.warn('[SessionConflictPrevention] ' + message);
        
        // Optionally show in-page notification (non-blocking)
        this.showInPageNotification(
            'ℹ️ Active Session Detected',
            `Your account is already logged in elsewhere. This new login will close your previous session.`,
            'info'
        );
    },

    /**
     * Show in-page notification (non-blocking)
     * @param {string} title - Notification title
     * @param {string} message - Notification message
     * @param {string} type - Type: 'info', 'warning', 'error'
     */
    showInPageNotification(title, message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `session-prevention-notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-header">
                <strong>${title}</strong>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">✕</button>
            </div>
            <div class="notification-body">${message}</div>
        `;
        
        // Append to body
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    },

    /**
     * Record current user session info in sessionStorage
     * Called after successful login from ResponseInterceptor
     */
    recordSession(userId, email, role) {
        try {
            const now = new Date().toISOString();
            
            sessionStorage.setItem(this.STORAGE_KEYS.USER_ID, userId);
            sessionStorage.setItem(this.STORAGE_KEYS.EMAIL, email);
            sessionStorage.setItem(this.STORAGE_KEYS.ROLE, role);
            sessionStorage.setItem(this.STORAGE_KEYS.LOGIN_TIME, now);
            
            console.log('[SessionConflictPrevention] Session recorded', {
                userId: userId,
                email: email,
                role: role,
                timestamp: now
            });
            
            // Broadcast login event to other tabs
            window.dispatchEvent(new CustomEvent('session:login', {
                detail: { userId, email, role, timestamp: now }
            }));
        } catch (error) {
            console.warn('[SessionConflictPrevention] Error recording session:', error);
        }
    },

    /**
     * Clear session info from sessionStorage
     * Called on logout
     */
    clearSession() {
        try {
            const email = sessionStorage.getItem(this.STORAGE_KEYS.EMAIL);

            this.clearConflictMarkers();
            
            console.log('[SessionConflictPrevention] Session cleared', { email });
            
            // Clear tab tokens too
            sessionStorage.removeItem('legal_connect_tab_token');
            sessionStorage.removeItem('legal_connect_tab_id');
            sessionStorage.removeItem('legal_connect_tab_expiry');
            
            // Broadcast logout event to other tabs
            window.dispatchEvent(new CustomEvent('session:logout', {
                detail: { email: email }
            }));
        } catch (error) {
            console.warn('[SessionConflictPrevention] Error clearing session:', error);
        }
    },

    clearConflictMarkers() {
        sessionStorage.removeItem(this.STORAGE_KEYS.USER_ID);
        sessionStorage.removeItem(this.STORAGE_KEYS.EMAIL);
        sessionStorage.removeItem(this.STORAGE_KEYS.ROLE);
        sessionStorage.removeItem(this.STORAGE_KEYS.LOGIN_TIME);
        sessionStorage.removeItem(this.STORAGE_KEYS.SESSION_LOCK);
    },

    /**
     * Handle storage events from other tabs
     * @param {StorageEvent} event
     */
    handleStorageChange(event) {
        // Detect if another tab logged in with a different user
        if (event.key === this.STORAGE_KEYS.USER_ID && event.newValue !== event.oldValue) {
            if (event.newValue && event.oldValue && event.newValue !== event.oldValue) {
                console.warn('[SessionConflictPrevention] Different user logged in on another tab', {
                    oldUserId: event.oldValue,
                    newUserId: event.newValue
                });
                
                // Another tab logged in as a different user
                // Optionally refresh this page or show a message
                this.handleCrossTabSessionChange('different_user');
            }
        }
        
        // Detect if another tab logged out
        if (event.key === this.STORAGE_KEYS.SESSION_LOCK && event.newValue === null) {
            console.log('[SessionConflictPrevention] Logout detected on another tab');
            this.handleCrossTabSessionChange('logout');
        }
    },

    /**
     * Handle session changes from other tabs
     * @param {string} changeType - Type of change: 'different_user', 'logout'
     */
    handleCrossTabSessionChange(changeType) {
        if (changeType === 'logout') {
            // Inform user that another tab logged out
            console.log('[SessionConflictPrevention] Another tab logged out');
        } else if (changeType === 'different_user') {
            // Different user logged in on another tab
            console.warn('[SessionConflictPrevention] A different user logged in on another tab');
        }
    },

    /**
     * Set up logout detection
     */
    setupLogoutDetection() {
        // Listen for logout button clicks
        document.addEventListener('click', (e) => {
            if (e.target && (
                e.target.classList.contains('logout-btn') ||
                e.target.classList.contains('logout-button') ||
                e.target.textContent.includes('Logout') ||
                e.target.textContent.includes('logout')
            )) {
                console.log('[SessionConflictPrevention] Logout detected');
                this.clearSession();
            }
        });
        
        // Detect form submissions to logout endpoints
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form && form.action && form.action.includes('logout')) {
                console.log('[SessionConflictPrevention] Logout form submitted');
                this.clearSession();
            }
        });
        
        // Detect SPA navigation to logout
        window.addEventListener('beforeunload', () => {
            // Don't clear on page unload, just log
            console.log('[SessionConflictPrevention] Page unload');
        });
    },

    /**
     * Synchronize sessions across tabs
     * Call this when user logs in successfully
     */
    synchronizeLogin(userData) {
        if (!userData) return;
        
        this.recordSession(
            userData.id,
            userData.email,
            userData.role
        );
        
        // Broadcast to other tabs
        try {
            // Use localStorage for cross-tab communication
            const syncData = {
                action: 'login',
                userId: userData.id,
                email: userData.email,
                role: userData.role,
                timestamp: new Date().toISOString()
            };
            
            localStorage.setItem('legal_connect_sync', JSON.stringify(syncData));
            
            setTimeout(() => {
                localStorage.removeItem('legal_connect_sync');
            }, 100);
        } catch (error) {
            console.warn('[SessionConflictPrevention] Error synchronizing login:', error);
        }
    },

    /**
     * Synchronize logout across tabs
     */
    synchronizeLogout() {
        this.clearSession();
        
        // Broadcast to other tabs
        try {
            const syncData = {
                action: 'logout',
                timestamp: new Date().toISOString()
            };
            
            localStorage.setItem('legal_connect_sync', JSON.stringify(syncData));
            
            setTimeout(() => {
                localStorage.removeItem('legal_connect_sync');
            }, 100);
        } catch (error) {
            console.warn('[SessionConflictPrevention] Error synchronizing logout:', error);
        }
    }
};

// Add CSS styles for notifications
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    .session-prevention-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        max-width: 400px;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        z-index: 10000;
        animation: slideInRight 0.3s ease-out;
    }
    
    .notification-info {
        background-color: #e3f2fd;
        border-left: 4px solid #2196f3;
        color: #1565c0;
    }
    
    .notification-warning {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
        color: #856404;
    }
    
    .notification-error {
        background-color: #f8d7da;
        border-left: 4px solid #f5222d;
        color: #721c24;
    }
    
    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
    
    .notification-close {
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.2s;
    }
    
    .notification-close:hover {
        opacity: 1;
    }
    
    .notification-body {
        font-size: 14px;
        line-height: 1.5;
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @media (max-width: 600px) {
        .session-prevention-notification {
            left: 10px;
            right: 10px;
            max-width: none;
        }
    }
`;

document.head.appendChild(notificationStyles);
