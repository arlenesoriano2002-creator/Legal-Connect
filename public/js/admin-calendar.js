// Utility functions
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function formatPhoneForDisplay(phone) {
    if (!phone) return '';
    
    // Remove non-numeric characters
    phone = phone.replace(/\D/g, '');
    
    if (phone.length === 10) {
        return '(' + phone.substring(0, 3) + ') ' + phone.substring(3, 6) + '-' + phone.substring(6);
    } else if (phone.length === 11 && phone.startsWith('0')) {
        return '(' + phone.substring(1, 4) + ') ' + phone.substring(4, 7) + '-' + phone.substring(7);
    } else if (phone.length === 12 && phone.startsWith('63')) {
        return '+63 ' + phone.substring(2, 5) + ' ' + phone.substring(5, 8) + ' ' + phone.substring(8);
    }
    
    return phone;
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    
    // Check if date is valid
    if (isNaN(date.getTime())) {
        return 'Recently';
    }
    
    const seconds = Math.floor((now - date) / 1000);
    
    let interval = Math.floor(seconds / 31536000);
    if (interval >= 1) return interval + ' year' + (interval > 1 ? 's' : '') + ' ago';
    
    interval = Math.floor(seconds / 2592000);
    if (interval >= 1) return interval + ' month' + (interval > 1 ? 's' : '') + ' ago';
    
    interval = Math.floor(seconds / 86400);
    if (interval >= 1) return interval + ' day' + (interval > 1 ? 's' : '') + ' ago';
    
    interval = Math.floor(seconds / 3600);
    if (interval >= 1) return interval + ' hour' + (interval > 1 ? 's' : '') + ' ago';
    
    interval = Math.floor(seconds / 60);
    if (interval >= 1) return interval + ' minute' + (interval > 1 ? 's' : '') + ' ago';
    
    return 'Just now';
}

// Global notification functions
window.loadNotifications = function() {
    const notificationList = document.getElementById('notificationList');
    if (!notificationList) return;
    
    fetch('/admin/notifications/unread')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
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

function updateNotificationBadge(count) {
    const notificationBadge = document.getElementById('notificationBadge');
    if (notificationBadge) {
        notificationBadge.textContent = count;
        notificationBadge.style.display = count > 0 ? 'block' : 'none';
    }
}

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
        const timeAgo = formatTimeAgo(notification.created_at);
        const isUnread = !notification.is_read;
        
        html += `
            <div class="notification-item ${isUnread ? 'unread' : ''}" 
                 data-id="${notification.id}" 
                 onclick="markNotificationAsRead(${notification.id}, this)">
                <div class="notification-icon">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${escapeHtml(notification.title)}</div>
                    <div class="notification-message">${escapeHtml(notification.message)}</div>
                    <div class="notification-time">
                        <i class="far fa-clock"></i>
                        ${timeAgo}
                    </div>
                    <div class="notification-actions-row">
                        <button class="btn btn-sm btn-outline-primary see-more-btn" 
                                onclick="event.stopPropagation(); window.location.href='{{ route('clientstbl') }}'">
                            <i class="fas fa-external-link-alt"></i> See More
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    notificationList.innerHTML = html;
}

function showFallbackNotifications() {
    const notificationList = document.getElementById('notificationList');
    if (notificationList) {
        notificationList.innerHTML = `
            <div class="notification-empty">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Unable to load notifications</p>
                <small>Please check your connection</small>
            </div>
        `;
    }
}

// Mark notification as read
window.markNotificationAsRead = function(id, element) {
    fetch(`/admin/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (element) {
                element.classList.remove('unread');
            }
            updateNotificationBadge(data.unread_count);
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
};

// Mark all notifications as read
function markAllNotificationsAsRead() {
    fetch('/admin/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove unread class from all items
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
            updateNotificationBadge(0);
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

// Refresh notifications function
window.refreshNotifications = function() {
    loadNotifications();
};

// Simplified logout modal function without aria issues
function showLogoutModal() {
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
        this.removeAttribute('aria-modal');
    });
}

// Keyboard shortcut - use the button click instead of direct modal
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'q') {
        e.preventDefault();
        // Find and click the logout button
        const logoutBtn = document.querySelector('.logout-btn[onclick*="showLogoutModal"]');
        if (logoutBtn) {
            logoutBtn.click();
        } else {
            showLogoutModal();
        }
    }
});

// Global error handler for better debugging
window.addEventListener('error', function(e) {
    console.error('Global error caught:', e.message, 'at', e.filename, 'line', e.lineno);
    if (e.message.includes('cordonCalendar') || e.message.includes('switchView')) {
        console.error('cordonCalendar object state:', typeof cordonCalendar);
        console.error('cordonCalendar properties:', cordonCalendar ? Object.keys(cordonCalendar) : 'undefined');
    }
});

