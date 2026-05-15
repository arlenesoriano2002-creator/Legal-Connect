<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/getsched.blade.css') }}">
    <style>
        .time-slot.past-slot {
            opacity: 0.6;
            cursor: not-allowed;
            filter: grayscale(0.25);
        }

        .time-slot.full-slot {
            opacity: 0.65;
            cursor: not-allowed;
            filter: grayscale(0.4);
        }

        .time-slot.past-slot .slot-details {
            color: #6b7280;
        }

        .time-slot.full-slot .slot-details {
            color: #6b7280;
            font-weight: 600;
        }
    </style>
    <title>Schedule Appointment - Legal Connect</title>
</head>
<body>
    <div class="container">
        <!-- Calendar Section -->
        <div class="calendar-section">
            <div class="calendar-header">
                <button class="back-btn" type="button" onclick="window.location.href='{{ route('appointment1') }}'">
                    ← Back to Previous
                </button>
                <div class="month-navigation">
                    <button class="nav-btn" id="prevMonth" aria-label="Previous month">‹</button>
                    <div class="month-year" id="monthYear" aria-live="polite"></div>
                    <button class="nav-btn" id="nextMonth" aria-label="Next month">›</button>
                </div>
                <!-- Display selected branch -->
                <div class="branch-info">
                    <strong>Selected Office</strong> 
                    <span id="selectedBranch">{{ session('branch', 'Diffun Branch Office') }}</span>
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
    let selectedSlotCount = null;
    let currentDate = new Date();
    currentDate.setDate(1);
    
    // Get selected branch from session and office_id from PHP
    const selectedBranch = "{{ session('branch', 'Diffun Branch Office') }}";
    const lawOfficeId = {{ $lawOfficeId ?? 'null' }};
    console.log('Selected Branch:', selectedBranch);
    console.log('Law Office ID:', lawOfficeId);

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

    // Helper function to add office_id parameter to API calls
    function addOfficeParam(url) {
        if (lawOfficeId) {
            const separator = url.includes('?') ? '&' : '?';
            return url + separator + 'office_id=' + lawOfficeId;
        }
        return url;
    }

    // Determine API endpoints based on branch
    function getApiEndpoints() {
        if (selectedBranch === 'Cordon Branch Office') {
            return {
                monthColors: '/cordon/calendar/month/colors',
                weekData: '/cordon/calendar/week/load-data',
                bookSlot: '/cordon/book-slot'
            };
        } else {
            return {
                monthColors: '/calendar/month/colors',
                weekData: '/calendar/week/load-data',
                bookSlot: '{{ route("appointment.book.week.slot") }}'
            };
        }
    }

    const apiEndpoints = getApiEndpoints();
    console.log('Using API endpoints:', apiEndpoints);

    function parseTimeSlotStart(date, timeRange) {
        if (!date || !timeRange) {
            return null;
        }

        const startTime = (timeRange.split(' - ')[0] || '').trim();
        const match = startTime.match(/^(\d{1,2}):(\d{2})\s*([AP]M)$/i);

        if (!match) {
            return null;
        }

        let hours = parseInt(match[1], 10);
        const minutes = parseInt(match[2], 10);
        const meridiem = match[3].toUpperCase();

        if (meridiem === 'PM' && hours !== 12) {
            hours += 12;
        }

        if (meridiem === 'AM' && hours === 12) {
            hours = 0;
        }

        return new Date(
            date.getFullYear(),
            date.getMonth(),
            date.getDate(),
            hours,
            minutes,
            0,
            0
        );
    }

    function isPastTimeSlot(date, timeRange) {
        const slotStart = parseTimeSlotStart(date, timeRange);

        if (!slotStart) {
            return false;
        }

        return slotStart.getTime() < Date.now();
    }

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

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        // Past date logic
        const today = new Date();
        const thisDate = new Date(year, month, day);

        if (thisDate < new Date(today.getFullYear(), today.getMonth(), today.getDate())) {
            dayDiv.classList.add('disabled', 'not-clickable');
            dayDiv.style.cursor = 'not-allowed';
            dayDiv.style.opacity = '0.5';
            isDisabled = true;
        }

        if (isDisabled) dayDiv.classList.add('disabled');
        if (isSelected) dayDiv.classList.add('selected');
        if (isToday) dayDiv.classList.add('today');

        dayDiv.textContent = day;
        dayDiv.setAttribute('role', 'gridcell');
        dayDiv.setAttribute('aria-selected', isSelected ? 'true' : 'false');
        dayDiv.setAttribute('tabindex', isDisabled ? '-1' : '0');

        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        dayDiv.setAttribute('data-date', dateStr);

        dayDiv._dateInfo = { year, month, day, dateStr };

        if (!isDisabled) {
            dayDiv.addEventListener('click', () => selectDay(year, month, day));
            dayDiv.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectDay(year, month, day);
                }
            });
        }

        return dayDiv;
    }

    // Fetch month colors from database based on branch
    function fetchMonthColors(monthStr) {
        const url = addOfficeParam(`${apiEndpoints.monthColors}?month=${monthStr}`);
        console.log('Fetching month colors for:', monthStr, 'from:', url);
        
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Network response failed');
                return response.json();
            })
            .then(response => {
                console.log('Month colors API response:', response);
                
                // Handle different response structures
                if (response.status === 'success' && response.data) {
                    monthColorsData = response.data;
                } else if (response.data) {
                    monthColorsData = response.data;
                } else {
                    monthColorsData = response;
                }
                
                console.log('Month colors data loaded:', monthColorsData);
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
        
        days.forEach(day => {
            const dateStr = day.getAttribute('data-date');
            
            // Remove existing color classes and reset styles
            day.classList.remove('color-green', 'color-red', 'color-orange', 'not-clickable');
            day.style.cursor = 'pointer';
            day.style.opacity = '1';
            day.style.backgroundColor = '';
            day.style.color = '';
            day.removeAttribute('title');
            
            let color = null;
            let description = 'Not set yet';
            let isClickable = true;
            
            if (dateStr && monthColorsData[dateStr]) {
                const colorData = monthColorsData[dateStr];
                color = colorData.color;
                description = colorData.description || '';
                
                console.log(`Applying color to ${dateStr}:`, { color, description });
                
                if (color && ['green', 'red', 'orange'].includes(color)) {
                    day.classList.add(`color-${color}`);
                    
                    if (color === 'green') {
                        if (!description || description.trim() === '') {
                            description = 'No description yet';
                        }
                        isClickable = true;
                        day.style.cursor = 'pointer';
                        day.style.opacity = '1';
                    } else {
                        if (!description || description.trim() === '') {
                            description = color === 'red' ? 'Not Available' : 'Holiday';
                        }
                        isClickable = false;
                        day.classList.add('not-clickable');
                        day.style.cursor = 'not-allowed';
                        day.style.opacity = '0.6';
                    }
                    
                    day.setAttribute('title', description);
                } else {
                    isClickable = false;
                    day.classList.add('not-clickable');
                    day.style.cursor = 'not-allowed';
                    day.style.opacity = '0.6';
                    day.setAttribute('title', 'Not set yet');
                }
            } else {
                isClickable = false;
                day.classList.add('not-clickable');
                day.style.cursor = 'not-allowed';
                day.style.opacity = '0.6';
                day.setAttribute('title', 'Not set yet');
            }
            
            updateDayClickHandlers(day, isClickable);
        });
    }

    function updateDayClickHandlers(day, isClickable) {
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

        const weekDataUrl = addOfficeParam(`${apiEndpoints.weekData}?date=${dateStr}`);
        console.log('Fetching week data from:', weekDataUrl);

        // Fetch week data for the selected date
        fetch(weekDataUrl)
            .then(response => {
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    // Try to get error details
                    return response.text().then(text => {
                        console.error('Error response text:', text);
                        throw new Error(`Server responded with ${response.status}: ${text.substring(0, 100)}`);
                    });
                }
                return response.json();
            })
            .then(response => {
                console.log('Week data response:', response);
                
                // Check if response indicates success
                if (response.status && response.status !== 'success') {
                    throw new Error(response.message || 'Failed to load time slots');
                }

                timeSlotsContainer.innerHTML = '';
                
                // Define all possible time slots
                const timeSlots = [
                    { display: '8:00 AM - 9:00 AM', value: '8:00 AM - 9:00 AM', variations: ['8:00 AM - 9:00AM', '8:00 AM - 9:00 AM'] },
                    { display: '9:00 AM - 10:00 AM', value: '9:00 AM - 10:00 AM', variations: ['9:00 AM - 10:00AM', '9:00 AM - 10:00 AM'] },
                    { display: '10:00 AM - 11:00 AM', value: '10:00 AM - 11:00 AM', variations: ['10:00 AM - 11:00AM', '10:00 AM - 11:00 AM'] },
                    { display: '11:00 AM - 12:00 PM', value: '11:00 AM - 12:00 PM', variations: ['11:00 AM - 12:00PM', '11:00 AM - 12:00 PM'] },
                    //{ display: '12:00 PM - 1:00 PM', value: '12:00 PM - 1:00 PM', variations: ['12:00 PM - 1:00PM', '12:00 PM - 1:00 PM'], isLunch: true },
                    { display: '1:00 PM - 2:00 PM', value: '1:00 PM - 2:00 PM', variations: ['1:00 PM - 2:00PM', '1:00 PM - 2:00 PM'] },
                    { display: '2:00 PM - 3:00 PM', value: '2:00 PM - 3:00 PM', variations: ['2:00 PM - 3:00PM', '2:00 PM - 3:00 PM'] },
                    { display: '3:00 PM - 4:00 PM', value: '3:00 PM - 4:00 PM', variations: ['3:00 PM - 4:00PM', '3:00 PM - 4:00 PM'] },
                    { display: '4:00 PM - 5:00 PM', value: '4:00 PM - 5:00 PM', variations: ['4:00 PM - 5:00PM', '4:00 PM - 5:00 PM'] }
                ];

               // Get week colors for this specific date
                let weekColorsForDate = {};

                // Debug the response structure
                console.log('Week data API response structure:', {
                    hasWeekColors: !!response.week_colors,
                    hasTimeSlots: !!response.time_slots,
                    isArray: Array.isArray(response),
                    responseKeys: Object.keys(response),
                    responseType: typeof response
                });

                if (Array.isArray(response)) {
                    // Diffun branch: Direct array response from /calendar/week/load-data
                    console.log('Processing Diffun branch array response');
                    weekColorsForDate = {};
                    response.forEach(slot => {
                        const timeRange = slot.time;
                        weekColorsForDate[timeRange] = {
                            color: slot.color || 'gray',
                            description: slot.description || 'Not set yet',
                            booked: slot.booked || 0,
                            time_slot: slot.time_slot || 0, // This is just position (1-9)
                            slot_number: slot.slot_number || 0 // 🔥 This is the ACTUAL available slots count
                        };
                        console.log(`Slot ${timeRange}: slot_number=${slot.slot_number}, time_slot=${slot.time_slot}`);
                    });
                } else if (response.week_colors && response.week_colors[dateStr]) {
                // Diffun branch: Nested structure
                console.log('Processing Diffun branch nested structure');
                weekColorsForDate = response.week_colors[dateStr];
                
                // Ensure each time slot uses slot_number for available slots
                Object.keys(weekColorsForDate).forEach(timeRange => {
                    // Force using slot_number for slot count
                    const slotData = weekColorsForDate[timeRange];
                    if (slotData) {
                        // If slot_number is not set but time_slot is, copy time_slot to slot_number
                        if ((slotData.slot_number === undefined || slotData.slot_number === null) && slotData.time_slot) {
                            slotData.slot_number = slotData.time_slot;
                        }
                        // Remove any direct use of time_slot for slot count
                        delete slotData.time_slot_for_display;
                    }
                });
            } else if (response.time_slots) {
                    // Cordon branch structure - convert array to object
                    console.log('Processing Cordon branch response');
                    weekColorsForDate = {};
                    response.time_slots.forEach(slot => {
                        const timeRange = slot.time_range || slot.time || `${slot.start_time} - ${slot.end_time}`;
                        weekColorsForDate[timeRange] = {
                            color: slot.color || slot.slot_color,
                            description: slot.description || 'Available',
                            booked: slot.status === 'booked' || slot.booked === 1,
                            time_slot: slot.time_slot || 0,
                            slot_number: slot.slot_number || 0
                        };
                    });
                } else {
                    // No data found
                    console.log('No week colors data found');
                    weekColorsForDate = {};
                }

                console.log('Processed week colors:', weekColorsForDate);



                console.log('Week colors for date:', dateStr, weekColorsForDate);

                let availableSlotsCount = 0;

                timeSlots.forEach(slotInfo => {
                let timeData = null;
                
                // Try to find matching time data
                for (const variation of slotInfo.variations) {
                    if (weekColorsForDate[variation]) {
                        timeData = weekColorsForDate[variation];
                        break;
                    }
                }

                console.log(`Time data for ${slotInfo.display}:`, timeData);
                
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
                    color = timeData.color || 'gray';
                    
                    // 🔥🔥🔥 CRITICAL FIX: ALWAYS use slot_number for available slots
                    // slot_number is the user-defined number of available slots
                    // time_slot is just the position (1-9) and should NOT be used for slot count
                    slotCount = parseInt(timeData.slot_number) || 0;
                    isBooked = timeData.booked === true || timeData.status === 'booked' || slotCount <= 0;
                    
                    console.log(`Processing ${slotInfo.display}: 
                                Branch: ${selectedBranch},
                                slot_number from DB: ${timeData.slot_number},
                                time_slot from DB: ${timeData.time_slot},
                                Using slotCount from slot_number: ${slotCount}`);
                    
                    if (color === 'green' && !isBooked && slotCount > 0) {
                        description = `Available slots: ${slotCount}`;
                        isClickable = true;
                    } else if (color === 'green' && (isBooked || slotCount <= 0)) {
                        description = 'Full';
                        color = 'gray';
                        isClickable = false;
                    } else if (isBooked) {
                        description = 'Full';
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
                    color = 'gray';
                    description = 'Not set yet';
                    isClickable = false;
                }

                const isPastSlot = isPastTimeSlot(selectedDate, slotInfo.value);
                if (isPastSlot) {
                    color = 'gray';
                    description = 'Past time';
                    isClickable = false;
                }

                if (isClickable) {
                    availableSlotsCount++;
                }

                const timeSlot = document.createElement('button');
                timeSlot.type = 'button';
                
                if (color !== 'gray') {
                    timeSlot.className = `time-slot color-${color}`;
                } else {
                    timeSlot.className = 'time-slot';
                }
                
                timeSlot.innerHTML = `
                    <div class="time-display">${slotInfo.display}</div>
                    <div class="slot-details">${description}</div>
                `;
                
                timeSlot.setAttribute('title', description);
                timeSlot.setAttribute('data-time-range', slotInfo.value);
                timeSlot.setAttribute('data-slot-count', slotCount); // Store slotCount
                
                // Add data attributes for debugging
                timeSlot.setAttribute('data-slot-number', timeData ? timeData.slot_number : 'null');
                timeSlot.setAttribute('data-time-slot', timeData ? timeData.time_slot : 'null');
                timeSlot.setAttribute('data-is-past', isPastSlot ? 'true' : 'false');
                
                if (!isClickable) {
                    timeSlot.classList.add('disabled');
                    if (isPastSlot) {
                        timeSlot.classList.add('past-slot');
                    } else if (slotCount <= 0 && timeData && timeData.color === 'green') {
                        timeSlot.classList.add('full-slot');
                    }
                    timeSlot.disabled = true;
                    timeSlot.setAttribute('aria-disabled', 'true');
                } else {
                    timeSlot.addEventListener('click', () => selectTime(slotInfo.value, timeSlot, slotCount));
                    
                    if (selectedTime === slotInfo.value) {
                        timeSlot.classList.add('selected');
                    }
                }

                timeSlotsContainer.appendChild(timeSlot);
            });

                if (availableSlotsCount === 0) {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'empty-state';
                    messageDiv.textContent = 'No available time slots for this date';
                    timeSlotsContainer.appendChild(messageDiv);
                }

            })
            .catch(err => {
                console.error('Error loading time slots:', err);
                timeSlotsContainer.innerHTML = `
                    <div class="empty-state" style="color: #ef4444;">
                        <p>Failed to load time slots.</p>
                        <p style="font-size: 0.9em; margin-top: 0.5em;">${err.message}</p>
                        <p style="font-size: 0.8em; margin-top: 0.5em;">Selected Office: ${selectedBranch}</p>
                    </div>
                `;
            });
    }

    function selectDay(year, month, day) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        
        console.log('Selecting day:', dateStr);
        
        // Check if date is clickable
        const colorData = monthColorsData[dateStr];
        const dayElement = document.querySelector(`.day[data-date="${dateStr}"]`);
        
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
            
            selectedDateDisplay.innerHTML = `<span style="color: #ef4444;">${message}</span>`;
            selectedDateDisplay.style.display = 'block';
            
            timeSlotsContainer.innerHTML = '<div class="empty-state">This date is not available</div>';
            
            selectionInfo.style.display = 'none';
            nextBtn.disabled = true;
            
            return;
        }
        
        // Date is available
        selectedDate = new Date(year, month, day);
        selectedTime = null;
        selectedTimeSlot = null;
        
        let successMessage = `Selected: ${selectedDate.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}`;
        
        if (colorData && colorData.description && colorData.description.trim() !== '' && colorData.description !== 'No description yet') {
            successMessage += ` - ${colorData.description}`;
        } else if (colorData && colorData.color === 'green') {
            successMessage += ' - No description yet';
        }
        
        selectedDateDisplay.innerHTML = `<span style="color: #16a34a;">${successMessage}</span>`;
        selectedDateDisplay.style.display = 'block';
        
        renderCalendar();
        renderTimeSlots();
        updateSelectionDisplay();
    }

            function selectTime(timeRange, element, slotCount) {
            if (element.disabled || !selectedDate || isPastTimeSlot(selectedDate, timeRange)) {
                if (element) {
                    element.classList.add('disabled', 'past-slot');
                    element.disabled = true;
                }

                selectedTime = null;
                selectedSlotCount = null;
                updateSelectionDisplay();
                return;
            }

            document.querySelectorAll('.time-slot').forEach(btn => {
                btn.classList.remove('selected');
            });

            selectedTime = timeRange;
            selectedSlotCount = slotCount; // This should now be the slot_number value
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
            
            // Clear time slots when no date is selected
            timeSlotsContainer.innerHTML = '<div class="empty-state">Please select a date from the calendar</div>';
        }
    }

    // Event listeners
    prevMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        
        // Preserve the selected date if it's in the same month/year as the new currentDate
        if (selectedDate) {
            const selectedYear = selectedDate.getFullYear();
            const selectedMonth = selectedDate.getMonth();
            const newYear = currentDate.getFullYear();
            const newMonth = currentDate.getMonth();
            
            if (selectedYear !== newYear || selectedMonth !== newMonth) {
                // Selected date is not in the displayed month, so clear it
                selectedDate = null;
                selectedTime = null;
                selectedTimeSlot = null;
            }
        }
        
        renderCalendar();
        
        // Re-render time slots if we still have a selected date
        if (selectedDate) {
            renderTimeSlots();
        }
    });

    nextMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        
        // Preserve the selected date if it's in the same month/year as the new currentDate
        if (selectedDate) {
            const selectedYear = selectedDate.getFullYear();
            const selectedMonth = selectedDate.getMonth();
            const newYear = currentDate.getFullYear();
            const newMonth = currentDate.getMonth();
            
            if (selectedYear !== newYear || selectedMonth !== newMonth) {
                // Selected date is not in the displayed month, so clear it
                selectedDate = null;
                selectedTime = null;
                selectedTimeSlot = null;
            }
        }
        
        renderCalendar();
        
        // Re-render time slots if we still have a selected date
        if (selectedDate) {
            renderTimeSlots();
        }
    });

    // Handle booking
    nextBtn.addEventListener('click', () => {
        if (!selectedDate || !selectedTime) return;

        if (isPastTimeSlot(selectedDate, selectedTime)) {
            alert('This time slot has already passed. Please select another available time.');
            selectedTime = null;
            selectedSlotCount = null;
            renderTimeSlots();
            updateSelectionDisplay();
            return;
        }
        
        const dateStr = selectedDate.getFullYear() + '-' +
            String(selectedDate.getMonth() + 1).padStart(2, '0') + '-' +
            String(selectedDate.getDate()).padStart(2, '0');

        // Show loading state
        nextBtn.textContent = 'Booking...';
        nextBtn.disabled = true;

        console.log('Sending booking request:', {
            date: dateStr,
            time_range: selectedTime,
            branch: selectedBranch,
            office_id: lawOfficeId
        });

        // For Cordon branch, use the Cordon booking endpoint
        if (selectedBranch === 'Cordon Branch Office') {
            fetch(apiEndpoints.bookSlot, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ 
                    date: dateStr, 
                    time_range: selectedTime,
                    office_id: lawOfficeId
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Cordon booking response:', data);
                
                if (data.status !== 'success') {
                    alert(data.message || 'Slot could not be booked.');
                    nextBtn.textContent = 'Confirm Appointment';
                    nextBtn.disabled = false;
                } else {
                    // Redirect to finalize appointment with branch info
                    window.location.href = `/FinalizeAppointment?date=${dateStr}&time=${encodeURIComponent(selectedTime)}&branch=${encodeURIComponent(selectedBranch)}`;
                }
            })
            .catch(err => {
                console.error('Cordon booking failed:', err);
                alert('Something went wrong while booking. Please try again.');
                nextBtn.textContent = 'Confirm Appointment';
                nextBtn.disabled = false;
            });
        } else {
            // For other branches, use the existing booking endpoint
            fetch(apiEndpoints.bookSlot, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ 
                    date: dateStr, 
                    time_range: selectedTime,
                    office_id: lawOfficeId
                })
            })
            .then(response => response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON response:', text);
                    throw new Error('Server returned an invalid response. Please try again.');
                }
            }))
            .then(data => {
                console.log('Diffun booking response:', data);
                
                if (data.status !== 'success') {
                    alert(data.message || 'Slot could not be booked.');
                    nextBtn.textContent = 'Confirm Appointment';
                    nextBtn.disabled = false;
                } else {
                    window.location.href = `/FinalizeAppointment?date=${dateStr}&time=${encodeURIComponent(selectedTime)}&branch=${encodeURIComponent(selectedBranch)}`;
                }
            })
            .catch(err => {
                console.error('Diffun booking failed:', err);
                alert(err.message || 'Something went wrong while booking. Please try again.');
                nextBtn.textContent = 'Confirm Appointment';
                nextBtn.disabled = false;
            });
        }
    });

    // Initialize calendar on load
    renderCalendar();
</script>
</body>
</html>
