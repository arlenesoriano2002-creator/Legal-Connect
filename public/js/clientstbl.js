// Auto-close after 4 seconds
window.onload = function() {
    const modal = document.getElementById('flashModal');
    if(modal){
        setTimeout(() => {
            modal.style.display = 'none';
        }, 4000);
    }
};

document.addEventListener("DOMContentLoaded", function () {
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
            
            // If we're closing this menu, no need to close others
            if (isExpanded) return;
            
            // Close all other open submenus
            menuItems.forEach(otherItem => {
                if (otherItem !== this) {
                    const otherTargetId = otherItem.getAttribute('href');
                    const otherTarget = document.querySelector(otherTargetId);
                    if (otherTarget && otherTarget.classList.contains('show')) {
                        // Use Bootstrap's collapse method to close
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
            // Don't set active for items that toggle submenus
            if (this.hasAttribute('data-bs-toggle') && 
                this.getAttribute('data-bs-toggle') === 'collapse') {
                return;
            }
            
            // Remove active class from all items
            allMenuItems.forEach(i => i.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
        });
    });

    // Ensure modal only opens on button click
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
                    // Populate modal fields including email
                    document.getElementById('appointment_id').value = data.id;
                    document.getElementById('fullname').value = data.fullname;
                    document.getElementById('email').value = data.email;
                    document.getElementById('address').value = data.address;
                    document.getElementById('phone').value = data.phone;
                    document.getElementById('consulting').value = data.consulting;
                    document.getElementById('selected_date').value = data.selected_date;
                    document.getElementById('selected_time').value = data.selected_time;
                    document.getElementById('appointment_approval').value = data.appointment_approval;
                    
                    // Handle image display
                    const frontImg = document.getElementById('id_front_preview');
                    const backImg = document.getElementById('id_back_preview');
                    const frontPlaceholder = document.getElementById('front_placeholder');
                    const backPlaceholder = document.getElementById('back_placeholder');
                    const imageError = document.getElementById('imageError');

                    // Reset display
                    frontImg.style.display = 'none';
                    backImg.style.display = 'none';
                    frontPlaceholder.style.display = 'flex';
                    backPlaceholder.style.display = 'flex';
                    imageError.style.display = 'none';

                   // Set image sources with proper error handling
                    if (data.id_front) {
                        const frontFilename = data.id_front.split('/').pop();
                        frontImg.onload = function() {
                            frontPlaceholder.style.display = 'none';
                            this.style.display = 'block';
                        };
                        frontImg.onerror = function() {
                            console.error('Failed to load front image:', this.src);
                            frontPlaceholder.style.display = 'flex';
                            this.style.display = 'none';
                            imageError.style.display = 'block';
                        };
                        // Use the custom image route
                        frontImg.src = `/storage/images/${frontFilename}`;
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
                            console.error('Failed to load back image:', this.src);
                            backPlaceholder.style.display = 'flex';
                            this.style.display = 'none';
                            imageError.style.display = 'block';
                        };
                        // Use the custom image route
                        backImg.src = `/storage/images/${backFilename}`;
                    } else {
                        backPlaceholder.textContent = 'No back ID image available';
                    }
                    // Only show modal after data loaded
                    document.getElementById('infoModal').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error fetching appointment data:', error);
                    alert('Failed to load appointment details.');
                });
        });
    });

    // Modal close on outside click
    window.onclick = function(event) {
        const modal = document.getElementById('infoModal');
        if (event.target === modal) {
            modal.style.display = "none";
        }
    }

    // Flash message auto-close
    const flash = document.getElementById('flashModal');
    if (flash) {
        setTimeout(() => {
            flash.style.display = 'none';
        }, 4000);
    }
});


// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearSearch = document.getElementById('clearSearch');
    const tableRows = document.querySelectorAll('tbody tr');

    // Show/hide clear button based on input
    searchInput.addEventListener('input', function() {
        if (this.value.trim() !== '') {
            clearSearch.style.display = 'flex';
            filterTable(this.value.toLowerCase());
        } else {
            clearSearch.style.display = 'none';
            showAllRows();
        }
    });

    // Clear search functionality
    clearSearch.addEventListener('click', function() {
        searchInput.value = '';
        searchInput.focus();
        clearSearch.style.display = 'none';
        showAllRows();
    });

    // Filter table rows
    function filterTable(searchTerm) {
        tableRows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            if (rowText.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Show all rows
    function showAllRows() {
        tableRows.forEach(row => {
            row.style.display = '';
        });
    }

    // Optional: Add debounce for better performance on large tables
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Apply debounce to search input (300ms delay)
    const debouncedFilter = debounce(filterTable, 300);
    searchInput.addEventListener('input', function() {
        if (this.value.trim() !== '') {
            debouncedFilter(this.value.toLowerCase());
        }
    });
});