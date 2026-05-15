class CordonCalendarManager {
    static MAX_SLOT_CAPACITY = 4;

    constructor() {
    console.log('CordonCalendarManager: Constructor called');
    
    // Check if already initialized
    if (window.cordonCalendarInitialized) {
        console.log('CordonCalendarManager: Already initialized, skipping...');
        return;
    }
    
    this.currentDate = new Date();
    this.selectedColor = null;
    this.selectedDate = null;
    this.selectedTime = null;
    this.currentView = 'month';
    this.timeSlots = this.generateTimeIntervals();
    this.modalSelectedDate = null;
    this.modalDateColor = null;
    this.existingTimeSlotData = {};
    this.branch = 'cordon';
    
    // Tooltip management
    this.activeTooltips = new Set();
    this.isInitialized = false;
    
    console.log('CordonCalendarManager: Initialized for Cordon branch');
    
    this.initializeEventListeners();
    // Don't load view here - will be called in initialize()
    
    // Mark as initialized
    window.cordonCalendarInitialized = true;
    window.cordonCalendar = this;
    console.log('CordonCalendarManager: Registered globally as window.cordonCalendar');
}
initialize() {
    if (this.isInitialized) {
        console.log('CordonCalendarManager: Already initialized, skipping initialize()');
        return;
    }
    
    console.log('CordonCalendarManager: Initializing calendar view...');
    this.loadCordonMonthView();
    this.isInitialized = true;
    console.log('CordonCalendarManager: Initialization complete');
}
// Add to CordonCalendarManager class
debugCordonData() {
    console.log('=== CORDON DATA DEBUG ===');
    console.log('Modal Selected Date:', this.modalSelectedDate);
    console.log('Existing Time Slot Data:', this.existingTimeSlotData);
    console.log('Time Slots defined:', this.timeSlots);
    
    // Check what API returns
    if (this.modalSelectedDate) {
        $.get(`/cordon/calendar/date-data?date=${this.modalSelectedDate}`)
            .then(response => {
                console.log('API Response:', response);
                console.log('Data structure:', response.data);
                console.log('Time slots array:', response.data?.time_slots);
            })
            .catch(err => {
                console.error('API Error:', err);
            });
    }
}

    generateTimeIntervals() {
        const intervals = [];
        const startHour = 8;
        const endHour = 17;
        
        for (let hour = startHour; hour < endHour; hour++) {
            const displayHour = hour % 12 || 12;
            const nextDisplayHour = (hour + 1) % 12 || 12;
            
            const period = hour < 12 ? 'AM' : 'PM';
            const nextPeriod = (hour + 1) < 12 ? 'AM' : 'PM';
            
            let currentPeriod = period;
            let nextHourPeriod = nextPeriod;
            
            if (hour === 11) {
                currentPeriod = 'AM';
                nextHourPeriod = 'PM';
            } else if (hour === 12) {
                currentPeriod = 'PM';
                nextHourPeriod = 'PM';
            }
            
            const intervalString = `${displayHour}:00 ${currentPeriod} - ${nextDisplayHour}:00 ${nextHourPeriod}`;
            const timeSlot = hour - 7;
            
            intervals.push({
                display: intervalString,
                slot: timeSlot,
                normalized: intervalString.trim().toUpperCase()
            });
        }
        
        console.log('CordonCalendarManager: Generated time intervals', intervals.length);
        return intervals;
    }

    initializeEventListeners() {
        // Month navigation for Cordon
        $('#cordonPrevMonth').off('click').on('click', () => this.changeMonth(-1));
        $('#cordonNextMonth').off('click').on('click', () => this.changeMonth(1));

        // Modal event listeners
        this.initializeModalEventListeners();
        
        console.log('CordonCalendarManager: Event listeners initialized');
    }

    // Added: Switch view method
    switchView(view) {
        console.log('CordonCalendarManager: Switching to view:', view);
        this.currentView = view;
        
        // Update tabs
        $('.view-tab').removeClass('active');
        $(`.view-tab[data-view="${view}"]`).addClass('active');
        
        // Show/hide views
        $('.view-pane').hide();
        if (view === 'cordon') {
            $('#cordonView').show();
            this.loadCordonMonthView();
        } else {
            $('#monthView').show();
        }
    }

    initializeModalEventListeners() {
        // Date color selection in modal
        $('.modal-color-option[data-color]').off('click').on('click', (e) => {
            const $option = $(e.currentTarget);
            const color = $option.data('color');
            const isDateColor = $option.closest('.modal-section').find('h6').text().includes('Date');
            
            if (isDateColor) {
                this.selectModalDateColor(color);
            }
        });

        // Save modal changes
        $('#saveModalChanges').off('click').on('click', () => {
            this.saveCordonModalChanges();
        });

        // Modal hidden event
        $('#colorSelectionModal').off('hidden.bs.modal').on('hidden.bs.modal', () => {
            this.resetModalState();
        });
    }

    selectModalDateColor(color) {
        this.modalDateColor = color;
        
        $('.modal-section:first .modal-color-option').removeClass('selected');
        $(`.modal-section:first .modal-color-option[data-color="${color}"]`).addClass('selected');
        
        console.log('CordonCalendarManager: Selected date color', color);
    }

    isPastDate(dateString) {
        if (!dateString) return false;
        
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const [year, month, day] = dateString.split('-');
        const compareDate = new Date(year, month - 1, day);
        compareDate.setHours(0, 0, 0, 0);
        
        return compareDate < today;
    }

    async openModalForDate(date) {
    console.log('CordonCalendarManager: Opening modal for date', { date, branch: 'cordon' });
    
    if (this.isPastDate(date)) {
        this.showMessage('Cannot edit past dates', 'error');
        return;
    }
    
    this.modalSelectedDate = date;
    
    const [year, month, day] = date.split('-');
    const dateObj = new Date(year, month - 1, day);
    const formattedDate = dateObj.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    // Update modal title with branch identifier
    $('#modalDateDisplay').text(formattedDate + ' (Cordon Branch)');
    
    this.resetModalState();
    
    try {
        await this.loadModalData(date);
        
        // Debug: Check what data was loaded
        this.debugCordonData();
        
        this.populateTimeSlotsTable();
        
        // Mark this as Cordon branch
        $('#saveModalChanges').data('branch', 'cordon');
        
        $('#colorSelectionModal').modal('show');
        console.log('CordonCalendarManager: Modal opened successfully');
    } catch (error) {
        console.error('CordonCalendarManager: Failed to open modal', error);
        this.showMessage('Failed to load Cordon branch data', 'error');
    }
}
destroy() {
    console.log('CordonCalendarManager: Destroying instance');
    
    // Clean up event listeners
    $('#cordonPrevMonth').off('click');
    $('#cordonNextMonth').off('click');
    $('.modal-color-option[data-color]').off('click');
    $('#saveModalChanges').off('click');
    $('#colorSelectionModal').off('hidden.bs.modal');
    $('.time-slot-availability').off('change');
    $('#cordonMonthGrid .day-cell').off('click mouseenter mouseleave mousemove');
    
    // Clean up tooltips
    this.cleanupAllTooltips();
    
    // Remove global reference
    delete window.cordonCalendar;
    window.cordonCalendarInitialized = false;
    this.isInitialized = false;
    
    console.log('CordonCalendarManager: Instance destroyed');
}
    resetModalState() {
        this.modalDateColor = null;
        $('.modal-color-option').removeClass('selected');
        $('.time-slot-row').removeClass('selected');
        $('#dateDescriptionInput').val('');
    }

    async loadModalData(date) {
        console.log('CordonCalendarManager: Loading modal data for', { date, branch: 'cordon' });
        
        try {
            const response = await $.get(`/cordon/calendar/date-data?date=${date}`);
            
            console.log('CordonCalendarManager: Modal data response', response);
            
            if (response.status === 'success' && response.branch === 'cordon') {
                const data = response.data;
                
                // Set date color and description
                if (data.date_color) {
                    this.selectModalDateColor(data.date_color);
                    $('#dateDescriptionInput').val(data.date_description || data.description || '');
                }
                
                // Transform Cordon time_slots array into keyed by time range
                this.existingTimeSlotData = {};
                
                if (data.time_slots && typeof data.time_slots === 'object') {
                    console.log('CordonCalendarManager: Raw time slots:', data.time_slots);
                    
                    // Transform the object keyed by time_slot number to keyed by time range
                    Object.values(data.time_slots).forEach(slot => {
                        if (!slot) return;
                        
                        // Use time field if available, otherwise format from time_range
                        let timeRange = slot.time || slot.time_range;
                        
                        if (!timeRange && (slot.slot_number || slot.time_slot)) {
                            // Generate time range based on slot number
                            const timeMap = {
                                1: '8:00 AM - 9:00 AM',
                                2: '9:00 AM - 10:00 AM',
                                3: '10:00 AM - 11:00 AM',
                                4: '11:00 AM - 12:00 PM',
                                5: '12:00 PM - 1:00 PM',
                                6: '1:00 PM - 2:00 PM',
                                7: '2:00 PM - 3:00 PM',
                                8: '3:00 PM - 4:00 PM',
                                9: '4:00 PM - 5:00 PM'
                            };
                            timeRange = timeMap[slot.slot_number] || timeMap[slot.time_slot] || 'Unknown';
                        }
                        
                        if (timeRange) {
                            // Store all data, including empty/null values
                            this.existingTimeSlotData[timeRange] = {
                                color: slot.color || '',
                                description: slot.description || '',
                                booked: slot.booked || 0,
                                time_slot: slot.time_slot || 0,
                                slot_number: slot.slot_number || 0
                            };
                        }
                    });
                    
                    console.log('CordonCalendarManager: Transformed time slots:', this.existingTimeSlotData);
                }
                
                console.log('CordonCalendarManager: Modal data loaded successfully', {
                    date_color: data.date_color,
                    time_slots_count: Object.keys(this.existingTimeSlotData).length,
                    existingTimeSlotData: this.existingTimeSlotData
                });
            } else {
                throw new Error('Invalid response from Cordon branch');
            }
        } catch (error) {
            console.error('CordonCalendarManager: Error loading modal data', error);
            this.existingTimeSlotData = {};
            throw error;
        }
    }

// Add helper method to format time
formatTimeTo12Hour(timeString) {
    if (!timeString) return '';
    
    // Remove seconds if present
    timeString = timeString.split(':').slice(0, 2).join(':');
    
    // Convert to 12-hour format
    const [hour, minute] = timeString.split(':');
    let hour12 = parseInt(hour);
    const period = hour12 >= 12 ? 'PM' : 'AM';
    
    if (hour12 === 0) hour12 = 12;
    else if (hour12 > 12) hour12 -= 12;
    
    return `${hour12}:${minute} ${period}`;
}

   populateTimeSlotsTable() {
        const tableBody = $('#timeSlotsTableBody');
        tableBody.empty();
        
        // Get current date and time
        const now = new Date();
        const todayStr = this.formatDate(now);
        const isToday = this.modalSelectedDate === todayStr;
        
        this.timeSlots.forEach(slot => {
            // Check if this time slot is in the past (for today only)
            let isPastTimeSlot = false;
            if (isToday) {
                const startTimeStr = slot.display.split(' - ')[0];
                const [time, period] = startTimeStr.split(' ');
                const [hours, minutes] = time.split(':').map(Number);
                
                let hour24 = hours;
                if (period === 'PM' && hour24 !== 12) {
                    hour24 += 12;
                } else if (period === 'AM' && hour24 === 12) {
                    hour24 = 0;
                }
                
                const slotTime = new Date(now.getFullYear(), now.getMonth(), now.getDate(), hour24, minutes);
                isPastTimeSlot = slotTime < now;
            }
            
            // Get existing data for this slot by time range
            const existingData = this.existingTimeSlotData[slot.display];
            
            // IMPORTANT: Get slot number - set to 0 if no existing data
            let slotNumberValue = '0'; // DEFAULT TO 0
            let currentColor = '';
            let description = '';
            
            if (existingData) {
                // Always use existing slot_number, even if it's 0
                slotNumberValue = existingData.slot_number !== undefined ? existingData.slot_number.toString() : '0';
                currentColor = existingData.color ? existingData.color : '';
                description = existingData.description || '';
            }
            
            const row = `
                <tr class="time-slot-row ${isPastTimeSlot ? 'past-time-slot' : ''}" 
                    data-time-slot="${slot.slot}" 
                    data-time-range="${slot.display}"
                    ${isPastTimeSlot ? 'data-past="true"' : ''}>
                    <td>
                        <div class="time-display">${slot.display}</div>
                    </td>
                    <td>
                        <input type="number" 
                            class="form-control form-control-sm time-slot-number" 
                            value="${slotNumberValue}"
                            min="0" 
                            max="${CordonCalendarManager.MAX_SLOT_CAPACITY}"
                            style="width: 80px;"
                            ${isPastTimeSlot ? 'disabled' : ''}
                            placeholder="Enter slot #">
                    </td>
                    <td>
                        <input type="text" 
                            class="form-control form-control-sm time-slot-description" 
                            placeholder="Description for this time slot" 
                            value="${description}"
                            ${isPastTimeSlot ? 'disabled' : ''}>
                    </td>
                    <td style="width: 150px;">
                        <select class="form-select form-select-sm time-slot-availability" 
                                ${isPastTimeSlot ? 'disabled' : ''}>
                            <option value="">Select availability</option>
                            <option value="green" ${currentColor === 'green' ? 'selected' : ''}>Available</option>
                            <option value="red" ${currentColor === 'red' ? 'selected' : ''}>Not Available</option>
                        </select>
                    </td>
                </tr>
            `;
            tableBody.append(row);
        });
        
        // Initialize availability dropdowns
        this.initializeAvailabilityDropdowns();
        
        // Debug: Check slot numbers after population
        this.debugSlotInputs();
    }

// Add this debug method to help troubleshoot
debugSlotInputs() {
    setTimeout(() => {
        const inputs = $('.time-slot-number');
        console.log(`Total slot inputs: ${inputs.length}`);
        inputs.each(function(index) {
            console.log(`Slot ${index + 1}: value = ${$(this).val()}`);
        });
    }, 100);
}


    initializeAvailabilityDropdowns() {
        $(document).off('change', '.time-slot-availability').on('change', '.time-slot-availability', (e) => {
            const $dropdown = $(e.currentTarget);
            const $row = $dropdown.closest('.time-slot-row');
            
            if ($row.hasClass('past-time-slot')) {
                this.showMessage('Past time slots cannot be edited', 'error');
                $dropdown.val('');
                return;
            }
        });

        $(document).off('input', '.time-slot-number').on('input', '.time-slot-number', (e) => {
            const $input = $(e.currentTarget);
            const rawValue = parseInt($input.val(), 10);
            const normalizedValue = Number.isNaN(rawValue)
                ? 0
                : Math.max(0, Math.min(CordonCalendarManager.MAX_SLOT_CAPACITY, rawValue));

            $input.val(normalizedValue);
        });
    }

   async saveCordonModalChanges() {
    if (!this.modalSelectedDate) {
        console.error('CordonCalendarManager: No date selected for saving');
        return;
    }
    
    console.log('CordonCalendarManager: Saving modal changes for Cordon branch', {
        date: this.modalSelectedDate
    });
    
    try {
        $('#saveModalChanges').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        // Define saveData here so it's available throughout the function
        const saveData = {
            date: this.modalSelectedDate,
            date_color: this.modalDateColor,
            date_description: $('#dateDescriptionInput').val().trim(),
            time_slots: {},
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        // Collect only edited time slot data
        let editedSlotsCount = 0;
        
        $('.time-slot-row').each((index, row) => {
            const $row = $(row);
            const fixedTimeSlot = $row.data('time-slot'); // This is 1-9 (fixed position)
            const timeRange = $row.data('time-range');
            
            // Get current values from inputs
            const slotNumberInput = $row.find('.time-slot-number').val();
            const rawSlotNumber = parseInt(slotNumberInput, 10);
            const slotNumber = Number.isNaN(rawSlotNumber)
                ? 0
                : Math.max(0, Math.min(CordonCalendarManager.MAX_SLOT_CAPACITY, rawSlotNumber));
            const color = $row.find('.time-slot-availability').val();
            const description = $row.find('.time-slot-description').val().trim();
            
            // Only include in save data if:
            // 1. Slot has a color selected (green/red) - regardless of slot number
            // 2. OR Slot has a slot number > 0 (even without color)
            // SAVE ONLY if user REALLY configured the slot
                const hasValidSlot = slotNumber > 0 && color;



                if (hasValidSlot) {
                    saveData.time_slots[fixedTimeSlot] = {
                        time_slot: fixedTimeSlot,
                        slot_number: slotNumber,
                        color: color || null,
                        description: description || null
                    };
                    editedSlotsCount++;
                }

        });
        
        console.log('CordonCalendarManager: Saving data', {
            date: saveData.date,
            date_color: saveData.date_color,
            edited_slots_count: editedSlotsCount,
            time_slots: saveData.time_slots
        });
        
        // Debug: Log the actual data being sent
        console.log('CordonCalendarManager: Full saveData object:', saveData);
        
        // Call debugSaveData method if it exists
        if (typeof this.debugSaveData === 'function') {
            this.debugSaveData(saveData);
        }
        
        // Send to Cordon-specific endpoint
        const response = await $.ajax({
            url: '/cordon/calendar/save-date-data',
            method: 'POST',
            data: saveData,
            dataType: 'json'
        });
        
        console.log('CordonCalendarManager: Save response', response);
        
        if (response.status === 'success' && response.branch === 'cordon') {
            this.showMessage('Cordon branch calendar data saved successfully!', 'success');
            $('#colorSelectionModal').modal('hide');
            
            setTimeout(() => {
                this.loadCordonMonthView();
            }, 500);
        } else {
            throw new Error(response.message || 'Failed to save Cordon data');
        }
        
    } catch (error) {
        console.error('CordonCalendarManager: Error saving modal data', error);
        
        // Better error handling for server errors
        let errorMessage = 'Unknown server error occurred';
        
        if (error.status === 500) {
            errorMessage = 'Server error (500). Please check server logs.';
        } else if (error.status === 404) {
            errorMessage = 'Endpoint not found. Please check the URL.';
        } else if (error.status === 403) {
            errorMessage = 'Access forbidden. Please check your permissions.';
        } else if (error.responseJSON && error.responseJSON.message) {
            errorMessage = error.responseJSON.message;
        } else if (error.responseText) {
            // Try to parse the response text
            try {
                const errorData = JSON.parse(error.responseText);
                if (errorData.message) {
                    errorMessage = errorData.message;
                }
            } catch (e) {
                // If not JSON, use the response text
                errorMessage = error.responseText.substring(0, 100) + '...';
            }
        } else if (error.message) {
            errorMessage = error.message;
        }
        
        this.showMessage('Error saving Cordon branch data: ' + errorMessage, 'error');
        
        // Log more details for debugging
        console.error('CordonCalendarManager: Error details:', {
            status: error.status,
            statusText: error.statusText,
            responseText: error.responseText,
            responseJSON: error.responseJSON
        });
    } finally {
        $('#saveModalChanges').prop('disabled', false).html('Save Changes');
    }
}

debugSaveData(saveData) {
    console.log('=== SAVE DATA DEBUG ===');
    console.log('Date:', saveData.date);
    console.log('Date Color:', saveData.date_color);
    console.log('Date Description:', saveData.date_description);
    console.log('Time Slots Count:', Object.keys(saveData.time_slots).length);
    console.log('Time Slots Structure:');
    
    Object.entries(saveData.time_slots).forEach(([key, value]) => {
        console.log(`  Slot ${key}:`, {
            time_slot: value.time_slot,
            slot_number: value.slot_number,
            color: value.color,
            description: value.description
        });
    });
    
    // Check for any NaN or invalid values
    Object.entries(saveData.time_slots).forEach(([key, value]) => {
        if (isNaN(value.slot_number) || value.slot_number < 0) {
            console.warn(`Invalid slot_number for slot ${key}:`, value.slot_number);
        }
    });
}
// Add to CordonCalendarManager class
debugSaveData(saveData) {
    console.log('=== SAVE DATA DEBUG ===');
    console.log('Date:', saveData.date);
    console.log('Date Color:', saveData.date_color);
    console.log('Date Description:', saveData.date_description);
    console.log('Time Slots Count:', Object.keys(saveData.time_slots).length);
    console.log('Time Slots Structure:');
    
    Object.entries(saveData.time_slots).forEach(([key, value]) => {
        console.log(`  Slot ${key}:`, {
            time_slot: value.time_slot,
            slot_number: value.slot_number,
            color: value.color,
            description: value.description
        });
    });
    
    // Check for any NaN or invalid values
    Object.entries(saveData.time_slots).forEach(([key, value]) => {
        if (isNaN(value.slot_number) || value.slot_number < 0) {
            console.warn(`Invalid slot_number for slot ${key}:`, value.slot_number);
        }
    });
}

    changeMonth(direction) {
        this.currentDate.setMonth(this.currentDate.getMonth() + direction);
        this.loadCordonMonthView();
    }

    async loadCordonMonthView() {
        console.log('CordonCalendarManager: Loading month view for Cordon branch');
    
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        
        $('#cordonCurrentMonthYear').text(this.currentDate.toLocaleString('default', { 
            month: 'long', 
            year: 'numeric' 
        }));
            
        // Generate calendar grid
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startDay = firstDay.getDay();
        const daysInMonth = lastDay.getDate();
        
        let html = '';
        
        // Previous month days
        const prevMonthLastDay = new Date(year, month, 0).getDate();
        for (let i = startDay - 1; i >= 0; i--) {
            const day = prevMonthLastDay - i;
            const date = new Date(year, month - 1, day);
            html += this.createDayCell(day, date, true);
        }
        
        // Current month days
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            html += this.createDayCell(day, date, false);
        }
        
        // Next month days
        const totalCells = 42;
        const remainingCells = totalCells - (startDay + daysInMonth);
        for (let day = 1; day <= remainingCells; day++) {
            const date = new Date(year, month + 1, day);
            html += this.createDayCell(day, date, true);
        }
        
        $('#cordonMonthGrid').html(html);

        // Force-correct month membership
        $('#cordonMonthGrid .day-cell').each((i, cell) => {
            const date = $(cell).data('date');
            if (!date) return;

            const parts = String(date).split('-');
            if (parts.length < 3) return;
            const cellMonth = parseInt(parts[1], 10) - 1;
            const currentMonth = this.currentDate.getMonth();

            if (cellMonth !== currentMonth) {
                $(cell).addClass('other-month');
            } else {
                $(cell).removeClass('other-month');
            }
        });

        // Load Cordon month colors
        await this.loadCordonMonthColors();

        // Add click handlers for Cordon grid
        $('#cordonMonthGrid .day-cell:not(.past-date):not(.other-month)').off('click').on('click', (e) => {
            const date = $(e.currentTarget).data('date');
            if (date) {
                this.openModalForDate(date);
            }
        });
        
        // Style past dates
        this.stylePastDates('#cordonMonthGrid');
        
        // Initialize tooltips for Cordon dates
        this.initializeTooltips('#cordonMonthGrid');
        
        console.log('CordonCalendarManager: Month view loaded with tooltips');
    }

    createDayCell(day, date, isOtherMonth) {
    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const dd = String(date.getDate()).padStart(2, '0');
    const dateStr = `${yyyy}-${mm}-${dd}`;
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const cellDate = new Date(yyyy, mm - 1, dd);
    cellDate.setHours(0, 0, 0, 0);
    
    const isPastDate = cellDate < today;
    const isToday = cellDate.getTime() === today.getTime();
    
    let additionalClasses = '';
    if (isPastDate) {
        additionalClasses += 'past-date ';
    }
    if (isToday) {
        additionalClasses += 'current-date ';
    }
    
    // Ensure the date is valid before creating the cell
    if (isNaN(cellDate.getTime())) {
        console.error('CordonCalendarManager: Invalid date for cell', { day, yyyy, mm, dd });
        return `
            <div class="day-cell ${isOtherMonth ? 'other-month' : ''} ${additionalClasses}" 
                data-date="invalid">
                <span>${day}</span>
            </div>
        `;
    }
    
    return `
        <div class="day-cell ${isOtherMonth ? 'other-month' : ''} ${additionalClasses}" 
            data-date="${dateStr}">
            <span>${date.getDate()}</span>
        </div>
    `;
}

    stylePastDates(selector) {
        $(`${selector} .day-cell.past-date`).each(function() {
            const $cell = $(this);
            $cell.css({
                'cursor': 'not-allowed',
                'opacity': '0.6'
            });
            $cell.off('mouseenter mouseleave');
            
            if (!$cell.find('.past-date-indicator').length) {
                $cell.append('<div class="past-date-indicator" title="Past dates cannot be edited"></div>');
            }
        });
    }

    async loadCordonMonthColors() {
    const month = this.currentDate.toISOString().substring(0, 7);
    
    console.log('CordonCalendarManager: Loading month colors for', month);
    
    try {
        const response = await $.get(`/cordon/calendar/month/colors?month=${month}`);
        
        console.log('CordonCalendarManager: Month colors response', response);
        
        // Clear all colors first
        $('#cordonMonthGrid .day-cell')
            .removeClass('has-color color-red color-orange color-green')
            .css('background-color', '')
            .css('color', '')
            .removeAttr('data-description')
            .removeAttr('title');

        if (response.status === 'success' && response.data) {
            console.log('CordonCalendarManager: Found colors data:', response.data);
            
            Object.entries(response.data).forEach(([date, item]) => {
                const $cell = $(`#cordonMonthGrid .day-cell[data-date="${date}"]`);
                if ($cell.length && item.color) {
                    $cell.addClass(`has-color color-${item.color}`);
                    $cell.attr('data-description', item.description || '');
                    
                    // Set background color based on color
                    const colorMap = {
                        'red': '#dc2626',
                        'orange': '#ea580c',
                        'green': '#16a34a'
                    };
                    
                    if (colorMap[item.color]) {
                        $cell.css('background-color', colorMap[item.color]);
                        $cell.css('color', 'white');
                    }
                    
                    // Add title for tooltip
                    const description = item.description || 'No description';
                    $cell.attr('title', `Description: ${description}`);
                }
            });
            
            console.log('CordonCalendarManager: Month colors applied successfully');
            
        } else {
            console.warn('CordonCalendarManager: No colors data found in response');
        }
        
        // Initialize tooltips
        setTimeout(() => {
            this.initializeTooltips('#cordonMonthGrid');
        }, 100);
        
    } catch (err) {
        console.error("CordonCalendarManager: Error loading month colors", err);
        this.showMessage('Failed to load Cordon branch colors: ' + err.message, 'error');
    }
}

    initializeTooltips(selector) {
        console.log('CordonCalendarManager: Initializing tooltips for', selector);
        
        // Clean up ALL existing tooltips first
        this.cleanupAllTooltips();
        
        // Remove previous event listeners
        $(`${selector} .day-cell`).off('mouseenter mouseleave mousemove');
        
        const self = this;
        
        // Add tooltip functionality to ALL Cordon day cells
        $(`${selector} .day-cell`).on('mouseenter', function(e) {
            const $element = $(this);
            let description = $element.attr('data-description');
            
            if (!description || description.trim() === '') {
                description = 'Not set yet';
            }
            
            // Skip if element doesn't have a date attribute
            if (!$element.data('date')) {
                console.warn('CordonCalendarManager: Day cell missing date data', $element);
                return;
            }
            
            // Don't create tooltip if one already exists for this element
            if ($element.data('tooltip-active')) {
                return;
            }
            
            const tooltip = $('<div class="calendar-tooltip"></div>')
                .html(self.formatTooltipContent(description, $element))
                .appendTo('body');
            
            self.positionTooltip($element, tooltip, e);
            $element.data('tooltip', tooltip);
            $element.data('tooltip-active', true);
            
            // Track active tooltip
            self.activeTooltips.add({
                element: $element,
                tooltip: tooltip
            });
            
        }).on('mouseleave', function() {
            const tooltip = $(this).data('tooltip');
            if (tooltip) {
                tooltip.remove();
                $(this).removeData('tooltip');
                $(this).removeData('tooltip-active');
                
                // Remove from active set
                self.activeTooltips.forEach(item => {
                    if (item.element.is($(this))) {
                        self.activeTooltips.delete(item);
                    }
                });
            }
        }).on('mousemove', function(e) {
            const tooltip = $(this).data('tooltip');
            if (tooltip) {
                const $element = $(this);
                self.positionTooltip($element, tooltip, e);
            }
        });
    }
     forceCleanup() {
        console.log('CordonCalendarManager: Force cleaning up');
        this.cleanupAllTooltips();
        
        // Also clean up any stuck event listeners
        $('.day-cell').off('mouseenter mouseleave mousemove');
        
        // Reset any hover states
        $('.day-cell').removeClass('hovered selected active-hover');
    }

    // New method to clean up all tooltips
    cleanupAllTooltips() {
        console.log('CordonCalendarManager: Cleaning up all tooltips', this.activeTooltips.size);
        
        // Remove all tooltip elements
        $('.calendar-tooltip').remove();
        
        // Remove tooltip data from all day cells
        $('.day-cell').removeData('tooltip').removeData('tooltip-active');
        
        // Clear the active set
        this.activeTooltips.clear();
        
        // Also remove any Bootstrap tooltips
        $('[data-bs-toggle="tooltip"]').each(function() {
            const bsTooltip = bootstrap.Tooltip.getInstance(this);
            if (bsTooltip) {
                bsTooltip.hide();
                bsTooltip.dispose();
            }
        });
    }

    formatTooltipContent(description, $element) {
    const date = $element.data('date');
    
    // Check if date exists and is valid
    if (!date || typeof date !== 'string' || !date.includes('-')) {
        return `
            <div class="tooltip-header">
                <i class="fas fa-calendar-alt"></i>
                <strong>Invalid Date</strong>
            </div>
            <div class="tooltip-description">
                <div class="tooltip-label">
                    <i class="fas fa-sticky-note"></i>
                    Description:
                </div>
                <div class="tooltip-text">${this.escapeHtml(description || 'Not set yet')}</div>
            </div>
        `;
    }
    
    const [year, month, day] = date.split('-');
    const dateObj = new Date(year, month - 1, day);
    const formattedDate = dateObj.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    let html = `
        <div class="tooltip-header">
            <i class="fas fa-calendar-alt"></i>
            <strong>${formattedDate}</strong>
        </div>
    `;
    
    html += `
        <div class="tooltip-description">
            <div class="tooltip-label">
                <i class="fas fa-sticky-note"></i>
                Description:
            </div>
            <div class="tooltip-text">${this.escapeHtml(description || 'Not set yet')}</div>
        </div>
    `;
    
    return html;
}

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    positionTooltip($element, tooltip, e) {
        const elementRect = $element[0].getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        
        const viewportPadding = 10;
        const tooltipHeight = tooltip.outerHeight();
        const tooltipWidth = tooltip.outerWidth();
        
        const aboveTop = elementRect.top + scrollTop - tooltipHeight - 8;
        const belowTop = elementRect.top + scrollTop + elementRect.height + 8;
        
        let top = aboveTop;
        let positionClass = 'above';
        
        if (aboveTop < viewportPadding) {
            top = belowTop;
            positionClass = 'below';
        }
        
        let left = elementRect.left + scrollLeft + (elementRect.width / 2) - (tooltipWidth / 2);
        
        if (left < viewportPadding) {
            left = viewportPadding;
        } else if (left + tooltipWidth > window.innerWidth - viewportPadding) {
            left = window.innerWidth - tooltipWidth - viewportPadding;
        }
        
        tooltip.css({
            top: top + 'px',
            left: left + 'px'
        }).removeClass('above below').addClass(positionClass + ' show');
    }

    showMessage(message, type) {
        console.log(`CordonCalendarManager: ${type} - ${message}`);
        
        const messageDiv = $('#messageContainer');
        messageDiv.html(`<div class="message ${type}">${message}</div>`);
        
        setTimeout(() => {
            messageDiv.empty();
        }, 3000);
    }
    
    formatDate(date) {
        if (!(date instanceof Date) || isNaN(date)) {
            console.error('Invalid date passed to formatDate:', date);
            return '';
        }
        
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        
        return `${year}-${month}-${day}`;
    }
    async checkDatabaseForSlotNumbers(date) {
    try {
        const response = await $.get(`/cordon/calendar/debug-slot-data?date=${date}`);
        console.log('Database slot data:', response);
        return response;
    } catch (error) {
        console.error('Error checking database:', error);
        return null;
    }
}
}

/// Initialize only once when DOM is ready
$(document).ready(function() {
    console.log('CordonCalendarManager: DOM ready, checking for initialization...');
    
    // Clean up any existing instance
    if (window.cordonCalendar && window.cordonCalendar.destroy) {
        window.cordonCalendar.destroy();
    }
    
    // Reset initialization flag
    window.cordonCalendarInitialized = false;
    
    console.log('Creating new CordonCalendarManager instance');
    window.cordonCalendar = new CordonCalendarManager();
    
    // Initialize after creation
    setTimeout(() => {
        if (window.cordonCalendar && typeof window.cordonCalendar.initialize === 'function') {
            window.cordonCalendar.initialize();
        }
    }, 500);
});
