        // Staff Calendar Manager
class StaffCalendarManager {
    static MAX_SLOT_CAPACITY = 4;

    constructor() {
        this.currentDate = new Date();
        this.selectedDate = null;
        this.timeSlots = this.generateTimeIntervals();
        this.modalDate = null;
        this.modalDateColor = null;
        this.timeSlotData = {};
        
        this.init();
    }
    
    init() {
        this.loadMonthView();
        this.bindEvents();
        this.setupModal();
    }
    
    bindEvents() {
        // Month Navigation
        $('#staffPrevMonth').click(() => this.changeMonth(-1));
        $('#staffNextMonth').click(() => this.changeMonth(1));
        
        // Refresh Button
        $('#refreshStaffCalendar').click(() => this.refreshCalendar());
        
        // Date clicks
        $(document).on('click', '.staff-day-cell:not(.staff-past-date)', (e) => {
            const date = $(e.currentTarget).data('date');
            if (date) this.openModal(date);
        });
    }
    
    setupModal() {
        // Date color selection
        $('.modal-color-option[data-color]').off('click').click((e) => {
            const color = $(e.currentTarget).data('color');
            this.selectDateColor(color);
        });

        $('#staffSetAllAvailability').off('change').on('change', (e) => {
            this.applyAvailabilityToAllSlots($(e.target).val());
        });
        
        // Time slot availability change
        $(document).on('change', '.staff-slot-availability', (e) => {
            const $select = $(e.target);
            const slotId = $select.data('slot');
            const availability = $select.val();
            
            this.timeSlotData[slotId] = this.timeSlotData[slotId] || {};
            this.timeSlotData[slotId].color = availability;
            
            const $row = $select.closest('tr');
            this.updateSlotRowAppearance($row, availability);
        });
        
        // Slot number input
        $(document).on('input', '.staff-slot-number', (e) => {
            const $input = $(e.target);
            const slotId = $input.data('slot');
            const rawValue = parseInt($input.val(), 10);
            const value = Number.isNaN(rawValue)
                ? 0
                : Math.max(0, Math.min(StaffCalendarManager.MAX_SLOT_CAPACITY, rawValue));

            $input.val(value);
            
            this.timeSlotData[slotId] = this.timeSlotData[slotId] || {};
            this.timeSlotData[slotId].slot_number = value;
        });
        
        // Slot description input
        $(document).on('input', '.staff-slot-description', (e) => {
            const $input = $(e.target);
            const slotId = $input.data('slot');
            
            this.timeSlotData[slotId] = this.timeSlotData[slotId] || {};
            this.timeSlotData[slotId].description = $input.val();
        });
        
        // Save button
        $('#saveStaffModalChanges').click(() => this.saveModalData());
    }

    updateSlotRowAppearance($row, availability) {
        $row.removeClass('table-success table-danger');

        if (availability === 'green') {
            $row.addClass('table-success');
        } else if (availability === 'red') {
            $row.addClass('table-danger');
        }
    }

    applyAvailabilityToAllSlots(availability) {
        if (!availability) {
            return;
        }

        const slotCapacity = availability === 'green'
            ? StaffCalendarManager.MAX_SLOT_CAPACITY
            : 0;

        $('.staff-time-slot-row').each((index, row) => {
            const $row = $(row);
            const $numberInput = $row.find('.staff-slot-number');
            const $availabilitySelect = $row.find('.staff-slot-availability');
            const slotId = $availabilitySelect.data('slot');

            if ($numberInput.prop('disabled') || $availabilitySelect.prop('disabled')) {
                return;
            }

            $numberInput.val(slotCapacity);
            $availabilitySelect.val(availability);
            this.updateSlotRowAppearance($row, availability);

            this.timeSlotData[slotId] = this.timeSlotData[slotId] || {};
            this.timeSlotData[slotId].slot_number = slotCapacity;
            this.timeSlotData[slotId].color = availability;
        });
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
            const timeSlot = hour - 7; // 8:00 AM = slot 1
            
            intervals.push({
                id: timeSlot,
                display: intervalString,
                slot: timeSlot
            });
        }
        
