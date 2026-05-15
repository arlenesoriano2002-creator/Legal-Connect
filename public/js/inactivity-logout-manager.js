/**
 * InactivityLogoutManager
 * 
 * Monitors user inactivity and automatically logs out the user after a specified timeout.
 * Features:
 * - Tracks user activity (mouse, keyboard, touch)
 * - Shows warning modal before automatic logout
 * - POST-based logout to prevent Method Not Allowed errors
 * - Converts form submission to properly formatted request
 * - Retrieves CSRF token from meta tag
 */

class InactivityLogoutManager {
    constructor(config = {}) {
        // Configuration with defaults
        this.timeoutMinutes = config.timeoutMinutes || 15;
        this.warningMinutes = config.warningMinutes || 2;
        this.checkIntervalSeconds = config.checkIntervalSeconds || 10;
        this.logoutEndpoint = config.logoutEndpoint || '/logout';
        this.sessionStatusEndpoint = config.sessionStatusEndpoint || '/session/status';

        // Convert to seconds
        this.timeoutSeconds = this.timeoutMinutes * 60;
        this.warningSeconds = (this.timeoutMinutes - this.warningMinutes) * 60;
        this.checkIntervalMs = this.checkIntervalSeconds * 1000;

        // State tracking
        this.lastActivityTime = Date.now();
        this.isWarningShown = false;
        this.checkInterval = null;
        this.warningModal = null;
        this.isCountdownRunning = false;

        // Get CSRF token from meta tag
        this.csrfToken = this.getCsrfToken();

        // Initialize
        this.initialize();
    }

    /**
     * Initialize the inactivity manager
     */
    initialize() {
        // Attach activity listeners
        this.attachActivityListeners();

        // Start interval check
        this.startCheckInterval();

        console.log('InactivityLogoutManager initialized', {
            timeoutMinutes: this.timeoutMinutes,
            warningMinutes: this.warningMinutes,
            logoutEndpoint: this.logoutEndpoint
        });
    }

    /**
     * Attach event listeners to detect user activity
     */
    attachActivityListeners() {
        const activityEvents = [
            'mousedown',
            'mousemove',
            'keydown',
            'scroll',
            'touchstart',
            'click',
            'wheel'
        ];

        activityEvents.forEach(eventName => {
            document.addEventListener(eventName, (e) => {
                // Ignore events from modal dialogs
                if (!this.isEventFromModal(e)) {
                    this.recordActivity();
                }
            }, { passive: true });
        });

        console.log('[InactivityManager] Activity listeners attached');
    }

    /**
     * Check if event originated from modal
     * @param {Event} event
     * @returns {boolean}
     */
    isEventFromModal(event) {
        const modal = document.querySelector('.modal.show, [role="dialog"]');
        if (!modal) return false;
        return modal.contains(event.target);
    }

    /**
     * Record user activity and reset inactivity timer
     */
    recordActivity() {
        this.lastActivityTime = Date.now();
        this.isWarningShown = false;

        // If warning modal is open, close it
        if (this.warningModal && this.warningModal.classList.contains('show')) {
            this.hideWarningModal();
        }
    }

    /**
     * Start interval to check for inactivity
     */
    startCheckInterval() {
        this.checkInterval = setInterval(() => {
            this.checkInactivity();
        }, this.checkIntervalMs);

        console.log('[InactivityManager] Inactivity check interval started: ' + this.checkIntervalSeconds + ' seconds');
    }

