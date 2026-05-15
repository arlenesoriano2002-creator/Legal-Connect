


<?php if(auth()->guard()->check()): ?>
    
    <script src="<?php echo e(asset('js/inactivity-logout-manager.js')); ?>"></script>
    
    <script>
        // Initialize the inactivity logout manager when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Create instance with configurable timeout
            const inactivityManager = new InactivityLogoutManager({
                // Logout after 15 minutes of inactivity (adjust as needed)
                timeoutMinutes: <?php echo e(config('session.inactivity_timeout', 15)); ?>,
                
                // Show warning 2 minutes before logout
                warningMinutes: <?php echo e(config('session.inactivity_warning', 2)); ?>,
                
                // Check inactivity every 10 seconds
                checkIntervalSeconds: 10,
                
                // API endpoints
                logoutEndpoint: '<?php echo e(route("logout")); ?>',
                sessionStatusEndpoint: '<?php echo e(route("session.status")); ?>'
            });

            // Optional: Make manager available globally for manual logout
            window.inactivityManager = inactivityManager;
            
            // Optional: Log initialization
            console.log('Inactivity logout manager initialized successfully');
        });
    </script>
<?php endif; ?>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\partials\inactivity-logout-manager.blade.php ENDPATH**/ ?>