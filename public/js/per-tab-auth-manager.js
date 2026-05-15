/**
 * Per-Tab Authentication Helper
 * 
 * This script handles per-tab authentication by:
 * 1. Reading tab_token from sessionStorage (stored by login)
 * 2. Sending tab_token with all AJAX requests via X-Tab-Token header
 * 3. Injecting tab_token with form submissions
 * 
 * Compatibility: Works with existing TabSessionManager in login view
 */

const PerTabAuthManager = {
    TAB_TOKEN_KEY: 'legal_connect_tab_token',  // Same key used by login view
    TAB_ID_KEY: 'legal_connect_tab_id',        // Same key used by login view
    
    /**
     * Get tab ID, using existing key if available
     */
    getTabId() {
        let tabId = sessionStorage.getItem(this.TAB_ID_KEY);
        if (!tabId) {
            tabId = 'tab_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem(this.TAB_ID_KEY, tabId);
        }
        return tabId;
    },

    /**
     * Get tab token from sessionStorage
     * Called after login to retrieve stored token
     */
    getTabToken() {
        return sessionStorage.getItem(this.TAB_TOKEN_KEY);
    },

    /**
     * Clear tab token on logout
     */
    clearTabToken() {
        sessionStorage.removeItem(this.TAB_TOKEN_KEY);
        console.log('[PerTabAuthManager] Tab token cleared from sessionStorage');
    },

    /**
     * Record user session info for conflict prevention
     * Called after successful login
     */
    recordSession(userData) {
        if (!userData || !userData.id) {
            console.warn('[PerTabAuthManager] No user data provided for session recording');
            return;
        }
        
        // Call SessionConflictPrevention if available
        if (typeof SessionConflictPrevention !== 'undefined') {
            SessionConflictPrevention.recordSession(userData.id, userData.email, userData.role);
            console.log('[PerTabAuthManager] Session recorded via SessionConflictPrevention');
        }
    },

    /**
     * Clear session info on logout
     * Call this when user logs out
     */
    clearSession() {
        this.clearTabToken();
        
        if (typeof SessionConflictPrevention !== 'undefined') {
            SessionConflictPrevention.clearSession();
            console.log('[PerTabAuthManager] Session cleared via SessionConflictPrevention');
        }
    },
    /**
     * Initialize per-tab authentication
     * Sets up automatic header injection
     */
    init() {
        const tabId = this.getTabId();
        const tabToken = this.getTabToken();
        
        console.log('[PerTabAuthManager] Init - Tab:', tabId, 'Token:', tabToken ? 'present' : 'absent');
        
        // Set up automatic header injection for fetch requests
        this.setupFetchInterceptor();
        
        // Track requests to ensure headers are sent
        this.setupRequestMonitoring();
    },

    /**
     * Intercept fetch requests to add X-Tab-Token and X-Tab-ID headers
     * This ensures every AJAX request includes the per-tab identification
     */
    setupFetchInterceptor() {
        const self = this;
        const originalFetch = window.fetch;
        
        window.fetch = function(...args) {
            let [resource, config] = args;
            
            // Only modify same-origin requests
            if (typeof resource === 'string' && !resource.startsWith('http')) {
                config = config || {};
                config.headers = config.headers || {};
                
                const tabToken = self.getTabToken();
                const tabId = self.getTabId();
                
                // Add per-tab headers
                if (tabToken) {
                    config.headers['X-Tab-Token'] = tabToken;
                }
                if (tabId) {
                    config.headers['X-Tab-ID'] = tabId;
                }
                
                console.log('[PerTabAuthManager] Fetch with headers - URL:', resource, 'Token header:', !!tabToken);
            }
            
            return originalFetch.apply(this, [resource, config]);
        };
    },

    /**
     * Monitor XMLHttpRequest (for jQuery.ajax, etc)
     */
    setupRequestMonitoring() {
        const self = this;
        const originalOpen = XMLHttpRequest.prototype.open;
        
        XMLHttpRequest.prototype.open = function(method, url, ...rest) {
            if (typeof url === 'string' && !url.startsWith('http')) {
                // Same-origin request
                this._tabToken = self.getTabToken();
                this._tabId = self.getTabId();
            }
            return originalOpen.call(this, method, url, ...rest);
        };
        
        const originalSetRequestHeader = XMLHttpRequest.prototype.setRequestHeader;
        XMLHttpRequest.prototype.setRequestHeader = function(header, value) {
            // Let normal headers be set first
            if (header.toLowerCase() === 'content-type' || !header.startsWith('X-')) {
                originalSetRequestHeader.call(this, header, value);
            } else {
                originalSetRequestHeader.call(this, header, value);
            }
        };
        
        // Override send to add our headers
        const originalSend = XMLHttpRequest.prototype.send;
        XMLHttpRequest.prototype.send = function(...args) {
            if (this._tabToken) {
                originalSetRequestHeader.call(this, 'X-Tab-Token', this._tabToken);
            }
            if (this._tabId) {
                originalSetRequestHeader.call(this, 'X-Tab-ID', this._tabId);
            }
            return originalSend.apply(this, args);
        };
    }
};
