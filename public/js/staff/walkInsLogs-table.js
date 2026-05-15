// ====================== GLOBAL TOAST FUNCTION ======================
function showToast(type, title, message) {
    // Create toast element
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}:</strong> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    // Append to toast container (create one if it doesn't exist)
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Show the toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 5000
    });
    
    toast.show();
    
    // Remove toast from DOM after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

// ====================== PASSWORD STRENGTH CHECKER ======================

// Function to check password strength
function checkPasswordStrength(password) {
    let score = 0;
    let tips = [];
    
    // If password is empty, hide the strength meter
    if (!password || password.length === 0) {
        return {
            score: 0,
            strength: 'Empty',
            percent: 0,
            color: '#6c757d',
            tips: []
        };
    }
    
    // Check length
    if (password.length >= 8) score += 25;
    else if (password.length >= 6) score += 15;
    else if (password.length >= 4) score += 5;
    
    // Check for lowercase letters
    if (/[a-z]/.test(password)) score += 15;
    else tips.push('Add lowercase letters');
    
    // Check for uppercase letters
    if (/[A-Z]/.test(password)) score += 15;
    else tips.push('Add uppercase letters');
    
    // Check for numbers
    if (/[0-9]/.test(password)) score += 15;
    else tips.push('Add numbers');
    
    // Check for special characters
    if (/[^A-Za-z0-9]/.test(password)) score += 15;
    else tips.push('Add special characters (like !@#$%)');
    
    // Check for common patterns
    if (password.length >= 12) score += 15;
    
    // Ensure score doesn't exceed 100
    score = Math.min(score, 100);
    
    // Determine strength level
    let strength, color;
    if (score >= 80) {
        strength = 'Very Strong';
        color = '#198754'; // Green
    } else if (score >= 60) {
        strength = 'Strong';
        color = '#20c997'; // Teal
    } else if (score >= 40) {
        strength = 'Medium';
        color = '#ffc107'; // Yellow
    } else if (score >= 20) {
        strength = 'Weak';
        color = '#fd7e14'; // Orange
    } else {
        strength = 'Very Weak';
        color = '#dc3545'; // Red
    }
    
    return {
        score: score,
        strength: strength,
        percent: score,
        color: color,
        tips: tips.slice(0, 2) // Limit to 2 tips
    };
}

// Function to update password strength display
function updatePasswordStrength() {
    const password = $('#logbookPassword').val();
    const strengthContainer = $('#passwordStrengthContainer');
    const strengthText = $('#passwordStrengthText');
    const strengthBar = $('#passwordStrengthBar');
    const strengthTips = $('#passwordStrengthTips');
    
    // Show/hide strength meter
    if (!password || password.length === 0) {
        strengthContainer.hide();
        return;
    }
    
    // Show strength meter
    strengthContainer.show();
    
    // Check strength
    const result = checkPasswordStrength(password);
    
    // Update display
    strengthText.text(result.strength);
    strengthText.css('color', result.color);
    strengthBar.css({
        'width': result.percent + '%',
        'background-color': result.color
    });
    
    // Update tips
    if (result.tips.length > 0) {
        let tipsHtml = '<small class="text-muted d-block mb-1">';
        tipsHtml += '<i class="fas fa-lightbulb me-1"></i>';
        tipsHtml += 'Suggestions to improve:';
        tipsHtml += '</small>';
        tipsHtml += '<ul class="list-unstyled mb-0">';
        result.tips.forEach(tip => {
            tipsHtml += `<li class="text-muted small"><i class="fas fa-chevron-right me-1"></i>${tip}</li>`;
        });
        tipsHtml += '</ul>';
        strengthTips.html(tipsHtml);
    } else {
        strengthTips.html(`
            <small class="text-success d-block">
                <i class="fas fa-check-circle me-1"></i>
                Great password! Contains all recommended elements.
            </small>
        `);
    }
}

// Function to get password score for validation
function getPasswordScore(password) {
    return checkPasswordStrength(password).score;
}

// ====================== FIX BOOTSTRAP FOCUS TRAP ======================
// Add this fix at the beginning to prevent infinite loops
document.addEventListener('DOMContentLoaded', function() {
    // Fix Bootstrap modal focus trap issues
    const originalHandleFocusin = bootstrap.Modal.prototype._handleFocusin;
    bootstrap.Modal.prototype._handleFocusin = function(event) {
        try {
            originalHandleFocusin.call(this, event);
        } catch (error) {
            console.warn('Focus trap error caught:', error);
            // Don't rethrow the error
        }
    };
});

