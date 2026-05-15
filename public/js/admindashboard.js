// ====================== UTILITY FUNCTIONS ======================
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

// ====================== APPOINTMENT NOTIFICATION FUNCTIONS ======================
function adminNotificationsEnabled() {
    return !!(window.LegalConnect && window.LegalConnect.adminNotificationsEnabled);
}

function getAdminNotificationRoute(key) {
    return window.LegalConnect?.routes?.[key] || null;
}

window.loadNotifications = function() {
    if (!adminNotificationsEnabled()) return;

    const notificationList = document.getElementById('notificationList');
    if (!notificationList) return;
    
    const unreadRoute = getAdminNotificationRoute('adminNotificationsUnread');
    if (!unreadRoute) return;

    fetch(unreadRoute)
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
                                onclick="event.stopPropagation(); window.location.href='${window.LegalConnect.routes.clientstbl}'">
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
    if (!adminNotificationsEnabled()) return;

    const baseRoute = getAdminNotificationRoute('adminNotificationsBase');
    if (!baseRoute) return;

    fetch(`${baseRoute}/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.LegalConnect.csrfToken,
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
    if (!adminNotificationsEnabled()) return;

    const markAllRoute = getAdminNotificationRoute('adminNotificationsMarkAllRead');
    if (!markAllRoute) return;

    fetch(markAllRoute, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.LegalConnect.csrfToken,
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

// ====================== TEST FUNCTIONS ======================
window.testNotification = function() {
    console.log('Testing notification creation...');
    
    // Test creating a notification via API
    fetch('/test-notification', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': window.LegalConnect.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Test notification result:', data);
        if (data.success) {
            alert('Test notification created! Check the bell icon.');
            if (typeof loadNotifications === 'function') {
                loadNotifications();
            } else {
                console.warn('loadNotifications function not found');
            }
        } else {
            alert('Error: ' + (data.message || 'Failed to create test notification'));
        }
    })
    .catch(error => {
        console.error('Test notification error:', error);
        alert('Error: ' + error.message);
    });
};

// ====================== OTHER FUNCTIONS ======================
// Simplified logout modal function
function showLogoutModal() {
    // Create modal instance
    const modalElement = document.getElementById('logoutConfirmationModal');
    if (!modalElement) return;
    
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

// Refresh notifications function
window.refreshNotifications = function() {
    loadNotifications();
};

// Backup card refresh function
window.refreshBackupCards = function () {
    fetch('/admin/backups/refresh')
        .then(res => res.json())
        .then(data => {
            document.getElementById('backupCardsContainer').outerHTML = data.html;
            // Re-attach filter event listener after refresh
            const newFilter = document.getElementById('backupFilter');
            if (newFilter) {
                newFilter.addEventListener('change', function() {
                    const filterValue = this.value;
                    filterBackupCards(filterValue);
                });
            }
        })
        .catch(err => console.log(err));
};

// Filter functionality for archive modal
function filterBackupCards(filterValue) {
    const backupCards = document.querySelectorAll('.backup-card');
    
    backupCards.forEach(card => {
        const fileName = card.querySelector('.backup-name').textContent.toLowerCase();
        
        if (filterValue === 'all') {
            card.style.display = 'flex';
        } else if (fileName.includes(filterValue)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });

    // Show empty message if no cards are visible
    const visibleCards = document.querySelectorAll('.backup-card[style="display: flex"]');
    const emptyMessage = document.querySelector('.backup-empty-message');
    
    if (visibleCards.length === 0 && emptyMessage) {
        emptyMessage.style.display = 'block';
    } else if (emptyMessage) {
        emptyMessage.style.display = 'none';
    }
}

// Function to update top bar position
function updateTopBarPosition() {
    const sidebarWrapper = document.getElementById('sidebar-wrapper');
    const topBar = document.querySelector('.top-bar');
    if (!sidebarWrapper || !topBar) return;
    
    if (sidebarWrapper.offsetWidth === 70) {
        topBar.style.left = '70px';
    } else if (sidebarWrapper.offsetWidth === 220) {
        topBar.style.left = '220px';
    } else {
        topBar.style.left = '0';
    }
}

// Chat functions
window.selectUser = function(id, name, email) {
    const receiverId = document.getElementById('receiverId');
    const noMessageText = document.getElementById('noMessageText');

    if (receiverId) {
        receiverId.value = id;
    }
    if (noMessageText) {
        noMessageText.innerText = "Messaging " + name;
    }
};

window.filterUsers = function() {
    const searchInput = document.getElementById('searchUser');
    if (!searchInput) return;

    const search = searchInput.value.toLowerCase();
    document.querySelectorAll('.user-item').forEach(user => {
        user.style.display = user.innerText.toLowerCase().includes(search) ? '' : 'none';
    });
};

window.toggleChatPanel = function() {
    const chatPanel = document.getElementById("chatPanel");
    if (chatPanel) {
        chatPanel.style.display = chatPanel.style.display === "block" ? "none" : "block";
    }
};

// ====================== DOM CONTENT LOADED ======================
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded');

    if (typeof PerTabAuthManager !== 'undefined') {
        PerTabAuthManager.init();
    }
    
    // Debug: Check if notification elements exist
    console.log('Notification button exists:', !!document.getElementById('notificationBtn'));
    console.log('Notification dropdown exists:', !!document.getElementById('notificationDropdown'));
    console.log('Notification badge exists:', !!document.getElementById('notificationBadge'));
    
    // ======== SIMPLE SIDEBAR TOGGLE ========
    const menuToggle = document.getElementById('menu-toggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            document.getElementById('wrapper').classList.toggle('toggled');
        });
    }

    // Modal functionality
    const modalArchive = document.getElementById("archiveModal");
    const modalBackup = document.getElementById("backupModal");
    const btnViewBackups = document.getElementById("btnViewBackups");

    if (btnViewBackups && modalBackup) {
        btnViewBackups.onclick = () => modalBackup.showModal();
    }

    // Close modal buttons
    document.querySelectorAll(".close-modal").forEach(btn => {
        btn.addEventListener("click", function(e) {
            const dialog = e.target.closest("dialog");
            if (dialog) dialog.close();
        });
    });

    // Create Backup
    const createBackupBtn = document.getElementById("createBackupBtn");
    if (createBackupBtn) {
        createBackupBtn.onclick = function() {
            fetch(window.LegalConnect.routes.adminCreateBackup, {
                method: "POST",
                headers: { "X-CSRF-TOKEN": window.LegalConnect.csrfToken }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (window.refreshBackupCards) {
                        refreshBackupCards();
                    }
                    const toast = document.getElementById("backupSuccessToast");
                    if (toast) {
                        toast.classList.add("show");
                        setTimeout(() => toast.classList.remove("show"), 2500);
                    }
                }
            });
        };
    }

    // Auto-open archive modal if needed
    if (window.LegalConnect.keepArchiveOpen && modalArchive) {
        modalArchive.showModal();
    }

    // Feedback chart
    const feedbackCtx = document.getElementById("feedbackBarChart");
    if (feedbackCtx) {
        fetch("/feedback-data")
            .then(response => response.json())
            .then(chartData => {
                new Chart(feedbackCtx, {
                    type: "bar",
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: "Number of Ratings",
                            data: chartData.data,
                            borderWidth: 1,
                            backgroundColor: [
                                "#f72585",
                                "#ff9e01", 
                                "#4cc9f0",
                                "#4895ef",
                                "#550b92"
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            title: {
                                display: true,
                                text: "Feedback Ratings Distribution",
                                font: { size: 16 }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1 }
                            }
                        }
                    }
                });
            })
            .catch(error => console.error("Error loading feedback data:", error));
    }

    // Auto-refresh data every 60 seconds
    setInterval(function() {
        console.log("Data refresh triggered");
    }, 60000);
    
    // Filter functionality for archive modal
    const backupFilter = document.getElementById('backupFilter');
    
    // Filter backups when dropdown changes
    if (backupFilter) {
        backupFilter.addEventListener('change', function() {
            const filterValue = this.value;
            filterBackupCards(filterValue);
        });
    }

    // ======== APPOINTMENT NOTIFICATION SYSTEM SETUP ========
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
    
    // Start polling for new notifications
    function startNotificationPolling() {
        if (!adminNotificationsEnabled()) return;

        // Check for new notifications every 30 seconds
        setInterval(() => {
            if (notificationDropdown && !notificationDropdown.classList.contains('show')) {
                const countRoute = getAdminNotificationRoute('adminNotificationsCount');
                if (!countRoute) return;

                fetch(countRoute)
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
        }, 30000); // 30 seconds
    }
    
    // Real-time polling every 10 seconds
    function startRealTimePolling() {
        if (!adminNotificationsEnabled()) return;

        setInterval(() => {
            if (notificationDropdown && !notificationDropdown.classList.contains('show')) {
                const countRoute = getAdminNotificationRoute('adminNotificationsCount');
                const notificationBadge = document.getElementById('notificationBadge');
                if (!countRoute || !notificationBadge) return;

                fetch(countRoute)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const currentCount = parseInt(notificationBadge.textContent || '0', 10);
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
    }
    
    // Initialize notification system
    if (adminNotificationsEnabled()) {
        loadNotifications();
        startRealTimePolling();
    }
    
    // Add event listener for new appointments (real-time simulation)
    window.addEventListener('storage', function(e) {
        if (e.key === 'new_appointment_notification') {
            loadNotifications();
        }
    });
    
    // Sidebar close button functionality
    const sidebarCloseBtn = document.getElementById('sidebar-close-btn');
    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('wrapper').classList.remove('toggled');
            // Also remove the body class if you added one
            document.body.classList.remove('sidebar-open');
        });
    }

    // Close sidebar when clicking outside on mobile backdrop
    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('wrapper');
        const sidebar = document.getElementById('sidebar-wrapper');
        const menuToggle = document.getElementById('menu-toggle');
        
        if (window.innerWidth <= 768 && 
            wrapper.classList.contains('toggled') && 
            !sidebar.contains(e.target) && 
            !menuToggle.contains(e.target) &&
            e.target !== sidebar) {
            
            wrapper.classList.remove('toggled');
            document.body.classList.remove('sidebar-open');
        }
    });
    
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
});

// ====================== PUSHER REAL-TIME NOTIFICATIONS ======================
document.addEventListener('DOMContentLoaded', function() {
    if (!adminNotificationsEnabled()) {
        return;
    }

    // Initialize Pusher for real-time message notifications
    const pusherKey = String(window.LegalConnect.pusherKey || '').trim().toLowerCase();
    const canUsePusher = typeof Pusher !== 'undefined' &&
        window.LegalConnect.broadcastDriver === 'pusher' &&
        pusherKey !== '' &&
        pusherKey !== 'your_app_key' &&
        pusherKey !== 'null';

    if (canUsePusher) {
        try {
            const pusher = new Pusher(window.LegalConnect.pusherKey, {
                cluster: window.LegalConnect.pusherCluster,
                forceTLS: true,
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': window.LegalConnect.csrfToken
                    }
                }
            });
            
            // Subscribe to admin's private notification channel
            const channelName = 'private-admin-message-notifications.' + window.LegalConnect.authId;
            const channel = pusher.subscribe(channelName);
            
            // Listen for new message notifications
            channel.bind('new-admin-message-notification', function(data) {
                console.log('New message notification received:', data);
                
                // Update badge count immediately
                const badge = document.getElementById('messageNotificationBadge');
                if (badge) {
                    badge.textContent = data.unread_count;
                    badge.style.display = data.unread_count > 0 ? 'inline-block' : 'none';
                }
                
                // If message notification dropdown is open, refresh notifications
                const dropdown = document.getElementById('messageNotificationDropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    loadMessageNotifications();
                }
                
                // Show toast notification
                showMessageNotificationToast(data.notification);
                
                // Flash the notification button to draw attention
                flashMessageNotificationButton();
            });
            
            console.log('Pusher initialized for message notifications on channel:', channelName);
            
        } catch (error) {
            console.error('Pusher initialization error:', error);
        }
    } else {
        console.warn('Pusher real-time notifications disabled for current broadcasting configuration.');
    }
    
    // Function to show toast notification
    function showMessageNotificationToast(notification) {
        // Check if browser is focused - only show toast if not focused
        if (document.hasFocus()) {
            return;
        }
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = 'notification-toast';
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-left: 4px solid #007bff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            max-width: 350px;
            animation: slideIn 0.3s ease;
        `;
        
        toast.innerHTML = `
            <div style="display: flex; align-items: flex-start; gap: 10px;">
                <div style="background: #007bff; color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-comment"></i>
                </div>
                <div style="flex: 1;">
                    <strong style="display: block; margin-bottom: 5px; color: #333;">${notification.title}</strong>
                    <div style="color: #666; font-size: 14px; margin-bottom: 8px;">${notification.message}</div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <small style="color: #999;">Just now</small>
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" style="background: none; border: none; color: #999; cursor: pointer;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 5000);
        
        // Add click handler to open appropriate chat
        toast.addEventListener('click', function() {
            const type = notification.type || 'system_chat';
            let url = '';
            
            switch(type) {
                case 'system_chat':
                    url = window.LegalConnect.routes.adminSystemChat;
                    break;
                case 'email':
                    url = window.LegalConnect.routes.messagesEmail;
                    break;
                case 'sms':
                    url = window.LegalConnect.routes.messagesSms;
                    break;
                default:
                    url = window.LegalConnect.routes.adminSystemChat;
            }
            
            window.location.href = url;
        });
    }
    
    // Function to flash the message notification button
    function flashMessageNotificationButton() {
        const button = document.getElementById('messageNotificationBtn');
        if (button) {
            // Add flashing animation
            button.style.animation = 'none';
            setTimeout(() => {
                button.style.animation = 'flash 1s 3';
            }, 10);
        }
    }
    
    // Add CSS for flash animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes flash {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
    
    // Request notification permission
    if ("Notification" in window && Notification.permission === "default") {
        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                console.log("Desktop notification permission granted");
            }
        });
    }
});

// Add CSS styles for message notification dropdown with proper dropdown animation
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    .notification-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        width: 400px;
        max-height: 500px;
        overflow-y: auto;
        z-index: 1050;
        display: none;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform-origin: top right;
        opacity: 0;
        transform: translateY(-10px);
    }
    
    .notification-dropdown.show {
        display: block;
        animation: dropdownFadeIn 0.15s ease 0.15s forwards;
    }
    
    @keyframes dropdownFadeIn {
        0% {
            opacity: 0;
            transform: translateY(-10px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .notification-header {
        padding: 12px 16px;
        border-bottom: 1px solid #dee2e6;
        background-color: #f8f9fa;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .notification-header h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }
    
    .notification-actions {
        display: flex;
        gap: 8px;
    }
    
    .notification-list {
        max-height: 350px;
        overflow-y: auto;
    }
    
    .notification-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f1f1;
        cursor: pointer;
        transition: background-color 0.2s;
        position: relative;
        display: flex;
        align-items: flex-start;
    }
    
    .notification-item:hover {
        background-color: #f8f9fa;
    }
    
    .notification-item.unread {
        background-color: #f0f9ff;
    }
    
    .notification-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        flex-shrink: 0;
    }
    
    .notification-content {
        flex: 1;
        min-width: 0;
    }
    
    .notification-title {
        font-weight: 600;
        margin-bottom: 4px;
        color: #333;
        word-break: break-word;
    }
    
    .notification-message {
        color: #666;
        font-size: 14px;
        margin-bottom: 4px;
        word-break: break-word;
    }
    
    .notification-time {
        color: #999;
        font-size: 12px;
        margin-bottom: 8px;
    }
    
    .notification-empty {
        padding: 40px 20px;
        text-align: center;
        color: #999;
    }
    
    .notification-empty i {
        font-size: 48px;
        margin-bottom: 16px;
        color: #dee2e6;
    }
    
    .notification-footer {
        padding: 12px 16px;
        border-top: 1px solid #dee2e6;
        background-color: #f8f9fa;
    }
    
    .unread-dot {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 8px;
        height: 8px;
        background-color: #007bff;
        border-radius: 50%;
        flex-shrink: 0;
    }
    
    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        display: none;
    }
    
    .notification-container {
        position: relative;
    }
    
    .notification-btn:hover {
        color: #007bff;
    }
    
    .notification-btn .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .notification-actions-row {
        margin-top: 8px;
    }
    
    .see-more-btn {
        padding: 4px 8px;
        font-size: 12px;
    }
`;
document.head.appendChild(notificationStyles);
