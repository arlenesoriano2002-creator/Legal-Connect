class FetchAppointments {
    constructor() {
        this.appointments = [];
        this.filteredAppointments = [];
        this.currentStatus = 'all';
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadAppointments();
    }

    bindEvents() {
        // Status filter
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.currentStatus = e.target.value;
                this.filterAppointments();
            });
        }

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filterAppointments();
            });
        }

        // Refresh button - UPDATED: call resetFilters instead of just loadAppointments
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.resetFilters();
            });
        }
          // Save Excel button 
        const saveExcelBtn = document.getElementById('saveExcelBtn');
        if (saveExcelBtn) {
            saveExcelBtn.addEventListener('click', () => {
                this.saveExcelBackup();
            });
        }

        // Save Backup button - Updated to PDF
        const saveBackupBtn = document.getElementById('saveBackupBtn');
        if (saveBackupBtn) {
            saveBackupBtn.addEventListener('click', () => {
                this.savePdfBackup();
            });
        }


        // Modal close events
        const appointmentModal = document.getElementById('appointmentModal');
        if (appointmentModal) {
            appointmentModal.addEventListener('hidden.bs.modal', () => {
                this.closeModal();
            });
        }

        // Escape key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') this.closeModal();
        });
    }
    // NEW METHOD: Save Excel backup
    async saveExcelBackup() {
        const saveExcelBtn = document.getElementById('saveExcelBtn');
        const originalText = saveExcelBtn.innerHTML;
        
        try {
            // Show loading state
            saveExcelBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            saveExcelBtn.disabled = true;

            const response = await fetch('/appointments/backup-excel', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    filter: this.currentStatus
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                this.showSuccessToast('Excel file saved successfully!');
            } else {
                throw new Error(result.message || 'Failed to save Excel file');
            }
        } catch (error) {
            console.error('Error saving Excel file:', error);
            alert('Failed to save Excel file: ' + error.message);
        } finally {
            // Restore button state
            saveExcelBtn.innerHTML = originalText;
            saveExcelBtn.disabled = false;
        }
    }