$(document).ready(function() {
    // ====================== FIX MODAL BACKDROP ISSUES ======================
    // Fix for Bootstrap focus trap infinite loop
    $(document).off('focusin.modal');
    
    // Clean up modal backdrops
    $(document).on('hidden.bs.modal', '.modal', function() {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 0) {
            backdrops.forEach(backdrop => backdrop.remove());
        }
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = '';
    });
    
    // Rest of your existing document.ready code remains the same...
    // (The same code you provided earlier, starting with the table initialization)
    
    // Check if table exists
    const tableElement = $('#walkinsTable');
    
    if (tableElement.length > 0) {
        // Count actual data rows (exclude the "no data" row if present)
        const dataRows = tableElement.find('tbody tr:has(td:not([colspan]))');
        
        if (dataRows.length > 0) {
            // Initialize DataTable if there are data rows
            try {
                // Dynamically compute column indices to avoid mismatches
                const headers = tableElement.find('thead th').map(function() { return $(this).text().trim().toUpperCase(); }).get();
                const createdIdx = headers.indexOf('CREATED');
                const purposeIdx = headers.indexOf('PURPOSE');
                const actionsIdx = headers.indexOf('ACTIONS');

                const orderIdx = createdIdx >= 0 ? createdIdx : Math.max(0, headers.length - 1);

                const dataTable = tableElement.DataTable({
                    dom: 'Bfrtip',
                    buttons: [], // Remove export buttons since we're using form submission
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                    language: {
                        search: '',
                        searchPlaceholder: 'Search walk-ins...',
                        emptyTable: 'No walk-in records found'
                    },
                    responsive: true,
                    order: [[orderIdx, 'desc']],
                    columnDefs: [
                        {
                            targets: actionsIdx >= 0 ? [actionsIdx] : [],
                            orderable: false,
                            searchable: false
                        }
                    ],
                    initComplete: function(settings, json) {
                        // Add refresh button to DataTables filter area
                        setTimeout(addRefreshButtonToDataTables, 100);
                    }
                });

                // Search input - global search
                $('#searchInput').on('keyup', function() {
                    dataTable.search($(this).val()).draw();
                });
                
                // Purpose filter (use computed index if available)
                const purposeIndex = (function(){
                    const hdrs = tableElement.find('thead th').map(function(){ return $(this).text().trim().toUpperCase(); }).get();
                    return hdrs.indexOf('PURPOSE');
                })();

                $('#purposeFilter').on('change', function() {
                    const selectedPurpose = $(this).val();
                    
                    if (selectedPurpose) {
                        if (purposeIndex >= 0) {
                            dataTable.column(purposeIndex).search('^' + selectedPurpose + '$', true, false).draw();
                        } else {
                            dataTable.search(selectedPurpose).draw();
                        }
                    } else {
                        if (purposeIndex >= 0) dataTable.column(purposeIndex).search('').draw();
                        else dataTable.search('').draw();
                    }
                });
                
                // Clear purpose filter button
                $('#clearPurposeFilter').on('click', function() {
                    $('#purposeFilter').val('').trigger('change');
                });
                
                // Function to apply both filters when needed
                function applyAllFilters() {
                    const searchTerm = $('#searchInput').val();
                    const selectedPurpose = $('#purposeFilter').val();
                    
                    // Apply global search
                    dataTable.search(searchTerm);
                    
                    // Apply purpose filter
                    const pIdx = (typeof purposeIndex !== 'undefined' && purposeIndex >= 0) ? purposeIndex : 2;
                    if (selectedPurpose) {
                        dataTable.column(pIdx).search('^' + selectedPurpose + '$', true, false);
                    } else {
                        dataTable.column(pIdx).search('');
                    }
                    
                    dataTable.draw();
                }
                
                // Initialize with any existing filters from URL parameters (optional)
                const urlParams = new URLSearchParams(window.location.search);
                const purposeParam = urlParams.get('purpose');
                if (purposeParam) {
                    $('#purposeFilter').val(purposeParam).trigger('change');
                }
                
            } catch (e) {
                console.error('DataTable initialization error:', e);
            }
        } else {
            // If no data rows, just show basic table
            console.log('No data rows found, skipping DataTable initialization');
            
            // Simple search functionality with purpose filter
                function filterRows() {
                const searchTerm = $('#searchInput').val().toLowerCase();
                const selectedPurpose = $('#purposeFilter').val().toLowerCase();
                const hdrs = tableElement.find('thead th').map(function(){ return $(this).text().trim().toUpperCase(); }).get();
                const purposeIdx = hdrs.indexOf('PURPOSE');
                
                $('tbody tr').each(function() {
                    const rowText = $(this).text().toLowerCase();
                    let purposeText = '';
                    if (purposeIdx >= 0) {
                        purposeText = $(this).find('td').eq(purposeIdx).text().toLowerCase();
                    } else {
                        purposeText = $(this).find('td:eq(3)').text().toLowerCase();
                    }
                    
                    const matchesSearch = searchTerm === '' || rowText.indexOf(searchTerm) > -1;
                    const matchesPurpose = selectedPurpose === '' || purposeText === selectedPurpose;
                    
                    $(this).toggle(matchesSearch && matchesPurpose);
                });
            }
            
            // Search input
            $('#searchInput').on('keyup', filterRows);
            
            // Purpose filter
            $('#purposeFilter').on('change', filterRows);
            
            // Clear purpose filter button
            $('#clearPurposeFilter').on('click', function() {
                $('#purposeFilter').val('');
                filterRows();
            });
        }
    }

    // ====================== REFRESH FUNCTIONALITY ======================
    
    // Main refresh button handler
    $('#refreshButton').on('click', function() {
        refreshTableData();
    });
    
    // Add refresh button to DataTables filter area
    function addRefreshButtonToDataTables() {
        // Find the DataTables filter area
        const filterDiv = $('.dataTables_filter');
        if (filterDiv.length > 0 && $('#dtRefreshButton').length === 0) {
            // Create refresh button
            const refreshButton = $('<button class="btn btn-sm btn-outline-primary ms-2" id="dtRefreshButton" title="Refresh Table">' +
                '<i class="fas fa-sync-alt"></i>' +
                '</button>');
            
            // Insert at the end of the filter div
            filterDiv.append(refreshButton);
            
            // Add click handler
            refreshButton.on('click', function() {
                refreshTableData();
            });
        }
    }
    
    function refreshTableData() {
        // Show loading state on main refresh button
        const refreshBtn = $('#refreshButton');
        const originalHtml = refreshBtn.html();
        refreshBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        refreshBtn.prop('disabled', true);
        
        // Also show loading on DataTables refresh button if exists
        const dtRefreshBtn = $('#dtRefreshButton');
        if (dtRefreshBtn.length > 0) {
            dtRefreshBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
            dtRefreshBtn.prop('disabled', true);
        }
        
        // Show toast notification
        showToast('info', 'Refreshing', 'Refreshing walk-in data...');
        
        // Send AJAX request to get updated table
        $.ajax({
            url: window.location.href, // Current page URL
            type: 'GET',
            dataType: 'html',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                try {
                    // Parse the response
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(response, 'text/html');
                    
                    // Extract table content
                    const newTable = doc.querySelector('#walkinsTable tbody');
                    
                    if (newTable) {
                        // Replace the table body
                        $('#walkinsTable tbody').html(newTable.innerHTML);
                        
                        // Check if we have DataTable initialized
                        const dataTable = $.fn.DataTable.isDataTable('#walkinsTable') ? $('#walkinsTable').DataTable() : null;
                        
                        if (dataTable) {
                            // Clear and redraw DataTable
                            dataTable.clear();
                            dataTable.rows.add($('#walkinsTable tbody tr'));
                            dataTable.draw();
                        }
                        
                        // Reinitialize delete buttons
                        reinitializeDeleteButtons();
                        
                        // Reinitialize purpose filter options if needed
                        const newPurposes = doc.querySelector('#purposeFilter');
                        if (newPurposes && newPurposes.innerHTML !== $('#purposeFilter').html()) {
                            $('#purposeFilter').html(newPurposes.innerHTML);
                        }
                        
                        // Show success message
                        showToast('success', 'Success', 'Table refreshed successfully!');
                    } else {
                        showToast('error', 'Error', 'Could not refresh table data.');
                    }
                } catch (error) {
                    console.error('Error processing refresh response:', error);
                    showToast('error', 'Error', 'Failed to process refresh response.');
                }
                
                // Reset button states
                resetRefreshButtonState(refreshBtn, originalHtml);
                if (dtRefreshBtn.length > 0) {
                    dtRefreshBtn.html('<i class="fas fa-sync-alt"></i>');
                    dtRefreshBtn.prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error refreshing table:', error);
                showToast('error', 'Error', 'Failed to refresh table data.');
                
                // Reset button states on error
                resetRefreshButtonState(refreshBtn, originalHtml);
                if (dtRefreshBtn.length > 0) {
                    dtRefreshBtn.html('<i class="fas fa-sync-alt"></i>');
                    dtRefreshBtn.prop('disabled', false);
                }
            }
        });
    }
    
    function resetRefreshButtonState(button, originalHtml) {
        button.html(originalHtml);
        button.prop('disabled', false);
    }
    
    function reinitializeDeleteButtons() {
        // Remove existing click handlers and reattach
        $('.delete-walkin-btn').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const walkinId = $(this).data('id');
            const walkinName = $(this).data('name');
            currentDeleteWalkinId = walkinId;
            
            showDeleteWalkinConfirmation(walkinName);
        });
    }

    // ====================== KEYBOARD SHORTCUTS ======================
    
    $(document).on('keydown', function(e) {
        // F5 key for refresh
        if (e.key === 'F5') {
            e.preventDefault();
            refreshTableData();
        }
        
        // Ctrl+R for refresh
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
            e.preventDefault();
            refreshTableData();
        }
    });

    // ====================== WALK-IN DELETE FUNCTIONALITY ======================
    
    let currentDeleteWalkinId = null;

    // NEW FUNCTION: Reset delete button to original state
    function resetDeleteButtonState() {
        const deleteBtn = $('#confirmDeleteWalkinBtn');
        deleteBtn.html('<i class="fas fa-trash-alt me-1"></i> Delete');
        deleteBtn.prop('disabled', false);
        // Remove any spinner classes that might persist
        deleteBtn.find('.spinner-border').remove();
    }

    // Delete walk-in button handler
    $(document).on('click', '.delete-walkin-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const walkinId = $(this).data('id');
        const walkinName = $(this).data('name');
        currentDeleteWalkinId = walkinId;
        
        showDeleteWalkinConfirmation(walkinName);
    });

    function showDeleteWalkinConfirmation(walkinName) {
        // Set the walk-in name in the modal
        $('#deleteWalkinName').text(walkinName);
        
        // RESET: Reset the delete button to its original state BEFORE showing modal
        resetDeleteButtonState();
        
        // Create and show delete modal for walk-in
        const deleteWalkinModalElement = document.getElementById('deleteWalkinModal');
        const deleteWalkinModal = new bootstrap.Modal(deleteWalkinModalElement, {
            backdrop: 'static',
            keyboard: true
        });
        
        // Show delete modal
        deleteWalkinModal.show();
        
        // Remove any existing click handlers to prevent duplication
        $('#confirmDeleteWalkinBtn').off('click');
        
        // Handle confirm delete button click
        $('#confirmDeleteWalkinBtn').on('click', function() {
            deleteWalkinRecord(currentDeleteWalkinId, deleteWalkinModal);
        });
        
        // Handle modal close to reset state - CRITICAL FIX
        $(deleteWalkinModalElement).off('hidden.bs.modal').on('hidden.bs.modal', function() {
            currentDeleteWalkinId = null;
            // RESET: Reset button state when modal is closed
            resetDeleteButtonState();
        });
        
        // Handle cancel button click to reset state
        $(deleteWalkinModalElement).find('.btn-secondary').off('click').on('click', function() {
            resetDeleteButtonState();
        });
    }

    function deleteWalkinRecord(walkinId, modalInstance) {
        // Show loading state on delete button
        const deleteBtn = $('#confirmDeleteWalkinBtn');
        const originalText = '<i class="fas fa-trash-alt me-1"></i> Delete';
        deleteBtn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Deleting...');
        deleteBtn.prop('disabled', true);
        
        // Send delete request
        $.ajax({
            url: '/staff/walkins/delete/' + walkinId,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showToast('success', 'Success', 'Walk-in record deleted successfully.');
                    
                    // Hide the modal
                    modalInstance.hide();
                    
                    // RESET: Button state will be reset in modal's hidden event
                    
                    // Remove the row from DataTable if it exists
                    const dataTable = $('#walkinsTable').DataTable();
                    if (dataTable) {
                        // Find and remove the row
                        dataTable.row($('.delete-walkin-btn[data-id="' + walkinId + '"]').closest('tr'))
                            .remove()
                            .draw();
                    } else {
                        // For simple table, remove the row directly
                        $(`.delete-walkin-btn[data-id="${walkinId}"]`).closest('tr').remove();
                        
                        // Check if table is now empty
                        if ($('#walkinsTable tbody tr').length === 0) {
                            $('#walkinsTable tbody').html(`
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> No walk-in records found.
                                        </div>
                                    </td>
                                </tr>
                            `);
                        }
                    }
                } else {
                    // Show error message
                    showToast('error', 'Error', response.message || 'Failed to delete walk-in record.');
                    
                    // RESET: Reset button state on error
                    resetDeleteButtonState();
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Error deleting walk-in record';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                // Show error message
                showToast('error', 'Error', errorMessage);
                
                // RESET: Reset button state on error
                resetDeleteButtonState();
            }
        });
    }

    // ====================== BACKUP LOGS MODAL ======================
    // Check if modal elements exist before initializing
    const backupLogsModalElement = document.getElementById('backupLogsModal');
    const backupPreviewModalElement = document.getElementById('backupPreviewModal');
    
    // Only initialize modals if elements exist
    let backupLogsModal = null;
    let backupPreviewModal = null;
    
    if (backupLogsModalElement) {
        backupLogsModal = new bootstrap.Modal(backupLogsModalElement, {
            backdrop: 'static',
            keyboard: true
        });
        
        // Properly handle modal hide to remove backdrop
        backupLogsModalElement.addEventListener('hidden.bs.modal', function() {
            // Force backdrop removal if needed
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            // Ensure body class is removed
            document.body.classList.remove('modal-open');
            document.body.style = '';
        });
    }
    
    if (backupPreviewModalElement) {
        backupPreviewModal = new bootstrap.Modal(backupPreviewModalElement, {
            backdrop: 'static',
            keyboard: true
        });
        
        // Properly handle modal hide to remove backdrop
        backupPreviewModalElement.addEventListener('hidden.bs.modal', function() {
            // Force backdrop removal if needed
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            // Ensure body class is removed
            document.body.classList.remove('modal-open');
            document.body.style = '';
            
            // Show the logs modal again when preview modal is closed
            setTimeout(() => {
                if (backupLogsModal) {
                    backupLogsModal.show();
                }
            }, 300);
        });
    }
    
    let currentPreviewBackupId = null;
    
    // Open backup logs modal when button is clicked - only if modal exists
    $('[data-bs-target="#backupLogsModal"]').on('click', function() {
        if (backupLogsModal) {
            loadBackupLogs();
            backupLogsModal.show();
        }
    });
    
    // Load backup logs via AJAX - FIXED VERSION
    function loadBackupLogs() {
        $.ajax({
            url: '/staff/walkins/logs',
            type: 'GET',
            dataType: 'html',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Extract backup logs from the response
                const parser = new DOMParser();
                const doc = parser.parseFromString(response, 'text/html');
                const backupLogsHtml = doc.getElementById('backupLogsList')?.innerHTML;
                
                if (backupLogsHtml) {
                    $('#backupLogsList').html(backupLogsHtml);
                    initializeBackupLogsActions();
                } else {
                    $('#backupLogsList').html(`
                        <tr>
                            <td colspan="3" class="text-center">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No backup logs found.
                                </div>
                            </td>
                        </tr>
                    `);
                }
            },
            error: function() {
                $('#backupLogsList').html(`
                    <tr>
                        <td colspan="3" class="text-center">
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> Error loading backup logs.
                            </div>
                        </td>
                    </tr>
                `);
            }
        });
    }
    
    // Initialize backup log action buttons
    function initializeBackupLogsActions() {
        // Preview button - only add if preview modal exists
        if (backupPreviewModalElement) {
            $('.preview-backup-btn').off('click').on('click', function(e) {
                e.preventDefault();
                const backupId = $(this).data('id');
                previewBackupFile(backupId);
            });
        }
        
        // Download button
        $('.download-backup-btn').off('click').on('click', function(e) {
            e.preventDefault();
            const backupId = $(this).data('id');
            downloadBackupFile(backupId);
        });
        
        // Delete button
        $('.delete-backup-btn').off('click').on('click', function(e) {
            e.preventDefault();
            const backupId = $(this).data('id');
            const fileName = $(this).data('name');
            showDeleteConfirmation(backupId, fileName);
        });
    }

    function showDeleteConfirmation(backupId, fileName) {
        $('#deleteFileName').text(fileName);
        $('#confirmDeleteBtn').data('id', backupId);
        
        // Remove any existing backdrops to prevent stacking issues
        const existingBackdrops = document.querySelectorAll('.modal-backdrop');
        existingBackdrops.forEach(backdrop => {
            backdrop.style.zIndex = '1049';
        });
        
        // Create and show delete modal
        const deleteModalElement = document.getElementById('deleteConfirmationModal');
        const deleteModal = new bootstrap.Modal(deleteModalElement, {
            backdrop: 'static',
            keyboard: true
        });
        
        // Show delete modal
        deleteModal.show();
        
        // Adjust backdrop z-index
        setTimeout(() => {
            const deleteBackdrop = document.querySelectorAll('.modal-backdrop');
            if (deleteBackdrop.length > 1) {
                // Set the delete modal's backdrop to be on top
                deleteBackdrop[deleteBackdrop.length - 1].style.zIndex = '1050';
            }
        }, 10);
        
        // Handle confirm delete button click
        $('#confirmDeleteBtn').off('click').on('click', function() {
            const idToDelete = $(this).data('id');
            deleteBackupFile(idToDelete, deleteModal);
        });
        
        // Handle delete modal close to go back to backup modal
        $(deleteModalElement).off('hidden.bs.modal').on('hidden.bs.modal', function() {
            // Remove delete modal backdrop
            const deleteBackdrop = document.querySelectorAll('.modal-backdrop');
            if (deleteBackdrop.length > 0) {
                deleteBackdrop[deleteBackdrop.length - 1].remove();
            }
            
            // Show backup logs modal again
            if (backupLogsModal) {
                setTimeout(() => {
                    backupLogsModal.show();
                }, 50);
            }
        });
    }

    function deleteBackupFile(backupId, modalInstance) {
        // Show loading state on delete button
        const deleteBtn = $('#confirmDeleteBtn');
        const originalText = deleteBtn.html();
        deleteBtn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Deleting...');
        deleteBtn.prop('disabled', true);
        
        // Send delete request
        $.ajax({
            url: '/staff/walkins/logs/delete-backup/' + backupId,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showToast('success', 'Success', 'Backup file deleted successfully.');
                    
                    // Hide the modal
                    modalInstance.hide();
                    
                    // Reload the backup logs
                    loadBackupLogs();
                } else {
                    // Show error message
                    showToast('error', 'Error', response.message || 'Failed to delete backup file.');
                    
                    // Reset delete button
                    deleteBtn.html(originalText);
                    deleteBtn.prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Error deleting backup file';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                // Show error message
                showToast('error', 'Error', errorMessage);
                
                // Reset delete button
                deleteBtn.html(originalText);
                deleteBtn.prop('disabled', false);
            }
        });
    }

    // Preview backup file - FIXED WITH CSRF TOKEN
    function previewBackupFile(backupId) {
        currentPreviewBackupId = backupId;
        
        // Show loading state
        $('#fileInfo').hide();
        $('#pdfPreviewSection').hide();
        $('#excelPreviewSection').hide();
        $('#csvPreviewSection').hide();
        $('#previewError').hide();
        $('#downloadPreviewBtn').hide();
        
        // Clear previous content
        $('#pdfPreviewFrame').attr('src', '');
        $('#excelPreviewTable').empty();
        $('#csvPreviewTable').empty();
        
        // Get file details via API
        $.ajax({
            url: '/staff/walkins/logs/view-backup/' + backupId,
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Show file info
                    $('#backupFileName').text(response.filename);
                    $('#backupFileDate').text(response.date);
                    $('#backupFileType').text(response.type || 'Unknown');
                    $('#backupFileSize').text(response.file_size || 'N/A');
                    $('#fileInfo').show();
                    
                    // Set download link
                    $('#downloadPreviewBtn').attr('href', '/staff/walkins/logs/download-file/' + backupId);
                    
                    // Handle different file types
                    const fileExtension = response.filename.split('.').pop().toLowerCase();
                    
                    if (fileExtension === 'pdf') {
                        showPdfPreview(response);
                    } else if (fileExtension === 'xlsx' || fileExtension === 'xls') {
                        showExcelPreview(response);
                    } else if (fileExtension === 'csv') {
                        showCsvPreview(response);
                    } else {
                        showPreviewError('Unsupported file format for preview. Please download the file.');
                    }
                } else {
                    showPreviewError(response.message || 'Unable to load file');
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Error loading file preview';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showPreviewError(errorMessage);
            },
            complete: function() {
                $('#downloadPreviewBtn').show();
                
                // Proper modal transition
                if (backupLogsModal) {
                    // Hide the logs modal first
                    backupLogsModal.hide();
                    
                    // Wait for the logs modal to be fully hidden, then show preview modal
                    $(backupLogsModalElement).one('hidden.bs.modal', function () {
                        // Small delay to ensure smooth transition
                        setTimeout(() => {
                            if (backupPreviewModal) {
                                backupPreviewModal.show();
                            }
                        }, 50);
                    });
                } else {
                    // If there's no logs modal, just show the preview modal
                    if (backupPreviewModal) {
                        setTimeout(() => {
                            backupPreviewModal.show();
                        }, 50);
                    }
                }
            }
        });
    }
    
    // Show PDF preview
    function showPdfPreview(response) {
        try {
            // If content is base64 encoded
            if (response.content) {
                const pdfDataUrl = 'data:application/pdf;base64,' + response.content;
                $('#pdfPreviewFrame').attr('src', pdfDataUrl + '#toolbar=1&navpanes=1&scrollbar=1');
                $('#pdfPreviewSection').show();
                $('#pdfControls').show();
            } else if (response.url) {
                // If we have a direct URL
                $('#pdfPreviewFrame').attr('src', response.url);
                $('#pdfPreviewSection').show();
                $('#pdfControls').show();
            } else {
                showPreviewError('No content available for PDF preview');
            }
        } catch (e) {
            console.error('Error showing PDF preview:', e);
            showPreviewError('Error displaying PDF file');
        }
    }
    
    // Show Excel preview (table view)
    function showExcelPreview(response) {
        try {
            // Clear previous content
            $('#excelPreviewTable').empty();
            
            if (response.content && Array.isArray(response.content)) {
                // response.content should be an array of arrays representing rows
                let tableHTML = '';
                
                // Add header row if available
                if (response.content.length > 0) {
                    tableHTML += '<thead><tr>';
                    response.content[0].forEach(cell => {
                        tableHTML += `<th>${escapeHtml(cell || '')}</th>`;
                    });
                    tableHTML += '</tr></thead><tbody>';
                    
                    // Add data rows (skip first row if it's header)
                    const startRow = response.hasHeader ? 1 : 0;
                    for (let i = startRow; i < response.content.length; i++) {
                        tableHTML += '<tr>';
                        response.content[i].forEach(cell => {
                            tableHTML += `<td>${escapeHtml(cell || '')}</td>`;
                        });
                        tableHTML += '</tr>';
                    }
                    tableHTML += '</tbody>';
                } else {
                    tableHTML = '<tbody><tr><td colspan="10" class="text-center text-muted">No data found</td></tr></tbody>';
                }
                
                $('#excelPreviewTable').html(tableHTML);
                $('#excelPreviewSection').show();
                $('#pdfControls').hide();
            } else if (response.html) {
                // If server returns HTML table
                $('#excelPreviewTable').html(response.html);
                $('#excelPreviewSection').show();
                $('#pdfControls').hide();
            } else {
                showPreviewError('Cannot preview this Excel file. Please download it.');
            }
        } catch (e) {
            console.error('Error showing Excel preview:', e);
            showPreviewError('Error displaying Excel file');
        }
    }
    
    // Show CSV preview (table view) - FIXED with PapaParse
    function showCsvPreview(response) {
        try {
            // Clear previous content
            $('#csvPreviewTable').empty();
            
            if (response.content && typeof response.content === 'string') {
                // Parse CSV content using PapaParse
                const results = Papa.parse(response.content.trim(), {
                    header: response.hasHeader,
                    skipEmptyLines: true,
                    trimHeaders: true,
                    dynamicTyping: false,
                    encoding: 'UTF-8'
                });
                
                let tableHTML = '';
                
                if (results.data.length > 0) {
                    if (response.hasHeader && results.meta.fields) {
                        // Add header row
                        tableHTML += '<thead><tr>';
                        results.meta.fields.forEach(header => {
                            tableHTML += `<th>${escapeHtml(header || '')}</th>`;
                        });
                        tableHTML += '</tr></thead><tbody>';
                        
                        // Add data rows
                        results.data.forEach(row => {
                            tableHTML += '<tr>';
                            results.meta.fields.forEach(header => {
                                tableHTML += `<td>${escapeHtml(row[header] || '')}</td>`;
                            });
                            tableHTML += '</tr>';
                        });
                        tableHTML += '</tbody>';
                    } else {
                        // No header, use array format
                        tableHTML += '<tbody>';
                        results.data.forEach(rowArray => {
                            tableHTML += '<tr>';
                            rowArray.forEach(cell => {
                                tableHTML += `<td>${escapeHtml(cell || '')}</td>`;
                            });
                            tableHTML += '</tr>';
                        });
                        tableHTML += '</tbody>';
                    }
                } else {
                    tableHTML = '<tbody><tr><td colspan="10" class="text-center text-muted">No data found</td></tr></tbody>';
                }
                
                $('#csvPreviewTable').html(tableHTML);
                $('#csvPreviewSection').show();
                $('#pdfControls').hide();
            } else {
                showPreviewError('Cannot preview this CSV file. Please download it.');
            }
        } catch (e) {
            console.error('Error showing CSV preview:', e);
            showPreviewError('Error displaying CSV file');
        }
    }
    
    // HTML escape function
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Show preview error
    function showPreviewError(message) {
        $('#errorMessage').text(message);
        $('#previewError').show();
        
        // Set download button
        $('#downloadInsteadBtn').off('click').on('click', function() {
            if (currentPreviewBackupId) {
                downloadBackupFile(currentPreviewBackupId);
            }
        });
    }
    
    // Download backup file
    function downloadBackupFile(backupId) {
        window.location.href = '/staff/walkins/logs/download-file/' + backupId;
    }
    
    // ====================== EXPORT FILTER FUNCTIONS ======================
    
    // Function to capture current table state
    function getCurrentTableState() {
        const hdrs = tableElement.find('thead th').map(function(){ return $(this).text().trim().toUpperCase(); }).get();
        const createdIdx = hdrs.indexOf('CREATED');
        const defaultSortCol = createdIdx >= 0 ? createdIdx : 6;

        const state = {
            search: '',
            purpose: '',
            sortColumn: defaultSortCol, // Default sort column (Created At)
            sortOrder: 'desc' // Default sort order
        };
        
        // Get the DataTable instance if it exists
        const dataTable = $('#walkinsTable').DataTable();
        if (dataTable) {
            // Get current search term
            state.search = dataTable.search();
            
            // Get current purpose filter
            const purposeFilter = $('#purposeFilter').val();
            state.purpose = purposeFilter || '';
            
            // Get current sort state
            const order = dataTable.order();
            if (order.length > 0) {
                state.sortColumn = order[0][0];
                state.sortOrder = order[0][1];
            }
        } else {
            // For simple table (no DataTables)
            state.search = $('#searchInput').val() || '';
            state.purpose = $('#purposeFilter').val() || '';
        }
        
        return state;
    }
    
    // Export form submission handlers
    // Use a namespaced submit handler to avoid duplicate bindings across pages
    $('#excelExportForm, #pdfExportForm').off('submit.export').on('submit.export', function(e) {
        e.preventDefault(); // Prevent immediate submission
        
        const form = $(this);
        const state = getCurrentTableState();
        const button = form.find('button[type="submit"]');
        
        // Store original button text
        if (!button.data('original-text')) {
            button.data('original-text', button.html());
        }
        
        // Populate hidden fields
        form.find('input[name="search"]').val(state.search);
        form.find('input[name="purpose"]').val(state.purpose);
        form.find('input[name="sort_column"]').val(state.sortColumn);
        form.find('input[name="sort_order"]').val(state.sortOrder);
        
        // Show loading indicator
        const originalText = button.data('original-text');
        button.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Preparing...');
        button.prop('disabled', true);
        
        // Submit the form after a short delay; only remove the namespaced submit handler
        setTimeout(() => {
            form.off('submit.export').submit();
        }, 100);
    });
    
    // Reset button state if form submission fails
    $(document).on('ajaxError', function() {
        $('#saveExcelBtn, #savePdfBtn').each(function() {
            const originalText = $(this).data('original-text');
            if (originalText) {
                $(this).html(originalText);
            }
            $(this).prop('disabled', false);
        });
    });
    
    // Store original button text on page load
    $('#saveExcelBtn').data('original-text', $('#saveExcelBtn').html());
    $('#savePdfBtn').data('original-text', $('#savePdfBtn').html());
    
    // Initialize when page loads - only if modals exist
    if (backupLogsModalElement || backupPreviewModalElement) {
        initializeBackupLogsActions();
    }
    
    // Initialize backup logs actions on page load if table exists
    if ($('#backupLogsList').length && ($('#backupLogsList').find('.preview-backup-btn').length || 
        $('#backupLogsList').find('.download-backup-btn').length)) {
        initializeBackupLogsActions();
    }
    
    // PDF zoom controls
    $('#zoomInBtn').on('click', function() {
        const iframe = document.getElementById('pdfPreviewFrame');
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        const body = iframeDoc.body;
        const currentZoom = parseFloat(body.style.zoom) || 1;
        body.style.zoom = (currentZoom + 0.1).toString();
    });
    
    $('#zoomOutBtn').on('click', function() {
        const iframe = document.getElementById('pdfPreviewFrame');
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        const body = iframeDoc.body;
        const currentZoom = parseFloat(body.style.zoom) || 1;
        body.style.zoom = Math.max(0.5, currentZoom - 0.1).toString();
    });
    
    $('#resetZoomBtn').on('click', function() {
        const iframe = document.getElementById('pdfPreviewFrame');
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        const body = iframeDoc.body;
        body.style.zoom = '1';
    });
    
    // ====================== INITIALIZE REFRESH BUTTON IN DATATABLES ======================
    // Wait a bit to ensure DataTables is fully initialized, then add refresh button
    setTimeout(function() {
        addRefreshButtonToDataTables();
    }, 500);
});

