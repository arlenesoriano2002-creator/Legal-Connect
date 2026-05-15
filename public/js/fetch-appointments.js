class FetchAppointments {
    constructor() {
        this.appointments = [];
        this.filteredAppointments = [];
        this.currentStatus = 'all';
        this.currentCategory = 'all';
        this.currentCaseName = 'all';
        // this.currentBranch = 'all';
        this.init();
    }

    init() {
    this.bindEvents();
    this.loadCategories();
    this.loadAppointments();
    this.bindBackupLogsEvents(); // NEW
    }

    bindBackupLogsEvents() {
        // View Backup Logs button
        const viewBackupLogsBtn = document.getElementById('viewBackupLogsBtn');
        if (viewBackupLogsBtn) {
            viewBackupLogsBtn.addEventListener('click', () => {
                this.showBackupLogsModal();
            });
        }

        // Handle tab changes
        const backupLogsModal = document.getElementById('backupLogsModal');
        if (backupLogsModal) {
            backupLogsModal.addEventListener('shown.bs.modal', () => {
                // Load backup logs when modal is shown
                this.loadBackupLogs();
            });

            backupLogsModal.addEventListener('hidden.bs.modal', () => {
                // Reset modal state when hidden
                this.resetBackupLogsModal();
            });
        }

        // Handle tab switching
        const backupLogsTab = document.getElementById('backupLogsTab');
        if (backupLogsTab) {
            backupLogsTab.addEventListener('click', (e) => {
                if (e.target.classList.contains('nav-link')) {
                    // If switching to PDF or CSV tab without selecting a backup, show info message
                    const targetId = e.target.getAttribute('data-bs-target');
                    if (targetId === '#pdf-preview' || targetId === '#csv-preview') {
                        const activeBackup = document.querySelector('.backup-log-item.active');
                        if (!activeBackup) {
                            e.preventDefault();
                            this.showToast('Please select a backup from the Backup Logs tab first.', 'warning');
                        }
                    }
                }
            });
        }
    }
    showBackupLogsModal() {
    const modalElement = document.getElementById('backupLogsModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}

// NEW METHOD: Load backup logs via AJAX
async loadBackupLogs() {
    const container = document.getElementById('backupLogsContainer');
    if (!container) return;

    try {
        // Show loading state
        container.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading backup logs...</span>
                </div>
                <p class="mt-2 text-muted">Loading backup logs...</p>
            </div>
        `;

        // Use the existing route that works
        const response = await fetch('/admin/get-backups', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        
        // Check if response is OK
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const backups = await response.json();

        if (!backups || backups.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-folder-open fa-2x text-muted mb-3"></i>
                    <p class="text-muted">No backup logs found.</p>
                </div>
            `;
            return;
        }

        // Render backup logs
        let html = '';
        backups.forEach(backup => {
            // Get decrypted filename if available
            const fileName = backup.file_name || backup.decrypted_file_name || 'Unknown Backup';
            const extension = fileName.split('.').pop().toLowerCase();
            const date = new Date(backup.created_at).toLocaleString();
            let typeClass = 'secondary';
            let typeIcon = 'fa-file';
            
            switch(extension) {
                case 'pdf':
                    typeClass = 'pdf';
                    typeIcon = 'fa-file-pdf';
                    break;
                case 'csv':
                case 'xlsx':
                    typeClass = 'csv';
                    typeIcon = 'fa-file-excel';
                    break;
                case 'sql':
                    typeClass = 'sql';
                    typeIcon = 'fa-database';
                    break;
            }

            html += `
                <div class="backup-log-item" data-backup-id="${backup.id}" data-file-name="${fileName}" data-extension="${extension}">
                    <div class="backup-log-info">
                        <div class="backup-log-name">
                            <i class="fas ${typeIcon} me-2"></i>
                            ${fileName}
                        </div>
                        <div class="backup-log-details">
                            <span class="backup-log-type ${typeClass}">${extension.toUpperCase()}</span>
                            <span class="ms-3">${date}</span>
                        </div>
                    </div>
                    <div class="backup-log-actions">
                        <button class="backup-log-btn view" onclick="appointmentsManager.viewBackup(${backup.id}, '${extension}')">
                            <i class="fas fa-eye me-1"></i> View
                        </button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;

        // Add click event to backup log items
        container.querySelectorAll('.backup-log-item').forEach(item => {
            item.addEventListener('click', (e) => {
                // Remove active class from all items
                container.querySelectorAll('.backup-log-item').forEach(i => {
                    i.classList.remove('active');
                });
                
                // Add active class to clicked item
                item.classList.add('active');
                
                // Get backup details
                const backupId = item.getAttribute('data-backup-id');
                const fileName = item.getAttribute('data-file-name');
                const extension = item.getAttribute('data-extension');
                
                // Store selected backup info
                this.selectedBackup = { id: backupId, fileName, extension };
            });
        });

    } catch (error) {
        console.error('Error loading backup logs:', error);
        container.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Failed to load backup logs. Please try again.
            </div>
        `;
    }
}
// NEW METHOD: View backup (PDF or CSV)
async viewBackup(backupId, extension) {
    try {
        if (extension === 'pdf') {
            // Switch to PDF tab
            const pdfTab = document.getElementById('pdf-preview-tab');
            if (pdfTab) {
                const tab = new bootstrap.Tab(pdfTab);
                tab.show();
                
                // Load PDF in iframe - FIXED: Use the correct route with proper headers
                const pdfFrame = document.getElementById('pdfPreviewFrame');
                
                // Clear previous content
                pdfFrame.src = '';
                
                // Create a URL with inline parameter
                const pdfUrl = `/backup/view/${backupId}?inline=true`;
                console.log('Loading PDF from:', pdfUrl);
                
                // Set iframe source
                pdfFrame.src = pdfUrl;
                
                // Add error handling
                pdfFrame.onload = function() {
                    console.log('PDF loaded successfully');
                };
                
                pdfFrame.onerror = function() {
                    console.error('Failed to load PDF');
                    this.showToast('Failed to load PDF preview. The file might be corrupted or unavailable.', 'danger');
                };
            }
        } else if (extension === 'csv' || extension === 'xlsx') {
            // Switch to CSV tab
            const csvTab = document.getElementById('csv-preview-tab');
            if (csvTab) {
                const tab = new bootstrap.Tab(csvTab);
                tab.show();
                
                // Load CSV preview
                await this.loadCsvPreview(backupId);
            }
        } else {
            this.showToast('Preview not available for this file type.', 'warning');
        }
    } catch (error) {
        console.error('Error viewing backup:', error);
        this.showToast('Failed to load backup preview.', 'danger');
    }
}

// NEW METHOD: Load CSV preview
async loadCsvPreview(backupId) {
    const tableHead = document.getElementById('csvPreviewTableHead');
    const tableBody = document.getElementById('csvPreviewTableBody');
    const pagination = document.getElementById('csvPagination');
    const pageInfo = document.getElementById('csvPageInfo');
    
    if (!tableHead || !tableBody) return;

    try {
        // Show loading
        tableHead.innerHTML = '';
        tableBody.innerHTML = `
            <tr>
                <td colspan="100" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    Loading CSV data...
                </td>
            </tr>
        `;
        pagination.classList.add('d-none');

        // Fetch CSV data
        console.log('Fetching CSV data for backup:', backupId);
        const response = await fetch(`/backup/view/${backupId}?format=json`);
        
        // Check if response is OK
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Get the response text first for debugging
        const responseText = await response.text();
        console.log('Raw response:', responseText.substring(0, 500));
        
        // Try to parse as JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (jsonError) {
            console.error('JSON parse error:', jsonError);
            console.error('Response text:', responseText);
            throw new Error('Invalid JSON response from server');
        }

        if (!data || data.error) {
            throw new Error(data?.error || 'Invalid CSV data');
        }

        if (!data.headers || !data.rows) {
            throw new Error('Missing headers or rows in CSV data');
        }

        // Clear existing content
        tableHead.innerHTML = '';
        tableBody.innerHTML = '';

        // Create headers
        const headerRow = document.createElement('tr');
        data.headers.forEach(header => {
            const th = document.createElement('th');
            th.textContent = header;
            headerRow.appendChild(th);
        });
        tableHead.appendChild(headerRow);

        // Create rows (limit to 100 for performance)
        const rowsToShow = data.rows.slice(0, 100);
        rowsToShow.forEach(row => {
            const tr = document.createElement('tr');
            data.headers.forEach(header => {
                const td = document.createElement('td');
                td.textContent = row[header] || '';
                td.style.whiteSpace = 'nowrap';
                td.style.overflow = 'hidden';
                td.style.textOverflow = 'ellipsis';
                td.style.maxWidth = '200px';
                tr.appendChild(td);
            });
            tableBody.appendChild(tr);
        });

        // Show pagination if there are more rows
        if (data.rows.length > 100) {
            pageInfo.textContent = `Showing 1-100 of ${data.rows.length} rows`;
            pagination.classList.remove('d-none');
            
            // Handle pagination
            this.csvData = data;
            this.currentCsvPage = 1;
            this.rowsPerPage = 100;
            
            // Update pagination event listeners
            this.bindCsvPagination();
        }

    } catch (error) {
        console.error('Error loading CSV preview:', error);
        tableBody.innerHTML = `
            <tr>
                <td colspan="100" class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Failed to load CSV preview: ${error.message}
                </td>
            </tr>
        `;
        
        // Show a debug button
        const debugBtn = document.createElement('button');
        debugBtn.className = 'btn btn-sm btn-outline-primary mt-2';
        debugBtn.innerHTML = '<i class="fas fa-bug me-1"></i>Debug CSV Parsing';
        debugBtn.onclick = () => window.open(`/test-csv-parse/${backupId}`, '_blank');
        
        const td = document.createElement('td');
        td.colSpan = 100;
        td.className = 'text-center';
        td.appendChild(debugBtn);
        
        const tr = document.createElement('tr');
        tr.appendChild(td);
        tableBody.appendChild(tr);
    }
}
// NEW METHOD: Bind CSV pagination events
bindCsvPagination() {
    const prevBtn = document.querySelector('[data-page="prev"]');
    const nextBtn = document.querySelector('[data-page="next"]');
    
    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (this.currentCsvPage > 1) {
                this.currentCsvPage--;
                this.updateCsvPage();
            }
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const totalPages = Math.ceil(this.csvData.rows.length / this.rowsPerPage);
            if (this.currentCsvPage < totalPages) {
                this.currentCsvPage++;
                this.updateCsvPage();
            }
        });
    }
}

