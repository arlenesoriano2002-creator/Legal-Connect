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
  // Ensure modal only opens on button click
  document.querySelectorAll('.view-btn').forEach(button => {
    button.addEventListener('click', function () {
      const id = this.getAttribute('data-id');
      fetch(`/appointments/${id}`)
        .then(res => res.json())
        .then(data => {
          // Populate modal fields
          document.getElementById('appointment_id').value = data.id;
          document.getElementById('fullname').value = data.fullname;
          document.getElementById('address').value = data.address;
          document.getElementById('phone').value = data.phone;
          document.getElementById('consulting').value = data.consulting;
          document.getElementById('selected_date').value = data.selected_date;
          document.getElementById('selected_time').value = data.selected_time;
          document.getElementById('term_status').value = data.term_status;
          document.getElementById('id_front_preview').src = `/storage/${data.id_front}`;
          document.getElementById('id_back_preview').src = `/storage/${data.id_back}`;
          
          // Only show modal after data loaded
          document.getElementById('infoModal').style.display = 'flex';
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

  window.onclick = function(event) {
    const modal = document.getElementById('infoModal');
    if (event.target === modal) {
      modal.style.display = "none";
    }
  }


  window.addEventListener('click', function (event) {
    const modal = document.getElementById('addClientModal');
    if (event.target === modal) {
      modal.style.display = 'none';
    }
  });

  // Burger menu
  const burgerBtn = document.getElementById('burgerBtn');
  const sidebar = document.querySelector('aside.sidebar');
  burgerBtn.addEventListener('click', () => {
    sidebar.classList.add('active');
    burgerBtn.style.display = 'none';
  });
  sidebar.addEventListener('mouseleave', () => {
    sidebar.classList.remove('active');
    burgerBtn.style.display = 'block';
  });


document.addEventListener('DOMContentLoaded', function () {

  const modal = document.getElementById('archiveConfirmModal');
  const itemNameEl = modal ? modal.querySelector('.archive-item-name') : null;
  const btnCancel = modal ? modal.querySelector('.cancel-archive-btn') : null;
  const btnConfirm = modal ? modal.querySelector('.confirm-archive-btn') : null;
  const backdrop = modal ? modal.querySelector('[data-close]') : null;

  let pendingForm = null;

  function openModal(name, form) {
    pendingForm = form;
    if (itemNameEl) itemNameEl.textContent = name ? name : '';
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    // focus confirm for accessibility
    btnConfirm && btnConfirm.focus();
  }

  function closeModal() {
    pendingForm = null;
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
  }

  // Attach to all archive forms
  document.querySelectorAll('.archive-form').forEach(form => {
    form.addEventListener('submit', function (e) {
      // intercept submit
      e.preventDefault();

      const fullname = form.getAttribute('data-fullname') || '';
      openModal(fullname, form);
    });
  });

  // Cancel button
  btnCancel && btnCancel.addEventListener('click', function () {
    closeModal();
  });

  // Backdrop click closes
  backdrop && backdrop.addEventListener('click', function () {
    closeModal();
  });

  // Confirm -> submit the form
  btnConfirm && btnConfirm.addEventListener('click', function () {
    if (!pendingForm) { closeModal(); return; }

    // submit programmatically
    // create a clone of form to avoid potential double-submits or location changes
    pendingForm.submit();
    // optional: show a tiny 'processing' state briefly (not required)
    closeModal();
  });

  // close modal on Escape
  document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape' && modal.style.display === 'flex') {
      closeModal();
    }
  });

});