// Fix for the cordonCalendar.switchView issue
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, checking cordonCalendar...');
    
    // Check if cordonCalendar exists and has the right methods
    if (typeof window.cordonCalendar === 'undefined') {
        console.error('cordonCalendar is not defined globally. Check cordon-calendar.js file.');
        
        // Try to initialize it if it exists as a class/function
        if (typeof CordonCalendarManager !== 'undefined') {
            console.log('CordonCalendarManager found, creating instance...');
            window.cordonCalendar = new CordonCalendarManager();
        }
    } else {
        console.log('cordonCalendar found:', typeof window.cordonCalendar);
        console.log('cordonCalendar methods:', Object.keys(window.cordonCalendar));
    }

    // Add refresh button functionality
    const refreshButton = document.getElementById('refreshCalendars');
    if (refreshButton) {
        refreshButton.addEventListener('click', function() {
            refreshAllCalendars();
        });
    }
    
    // Simple sidebar toggle
    const menuToggle = document.getElementById('menu-toggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            document.getElementById('wrapper').classList.toggle('toggled');
        });
    }
    
    // Notification System Setup
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    
    // Toggle notification dropdown
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
            loadNotifications();
        });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (notificationBtn && notificationDropdown &&
            !notificationBtn.contains(e.target) && 
            !notificationDropdown.contains(e.target)) {
            notificationDropdown.classList.remove('show');
        }
    });
    
    // Mark all as read
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            markAllNotificationsAsRead();
        });
    }
    
    // Initialize notification system
    loadNotifications();
    
    // Real-time polling every 10 seconds
    setInterval(() => {
        if (!notificationDropdown.classList.contains('show')) {
            fetch('/admin/notifications/count')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const currentCount = parseInt(document.getElementById('notificationBadge').textContent);
                        if (data.unread_count > currentCount) {
                            loadNotifications();
                        }
                        updateNotificationBadge(data.unread_count);
                    }
                })
                .catch(error => {
                    console.error('Real-time polling error:', error);
                });
        }
    }, 10000); // 10 seconds
    
    // Tab switching functionality
    const viewTabs = document.querySelectorAll('.view-tab');
    
    viewTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const view = this.getAttribute('data-view');
            
            // Remove active class from all tabs
            viewTabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Hide all view panes
            document.querySelectorAll('.view-pane').forEach(pane => {
                pane.style.display = 'none';
            });
            
            // Show selected view
            if (view === 'cordon') {
                document.getElementById('cordonView').style.display = 'block';
                // Trigger Cordon calendar load if needed
                if (window.cordonCalendar) {
                    window.cordonCalendar.loadCordonMonthView();
                }
            } else {
                document.getElementById('monthView').style.display = 'block';
                // Trigger Diffun calendar load if needed
                if (window.calendarManager && typeof window.calendarManager.loadMonthView === 'function') {
                    window.calendarManager.loadMonthView();
                }
            }
        });
    });
    
    // FIX: Initialize both calendars properly
    console.log('Initializing calendars...');
    
    // Initialize Diffun calendar if it exists
    if (window.calendarManager && typeof window.calendarManager.loadMonthView === 'function') {
        window.calendarManager.loadMonthView();
    }
    
    // Initialize Cordon calendar if it exists
    if (window.cordonCalendar && typeof window.cordonCalendar.initialize === 'function') {
        setTimeout(() => {
            window.cordonCalendar.initialize();
        }, 100);
    }
});

// Function to close all floating/stuck modals
function closeAllFloatingModals() {
    // Close Bootstrap modals
    const openModals = document.querySelectorAll('.modal.show');
    openModals.forEach(modal => {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }
    });
    
    // Close any custom floating description modals
    const floatingModals = document.querySelectorAll('.floating-description, .date-description, .hover-modal, .tooltip, .date-tooltip');
    floatingModals.forEach(modal => {
        modal.style.display = 'none';
        modal.classList.remove('active', 'show');
    });
    
    // Remove any tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        const bsTooltip = bootstrap.Tooltip.getInstance(tooltip);
        if (bsTooltip) {
            bsTooltip.hide();
        }
    });
    
    // Remove any hover effects from calendar days
    const calendarDays = document.querySelectorAll('.calendar-day, .day-cell, .date-cell');
    calendarDays.forEach(day => {
        day.classList.remove('hover', 'active-hover', 'selected', 'hovered');
        // Reset any inline styles that might be causing hover effects
        day.style.backgroundColor = '';
        day.style.border = '';
        day.style.boxShadow = '';
    });
    
    // Hide any popovers
    const popovers = document.querySelectorAll('.popover');
    popovers.forEach(popover => {
        popover.style.display = 'none';
    });
    
    console.log('All floating modals and hover effects cleared');
}

