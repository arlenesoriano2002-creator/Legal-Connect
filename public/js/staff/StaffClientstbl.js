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

            // Burger menu functionality
            const burgerBtn = document.getElementById('burgerBtn');
            const sidebar = document.querySelector('aside.sidebar');
            if (burgerBtn && sidebar) {
                burgerBtn.addEventListener('click', () => {
                    sidebar.classList.add('active');
                    burgerBtn.style.display = 'none';
                });
                
                sidebar.addEventListener('mouseleave', () => {
                    sidebar.classList.remove('active');
                    burgerBtn.style.display = 'block';
                });
            }
        });