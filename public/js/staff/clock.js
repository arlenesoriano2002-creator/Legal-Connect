function updateClock() {
    const now = new Date();

    let hours = now.getHours();
    let minutes = now.getMinutes();
    let seconds = now.getSeconds();
    let ampm = hours >= 12 ? "PM" : "AM";

    hours = hours % 12 || 12;
    minutes = minutes.toString().padStart(2, "0");
    seconds = seconds.toString().padStart(2, "0");

    document.getElementById("time").innerText =
        `${hours}:${minutes}:${seconds} ${ampm}`;

    const options = {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric"
    };

    document.getElementById("date").innerText =
        now.toLocaleDateString("en-US", options).toUpperCase();
}

setInterval(updateClock, 1000);
updateClock();

// Form submission
$(document).ready(function() {
    // Set up CSRF token for AJAX - CRITICAL FIX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Load purposes from database
    $.ajax({
        url: '/staff/walkins/logbook/purposes',
        method: 'GET',
        success: function(purposes) {
            $('#purposeSelect').html('<option value="" selected disabled>Select purpose of visit</option>');
            $.each(purposes, function(index, purpose) {
                $('#purposeSelect').append('<option value="' + purpose.purpose + '">' + purpose.purpose + '</option>');
            });
        },
        error: function() {
            $('#purposeSelect').html('<option value="" selected disabled>Error loading purposes</option>');
        }
    });

    // Handle contact number input - only allow numbers and max 11 digits
    $('input[name="contact_number"]').on('input', function() {
        let value = $(this).val();
        
        // Remove all non-numeric characters
        value = value.replace(/\D/g, '');
        
        // Limit to 11 digits
        if (value.length > 11) {
            value = value.substring(0, 11);
        }
        
        // Set the cleaned value back (no formatting)
        $(this).val(value);
        
        // Show/hide validation message
        const errorSpan = $('#contact-error');
        
        if (value.length > 0 && value.length !== 11) {
            errorSpan.text('Contact number must be exactly 11 digits.').show();
        } else {
            errorSpan.hide();
        }
    });

    // Handle form submission - UPDATED WITH CSRF FIX
    $('#walkinForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get the form element
        const form = $(this);
        
        // Get the store route from the form's action attribute
        const storeRoute = form.attr('action');
        
        // Get raw contact number (already numeric only from input handler)
        const contactNumber = $('input[name="contact_number"]').val();
        
        // Validate contact number
        if (contactNumber.length !== 11) {
            toastr.error('Contact number must be exactly 11 digits.', 'Validation Error!');
            return false;
        }
        
        // Check if contact number contains only digits
        if (!/^\d+$/.test(contactNumber)) {
            toastr.error('Contact number must contain only numbers.', 'Validation Error!');
            return false;
        }
        
        // Check if purpose is selected
        const purpose = $('select[name="purpose"]').val();
        if (!purpose) {
            toastr.error('Please select a purpose of visit.', 'Validation Error!');
            return false;
        }
        
        // Disable submit button to prevent double submission
        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true).text('SUBMITTING...');
        
        // Prepare form data with cleaned contact number
        const formData = {
            fullname: $('input[name="fullname"]').val(),
            contact_number: contactNumber,
            address: $('input[name="address"]').val(),
            purpose: purpose,
            client_datetime: new Date().toISOString() // Add current datetime
        };
        
        // Send AJAX request with CSRF token in headers
        $.ajax({
            url: storeRoute,
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message with the recorded time
                    const recordedTime = response.recorded_time || new Date().toLocaleString();
                    
                    toastr.success(`Walk-in entry submitted successfully at ${recordedTime}!`, 'Success!', {
                        timeOut: 3000,
                        progressBar: true,
                        closeButton: true
                    });
                    
                    // Reset form
                    $('#walkinForm')[0].reset();
                    
                    // Reset validation
                    $('#contact-error').hide();
                    
                    // Reset purpose dropdown to default
                    $('#purposeSelect').val('');
                } else {
                    toastr.error(response.message, 'Error!');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred. Please try again.';
                
                // Handle CSRF token mismatch (419 error)
                if (xhr.status === 419) {
                    errorMessage = 'Session expired. Please refresh the page and try again.';
                    // Optionally reload the page to get new CSRF token
                    setTimeout(() => location.reload(), 2000);
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 422) {
                    // Laravel validation errors
                    const errors = xhr.responseJSON.errors;
                    if (errors) {
                        errorMessage = Object.values(errors)[0][0];
                    }
                }
                
                toastr.error(errorMessage, 'Error!');
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).text('SUBMIT ENTRY');
            }
        });
    });

    // Show server-side flash messages (success/error) if present
    (function() {
        const serverSuccessEl = document.getElementById('server-success');
        const serverErrorEl = document.getElementById('server-error');

        if (serverSuccessEl) {
            const msg = serverSuccessEl.getAttribute('data-message') || 'Saved successfully.';
            if (window.toastr) {
                toastr.success(msg, 'Success', { timeOut: 3000, progressBar: true, closeButton: true });
            } else {
                // Fallback banner
                const banner = document.createElement('div');
                banner.className = 'server-toast success';
                banner.innerText = msg;
                document.body.appendChild(banner);
                setTimeout(() => banner.classList.add('show'), 50);
                setTimeout(() => banner.classList.remove('show'), 3500);
                setTimeout(() => banner.remove(), 4000);
            }
        }

        if (serverErrorEl) {
            const msg = serverErrorEl.getAttribute('data-message') || 'An error occurred.';
            if (window.toastr) {
                toastr.error(msg, 'Error', { timeOut: 4000, progressBar: true, closeButton: true });
            } else {
                const banner = document.createElement('div');
                banner.className = 'server-toast error';
                banner.innerText = msg;
                document.body.appendChild(banner);
                setTimeout(() => banner.classList.add('show'), 50);
                setTimeout(() => banner.classList.remove('show'), 4500);
                setTimeout(() => banner.remove(), 5000);
            }
        }
    })();

    // Add paste event handler to clean non-numeric characters
    $('input[name="contact_number"]').on('paste', function(e) {
        e.preventDefault();
        
        // Get pasted text
        const pastedText = (e.originalEvent || e).clipboardData.getData('text/plain');
        
        // Remove all non-numeric characters
        const numericOnly = pastedText.replace(/\D/g, '');
        
        // Limit to 11 digits
        const limitedNumeric = numericOnly.substring(0, 11);
        
        // Insert the cleaned text at cursor position
        const start = this.selectionStart;
        const end = this.selectionEnd;
        const currentValue = $(this).val();
        
        const newValue = currentValue.substring(0, start) + limitedNumeric + currentValue.substring(end);
        $(this).val(newValue);
        
        // Trigger input event to update validation
        $(this).trigger('input');
    });
    
    // Prevent non-numeric keypress
    $('input[name="contact_number"]').on('keypress', function(e) {
        const charCode = e.which ? e.which : e.keyCode;
        
        // Allow backspace, delete, tab, escape, enter
        if (charCode === 8 || charCode === 46 || charCode === 9 || charCode === 27 || charCode === 13) {
            return;
        }
        
        // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
        if ((e.ctrlKey || e.metaKey) && (charCode === 65 || charCode === 67 || charCode === 86 || charCode === 88)) {
            return;
        }
        
        // Allow only numbers (0-9)
        if (charCode < 48 || charCode > 57) {
            e.preventDefault();
            return false;
        }
        
        // Get current value
        const currentValue = $(this).val();
        
        // If already 11 digits, prevent more input
        if (currentValue.length >= 11) {
            e.preventDefault();
            return false;
        }
    });
    
    // Alternative: Use jQuery serialize() method (simpler approach)
    /*
    $('#walkinForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#submitBtn');
        
        // Validate contact number
        const contactNumber = $('input[name="contact_number"]').val();
        if (contactNumber.length !== 11 || !/^\d+$/.test(contactNumber)) {
            toastr.error('Contact number must be exactly 11 digits (numbers only).', 'Validation Error!');
            return false;
        }
        
        submitBtn.prop('disabled', true).text('SUBMITTING...');
        
        // Use serialize() which automatically includes all form fields including CSRF token
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize() + '&client_datetime=' + new Date().toISOString(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success('Walk-in entry submitted successfully!', 'Success!');
                    form[0].reset();
                    $('#contact-error').hide();
                } else {
                    toastr.error(response.message, 'Error!');
                }
            },
            error: function(xhr) {
                if (xhr.status === 419) {
                    toastr.error('Session expired. Please refresh the page.', 'Error!');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    toastr.error('An error occurred. Please try again.', 'Error!');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('SUBMIT ENTRY');
            }
        });
    });
    */
});