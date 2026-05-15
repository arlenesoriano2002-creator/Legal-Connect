<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Pending Requests - LegalConnect</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/clientstbl.blade.css') }}">
</head>
<body>
    <style>
   
    </style>

    <div id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">
                <img src="{{ asset('logo6.png') }}" alt="LegalConnect logo" width="40" height="40" style="border-radius: 50%;">
                <span>LegalConnect</span>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ url('/admindashboard') }}" class="list-group-item list-group-item-action {{ request()->is('admindashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="{{ url('/administrator') }}" class="list-group-item list-group-item-action {{ request()->is('administrator') ? 'active' : '' }}">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Set Time</span>
                </a>
                 <a href="{{ url('/appointments') }}" class="list-group-item list-group-item-action {{ request()->is('appointments') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Logs Requests</span>
                </a>
                <a href="{{ route('admin.walkins') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.walkins') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard" style="color: #cdd3df;"></i>
                    <span>Walk-Ins logs</span>
                </a>
                <a href="{{ url('/statistics') }}" class="list-group-item list-group-item-action {{ request()->is('statistics') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>Statistics</span>
                </a>
                <a href="#messagesSubmenu" 
                class="list-group-item list-group-item-action {{ request()->is('email-chat') || request()->is('messages/*') ? 'active' : '' }}"
                data-bs-toggle="collapse" 
                aria-expanded="{{ request()->is('email-chat') || request()->is('messages/*') ? 'true' : 'false' }}">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu collapse {{ request()->is('email-chat') || request()->is('messages/*') ? 'show' : '' }} list-group" id="messagesSubmenu">
                    <a href="{{ route('messages.email') }}" class="list-group-item list-group-item-action {{ request()->is('email-chat') ? 'active' : '' }}">
                        <i class="fas fa-envelope"></i>
                        <span>Email</span>
                    </a>
                    <a href="{{ route('messages.sms') }}" class="list-group-item list-group-item-action {{ request()->is('sms-chat') ? 'active' : '' }}">
                        <i class="fas fa-sms"></i>
                        <span>SMS</span>
                    </a>
                    <a href="{{ route('admin.system-chat') }}" class="list-group-item list-group-item-action {{ request()->is('admin/system-chat') ? 'active' : '' }}">
                        <i class="fas fa-comments"></i>
                        <span>System Chatting</span>
                    </a>
                </div>
                <a href="{{ url('/practice-areas') }}" class="list-group-item list-group-item-action {{ request()->is('practice-areas') ? 'active' : '' }}">
                    <i class="fa-solid fa-suitcase"></i>
                    <span>Services</span>
                </a>
                <!--<a href="{{ url('/messages') }}" class="list-group-item list-group-item-action {{ request()->is('messages') ? 'active' : '' }}">
                    <i class="fas fa-comments"></i>
                    <span>Messages</span>
                </a>-->

                <a href="#requestsSubmenu" class="list-group-item list-group-item-action {{ request()->is('clientstbl') || request()->is('adminAcceptedRequest') || request()->is('adminDeniedRequest') ? 'active' : '' }}" data-bs-toggle="collapse" aria-expanded="{{ request()->is('clientstbl') || request()->is('adminAcceptedRequest') || request()->is('adminDeniedRequest') ? 'true' : 'false' }}">
                    <i class="fas fa-list-alt"></i>
                    <span>Appointment Requests</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu collapse {{ request()->is('clientstbl') || request()->is('adminAcceptedRequest') || request()->is('adminDeniedRequest') ? 'show' : '' }} list-group" id="requestsSubmenu">
                    <a href="{{ url('/clientstbl') }}" class="list-group-item list-group-item-action {{ request()->is('clientstbl') ? 'active' : '' }}">
                        <i class="fas fa-clock"></i>
                        <span>Pending Requests</span>
                    </a>
                    <a href="{{ url('/adminAcceptedRequest') }}" class="list-group-item list-group-item-action {{ request()->is('adminAcceptedRequest') ? 'active' : '' }}">
                        <i class="fas fa-check-circle"></i>
                        <span>Accepted Requests</span>
                    </a>
                    <a href="{{ url('/adminDeniedRequest') }}" class="list-group-item list-group-item-action {{ request()->is('adminDeniedRequest') ? 'active' : '' }}">
                        <i class="fas fa-times-circle"></i>
                        <span>Denied Requests</span>
                    </a>
                </div>

                <a href="{{ url('/adminAccount') }}" class="list-group-item list-group-item-action {{ request()->is('adminAccount') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-group"></i>
                    <span>All Staff Accounts</span>
                </a>
                <a href="{{ route('admin.account.settings') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('admin.account.settings') ? 'active' : '' }}">
                    <i class="fas fa-user-cog"></i>
                    <span>Account Setting</span>
                </a>
            </div>
        </div>
        
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="top-bar" role="banner">
                <div class="burger-menu">
                    <button class="btn btn-primary" id="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                
                <div class="top-bar-spacer"></div>
                    <!-- Notification Dropdown -->
                <div class="notification-container">
                    <button class="notification-btn" id="notificationBtn">
                        <i class="fas fa-bell"></i>
                        <span class="badge" id="notificationBadge">0</span>
                    </button>
                    
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-header">
                            <h4>Appointment Request Notifications</h4>
                            <div class="notification-actions">
                                <button class="btn btn-sm btn-link" id="markAllReadBtn">Mark all as read</button>
                                <button class="btn btn-sm btn-link" onclick="refreshNotifications()">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="notification-list" id="notificationList">
                            <div class="notification-empty">
                                <i class="fas fa-bell-slash"></i>
                                <p>No new notifications</p>
                            </div>
                        </div>
                        
                        <div class="notification-footer">
                            <a href="{{ route('clientstbl') }}" class="btn btn-sm btn-primary w-100">
                                View All Pending Requests
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Log Out -->
                <form id="logout-form" action="{{ route('custom.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <button type="button" class="btn logout-btn" onclick="showLogoutModal()">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>

            </nav>
             
                        <!-- Modal -->
            <div id="infoModal" class="modal" style="display: none;">
                <div class="modal-content">
                   <!-- <span class="close" onclick="document.getElementById('infoModal').style.display='none'">&times;</span>-->

                    <div class="modal-left">
                        <h3>Appointment Details</h3>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="fullname">Fullname:</label>
                                <input type="text" name="fullname" id="fullname" readonly>
                            </div>
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" name="email" id="email" readonly>
                            </div>
                            <div class="form-group">
                                <label for="address">Address:</label>
                                <input type="text" name="address" id="address" readonly>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone:</label>
                                <input type="text" name="phone" id="phone" readonly>
                            </div>
                            <div class="form-group">
                                <label for="consulting">Consulting:</label>
                                <input type="text" name="consulting" id="consulting" readonly>
                            </div>
                            <div class="form-group">
                                <label for="selected_date">Date:</label>
                                <input type="date" name="selected_date" id="selected_date" readonly>
                            </div>
                            <div class="form-group">
                                <label for="selected_time">Time:</label>
                                <input type="text" name="selected_time" id="selected_time" readonly>
                            </div>
                            {{-- <div class="form-group">
                                <label for="selected_branch">Branch:</label>
                                <input type="text" name="selected_branch" id="selected_branch" readonly>
                            </div> --}}
                            <div class="form-group">
                                <label for="appointment_approval">Status:</label>
                                <input type="text" name="appointment_approval" id="appointment_approval" readonly>
                            </div>
                        </div>

                       <!-- Action Buttons -->
                        <div class="modal-actions">
                            <form id="approveForm" method="POST" action="">
                                @csrf
                                <input type="hidden" name="appointment_id" id="approve_appointment_id" value="">
                                <button type="submit" class="info-btn">
                                    <i class="fas fa-check"></i> APPROVE
                                </button>
                            </form>
                            
                            <!-- Deny Button with type="button" to prevent direct form submission -->
                            <button type="button" class="deny-btn" id="denyButton">
                                <i class="fas fa-times"></i> DENY
                            </button>
                        </div>
                    </div>

                    <!-- Image Section on Right -->
                    <div class="modal-right">
                        <h4>ID Images</h4>
                        <div class="image-preview-container">
                            <div class="image-section">
                                <label>Front ID:</label>
                                <div id="front_placeholder" class="image-placeholder">
                                    Front ID image
                                </div>
                                <img id="id_front_preview" class="id-image-preview">
                            </div>
                            <div class="image-section">
                                <label>Back ID:</label>
                                <div id="back_placeholder" class="image-placeholder">
                                    Back ID image
                                </div>
                                <img id="id_back_preview" class="id-image-preview">
                            </div>
                        </div>
                        <p id="imageError" class="image-error">Error loading image</p>
                    </div>
                </div>
            </div>
                        
            <div class="page-content">
                <div class="header-container">
                    <!-- Header -->
                    <div class="page-header">
                        <h1>Pending Requests</h1>
                        <p>Access and manage all logs entries across the system. Review, filter, and maintain your logs records with ease.</p>
                    </div>
                    <!-- Search Bar + Refresh -->
                    <div class="search-container d-flex align-items-center justify-content-between" style="gap:12px">
                        <div class="search-wrapper" style="flex:1;">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="searchInput" placeholder="Search appointments..." class="search-input">
                            <button class="search-clear" id="clearSearch" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="ms-2">
                            <button id="refreshBtn" class="btn btn-outline-secondary" title="Refresh table">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Fullname</th>
                                <th>Address</th>
                                <th>Phone</th>
                                <th>Consulting</th>
                                 {{-- <th>Branch Chosen</th> --}}
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($appointments as $appointment)
                                <tr>
                                    <td>{{ $appointment->fullname }}</td>
                                    <td>{{ $appointment->address }}</td>
                                    <td>{{ $appointment->phone }}</td>
                                    <td>{{ $appointment->consulting }}</td>
                                    {{-- <td>{{ $appointment->selected_branch ?? 'N/A' }}</td> --}}
                                    <td>{{ ucfirst($appointment->appointment_approval) }}</td>
                                    <td>
                                        <button class="info-btn view-btn" title="See Info" data-id="{{ $appointment->id }}">
                                            <i class="fas fa-eye"></i> VIEW INFORMATION
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" style="text-align:center;">No pending appointments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="actionFeedbackAlert" class="alert position-fixed top-0 end-0 m-3 shadow" style="display: none; z-index: 2000;" role="alert"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Updated JavaScript -->
   <!-- Updated JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('wrapper').classList.toggle('toggled');
        });
        
        // Close other submenus when opening a new one
        const menuItems = document.querySelectorAll('.list-group-item[data-bs-toggle="collapse"]');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                const targetId = this.getAttribute('href');
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                
                if (isExpanded) return;
                
                menuItems.forEach(otherItem => {
                    if (otherItem !== this) {
                        const otherTargetId = otherItem.getAttribute('href');
                        const otherTarget = document.querySelector(otherTargetId);
                        if (otherTarget && otherTarget.classList.contains('show')) {
                            const bsCollapse = new bootstrap.Collapse(otherTarget);
                            bsCollapse.hide();
                        }
                    }
                });
            });
        });
        
        // Set active menu item on click
        const allMenuItems = document.querySelectorAll('.list-group-item');
        allMenuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                if (this.hasAttribute('data-bs-toggle') && 
                    this.getAttribute('data-bs-toggle') === 'collapse') {
                    return;
                }
                
                allMenuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // ===== SEARCH + REFRESH FUNCTIONALITY =====
        const searchInput = document.getElementById('searchInput');
        const clearSearch = document.getElementById('clearSearch');
        const refreshBtn = document.getElementById('refreshBtn');
        const feedbackAlert = document.getElementById('actionFeedbackAlert');
        let isActionProcessing = false;
        let feedbackTimeout = null;

        function cleanupModalArtifacts() {
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.overflow = '';
        }

        function hideInfoModal() {
            const infoModal = document.getElementById('infoModal');
            if (infoModal) {
                infoModal.style.display = 'none';
            }
        }

        function showActionFeedback(message, type = 'success') {
            if (!feedbackAlert) {
                alert(message);
                return;
            }

            feedbackAlert.className = `alert alert-${type} position-fixed top-0 end-0 m-3 shadow`;
            feedbackAlert.textContent = message;
            feedbackAlert.style.display = 'block';

            if (feedbackTimeout) {
                clearTimeout(feedbackTimeout);
            }

            feedbackTimeout = setTimeout(() => {
                feedbackAlert.style.display = 'none';
            }, 3500);
        }

        function setButtonLoading(button, loadingText, isLoading) {
            if (!button) {
                return;
            }

            if (!button.dataset.originalHtml) {
                button.dataset.originalHtml = button.innerHTML;
            }

            button.disabled = isLoading;
            button.innerHTML = isLoading
                ? `<i class="fas fa-spinner fa-spin me-1"></i>${loadingText}`
                : button.dataset.originalHtml;
        }

        // Get current table rows dynamically
        function getTableRows() {
            const tableBody = document.querySelector('.table-container table tbody');
            return tableBody ? Array.from(tableBody.querySelectorAll('tr')) : [];
        }

        // Search function
        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase().trim();

            // Show/hide clear button based on input
            if (searchTerm.length > 0) {
                clearSearch.style.display = 'block';
            } else {
                clearSearch.style.display = 'none';
            }

            let hasVisibleRows = false;

            const tableRows = getTableRows();
            tableRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                let rowMatches = false;

                // Check each cell in the row for the search term
                cells.forEach(cell => {
                    const cellText = cell.textContent.toLowerCase();
                    if (cellText.includes(searchTerm)) {
                        rowMatches = true;
                    }
                });

                // Show/hide row based on match
                if (rowMatches || searchTerm === '') {
                    row.style.display = '';
                    hasVisibleRows = true;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show "no results" message if no rows match
            const tableBody = document.querySelector('.table-container table tbody');
            const noResultsRow = tableBody ? tableBody.querySelector('.no-results-message') : null;
            if (!hasVisibleRows && searchTerm !== '') {
                if (!noResultsRow && tableBody) {
                    const noResultsTr = document.createElement('tr');
                    noResultsTr.className = 'no-results-message';
                    noResultsTr.innerHTML = `
                        <td colspan="8" style="text-align: center; color: #666; padding: 20px;">
                            <i class="fas fa-search" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                            No matching appointments found for "${searchTerm}"
                        </td>
                    `;
                    tableBody.appendChild(noResultsTr);
                }
            } else if (noResultsRow) {
                noResultsRow.remove();
            }
        }

        // Event listeners for search
        searchInput.addEventListener('input', performSearch);

        // Clear search functionality
        clearSearch.addEventListener('click', function() {
            searchInput.value = '';
            clearSearch.style.display = 'none';
            performSearch();
            searchInput.focus();
        });

        // Close search on escape key
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                searchInput.value = '';
                clearSearch.style.display = 'none';
                performSearch();
            }
        });

        // Attach event listeners to buttons inside table (view buttons)
        function attachTableListeners() {
            // Re-bind view buttons
            document.querySelectorAll('.view-btn').forEach(button => {
                // Remove existing listeners by cloning
                const newButton = button.cloneNode(true);
                button.parentNode.replaceChild(newButton, button);
                newButton.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    fetch(`/appointments/${id}`)
                        .then(res => {
                            if (!res.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return res.json();
                        })
                        .then(data => {
                            // Populate modal fields (same as original handler)
                            document.getElementById('fullname').value = data.fullname || 'N/A';
                            document.getElementById('email').value = data.email || 'N/A';
                            document.getElementById('address').value = data.address || 'N/A';
                            document.getElementById('phone').value = data.phone || 'N/A';
                            const consulting = (data.category || 'General') + ' - ' + (data.case_name || 'Consultation');
                            document.getElementById('consulting').value = consulting;
                            document.getElementById('selected_date').value = data.selected_date || 'N/A';
                            document.getElementById('selected_time').value = data.selected_time || 'N/A';
                            {{-- document.getElementById('selected_branch').value = data.selected_branch || 'N/A'; --}}
                            document.getElementById('appointment_approval').value = data.appointment_approval || 'pending';

                            // Set form actions
                            const approveForm = document.getElementById('approveForm');
                            approveForm.action = `/appointments/${data.id}/approve`;
                            if (!approveForm.querySelector('input[name="_token"]')) {
                                approveForm.innerHTML += `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;
                            }

                            // Intercept approve form submit
                            (function() {
                                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                                // Remove previous listener by cloning
                                const newApproveForm = approveForm.cloneNode(true);
                                approveForm.parentNode.replaceChild(newApproveForm, approveForm);
                                newApproveForm.addEventListener('submit', function(ev) {
                                    ev.preventDefault();
                                    if (isActionProcessing) return;
                                    const url = newApproveForm.action;
                                    const phone = document.getElementById('phone').value || '';
                                    const email = document.getElementById('email').value || '';
                                    const approveSubmitButton = newApproveForm.querySelector('button[type="submit"]');
                                    isActionProcessing = true;
                                    setButtonLoading(approveSubmitButton, 'Approving...', true);

                                    fetch(url, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': csrfToken,
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Accept': 'application/json',
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({ email: email, phone: phone })
                                    }).then(resp => {
                                        if (!resp.ok) throw new Error('Approve request failed');
                                        return resp.json();
                                    }).then((respData) => {
                                        if (!respData.success) throw new Error(respData.message || 'Approve failed');
                                        hideInfoModal();
                                        cleanupModalArtifacts();
                                        showActionFeedback(respData.admin_message || 'Appointment approved successfully.', 'success');
                                        refreshTable();
                                    }).catch(err => {
                                        console.error(err);
                                        showActionFeedback(err.message || 'Failed to approve appointment.', 'danger');
                                    }).finally(() => {
                                        isActionProcessing = false;
                                        setButtonLoading(approveSubmitButton, 'Approving...', false);
                                    });
                                });
                            })();

                            // Store the deny URL for later use in the confirmation modal
                            const denyButton = document.getElementById('denyButton');
                            if (denyButton) {
                                denyButton.dataset.denyUrl = `/appointments/${data.id}/deny`;
                                denyButton.dataset.appointmentId = data.id;
                            }

                            // Handle images (omitted for brevity here since same as original)
                            const frontImg = document.getElementById('id_front_preview');
                            const backImg = document.getElementById('id_back_preview');
                            const frontPlaceholder = document.getElementById('front_placeholder');
                            const backPlaceholder = document.getElementById('back_placeholder');
                            const imageError = document.getElementById('imageError');

                            if (frontImg && backImg && frontPlaceholder && backPlaceholder) {
                                frontImg.style.display = 'none';
                                backImg.style.display = 'none';
                                frontPlaceholder.style.display = 'flex';
                                backPlaceholder.style.display = 'flex';
                                if (imageError) imageError.style.display = 'none';

                                if (data.id_front) {
                                    const frontFilename = data.id_front.split('/').pop();
                                    frontImg.onload = function() {
                                        frontPlaceholder.style.display = 'none';
                                        this.style.display = 'block';
                                    };
                                    frontImg.onerror = function() {
                                        frontPlaceholder.textContent = 'Front ID image not available';
                                        frontPlaceholder.style.display = 'flex';
                                        this.style.display = 'none';
                                        if (imageError) imageError.style.display = 'block';
                                    };
                                    frontImg.src = `/storage/ids/${frontFilename}`;
                                } else {
                                    frontPlaceholder.textContent = 'No front ID image available';
                                }

                                if (data.id_back) {
                                    const backFilename = data.id_back.split('/').pop();
                                    backImg.onload = function() {
                                        backPlaceholder.style.display = 'none';
                                        this.style.display = 'block';
                                    };
                                    backImg.onerror = function() {
                                        backPlaceholder.textContent = 'Back ID image not available';
                                        backPlaceholder.style.display = 'flex';
                                        this.style.display = 'none';
                                        if (imageError) imageError.style.display = 'block';
                                    };
                                    backImg.src = `/storage/ids/${backFilename}`;
                                } else {
                                    backPlaceholder.textContent = 'No back ID image available';
                                }
                            }

                            // Show modal after data is loaded
                            document.getElementById('infoModal').style.display = 'flex';
                        })
                        .catch(error => {
                            console.error('Error fetching appointment data:', error);
                            alert('Failed to load appointment details.');
                        });
                });
            });

            // Re-bind deny button handler
            const denyButton = document.getElementById('denyButton');
            if (denyButton) {
                denyButton.addEventListener('click', function() {
                    const denyUrl = this.dataset.denyUrl;
                    if (!denyUrl) {
                        showActionFeedback('No deny URL found. Please reload the page and try again.', 'warning');
                        return;
                    }
                    const denyConfirmationModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('denyConfirmationModal'));
                    const confirmDenyBtn = document.getElementById('confirmDenyBtn');
                    if (confirmDenyBtn) {
                        const newConfirmBtn = confirmDenyBtn.cloneNode(true);
                        confirmDenyBtn.parentNode.replaceChild(newConfirmBtn, confirmDenyBtn);
                        newConfirmBtn.addEventListener('click', function() {
                            if (isActionProcessing) return;
                            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                            const phone = document.getElementById('phone').value || '';
                            isActionProcessing = true;
                            setButtonLoading(newConfirmBtn, 'Denying...', true);
                            fetch(denyUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({ email: document.getElementById('email').value || '', phone: phone })
                            }).then(resp => {
                                if (!resp.ok) throw new Error('Deny request failed');
                                return resp.json();
                            }).then((respData) => {
                                if (!respData.success) throw new Error(respData.message || 'Deny failed');
                                denyConfirmationModal.hide();
                                hideInfoModal();
                                cleanupModalArtifacts();
                                showActionFeedback(respData.admin_message || 'Appointment denied successfully.', 'success');
                                refreshTable();
                            }).catch(err => {
                                console.error(err);
                                showActionFeedback(err.message || 'Failed to deny appointment.', 'danger');
                            }).finally(() => {
                                isActionProcessing = false;
                                setButtonLoading(newConfirmBtn, 'Denying...', false);
                            });
                        });
                    }
                    denyConfirmationModal.show();
                });
            }
        }

        // Initial attach for existing rows
        attachTableListeners();

        // Refresh table by fetching the same page and extracting the table body
        let isRefreshing = false;
        async function refreshTable() {
            if (isRefreshing) return;
            isRefreshing = true;
            try {
                const resp = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!resp.ok) throw new Error('Failed to fetch updated table');
                const html = await resp.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTbody = doc.querySelector('.table-container table tbody');
                const currentTbody = document.querySelector('.table-container table tbody');
                if (newTbody && currentTbody) {
                    currentTbody.innerHTML = newTbody.innerHTML;
                    // Re-attach listeners and re-run search filter
                    attachTableListeners();
                    performSearch();
                }
            } catch (err) {
                console.error('Error refreshing table:', err);
            } finally {
                isRefreshing = false;
            }
        }

        // Hook refresh button
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                refreshTable();
            });
        }

        // Auto-poll every 10 seconds to keep table near real-time
        setInterval(() => {
            refreshTable();
        }, 10000);

        // Modal close on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('infoModal');
            if (event.target === modal) {
                hideInfoModal();
            }
        }

        // Close modal with close button
        const closeButton = document.querySelector('.close');
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                hideInfoModal();
            });
        }

        const denyModalElement = document.getElementById('denyConfirmationModal');
        if (denyModalElement) {
            denyModalElement.addEventListener('hidden.bs.modal', function() {
                cleanupModalArtifacts();
            });
        }
        
        const logoutModalElement = document.getElementById('logoutConfirmationModal');
        if (logoutModalElement) {
            logoutModalElement.addEventListener('show.bs.modal', function() {
                // Add any additional logic when modal opens
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            });
            
            logoutModalElement.addEventListener('hidden.bs.modal', function() {
                // Restore scrolling when modal closes
                document.body.style.overflow = '';
            });
        }
        
    });
</script>
<script>
function showLogoutModal() {
    const modal = new bootstrap.Modal(document.getElementById('logoutConfirmationModal'));
    modal.show();
}
</script>
<script>
    // ===== NOTIFICATION SYSTEM =====
function initializeNotificationSystem() {
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    
    // Toggle notification dropdown
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
            // If dropdown opened, immediately hide badge and mark as read (user viewed notifications)
            if (notificationDropdown.classList.contains('show')) {
                try {
                    // Visual hide immediately
                    updateNotificationBadge(0);
                } catch (err) {
                    console.error('updateNotificationBadge not available', err);
                }
                try {
                    // Mark all as read on server (non-blocking)
                    markAllNotificationsAsRead();
                } catch (err) {
                    console.error('markAllNotificationsAsRead not available', err);
                }
            }
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
}

function loadNotifications() {
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
}

function updateNotificationBadge(count) {
    const notificationBadge = document.getElementById('notificationBadge');
    if (notificationBadge) {
        notificationBadge.textContent = count;
        notificationBadge.style.display = count > 0 ? 'block' : 'none';
    }
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
        
        // Determine icon and redirect URL based on notification type
        let iconClass = 'fas fa-calendar-plus';
        let redirectUrl = '{{ route("clientstbl") }}';
        let seeMoreText = 'See More';
        
        if (notification.type === 'message') {
            switch (notification.icon_type) {
                case 'envelope':
                    iconClass = 'fas fa-envelope';
                    seeMoreText = 'View Email';
                    break;
                case 'sms':
                    iconClass = 'fas fa-sms';
                    seeMoreText = 'View SMS';
                    break;
                case 'comments':
                    iconClass = 'fas fa-comments';
                    seeMoreText = 'View Chat';
                    break;
                default:
                    iconClass = 'fas fa-comments';
                    seeMoreText = 'View Message';
                    break;
            }
            redirectUrl = notification.redirect_url;
        }
        
        html += `
            <div class="notification-item ${isUnread ? 'unread' : ''}" 
                 data-id="${notification.id}" 
                 onclick="markNotificationAsRead('${notification.id}', this)">
                <div class="notification-icon">
                    <i class="${iconClass}"></i>
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
                                onclick="event.stopPropagation(); window.location.href='${redirectUrl}'">
                            <i class="fas fa-external-link-alt"></i> ${seeMoreText}
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
function markNotificationAsRead(id, element) {
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
}

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
function refreshNotifications() {
    loadNotifications();
}

// Utility function for escaping HTML (add if not already present)
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Initialize notification system when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Existing initialization code...
    
    // Initialize notification system
    initializeNotificationSystem();
    
    // Existing code continues...
});
</script>
 <!-- Bootstrap Modal for Logout Confirmation -->
        <div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-labelledby="logoutModalLabel">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-container">
                    <div class="title-header">
                        <h5 class="modal-title" id="logoutModalLabel">
                            <i class="fas fa-sign-out-alt me-2"></i>Confirm Logout
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <center>
                    <div class="content-modal">
                        <div style="font-size: 48px; color: #ffc107; margin-bottom: 15px;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                       
                        <h4 class="mb-3">Confirm Logout</h4>
                        <p>Are you sure you want to log out?<br>You will be redirected to the login page.</p>
                    </div>
                     </center>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-danger" onclick="document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt me-1"></i> Log Out
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Deny Confirmation Modal -->
        <div class="modal fade" id="denyConfirmationModal" tabindex="-1" aria-labelledby="denyModalLabel">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-container">
                    <div class="title-header">
                        <h5 class="modal-title" id="denyModalLabel">
                            <i class="fas fa-exclamation-triangle me-2"></i>Confirm Denial
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <center>
                        <div class="content-modal">
                            <div style="font-size: 48px; color: #ffc107; margin-bottom: 15px;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                           
                            <h4 class="mb-3">Confirm Denial</h4>
                            <p>Are you sure you want to deny this appointment request?<br>This action cannot be undone.</p>
                        </div>
                    </center>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmDenyBtn">
                            <i class="fas fa-times-circle me-1"></i> Deny Appointment
                        </button>
                    </div>
                </div>
            </div>
        </div>
@include('partials.notification-badge-visibility')
</body>
</html>
