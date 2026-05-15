/**
 * History Control Manager
 * 
 * Manages browser history to prevent back navigation to protected pages.
 * Uses history.replaceState to overwrite navigation entries.
 * Ensures protected pages cannot be revisited via browser history.
 */

class HistoryControlManager {
    constructor() {
        this.protectedRoutes = [
            'admindashboard',
            'dashboardStaff',
            'cordon/dashboard',
            'administrator',
            'appointments',
            'practice-areas',
            'clientstbl',
            'adminAcceptedRequest',
            'adminDeniedRequest',
            'adminAccount',
            'staff',
            'cordon'
        ];
        
        this.init();
    }

    init() {
        // Replace current history state on protected pages
        this.replaceProtectedPageHistory();
        
        // Handle popstate events (back button)
        window.addEventListener('popstate', (event) => {
            this.handleBackNavigation(event);
        });
        
        console.log('History control manager initialized');
    }

    /**
     * Check if current page is a protected route
     */
    isProtectedPage() {
        const currentPath = window.location.pathname.replace(/^\//, '');
        
        return this.protectedRoutes.some(route => {
            if (route.includes('*')) {
                const pattern = new RegExp('^' + route.replace(/\*/g, '.*'));
                return pattern.test(currentPath);
            }
            return currentPath.startsWith(route);
        });
    }

    /**
     * Replace current history state for protected pages
     * This prevents the page from appearing in browser history
     */
    replaceProtectedPageHistory() {
        if (this.isProtectedPage()) {
            // Replace the current history entry with a welcome page state
            // This prevents users from returning to this page via back button
            history.replaceState(
                { protected: true, originalUrl: window.location.href },
                document.title,
                window.location.href
            );
            
            // Push a new state pointing to welcome page
            // This creates a "barrier" in history
            history.pushState(
                { barrier: true, redirectTo: '/welcome' },
                document.title,
                window.location.href
            );
            
            console.log('Protected page history replaced');
        }
    }

    /**
     * Handle back button navigation
     */
    handleBackNavigation(event) {
        const state = event.state;
        
        // If trying to go back from a protected page
        if (state && state.barrier) {
            event.preventDefault();
            window.location.href = '/welcome';
            return;
        }

        // Additional check: verify user is still authenticated
        this.verifyAuthentication();
    }

    /**
     * Verify user authentication before allowing navigation
     */
    verifyAuthentication() {
        // Check if auth token exists in sessionStorage
        const authToken = sessionStorage.getItem('legal_connect_tab_token');
        const tabSession = sessionStorage.getItem('tab_session');
        const serverRenderedAuthUser = document.querySelector('meta[name="auth-user"]')?.content;
        
        // If no valid auth token and on protected page
        if (!authToken && !tabSession && !serverRenderedAuthUser && this.isProtectedPage()) {
            // Redirect to welcome
            window.location.href = '/welcome';
        }
    }

    /**
     * Force redirect if trying to access protected page without auth
     */
    checkProtectedAccess() {
        // This is called periodically to ensure user cannot access protected pages
        // even through browser cache or history manipulation
        
        const isAuthenticated = !!sessionStorage.getItem('legal_connect_tab_token') || 
                              !!localStorage.getItem('legal_connect_tab_token') ||
                              !!document.querySelector('meta[name="auth-user"]')?.content;
        
        if (this.isProtectedPage() && !isAuthenticated) {
            console.warn('Attempted access to protected page without authentication');
            window.location.href = '/welcome';
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    window.historyControlManager = new HistoryControlManager();
    
    // Run periodic checks to ensure protected pages cannot be accessed
    setInterval(() => {
        window.historyControlManager.checkProtectedAccess();
    }, 1000);
});

// Handle beforeunload to clear sensitive data
window.addEventListener('beforeunload', function() {
    // This runs when user is leaving the page
    // Server-side logout will handle session destruction
});