// NEW METHOD: Update CSV page
updateCsvPage() {
    if (!this.csvData) return;
    
    const tableBody = document.getElementById('csvPreviewTableBody');
    const pageInfo = document.getElementById('csvPageInfo');
    
    const startIndex = (this.currentCsvPage - 1) * this.rowsPerPage;
    const endIndex = startIndex + this.rowsPerPage;
    const rowsToShow = this.csvData.rows.slice(startIndex, endIndex);
    
    // Clear and update table
    tableBody.innerHTML = '';
    rowsToShow.forEach(row => {
        const tr = document.createElement('tr');
        this.csvData.headers.forEach(header => {
            const td = document.createElement('td');
            td.textContent = row[header] || '';
            td.style.whiteSpace = 'nowrap';
            td.style.overflow = 'hidden';
            td.style.textOverflow = 'ellipsis';
            td.style.maxWidth = '200px';
            tr.appendChild(td);
        });
        tableBody.appendChild(tr);
    });
    
    // Update page info
    pageInfo.textContent = `Showing ${startIndex + 1}-${Math.min(endIndex, this.csvData.rows.length)} of ${this.csvData.rows.length} rows`;
}

// NEW METHOD: Reset backup logs modal
resetBackupLogsModal() {
    // Clear selected backup
    this.selectedBackup = null;
    
    // Clear active classes
    const container = document.getElementById('backupLogsContainer');
    if (container) {
        container.querySelectorAll('.backup-log-item').forEach(item => {
            item.classList.remove('active');
        });
    }
    
    // Clear previews
    const pdfFrame = document.getElementById('pdfPreviewFrame');
    if (pdfFrame) pdfFrame.src = '';
    
    const tableHead = document.getElementById('csvPreviewTableHead');
    const tableBody = document.getElementById('csvPreviewTableBody');
    if (tableHead) tableHead.innerHTML = '';
    if (tableBody) tableBody.innerHTML = '<tr><td colspan="100" class="text-center py-4 text-muted">Select a CSV backup to preview</td></tr>';
    
    const pagination = document.getElementById('csvPagination');
    if (pagination) pagination.classList.add('d-none');
    
    // Switch back to first tab
    const firstTab = document.getElementById('backup-list-tab');
    if (firstTab) {
        const tab = new bootstrap.Tab(firstTab);
        tab.show();
    }
}

