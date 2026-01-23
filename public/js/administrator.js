class CalendarManager {
    constructor() {
        this.currentDate = new Date();
        this.selectedColor = null;
        this.selectedDate = null;
        this.selectedTime = null;
        this.currentView = 'month';
        this.timeSlots = this.generateTimeIntervals();
        this.modalSelectedDate = null;
        this.modalSelectedTimeSlot = null;
        this.modalDateColor = null;
        this.modalTimeColor = null;
        this.existingTimeSlotData = {};
        
        this.initializeEventListeners();
        this.loadMonthView();
    }

    generateTimeIntervals() {
        const intervals = [];
        const startHour = 8; // 8 AM
        const endHour = 17;  // 5 PM
        
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
            
            // Fixed time slot numbers: 1-9 for 8AM-5PM
            const timeSlot = hour - 7; // 8:00 AM = slot 1, 9:00 AM = slot 2, etc.
            
            intervals.push({
                display: intervalString,
                slot: timeSlot, // This maps directly to time_slot column
                normalized: intervalString.trim().toUpperCase()
            });
        }
        
        console.log('Generated time intervals:', intervals);
        return intervals;
    }

    initializeEventListeners() {
        // View tabs
        $('.view-tab').on('click', (e) => {
            this.switchView($(e.currentTarget).data('view'));
        });
        
        // Month navigation
        $('#prevMonth').on('click', () => this.changeMonth(-1));
        $('#nextMonth').on('click', () => this.changeMonth(1));
        
        // Week navigation
        $('#prevWeek').on('click', () => this.changeWeek(-1));
        $('#nextWeek').on('click', () => this.changeWeek(1));

        // Modal event listeners
        this.initializeModalEventListeners();
    }

    initializeModalEventListeners() {
        // Date color selection in modal
        $('.modal-color-option[data-color]').on('click', (e) => {
            const $option = $(e.currentTarget);
            const color = $option.data('color');
            const isDateColor = $option.closest('.modal-section').find('h6').text().includes('Date');
            
            if (isDateColor) {
                this.selectModalDateColor(color);
            } else {
                if (this.modalSelectedTimeSlot) {
                    this.selectModalTimeColor(color);
                } else {
                    this.showMessage('Please select a time slot first', 'error');
                }
            }
        });

        // Time slot selection in modal
        $(document).on('click', '.time-slot-row', (e) => {
            const $row = $(e.currentTarget).closest('.time-slot-row');
            this.selectModalTimeSlot($row);
        });

        // Save modal changes
        $('#saveModalChanges').on('click', () => {
            this.saveModalChanges();
        });

        // Modal hidden event
        $('#colorSelectionModal').on('hidden.bs.modal', () => {
            this.resetModalState();
        });
    }

    selectModalDateColor(color) {
        this.modalDateColor = color;
        
        // Update UI
        $('.modal-section:first .modal-color-option').removeClass('selected');
        $(`.modal-section:first .modal-color-option[data-color="${color}"]`).addClass('selected');
    }

    selectModalTimeColor(color) {
        this.modalTimeColor = color;
        
        // Update UI
        $('.modal-section:last .modal-color-option').removeClass('selected');
        $(`.modal-section:last .modal-color-option[data-color="${color}"]`).addClass('selected');
        
        // Update the selected time slot row if one is selected
        if (this.modalSelectedTimeSlot) {
            this.applyColorToTimeSlot(this.modalSelectedTimeSlot, color);
        }
    }

    selectModalTimeSlot($row) {
        // Remove previous selection
        $('.time-slot-row').removeClass('selected');
        
        // Add selection to current row
        $row.addClass('selected');
        this.modalSelectedTimeSlot = $row;
        
        // Get existing color from the row
        const existingColor = $row.hasClass('color-red') ? 'red' : 
                             $row.hasClass('color-green') ? 'green' : null;
        
        // Update time color selection in the modal
        if (existingColor) {
            this.selectModalTimeColor(existingColor);
        } else {
            // Reset time color selection
            $('.modal-section:last .modal-color-option').removeClass('selected');
            this.modalTimeColor = null;
        }
        
        console.log('Selected time slot:', {
            slot: $row.data('time-slot'),
            timeRange: $row.data('time-range'),
            color: existingColor
        });
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
        // Check if the date is in the past
        if (this.isPastDate(date)) {
            this.showMessage('Cannot edit past dates', 'error');
            return;
        }
        
        this.modalSelectedDate = date;
        
        // Format date for display
        const [year, month, day] = date.split('-');
        const dateObj = new Date(year, month - 1, day);
        const formattedDate = dateObj.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Update modal title
        $('#modalDateDisplay').text(formattedDate);
        
        // Reset modal state
        this.resetModalState();
        
        // Load existing data for this date
        await this.loadModalData(date);
        
        // Populate time slots table
        this.populateTimeSlotsTable();
        
        // Show modal
        $('#colorSelectionModal').modal('show');
    }

    resetModalState() {
        this.modalSelectedTimeSlot = null;
        this.modalDateColor = null;
        this.modalTimeColor = null;
        
        // Reset UI
        $('.modal-color-option').removeClass('selected');
        $('.time-slot-row').removeClass('selected');
        $('#dateDescriptionInput').val('');
    }

    populateTimeSlotsTable() {
        const tableBody = $('#timeSlotsTableBody');
        tableBody.empty();
        
        // Get current date and time
        const now = new Date();
        const todayStr = this.formatDate(now);
        
        // Check if this is today's date
        const isToday = this.modalSelectedDate === todayStr;
        
        this.timeSlots.forEach(slot => {
            // Check if this time slot is in the past (for today only)
            let isPastTimeSlot = false;
            if (isToday) {
                // Parse the start time from the time slot display
                const startTimeStr = slot.display.split(' - ')[0]; // Get "8:00 AM"
                const [time, period] = startTimeStr.split(' ');
                const [hours, minutes] = time.split(':').map(Number);
                
                // Convert to 24-hour format
                let hour24 = hours;
                if (period === 'PM' && hour24 !== 12) {
                    hour24 += 12;
                } else if (period === 'AM' && hour24 === 12) {
                    hour24 = 0;
                }
                
                // Create a date object for this time slot
                const slotTime = new Date(now.getFullYear(), now.getMonth(), now.getDate(), hour24, minutes);
                
                // Check if this time slot is in the past
                isPastTimeSlot = slotTime < now;
            }
            
            // Check if this slot has existing data
            const existingData = this.existingTimeSlotData[slot.slot];
            const hasColor = existingData && existingData.color;
            
            // Determine the slot number to display
            // If no color is set, show 0. Otherwise show the original slot number
            const slotNumberValue = hasColor ? slot.slot : 0;
            
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
                               max="24"
                               style="border: 1px solid #ced4da; width: 80px;"
                               ${isPastTimeSlot ? 'disabled' : ''}>
                    </td>
                    <td>
                        <input type="text" 
                               class="form-control form-control-sm time-slot-description" 
                               placeholder="Description for this time slot" 
                               style="border: 1px solid #ced4da;"
                               ${isPastTimeSlot ? 'disabled' : ''}>
                    </td>
                    <td style="width: 60px; text-align: center;">
                        <div class="time-slot-color-indicator"></div>
                    </td>
                </tr>
            `;
            tableBody.append(row);
        });
        
        // Load existing time slot data
        this.loadTimeSlotData();
        
        // Make time slots clickable for color selection (but not interfere with inputs)
        this.initializeTimeSlotInteractions();
        
        // Update slot numbers based on color status
        this.updateSlotNumbersBasedOnColor();
    }
    updateSlotNumbersBasedOnColor() {
        $('.time-slot-row').each((index, row) => {
            const $row = $(row);
            const hasColor = $row.hasClass('color-red') || $row.hasClass('color-green');
            const originalSlot = $row.data('time-slot');
            
            if (!hasColor) {
                // If no color, set slot number to 0
                $row.find('.time-slot-number').val(0);
            } else {
                // If has color, ensure slot number is not 0
                const currentValue = parseInt($row.find('.time-slot-number').val());
                if (currentValue === 0) {
                    $row.find('.time-slot-number').val(originalSlot);
                }
            }
        });
    }
 initializeTimeSlotInteractions() {
        // Remove previous event listeners to prevent duplicates
        $('.time-slot-row').off('click').off('dblclick');

        // When a time slot row is clicked, select it for color editing
        $('.time-slot-row').on('click', (e) => {
            // Don't trigger if clicking on input fields or their children
            if ($(e.target).is('input') || 
                $(e.target).is('textarea') || 
                $(e.target).hasClass('form-control') ||
                $(e.target).closest('input, textarea, .form-control').length > 0) {
                return;
            }
            
            const $row = $(e.currentTarget);
            
            // Don't allow selection of past time slots
            if ($row.hasClass('past-time-slot')) {
                this.showMessage('Past time slots cannot be edited', 'error');
                return;
            }
            
            this.selectModalTimeSlot($row);
        });

        // Add color change on double-click for quick editing
        $('.time-slot-row').on('dblclick', (e) => {
            // Don't trigger if clicking on input fields
            if ($(e.target).is('input') || 
                $(e.target).is('textarea') || 
                $(e.target).hasClass('form-control') ||
                $(e.target).closest('input, textarea, .form-control').length > 0) {
                return;
            }
            
            const $row = $(e.currentTarget);
            
            // Don't allow editing of past time slots
            if ($row.hasClass('past-time-slot')) {
                this.showMessage('Past time slots cannot be edited', 'error');
                return;
            }
            
            this.quickToggleTimeSlotColor($row);
        });

        // Make sure input fields are fully functional (only for non-past slots)
        $('.time-slot-description, .time-slot-number').off('focus').on('focus', function(e) {
            e.stopPropagation();
            const $row = $(this).closest('.time-slot-row');
            if (!$row.hasClass('past-time-slot')) {
                $row.removeClass('selected');
            }
        });

        $('.time-slot-description, .time-slot-number').off('click').on('click', function(e) {
            e.stopPropagation();
        });

        $('.time-slot-description, .time-slot-number').off('input').on('input', function(e) {
            e.stopPropagation();
        });
    }

 quickToggleTimeSlotColor($row) {
        // Don't allow editing of past time slots
        if ($row.hasClass('past-time-slot')) {
            this.showMessage('Past time slots cannot be edited', 'error');
            return;
        }
        
        const currentColor = $row.hasClass('color-red') ? 'red' : 
                           $row.hasClass('color-green') ? 'green' : null;
        
        let newColor;
        if (!currentColor || currentColor === 'red') {
            newColor = 'green';
        } else {
            newColor = 'red';
        }
        
        this.applyColorToTimeSlot($row, newColor);
        
        // Update the time color selection in modal
        this.selectModalTimeColor(newColor);
    }

    applyColorToTimeSlot($row, color) {
        // Remove existing color classes
        $row.removeClass('color-red color-green');
        
        // Add new color class
        $row.addClass(`color-${color}`);
        
        // Update color indicator
        $row.find('.time-slot-color-indicator')
            .removeClass('color-red color-green')
            .addClass(`color-${color}`);
        
        // Update slot number based on color status
        const originalSlot = $row.data('time-slot');
        const $slotNumberInput = $row.find('.time-slot-number');
        
        if (color) {
            // If color is being set, ensure slot number is not 0
            const currentValue = parseInt($slotNumberInput.val());
            if (currentValue === 0) {
                $slotNumberInput.val(originalSlot);
            }
        } else {
            // If color is being removed, set slot number to 0
            $slotNumberInput.val(0);
        }
    }

    async loadModalData(date) {
        try {
            console.log('Loading modal data for date:', date);
            
            // Load date-level data
            const response = await $.get(`/calendar/date-data?date=${date}`);
            console.log('Modal data response:', response);
            
            if (response.status === 'success') {
                const data = response.data;
                
                // Set date color and description
                if (data.date_color) {
                    this.selectModalDateColor(data.date_color);
                    $('#dateDescriptionInput').val(data.date_description || '');
                }
                
                // Store time slot data for later use
                this.existingTimeSlotData = data.time_slots || {};
                console.log('Existing time slot data:', this.existingTimeSlotData);
            }
        } catch (error) {
            console.error('Error loading modal data:', error);
            this.existingTimeSlotData = {};
        }
    }

    loadTimeSlotData() {
        if (!this.existingTimeSlotData) return;
        
        Object.entries(this.existingTimeSlotData).forEach(([slot, data]) => {
            const $row = $(`.time-slot-row[data-time-slot="${slot}"]`);
            if ($row.length) {
                if (data.color) {
                    $row.addClass(`color-${data.color}`);
                    $row.find('.time-slot-color-indicator').addClass(`color-${data.color}`);
                    // Set slot number to the actual slot (not 0)
                    $row.find('.time-slot-number').val(slot);
                }
                if (data.description) {
                    $row.find('.time-slot-description').val(data.description);
                }
            }
        });
    }


    async saveModalChanges() {
        if (!this.modalSelectedDate) return;
        
        try {
            // Show loading state
            $('#saveModalChanges').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            
            // Prepare data for saving
            const saveData = {
                date: this.modalSelectedDate,
                date_color: this.modalDateColor,
                date_description: $('#dateDescriptionInput').val().trim(),
                time_slots: {}
            };
            
            console.log('Date-level data:', {
                date_color: saveData.date_color,
                date_description: saveData.date_description
            });
            
            // Collect time slot data
            let timeSlotsCount = 0;
            $('.time-slot-row').each((index, row) => {
                const $row = $(row);
                const originalSlot = $row.data('time-slot'); // Original slot from data attribute
                const editedSlot = parseInt($row.find('.time-slot-number').val()); // Editable slot number from input
                const color = $row.hasClass('color-red') ? 'red' : 
                             $row.hasClass('color-green') ? 'green' : null;
                const description = $row.find('.time-slot-description').val().trim();
                
                // Only save slots that have a color
                if (color) {
                    // Use the edited slot number if it's valid and not 0
                    // Otherwise use the original slot number
                    let finalSlot;
                    if (!isNaN(editedSlot) && editedSlot > 0 && editedSlot <= 24) {
                        finalSlot = editedSlot;
                    } else {
                        finalSlot = originalSlot;
                    }
                    
                    saveData.time_slots[finalSlot] = {
                        color: color,
                        description: description
                    };
                    timeSlotsCount++;
                    
                    console.log(`Time slot ${originalSlot} -> ${finalSlot}:`, {
                        color: color,
                        description: description
                    });
                }
            });
            
            console.log('Saving data:', {
                date: saveData.date,
                date_color: saveData.date_color,
                time_slots_count: timeSlotsCount,
                time_slots: saveData.time_slots
            });
            
            // Validate that we have at least date color or time slots
            if (!saveData.date_color && Object.keys(saveData.time_slots).length === 0) {
                this.showMessage('Please select at least a date color or time slot colors', 'error');
                $('#saveModalChanges').prop('disabled', false).html('Save Changes');
                return;
            }
            
            // Save data
            const response = await $.ajax({
                url: '/calendar/save-date-data',
                method: 'POST',
                data: {
                    ...saveData,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json'
            });
            
            if (response.status === 'success') {
                this.showMessage('Calendar data saved successfully!', 'success');
                $('#colorSelectionModal').modal('hide');
                
                // Reload the current view to reflect changes
                setTimeout(() => {
                    if (this.currentView === 'month') {
                        this.loadMonthView();
                    } else {
                        this.loadWeekView();
                    }
                }, 500);
                
            } else {
                this.showMessage('Error saving data: ' + response.message, 'error');
            }
            
        } catch (error) {
            console.error('Error saving modal data:', error);
            let errorMessage = 'Error saving data. Please try again.';
            
            if (error.responseJSON && error.responseJSON.message) {
                errorMessage = error.responseJSON.message;
            }
            
            this.showMessage(errorMessage, 'error');
        } finally {
            $('#saveModalChanges').prop('disabled', false).html('Save Changes');
        }
    }

    // MONTH VIEW FUNCTIONS
    
    changeMonth(direction) {
        this.currentDate.setMonth(this.currentDate.getMonth() + direction);
        this.selectedDate = null;
        this.loadMonthView();
    }
    
    async loadMonthView() {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        
        // Update header
        $('#currentMonthYear').text(this.currentDate.toLocaleString('default', { 
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
        const totalCells = 42; // 6 weeks
        const remainingCells = totalCells - (startDay + daysInMonth);
        for (let day = 1; day <= remainingCells; day++) {
            const date = new Date(year, month + 1, day);
            html += this.createDayCell(day, date, true);
        }
        
        $('#monthGrid').html(html);

        // Force-correct month membership
        $('.day-cell').each((i, cell) => {
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

        // Load month colors
        await this.loadMonthColors();

        // Click handler - Only allow clicks on non-past dates
        $('.day-cell:not(.past-date):not(.other-month)').off('click').on('click', (e) => {
            const date = $(e.currentTarget).data('date');
            if (date) {
                this.openModalForDate(date);
            }
        });
        
        // Add specific styling for past dates
        this.stylePastDates();
    }
        stylePastDates() {
        $('.day-cell.past-date').each(function() {
            const $cell = $(this);
            
            // Remove hover effects and cursor pointer
            $cell.css({
                'cursor': 'not-allowed',
                'opacity': '0.6'
            });
            
            // Remove any existing hover event handlers
            $cell.off('mouseenter mouseleave');
            
            // Add a visual indicator that it's disabled
            if (!$cell.find('.past-date-indicator').length) {
                $cell.append('<div class="past-date-indicator" title="Past dates cannot be edited"></div>');
            }
        });
    }
    
  createDayCell(day, date, isOtherMonth) {
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');
        const dateStr = `${yyyy}-${mm}-${dd}`;
        
        // Get today's date (without time)
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        // Create date for comparison (without time)
        const cellDate = new Date(yyyy, mm - 1, dd);
        cellDate.setHours(0, 0, 0, 0);
        
        // Check if the date is in the past (excluding today)
        const isPastDate = cellDate < today;
        
        // Check if the date is today
        const isToday = cellDate.getTime() === today.getTime();
        
        // Add appropriate classes
        let additionalClasses = '';
        if (isPastDate) {
            additionalClasses += 'past-date ';
        }
        if (isToday) {
            additionalClasses += 'current-date ';
        }
        
        return `
            <div class="day-cell ${isOtherMonth ? 'other-month' : ''} ${additionalClasses}" 
                data-date="${dateStr}">
                <span>${date.getDate()}</span>
            </div>
        `;
    }

    async loadMonthColors() {
    const month = this.currentDate.toISOString().substring(0, 7);
    
    console.log("🔄 Loading month colors for:", month);

    try {
        const response = await $.get(`/calendar/month/colors?month=${month}`);
        console.log("📦 Month Colors API Response:", response);

        // Clear all colors first
        $('.day-cell')
            .removeClass('has-color color-red color-orange color-green')
            .css('background-color', '')
            .css('color', '')
            .removeAttr('data-description');

        let colors = {};
        
        if (response && typeof response === 'object') {
            if (response.status === "success" && response.data) {
                colors = response.data;
                console.log("✅ Using new API response structure");
            } else if (!response.status && Object.keys(response).length > 0) {
                colors = response;
                console.log("✅ Using direct API response structure");
            } else if (response.data) {
                colors = response.data;
                console.log("✅ Using data property from response");
            }
        }

        console.log("🎨 Colors data to apply:", colors);

        if (!colors || Object.keys(colors).length === 0) {
            console.log("💡 No color records in database for this month");
            return;
        }

        let appliedCount = 0;
        
        // Apply colors from database
        Object.entries(colors).forEach(([date, item]) => {
            // Handle different response structures
            let color, description;
            
            if (typeof item === 'string') {
                // If item is just a color string
                color = item;
                description = '';
            } else if (item && typeof item === 'object') {
                // If item is an object with color and description
                color = item.color; // This matches the backend response structure
                description = item.description || '';
                
                // If no color, skip this date
                if (!color) {
                    console.log(`⚠️ No color for date: ${date}, skipping`);
                    return;
                }
            } else {
                console.warn(`⚠️ Invalid color data for date: ${date}`, item);
                return;
            }
            
            if (!color || String(color).trim() === '') {
                console.warn(`⚠️ Empty color for date: ${date}`);
                return;
            }

            const $cell = $(`.day-cell[data-date="${date}"]`);
            if ($cell.length) {
                console.log(`🎨 Applying ${color} to ${date}`, {description});
                
                // Remove any existing color classes
                $cell.removeClass('color-red color-orange color-green');
                
                // Add the new color class
                $cell.addClass(`has-color color-${color}`);
                
                // Add description as data attribute (even if empty)
                $cell.attr('data-description', description || '');
                
                // Force background color with inline style as backup
                const colorMap = {
                    'red': '#dc2626',
                    'orange': '#ea580c', 
                    'green': '#16a34a'
                };
                
                if (colorMap[color]) {
                    $cell.css('background-color', colorMap[color]);
                    $cell.css('color', 'white');
                }
                
                appliedCount++;
            } else {
                console.warn(`❌ Cell not found for date: ${date}`);
            }
        });

        console.log(`✅ Applied colors to ${appliedCount} dates`);

        // Initialize tooltips after a brief delay to ensure DOM is updated
        setTimeout(() => {
            this.initializeTooltips();
        }, 100);

    } catch (err) {
        console.error("❌ ERROR loading month colors:", err);
        console.error("Error details:", err.responseJSON || err.statusText);
    }
}

    // WEEK VIEW FUNCTIONS
    
    changeWeek(direction) {
        this.currentDate.setDate(this.currentDate.getDate() + (direction * 7));
        this.selectedDate = null;
        this.selectedTime = null;
        
        console.log('Changing week to:', this.formatDate(this.currentDate));
        
        this.loadWeekView();
    }

    async loadWeekView() {
        // Ensure we're starting from the correct date (Sunday of current week)
        const startOfWeek = new Date(this.currentDate);
        startOfWeek.setDate(this.currentDate.getDate() - this.currentDate.getDay());
        
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);
        
        // Update header with proper formatting
        $('#currentWeekRange').text(
            `Week of ${this.formatDateDisplay(startOfWeek)} - ${this.formatDateDisplay(endOfWeek)}`
        );
        
        // Generate week grid with intervals
        let html = '';
        
        // Time header and day headers
        html += '<div class="time-header">Time</div>';
        
        // Day headers with proper date attributes
        const weekDates = [];
        for (let i = 0; i < 7; i++) {
            const day = new Date(startOfWeek);
            day.setDate(startOfWeek.getDate() + i);
            const dateStr = this.formatDate(day);
            weekDates.push(dateStr);
            html += `<div class="day-header" data-date="${dateStr}">${this.formatDateDisplay(day, true)}</div>`;
        }
        
        console.log('Week dates being generated:', weekDates);
        
        // Time slots with proper 1-hour intervals
        this.timeSlots.forEach(timeSlot => {
            // Add time label - show the full interval
            html += `<div class="time-label">${timeSlot.display}</div>`;
            
            // Add time slots for each day
            for (let i = 0; i < 7; i++) {
                const day = new Date(startOfWeek);
                day.setDate(startOfWeek.getDate() + i);
                const dateStr = this.formatDate(day);
                
                html += `
                    <div class="time-slot" 
                         data-date="${dateStr}" 
                         data-time="${timeSlot.display}"
                         data-time-normalized="${timeSlot.display.trim().toUpperCase()}"
                         title="${dateStr} ${timeSlot.display}">
                        <div class="time-slot-content">
                            <span class="time-display">${timeSlot.display}</span>
                        </div>
                    </div>
                `;
            }
        });
        
        $('#weekGrid').html(html);
        
        // Debug: Log all generated time slots and dates
        console.log('Generated week grid details:', {
            dateRange: `${this.formatDate(startOfWeek)} to ${this.formatDate(endOfWeek)}`,
            weekDates: weekDates,
            timeSlots: this.timeSlots.map(ts => ts.display),
            totalSlots: $('.time-slot').length,
            datesWithSlots: Array.from(new Set($('.time-slot').map(function() {
                return $(this).data('date');
            }).get()))
        });
        
        // Load week data - PASS THE CORRECT DATE (start of week)
        await this.loadWeekData(startOfWeek, endOfWeek);
        
        // Add click events to ALL time slots
        $('.time-slot').off('click').on('click', (e) => {
            const $slot = $(e.currentTarget);
            const date = $slot.data('date');
            
            console.log('Week slot clicked:', { date });
            this.openModalForDate(date);
        });
    }

    async loadWeekData(startOfWeek, endOfWeek) {
        try {
            const startDateStr = this.formatDate(startOfWeek);
            console.log('Loading week data for date range:', {
                start: startDateStr,
                end: this.formatDate(endOfWeek),
                currentDate: this.formatDate(this.currentDate)
            });
            
            const response = await $.get('/calendar/week/load-data', {
                date: startDateStr
            });
            console.log('Week data response:', response);
            
            if (response.status === 'success') {
                // Clear existing colors and descriptions first
                $('.time-slot')
                    .removeClass('has-color color-red color-orange color-green')
                    .removeAttr('data-description')
                    .css('background-color', '')
                    .css('color', '');
                
                console.log('Available dates in response:', {
                    monthColors: Object.keys(response.month_colors || {}),
                    weekColors: Object.keys(response.week_colors || {})
                });

                // Apply month colors (red and orange)
                if (response.month_colors) {
                    Object.entries(response.month_colors).forEach(([date, data]) => {
                        const slots = $(`.time-slot[data-date="${date}"]`);
                        console.log(`Applying month color to ${date}:`, data, 'Slots found:', slots.length);
                        
                        if (slots.length > 0 && (data.color === 'red' || data.color === 'orange')) {
                            slots.addClass(`has-color color-${data.color}`);
                            
                            // Force background color
                            const colorMap = {
                                'red': '#dc2626',
                                'orange': '#ea580c', 
                                'green': '#16a34a'
                            };
                            
                            if (colorMap[data.color]) {
                                slots.css('background-color', colorMap[data.color]);
                                slots.css('color', 'white');
                            }
                            
                            // Add month-level description to all time slots for that date
                            slots.each(function() {
                                const $slot = $(this);
                                const existingDesc = $slot.attr('data-description');
                                if (!existingDesc && data.description && data.description.trim() !== '') {
                                    $slot.attr('data-description', data.description.trim());
                                }
                            });
                        } else if (slots.length === 0) {
                            console.warn(`❌ No slots found for month color date: ${date}`);
                        }
                    });
                }
                
                // Apply week colors (green time slots)
                if (response.week_colors) {
                    let appliedCount = 0;
                    let missingCount = 0;
                    
                    Object.entries(response.week_colors).forEach(([date, times]) => {
                        console.log(`Processing week colors for date: ${date}`);
                        console.log(`Available time ranges for ${date}:`, Object.keys(times));
                        
                        Object.entries(times).forEach(([timeRange, data]) => {
                            console.log(`Looking for slot: ${date} at "${timeRange}"`);
                            
                            // Try multiple matching strategies
                            const normalizedTimeRange = timeRange.trim().toUpperCase();
                            
                            // Strategy 1: Direct match
                            let slot = $(`.time-slot[data-date="${date}"][data-time="${timeRange}"]`);
                            
                            // Strategy 2: Normalized match
                            if (!slot.length) {
                                slot = $(`.time-slot[data-date="${date}"][data-time-normalized="${normalizedTimeRange}"]`);
                            }
                            
                            // Strategy 3: Case-insensitive partial matching
                            if (!slot.length) {
                                $(`.time-slot[data-date="${date}"]`).each(function() {
                                    const slotTime = $(this).data('time');
                                    const slotNormalized = $(this).data('time-normalized');
                                    if (slotNormalized === normalizedTimeRange) {
                                        slot = $(this);
                                        return false; // break the loop
                                    }
                                });
                            }
                            
                            if (slot.length) {
                                console.log(`✅ Found and coloring slot for ${date} at ${timeRange}`, {
                                    slotTime: slot.data('time'),
                                    slotDate: slot.data('date'),
                                    color: data.color,
                                    slotElement: slot[0]
                                });
                                
                                // Only apply green color (time slots can override month colors)
                                if (data.color === 'green') {
                                    slot.removeClass('has-color color-red color-orange color-green');
                                    slot.addClass(`has-color color-${data.color}`);
                                    
                                    // Force background color
                                    slot.css('background-color', '#16a34a');
                                    slot.css('color', 'white');
                                    
                                    // Add/update description
                                    if (data.description && data.description.trim() !== '') {
                                        slot.attr('data-description', data.description.trim());
                                        console.log(`Set description: "${data.description.trim()}"`);
                                    } else {
                                        slot.removeAttr('data-description');
                                    }
                                }
                                
                                appliedCount++;
                            } else {
                                console.warn(`❌ Slot not found for ${date} at "${timeRange}"`);
                                missingCount++;
                            }
                        });
                    });
                    
                    console.log(`Week colors application summary: ${appliedCount} applied, ${missingCount} missing`);
                }

                // Final debug information
                const totalSlots = $('.time-slot').length;
                const coloredSlots = $('.time-slot.has-color').length;
                
                console.log('Week view final state:', {
                    'Total time slots': totalSlots,
                    'Colored time slots': coloredSlots,
                    'Green slots': $('.time-slot.color-green').length,
                    'Red slots': $('.time-slot.color-red').length,
                    'Orange slots': $('.time-slot.color-orange').length,
                    'All dates in grid': Array.from(new Set($('.time-slot').map(function() {
                        return $(this).data('date');
                    }).get()))
                });

                // Re-initialize tooltips after loading data
                setTimeout(() => {
                    this.initializeTooltips();
                }, 100);
            }
        } catch (error) {
            console.error('Error loading week data:', error);
            this.showMessage('Error loading week data: ' + error.message, 'error');
        }
    }

    // TOOLTIP FUNCTIONALITY
    initializeTooltips() {
        console.log('Initializing tooltips...');
        
        // Remove existing tooltip event listeners
        $('.day-cell, .time-slot').off('mouseenter mouseleave mousemove');
        
        // Store reference to 'this' for use in event handlers
        const self = this;
        
        // Add tooltip functionality to day cells and time slots
        $('.day-cell, .time-slot').on('mouseenter', function(e) {
            const $element = $(this);
            let description = $element.attr('data-description');
            
            // If no description, show "Not set yet"
            if (!description || description.trim() === '') {
                description = 'Not set yet';
            }
            
            // Create tooltip element
            const tooltip = $('<div class="calendar-tooltip"></div>')
                .html(self.formatTooltipContent(description, $element))
                .appendTo('body');
            
            // Position tooltip
            self.positionTooltip($element, tooltip, e);
            
            $element.data('tooltip', tooltip);
            
        }).on('mouseleave', function() {
            const tooltip = $(this).data('tooltip');
            if (tooltip) {
                tooltip.remove();
                $(this).removeData('tooltip');
            }
        }).on('mousemove', function(e) {
            const tooltip = $(this).data('tooltip');
            if (tooltip) {
                const $element = $(this);
                self.positionTooltip($element, tooltip, e);
            }
        });
    }

    formatTooltipContent(description, $element) {
        const date = $element.data('date');
        const time = $element.data('time');
        
        // Format date for display
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
        `;
        
        if (time) {
            html += `<div class="tooltip-time"><i class="fas fa-clock"></i> ${time}</div>`;
        }
        
        html += `</div>`;
        
        // Add description section
        html += `
            <div class="tooltip-description">
                <div class="tooltip-label">
                    <i class="fas fa-sticky-note"></i>
                    Description:
                </div>
                <div class="tooltip-text">${this.escapeHtml(description)}</div>
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
        
        // Calculate positions for above and below
        const aboveTop = elementRect.top + scrollTop - tooltipHeight - 8;
        const belowTop = elementRect.top + scrollTop + elementRect.height + 8;
        
        // Default to positioning above
        let top = aboveTop;
        let positionClass = 'above';
        
        // If above position would go off-screen, position below
        if (aboveTop < viewportPadding) {
            top = belowTop;
            positionClass = 'below';
        }
        
        // Calculate horizontal position (centered relative to element)
        let left = elementRect.left + scrollLeft + (elementRect.width / 2) - (tooltipWidth / 2);
        
        // Adjust if tooltip would go off-screen horizontally
        if (left < viewportPadding) {
            left = viewportPadding;
        } else if (left + tooltipWidth > window.innerWidth - viewportPadding) {
            left = window.innerWidth - tooltipWidth - viewportPadding;
        }
        
        // Apply positioning
        tooltip.css({
            top: top + 'px',
            left: left + 'px'
        }).removeClass('above below').addClass(positionClass + ' show');
    }

    switchView(view) {
        this.currentView = view;
        this.selectedDate = null;
        this.selectedTime = null;
        
        // Update tabs
        $('.view-tab').removeClass('active');
        $(`.view-tab[data-view="${view}"]`).addClass('active');
        
        // Show/hide views
        $('.view-pane').hide();
        $(`#${view}View`).show();
        
        // Update selection details
        this.updateSelectionDetails();
        
        // Load appropriate view
        if (view === 'month') {
            this.loadMonthView();
        } else {
            this.loadWeekView();
        }
    }

    updateSelectionDetails() {
        // This method is kept for compatibility but may not be needed with modal approach
        console.log('Selection updated - current view:', this.currentView);
    }

    showMessage(message, type) {
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
    
    formatDateDisplay(date, includeWeekday = false) {
        if (includeWeekday) {
            return date.toLocaleDateString('en-US', { 
                weekday: 'short', 
                month: 'short', 
                day: 'numeric' 
            });
        }
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }
}

// Initialize calendar when document is ready
$(document).ready(function() {
    const calendar = new CalendarManager();
    
    // Sidebar toggle functionality
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

    console.log('Calendar manager initialized');
    console.log('CSRF Token:', $('meta[name="csrf-token"]').attr('content'));
    
    // Debug functions - can be called from browser console
    window.calendarManager = calendar;
});