/**
 * Session Timeout Manager
 * 
 * Handles 30-minute session timeout with warning modal.
 * Shows warning 5 minutes before session expires.
 * Auto-logs out user when session expires.
 */

class SessionTimeoutManager {
    constructor() {
        this.SESSION_TIMEOUT = 30 * 60 * 1000; // 30 minutes in milliseconds
        this.WARNING_TIME = 5 * 60 * 1000; // 5 minute warning before expiration
        this.timeoutId = null;
        this.warningShown = false;
        this.lastActivityTime = Date.now();
        
        this.init();
    }

    init() {
        // Only initialize on protected pages
        if (!this.isAuthenticatedPage()) {
            console.log('Session Timeout Manager: Not on authenticated page, skipping initialization');
            return;
        }

        // Track user activity
        this.setupActivityListeners();
        
        // Start timeout timer
        this.resetTimer();
        
        console.log('Session Timeout Manager: Initialized (30-minute timeout)');
    }

    isAuthenticatedPage() {
        // Check if page has auth-user meta tag or user is logged in
        return document.querySelector('meta[name="auth-user"]') !== null;
    }

    setupActivityListeners() {
        // Reset timer on user activity
        const activityEvents = ['mousedown', 'keydown', 'scroll', 'touchstart', 'click'];
        
        activityEvents.forEach(event => {
            document.addEventListener(event, () => this.onUserActivity());
        });
    }

    onUserActivity() {
        // Update last activity time
        this.lastActivityTime = Date.now();
        
        // If warning was shown, hide it on new activity
        if (this.warningShown) {
            this.hideWarningModal();
            this.warningShown = false;
        }
        
        // Reset timeout timer
        this.resetTimer();
    }

    resetTimer() {
        // Clear existing timer
        if (this.timeoutId) {
            clearTimeout(this.timeoutId);
        }

        // Set warning timer (25 minutes)
        setTimeout(() => {
            if (!this.warningShown) {
                this.showWarningModal();
            }
        }, this.SESSION_TIMEOUT - this.WARNING_TIME);

        // Set logout timer (30 minutes)
        this.timeoutId = setTimeout(() => {
            this.logout();
        }, this.SESSION_TIMEOUT);
    }

    showWarningModal() {
        this.warningShown = true;
        
        // Create warning modal if it doesn't exist
        let modal = document.getElementById('sessionTimeoutWarning');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'sessionTimeoutWarning';
            modal.className = 'session-timeout-warning-modal';
            modal.innerHTML = `
                <div class="session-timeout-warning-content">
                    <div class="session-timeout-warning-header">
                        <h2>Session Ending</h2>
                        <button type="button" class="close-btn" onclick="document.getElementById('sessionTimeoutWarning').style.display='none'">&times;</button>
                    </div>
                    <div class="session-timeout-warning-body">
                        <p>Your session will expire in <strong>5 minutes</strong> due to inactivity.</p>
                        <p>Click the button below to continue your session.</p>
                    </div>
                    <div class="session-timeout-warning-footer">
                        <button onclick="sessionTimeoutManager.onUserActivity()" class="btn-continue">Continue Session</button>
                        <button onclick="sessionTimeoutManager.logout()" class="btn-logout">Logout</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        
        modal.style.display = 'flex';
        console.log('Session Timeout Manager: Warning modal displayed');
    }

    hideWarningModal() {
        const modal = document.getElementById('sessionTimeoutWarning');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    logout() {
        console.log('Session Timeout Manager: Logging out due to timeout');

        const csrfToken =
            document.querySelector('meta[name="csrf-token"]')?.content ||
            document.querySelector('input[name="_token"]')?.value ||
            '';

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/logout';
        form.style.display = 'none';

        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }

        document.body.appendChild(form);
        form.submit();
    }
}

// Initialize Session Timeout Manager
document.addEventListener('DOMContentLoaded', function() {
    window.sessionTimeoutManager = new SessionTimeoutManager();
});

// CSS for warning modal (add to document)
const style = document.createElement('style');
style.innerHTML = `
    .session-timeout-warning-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .session-timeout-warning-content {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        max-width: 500px;
        overflow: hidden;
    }

    .session-timeout-warning-header {
        background: linear-gradient(135deg, #f93b1d 0%, #ea1e63 100%);
        color: white;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .session-timeout-warning-header h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .session-timeout-warning-header .close-btn {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }

    .session-timeout-warning-body {
        padding: 20px;
        text-align: center;
        font-size: 1rem;
        color: #333;
    }

    .session-timeout-warning-body p {
        margin: 10px 0;
        line-height: 1.6;
    }

    .session-timeout-warning-body strong {
        color: #f93b1d;
        font-weight: 600;
    }

    .session-timeout-warning-footer {
        padding: 20px;
        background: #f5f5f5;
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .session-timeout-warning-footer button {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s ease;
        font-weight: 600;
    }

    .session-timeout-warning-footer .btn-continue {
        background: #4CAF50;
        color: white;
    }

    .session-timeout-warning-footer .btn-continue:hover {
        background: #45a049;
    }

    .session-timeout-warning-footer .btn-logout {
        background: #f93b1d;
        color: white;
    }

    .session-timeout-warning-footer .btn-logout:hover {
        background: #e01f0b;
    }
`;
document.head.appendChild(style);
