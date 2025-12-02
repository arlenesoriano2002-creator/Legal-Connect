<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <link rel="stylesheet" href="<?php echo e(asset('css/getsched.blade.css')); ?>">
    <title>Schedule Appointment - Legal Connect</title>
</head>
<body>
    <div class="container">
        <!-- Calendar Section -->
        <div class="calendar-section">
            <div class="calendar-header">
                <button class="back-btn" type="button" onclick="window.location.href='<?php echo e(route('appointment1')); ?>'">
                    ← Back to Previous
                </button>
                <div class="month-navigation">
                    <button class="nav-btn" id="prevMonth" aria-label="Previous month">‹</button>
                    <div class="month-year" id="monthYear" aria-live="polite"></div>
                    <button class="nav-btn" id="nextMonth" aria-label="Next month">›</button>
                </div>
            </div>

            <div class="calendar">
                <div class="weekdays" aria-hidden="true">
                    <div>Sun</div>
                    <div>Mon</div>
                    <div>Tue</div>
                    <div>Wed</div>
                    <div>Thu</div>
                    <div>Fri</div>
                    <div>Sat</div>
                </div>
                <div class="days-grid" id="daysGrid" role="grid" aria-label="Calendar days"></div>
            </div>
        </div>

        <!-- Time Selection Section -->
        <div class="time-section">
            <h2>Available Time Slots</h2>
            <div class="selected-date" id="selectedDateDisplay">
                Select a date to see available times
            </div>
            
            <div class="time-slots-container" id="timeSlots" role="list">
                <div class="empty-state">
                    Please select a date from the calendar
                </div>
            </div>

            <div class="selection-info" id="selectionInfo" style="display: none;">
                <h3>Selected Appointment</h3>
                <p id="appointmentDetails"></p>
            </div>

            <button class="next-btn" id="nextBtn" type="button" disabled>
                Confirm Appointment
            </button>
        </div>
    </div>

<script>
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Store selected day and time
    let selectedDate = null;
    let selectedTime = null;
    let selectedTimeSlot = null;
    let currentDate = new Date();
    currentDate.setDate(1);

    // DOM elements
    const daysGrid = document.getElementById('daysGrid');
    const monthYear = document.getElementById('monthYear');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const timeSlotsContainer = document.getElementById('timeSlots');
    const selectionInfo = document.getElementById('selectionInfo');
    const appointmentDetails = document.getElementById('appointmentDetails');
    const nextBtn = document.getElementById('nextBtn');
    const selectedDateDisplay = document.getElementById('selectedDateDisplay');

    // Store month colors data
   let monthColorsData = {};

