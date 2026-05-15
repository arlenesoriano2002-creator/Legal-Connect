 // ====================== SIDEBAR TOGGLE ======================
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle
            const menuToggle = document.getElementById('menu-toggle');
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    document.getElementById('wrapper').classList.toggle('toggled');
                });
            }
        });

        // ====================== LOGOUT MODAL ======================
        function showLogoutModal() {
            // Create modal instance
            const modalElement = document.getElementById('logoutConfirmationModal');
            
            // Remove any aria-hidden attributes that might conflict
            modalElement.removeAttribute('aria-hidden');
            modalElement.setAttribute('aria-modal', 'true');
            
            // Use Bootstrap's modal properly
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: 'static',
                keyboard: true,
                focus: true
            });
            
            // Show modal
            modal.show();
            
            // Listen for modal events to fix aria attributes
            modalElement.addEventListener('shown.bs.modal', function() {
                // Ensure proper accessibility
                this.removeAttribute('aria-hidden');
                this.setAttribute('aria-modal', 'true');
                
                // Focus on the cancel button
                setTimeout(() => {
                    const cancelBtn = this.querySelector('.btn-secondary');
                    if (cancelBtn) {
                        cancelBtn.focus();
                    }
                }, 100);
            });
            
            modalElement.addEventListener('hidden.bs.modal', function() {
                // When hidden, let Bootstrap handle aria-hidden
                this.removeAttribute('aria-modal');
            });
        }

        // Keyboard shortcut (Ctrl+Q) for logout
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'q') {
                e.preventDefault();
                // Find and click the logout button
                const logoutBtn = document.querySelector('.logout-btn[onclick*="showLogoutModal"]');
                if (logoutBtn) {
                    logoutBtn.click();
                } else {
                    // Fallback to calling the function directly
                    showLogoutModal();
                }
            }
        });