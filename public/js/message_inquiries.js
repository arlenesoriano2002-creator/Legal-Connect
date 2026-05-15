/**
 * Message Inquiries Search Functionality
 * Handles real-time filtering of inquiry table data
 */

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('inquirySearch');
    const tableBody = document.getElementById('inquiriesTableBody');
    const inquiriesCount = document.getElementById('inquiriesCount');
    
    if (!searchInput) return;

    // Filter table rows based on search input
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const rows = tableBody.querySelectorAll('tr:not(.no-results)');
        let visibleCount = 0;
        let hasResults = false;

        rows.forEach(row => {
            // Skip the "no inquiries found" row
            const cells = row.querySelectorAll('td');
            if (cells.length === 0) return;

            // Check if this is the empty state row
            if (cells[0].getAttribute('colspan')) {
                row.style.display = 'none';
                return;
            }

            // Get text content from searchable columns (Name, Phone, Email, Subject, Message)
            const name = cells[0]?.textContent?.toLowerCase() || '';
            const phone = cells[1]?.textContent?.toLowerCase() || '';
            const email = cells[2]?.textContent?.toLowerCase() || '';
            const subject = cells[3]?.textContent?.toLowerCase() || '';
            const message = cells[4]?.textContent?.toLowerCase() || '';

            // Check if any column matches the search term
            const matches = !searchTerm || 
                name.includes(searchTerm) || 
                phone.includes(searchTerm) || 
                email.includes(searchTerm) || 
                subject.includes(searchTerm) || 
                message.includes(searchTerm);

            if (matches) {
                row.style.display = '';
                visibleCount++;
                hasResults = true;
            } else {
                row.style.display = 'none';
            }
        });

        // Show "no results" message if no matches found
        if (!hasResults && searchTerm) {
            let noResultsRow = tableBody.querySelector('.no-results');
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results';
                noResultsRow.innerHTML = `
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="fas fa-search" style="font-size: 24px; opacity: 0.5;"></i>
                        <p class="mt-2">No inquiries match your search</p>
                    </td>
                `;
                tableBody.appendChild(noResultsRow);
            }
            noResultsRow.style.display = '';
        } else {
            const noResultsRow = tableBody.querySelector('.no-results');
            if (noResultsRow) {
                noResultsRow.style.display = 'none';
            }
        }

        // Update count (showing filtered results)
        if (inquiriesCount) {
            inquiriesCount.textContent = visibleCount;
        }
    });

    // Clear search on Escape key
    searchInput.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            this.value = '';
            this.dispatchEvent(new Event('keyup'));
            this.blur();
        }
    });

    // Optional: Add focus/blur effects
    searchInput.addEventListener('focus', function() {
        this.parentElement.classList.add('search-focused');
    });

    searchInput.addEventListener('blur', function() {
        this.parentElement.classList.remove('search-focused');
    });
});
