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
  document.querySelectorAll('.btn-view').forEach(button => {
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