// Updated method: Save PDF backup (renamed from saveBackup)
async savePdfBackup() {
    const saveBackupBtn = document.getElementById('saveBackupBtn');
    const originalText = saveBackupBtn.innerHTML;
    
    try {
        // Show loading state
        saveBackupBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        saveBackupBtn.disabled = true;

        const response = await fetch('/appointments/backup-pdf', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                filter: this.currentStatus
            })
        });

        const result = await response.json();

        if (response.ok && result.success) {
            this.showSuccessToast('PDF file saved successfully!');
        } else {
            throw new Error(result.message || 'Failed to save PDF file');
        }
    } catch (error) {
        console.error('Error saving PDF file:', error);
        alert('Failed to save PDF file: ' + error.message);
    } finally {
        // Restore button state
        saveBackupBtn.innerHTML = originalText;
        saveBackupBtn.disabled = false;
    }
}


    // NEW METHOD: Show success toast
    showSuccessToast(message = 'Backup saved successfully!') {
    const toastElement = document.getElementById('successToast');
    if (toastElement) {
        // Update toast message
        const toastBody = toastElement.querySelector('.toast-body');
        if (toastBody) {
            toastBody.textContent = message;
        }
        
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
    }
}

    // NEW METHOD: Reset all filters to default state
    resetFilters() {
        // Reset status filter to default
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.value = 'all';
            this.currentStatus = 'all';
        }

        // Clear search input
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.value = '';
        }

        // Reset button states if they were disabled
        const saveExcelBtn = document.getElementById('saveExcelBtn');
        const saveBackupBtn = document.getElementById('saveBackupBtn');
        const refreshBtn = document.getElementById('refreshBtn');
        
        if (saveExcelBtn) {
            saveExcelBtn.disabled = false;
            saveExcelBtn.innerHTML = '<i class="fas fa-file-excel me-2"></i>Save as Excel';
        }
        
        if (saveBackupBtn) {
            saveBackupBtn.disabled = false;
            saveBackupBtn.innerHTML = '<i class="fas fa-file-pdf me-2"></i>Save as PDF';
        }

        // Reload appointments with default filters
        this.loadAppointments();
    }

    async loadAppointments() {
        this.showLoading();
        
        try {
            // UPDATED: Always fetch all appointments regardless of current filter
            const response = await fetch(`/api/appointments?status=all`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            this.appointments = await response.json();
            this.filterAppointments();
        } catch (error) {
            console.error('Error loading appointments:', error);
            this.showError('Failed to load appointments');
        }
    }

    filterAppointments() {
    const searchInput = document.getElementById('searchInput');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    
    this.filteredAppointments = this.appointments.filter(appointment => {
        // Filter by status
        const matchesStatus = this.currentStatus === 'all' || 
                            appointment.appointment_approval === this.currentStatus;
        
        // Filter by search term - UPDATED to include date and time
        const matchesSearch = !searchTerm || 
            appointment.fullname?.toLowerCase().includes(searchTerm) ||
            appointment.email?.toLowerCase().includes(searchTerm) ||
            appointment.category?.toLowerCase().includes(searchTerm) ||
            appointment.case_name?.toLowerCase().includes(searchTerm) ||
            appointment.selected_date?.toLowerCase().includes(searchTerm) ||
            appointment.selected_time?.toLowerCase().includes(searchTerm) ||
            appointment.appointment_approval?.toLowerCase().includes(searchTerm);
        
        return matchesStatus && matchesSearch;
    });

    this.renderTable();
}

    renderTable() {
    const tbody = document.getElementById('appointmentsTable');
    const emptyState = document.getElementById('emptyState');
    const loadingState = document.getElementById('loadingState');

    if (loadingState) loadingState.style.display = 'none';

    if (!this.filteredAppointments || this.filteredAppointments.length === 0) {
        if (tbody) tbody.innerHTML = '';
        if (emptyState) emptyState.classList.remove('d-none');
        return;
    }

    if (emptyState) emptyState.classList.add('d-none');

    if (tbody) {
        tbody.innerHTML = this.filteredAppointments.map(appointment => {
            // FIXED: Use selected_date and selected_time from database
            const dateTime = appointment.selected_date && appointment.selected_time 
                ? `${appointment.selected_date} ${this.formatTime(appointment.selected_time)}`
                : 'N/A';
            
            // Format created date
            const createdDate = new Date(appointment.created_at).toLocaleDateString();
            
            // Status text
            const statusText = this.getStatusText(appointment.appointment_approval);

            return `
                <tr>
                    <td>${appointment.fullname || 'N/A'}</td>
                    <td>${appointment.email || 'N/A'}</td>
                    <td>${appointment.category || 'N/A'}</td>
                    <td>${appointment.case_name || 'N/A'}</td>
                    <td>${dateTime}</td>
                    <td>
                        <span class="status-text">
                            ${statusText}
                        </span>
                    </td>
                    <td>${createdDate}</td>
                    <td>
                        <button onclick="appointmentsManager.viewDetails(${appointment.id})" class="view-btn">
                            View
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }
}

    async viewDetails(id) {
        try {
            const response = await fetch(`/api/appointments/${id}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const appointment = await response.json();
            
            if (appointment.error) {
                alert(appointment.error);
                return;
            }

            this.showModal(appointment);
        } catch (error) {
            console.error('Error loading appointment details:', error);
            alert('Failed to load appointment details');
        }
    }

showModal(appointment) {
    const modalContent = document.getElementById('modalContent');
    const modalElement = document.getElementById('appointmentModal');
    
    if (!modalContent || !modalElement) return;

    modalContent.innerHTML = `
        <div class="row">
            <!-- Personal Information -->
            <div class="col-md-6">
                <h4 class="text-lg font-semibold text-gray-800 border-bottom pb-2">Personal Information</h4>
                
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <p class="text-gray-900">${appointment.fullname || 'N/A'}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <p class="text-gray-900">${appointment.email || 'N/A'}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <p class="text-gray-900">${appointment.phone || 'N/A'}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <p class="text-gray-900">${appointment.address || 'N/A'}</p>
                </div>
            </div>

            <!-- Appointment Details -->
            <div class="col-md-6">
                <h4 class="text-lg font-semibold text-gray-800 border-bottom pb-2">Appointment Details</h4>
                
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <p class="text-gray-900">${appointment.category || 'N/A'}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Case Name</label>
                    <p class="text-gray-900">${appointment.case_name || 'N/A'}</p>
                </div>
                
                <!-- FIXED: Use selected_date and selected_time -->
                <div class="mb-3">
                    <label class="form-label">Selected Date</label>
                    <p class="text-gray-900">${appointment.selected_date || 'N/A'}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Selected Time</label>
                    <p class="text-gray-900">${appointment.selected_time ? this.formatTime(appointment.selected_time) : 'N/A'}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <p class="text-gray-900">${this.getStatusText(appointment.appointment_approval)}</p>
                </div>

                <div class="mb-3">
                    <label class="form-label">Terms Accepted</label>
                    <p class="text-gray-900">${appointment.term_status || 'N/A'}</p>
                </div>
            </div>

            <!-- ID Images -->
            <div class="col-12 mt-4">
                <h4 class="text-lg font-semibold text-gray-800 border-bottom pb-2">Identification Documents</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Front ID</label>
                        ${appointment.id_front_url ? 
                            `<div>
                                <img src="${appointment.id_front_url}" alt="Front ID" class="img-fluid rounded border shadow-sm" style="max-height: 200px; width: auto;" 
                                     onerror="this.style.display='none'; document.getElementById('front-error').style.display='block';">
                                <div id="front-error" style="display: none;" class="alert alert-warning">
                                    <strong>Image failed to load</strong><br>
                                    Database path: ${appointment.id_front}<br>
                                    Generated URL: ${appointment.id_front_url}<br>
                                    <a href="${appointment.id_front_url}" target="_blank">Test direct link</a> | 
                                    <a href="/debug-file-permissions/${appointment.id_front}" target="_blank">Debug file info</a> |
                                    <a href="/debug-storage-structure" target="_blank">Check storage structure</a>
                                </div>
                            </div>` :
                            '<p class="text-muted">No image uploaded</p>'
                        }
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Back ID</label>
                        ${appointment.id_back_url ? 
                            `<div>
                                <img src="${appointment.id_back_url}" alt="Back ID" class="img-fluid rounded border shadow-sm" style="max-height: 200px; width: auto;" 
                                     onerror="this.style.display='none'; document.getElementById('back-error').style.display='block';">
                                <div id="back-error" style="display: none;" class="alert alert-warning">
                                    <strong>Image failed to load</strong><br>
                                    Database path: ${appointment.id_back}<br>
                                    Generated URL: ${appointment.id_back_url}<br>
                                    <a href="${appointment.id_back_url}" target="_blank">Test direct link</a> | 
                                    <a href="/debug-file-permissions/${appointment.id_back}" target="_blank">Debug file info</a> |
                                    <a href="/debug-storage-structure" target="_blank">Check storage structure</a>
                                </div>
                            </div>` :
                            '<p class="text-muted">No image uploaded</p>'
                        }
                    </div>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="col-12 mt-4">
                <h4 class="text-lg font-semibold text-gray-800 border-bottom pb-2">Timestamps</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Created At</label>
                        <p class="text-gray-900">${new Date(appointment.created_at).toLocaleString()}</p>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Updated At</label>
                        <p class="text-gray-900">${new Date(appointment.updated_at).toLocaleString()}</p>
                    </div>
                </div>
            </div>
        </div>
    `;

    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}
    closeModal() {
        const modalElement = document.getElementById('appointmentModal');
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        }
    }

    showLoading() {
        const loadingState = document.getElementById('loadingState');
        const emptyState = document.getElementById('emptyState');
        const tbody = document.getElementById('appointmentsTable');
        
        if (loadingState) loadingState.style.display = 'block';
        if (emptyState) emptyState.classList.add('d-none');
        if (tbody) tbody.innerHTML = '';
    }

    showError(message) {
        const tbody = document.getElementById('appointmentsTable');
        const loadingState = document.getElementById('loadingState');
        const emptyState = document.getElementById('emptyState');
        
        if (loadingState) loadingState.style.display = 'none';
        if (emptyState) emptyState.classList.add('d-none');
        
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-danger p-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>${message}
                    </td>
                </tr>
            `;
        }
    }

    async verifyBackup(backupId) {
        try {
            const response = await fetch(`/backup/verify/${backupId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                if (result.is_valid) {
                    alert('Backup verification successful! The backup content is intact.');
                } else {
                    alert('Backup verification failed! The backup content may be corrupted.');
                }
            } else {
                throw new Error(result.message || 'Failed to verify backup');
            }
        } catch (error) {
            console.error('Error verifying backup:', error);
            alert('Failed to verify backup: ' + error.message);
        }
    }
    // Add these methods to your FetchAppointments class

// Helper method to format time
formatTime(time) {
    if (!time) return '';
    
    // Convert 24h to 12h format if needed
    if (time.includes(':')) {
        const [hours, minutes] = time.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        return `${hour12}:${minutes} ${ampm}`;
    }
    
    return time;
}

// Helper method to get status CSS class
getStatusClass(status) {
    if (!status) return 'status-pending';
    
    switch (status.toLowerCase()) {
        case 'approved': return 'bg-success';
        case 'pending': return 'bg-warning';
        case 'denied': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

// Helper method to get formatted status text
getStatusText(status) {
    if (!status) return 'Pending';
    return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
}
}

// Initialize the appointments manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.appointmentsManager = new FetchAppointments();
});