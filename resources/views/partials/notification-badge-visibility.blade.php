<style>
    .notification-badge-hidden #notificationBadge,
    .notification-badge-hidden #notificationUnreadBadge,
    .notification-badge-hidden #diffunNotificationBadge,
    .notification-badge-hidden #cordonNotificationBadge,
    .notification-badge-hidden #notificationIndicator,
    .notification-badge-hidden .notification-indicator,
    .notification-badge-hidden .notification-badge {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }
</style>

<script>
    (function() {
        const HIDDEN_CLASS = 'notification-badge-hidden';
        const ADMIN_NOTIFICATION_PREFIX = '/admin/notifications';

        if (typeof window.getAdminNotificationRequestConfig !== 'function') {
            window.getAdminNotificationRequestConfig = function(method = 'GET', existingHeaders = {}) {
                const headers = new Headers(existingHeaders || {});
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const tabToken = sessionStorage.getItem('legal_connect_tab_token');

                if (!headers.has('Accept')) {
                    headers.set('Accept', 'application/json');
                }

                if (!headers.has('X-Requested-With')) {
                    headers.set('X-Requested-With', 'XMLHttpRequest');
                }

                if (csrfToken && !headers.has('X-CSRF-TOKEN')) {
                    headers.set('X-CSRF-TOKEN', csrfToken);
                }

                if (tabToken && !headers.has('X-Tab-Token')) {
                    headers.set('X-Tab-Token', tabToken);
                }

                if (method.toUpperCase() !== 'GET' && !headers.has('Content-Type')) {
                    headers.set('Content-Type', 'application/json');
                }

                return {
                    method,
                    credentials: 'same-origin',
                    headers
                };
            };
        }

        if (!window.__adminNotificationFetchPatched) {
            const originalFetch = window.fetch.bind(window);

            window.fetch = function(resource, config = {}) {
                const requestUrl = typeof resource === 'string'
                    ? resource
                    : (resource && typeof resource.url === 'string' ? resource.url : '');

                if (!requestUrl.includes(ADMIN_NOTIFICATION_PREFIX)) {
                    return originalFetch(resource, config);
                }

                const method = (config.method || (resource && resource.method) || 'GET').toUpperCase();
                const requestConfig = window.getAdminNotificationRequestConfig(
                    method,
                    config.headers || (resource && resource.headers)
                );

                return originalFetch(resource, {
                    ...config,
                    ...requestConfig,
                });
            };

            window.__adminNotificationFetchPatched = true;
        }

        function getNotificationElements() {
            const dropdown = document.getElementById('notificationDropdown');
            const button = document.getElementById('notificationBtn') || document.querySelector('.notification-icon-btn');

            if (!dropdown) {
                return null;
            }

            const container = dropdown.parentElement || (button ? button.parentElement : null);

            if (!container) {
                return null;
            }

            return { dropdown, button, container };
        }

        function isDropdownOpen(dropdown) {
            if (!dropdown) {
                return false;
            }

            if (dropdown.classList.contains('show') || dropdown.classList.contains('active') || dropdown.classList.contains('open')) {
                return true;
            }

            const computedStyle = window.getComputedStyle(dropdown);
            const hasDimensions = dropdown.offsetWidth > 0 || dropdown.offsetHeight > 0 || dropdown.getClientRects().length > 0;

            return hasDimensions &&
                computedStyle.display !== 'none' &&
                computedStyle.visibility !== 'hidden' &&
                computedStyle.opacity !== '0';
        }

        function applyBadgeVisibility() {
            const elements = getNotificationElements();

            if (!elements) {
                return;
            }

            const open = isDropdownOpen(elements.dropdown);
            elements.container.classList.toggle(HIDDEN_CLASS, open);

            // When the dropdown is opened, ensure badge is hidden immediately and
            // mark notifications as read on the server (if available).
            try {
                if (open && elements.dropdown.dataset.openMarked !== '1') {
                    // Prevent repeated marking while dropdown remains open
                    elements.dropdown.dataset.openMarked = '1';

                    if (typeof window.updateNotificationBadge === 'function') {
                        try { window.updateNotificationBadge(0); } catch (e) { /* ignore */ }
                    }
                    if (typeof window.markAllNotificationsAsRead === 'function') {
                        try { window.markAllNotificationsAsRead(); } catch (e) { /* ignore */ }
                    }
                }
                // When dropdown closed, keep badge hidden until new notifications arrive.
            } catch (err) {
                console.error('Error applying badge visibility actions', err);
            }
        }

        function bindNotificationBadgeVisibility() {
            const elements = getNotificationElements();

            if (!elements) {
                return false;
            }

            if (elements.dropdown.dataset.badgeVisibilityBound === '1') {
                applyBadgeVisibility();
                return true;
            }

            elements.dropdown.dataset.badgeVisibilityBound = '1';

            const scheduleApply = function() {
                window.requestAnimationFrame(applyBadgeVisibility);
            };

            const dropdownObserver = new MutationObserver(scheduleApply);
            dropdownObserver.observe(elements.dropdown, {
                attributes: true,
                attributeFilter: ['class', 'style', 'aria-expanded']
            });

            if (elements.button) {
                elements.button.addEventListener('click', function() {
                    setTimeout(scheduleApply, 0);
                });
            }

            elements.dropdown.addEventListener('click', function() {
                setTimeout(scheduleApply, 0);
            });

            document.addEventListener('click', function() {
                setTimeout(scheduleApply, 0);
            });

            window.addEventListener('load', scheduleApply);
            window.addEventListener('resize', scheduleApply);

            scheduleApply();
            return true;
        }

        function initializeNotificationBadgeVisibility() {
            if (bindNotificationBadgeVisibility()) {
                return;
            }

            if (!document.body) {
                return;
            }

            const bodyObserver = new MutationObserver(function() {
                if (bindNotificationBadgeVisibility()) {
                    bodyObserver.disconnect();
                }
            });

            bodyObserver.observe(document.body, {
                childList: true,
                subtree: true
            });

            window.setTimeout(function() {
                bodyObserver.disconnect();
            }, 15000);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeNotificationBadgeVisibility);
        } else {
            initializeNotificationBadgeVisibility();
        }
    })();
</script>
