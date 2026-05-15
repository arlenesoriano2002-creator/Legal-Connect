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
        
// Auto-refresh data every 60 seconds
        setInterval(function() {
            // This would typically make an AJAX request to refresh the data
            console.log("Data refresh triggered");
            // In a real implementation, you would fetch updated data from the server
        }, 60000);

// ====================== STAFF NOTIFICATION SYSTEM ======================
document.addEventListener('DOMContentLoaded', function() {
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    
    // Initialize notification system
    if (window.loadNotifications) {
        loadNotifications();
    }
    
    // ======== TOGGLE NOTIFICATION DROPDOWN ========
    if (notificationBtn && notificationDropdown) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
            
            if (notificationDropdown.classList.contains('show') && window.loadNotifications) {
                loadNotifications();
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (notificationBtn && notificationDropdown &&
                !notificationBtn.contains(e.target) && 
                !notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.remove('show');
            }
        });
    }
    
    // ======== MARK ALL AS READ ========
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (window.markAllNotificationsAsRead) {
                markAllNotificationsAsRead();
            }
        });
    }
    
    // Start polling for new notifications
    if (window.startNotificationPolling) {
        startNotificationPolling();
    }
});

// Load staff notifications
window.loadNotifications = function() {
    const notificationList = document.getElementById('notificationList');
    if (!notificationList) return;
    
    console.log('Loading staff notifications...');
    
    // Try staff route first, fallback to regular route
    const staffRoute = '/staff/notifications/unread';
    const regularRoute = '/notifications/unread';
    
    fetch(staffRoute, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (response.status === 404) {
            // Fallback to regular notification route
            console.log('Staff route not found, trying regular route...');
            return fetch(regularRoute, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
        }
        return response;
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Notifications data:', data);
        if (data.success) {
            updateNotificationBadge(data.unread_count);
            renderNotifications(data.notifications);
        } else {
            console.error('Notification error:', data.error || 'Unknown error');
            showFallbackNotifications();
        }
    })
    .catch(error => {
        console.error('Error loading notifications:', error);
        showFallbackNotifications();
    });
};

// Update notification badge
function updateNotificationBadge(count) {
    const notificationBadge = document.getElementById('notificationBadge');
    if (notificationBadge) {
        notificationBadge.textContent = count;
        notificationBadge.style.display = count > 0 ? 'flex' : 'none';
    }
}

// Render notifications
function renderNotifications(notifications) {
    const notificationList = document.getElementById('notificationList');
    if (!notificationList) return;
    
    if (!notifications || notifications.length === 0) {
        notificationList.innerHTML = `
            <div class="notification-empty">
                <i class="fas fa-bell-slash"></i>
                <p>No new notifications</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    notifications.forEach(notification => {
        const timeAgo = notification.time_ago || 'Recently';
        const isUnread = !notification.is_read;
        
        html += `
            <div class="notification-item ${isUnread ? 'unread' : ''}" 
                 data-id="${notification.id}" 
                 onclick="markNotificationAsRead(${notification.id}, this)">
                <div class="notification-icon">
                    <i class="fas fa-${getNotificationIcon(notification.type)}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${escapeHtml(notification.title)}</div>
                    <div class="notification-message">${escapeHtml(notification.message)}</div>
                    <div class="notification-time">
                        <i class="far fa-clock"></i>
                        ${timeAgo}
                    </div>
                    ${notification.appointment ? `
                    <div class="notification-actions-row">
                        <button class="btn btn-sm btn-outline-primary see-more-btn" 
                                onclick="event.stopPropagation(); window.location.href='/appointments'">
                            <i class="fas fa-external-link-alt"></i> View Appointment
                        </button>
                    </div>
                    ` : ''}
                </div>
                ${isUnread ? '<span class="unread-dot"></span>' : ''}
            </div>
        `;
    });
    
    notificationList.innerHTML = html;
}

// Get appropriate icon for notification type
function getNotificationIcon(type) {
    const icons = {
        'pending_request': 'calendar-plus',
        'appointment_update': 'calendar-check',
        'system': 'bell',
        'message': 'envelope',
        'test': 'bell'
    };
    return icons[type] || 'bell';
}

// Mark notification as read
window.markNotificationAsRead = function(id, element) {
    fetch(`/staff/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (element) {
                element.classList.remove('unread');
                const unreadDot = element.querySelector('.unread-dot');
                if (unreadDot) {
                    unreadDot.remove();
                }
            }
            updateNotificationBadge(data.unread_count);
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
};

// Mark all notifications as read
window.markAllNotificationsAsRead = function() {
    fetch('/staff/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
                const unreadDot = item.querySelector('.unread-dot');
                if (unreadDot) {
                    unreadDot.remove();
                }
            });
            updateNotificationBadge(0);
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
};

// Start polling
window.startNotificationPolling = function() {
    setInterval(() => {
        const notificationDropdown = document.getElementById('notificationDropdown');
        
        if (!notificationDropdown || !notificationDropdown.classList.contains('show')) {
            fetch('/staff/notifications/count', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationBadge(data.unread_count);
                }
            })
            .catch(error => {
                console.error('Polling error:', error);
            });
        }
    }, 30000);
};

// Utility functions
function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function showFallbackNotifications() {
    const notificationList = document.getElementById('notificationList');
    if (notificationList) {
        notificationList.innerHTML = `
            <div class="notification-empty">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Unable to load notifications</p>
                <small>Please try again later</small>
            </div>
        `;
    }
}

// Test function
window.testStaffNotification = function() {
    fetch('/staff/notifications/test', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Test staff notification result:', data);
        if (data.success) {
            alert('Test staff notification created!');
            loadNotifications();
        }
    })
    .catch(error => {
        console.error('Test staff notification error:', error);
    });
};