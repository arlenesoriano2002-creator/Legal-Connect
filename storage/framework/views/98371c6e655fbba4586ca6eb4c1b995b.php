<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <link rel="stylesheet" href="<?php echo e(asset('css/forgot-password/otp.css')); ?>">
    <title>Verify OTP - Legal Connect</title>
</head>
<body class="theme-dark-gold">
    <div class="forgot-container">
        <!-- Header -->
            <div class="form-header">
                <h1 class="form-title">VERIFY THE CODE</h1>
                <p class="form-subtitle">Enter the 4-digit code sent to your email</p>
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

            <!-- OTP Form -->
            <form method="POST" action="<?php echo e(route('password.verify-otp')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="email" value="<?php echo e(session('email') ?? old('email')); ?>">
                
                <!-- OTP Input -->
                <div class="form-group">
                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <input type="text" name="otp" class="form-input" placeholder="Enter 4-digit Code" maxlength="4" pattern="\d{4}" required>
                </div>

                <!-- Buttons -->
                <div class="btn-group">
                    <button type="submit" class="submit-btn">Verify</button>
                    <button type="button" class="back-btn" onclick="window.location.href='<?php echo e(route('password.request')); ?>'">Back</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html><?php /**PATH D:\xampp\htdocs\LEGAL CONNECT\resources\views/forgot-password/otp.blade.php ENDPATH**/ ?>