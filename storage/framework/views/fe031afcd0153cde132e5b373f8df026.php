<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Digital Logbook</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">

    <!-- Custom CSS -->
     <link rel="stylesheet" href="/css/cordon_staff/index.css">
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
                <h1>Legal Connect:Digital Logbook</h1>
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

    <?php if(session('success')): ?>
        <div id="server-success" data-message="<?php echo e(session('success')); ?>"></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div id="server-error" data-message="<?php echo e(session('error')); ?>"></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" action="<?php echo e(route('logbook.cordon.store')); ?>">
            <?php echo csrf_field(); ?>
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="fas fa-user"></i> Full Name
                    </label>
                    <input type="text" name="fullname" class="form-control" placeholder="Juan Dela Cruz" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        <i class="fas fa-phone"></i> Contact Number
                    </label>
                    <input type="text" id="contact_number" name="contact_number" class="form-control" placeholder="09XXXXXXXXX" inputmode="numeric" maxlength="11" pattern="\d{11}" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">
                    <i class="fas fa-location-dot"></i> Complete Address
                </label>
                <input type="text" name="address" class="form-control" placeholder="Street, Barangay, City, Province">
            </div>

            <div class="mb-5">
                <label class="form-label">
                    <i class="fas fa-file-lines"></i> Purpose of Visit
                </label>
                <select id="purposeSelect" name="purpose_display" class="form-select" required>
                    <option selected disabled>Select purpose of visit</option>
                    <?php if(!empty($purposes) && count($purposes) > 0): ?>
                        <?php $__currentLoopData = $purposes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p->purpose); ?>"><?php echo e($p->purpose); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <option>Consultation</option>
                        <option>Document Submission</option>
                        <option>Follow-up</option>
                        <option>Other</option>
                    <?php endif; ?>
                    <?php if(isset($purposes_count) && $purposes_count == 0): ?>
                        <div class="mt-2 text-muted small">No predefined purposes found (check <code>cordon_choice_purpose</code> table).</div>
                    <?php endif; ?>
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

            <button type="submit" class="btn-submit w-100">
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

<!-- JS -->
<!-- jQuery (required by clock.js) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Clock JS -->
<script src="/js/cordon_staff/clock.js"></script>

<script>
    $(document).ready(function() {
        // Display session messages
        <?php if(session('success')): ?>
            toastr.success('<?php echo e(session('success')); ?>');
        <?php endif; ?>
        <?php if(session('error')): ?>
            toastr.error('<?php echo e(session('error')); ?>');
        <?php endif; ?>

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

        // Handle form submission
        $('form').on('submit', function(e) {
            var selectedPurpose = $('#purposeSelect').val();
            
            if (selectedPurpose === 'Other') {
                var customPurpose = $('#customPurposeInput').val().trim();
                
                if (!customPurpose) {
                    e.preventDefault();
                    $('#customPurposeInput').addClass('is-invalid');
                    $('#custom-purpose-error').show();
                    $('#customPurposeInput').focus();
                    return false;
                } else {
                    // Set the hidden purpose value to the custom input
                    $('#purposeHidden').val(customPurpose);
                }
            }
            // For non-"Other" selections, the purpose is already set in the change handler
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
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\walkin logbook\cordon_logbook\index.blade.php ENDPATH**/ ?>