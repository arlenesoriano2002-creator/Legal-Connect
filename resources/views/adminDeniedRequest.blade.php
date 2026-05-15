<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Denied Requests - LegalConnect</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/adminDeniedRequest.blade.css') }}">
    <style>
        /* Fix for modal z-index issues */
         .modal-backdrop {
            z-index: 1040;
        }
        
        .modal {
            z-index: 1050;
        }
        
        #deleteConfirmationModal .modal-container {
            background-color: #fff !important;
            border: 1px solid rgba(0, 0, 0, 0.2) !important;
            border-radius: 0.3rem !important;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.5) !important;
            display: block !important;
            flex-direction: column !important;
            pointer-events: auto !important;
            position: relative !important;
            width: 100% !important;
            max-width: 500px !important;
        }
        
        #deleteConfirmationModal .title-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 1rem !important;
            border-bottom: 1px solid #dee2e6 !important;
        }
        
        #deleteConfirmationModal .content-modal {
            padding: 1rem;
        }
        
        #deleteConfirmationModal .modal-footer {
            justify-content: flex-end !important;
            padding: 0.75rem;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
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
                            <h4>Notifications</h4>
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

            <!-- Information Modal -->
            <div id="infoModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" onclick="document.getElementById('infoModal').style.display='none'">&times;</span>

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
                            <!-- Changed to type="button" to show confirmation modal first -->
                            <button type="button" class="deny-btn" id="deleteBtn" data-id="">
                                <i class="fas fa-trash"></i> DELETE
                            </button>
                            
                            <form id="archiveForm" method="POST" action="">
                                @csrf
                            </form>
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
                        <h1>Denied Requests</h1>
                        <p>Access and manage all denied appointment requests. Review and maintain denied appointment records.</p>
                    </div>
                    <!-- Search Bar -->
                    <div class="search-container">
                        <div class="search-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="searchInput" placeholder="Search appointments..." class="search-input">
                            <button class="search-clear" id="clearSearch" style="display: none;">
                                <i class="fas fa-times"></i>
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
                                        <i class="fas fa-eye"></i> VIEW
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center;">No denied appointments found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Modal for Logout Confirmation -->
    <div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-labelledby="logoutModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">
                        <i class="fas fa-sign-out-alt me-2"></i>Confirm Logout
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div style="font-size: 48px; color: #ffc107; margin-bottom: 15px;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h4 class="mb-3">Confirm Logout</h4>
                    <p>Are you sure you want to log out?<br>You will be redirected to the login page.</p>
                </div>
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
    
    <!-- Delete Confirmation Modal (using same structure as adminAcceptedRequest.blade.php) -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-container">
                <div class="title-header">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <center>
                    <div class="content-modal">
                        <div style="font-size: 48px; color: #dc3545; margin-bottom: 15px;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                       
                        <h4 class="mb-3">Confirm Deletion</h4>
                        <p>Are you sure you want to delete this appointment?<br>This action cannot be undone.</p>
                        
                        <div class="confirmation-details mt-3" style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: left; max-width: 80%; margin: 0 auto;">
                            <p style="margin: 5px 0; font-size: 14px;"><strong>Client:</strong> <span id="confirmClientName">N/A</span></p>
                            <p style="margin: 5px 0; font-size: 14px;"><strong>Date:</strong> <span id="confirmAppointmentDate">N/A</span></p>
                            <p style="margin: 5px 0; font-size: 14px;"><strong>Time:</strong> <span id="confirmAppointmentTime">N/A</span></p>
                            <p style="margin: 5px 0; font-size: 14px;"><strong>Consulting:</strong> <span id="confirmConsulting">N/A</span></p>
                        </div>
                    </div>
                </center>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash me-1"></i> Delete Appointment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
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

            // ===== SEARCH FUNCTIONALITY =====
            const searchInput = document.getElementById('searchInput');
            const clearSearch = document.getElementById('clearSearch');
            const tableBody = document.querySelector('tbody');
            const tableRows = tableBody.querySelectorAll('tr');

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
                const noResultsRow = tableBody.querySelector('.no-results-message');
                if (!hasVisibleRows && searchTerm !== '') {
                    if (!noResultsRow) {
                        const noResultsTr = document.createElement('tr');
                        noResultsTr.className = 'no-results-message';
                        noResultsTr.innerHTML = `
                            <td colspan="7" style="text-align: center; color: #666; padding: 20px;">
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

            // ===== MODAL FUNCTIONALITY =====
            let currentAppointmentData = null;
            let currentDeleteUrl = '';

            document.querySelectorAll('.view-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    fetch(`/appointments/${id}`)
                        .then(res => {
                            if (!res.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return res.json();
                        })
                       .then(data => {
                            console.log('Appointment data:', data);
                            currentAppointmentData = data;
                            
                            // Set delete URL for confirmation modal
                            currentDeleteUrl = `/appointments/delete/${data.id}`;
                            
                            // Populate modal fields
                            document.getElementById('fullname').value = data.fullname || 'N/A';
                            document.getElementById('email').value = data.email || 'N/A';
                            document.getElementById('address').value = data.address || 'N/A';
                            document.getElementById('phone').value = data.phone || 'N/A';
                            
                            // Build consulting field from category and case_name
                            const consulting = (data.category || 'General') + ' - ' + (data.case_name || 'Consultation');
                            document.getElementById('consulting').value = consulting;
                            
                            document.getElementById('selected_date').value = data.selected_date || 'N/A';
                            document.getElementById('selected_time').value = data.selected_time || 'N/A';
                            {{-- document.getElementById('selected_branch').value = data.selected_branch || 'N/A'; --}}
                            document.getElementById('appointment_approval').value = data.appointment_approval || 'denied';
                            
                            // Set delete button data
                            document.getElementById('deleteBtn').setAttribute('data-id', data.id);
                            
                            // Set form actions for ARCHIVE
                            document.getElementById('archiveForm').action = `/admin/appointments/archive/${data.id}`;

                            // Handle image display
                            const frontImg = document.getElementById('id_front_preview');
                            const backImg = document.getElementById('id_back_preview');
                            const frontPlaceholder = document.getElementById('front_placeholder');
                            const backPlaceholder = document.getElementById('back_placeholder');
                            const imageError = document.getElementById('imageError');

                            // Reset display
                            if (frontImg && backImg && frontPlaceholder && backPlaceholder) {
                                frontImg.style.display = 'none';
                                backImg.style.display = 'none';
                                frontPlaceholder.style.display = 'flex';
                                backPlaceholder.style.display = 'flex';
                                if (imageError) imageError.style.display = 'none';

                                // Load front image
                                if (data.id_front) {
                                    const frontFilename = data.id_front.split('/').pop();
                                    frontImg.onload = function() {
                                        frontPlaceholder.style.display = 'none';
                                        this.style.display = 'block';
                                    };
                                    frontImg.onerror = function() {
                                        console.error('Failed to load front image:', this.src);
                                        frontPlaceholder.textContent = 'Front ID image not available';
                                        frontPlaceholder.style.display = 'flex';
                                        this.style.display = 'none';
                                        if (imageError) imageError.style.display = 'block';
                                    };
                                    frontImg.src = `/storage/ids/${frontFilename}`;
                                } else {
                                    frontPlaceholder.textContent = 'No front ID image available';
                                }

                                // Load back image
                                if (data.id_back) {
                                    const backFilename = data.id_back.split('/').pop();
                                    backImg.onload = function() {
                                        backPlaceholder.style.display = 'none';
                                        this.style.display = 'block';
                                    };
                                    backImg.onerror = function() {
                                        console.error('Failed to load back image:', this.src);
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

            // Delete button click handler
            document.getElementById('deleteBtn').addEventListener('click', function() {
                if (currentAppointmentData) {
                    // Populate confirmation modal
                    document.getElementById('confirmClientName').textContent = currentAppointmentData.fullname || 'N/A';
                    document.getElementById('confirmAppointmentDate').textContent = currentAppointmentData.selected_date || 'N/A';
                    document.getElementById('confirmAppointmentTime').textContent = currentAppointmentData.selected_time || 'N/A';
                    document.getElementById('confirmConsulting').textContent = 
                        (currentAppointmentData.category || 'General') + ' - ' + (currentAppointmentData.case_name || 'Consultation');
                    
                    // Hide the info modal
                    document.getElementById('infoModal').style.display = 'none';
                    
                    // Show the delete confirmation modal
                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
                    deleteModal.show();
                }
            });

            // Confirm delete button handler
            document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                if (currentDeleteUrl) {
                    // Create a form and submit it
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = currentDeleteUrl;
                    
                    // Add CSRF token
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);
                    
                    // Add method spoofing for DELETE
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);
                    
                    // Add to body and submit
                    document.body.appendChild(form);
                    form.submit();
                }
            });

            // Modal close on outside click
            window.onclick = function(event) {
                const infoModal = document.getElementById('infoModal');
                
                if (event.target === infoModal) {
                    infoModal.style.display = "none";
                }
            }

            // Close modal with close button
            const closeButton = document.querySelector('.close');
            if (closeButton) {
                closeButton.addEventListener('click', function() {
                    document.getElementById('infoModal').style.display = "none";
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
        // ===== NOTIFICATION SYSTEM =====//
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
@include('partials.notification-badge-visibility')
</body>
</html>