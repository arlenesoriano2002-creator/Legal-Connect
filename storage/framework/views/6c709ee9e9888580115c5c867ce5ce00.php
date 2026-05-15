<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Digital Logbook</title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/staff/index.css">
    <!-- Toastr for notifications -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>
<body>

<!-- HEADER -->
<header class="logbook-header">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <div class="logo-box">
                <i class="fas fa-scale-balanced"></i>
            </div>
            <div class="ms-3">
                <h1>Legal Connect: Digital Logbook</h1>
                <p>A Digital Logbook of Legal Connect</p>
            </div>
        </div>
        <div class="header-dots">
            <span></span><span></span><span></span>
        </div>
    </div>
</header>

<!-- MAIN CONTENT -->
<section class="logbook-container">
    <h2>New Entry</h2>
    <p class="subtitle">Please fill in all the required information below</p>

    <div class="form-card">
        <!-- Update the form tag to add a data attribute -->
        <form id="walkinForm" action="<?php echo e(route('logbook.diffun.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="fas fa-user"></i> Full Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" name="fullname" placeholder="Juan Dela Cruz" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        <i class="fas fa-phone"></i> Contact Number <span class="text-danger">*</span>
                        <small class="text-muted ms-1">(11 digits only, numbers only)</small>
                    </label>
                    <input type="text" class="form-control" name="contact_number" 
                           placeholder="09123456789" 
                           maxlength="11"
                           required>
                    <div class="invalid-feedback" id="contact-error" style="display: none;">
                        Contact number must be exactly 11 digits (numbers only).
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-location-dot"></i> Complete Address <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" name="address" placeholder="Street, Barangay, City, Province" required>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-building"></i> Law Office <span class="text-danger">*</span>
                </label>
                <select class="form-select" name="law_office_id" required id="lawOfficeSelect">
                    <option value="" selected disabled>Select law office...</option>
                </select>
            </div>

            <div class="mb-5">
                <label class="form-label">
                    <i class="fas fa-file-lines"></i> Purpose of Visit <span class="text-danger">*</span>
                </label>
                <select class="form-select" name="purpose_display" required id="purposeSelect">
                    <option value="" selected disabled>Loading purposes...</option>
                </select>
                <!-- Hidden input to store the actual purpose value -->
                <input type="hidden" name="purpose" id="purposeHidden" value="">
            </div>

            <!-- Custom Purpose Input Field (hidden by default) -->
            <div class="mb-5" id="customPurposeContainer" style="display: none;">
                <label class="form-label">
                    <i class="fas fa-edit"></i> Specify Purpose <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" name="custom_purpose" id="customPurposeInput" placeholder="Please specify your purpose of visit">
                <div class="invalid-feedback" id="custom-purpose-error" style="display: none;">
                    Please specify your purpose of visit.
                </div>
            </div>
                <input type="hidden" name="client_datetime" id="clientDateTime">
            <button type="submit" class="btn-submit w-100" id="submitBtn">
                SUBMIT ENTRY
            </button>
        </form>
    </div>
</section>

<!-- CLOCK -->
<div class="clock-box">
    <div class="clock-icon">
        <i class="far fa-clock"></i>
    </div>
    <div class="clock-text">
        <h4 id="time">00:00:00 AM</h4>
        <p id="date">SATURDAY, JANUARY 17, 2026</p>
    </div>
</div>

<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Custom JS -->
<script src="/js/staff/clock.js"></script>

<script>
    $(document).ready(function() {
        // Load law offices from database
        $.ajax({
            url: '/staff/walkins/logbook/law-offices',
            method: 'GET',
            success: function(offices) {
                $('#lawOfficeSelect').html('<option value="" selected disabled>Select law office</option>');
                $.each(offices, function(index, office) {
                    $('#lawOfficeSelect').append('<option value="' + office.id + '">' + office.law_office + '</option>');
                });
            },
            error: function() {
                $('#lawOfficeSelect').html('<option value="" selected disabled>Error loading law offices</option>');
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

        // Handle purpose dropdown change
        $('#purposeSelect').on('change', function() {
            var selectedValue = $(this).val();
            
            if (selectedValue === 'Other') {
                // Show custom purpose input and make it required
                $('#customPurposeContainer').show();
                $('#customPurposeInput').attr('required', true);
                // Clear the hidden purpose value until user enters custom purpose
                $('#purposeHidden').val('');
            } else {
                // Hide custom purpose input and remove required attribute
                $('#customPurposeContainer').hide();
                $('#customPurposeInput').removeAttr('required').val('');
                $('#customPurposeInput').removeClass('is-invalid');
                $('#custom-purpose-error').hide();
                // Set the hidden purpose value to the selected option
                $('#purposeHidden').val(selectedValue);
            }
        });

        // Handle form submission with AJAX
        $('#walkinForm').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            var selectedLawOffice = $('#lawOfficeSelect').val();
            var selectedPurpose = $('#purposeSelect').val();
            
            // Check if a law office is selected
            if (!selectedLawOffice || selectedLawOffice === "") {
                toastr.error('Please select a law office.');
                $('#lawOfficeSelect').focus();
                return false;
            }
            
            // Check if a purpose is selected
            if (!selectedPurpose || selectedPurpose === "") {
                toastr.error('Please select a purpose of visit.');
                $('#purposeSelect').focus();
                return false;
            }
            
            if (selectedPurpose === 'Other') {
                var customPurpose = $('#customPurposeInput').val().trim();
                
                if (!customPurpose) {
                    $('#customPurposeInput').addClass('is-invalid');
                    $('#custom-purpose-error').show();
                    $('#customPurposeInput').focus();
                    return false;
                } else {
                    // Set the hidden purpose value to the custom input
                    $('#purposeHidden').val(customPurpose);
                }
            }
            
            // Submit form via AJAX
            var formData = new FormData(this);
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        toastr.success(response.message);
                        
                        // Reset form
                        $('#walkinForm')[0].reset();
                        $('#customPurposeContainer').hide();
                        $('#customPurposeInput').removeAttr('required');
                        $('#purposeHidden').val('');
                        $('#purposeSelect').html('<option value="" selected disabled>Select purpose of visit</option>');
                        
                        // Reload purposes
                        $.ajax({
                            url: '/staff/walkins/logbook/purposes',
                            method: 'GET',
                            success: function(purposes) {
                                $.each(purposes, function(index, purpose) {
                                    $('#purposeSelect').append('<option value="' + purpose.purpose + '">' + purpose.purpose + '</option>');
                                });
                            }
                        });
                    } else {
                        toastr.error(response.message || 'An error occurred while submitting the form.');
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = 'An error occurred while submitting the form.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    toastr.error(errorMessage);
                }
            });
        });

        // Update hidden field when user types in custom purpose field
        $('#customPurposeInput').on('input', function() {
            var customPurpose = $(this).val().trim();
            if (customPurpose) {
                $('#purposeHidden').val(customPurpose);
                $(this).removeClass('is-invalid');
                $('#custom-purpose-error').hide();
            } else {
                $('#purposeHidden').val('');
            }
        });
    });
</script>

</body>
</html><?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\walkin logbook\diffun_logbook\index.blade.php ENDPATH**/ ?>