// Function to refresh all calendars
function refreshAllCalendars() {
    console.log('Starting refresh of all calendars...');
    
    // Force cleanup of all tooltips first
    if (window.calendarManager && typeof window.calendarManager.forceCleanup === 'function') {
        window.calendarManager.forceCleanup();
    }
    
    if (window.cordonCalendar && typeof window.cordonCalendar.forceCleanup === 'function') {
        window.cordonCalendar.forceCleanup();
    }
    
    // First, close any floating/stuck modals
    closeAllFloatingModals();
    
    const refreshButton = document.getElementById('refreshCalendars');
    const icon = refreshButton.querySelector('i');
    
    // Add spinning animation
    icon.classList.add('refreshing');
    refreshButton.disabled = true;
    refreshButton.innerHTML = '<i class="fas fa-sync-alt"></i> Refreshing...';
    
    // Show loading message
    const messageContainer = document.getElementById('messageContainer');
    if (messageContainer) {
        messageContainer.innerHTML = '<div class="alert alert-info">Refreshing calendar data and clearing modals...</div>';
    }
    
    // Refresh Diffun Branch (Month View)
    try {
        console.log('Refreshing Diffun calendar...');
        if (window.calendarManager && typeof window.calendarManager.loadMonthView === 'function') {
            window.calendarManager.loadMonthView();
        } else {
            console.log('Diffun calendar refresh function not found');
        }
    } catch (error) {
        console.error('Error refreshing Diffun calendar:', error);
    }
    
    // Refresh Cordon Branch
    try {
        console.log('Refreshing Cordon calendar...');
        if (window.cordonCalendar && typeof window.cordonCalendar.loadCordonMonthView === 'function') {
            window.cordonCalendar.loadCordonMonthView();
        } else {
            console.log('Cordon calendar refresh function not found');
        }
    } catch (error) {
        console.error('Error refreshing Cordon calendar:', error);
    }
    
    // Clear any date descriptions or hover states
    clearDateHoverStates();
    
    // Remove spinning animation after delay
    setTimeout(function() {
        icon.classList.remove('refreshing');
        refreshButton.disabled = false;
        refreshButton.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
        
        // Show success message
        if (messageContainer) {
            messageContainer.innerHTML = '<div class="alert alert-success">Calendars refreshed successfully! All floating modals cleared.</div>';
            
            // Clear message after 3 seconds
            setTimeout(function() {
                messageContainer.innerHTML = '';
            }, 3000);
        }
        
        console.log('Refresh complete');
    }, 1500);
}

// Function to clear date hover states (for descriptions like "DISCRIPTION: Not set yet")
function clearDateHoverStates() {
    // Clear any date cell descriptions
    const dateCells = document.querySelectorAll('[data-description], [title*="DISCRIPTION"], [title*="Description"]');
    dateCells.forEach(cell => {
        cell.removeAttribute('title');
        cell.removeAttribute('data-description');
        cell.removeAttribute('data-original-title');
        
        // Remove any hover event listeners
        const newCell = cell.cloneNode(true);
        cell.parentNode.replaceChild(newCell, cell);
    });
    
    // Clear any date description elements
    const descriptionElements = document.querySelectorAll('.date-description, .day-description, .description-text');
    descriptionElements.forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    // Reset any hover tooltips
    const hoverElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    hoverElements.forEach(el => {
        el.setAttribute('data-bs-original-title', '');
        el.removeAttribute('title');
    });
    
    console.log('Date hover states and descriptions cleared');
}

// Function to update calendar visuals
function updateCalendarVisuals() {
    // Update Diffun calendar dates if visible
    const monthView = document.getElementById('monthView');
    if (monthView && monthView.style.display !== 'none') {
        const currentMonthYear = document.getElementById('currentMonthYear');
        if (currentMonthYear) {
            // This would trigger a visual update
            console.log('Refreshing Diffun calendar visuals...');
        }
    }
    
    // Update Cordon calendar dates if visible
    const cordonView = document.getElementById('cordonView');
    if (cordonView && cordonView.style.display !== 'none') {
        const cordonMonthYear = document.getElementById('cordonCurrentMonthYear');
        if (cordonMonthYear) {
            console.log('Refreshing Cordon calendar visuals...');
        }
    }
}

// Add keyboard shortcut for refresh (Ctrl+R or Cmd+R)
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        e.preventDefault();
        const refreshButton = document.getElementById('refreshCalendars');
        if (refreshButton) {
            refreshButton.click();
        }
    }
});

// Add to the <script> section in administrator.blade.php
function testCordonSlotNumbers() {
    if (typeof cordonCalendar !== 'undefined') {
        console.log('Testing Cordon slot numbers...');
        cordonCalendar.debugSlotInputs();
    } else {
        console.error('cordonCalendar is not defined');
    }
}

