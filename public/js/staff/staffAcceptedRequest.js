document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const menuToggle = document.getElementById('menu-toggle');
    const wrapper = document.getElementById('wrapper');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            wrapper.classList.toggle('toggled');
        });
    }

    // Notification dropdown
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    if (notificationBtn && notificationDropdown) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.remove('show');
            }
        });
    }

    // Mark all notifications as read
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            // Implement mark all as read functionality
            const badges = document.querySelectorAll('.badge');
            badges.forEach(badge => {
                badge.style.display = 'none';
            });
            notificationDropdown.classList.remove('show');
        });
    }

    // Refresh button
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            location.reload();
        });
    }

    // Search functionality
    const searchInput = document.querySelector('.search-input');
    const searchClear = document.querySelector('.search-clear');
    const tableRows = document.querySelectorAll('#acceptedAppointmentsTable tbody tr');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let hasResults = false;
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    hasResults = true;
                    // Add highlight class
                    row.classList.add('search-highlight');
                } else {
                    row.style.display = 'none';
                    row.classList.remove('search-highlight');
                }
            });
            
            // Show/hide clear button
            if (searchClear) {
                searchClear.style.display = searchTerm.length > 0 ? 'block' : 'none';
            }
        });
        
        // Clear search
        if (searchClear) {
            searchClear.addEventListener('click', function() {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
                this.style.display = 'none';
            });
        }
    }

    // Delete functionality
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    let appointmentToDelete = null;
    
    // Handle delete button clicks
    let appointmentToDeleteUrl = null;
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-btn')) {
            const button = e.target.closest('.delete-btn');
            appointmentToDelete = button.getAttribute('data-id');
            appointmentToDeleteUrl = button.getAttribute('data-delete-url');
            const appointmentName = button.getAttribute('data-name');
            
            document.getElementById('deleteAppointmentName').textContent = 
                `Appointment for: ${appointmentName}`;
            deleteModal.show();
        }
    });
    
    // Confirm delete
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (!appointmentToDelete) return;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const deleteUrl = appointmentToDeleteUrl || `/staff/accepted-requests/${appointmentToDelete}`;
            fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the row from the table
                    const row = document.getElementById(`appointment-${appointmentToDelete}`);
                    if (row) {
                        row.remove();
                    }
                    
                    // Show success message
                    showToast('Success', 'Appointment deleted successfully', 'success');
                } else {
                    showToast('Error', data.message || 'Failed to delete appointment', 'error');
                }
                deleteModal.hide();
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred while deleting the appointment', 'error');
                deleteModal.hide();
            });
        });
    }

    // View appointment details
    document.addEventListener('click', function(e) {
        if (e.target.closest('.view-btn')) {
            const button = e.target.closest('.view-btn');
            const detailsUrl = button.getAttribute('data-details-url') || `/staff/accepted-requests/${button.getAttribute('data-id')}/details`;
            viewAppointmentDetails(detailsUrl);
        }
    });

    function viewAppointmentDetails(url) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Show loading state
        document.getElementById('appointmentDetailsContent').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading appointment details...</p>
            </div>
        `;
        
        // Get appointment details
        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.appointment) {
                populateAppointmentDetails(data.appointment);
            } else {
                document.getElementById('appointmentDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Failed to load appointment details: ${data.error || 'Unknown error'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('appointmentDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    An error occurred while loading appointment details.
                </div>
            `;
        })
        .finally(() => {
            // Show the modal
            const viewModal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));
            viewModal.show();
        });
    }

    function populateAppointmentDetails(appointment) {
        const formatDate = (dateString) => {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };

        let html = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Client Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <th>Full Name:</th>
                            <td>${appointment.fullname || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>${appointment.email || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>${appointment.phone || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td>${appointment.address || 'N/A'}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Appointment Details</h6>
                    <table class="table table-sm">
                        <tr>
                            <th>Category:</th>
                            <td>${appointment.category || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Case Name:</th>
                            <td>${appointment.case_name || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Selected Branch:</th>
                            <td>${appointment.selected_branch || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Selected Date:</th>
                            <td>${appointment.selected_date || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Selected Time:</th>
                            <td>${appointment.selected_time || 'N/A'}</td>
                        </tr>
                         <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-success rounded-pill">
                                    <i class="fas fa-check-circle me-1"></i> Approved
                                </span>
                                ${appointment.appointment_approval ? `(${appointment.appointment_approval})` : ''}
                            </td>
                        </tr>
                        <tr>
                            <th>Request Date:</th>
                            <td>${formatDate(appointment.created_at)}</td>
                        </tr>
                    </table>
                </div>
            </div>
        `;

        // Add ID images if available
        if (appointment.id_front || appointment.id_back) {
            html += `
                <div class="row mt-4">
                    <div class="col-12">
                        <h6>Identification Documents</h6>
                        <div class="row">
            `;
            
            if (appointment.id_front) {
                html += `
                    <div class="col-md-6">
                        <p class="small text-muted mb-1">ID Front</p>
                        <img src="${appointment.id_front}" alt="ID Front" class="img-fluid rounded border" style="max-height: 200px;">
                    </div>
                `;
            }
            
            if (appointment.id_back) {
                html += `
                    <div class="col-md-6">
                        <p class="small text-muted mb-1">ID Back</p>
                        <img src="${appointment.id_back}" alt="ID Back" class="img-fluid rounded border" style="max-height: 200px;">
                    </div>
                `;
            }
            
            html += `
                        </div>
                    </div>
                </div>
            `;
        }

        // Add any additional notes or denial reason if available
        if (appointment.denial_reason) {
            html += `
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Denial Reason</h6>
                        <div class="alert alert-warning">
                            ${appointment.denial_reason}
                        </div>
                    </div>
                </div>
            `;
        }

        document.getElementById('appointmentDetailsContent').innerHTML = html;
    }

    function showToast(title, message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}:</strong> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        // Add to toast container
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        toastContainer.appendChild(toast);
        
        // Initialize and show toast
        const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
        bsToast.show();
        
        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    }

    // Generate Report button
    const generateReportBtn = document.getElementById('generateReportBtn');
    if (generateReportBtn) {
        generateReportBtn.addEventListener('click', function() {
            // Get filter values from the form
            const dateFilter = document.getElementById('dateFilter');
            const timeFilter = document.getElementById('timeFilter');
            const categoryFilter = document.getElementById('categoryFilter');

            // Build query parameters
            const params = new URLSearchParams();
            
            if (dateFilter && dateFilter.value) {
                params.append('date', dateFilter.value);
            }
            if (timeFilter && timeFilter.value) {
                params.append('time', timeFilter.value);
            }
            if (categoryFilter && categoryFilter.value) {
                params.append('category', categoryFilter.value);
            }

            // Build the URL with parameters
            const url = new URL('/staff/accepted-requests/report/pdf', window.location.origin);
            params.forEach((value, key) => {
                url.searchParams.append(key, value);
            });

            // Create a hidden link and trigger download
            const link = document.createElement('a');
            link.href = url.toString();
            link.target = '_blank';
            link.click();

            // Show success message
            showToast('Success', 'Generating report... Your PDF will download shortly.', 'success');
        });
    }

    // Logout modal
    window.showLogoutModal = function() {
        const logoutModal = new bootstrap.Modal(document.getElementById('logoutConfirmationModal'));
        logoutModal.show();
    };
});