// Debug function - call this in browser console to check data
window.debugCalendar = function() {
    console.log('Month Colors Data:', monthColorsData);
    console.log('Selected Date:', selectedDate);
    console.log('Current Month:', currentDate.getFullYear() + '-' + (currentDate.getMonth() + 1));
    
    // Test API calls
    const monthStr = currentDate.getFullYear() + '-' + String(currentDate.getMonth() + 1).padStart(2, '0');
    fetch(`/debug-month-colors/${monthStr}`)
        .then(r => r.json())
        .then(console.log)
        .catch(console.error);
};
    function renderCalendar() {
        daysGrid.innerHTML = '';

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const today = new Date();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        // Display month and year text
        const monthNames = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        monthYear.textContent = `${monthNames[month]} ${year}`;

        // Get first day of the month (0=Sun, 6=Sat)
        const firstDayIndex = new Date(year, month, 1).getDay();

        // Get number of days in previous month for leading blanks
        const prevMonthDays = new Date(year, month, 0).getDate();

        // Fill in days from previous month as disabled
        for (let i = firstDayIndex - 1; i >= 0; i--) {
            const dayDiv = createDayElement(prevMonthDays - i, true, false);
            daysGrid.appendChild(dayDiv);
        }

        // Fill in current month days
        for (let day = 1; day <= daysInMonth; day++) {
            const isToday = today.getDate() === day && 
                           today.getMonth() === month && 
                           today.getFullYear() === year;
            const isSelected = selectedDate && 
                              selectedDate.getFullYear() === year && 
                              selectedDate.getMonth() === month && 
                              selectedDate.getDate() === day;
            
            const dayDiv = createDayElement(day, false, isSelected, isToday);
            daysGrid.appendChild(dayDiv);
        }

        // Fill in trailing blanks for next month to complete grid
        const totalCells = daysGrid.children.length;
        const trailingBlanks = 42 - totalCells;
        for (let i = 1; i <= trailingBlanks; i++) {
            const dayDiv = createDayElement(i, true, false);
            daysGrid.appendChild(dayDiv);
        }
        
        // Fetch month colors for the current month
        const monthStr = `${year}-${String(month + 1).padStart(2, '0')}`;
        fetchMonthColors(monthStr);
        
        updateSelectionDisplay();
    }

    function createDayElement(day, isDisabled, isSelected, isToday = false) {
    const dayDiv = document.createElement('div');
    dayDiv.className = 'day';
    
    if (isDisabled) dayDiv.classList.add('disabled');
    if (isSelected) dayDiv.classList.add('selected');
    if (isToday) dayDiv.classList.add('today');
    
    dayDiv.textContent = day;
    dayDiv.setAttribute('role', 'gridcell');
    dayDiv.setAttribute('tabindex', isDisabled ? '-1' : '0');
    dayDiv.setAttribute('aria-selected', isSelected ? 'true' : 'false');

    // Add data attributes for date tracking
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    dayDiv.setAttribute('data-date', dateStr);

    // Store the date info for later use
    dayDiv._dateInfo = { year, month, day, dateStr };

    if (!isDisabled) {
        dayDiv.addEventListener('click', () => selectDay(year, month, day));
        dayDiv.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                selectDay(year, month, day);
            }
        });
    }

    return dayDiv;
}

    // Fetch month colors from database