        return intervals;
    }
    
    changeMonth(direction) {
        this.currentDate.setMonth(this.currentDate.getMonth() + direction);
        this.loadMonthView();
    }
    
    async loadMonthView() {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        
        // Update header
        $('#staffCurrentMonthYear').text(
            this.currentDate.toLocaleString('default', { 
                month: 'long', 
                year: 'numeric' 
            })
        );
        
        // Generate grid
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
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const isToday = date.getTime() === today.getTime();
            const isPast = date < today;
            html += this.createDayCell(day, date, false, isToday, isPast);
        }
        
        // Next month days
        const totalCells = 42;
        const remainingCells = totalCells - (startDay + daysInMonth);
        for (let day = 1; day <= remainingCells; day++) {
            const date = new Date(year, month + 1, day);
            html += this.createDayCell(day, date, true);
        }
        
        $('#staffMonthGrid').html(html);
        
        // Load colors from server
        await this.loadMonthColors();
    }
    
    createDayCell(day, date, isOtherMonth, isToday = false, isPast = false) {
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(day).padStart(2, '0');
        const dateStr = `${yyyy}-${mm}-${dd}`;
        
        let classes = 'staff-day-cell';
        if (isOtherMonth) {
            classes += ' staff-other-month staff-past-date';
        }
        if (isToday) classes += ' staff-today';
        if (isPast) classes += ' staff-past-date';
        
        return `
            <div class="${classes}" data-date="${dateStr}">
                <div class="day-number">${day}</div>
                <div class="staff-status-indicator" id="staff-status-${dateStr}"></div>
            </div>
        `;
    }
    
    async loadMonthColors() {
        const month = this.currentDate.toISOString().substring(0, 7);
        
        try {
            const response = await $.ajax({
                url: '/staff/calendar/month/colors',
                method: 'GET',
                data: { month: month },
                dataType: 'json'
            });
            
            if (response.status === 'success') {
                Object.entries(response.data).forEach(([date, data]) => {
                    const $cell = $(`.staff-day-cell[data-date="${date}"]`);
                    const $status = $(`#staff-status-${date}`);
                    
                    if (data.color) {
                        $cell.addClass(`color-${data.color}`);
                        $status.addClass(data.color);
                        
                        // Add tooltip with description
                        if (data.description) {
                            $cell.attr('title', data.description);
                            $cell.attr('data-bs-toggle', 'tooltip');
                        }
                    }
                });
                
                // Initialize tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        } catch (error) {
            console.error('Error loading month colors:', error);
            this.showMessage('Error loading calendar data', 'error');
        }
    }
    
    selectDateColor(color) {
        this.modalDateColor = color;
        
        // Update UI
        $('#staffColorSelectionModal .modal-color-option').removeClass('selected');
        $(`#staffColorSelectionModal .modal-color-option[data-color="${color}"]`).addClass('selected');
    }
    
    async openModal(date) {

            const selectedDate = new Date(date);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (selectedDate < today) {
                return; // 🚫 Do NOT open modal for past dates
            }
        this.modalDate = date;
        this.timeSlotData = {};
        
        // Format date for display
        const [year, month, day] = date.split('-');
        const dateObj = new Date(year, month - 1, day);
        const formattedDate = dateObj.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        $('#staffModalDateDisplay').text(formattedDate);
        
        // Reset modal
        $('#staffColorSelectionModal .modal-color-option').removeClass('selected');
        $('#staffDateDescriptionInput').val('');
        $('#staffSetAllAvailability').val('');
        this.modalDateColor = null;
        
        // Load existing data
        await this.loadModalData(date);
        
        // Populate time slots table
        this.populateTimeSlotsTable();
        
        // Show modal
        new bootstrap.Modal(document.getElementById('staffColorSelectionModal')).show();
    }
    
    async loadModalData(date) {
        try {
            const response = await $.ajax({
                url: '/staff/calendar/date-data',
                method: 'GET',
                data: { date: date },
                dataType: 'json'
            });
            
            if (response.status === 'success') {
                const data = response.data;
                
                // Date-level data
                if (data.date_color) {
                    this.selectDateColor(data.date_color);
                    $('#staffDateDescriptionInput').val(data.date_description || '');
                }
                
                // Time slot data
                if (data.time_slots) {
                    this.timeSlotData = data.time_slots;
                }
            }
        } catch (error) {
            console.error('Error loading modal data:', error);
            this.showMessage('Error loading date data', 'error');
        }
    }
    
    populateTimeSlotsTable() {
        const tableBody = $('#staffTimeSlotsTableBody');
        tableBody.empty();
        
        const now = new Date();
        const todayStr = this.formatDate(now);
        const isToday = this.modalDate === todayStr;
        
        this.timeSlots.forEach(slot => {
            const existingData = this.timeSlotData[slot.id] || {};
            const isPastSlot = isToday && this.isPastTimeSlot(slot.display);
            
            const row = `
                <tr class="staff-time-slot-row ${isPastSlot ? 'past-time-slot' : ''}">
                    <td>${slot.display}</td>
                    <td>
                        <input type="number" 
                            class="form-control form-control-sm staff-slot-number" 
                            data-slot="${slot.id}"
                            value="${existingData.slot_number ?? 0}"
                            min="0" 
                            max="${StaffCalendarManager.MAX_SLOT_CAPACITY}"
                            ${isPastSlot ? 'disabled' : ''}>

                    </td>
                    <td>
                        <input type="text" 
                               class="form-control form-control-sm staff-slot-description" 
                               data-slot="${slot.id}"
                               value="${existingData.description || ''}"
                               placeholder="Optional description"
                               ${isPastSlot ? 'disabled' : ''}>
                    </td>
                    <td>
                        <select class="form-select form-select-sm staff-slot-availability" 
                                data-slot="${slot.id}"
                                ${isPastSlot ? 'disabled' : ''}>
                            <option value="" style="color:black">Select</option>
                            <option value="green" style="color:black" ${existingData.color === 'green' ? 'selected' : ''}>
                                Available
                            </option>
                            <option value="red" style="color:black" ${existingData.color === 'red' ? 'selected' : ''}>
                                Not Available
                            </option>
                        </select>
                    </td>
                </tr>
            `;
            
            tableBody.append(row);
            
            // Apply existing color to row
            if (existingData.color) {
                const $row = tableBody.find(`tr:last-child`);
                $row.addClass(`table-${existingData.color === 'green' ? 'success' : 'danger'}`);
            }
        });
    }
    
    isPastTimeSlot(timeString) {
        const now = new Date();
        const [startTime] = timeString.split(' - ');
        const [time, period] = startTime.split(' ');
        const [hours, minutes] = time.split(':').map(Number);
        
        let hour24 = hours;
        if (period === 'PM' && hour24 !== 12) hour24 += 12;
        if (period === 'AM' && hour24 === 12) hour24 = 0;
        
        const slotTime = new Date(now.getFullYear(), now.getMonth(), now.getDate(), hour24, minutes);
        return slotTime < now;
    }
    
    async saveModalData() {
        if (!this.modalDate) return;
        
        try {
            // Show loading state
            $('#saveStaffModalChanges').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            
            // Prepare data
            const saveData = {
                date: this.modalDate,
                date_color: this.modalDateColor,
                date_description: $('#staffDateDescriptionInput').val().trim(),
                time_slots: []
            };
            
            // Collect time slot data
            $('.staff-time-slot-row').each((index, row) => {
                const $row = $(row);
                const slotId = $row.find('.staff-slot-number').data('slot');
                const rawSlotNumber = parseInt($row.find('.staff-slot-number').val(), 10);
                const slotNumber = Number.isNaN(rawSlotNumber)
                    ? 0
                    : Math.max(0, Math.min(StaffCalendarManager.MAX_SLOT_CAPACITY, rawSlotNumber));
                const availability = $row.find('.staff-slot-availability').val();
                const description = $row.find('.staff-slot-description').val().trim();
                
                if (availability) {
                    saveData.time_slots.push({
                        time_slot: slotId,
                        slot_number: slotNumber,
                        color: availability,
                        description: description || null
                    });
                }
            });
            
            // Save to server
            const response = await $.ajax({
                url: '/staff/calendar/save-date-data',
                method: 'POST',
                data: {
                    ...saveData,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json'
            });
            
            if (response.status === 'success') {
                this.showMessage('Calendar data saved successfully!', 'success');
                $('#staffColorSelectionModal').modal('hide');
                this.loadMonthView(); // Refresh calendar
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
            $('#saveStaffModalChanges').prop('disabled', false).html('Save Changes');
        }
    }
    
    refreshCalendar() {
        const refreshButton = $('#refreshStaffCalendar');
        const icon = refreshButton.find('i');
        
        // Add spinning animation
        icon.addClass('fa-spin');
        refreshButton.prop('disabled', true);
        
        this.loadMonthView();
        
        // Remove spinning animation after delay
        setTimeout(() => {
            icon.removeClass('fa-spin');
            refreshButton.prop('disabled', false);
            this.showMessage('Calendar refreshed', 'info');
        }, 1000);
    }
    
    showMessage(message, type) {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'info': 'alert-info',
            'warning': 'alert-warning'
        }[type] || 'alert-info';
        
        const messageDiv = $(`
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('#staffMessageContainer').html(messageDiv);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            messageDiv.alert('close');
        }, 3000);
    }
    
    formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
}

// Initialize staff calendar when page loads
$(document).ready(function() {
    // Check if we're on the staff calendar page
    if ($('#staffMonthView').length) {
        window.staffCalendar = new StaffCalendarManager();
    }
});