    /**
     * Stop the inactivity check interval
     */
    stopCheckInterval() {
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
            this.checkInterval = null;
        }
    }

    /**
     * Check current inactivity duration
     */
    checkInactivity() {
        const inactiveSeconds = Math.floor((Date.now() - this.lastActivityTime) / 1000);

        // Session has expired - logout immediately
        if (inactiveSeconds >= this.timeoutSeconds) {
            console.warn('Session timeout reached - logging out user');
            this.hideWarningModal();
            this.performAutoLogout('Browser inactivity timeout reached');
            return;
        }

        // Show warning if approaching timeout and not already shown
        if (inactiveSeconds >= this.warningSeconds && !this.isWarningShown) {
            console.warn('Session warning time reached - showing modal');
            this.isWarningShown = true;
            const remainingSeconds = this.timeoutSeconds - inactiveSeconds;
            this.showWarningModal(remainingSeconds);
        }
    }

    /**
     * Show session expiration warning modal
     * @param {number} remainingSeconds
     */
    showWarningModal(remainingSeconds) {
        // Create modal if it doesn't exist
        if (!this.warningModal) {
            this.warningModal = this.createWarningModal();
            document.body.appendChild(this.warningModal);
        }

        // Show the modal
        this.warningModal.classList.add('show');
        this.warningModal.style.display = 'block';

        // Start countdown timer
        this.startCountdownTimer(remainingSeconds);

        // Add backdrop
        this.addModalBackdrop();
    }

    /**
     * Hide warning modal
     */
    hideWarningModal() {
        if (this.warningModal) {
            this.warningModal.classList.remove('show');
            this.warningModal.style.display = 'none';
            this.removeModalBackdrop();
        }
        this.isCountdownRunning = false;
    }

    /**
     * Create the warning modal element
     * @returns {HTMLElement}
     */
    createWarningModal() {
        const modal = document.createElement('div');
        modal.id = 'inactivityWarningModal';
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('role', 'dialog');
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">⏰ Session Expiring Soon</h5>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Your session will expire due to inactivity in:</p>
                        <div class="text-center">
                            <h2 id="countdownTimer" class="text-danger font-weight-bold">--:--</h2>
                        </div>
                        <p class="mt-3 text-muted small">
                            Click <strong>Continue Session</strong> to remain logged in, 
                            or you will be automatically logged out.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="logoutNowBtn">
                            Logout Now
                        </button>
                        <button type="button" class="btn btn-primary" id="continueSessionBtn">
                            Continue Session
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Attach button listeners
        modal.querySelector('#continueSessionBtn').addEventListener('click', () => {
            this.recordActivity();
            this.hideWarningModal();
        });

        modal.querySelector('#logoutNowBtn').addEventListener('click', () => {
            this.hideWarningModal();
            this.performAutoLogout('User clicked logout button');
        });

        return modal;
    }

    /**
     * Start countdown timer display
     * @param {number} remainingSeconds
     */
    startCountdownTimer(remainingSeconds) {
        if (this.isCountdownRunning) return;

        this.isCountdownRunning = true;
        let secondsLeft = remainingSeconds;

        const updateCountdown = () => {
            const minutes = Math.floor(secondsLeft / 60);
            const seconds = secondsLeft % 60;
            const timerElement = document.getElementById('countdownTimer');

            if (timerElement) {
                timerElement.textContent = 
                    String(minutes).padStart(2, '0') + ':' + 
                    String(seconds).padStart(2, '0');
            }

            secondsLeft--;

            if (secondsLeft >= 0) {
                setTimeout(updateCountdown, 1000);
            }
        };

        updateCountdown();
    }

    /**
     * Add modal backdrop
     */
    addModalBackdrop() {
        if (!document.querySelector('.modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
    }

    /**
     * Remove modal backdrop
     */
    removeModalBackdrop() {
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    }

    /**
     * Perform automatic logout via POST request
     * @param {string} reason
     */
    performAutoLogout(reason = 'Session timeout') {
        console.log('Performing automatic logout: ' + reason);

        // Stop the interval check
        this.stopCheckInterval();

        // Method 1: Use Fetch API (Recommended for modern browsers)
        this.logoutViaFetch(reason);
    }

    /**
     * Logout using Fetch API with POST
     * @param {string} reason
     */
    logoutViaFetch(reason) {
        const options = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({
                logout_reason: reason,
                timestamp: new Date().toISOString()
            })
        };

        fetch(this.logoutEndpoint, options)
            .then(response => {
                if (response.status === 405) {
                    console.warn('Logout endpoint returned 405, using form submission fallback');
                    this.logoutViaFormSubmission();
                    return null;
                }

                if (!response.ok) {
                    throw new Error('Logout request failed: ' + response.status);
                }

                // Some endpoints may redirect without returning JSON, so handle both cases
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    // Assume redirect has already happened, but do safe navigation
                    window.location.href = '/welcome';
                    return null;
                }

                return response.json().catch(() => null);
            })
            .then(data => {
                if (data) {
                    console.log('Logout successful', data);
                }
                // Redirect to welcome or login page, if not already redirected
                window.location.href = '/welcome';
            })
            .catch(error => {
                console.error('Logout error:', error);
                // Fallback: Use form submission method
                this.logoutViaFormSubmission();
            });
    }

    /**
     * Fallback: Logout using form submission
     * This method sends a proper POST request using a form
     */
    logoutViaFormSubmission() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = this.logoutEndpoint;
        form.setAttribute('style', 'display:none;');

        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = this.csrfToken;
        form.appendChild(csrfInput);

        document.body.appendChild(form);
        form.submit();
    }

    /**
     * Get CSRF token from meta tag or input
     * @returns {string}
     */
    getCsrfToken() {
        // Try meta tag first
        let token = document.querySelector('meta[name="csrf-token"]')?.content;
        
        if (!token) {
            // Try hidden input field
            token = document.querySelector('input[name="_token"]')?.value;
        }

        if (!token) {
            console.warn('CSRF token not found');
            return '';
        }

        return token;
    }

    /**
     * Manually trigger logout
     */
    logout() {
        this.performAutoLogout('User initiated logout');
    }

    /**
     * Destroy the manager and clean up
     */
    destroy() {
        this.stopCheckInterval();
        this.hideWarningModal();
        if (this.warningModal && this.warningModal.parentNode) {
            this.warningModal.parentNode.removeChild(this.warningModal);
        }
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = InactivityLogoutManager;
}

// Optional helper function for logging (if you need custom log behavior)
function inactivityLogger(message, data) {
    if (window.DEBUG_MODE) {
        console.log('[InactivityManager] ' + message, data || '');
    }
}
