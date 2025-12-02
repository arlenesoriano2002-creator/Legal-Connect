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
    
    <link rel="stylesheet" href="{{ asset('css/adminAcceptedRequest.blade.css') }}">
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
               <a href="{{ url('/email-chat') }}" class="list-group-item list-group-item-action {{ request()->is('email-chat') ? 'active' : '' }}">
                    <i class="fas fa-envelope"></i>
                    <span>Email Chat</span>
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
                    <i class="fas fa-user-cog"></i>
                    <span>All Accounts</span>
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

                <!-- Log Out -->
                <form id="logout-form" action="{{ route('custom.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <button type="button" class="btn logout-btn" aria-label="Log out" onclick="document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>

            <!-- Modal -->
                         <!-- Delete Confirmation Modal -->
            <div id="deleteConfirmationModal" class="modal" style="display: none;">
                <div class="modal-content" style="max-width: 500px;">
                    <span class="close" onclick="document.getElementById('deleteConfirmationModal').style.display='none'">&times;</span>
                    
                    <div class="modal-left" style="flex: 1; text-align: center;">
                        <h3 style="border-left: none; text-align: center; color: #dc3545;">
                            <i class="fas fa-exclamation-triangle" style="color: #dc3545; margin-right: 10px;"></i>
                            Confirm Deletion
                        </h3>
                        
                        <div class="confirmation-message" style="margin: 20px 0;">
                            <p style="font-size: 16px; color: #333; margin-bottom: 10px;">
                                Are you sure you want to delete this appointment?
                            </p>
                            <p style="font-size: 14px; color: #666; font-style: italic;">
                                This action cannot be undone and will permanently remove the appointment record.
                            </p>
                        </div>

                        <div class="confirmation-details" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; text-align: left;">
                            <p style="margin: 5px 0; font-size: 14px;"><strong>Client:</strong> <span id="confirmClientName"></span></p>
                            <p style="margin: 5px 0; font-size: 14px;"><strong>Date:</strong> <span id="confirmAppointmentDate"></span></p>
                            <p style="margin: 5px 0; font-size: 14px;"><strong>Time:</strong> <span id="confirmAppointmentTime"></span></p>
                        </div>

                        <div class="modal-actions" style="justify-content: center; margin-top: 25px;">
                            <button type="button" class="info-btn" onclick="document.getElementById('deleteConfirmationModal').style.display='none'" style="background-color: #6c757d;">
                                <i class="fas fa-times"></i> CANCEL
                            </button>
                            <form id="confirmDeleteForm" method="POST" action="" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="deny-btn" style="background-color: #dc3545;">
                                    <i class="fas fa-trash"></i> DELETE APPOINTMENT
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
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
                            <div class="form-group">
                                <label for="appointment_approval">Status:</label>
                                <input type="text" name="appointment_approval" id="appointment_approval" readonly>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="modal-actions">
                            <button type="button" class="deny-btn" id="deleteBtn" data-id="">
                                <i class="fas fa-trash"></i> DELETE
                            </button>
                            
                            <form id="archiveForm" method="POST" action="">
                                @csrf
                                <button type="submit" class="info-btn" style="background-color: #6b7280;">
                                    <i class="fas fa-archive"></i> ARCHIVE
                                </button>
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
                        <h1>Accepted Requests</h1>
                        <p>Access and manage all accepted appointment requests. Review and maintain approved appointment records.</p>
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
                                    <td>{{ ucfirst($appointment->appointment_approval) }}</td>
                                    <td>
                                        <button class="info-btn view-btn" title="See Info" data-id="{{ $appointment->id }}">
                                            <i class="fas fa-eye"></i> VIEW INFORMATION
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align:center;">No pending appointments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
                        <td colspan="6" style="text-align: center; color: #666; padding: 20px;">
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
                            document.getElementById('appointment_approval').value = data.appointment_approval || 'approved';
                            
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
                const appointmentId = this.getAttribute('data-id');
                
                if (currentAppointmentData) {
                    // Populate confirmation modal
                    document.getElementById('confirmClientName').textContent = currentAppointmentData.fullname || 'N/A';
                    document.getElementById('confirmAppointmentDate').textContent = currentAppointmentData.selected_date || 'N/A';
                    document.getElementById('confirmAppointmentTime').textContent = currentAppointmentData.selected_time || 'N/A';
                    
                    // Set delete form action
                    document.getElementById('confirmDeleteForm').action = `/appointments/delete/${appointmentId}`;
                    
                    // Show confirmation modal
                    document.getElementById('infoModal').style.display = 'none';
                    document.getElementById('deleteConfirmationModal').style.display = 'flex';
                }
            });

            // Modal close on outside click
            window.onclick = function(event) {
                const infoModal = document.getElementById('infoModal');
                const deleteModal = document.getElementById('deleteConfirmationModal');
                
                if (event.target === infoModal) {
                    infoModal.style.display = "none";
                }
                if (event.target === deleteModal) {
                    deleteModal.style.display = "none";
                }
            }

            // Close modals with close buttons
            document.querySelectorAll('.close').forEach(closeBtn => {
                closeBtn.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    if (modal) {
                        modal.style.display = "none";
                    }
                });
            });

        // Modal close on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('infoModal');
            if (event.target === modal) {
                modal.style.display = "none";
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
</body>
</html>