// ====================== LOGBOOK PASSWORD FUNCTIONALITY ======================
const logbookPasswordModalElement = document.getElementById('logbookPasswordModal');
let logbookPasswordModal = null;

if (logbookPasswordModalElement) {
    logbookPasswordModal = new bootstrap.Modal(logbookPasswordModalElement, {
        backdrop: 'static',
        keyboard: true
    });
}

// Create confirmation modal element if it doesn't exist
function createConfirmPasswordModal() {
    if ($('#logbookPasswordConfirmModal').length === 0) {
        const confirmModalHtml = `
            <div class="modal fade" id="logbookPasswordConfirmModal" tabindex="-1" aria-labelledby="logbookPasswordConfirmModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title" id="logbookPasswordConfirmModalLabel">
                                <i class="fas fa-exclamation-triangle me-2"></i> Confirm Credentials Update
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3" id="confirmMessage">Are you sure you want to update the logbook credentials?</p>
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Security Note:</strong> The password field is intentionally left blank. 
                                Current password will be kept unless you entered a new one.
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label"><strong>Username:</strong></label>
                                <input type="text" class="form-control" id="confirmUsername" readonly>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><strong>Password Action:</strong></label>
                                <div class="alert alert-info" id="passwordActionAlert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <span id="passwordActionText">Keeping current password (field was empty)</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Cancel
                            </button>
                            <button type="button" class="btn btn-warning" id="confirmSaveLogbookPasswordBtn">
                                <i class="fas fa-save me-1"></i> Confirm Update
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('body').append(confirmModalHtml);
    }
}

// Initialize confirmation modal
let logbookPasswordConfirmModal = null;

// Open logbook password modal
$(document).on('click', '[data-bs-target="#logbookPasswordModal"]', function() {
    if (logbookPasswordModal) {
        loadLogbookPassword();
        logbookPasswordModal.show();
    }
});

// Load logbook password (only for id=1, branch=diffun)
function loadLogbookPassword() {
    // Show loading state only for username
    $('#logbookUsername').val('Loading...');
    // Password field remains empty by default
    $('#logbookPassword').val('');
    $('#saveLogbookPasswordBtn').prop('disabled', true);
    
    $.ajax({
        url: '/staff/walkins/logs/logbook-password',
        type: 'GET',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success && response.data) {
                // Populate only the username, NOT the password
                $('#logbookId').val(response.data.id);
                $('#logbookUsername').val(response.data.username);
                // DO NOT populate password field - keep it empty for security
                $('#logbookPassword').val('');
                $('#logbookBranch').val(response.data.branch);
                
                // Enable save button
                $('#saveLogbookPasswordBtn').prop('disabled', false);
            } else {
                showToast('error', 'Error', response.message || 'Failed to load logbook password');
                
                // Set default values
                $('#logbookUsername').val('');
                $('#logbookPassword').val('');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading logbook password:', error);
            showToast('error', 'Error', 'Failed to load logbook password');
            
            $('#logbookUsername').val('');
            $('#logbookPassword').val('');
        }
    });
}

// Toggle password visibility in main modal
$(document).on('click', '.toggle-password', function() {
    const targetId = $(this).data('target');
    const input = $('#' + targetId);
    const icon = $(this).find('i');
    
    // Check current input type
    if (input.attr('type') === 'password') {
        // Changing from hidden (dots) to visible (text)
        input.attr('type', 'text');
        icon.removeClass('fa-eye-slash').addClass('fa-eye'); 
        $(this).attr('title', 'Hide password');
    } else {
        // Changing from visible (text) to hidden (dots)
        input.attr('type', 'password');
        icon.removeClass('fa-eye').addClass('fa-eye-slash'); 
        $(this).attr('title', 'Show password');
    }
});

// Handle save password button click - Show confirmation modal
$(document).on('click', '#saveLogbookPasswordBtn', function(e) {
    e.preventDefault();
    showPasswordConfirmModal();
});

function showPasswordConfirmModal() {
    // Validate form first
    const username = $('#logbookUsername').val().trim();
    const password = $('#logbookPassword').val().trim();
    
    if (!username) {
        showToast('error', 'Validation Error', 'Please fill in the username field');
        return;
    }
    
    // Create confirmation modal if it doesn't exist
    createConfirmPasswordModal();
    
    // Populate confirmation modal
    $('#confirmUsername').val(username);
    
    // Get the confirmation modal element
    const confirmModalElement = document.getElementById('logbookPasswordConfirmModal');
    
    // Initialize modal if not already done
    if (!logbookPasswordConfirmModal) {
        logbookPasswordConfirmModal = new bootstrap.Modal(confirmModalElement, {
            backdrop: 'static',
            keyboard: true
        });
    }
    
    // Remove any existing click handlers to prevent duplication
    $('#confirmSaveLogbookPasswordBtn').off('click');
    
    // Handle confirm button click
    $('#confirmSaveLogbookPasswordBtn').on('click', function() {
        // Close confirmation modal
        logbookPasswordConfirmModal.hide();
        // Save the password
        saveLogbookPassword();
    });
    
    // Handle modal hidden event to clean up
    $(confirmModalElement).off('hidden.bs.modal').on('hidden.bs.modal', function() {
        // Reset button states if needed
        $('#saveLogbookPasswordBtn').prop('disabled', false);
    });
    
    // Show confirmation modal
    logbookPasswordConfirmModal.show();
}

// FIXED: Save logbook password with proper button reset
function saveLogbookPassword() {
    const saveBtn = $('#saveLogbookPasswordBtn');
    const originalText = '<i class="fas fa-save me-1"></i> Save Changes';
    const logbookId = $('#logbookId').val();
    
    // Get form values
    const username = $('#logbookUsername').val().trim();
    const password = $('#logbookPassword').val().trim(); // This will be empty or new password
    
    if (!username) {
        showToast('error', 'Validation Error', 'Please fill in the username field');
        return;
    }
    
    // Password is optional - if empty, it means keep current password
    if (!password) {
        // Show confirmation if password is empty (keeping current password)
        if (!confirm('Password field is empty. This will keep the current password. Continue?')) {
            return;
        }
    }
    
    // Show loading on original button
    saveBtn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...');
    saveBtn.prop('disabled', true);
    
    const formData = {
        id: logbookId,
        username: username,
        password: password, // Send empty or new password
        branch: 'diffun',
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    
    $.ajax({
        url: '/staff/walkins/logs/logbook-password/update',
        type: 'PUT',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast('success', 'Success', response.message || 'Logbook credentials updated successfully');
                
                // Close main modal after a short delay
                setTimeout(() => {
                    if (logbookPasswordModal) {
                        logbookPasswordModal.hide();
                    }
                }, 1500);
                
                // Reset button - IMPORTANT FIX
                setTimeout(() => {
                    saveBtn.html(originalText);
                    saveBtn.prop('disabled', false);
                }, 100);
            } else {
                showToast('error', 'Error', response.message || 'Failed to update credentials');
                
                // Reset button
                saveBtn.html(originalText);
                saveBtn.prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            let errorMessage = 'Error updating credentials';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            showToast('error', 'Error', errorMessage);
            
            // Reset button
            saveBtn.html(originalText);
            saveBtn.prop('disabled', false);
        }
    });
}

// Initialize modal events
if (logbookPasswordModalElement) {
    // Reset form when modal is closed
    $(logbookPasswordModalElement).on('hidden.bs.modal', function() {
        // Enable save button
        $('#saveLogbookPasswordBtn').html('<i class="fas fa-save me-1"></i> Save Changes');
        $('#saveLogbookPasswordBtn').prop('disabled', false);
        
        // Reset password to hidden state with correct icon
        $('#logbookPassword').attr('type', 'password');
        $('#logbookPassword').val(''); // Clear the password field
        $('.toggle-password i').removeClass('fa-eye').addClass('fa-eye-slash');
        $('.toggle-password').attr('title', 'Show password');
        
        // Hide password strength meter
        $('#passwordStrengthContainer').hide();
    });
    
    // Load data when modal is shown
    $(logbookPasswordModalElement).on('shown.bs.modal', function() {
        // Ensure password field is hidden and icon is correct
        $('#logbookPassword').attr('type', 'password');
        $('.toggle-password i').removeClass('fa-eye').addClass('fa-eye-slash');
        $('.toggle-password').attr('title', 'Show password');
        
        // Initialize password strength checking
        $('#logbookPassword').off('input.passwordStrength').on('input.passwordStrength', function() {
            updatePasswordStrength();
        });
        
        // Focus on username field
        setTimeout(() => {
            $('#logbookUsername').focus();
        }, 300);
    });
}

// ====================== CSS STYLES ======================
const logbookStyle = document.createElement('style');
logbookStyle.textContent = `
    .toggle-password {
        min-width: 45px;
        transition: all 0.2s ease;
        border-left: 1px solid #dee2e6;
    }
    
    .toggle-password:hover {
        background-color: #f8f9fa;
        color: #495057;
    }
    
    #logbookPassword {
        letter-spacing: 1px;
    }
    
    /* Eye icon color states */
    .toggle-password .fa-eye {
        color: #198754; /* Green when password is visible */
    }
    
    .toggle-password .fa-eye-slash {
        color: #6c757d; /* Gray when password is hidden */
    }
    
    .toggle-password:hover .fa-eye {
        color: #0d6efd; /* Blue on hover when visible */
    }
    
    .toggle-password:hover .fa-eye-slash {
        color: #495057; /* Darker gray on hover when hidden */
    }
    
    .modal-header.bg-warning {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    }
    
    .btn-warning {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #000;
        font-weight: 500;
    }
    
    .btn-warning:hover {
        background-color: #e0a800;
        border-color: #e0a800;
        color: #000;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(255, 193, 7, 0.2);
    }
    
    .btn-warning:active {
        transform: translateY(0);
    }
    
    /* Confirmation modal specific styles */
    #logbookPasswordConfirmModal .modal-body {
        padding: 1.5rem;
    }
    
    #logbookPasswordConfirmModal .alert-warning {
        background-color: rgba(255, 193, 7, 0.1);
        border-left: 4px solid #ffc107;
    }
    
    #logbookPasswordConfirmModal .alert-info {
        background-color: rgba(13, 110, 253, 0.1);
        border-left: 4px solid #0d6efd;
    }
    
    #confirmUsername {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 0.5rem;
        border-radius: 0.25rem;
    }
    
    /* Password strength meter styles */
    #passwordStrengthContainer {
        transition: all 0.3s ease;
    }
    
    #passwordStrengthBar {
        transition: width 0.3s ease, background-color 0.3s ease;
        border-radius: 2px;
    }
    
    #passwordStrengthTips ul {
        margin-left: 1.5rem;
    }
    
    #passwordStrengthTips li {
        font-size: 0.75rem;
        line-height: 1.4;
        margin-bottom: 0.25rem;
    }
    
    #passwordStrengthTips .fa-chevron-right {
        font-size: 0.6rem;
    }
    
    /* Strength level colors */
    .strength-very-weak { color: #dc3545; }
    .strength-weak { color: #fd7e14; }
    .strength-medium { color: #ffc107; }
    .strength-strong { color: #20c997; }
    .strength-very-strong { color: #198754; }
    
    /* Visual feedback for strength levels */
    .strength-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 4px;
    }
    
    .strength-dot.very-weak { background-color: #dc3545; }
    .strength-dot.weak { background-color: #fd7e14; }
    .strength-dot.medium { background-color: #ffc107; }
    .strength-dot.strong { background-color: #20c997; }
    .strength-dot.very-strong { background-color: #198754; }
`;
document.head.appendChild(logbookStyle);