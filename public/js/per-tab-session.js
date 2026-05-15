/**
 * Per-Tab Session Manager
 * Ensures each browser tab maintains its own independent login session
 * 
 * Features:
 * - Unique tab ID generation per tab (persists within that tab)
 * - Tab token management in sessionStorage
 * - Automatic header injection for API requests
 * - Persistence tracking in localStorage
 * - Token refresh mechanism
 */

class PerTabSessionManager {
    static CONFIG = {
        TAB_ID_KEY: 'legal_connect_tab_id',
        TAB_TOKEN_KEY: 'legal_connect_tab_token',
        TAB_EXPIRY_KEY: 'legal_connect_tab_expiry',
        ACTIVE_TABS_KEY: 'legal_connect_active_tabs',
        REFRESH_INTERVAL: 10 * 60 * 1000, // RefreshToken every 10 minutes
    };

    static instance = null;

    static _getInstance() {
        if (!this.instance) {
            this.instance = new PerTabSessionManager();
        }
        return this.instance;
    }

    /**
     * Initialize the per-tab session manager
     * Should be called once on page load
     */
    static initialize() {
        const manager = this._getInstance();
        manager._setupTabId();
        manager._setupRequestInterceptors();
        manager._setupTokenRefresh();
        manager._trackActiveTab();
        console.log('Per-Tab Session Manager initialized');
    }

    /**
     * Setup the unique tab ID for this tab
     */
    _setupTabId() {
        let tabId = sessionStorage.getItem(PerTabSessionManager.CONFIG.TAB_ID_KEY);

        if (!tabId) {
            // Generate new unique tab ID
            tabId = this._generateTabId();
            sessionStorage.setItem(PerTabSessionManager.CONFIG.TAB_ID_KEY, tabId);
            console.log('Generated new tab ID:', tabId);
        } else {
            console.log('Using existing tab ID:', tabId);
        }

        return tabId;
    }

    /**
     * Generate a unique tab ID combining timestamp and UUID
     */
    _generateTabId() {
        return `tab_${Date.now()}_${this._generateUUID()}`;
    }

    /**
     * Generate a UUID v4
     */
    _generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    /**
     * Setup interceptors for all API requests to include tab token headers
     */
    _setupRequestInterceptors() {
        this._interceptFetchAPI();
        this._interceptXMLHttpRequest();
    }

    /**
     * Intercept Fetch API requests
     */
    _interceptFetchAPI() {
        const originalFetch = window.fetch;
        const self = this;

        window.fetch = function(...args) {
            let [resource, config] = args;
            const tabToken = PerTabSessionManager.getTabToken();
            const tabId = PerTabSessionManager.getTabId();

            // Only add headers to API calls (exclude external URLs, etc.)
            if (typeof resource === 'string' && resource.startsWith('/')) {
                config = config || {};
                config.headers = config.headers || {};

                if (tabToken) {
                    config.headers['X-Tab-Token'] = tabToken;
                }
                if (tabId) {
                    config.headers['X-Tab-ID'] = tabId;
                }
            }

            args[1] = config;
            return originalFetch.apply(this, args);
        };
    }

    /**
     * Intercept XMLHttpRequest (jQuery AJAX, etc.)
     */
    _interceptXMLHttpRequest() {
        const originalOpen = XMLHttpRequest.prototype.open;

        XMLHttpRequest.prototype.open = function(method, url, ...args) {
            originalOpen.apply(this, [method, url, ...args]);

            // Store reference to this request
            const self = this;
            const originalSetRequestHeader = this.setRequestHeader;

            this.setRequestHeader = function(header, value) {
                // Let normal headers be set
                originalSetRequestHeader.call(this, header, value);
            };

            // Add our custom headers after the request is opened
            setTimeout(() => {
                const tabToken = PerTabSessionManager.getTabToken();
                const tabId = PerTabSessionManager.getTabId();

                if (tabToken) {
                    this.setRequestHeader('X-Tab-Token', tabToken);
                }
                if (tabId) {
                    this.setRequestHeader('X-Tab-ID', tabId);
                }
            }, 0);
        };
    }

