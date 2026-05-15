document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('backupDeleteModal');
  const backdrop = modal.querySelector('.backup-delete-backdrop');
  const btnCancel = modal.querySelector('.backup-delete-cancel');
  const btnConfirm = modal.querySelector('.backup-delete-confirm');
  const fileText = document.getElementById('backupDeleteFileName');
  const backupFilter = document.getElementById('backupFilter');

  let targetForm = null;

  // PDF Viewer Elements
  const pdfViewerModal = document.getElementById('pdfViewerModal');
  const pdfViewerBackdrop = pdfViewerModal.querySelector('.pdf-viewer-backdrop');
  const pdfViewerClose = pdfViewerModal.querySelector('.pdf-viewer-close');
  const pdfViewerFrame = document.getElementById('pdfViewerFrame');
  const pdfViewerTitle = document.getElementById('pdfViewerTitle');
  const pdfDownloadBtn = document.getElementById('pdfDownloadBtn');
  const closePdfViewer = document.querySelector('.close-pdf-viewer');
  
// Replace the viewCsvInline function with:
function viewCsvInline(backupId, fileName) {
    const modal = document.getElementById('csvViewerModal');
    const title = document.getElementById('csvViewerTitle');
    const body = document.getElementById('csvViewerBody');
    const downloadBtn = document.getElementById('csvDownloadBtn');
    const csvBackdrop = modal.querySelector('.pdf-viewer-backdrop');
    const csvClose = modal.querySelector('.csv-viewer-close');
    const closeBtn = modal.querySelector('.close-csv-viewer');

    // Show loading state
    title.textContent = `Preview: ${fileName}`;
    body.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border spinner-border-sm"></div>
            Loading CSV preview...
        </div>
    `;
    
    // Show modal
    modal.classList.add('visible');

    // Set download button
    downloadBtn.onclick = () => {
        window.open(`/backup/download/${backupId}`, '_blank');
    };

    // Fetch CSV data
    fetch(`/backup/view/${backupId}?format=json`)
        .then(res => {
            if (!res.ok) throw new Error('Failed to load CSV');
            return res.json();
        })
        .then(data => {
            if (!data.headers || !data.rows) {
                throw new Error('Invalid CSV data');
            }

            let table = `<table class="table table-bordered table-sm"><thead><tr>`;
            data.headers.forEach(h => table += `<th>${h}</th>`);
            table += `</tr></thead><tbody>`;

            data.rows.forEach(row => {
                table += `<tr>`;
                data.headers.forEach(h => {
                    table += `<td>${row[h] ?? ''}</td>`;
                });
                table += `</tr>`;
            });

            table += `</tbody></table>`;
            body.innerHTML = table;
        })
        .catch(err => {
            console.error(err);
            body.innerHTML = `
                <div class="alert alert-danger">
                    Failed to load CSV preview.
                </div>
            `;
        });

    // Close modal functions
    function closeCsvModal() {
        modal.classList.remove('visible');
        body.innerHTML = '';
    }

    // Add event listeners for closing
    csvClose.addEventListener('click', closeCsvModal);
    csvBackdrop.addEventListener('click', closeCsvModal);
    closeBtn.addEventListener('click', closeCsvModal);
    
    // Close with Escape key
    document.addEventListener('keydown', function csvEscapeHandler(e) {
        if (e.key === 'Escape' && modal.classList.contains('visible')) {
            closeCsvModal();
            document.removeEventListener('keydown', csvEscapeHandler);
        }
    });
}

// Update the view button event listener:
document.body.addEventListener('click', (e) => {
    const btn = e.target.closest('.view-btn');
    if (!btn) return;

    const backupId = btn.getAttribute('data-backup-id');
    const fileName = btn.closest('.backup-card').querySelector('.backup-name').textContent;
    
    console.log('View button clicked:', { backupId, fileName });
    
    const extension = fileName.split('.').pop().toLowerCase();

    if (extension === 'pdf') {
        openPdfViewer(backupId, fileName);
    } else if (extension === 'csv') {
        viewCsvInline(backupId, fileName);
    } else {
        alert('This file type cannot be previewed. Please download it.');
    }
});

  // Filter backups when dropdown changes
  if (backupFilter) {
    backupFilter.addEventListener('change', function() {
      const filterValue = this.value;
      filterBackupCards(filterValue);
    });
  }

  // Function to filter backup cards
  function filterBackupCards(filterValue) {
    const backupCards = document.querySelectorAll('.backup-card');
    
    backupCards.forEach(card => {
      const fileName = card.querySelector('.backup-name').textContent.toLowerCase();
      
      if (filterValue === 'all') {
        card.style.display = 'flex';
      } else if (fileName.includes(filterValue)) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });

    // Show empty message if no cards are visible
    const visibleCards = document.querySelectorAll('.backup-card[style="display: flex"]');
    const emptyMessage = document.querySelector('.backup-empty-message');
    
    if (visibleCards.length === 0 && emptyMessage) {
      emptyMessage.style.display = 'block';
    } else if (emptyMessage) {
      emptyMessage.style.display = 'none';
    }
  }

  // Open delete confirm modal
  document.body.addEventListener('click', (e) => {
    const btn = e.target.closest('.backup-delete-btn');
    if (!btn) return;

    targetForm = btn.closest('form.delete-backup-form');
    const filename = targetForm.dataset.file;
    fileText.textContent = `Are you sure you want to delete: ${filename}?`;
    modal.classList.add('visible');
  });

  // Close modal function
  function closeModal() {
    modal.classList.remove('visible');
    targetForm = null;
  }

  btnCancel.addEventListener('click', closeModal);
  backdrop.addEventListener('click', closeModal);

  // ✅ Delete using AJAX (so main modal does NOT close)
  btnConfirm.addEventListener('click', () => {
    if (!targetForm) return;

    fetch(targetForm.action, {
      method: "POST",
      body: new FormData(targetForm)
    })
    .then(() => {
      closeModal();
      refreshBackupCards(); // ✅ Reload card list without page refresh
    })
    .catch(err => console.error(err));
  });

  // ✅ PDF Viewer Functions
  function openPdfViewer(backupId, fileName) {
    const extension = fileName.split('.').pop().toLowerCase();
    
    // Show loading state
    showPdfViewerLoading();
    
    // Use the view route for inline display WITH inline=true parameter
    const viewUrl = `/backup/view/${backupId}?inline=true`;
    
    // Add timestamp to prevent caching issues
    const timestamp = new Date().getTime();
    const finalUrl = `${viewUrl}&_=${timestamp}`;
    
    console.log('Opening PDF viewer with URL:', finalUrl);
    
    // Clear iframe first
    pdfViewerFrame.src = '';
    
    // Set iframe source and title after a small delay
    setTimeout(() => {
        pdfViewerFrame.src = finalUrl;
        pdfViewerTitle.textContent = `Preview: ${fileName}`;
        
        // Set download button to use the download route
        pdfDownloadBtn.onclick = () => {
            window.open(`/backup/download/${backupId}`, '_blank');
        };
        
        // Set appropriate iframe title based on file type
        if (extension === 'csv') {
            pdfViewerFrame.title = `CSV File: ${fileName}`;
        } else if (extension === 'pdf') {
            pdfViewerFrame.title = `PDF File: ${fileName}`;
        } else {
            pdfViewerFrame.title = `File: ${fileName}`;
        }
    }, 100);
    
    // Show modal
    pdfViewerModal.classList.add('visible');
}
function openCsvViewer(backupId, fileName) {
    const modal = document.getElementById('csvViewerModal');
    const title = document.getElementById('csvViewerTitle');
    const body = document.getElementById('csvViewerBody');

    title.textContent = `Preview: ${fileName}`;
    body.innerHTML = '<p>Loading CSV content...</p>';
    modal.classList.add('visible');

   fetch(`/backup/view/${backupId}?format=json`)

        .then(res => res.json())
        .then(data => {
            if (data.status !== 'success') {
                body.innerHTML = '<p class="text-danger">Failed to load CSV.</p>';
                return;
            }

            let table = `<table class="table table-bordered table-sm"><thead><tr>`;
            data.headers.forEach(h => table += `<th>${h}</th>`);
            table += `</tr></thead><tbody>`;

            data.rows.forEach(row => {
                table += `<tr>`;
                row.forEach(col => table += `<td>${col ?? ''}</td>`);
                table += `</tr>`;
            });

            table += `</tbody></table>`;
            body.innerHTML = table;
        })
        .catch(() => {
            body.innerHTML = '<p class="text-danger">Error loading CSV.</p>';
        });
}


// ✅ NEW: Function to show loading state
function showPdfViewerLoading() {
    // Create or show loading indicator
    let loadingIndicator = pdfViewerModal.querySelector('.pdf-loading-indicator');
    if (!loadingIndicator) {
        loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'pdf-loading-indicator';
        loadingIndicator.innerHTML = `
            <div style="text-align: center; padding: 20px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading preview...</p>
            </div>
        `;
        pdfViewerFrame.parentNode.insertBefore(loadingIndicator, pdfViewerFrame);
    }
    loadingIndicator.style.display = 'block';
    pdfViewerFrame.style.display = 'none';
}

// ✅ NEW: Function to hide loading and show iframe
function hidePdfViewerLoading() {
    const loadingIndicator = pdfViewerModal.querySelector('.pdf-loading-indicator');
    if (loadingIndicator) {
        loadingIndicator.style.display = 'none';
    }
    pdfViewerFrame.style.display = 'block';
}


  // ✅ Function to determine if file can be viewed inline
 function canViewInline(fileName) {
  const extension = fileName.split('.').pop().toLowerCase();
  return extension === 'pdf' || extension === 'csv';
}



// ✅ UPDATED: Function to close PDF viewer modal
function closePdfViewerModal() {
    pdfViewerModal.classList.remove('visible');
    // Clear iframe source when closing to stop PDF rendering
    setTimeout(() => {
        pdfViewerFrame.src = '';
        hidePdfViewerLoading();
    }, 300);
}
pdfViewerFrame.onload = function() {
    console.log('PDF iframe loaded successfully');
    hidePdfViewerLoading();
};

pdfViewerFrame.onerror = function() {
    console.error('Failed to load PDF in iframe');
    hidePdfViewerLoading();
    
    // Show error message
    const errorMsg = document.createElement('div');
    errorMsg.className = 'alert alert-danger mt-2';
    errorMsg.innerHTML = `
        <i class="fas fa-exclamation-triangle me-2"></i>
        Failed to load PDF preview. The file might be corrupted or unavailable.
    `;
    
    const loadingIndicator = pdfViewerModal.querySelector('.pdf-loading-indicator');
    if (loadingIndicator) {
        loadingIndicator.innerHTML = '';
        loadingIndicator.appendChild(errorMsg);
    }
};

  // Close PDF viewer events
  pdfViewerClose.addEventListener('click', closePdfViewerModal);
  pdfViewerBackdrop.addEventListener('click', closePdfViewerModal);
  closePdfViewer.addEventListener('click', closePdfViewerModal);

  // Close PDF viewer with Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && pdfViewerModal.classList.contains('visible')) {
      closePdfViewerModal();
    }
  });

  // ✅ Function to reload the updated backup cards
  window.refreshBackupCards = function () {
    fetch('/admin/backups/refresh')
      .then(res => res.json())
      .then(data => {
        document.getElementById('backupCardsContainer').outerHTML = data.html;
        // Re-attach filter event listener after refresh
        const newFilter = document.getElementById('backupFilter');
        if (newFilter) {
          newFilter.addEventListener('change', function() {
            const filterValue = this.value;
            filterBackupCards(filterValue);
          });
        }
      })
      .catch(err => console.log(err));
  };
});