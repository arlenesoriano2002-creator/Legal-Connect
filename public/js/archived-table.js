document.addEventListener('DOMContentLoaded', function () {
  // -----------------------------
  // View Image Modal
  // -----------------------------
  const viewModal = document.getElementById('archiveImageModal');
  if (viewModal) {
    const modalImg = document.getElementById('popupImage');
    const viewBackdrop = viewModal.querySelector('.image-modal-backdrop');
    const viewClose = viewModal.querySelector('.image-modal-close');

    function openImageModal(url) {
      modalImg.src = url;
      viewModal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    }
    function closeImageModal() {
      viewModal.style.display = 'none';
      modalImg.src = '';
      document.body.style.overflow = '';
    }

    document.body.addEventListener('click', (e) => {
      const btn = e.target.closest('.image-btn');
      if (btn) {
        e.preventDefault();
        openImageModal(btn.dataset.src);
      }
    });

    viewBackdrop.addEventListener('click', closeImageModal);
    viewClose.addEventListener('click', closeImageModal);

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && viewModal.style.display === 'flex') closeImageModal();
    });
  }

  // -----------------------------
  // Delete Confirmation Modal
  // -----------------------------
    // -----------------------------
  // Delete Confirmation Modal
  // -----------------------------
  const deleteModal = document.getElementById('deleteConfirmModal');
  if (deleteModal) {
    const backdrop   = deleteModal.querySelector('.delete-modal-backdrop');
    const btnCancel  = deleteModal.querySelector('.cancel-delete-btn');
    const btnConfirm = deleteModal.querySelector('.confirm-delete-btn');
    const nameDisplay = document.getElementById('deleteItemName');

    let targetForm = null;

    // Open (delegation)
    document.body.addEventListener('click', (e) => {
      const trigger = e.target.closest('.icon-delete-btn');
      if (!trigger) return;

      targetForm = trigger.closest('form.delete-archived-form');
      if (!targetForm) return;

      const n = targetForm.dataset.name || '';
      nameDisplay.textContent = n ? `Delete: ${n}?` : 'Delete this record?';

      // ✅ show modal using CSS class
      deleteModal.classList.add('show');
    });

    function closeDeleteModal() {
      deleteModal.classList.remove('show');
      targetForm = null;
    }

    btnCancel.addEventListener('click', closeDeleteModal);
    backdrop.addEventListener('click', closeDeleteModal);

    btnConfirm.addEventListener('click', () => {
      if (targetForm) targetForm.submit();
      closeDeleteModal();
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && deleteModal.classList.contains('show')) closeDeleteModal();
    });
  }

});
document.body.addEventListener("click", function(e){
    const btn = e.target.closest(".create-backup-btn");
    if (!btn) return;

    fetch('/admin/create-backup', {
        method: "POST",
        headers: { "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content }
    })
    .then(res => res.json())
    .then(() => {
        // ✅ Auto-update backup list displayed in the dashboard modal
        if (window.refreshBackupCards) {
            window.refreshBackupCards();
        }
    })
    .catch(err => console.log(err));
});

