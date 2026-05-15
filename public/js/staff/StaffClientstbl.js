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

// ====================== PENDING APPOINTMENTS MANAGEMENT ======================

let currentAppointments = [];
let currentPage = 1;
const itemsPerPage = 10;

// Define routes - these should match your actual routes in web.php
const routes = {
    pendingAppointments: '/staff/pending-appointments',
    appointmentDetails: '/staff/appointments',
    approveAppointment: '/staff/appointments',
    denyAppointment: '/staff/appointments'
};

// Load appointments on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAppointments();
    
    // Set up auto-refresh every 30 seconds
    setInterval(function() {
        loadAppointments(true); // Silent refresh
    }, 30000);
    
    // Set up event listeners
    setupEventListeners();
});

// Set up event listeners
function setupEventListeners() {
    // Confirm deny button
    const confirmDenyBtn = document.getElementById('confirmDenyBtn');
    if (confirmDenyBtn) {
        confirmDenyBtn.addEventListener('click', function() {
            denyAppointment();
        });
    }
    
    // Search input
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchTable();
            }
        });
    }
}

// Load appointments via AJAX
function loadAppointments(silent = false) {
    if (!silent) {
        showLoading();
    }
    
    fetch(routes.pendingAppointments)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                currentAppointments = data.appointments;
                renderTable();
                updateTableCount();
                if (!silent) {
                    showToast('Appointments loaded successfully', 'success');
                }
            } else {
                throw new Error(data.error || 'Failed to load appointments');
            }
        })
        .catch(error => {
            console.error('Error loading appointments:', error);
            if (!silent) {
                showToast('Error loading appointments: ' + error.message, 'danger');
            }
            showErrorState();
        });
}