// Call this after modal opens
$(document).on('shown.bs.modal', '#colorSelectionModal', function() {
    setTimeout(() => {
        testCordonSlotNumbers();
    }, 500);
});

(function() {
    console.log('Attempting to fix menu toggle...');
    
    // Wait a bit to ensure DOM is ready
    setTimeout(function() {
        const menuButton = document.querySelector('#menu-toggle');
        
        if (menuButton) {
            console.log('Found menu button:', menuButton);
            
            // Remove ALL existing click events
            const newButton = menuButton.cloneNode(true);
            menuButton.parentNode.replaceChild(newButton, menuButton);
            
            // Add our click handler
            newButton.addEventListener('click', function() {
                console.log('Menu button clicked!');
                const wrapper = document.getElementById('wrapper');
                
                if (wrapper) {
                    const isToggled = wrapper.classList.toggle('toggled');
                    console.log('Toggled state:', isToggled);
                    
                    // Force a reflow to ensure CSS updates
                    void wrapper.offsetWidth;
                }
            }, true); // Use capture phase
            
            console.log('Menu toggle handler added');
        } else {
            console.error('Menu button not found!');
            
            // Try alternative selector
            const altButton = document.querySelector('button.btn-primary[class*="menu"], button[class*="toggle"]');
            if (altButton) {
                console.log('Found alternative button:', altButton);
                altButton.addEventListener('click', function() {
                    console.log('Alternative button clicked');
                    const wrapper = document.getElementById('wrapper');
                    if (wrapper) wrapper.classList.toggle('toggled');
                });
            }
        }
    }, 100);
})();

// FIX: Initialize both calendars when page loads
$(window).on('load', function() {
    console.log('Window loaded, initializing calendars...');
    
    // Give a moment for all scripts to load
    setTimeout(function() {
        // Initialize Diffun calendar
        if (window.calendarManager && typeof window.calendarManager.loadMonthView === 'function') {
            console.log('Initializing Diffun calendar...');
            window.calendarManager.loadMonthView();
        } else {
            console.warn('Diffun calendar manager not found or missing loadMonthView');
        }
        
        // Initialize Cordon calendar
        if (window.cordonCalendar && typeof window.cordonCalendar.initialize === 'function') {
            console.log('Initializing Cordon calendar...');
            window.cordonCalendar.initialize();
        } else {
            console.warn('Cordon calendar manager not found or missing initialize');
        }
        
        // Add click handlers for Cordon calendar dates
        $(document).on('click', '#cordonMonthGrid .day-cell:not(.past-date):not(.other-month)', function() {
            const date = $(this).data('date');
            if (date && window.cordonCalendar) {
                console.log('Cordon date clicked:', date);
                window.cordonCalendar.openModalForDate(date);
            }
        });
    }, 500);
});

function initializeMobileSidebar() {
    const menuToggle = document.getElementById('menu-toggle');
    const wrapper = document.getElementById('wrapper');
    
    if (!menuToggle || !wrapper) return;
    
    // Remove all existing click events
    const newToggle = menuToggle.cloneNode(true);
    menuToggle.parentNode.replaceChild(newToggle, menuToggle);
    
    // Add click handler
    newToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        
        wrapper.classList.toggle('toggled');
        
        // On mobile, if sidebar is open and we click outside, close it
        if (window.innerWidth <= 768) {
            if (wrapper.classList.contains('toggled')) {
                // Add overlay when sidebar is open on mobile
                let overlay = document.getElementById('sidebar-overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.id = 'sidebar-overlay';
                    overlay.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(0,0,0,0.5);
                        z-index: 1199;
                        display: none;
                    `;
                    document.body.appendChild(overlay);
                    
                    overlay.addEventListener('click', function() {
                        wrapper.classList.remove('toggled');
                        overlay.style.display = 'none';
                    });
                }
                overlay.style.display = 'block';
            } else {
                // Hide overlay
                const overlay = document.getElementById('sidebar-overlay');
                if (overlay) {
                    overlay.style.display = 'none';
                }
            }
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            // On desktop, ensure proper state
            const overlay = document.getElementById('sidebar-overlay');
            if (overlay) {
                overlay.style.display = 'none';
            }
        } else {
            // On mobile, close sidebar if open
            wrapper.classList.remove('toggled');
        }
    });
    
    // Close sidebar when clicking on links (mobile only)
    document.querySelectorAll('#sidebar-wrapper a').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                wrapper.classList.remove('toggled');
                const overlay = document.getElementById('sidebar-overlay');
                if (overlay) {
                    overlay.style.display = 'none';
                }
            }
        });
    });
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initializeMobileSidebar();
    
    // Also run on window load
    window.addEventListener('load', function() {
        initializeMobileSidebar();
    });
});