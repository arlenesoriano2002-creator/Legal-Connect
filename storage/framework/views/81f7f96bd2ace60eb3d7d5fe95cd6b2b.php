<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalize Appointment</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/FinalizeAppointment.blade.css')); ?>">

    
    <?php echo $__env->make('partials.global-error-handler', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</head>
<body>
    <div class="container">
        <header>
            <h1>Finalize Appointment</h1>
        </header>
        
        <div class="content">
            <!-- Error/Success Messages -->
            <?php if($errors->any()): ?>
                <div class="alert alert-error">
                    <h3>Please fix the following errors:</h3>
                    <ul>
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if(session('error')): ?>
                <div class="alert alert-error">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>
            
            <?php if(session('success')): ?>
                <div class="alert alert-success">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

           <div class="appointment-details">
                <h2>Appointment Details</h2>
                <p><strong>Full Name:</strong> <?php echo e($fullname); ?></p>
                <p><strong>Address:</strong> <?php echo e($address); ?></p>
                <p><strong>Phone:</strong> <?php echo e($phone); ?></p>
                <p><strong>Email:</strong> <?php echo e($email); ?></p>
                <p><strong>Category:</strong> <?php echo e($selected_category ?? 'Not specified'); ?></p>
                <p><strong>Case Type:</strong> <?php echo e($selected_case_name ?? 'Not specified'); ?></p>
                <!-- Add Selected Branch Display -->
                <p><strong>Selected Office:</strong> <?php echo e($selected_branch ?? session('branch') ?? 'Not specified'); ?></p>
                <p><strong>Terms Approval:</strong> 
                    <span class="terms-status <?php echo e($status_approval == 'approved' ? 'terms-approved' : 'terms-pending'); ?>">
                        <?php echo e(ucfirst($status_approval == 'approved' ? 'accepted' : 'pending')); ?>

                    </span>
                </p>
                <p><strong>Selected Date:</strong> <?php echo e($date); ?></p>
                <p><strong>Selected Time:</strong> <?php echo e($time); ?></p>
            </div>
            <form id="finalizeForm" action="<?php echo e(route('appointment.finalize')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="fullname" value="<?php echo e($fullname); ?>">
                <input type="hidden" name="address" value="<?php echo e($address); ?>">
                <input type="hidden" name="phone" value="<?php echo e($phone); ?>">
                <input type="hidden" name="email" value="<?php echo e($email); ?>">
                <input type="hidden" name="category" value="<?php echo e($selected_category); ?>">
                <input type="hidden" name="case_name" value="<?php echo e($selected_case_name); ?>">
                <!-- Add hidden input for branch with value -->
                <input type="hidden" name="selected_branch" id="selected_branch" value="<?php echo e($selected_branch); ?>">

                <strong>Selected Branch:</strong>
                <span id="selectedBranchText"><?php echo e($selected_branch); ?></span>
                <input type="hidden" name="selected_date" value="<?php echo e($date); ?>">
                <input type="hidden" name="selected_time" value="<?php echo e($time); ?>">
                <input type="hidden" name="term_status" value="<?php echo e($status_approval ?? 'Approved'); ?>">
                            
                <div class="upload-section">
                    <h2>Identification Documents</h2>
                    
                    <!-- Upload ID Front -->
                    <label for="id_front">Upload ID Front (Max 2MB)</label>
                    <input type="file" id="id_front" name="id_front" accept="image/jpeg,image/png,image/jpg" required>
                    <div class="compression-info" id="frontSizeInfo"></div>
                    <?php $__errorArgs = ['id_front'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="validation-error"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <div class="preview-container">
                        <img id="preview_front" alt="Front ID preview">
                        <button type="button" class="btn btn-remove" onclick="resetImage('id_front','preview_front')">Remove Front Image</button>
                    </div>
                    
                    <!-- Upload ID Back -->
                    <label for="id_back">Upload ID Back (Max 2MB)</label>
                    <input type="file" id="id_back" name="id_back" accept="image/jpeg,image/png,image/jpg">
                    <div class="compression-info" id="backSizeInfo"></div>
                    <?php $__errorArgs = ['id_back'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="validation-error"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <div class="preview-container">
                        <img id="preview_back" alt="Back ID preview">
                        <button type="button" class="btn btn-remove" onclick="resetImage('id_back','preview_back')">Remove Back Image</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-submit" id="submitBtn">Submit Appointment</button>
                <button type="button" class="btn btn-back" onclick="cancelAndBack()">Back</button>
            </form>
            
        </div>
    </div>

     <script src="<?php echo e(asset('js/FinalizeAppointment.js')); ?>"></script>
     <script>
        const backUrl = "<?php echo e(route('getsched')); ?>";
    </script>
</body>
</html><?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\FinalizeAppointment.blade.php ENDPATH**/ ?>