// Fetch month colors from database
function fetchMonthColors(monthStr) {
    console.log('Fetching month colors for:', monthStr);
    
    fetch(`/calendar/month/colors?month=${monthStr}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response failed');
            return response.json();
        })
        .then(response => {
            console.log('Month colors API response:', response);
            
            // Handle different response structures
            if (response.status === 'success' && response.data) {
                monthColorsData = response.data;
                console.log('Using structured response data:', monthColorsData);
            } else if (response.data) {
                monthColorsData = response.data;
                console.log('Using data property:', monthColorsData);
            } else {
                monthColorsData = response;
                console.log('Using direct response:', monthColorsData);
            }
            
            applyMonthColors();
        })
        .catch(err => {
            console.error('Error fetching month colors:', err);
            monthColorsData = {};
            applyMonthColors();
        });
}

  // Apply month colors to calendar days
function applyMonthColors() {
    const days = document.querySelectorAll('.day:not(.disabled)');
    
    console.log('Applying month colors to days:', days.length);
    console.log('Month colors data:', monthColorsData);
    
    days.forEach(day => {
        const dateStr = day.getAttribute('data-date');
        
        // Remove existing color classes and reset styles
        day.classList.remove('color-green', 'color-red', 'color-orange', 'not-clickable');
        day.style.cursor = 'pointer'; // Default to clickable
        day.style.opacity = '1';
        day.style.backgroundColor = ''; // Reset to default
        day.style.color = ''; // Reset to default
        day.removeAttribute('title');
        
        let color = null;
        let description = 'Not set yet';
        let isClickable = true;
        
        if (dateStr && monthColorsData[dateStr]) {
            const colorData = monthColorsData[dateStr];
            color = colorData.color;
            description = colorData.description || '';
            
            console.log(`Applying color to ${dateStr}:`, { color, description });
            
            // Apply color class only if we have a valid color
            if (color && ['green', 'red', 'orange'].includes(color)) {
                day.classList.add(`color-${color}`);
                
                // Handle description for different colors
                if (color === 'green') {
                    // For green days, show "No description yet" if empty
                    if (!description || description.trim() === '') {
                        description = 'No description yet';
                    }
                    isClickable = true;
                    day.style.cursor = 'pointer';
                    day.style.opacity = '1';
                } else {
                    // For red and orange, use the description or default message
                    if (!description || description.trim() === '') {
                        description = color === 'red' ? 'Not Available' : 'Holiday';
                    }
                    isClickable = false;
                    day.classList.add('not-clickable');
                    day.style.cursor = 'not-allowed';
                    day.style.opacity = '0.6';
                }
                
                // Add description as title/tooltip
                day.setAttribute('title', description);
                
            } else {
                // Invalid color - treat as uncolored (unclickable)
                isClickable = false;
                day.classList.add('not-clickable');
                day.style.cursor = 'not-allowed';
                day.style.opacity = '0.6';
                day.setAttribute('title', 'Not set yet');
            }
        } else {
            // No color data found - treat as uncolored (unclickable)
            isClickable = false;
            day.classList.add('not-clickable');
            day.style.cursor = 'not-allowed';
            day.style.opacity = '0.6';
            day.setAttribute('title', 'Not set yet');
        }
        
        // Update event handlers based on clickability
        updateDayClickHandlers(day, isClickable);
    });
}

function updateDayClickHandlers(day, isClickable) {
    // Remove existing event listeners by cloning the element
    const newDay = day.cloneNode(true);
    day.parentNode.replaceChild(newDay, day);
    
    if (isClickable && !newDay.classList.contains('disabled')) {
        const dateInfo = newDay._dateInfo || {
            year: currentDate.getFullYear(),
            month: currentDate.getMonth(),
            day: parseInt(newDay.textContent),
            dateStr: newDay.getAttribute('data-date')
        };
        
        newDay.addEventListener('click', () => selectDay(dateInfo.year, dateInfo.month, dateInfo.day));
        newDay.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                selectDay(dateInfo.year, dateInfo.month, dateInfo.day);
            }
        });
        newDay.style.cursor = 'pointer';
        newDay.style.opacity = '1';
    } else {
        newDay.style.cursor = 'not-allowed';
        newDay.style.opacity = '0.6';
    }
}

// Attach click events to clickable days
function attachDayClickEvents() {
    const clickableDays = document.querySelectorAll('.day:not(.disabled):not(.not-clickable)');
    
    clickableDays.forEach(day => {
        // Remove existing event listeners
        day.replaceWith(day.cloneNode(true));
    });
    
    // Re-attach events to the new elements
    const newClickableDays = document.querySelectorAll('.day:not(.disabled):not(.not-clickable)');
    
    newClickableDays.forEach(day => {
        day.addEventListener('click', function() {
            const dateStr = this.getAttribute('data-date');
            const [year, month, dayNum] = dateStr.split('-').map(Number);
            selectDay(year, month - 1, dayNum);
        });
        
        day.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const dateStr = this.getAttribute('data-date');
                const [year, month, dayNum] = dateStr.split('-').map(Number);
                selectDay(year, month - 1, dayNum);
            }
        });
    });
}


    // Fetch week colors for selected date
    function fetchWeekColors(dateStr) {
        return fetch(`/calendar/week/colors?date=${dateStr}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response failed');
                return response.json();
            })
            .catch(err => {
                console.error('Error fetching week colors:', err);
                return [];
            });
    }
// In the renderTimeSlots function, replace the time slot creation logic:

// In the renderTimeSlots function, update the timeSlots array:
function renderTimeSlots() {
    if (!selectedDate) {
        timeSlotsContainer.innerHTML = '<div class="empty-state">Please select a date from the calendar</div>';
        return;
    }

    const dateStr = selectedDate.getFullYear() + '-' +
        String(selectedDate.getMonth() + 1).padStart(2, '0') + '-' +
        String(selectedDate.getDate()).padStart(2, '0');

    // Show loading state
    timeSlotsContainer.innerHTML = '<div class="empty-state">Loading available times...</div>';

    // Fetch week colors for the selected date
    fetch(`/calendar/week/load-data?date=${dateStr}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response failed');
            return response.json();
        })
        .then(response => {
            console.log('Week data response:', response);
            
            if (response.status !== 'success') {
                throw new Error(response.message || 'Failed to load time slots');
            }

            timeSlotsContainer.innerHTML = '';
            
            // Define all possible time slots in 12-hour format with variations
            const timeSlots = [
                { display: '8:00 AM - 9:00 AM', value: '8:00 AM - 9:00 AM', variations: ['8:00 AM - 9:00AM', '8:00 AM - 9:00 AM'] },
                { display: '9:00 AM - 10:00 AM', value: '9:00 AM - 10:00 AM', variations: ['9:00 AM - 10:00AM', '9:00 AM - 10:00 AM'] },
                { display: '10:00 AM - 11:00 AM', value: '10:00 AM - 11:00 AM', variations: ['10:00 AM - 11:00AM', '10:00 AM - 11:00 AM'] },
                { display: '11:00 AM - 12:00 PM', value: '11:00 AM - 12:00 PM', variations: ['11:00 AM - 12:00PM', '11:00 AM - 12:00 PM'] },
                { display: '12:00 PM - 1:00 PM', value: '12:00 PM - 1:00 PM', variations: ['12:00 PM - 1:00PM', '12:00 PM - 1:00 PM'], isLunch: true },
                { display: '1:00 PM - 2:00 PM', value: '1:00 PM - 2:00 PM', variations: ['1:00 PM - 2:00PM', '1:00 PM - 2:00 PM'] },
                { display: '2:00 PM - 3:00 PM', value: '2:00 PM - 3:00 PM', variations: ['2:00 PM - 3:00PM', '2:00 PM - 3:00 PM'] },
                { display: '3:00 PM - 4:00 PM', value: '3:00 PM - 4:00 PM', variations: ['3:00 PM - 4:00PM', '3:00 PM - 4:00 PM'] },
                { display: '4:00 PM - 5:00 PM', value: '4:00 PM - 5:00 PM', variations: ['4:00 PM - 5:00PM', '4:00 PM - 5:00 PM'] }
            ];

            // Get week colors for this specific date
            const weekColorsForDate = response.week_colors && response.week_colors[dateStr] 
                ? response.week_colors[dateStr] 
                : {};

            console.log('Week colors for date:', dateStr, weekColorsForDate);

            let availableSlotsCount = 0;

            timeSlots.forEach(slotInfo => {
                // Try to find matching time data using variations
                let timeData = null;
                for (const variation of slotInfo.variations) {
                    if (weekColorsForDate[variation]) {
                        timeData = weekColorsForDate[variation];
                        break;
                    }
                }

                console.log(`Time slot ${slotInfo.display}:`, timeData);

                let color = 'gray';
                let description = 'Not set yet';
                let isClickable = false;
                let isBooked = false;
                let slotCount = 0;
                
                // Check if this is lunch time
                if (slotInfo.isLunch) {
                    color = 'red';
                    description = 'Lunch time';
                    isClickable = false;
                } 
                // Check if we have data from database
                else if (timeData) {
                    color = timeData.color;
                    isBooked = timeData.booked === 1 || timeData.booked === true;
                    slotCount = timeData.time_slot || 0;
                    
                    console.log(`Processing ${slotInfo.display}: color=${color}, booked=${isBooked}, slotCount=${slotCount}`);
                    
                    // Set description based on color and booked status
                    if (color === 'green' && !isBooked && slotCount > 0) {
                        description = `Available slots: ${slotCount}`;
                        isClickable = true;
                        availableSlotsCount++;
                        console.log(`Slot ${slotInfo.display} is available and clickable`);
                    } else if (color === 'green' && (isBooked || slotCount <= 0)) {
                        description = 'No available slots';
                        color = 'red'; // Override to red if no slots
                        isClickable = false;
                        console.log(`Slot ${slotInfo.display} is green but no slots available`);
                    } else if (isBooked) {
                        description = 'Already Booked';
                        isClickable = false;
                    } else if (color === 'red' || color === 'orange') {
                        isClickable = false;
                        if (timeData.description && timeData.description !== 'Not set yet') {
                            description = timeData.description;
                        } else {
                            description = color === 'red' ? 'Not Available' : 'Holiday';
                        }
                    } else if (color === 'gray' || !color) {
                        color = 'gray';
                        description = 'Not set yet';
                        isClickable = false;
                    } else {
                        description = timeData.description || 'Not set yet';
                        isClickable = false;
                    }
                } else {
                    // No data found for this time slot - not set
                    color = 'gray';
                    description = 'Not set yet';
                    isClickable = false;
                    console.log(`No data found for ${slotInfo.display}`);
                }

                const timeSlot = document.createElement('button');
                timeSlot.type = 'button';
                
                // Only add color class if it's not gray (no color)
                if (color !== 'gray') {
                    timeSlot.className = `time-slot color-${color}`;
                } else {
                    timeSlot.className = 'time-slot';
                }
                
                // Create HTML content with slot details
                timeSlot.innerHTML = `
                    <div class="time-display">${slotInfo.display}</div>
                    <div class="slot-details">${description}</div>
                `;
                
                timeSlot.setAttribute('title', description);
                timeSlot.setAttribute('data-slot', slotInfo.slot);
                timeSlot.setAttribute('data-time-range', slotInfo.value);
                timeSlot.setAttribute('data-slot-count', slotCount);
                
                if (!isClickable) {
                    timeSlot.classList.add('disabled');
                    timeSlot.disabled = true;
                } else {
                    timeSlot.addEventListener('click', () => selectTime(slotInfo.slot, slotInfo.value, timeSlot, slotCount));
                    
                    if (selectedTimeSlot === slotInfo.slot) {
                        timeSlot.classList.add('selected');
                    }
                }

                timeSlotsContainer.appendChild(timeSlot);
            });

            // If no slots available, show message
            if (availableSlotsCount === 0) {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'empty-state';
                messageDiv.textContent = 'No available time slots for this date';
                timeSlotsContainer.appendChild(messageDiv);
            }

        })
        .catch(err => {
            console.error('Error loading time slots:', err);
            timeSlotsContainer.innerHTML = '<div class="empty-state" style="color: #ef4444;">Failed to load time slots. Please try again.</div>';
        });
}

function selectDay(year, month, day) {
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    
    console.log('Selecting day:', dateStr);
    console.log('Month colors data for this date:', monthColorsData[dateStr]);
    
    // Check if date is clickable (only green color is clickable)
    const colorData = monthColorsData[dateStr];
    const dayElement = document.querySelector(`.day[data-date="${dateStr}"]`);
    
    // Block selection if:
    // 1. Color is red or orange
    // 2. No color data exists (uncolored)
    // 3. Element has not-clickable class
    if ((colorData && (colorData.color === 'red' || colorData.color === 'orange')) || 
        !colorData || 
        (dayElement && dayElement.classList.contains('not-clickable'))) {
        
        let message = 'This date is not available for appointments.';
        if (colorData) {
            const colorMessages = {
                'red': 'Not Available',
                'orange': 'Holiday'
            };
            message = `This date is marked as ${colorMessages[colorData.color] || colorData.color}.`;
            if (colorData.description && colorData.description.trim() !== '' && colorData.description !== 'Not set yet') {
                message += ` ${colorData.description}`;
            }
        } else {
            message = 'This date has not been configured for appointments.';
        }
        
        // Show user-friendly message
        const selectedDateDisplay = document.getElementById('selectedDateDisplay');
        selectedDateDisplay.innerHTML = `<span style="color: #ef4444;">${message}</span>`;
        selectedDateDisplay.style.display = 'block';
        
        // Clear time slots
        const timeSlotsContainer = document.getElementById('timeSlots');
        timeSlotsContainer.innerHTML = '<div class="empty-state">This date is not available</div>';
        
        // Reset selection info
        const selectionInfo = document.getElementById('selectionInfo');
        selectionInfo.style.display = 'none';
        const nextBtn = document.getElementById('nextBtn');
        nextBtn.disabled = true;
        
        return;
    }
    
    // Date is available (green color) - proceed with selection
    selectedDate = new Date(year, month, day);
    selectedTime = null;
    selectedTimeSlot = null;
    
    // Show success message for green days
    const selectedDateDisplay = document.getElementById('selectedDateDisplay');
    let successMessage = `Selected: ${selectedDate.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}`;
    
    // Add description if available for green days
    if (colorData && colorData.description && colorData.description.trim() !== '' && colorData.description !== 'No description yet') {
        successMessage += ` - ${colorData.description}`;
    } else if (colorData && colorData.color === 'green') {
        successMessage += ' - No description yet';
    }
    
    selectedDateDisplay.innerHTML = `<span style="color: #16a34a;">${successMessage}</span>`;
    selectedDateDisplay.style.display = 'block';
    
    // Update calendar display
    renderCalendar();
    
    // Load time slots for this date
    renderTimeSlots();
    updateSelectionDisplay();
}
function selectTime(slot, timeRange, element, slotCount) {
    // Only allow selection if the time slot is clickable (not disabled)
    if (element.disabled) {
        return;
    }

    // Deselect any previously selected slot
    document.querySelectorAll('.time-slot').forEach(btn => {
        btn.classList.remove('selected');
    });

    // Select the new one
    selectedTimeSlot = slot; // Make sure this is set
    selectedTime = timeRange;
    selectedSlotCount = slotCount; // Store the slot count
    element.classList.add('selected');
    updateSelectionDisplay();
}
    function updateSelectionDisplay() {
        if (selectedDate && selectedTime) {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateStr = selectedDate.toLocaleDateString(undefined, options);
            appointmentDetails.textContent = `${dateStr} at ${selectedTime}`;
            selectionInfo.style.display = 'block';
            nextBtn.disabled = false;
            selectedDateDisplay.textContent = `Selected: ${dateStr}`;
        } else if (selectedDate) {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateStr = selectedDate.toLocaleDateString(undefined, options);
            selectionInfo.style.display = 'none';
            nextBtn.disabled = true;
            selectedDateDisplay.textContent = `Selected: ${dateStr}`;
        } else {
            selectionInfo.style.display = 'none';
            nextBtn.disabled = true;
            selectedDateDisplay.textContent = 'Select a date to see available times';
        }
    }

    // Event listeners
    prevMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        selectedDate = null;
        selectedTime = null;
        selectedTimeSlot = null;
        renderCalendar();
    });

    nextMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        selectedDate = null;
        selectedTime = null;
        selectedTimeSlot = null;
        renderCalendar();
    });

    // Update the nextBtn event listener to handle 12-hour format
// Update the nextBtn event listener to handle the new description
nextBtn.addEventListener('click', () => {
    if (!selectedDate || !selectedTime) return;
     console.log('Booking with:', {
        date: selectedDate,
        time: selectedTime,
        timeSlot: selectedTimeSlot
    });
   const dateStr = selectedDate.getFullYear() + '-' +
        String(selectedDate.getMonth() + 1).padStart(2, '0') + '-' +
        String(selectedDate.getDate()).padStart(2, '0');

    // Show loading state
    nextBtn.textContent = 'Booking...';
    nextBtn.disabled = true;

    console.log('Sending booking request:', {
        date: dateStr,
        time_slot: selectedTimeSlot,
        time_range: selectedTime
    });

fetch('<?php echo e(route("appointment.book.week.slot")); ?>', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({ 
        date: dateStr, 
        time_range: selectedTime
        // Remove time_slot parameter
    })
})
    .then(response => {
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse JSON response:', text);
                throw new Error('Server returned an invalid response. Please try again.');
            }
        });
    })
    .then(data => {
        console.log('Booking response:', data);
        
        if (data.status !== 'success') {
            alert(data.message || 'Slot could not be booked.');
            nextBtn.textContent = 'Confirm Appointment';
            nextBtn.disabled = false;
        } else {
            // Pass the 12-hour format time to FinalizeAppointment
            window.location.href = `/FinalizeAppointment?date=${dateStr}&time=${encodeURIComponent(selectedTime)}`;
        }
    })
    .catch(err => {
        console.error('Booking failed:', err);
        alert(err.message || 'Something went wrong while booking. Please try again.');
        nextBtn.textContent = 'Confirm Appointment';
        nextBtn.disabled = false;
    });
});
    // Initialize calendar on load
    renderCalendar();
</script>
</body>
</html><?php /**PATH D:\xampp\htdocs\LEGAL CONNECT\resources\views/getsched.blade.php ENDPATH**/ ?>