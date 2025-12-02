document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.getElementById('slots-table-body');
  const calendarGrid = document.getElementById('calendarGrid');
  const monthYearLabel = document.getElementById('monthYearLabel');
  const monthYearInput = document.getElementById('monthYearInput');
  const goToMonthBtn = document.getElementById('goToMonthBtn');
  const todayBtn = document.getElementById('todayBtn');
  const prevMonthBtn = document.getElementById('prevMonthBtn');
  const nextMonthBtn = document.getElementById('nextMonthBtn');
  const timeSlotsContainer = document.getElementById('timeSlots');
  const submitAvailabilityBtn = document.getElementById('submitAvailabilityBtn');
  const messageContainer = document.getElementById('messageContainer');

  // Edit Modal elements
  const editModal = document.getElementById('editModal');
  const closeEditModal = document.getElementById('closeEditModal');
  const editSlotForm = document.getElementById('editSlotForm');
  const editSlotId = document.getElementById('editSlotId');
  const editSlotDate = document.getElementById('editSlotDate');
  const editTimeSlots = document.getElementById('editTimeSlots');
  const editMessageBox = document.getElementById('editMessageBox');

  let currentDate = new Date();
  let selectedDate = null;

  const allTimes = [
    "08:00 AM - 09:00 AM",
    "10:00 AM - 11:00 AM",
    "12:00 PM - 01:00 PM",
    "02:00 PM - 03:00 PM",
    "04:00 PM - 05:00 PM"
  ];

  function formatDate(date) {
    const y = date.getFullYear();
    const m = (date.getMonth() + 1).toString().padStart(2, '0');
    const d = date.getDate().toString().padStart(2, '0');
    return `${y}-${m}-${d}`;
  }

  // Show message function
  function showMessage(message, type) {
    messageContainer.innerHTML = `<div class="message ${type}">${message}</div>`;
    setTimeout(() => {
      messageContainer.innerHTML = '';
    }, 3000);
  }

  // Load slots table
  function loadSlots() {
    fetch('/appointment-slots')
      .then(res => res.json())
      .then(slots => {
        tbody.innerHTML = '';
        slots.forEach(slot => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td>${slot.date}</td>
            <td>${slot.time}</td>
            <td>
              <div class="action-buttons">
                <button class="edit-btn" data-id="${slot.id}">
                  <i class="fas fa-edit"></i> Edit
                </button>
                <button class="delete-btn" data-id="${slot.id}">
                  <i class="fas fa-trash"></i> Delete
                </button>
              </div>
            </td>
          `;
          tbody.appendChild(row);
        });
      })
      .catch(err => console.error("Error loading slots:", err));
  }

  // Calendar rendering
  function renderCalendar(date) {
    calendarGrid.innerHTML = '';
    const year = date.getFullYear();
    const month = date.getMonth();
    monthYearLabel.textContent = date.toLocaleString('default', { month: 'long', year: 'numeric' });
    monthYearInput.value = `${(month + 1).toString().padStart(2, '0')}/${year}`;

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDay = firstDay.getDay();
    const daysInMonth = lastDay.getDate();

    for (let i = 0; i < 42; i++) {
      const dayCell = document.createElement('div');
      dayCell.className = 'date-cell';
      let dayNum, cellDate, isCurrentMonth = true;

      if (i < startDay) {
        dayNum = new Date(year, month, 0).getDate() - (startDay - 1) + i;
        isCurrentMonth = false;
        cellDate = new Date(year, month - 1, dayNum);
        dayCell.classList.add('inactive');
      } else if (i >= startDay + daysInMonth) {
        dayNum = i - (startDay + daysInMonth) + 1;
        isCurrentMonth = false;
        cellDate = new Date(year, month + 1, dayNum);
        dayCell.classList.add('inactive');
      } else {
        dayNum = i - startDay + 1;
        cellDate = new Date(year, month, dayNum);
      }

      const cellDateStr = formatDate(cellDate);
      if (selectedDate && cellDateStr === selectedDate) {
        dayCell.classList.add('selected');
      }
      dayCell.textContent = dayNum;

      dayCell.addEventListener('click', () => {
        if (!isCurrentMonth) return;
        selectedDate = cellDateStr;
        renderCalendar(currentDate);
        renderTimeSlots();
      });

      calendarGrid.appendChild(dayCell);
    }
  }

  // Render time slots for new availability
  function renderTimeSlots() {
    timeSlotsContainer.innerHTML = '';
    if (!selectedDate) {
      timeSlotsContainer.innerHTML = '<p style="color:white;">Select a date</p>';
      return;
    }

    fetch(`/available-times/${selectedDate}`)
      .then(res => res.json())
      .then(bookedSlots => {
        // Extract just the time strings from the booked slots
        const bookedTimes = bookedSlots.map(s => s.time);
        
        allTimes.forEach(time => {
          if (!bookedTimes.includes(time)) {
            const label = document.createElement('label');
            label.style.display = 'block';
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.value = time;
            radio.name = 'selectedTime';
            label.appendChild(radio);
            label.appendChild(document.createTextNode(" " + time));
            timeSlotsContainer.appendChild(label);
          }
        });

        if (timeSlotsContainer.innerHTML === '') {
          timeSlotsContainer.innerHTML = '<p class="no-times-message">No available times</p>';
        }
      })
      .catch(() => {
        timeSlotsContainer.innerHTML = '<p class="no-times-message">Error loading times</p>';
      });
  }

  // ===================== EDIT MODAL =====================
  // ===================== EDIT MODAL =====================
  function openEditModal(slotId) {
    fetch(`/appointment-slots/${slotId}`)
      .then(res => {
        if (!res.ok) {
          throw new Error('Failed to fetch slot details');
        }
        return res.json();
      })
      .then(slot => {
        editSlotId.value = slot.id;
        editSlotDate.value = slot.date;
        editMessageBox.textContent = "";

        // Fetch available times for this date
        fetch(`/available-times/${slot.date}`)
          .then(res => {
            if (!res.ok) {
              throw new Error('Failed to fetch available times');
            }
            return res.json();
          })
          .then(bookedSlots => {
            console.log('Booked slots:', bookedSlots); // Debug log
            
            // Extract just the time strings from the booked slots
            const bookedTimes = bookedSlots.map(s => s.time);
            editTimeSlots.innerHTML = '';
            let hasAvailableSlots = false;

            // Create a container for the radio buttons
            const radioContainer = document.createElement('div');
            radioContainer.className = 'radio-container';
            
            allTimes.forEach(time => {
              // Show times that are either available or the current slot's time
              if (!bookedTimes.includes(time) || time === slot.time) {
                hasAvailableSlots = true;
                
                const label = document.createElement('label');
                label.style.display = 'flex';
                label.style.alignItems = 'center';
                label.style.marginBottom = '10px';
                label.style.color = '#333'; // Ensure text is visible
                
                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.value = time;
                radio.name = 'selectedTime';
                radio.style.marginRight = '10px';
                
                if (time === slot.time) {
                  radio.checked = true;
                }
                
                label.appendChild(radio);
                label.appendChild(document.createTextNode(time));
                radioContainer.appendChild(label);
              }
            });
            
            editTimeSlots.appendChild(radioContainer);
            
            if (!hasAvailableSlots) {
              editTimeSlots.innerHTML = '<p class="no-times-message">No available times</p>';
            }
          })
          .catch(error => {
            console.error('Error loading available times:', error);
            editTimeSlots.innerHTML = '<p class="error-message">Error loading available times</p>';
          });

        editModal.style.display = 'flex';
      })
      .catch(error => {
        console.error('Error loading slot details:', error);
        editMessageBox.textContent = "❌ Error loading slot details.";
        editMessageBox.style.color = "red";
      });
  }

  // Close modal
  closeEditModal.addEventListener('click', () => {
    editModal.style.display = 'none';
  });

  // Submit edit form
  editSlotForm.addEventListener('submit', e => {
    e.preventDefault();

    const id = editSlotId.value;
    const date = editSlotDate.value;
    const selectedTime = document.querySelector('#editTimeSlots input[name="selectedTime"]:checked');

    if (!selectedTime) {
      editMessageBox.textContent = "⚠️ Please select a time.";
      editMessageBox.style.color = "red";
      return;
    }

    const time = selectedTime.value;

    fetch(`/appointment-slots/${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ date: date, time: time })
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        editMessageBox.textContent = "✅ Slot updated successfully!";
        editMessageBox.style.color = "green";
        setTimeout(() => {
          editModal.style.display = 'none';
          loadSlots();
        }, 1000);
      } else {
        editMessageBox.textContent = "❌ Failed to update slot.";
        editMessageBox.style.color = "red";
      }
    })
    .catch(() => {
      editMessageBox.textContent = "❌ Error updating slot.";
      editMessageBox.style.color = "red";
    });
  });

  // Handle new time slot submission
  submitAvailabilityBtn.addEventListener('click', () => {
    if (!selectedDate) {
      showMessage('Please select a date first', 'error');
      return;
    }
    
    const selectedTime = document.querySelector('#timeSlots input[name="selectedTime"]:checked');
    
    if (!selectedTime) {
      showMessage('Please select a time slot', 'error');
      return;
    }
    
    const time = selectedTime.value;
    
    fetch('/store-availability', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ 
        availability: [
          { date: selectedDate, time: time }
        ]
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        showMessage('Time slot added successfully', 'success');
        loadSlots();
        renderTimeSlots(); // Refresh available times
      } else {
        showMessage('Failed to add time slot: ' + (data.message || 'Unknown error'), 'error');
      }
    })
    .catch(err => {
      console.error('Error adding time slot:', err);
      showMessage('Error adding time slot', 'error');
    });
  });

  // Handle table buttons
  document.addEventListener('click', function(e) {
    if (e.target.closest('.edit-btn')) {
      const btn = e.target.closest('.edit-btn');
      const slotId = btn.getAttribute('data-id');
      openEditModal(slotId);
    }

    if (e.target.closest('.delete-btn')) {
      const btn = e.target.closest('.delete-btn');
      const slotId = btn.getAttribute('data-id');
      if (confirm('Are you sure you want to delete this slot?')) {
        fetch(`/appointment-slots/${slotId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            loadSlots();
          }
        });
      }
    }
  });

  // Calendar navigation
  prevMonthBtn.addEventListener('click', () => {
    currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 1);
    selectedDate = null;
    renderCalendar(currentDate); 
    renderTimeSlots();
  });
  
  nextMonthBtn.addEventListener('click', () => {
    currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1);
    selectedDate = null;
    renderCalendar(currentDate); 
    renderTimeSlots();
  });
  
  goToMonthBtn.addEventListener('click', () => {
    const [m,y] = monthYearInput.value.split('/');
    if (m && y) {
      currentDate = new Date(parseInt(y), parseInt(m)-1, 1);
      selectedDate = null;
      renderCalendar(currentDate); 
      renderTimeSlots();
    }
  });
  
  todayBtn.addEventListener('click', () => {
    currentDate = new Date();
    selectedDate = null;
    renderCalendar(currentDate); 
    renderTimeSlots();
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

  // Init
  loadSlots();
  renderCalendar(currentDate);
  renderTimeSlots();
});