    /**
     * Setup automatic token refresh
     */
    _setupTokenRefresh() {
        // Refresh token every REFRESH_INTERVAL if it exists
        setInterval(() => {
            const tabToken = PerTabSessionManager.getTabToken();
            if (tabToken) {
                this._refreshToken();
            }
        }, PerTabSessionManager.CONFIG.REFRESH_INTERVAL);

        // Also refresh on user activity
        ['mousedown', 'keydown', 'touch'].forEach(event => {
            document.addEventListener(event, () => {
                const tabToken = PerTabSessionManager.getTabToken();
                if (tabToken) {
                    this._refreshToken();
                }
            }, { passive: true });
        });
    }

    /**
     * Refresh the tab token with the server
     */
    _refreshToken() {
        const tabToken = PerTabSessionManager.getTabToken();
        if (!tabToken) return;

        fetch('/tab-session/refresh', {
            method: 'POST',
            headers: {
                'X-Tab-Token': tabToken,
                'Content-Type': 'application/json',
            }
        })
        .catch(err => console.debug('Token refresh failed:', err));
    }

    /**
     * Track this tab as active
     */
    _trackActiveTab() {
        try {
            const tabId = PerTabSessionManager.getTabId();
            const activeTabs = JSON.parse(localStorage.getItem(PerTabSessionManager.CONFIG.ACTIVE_TABS_KEY) || '{}');

            activeTabs[tabId] = {
                lastActive: new Date().toISOString(),
                page: window.location.pathname
            };

            localStorage.setItem(PerTabSessionManager.CONFIG.ACTIVE_TABS_KEY, JSON.stringify(activeTabs));
        } catch (e) {
            console.debug('Could not track active tab:', e);
        }
    }

    /**
     * Store tab token after login
     * Public static method
     */
    static setTabToken(token, expiresAt) {
        sessionStorage.setItem(PerTabSessionManager.CONFIG.TAB_TOKEN_KEY, token);
        if (expiresAt) {
            sessionStorage.setItem(PerTabSessionManager.CONFIG.TAB_EXPIRY_KEY, expiresAt);
        }
        console.log('Tab token stored');
        PerTabSessionManager._getInstance()._trackActiveTab();
    }

    /**
     * Get the current tab's token
     * Public static method
     */
    static getTabToken() {
        return sessionStorage.getItem(PerTabSessionManager.CONFIG.TAB_TOKEN_KEY);
    }

    /**
     * Get the current tab's ID
     * Public static method
     */
    static getTabId() {
        return sessionStorage.getItem(PerTabSessionManager.CONFIG.TAB_ID_KEY);
    }

    /**
     * Clear tab session (on logout)
     * Public static method
     */
    static clearTabSession() {
        const tabId = sessionStorage.getItem(PerTabSessionManager.CONFIG.TAB_ID_KEY);

        sessionStorage.removeItem(PerTabSessionManager.CONFIG.TAB_TOKEN_KEY);
        sessionStorage.removeItem(PerTabSessionManager.CONFIG.TAB_EXPIRY_KEY);

        // Remove from active tabs
        if (tabId) {
            try {
                const activeTabs = JSON.parse(localStorage.getItem(PerTabSessionManager.CONFIG.ACTIVE_TABS_KEY) || '{}');
                delete activeTabs[tabId];
                localStorage.setItem(PerTabSessionManager.CONFIG.ACTIVE_TABS_KEY, JSON.stringify(activeTabs));
            } catch (e) {
                console.debug('Could not update active tabs:', e);
            }
        }

        console.log('Tab session cleared');
    }

    /**
     * Check if current tab is a guest (no valid token)
     * Public static method
     */
    static isGuestTab() {
        return !PerTabSessionManager.getTabToken();
    }

    /**
     * Logout current tab and redirect
     * Public static method
     */
    static logoutTab() {
        const tabToken = PerTabSessionManager.getTabToken();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (tabToken) {
            // Send logout request to server
            fetch('/tab-session/logout', {
                method: 'POST',
                headers: {
                    'X-Tab-Token': tabToken,
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ tab_token: tabToken })
            })
            .finally(() => {
                PerTabSessionManager.clearTabSession();
            });
        } else {
            PerTabSessionManager.clearTabSession();
        }
    }
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => PerTabSessionManager.initialize());
} else {
    PerTabSessionManager.initialize();
}
