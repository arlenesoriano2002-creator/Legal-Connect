<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <link rel="stylesheet" href="<?php echo e(asset('css/forgot-password/email.css')); ?>">
    <title>Forgot Password - Legal Connect</title>

    
    <?php echo $__env->make('partials.global-error-handler', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</head>
<body class="theme-dark-gold">
    <div class="forgot-container">
        <!-- Header -->
            <div class="form-header">
                <h1 class="form-title">RESET PASSWORD</h1>
                <p class="form-subtitle">Enter your email to receive Verification code</p>
            </div>
        <div class="forgot-card">
            

            <!-- Error/Success Messages -->
            <?php if($errors->any()): ?>
                <div class="error-list">
                    <ul>
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if(session('success')): ?>
                <div class="success-message">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <!-- Email Form -->
            <form method="POST" action="<?php echo e(route('password.send-otp')); ?>">
                <?php echo csrf_field(); ?>
                
                <!-- Email Input -->
                <div class="form-group">
                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                    </svg>
                    <input type="email" name="email" class="form-input" placeholder="Enter your email" value="<?php echo e(old('email')); ?>" required>
                </div>

                <!-- Buttons -->
                <div class="btn-group">
                    <button type="submit" class="submit-btn">Send Code</button>
                    <button type="button" class="back-btn" onclick="window.location.href='<?php echo e(route('login')); ?>'">Back to Login</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html><?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\forgot-password\email.blade.php ENDPATH**/ ?>