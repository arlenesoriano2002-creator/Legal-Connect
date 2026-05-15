{{-- Inactivity Logout Manager Partial --}}
{{-- Include this in your base layout or individual pages where you want inactivity timeout --}}

@auth
    {{-- Include the inactivity manager script --}}
    <script src="{{ asset('js/inactivity-logout-manager.js') }}"></script>
    
    <script>
        // Initialize the inactivity logout manager when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Create instance with configurable timeout
            const inactivityManager = new InactivityLogoutManager({
                // Logout after 15 minutes of inactivity (adjust as needed)
                timeoutMinutes: {{ config('session.inactivity_timeout', 15) }},
                
                // Show warning 2 minutes before logout
                warningMinutes: {{ config('session.inactivity_warning', 2) }},
                
                // Check inactivity every 10 seconds
                checkIntervalSeconds: 10,
                
                // API endpoints
                logoutEndpoint: '{{ route("logout") }}',
                sessionStatusEndpoint: '{{ route("session.status") }}'
            });

            // Optional: Make manager available globally for manual logout
            window.inactivityManager = inactivityManager;
            
            // Optional: Log initialization
            console.log('Inactivity logout manager initialized successfully');
        });
    </script>
@endauth
