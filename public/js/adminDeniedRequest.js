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

    window.addEventListener('click', function (event) {
        const modal = document.getElementById('addClientModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});