// Render table with pagination
function renderTable() {
    const tbody = document.getElementById('appointmentsTableBody');
    if (!tbody) return;
    
    // Calculate pagination
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedAppointments = currentAppointments.slice(startIndex, endIndex);
    
    // Clear table
    tbody.innerHTML = '';
    
    if (paginatedAppointments.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No pending appointments found</h5>
                    <p class="text-muted">All appointments are processed or there are no new requests.</p>
                </td>
            </tr>
        `;
        updatePagination();
        return;
    }
    
    // Add rows
    paginatedAppointments.forEach(appointment => {
        const row = createAppointmentRow(appointment);
        tbody.appendChild(row);
    });
    
    updatePagination();
    updateShowingCount();
}

// Create a table row for an appointment (WITHOUT ID COLUMN)
function createAppointmentRow(appointment) {
    const row = document.createElement('tr');
    row.id = `appointment-${appointment.id}`;
    
    // Format date
    const requestDate = new Date(appointment.created_at);
    const formattedDate = requestDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
    
    // Format time
    const formattedTime = requestDate.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
    
    row.innerHTML = `
        <td>
            <strong>${appointment.fullname || 'N/A'}</strong><br>
            <small class="text-muted">${appointment.email || 'No email'}</small>
        </td>
        <td>
            <div>${appointment.phone || 'N/A'}</div>
            <div>${appointment.address || 'N/A'}</div>
        </td>
        <td>
            <div><strong>Category:</strong> ${appointment.category || 'N/A'}</div>
            <div><strong>Case:</strong> ${appointment.case_name || 'N/A'}</div>
            <div><strong>Date/Time:</strong> ${appointment.selected_date || 'N/A'} at ${appointment.selected_time || 'N/A'}</div>
        </td>
        <td>
            <div>${formattedDate}</div>
            <small class="text-muted">${formattedTime}</small>
        </td>
        <td>
            <span class="badge-status badge-pending">
                <i class="fas fa-clock me-1"></i> Pending
            </span>
        </td>
        <td>
            <div class="btn-group" role="group">
                <button class="btn btn-sm btn-view btn-action" onclick="viewDetails(${appointment.id})" 
                        title="View Details">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-approve btn-action" onclick="approveAppointment(${appointment.id})" 
                        title="Approve Appointment">
                    <i class="fas fa-check"></i>
                </button>
                <button class="btn btn-sm btn-deny btn-action" onclick="showDenyModal(${appointment.id})" 
                        title="Deny Appointment">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </td>
    `;
    
    return row;
}

// View appointment details with ID images
function viewDetails(appointmentId) {
    console.log('Fetching appointment details for ID:', appointmentId);
    
    fetch(`${routes.appointmentDetails}/${appointmentId}/details`)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Full API response:', data);
            
            if (data.success) {
                const appointment = data.appointment;
                console.log('Appointment object:', appointment);
                console.log('ID Front value:', appointment.id_front);
                console.log('ID Front type:', typeof appointment.id_front);
                console.log('ID Back value:', appointment.id_back);
                console.log('ID Back type:', typeof appointment.id_back);
                
                const modalContent = document.getElementById('appointmentDetailsContent');
                
                // Format date
                const requestDate = new Date(appointment.created_at);
                const formattedRequestDate = requestDate.toLocaleString();
                const updatedDate = new Date(appointment.updated_at).toLocaleString();
                
                // Build ID Images HTML
                let idImagesHtml = '';
                
                // Helper function to create image HTML
                const createImageHtml = (imageData, title) => {
                    console.log(`Creating HTML for ${title}:`, imageData);
                    
                    if (!imageData || imageData === 'null' || imageData === 'NULL' || imageData === '') {
                        return `
                            <div class="alert alert-warning py-3">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                No ${title} uploaded
                            </div>
                        `;
                    }
                    
                    // Check if it's base64 or URL
                    if (imageData.startsWith('data:image') || imageData.startsWith('http')) {
                        return `
                            <div class="text-center">
                                <img src="${imageData}" 
                                     alt="${title}" 
                                     class="img-thumbnail"
                                     style="max-height: 200px; max-width: 100%; cursor: pointer;"
                                     onclick="viewImage('${imageData.replace(/'/g, "\\'")}')"
                                     data-bs-toggle="tooltip" 
                                     title="Click to enlarge">
                                <div class="mt-1">
                                    <small class="text-muted">Click image to view full size</small>
                                </div>
                            </div>
                        `;
                    } else {
                        // Try to construct a URL
                        const possibleUrls = [
                            imageData,
                            '/storage/ids/' + imageData,
                            '/storage/' + imageData,
                            '/ids/' + imageData,
                            window.location.origin + '/storage/ids/' + imageData
                        ];
                        
                        console.log(`Trying URLs for ${title}:`, possibleUrls);
                        
                        return `
                            <div class="text-center">
                                ${possibleUrls.map((url, index) => `
                                    <div class="mb-2">
                                        <img src="${url}" 
                                             alt="${title}" 
                                             class="img-thumbnail"
                                             style="max-height: 200px; max-width: 100%; cursor: pointer;"
                                             onerror="this.style.display='none'"
                                             onclick="viewImage('${url}')">
                                        <div><small>Attempt ${index + 1}: ${url}</small></div>
                                    </div>
                                `).join('')}
                                <div class="alert alert-info">
                                    <small>Raw value in database: "${imageData}"</small>
                                </div>
                            </div>
                        `;
                    }
                };
                
                // Always show ID Images section, even if empty
                idImagesHtml = `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">ID Images</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Front ID:</strong></label>
                                    ${createImageHtml(appointment.id_front, 'Front ID')}
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Back ID:</strong></label>
                                    ${createImageHtml(appointment.id_back, 'Back ID')}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Build the complete modal HTML
                modalContent.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Client Information</h6>
                            <div class="mb-3">
                                <label class="form-label"><strong>Full Name:</strong></label>
                                <p class="mb-1">${appointment.fullname || 'N/A'}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Email:</strong></label>
                                <p class="mb-1">${appointment.email || 'N/A'}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Phone:</strong></label>
                                <p class="mb-1">${appointment.phone || 'N/A'}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Address:</strong></label>
                                <p class="mb-1">${appointment.address || 'N/A'}</p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Appointment Details</h6>
                            <div class="mb-3">
                                <label class="form-label"><strong>Category:</strong></label>
                                <p class="mb-1">${appointment.category || 'N/A'}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Case Name:</strong></label>
                                <p class="mb-1">${appointment.case_name || 'N/A'}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Branch:</strong></label>
                                <p class="mb-1">${appointment.selected_branch || 'N/A'}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Appointment Date:</strong></label>
                                <p class="mb-1">${appointment.selected_date || 'N/A'}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Appointment Time:</strong></label>
                                <p class="mb-1">${appointment.selected_time || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                    
                    ${idImagesHtml}
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Timestamps</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label class="form-label"><strong>Request Submitted:</strong></label>
                                        <p class="mb-1">${formattedRequestDate}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label class="form-label"><strong>Last Updated:</strong></label>
                                        <p class="mb-1">${updatedDate}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    ${appointment.additional_info ? `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Additional Information</h6>
                            <p class="mb-0">${appointment.additional_info}</p>
                        </div>
                    </div>
                    ` : ''}
                `;
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));
                modal.show();
            } else {
                showToast('Error loading appointment details', 'danger');
            }
        })
        .catch(error => {
            console.error('Error loading details:', error);
            showToast('Error loading appointment details', 'danger');
        });
}

// Function to view image in full size
function viewImage(imageUrl) {
    const imageModalHtml = `
        <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">ID Image</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="${imageUrl}" 
                             alt="Full size ID" 
                             style="max-width: 100%; max-height: 70vh;"
                             class="img-fluid">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove any existing image modal
    const existingModal = document.getElementById('imageModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', imageModalHtml);
    
    // Show modal
    const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
    imageModal.show();
}

// Approve appointment (NO CONFIRMATION ALERT) WITH EMAIL NOTIFICATION
function approveAppointment(appointmentId) {
    // Directly proceed with approval without confirmation
    // Include client email and phone from the currently loaded appointments to ensure server uses the displayed values
    const appointment = currentAppointments.find(a => Number(a.id) === Number(appointmentId));
    const payload = appointment ? { email: appointment.email || '', phone: appointment.phone || '' } : { email: '', phone: '' };

    fetch(`${routes.approveAppointment}/${appointmentId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(payload)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Remove row from table
            const row = document.getElementById(`appointment-${appointmentId}`);
            if (row) {
                row.remove();
            }
            
            // Update counts
            updateTableCount();
            updateShowingCount();
            
            // Show reminder message with timestamp and email status
            const ts = new Date().toLocaleString();
            let message = 'Appointment approved successfully';
            if (data.email_sent) {
                message += ' ✓ Email notification sent to client';
            } else {
                message += ' (Email notification failed)';
            }
            if (data.sms_sent) {
                message += ' ✓ SMS sent to client';
                showToast(`${message} — ${ts}`, 'success');
            } else {
                // include sms status if available
                const smsInfo = data.sms_status ? `SMS status: ${data.sms_status}` : 'SMS not sent';
                message += ` (${smsInfo})`;
                showToast(`${message} — ${ts}`, data.email_sent ? 'warning' : 'warning');
            }
            
            // If table is empty, reload
            if (document.getElementById('appointmentsTableBody').children.length === 0) {
                loadAppointments();
            }
        } else {
            throw new Error(data.error || 'Failed to approve appointment');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error: ' + error.message, 'danger');
    });
}

// Show deny modal (SIMPLIFIED - NO REASON INPUT)
function showDenyModal(appointmentId) {
    document.getElementById('denyAppointmentId').value = appointmentId;
    
    const modal = new bootstrap.Modal(document.getElementById('denyModal'));
    modal.show();
}

// Deny appointment (SIMPLIFIED - NO REASON INPUT) WITH EMAIL NOTIFICATION
function denyAppointment() {
    const appointmentId = document.getElementById('denyAppointmentId').value;
    // Include client email and phone to ensure server sends to the address/number shown in the UI
    const appointment = currentAppointments.find(a => Number(a.id) === Number(appointmentId));
    const payload = appointment ? { email: appointment.email || '', phone: appointment.phone || '' } : { email: '', phone: '' };

    fetch(`${routes.denyAppointment}/${appointmentId}/deny`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(payload)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('denyModal'));
            modal.hide();
            
            // Remove row from table
            const row = document.getElementById(`appointment-${appointmentId}`);
            if (row) {
                row.remove();
            }
            
            // Update counts
            updateTableCount();
            updateShowingCount();
            
            // Show success message with email and sms status
            let message = 'Appointment denied successfully';
            if (data.email_sent) {
                message += ' ✓ Email notification sent to client';
            } else {
                message += ' (Email notification failed)';
            }
            if (data.sms_sent) {
                message += ' ✓ SMS sent to client';
                showToast(message, 'success');
            } else {
                const smsInfo = data.sms_status ? `SMS status: ${data.sms_status}` : 'SMS not sent';
                message += ` (${smsInfo})`;
                showToast(message, 'warning');
            }
            
            // If table is empty, reload
            if (document.getElementById('appointmentsTableBody').children.length === 0) {
                loadAppointments();
            }
        } else {
            throw new Error(data.error || 'Failed to deny appointment');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error: ' + error.message, 'danger');
    });
}

// Search table
function searchTable() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    
    if (!searchTerm) {
        currentPage = 1;
        renderTable();
        return;
    }
    
    const filtered = currentAppointments.filter(appointment => {
        return (
            (appointment.fullname && appointment.fullname.toLowerCase().includes(searchTerm)) ||
            (appointment.email && appointment.email.toLowerCase().includes(searchTerm)) ||
            (appointment.phone && appointment.phone.includes(searchTerm)) ||
            (appointment.category && appointment.category.toLowerCase().includes(searchTerm)) ||
            (appointment.case_name && appointment.case_name.toLowerCase().includes(searchTerm))
        );
    });
    
    // Render filtered results
    const tbody = document.getElementById('appointmentsTableBody');
    tbody.innerHTML = '';
    
    if (filtered.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No matching appointments found</h5>
                    <p class="text-muted">Try different search terms</p>
                </td>
            </tr>
        `;
    } else {
        filtered.forEach(appointment => {
            const row = createAppointmentRow(appointment);
            tbody.appendChild(row);
        });
    }
    
    // Update counts
    document.getElementById('tableCount').textContent = filtered.length;
    document.getElementById('showingCount').textContent = filtered.length;
    document.getElementById('totalCount').textContent = filtered.length;
    
    // Hide pagination for search results
    document.getElementById('pagination').innerHTML = '';
}

// Update pagination
function updatePagination() {
    const totalPages = Math.ceil(currentAppointments.length / itemsPerPage);
    const pagination = document.getElementById('pagination');
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Previous button
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1})" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                </li>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Next button
    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1})" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    `;
    
    pagination.innerHTML = html;
}

