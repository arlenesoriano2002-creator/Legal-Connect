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
    // CHANGE: Use the view route instead of download route
    const pdfUrl = `/backup/view/${backupId}`;
    
    // Set iframe source and title
    pdfViewerFrame.src = pdfUrl;
    pdfViewerTitle.textContent = `Preview: ${fileName}`;
    
    // Set download button to use the download route
    pdfDownloadBtn.onclick = () => {
        window.open(`/backup/download/${backupId}`, '_blank');
    };
    
    // Show modal
    pdfViewerModal.classList.add('visible');
}

  function closePdfViewerModal() {
    pdfViewerModal.classList.remove('visible');
    // Clear iframe source when closing to stop PDF rendering
    setTimeout(() => {
      pdfViewerFrame.src = '';
    }, 300);
  }

  // View button event listener
  document.body.addEventListener('click', (e) => {
    const btn = e.target.closest('.view-btn');
    if (!btn) return;

    const backupId = btn.getAttribute('data-backup-id');
    const fileName = btn.closest('.backup-card').querySelector('.backup-name').textContent;
    
    openPdfViewer(backupId, fileName);
  });

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