// NEW METHOD: Show toast message
showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast align-items-center text-bg-${type} border-0`;
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
    
    // Remove toast after hiding
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}
    bindEvents() {
        // Category filter
        const categoryFilter = document.getElementById('categoryFilter');
        if (categoryFilter) {
            categoryFilter.addEventListener('change', (e) => {
                this.currentCategory = e.target.value;
                // Reload case name options when category changes
                this.loadCaseNames(this.currentCategory);
                // Reset selected case name when category changes
                const caseSelect = document.getElementById('caseNameFilter');
                if (caseSelect) {
                    caseSelect.value = 'all';
                    this.currentCaseName = 'all';
                }
                this.filterAppointments();
            });
        }
        // Branch filter
        // const branchFilter = document.getElementById('branchFilter');
        // if (branchFilter) {
        //     branchFilter.addEventListener('change', (e) => {
        //         this.currentBranch = e.target.value;
        //         this.filterAppointments();
        //     });
        // }
        // Case name filter
        const caseNameFilter = document.getElementById('caseNameFilter');
        if (caseNameFilter) {
            caseNameFilter.addEventListener('change', (e) => {
                this.currentCaseName = e.target.value;
                this.filterAppointments();
            });
        }
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

    // Load categories from server and populate the category filter
    async loadCategories() {
        const select = document.getElementById('categoryFilter');
        if (!select) return;

        try {
            const res = await fetch('/api/appointment-categories', {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Failed to load categories');
            const data = await res.json();
            const categories = data.categories || [];

            // Clear existing options except the default
            select.innerHTML = '<option value="all">All Categories</option>' +
                categories.map(c => `<option value="${c}">${c}</option>`).join('');
            // After loading categories, also load case names (unfiltered)
            this.loadCaseNames();
        } catch (err) {
            console.error('Error loading categories:', err);
        }
    }

    // Load case names from server and populate caseNameFilter; optional category param
    async loadCaseNames(category = 'all') {
        const select = document.getElementById('caseNameFilter');
        if (!select) return;

        try {
            const url = category && category !== 'all' ? `/api/appointment-case-names?category=${encodeURIComponent(category)}` : '/api/appointment-case-names';
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('Failed to load case names');
            const data = await res.json();
            const caseNames = data.case_names || [];

            select.innerHTML = '<option value="all">All Case Names</option>' +
                caseNames.map(c => `<option value="${escapeHtml(c)}">${escapeHtml(c)}</option>`).join('');
        } catch (err) {
            console.error('Error loading case names:', err);
        }
    }
    // NEW METHOD: Save Excel backup with filtered data
    async saveExcelBackup() {
        const saveExcelBtn = document.getElementById('saveExcelBtn');
        const originalText = saveExcelBtn.innerHTML;
        
        // Check if there are filtered appointments to export
        if (!this.filteredAppointments || this.filteredAppointments.length === 0) {
            this.showSuccessToast('No appointments to export. Please adjust your filters.', 'warning');
            return;
        }
        
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
                    // Send the filtered appointments array and filter info for context
                    appointments: this.filteredAppointments,
                    filters: {
                        status: this.currentStatus,
                        category: this.currentCategory,
                        caseName: this.currentCaseName,
                        // branch: this.currentBranch
                    }
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                this.showSuccessToast(`Excel file saved successfully! (${this.filteredAppointments.length} records)`);
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
// Updated method: Save PDF backup with filtered data
async savePdfBackup() {
    const saveBackupBtn = document.getElementById('saveBackupBtn');
    const originalText = saveBackupBtn.innerHTML;
    
    // Check if there are filtered appointments to export
    if (!this.filteredAppointments || this.filteredAppointments.length === 0) {
        this.showSuccessToast('No appointments to export. Please adjust your filters.', 'warning');
        return;
    }
    
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
                // Send the filtered appointments array and filter info for context
                appointments: this.filteredAppointments,
                filters: {
                    status: this.currentStatus,
                    category: this.currentCategory,
                    caseName: this.currentCaseName,
                    // branch: this.currentBranch
                }
            })
        });

        const result = await response.json();

        if (response.ok && result.success) {
            this.showSuccessToast(`PDF file saved successfully! (${this.filteredAppointments.length} records)`);
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


    // NEW METHOD: Show success/warning toast
    showSuccessToast(message = 'Backup saved successfully!', type = 'success') {
    const toastElement = document.getElementById('successToast');
    if (toastElement) {
        // Update toast message
        const toastBody = toastElement.querySelector('.toast-body');
        if (toastBody) {
            toastBody.textContent = message;
        }
        
        // Update toast header color based on type
        const toastHeader = toastElement.querySelector('.toast-header');
        if (toastHeader) {
            if (type === 'warning') {
                toastHeader.className = 'toast-header bg-warning text-dark';
            } else if (type === 'danger') {
                toastHeader.className = 'toast-header bg-danger text-white';
            } else {
                toastHeader.className = 'toast-header bg-success text-white';
            }
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

        // Reset category and case name filters
        const categoryFilter = document.getElementById('categoryFilter');
        if (categoryFilter) {
            categoryFilter.value = 'all';
            this.currentCategory = 'all';
        }

        const caseNameFilter = document.getElementById('caseNameFilter');
        if (caseNameFilter) {
            caseNameFilter.value = 'all';
            this.currentCaseName = 'all';
        }

        // const branchFilter = document.getElementById('branchFilter');
        // if (branchFilter) {
        //     branchFilter.value = 'all';
        //     this.currentBranch = 'all';
        // }

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
        // Filter by category
        const matchesCategory = !this.currentCategory || this.currentCategory === 'all' || (appointment.category === this.currentCategory);
        // Filter by case name
        const matchesCaseName = !this.currentCaseName || this.currentCaseName === 'all' || (appointment.case_name === this.currentCaseName);
        // Filter by branch
        // const matchesBranch = !this.currentBranch || this.currentBranch === 'all' || (appointment.selected_branch === this.currentBranch);
        
        // Filter by search term - UPDATED to include date and time
        const matchesSearch = !searchTerm || 
            appointment.fullname?.toLowerCase().includes(searchTerm) ||
            appointment.email?.toLowerCase().includes(searchTerm) ||
            appointment.category?.toLowerCase().includes(searchTerm) ||
            appointment.case_name?.toLowerCase().includes(searchTerm) ||
            appointment.selected_date?.toLowerCase().includes(searchTerm) ||
            appointment.selected_time?.toLowerCase().includes(searchTerm) ||
            appointment.appointment_approval?.toLowerCase().includes(searchTerm);
        
        return matchesStatus && matchesCategory && matchesCaseName /* && matchesBranch */ && matchesSearch;
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
                    <!--<td>${appointment.selected_branch || 'N/A'}</td>-->
                    <td>${dateTime}</td>
                    <td>
                        <span class="status-text">
                            ${statusText}
                        </span>
                    </td>
                    <td>${appointment.processed_by || appointment.approved_by || 'N/A'}</td>
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
                <!-- <div class="mb-3">
                    <label class="form-label">Selected Branch</label>
                    <p class="text-gray-900">${appointment.selected_branch || 'N/A'}</p>
                </div> -->

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

// Add these methods to the FetchAppointments class
showPdfLoading() {
    const pdfLoading = document.getElementById('pdfLoading');
    const pdfError = document.getElementById('pdfError');
    if (pdfLoading) pdfLoading.classList.remove('d-none');
    if (pdfError) pdfError.classList.add('d-none');
}

showPdfError(message) {
    const pdfLoading = document.getElementById('pdfLoading');
    const pdfError = document.getElementById('pdfError');
    const pdfErrorMessage = document.getElementById('pdfErrorMessage');
    
    if (pdfLoading) pdfLoading.classList.add('d-none');
    if (pdfError && pdfErrorMessage) {
        pdfErrorMessage.textContent = message;
        pdfError.classList.remove('d-none');
    }
}

// Update the viewBackup method to use these
async viewBackup(backupId, extension) {
    try {
        if (extension === 'pdf') {
            // Switch to PDF tab
            const pdfTab = document.getElementById('pdf-preview-tab');
            if (pdfTab) {
                const tab = new bootstrap.Tab(pdfTab);
                tab.show();
                
                // Show loading state
                this.showPdfLoading();
                
                // Load PDF in iframe
                const pdfFrame = document.getElementById('pdfPreviewFrame');
                
                // Clear previous content
                pdfFrame.src = '';
                
                // Create URL with inline parameter
                const pdfUrl = `/backup/view/${backupId}?inline=true`;
                
                // Add timestamp to prevent caching issues
                const timestamp = new Date().getTime();
                const finalUrl = `${pdfUrl}&_=${timestamp}`;
                
                console.log('Loading PDF from:', finalUrl);
                
                // Set iframe source
                setTimeout(() => {
                    pdfFrame.src = finalUrl;
                }, 100);
            }
        } else if (extension === 'csv' || extension === 'xlsx') {
            // Switch to CSV tab
            const csvTab = document.getElementById('csv-preview-tab');
            if (csvTab) {
                const tab = new bootstrap.Tab(csvTab);
                tab.show();
                
                // Load CSV preview
                await this.loadCsvPreview(backupId);
            }
        } else {
            this.showToast('Preview not available for this file type.', 'warning');
        }
    } catch (error) {
        console.error('Error viewing backup:', error);
        this.showPdfError('Failed to load backup preview: ' + error.message);
        this.showToast('Failed to load backup preview.', 'danger');
    }
}
}

// Initialize the appointments manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.appointmentsManager = new FetchAppointments();
});