// Change page
function changePage(page) {
    currentPage = page;
    renderTable();
    window.scrollTo(0, 0);
}

// Update table count
function updateTableCount() {
    const count = currentAppointments.length;
    document.getElementById('tableCount').textContent = count;
    document.getElementById('totalCount').textContent = count;
}

// Update showing count
function updateShowingCount() {
    const startIndex = (currentPage - 1) * itemsPerPage + 1;
    const endIndex = Math.min(currentPage * itemsPerPage, currentAppointments.length);
    document.getElementById('showingCount').textContent = 
        currentAppointments.length > 0 ? `${startIndex}-${endIndex}` : '0';
}

// Show loading state
function showLoading() {
    const tbody = document.getElementById('appointmentsTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr id="loadingRow">
                <td colspan="6" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading appointments...</p>
                </td>
            </tr>
        `;
    }
}

// Show error state
function showErrorState() {
    const tbody = document.getElementById('appointmentsTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5 class="text-danger">Failed to load appointments</h5>
                    <p class="text-muted">Please try again later</p>
                    <button class="btn btn-primary mt-2" onclick="loadAppointments()">
                        <i class="fas fa-sync-alt me-1"></i> Retry
                    </button>
                </td>
            </tr>
        `;
    }
}

// Show toast notification
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Show toast
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    
    // Remove toast after it's hidden
    toast.addEventListener('hidden.bs.toast', function () {
        toast.remove();
    });
}

// Refresh table
function refreshTable() {
    loadAppointments();
}

// ====================== UTILITY FUNCTIONS ======================

// Test notification function
function testNotification() {
    showToast('Test notification from Pending Requests page', 'info');
}

// Refresh notifications
function refreshNotifications() {
    console.log('Refreshing notifications...');
}