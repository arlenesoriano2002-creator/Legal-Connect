function showLogoutConfirmation() {
    const logoutModal = new bootstrap.Modal(document.getElementById('logoutConfirmationModal'));
    logoutModal.show();
}

document.getElementById('confirmLogoutBtn').addEventListener('click', function() {
    document.getElementById('logout-form').submit();
});

// Optional: Add keyboard shortcut (Ctrl+Q) for logout
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'q') {
        e.preventDefault();
        showLogoutConfirmation();
    }
});

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