


<?php echo $__env->make('partials.notification-badge-visibility', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">


<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


<script>
    // Configure Toastr to match cordon logbook styling
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "4000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut",
        "tapToDismiss": false
    };

    // Global error handler function
    window.showError = function(message, title = 'Error') {
        toastr.error(message, title);
    };

    window.showSuccess = function(message, title = 'Success') {
        toastr.success(message, title);
    };

    window.showWarning = function(message, title = 'Warning') {
        toastr.warning(message, title);
    };

    window.showInfo = function(message, title = 'Info') {
        toastr.info(message, title);
    };

    // Handle Laravel validation errors
    document.addEventListener('DOMContentLoaded', function() {
        // Handle server-side flash messages (success/error)
        const serverSuccessEl = document.getElementById('server-success');
        const serverErrorEl = document.getElementById('server-error');

        if (serverSuccessEl) {
            const msg = serverSuccessEl.getAttribute('data-message') || 'Operation completed successfully.';
            window.showSuccess(msg, 'Success');
        }

        if (serverErrorEl) {
            const msg = serverErrorEl.getAttribute('data-message') || 'An error occurred.';
            window.showError(msg, 'Error');
        }

        // Handle Laravel validation errors
        const errorLists = document.querySelectorAll('.error-list');
        errorLists.forEach(function(errorList) {
            const errors = errorList.querySelectorAll('li');
            errors.forEach(function(error) {
                window.showError(error.textContent.trim(), 'Validation Error');
            });
            // Hide the original error list after showing toasts
            errorList.style.display = 'none';
        });

        // Handle individual field errors (Blade error feedback classes)
        const fieldErrors = document.querySelectorAll('.invalid-feedback, .error-message');
        fieldErrors.forEach(function(error) {
            if (error.textContent.trim()) {
                window.showError(error.textContent.trim(), 'Field Error');
                // Optionally hide the inline error after showing toast
                // error.style.display = 'none';
            }
        });

        // Handle Bootstrap alert errors
        const alertErrors = document.querySelectorAll('.alert-danger, .alert-error');
        alertErrors.forEach(function(alert) {
            const message = alert.textContent.trim();
            if (message) {
                window.showError(message, 'Error');
                // Optionally hide the alert after showing toast
                // alert.style.display = 'none';
            }
        });
    });
</script>


<?php if(session('success')): ?>
    <div id="server-success" data-message="<?php echo e(session('success')); ?>" style="display:none;"></div>
<?php endif; ?>
<?php if(session('error')): ?>
    <div id="server-error" data-message="<?php echo e(session('error')); ?>" style="display:none;"></div>
<?php endif; ?>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\partials\global-error-handler.blade.php ENDPATH**/ ?>