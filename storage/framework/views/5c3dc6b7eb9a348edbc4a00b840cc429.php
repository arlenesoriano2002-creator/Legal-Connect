<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="0">
     <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <link rel="stylesheet" href="<?php echo e(asset('css/login.blade.css')); ?>">
    <title>Legal Connect - Online Legal Appointments</title>
    <style>

    </style>
</head>
<body>
    <div class="login-container">
        <!--<a href="<?php echo e(url('/register')); ?>"class="signin-text">REGISTER</a>-->

        <!-- Right Side - Login Form -->
        <div class="form-side">
            <!-- Header -->
                <div class="form-header">
                    <div class="form-logo">
                         <img src="logo6.png" alt="LegalConnect logo" width="80" height="80" />
                    </div>
                    <h1 class="form-title">LOGIN</h1>
                </div>
            <div class="form-content">

                <!-- Error Messages (Laravel Blade) -->
                 <?php if($errors->any()): ?>
                    <div class="error-list">
                        <ul>
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?> 

                <!-- Login Form -->
                <form method="POST" action="<?php echo e(route('login')); ?>">
                     <?php echo csrf_field(); ?>
                    
                    <!-- Email Input -->
                    <div class="form-group">
                        <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                        </svg>
                        <input type="email" name="email" class="form-input" placeholder="Email" required>
                    </div>

                    <!-- Password Input -->
                    <div class="form-group">
                        <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <input type="password" name="password" class="form-input" placeholder="Password" required id="password">
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <svg id="eye-closed" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eye-open" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Forgot Password -->
                    <div class="forgot-password">
                        <a href="<?php echo e(route('password.request')); ?>">Forgot Password?</a>
                    </div>

                    <!-- Login Button -->
                   <div class="btn">
                     <button type="submit" class="login-button">Login</button>
                    <button type="button"  class="back-button" onclick="window.location.href='<?php echo e(url('/welcome')); ?>'">Back</button>
                   </div>
                   <div class="textRegisterDescription">
                    <p>Don't have an account?</p><a href="<?php echo e(url('/register')); ?>"class="signin-text">Register</a>
                   </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Prevent back and forward navigation, and disable shortcuts after logout or sensitive actions
        function preventNavigation() {
            // Push multiple states to prevent both back and forward navigation
            history.pushState(null, null, location.href);
            history.pushState(null, null, location.href);
            history.pushState(null, null, location.href);

            // Handle any navigation attempt (back or forward)
            window.onpopstate = function(event) {
                // Push state again to prevent navigation
                history.pushState(null, null, location.href);
                // Show alert for any navigation attempt
                alert('Navigation is disabled for security reasons.');
            };
        }

        // Disable browser navigation buttons, shortcuts, and undo/redo
        function disableBrowserButtons() {
            // Disable back button
            history.pushState(null, null, location.href);
            // Disable forward button by manipulating history
            history.replaceState(null, null, location.href);

            // Prevent context menu back/forward
            document.addEventListener('contextmenu', function(e) {
                // Allow context menu but prevent back/forward actions
                setTimeout(() => {
                    history.pushState(null, null, location.href);
                }, 0);
            });

            // Prevent keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Prevent Alt+Left (back), Alt+Right (forward), Ctrl+Left, Ctrl+Right
                if ((e.altKey && (e.key === 'ArrowLeft' || e.key === 'ArrowRight')) ||
                    (e.ctrlKey && (e.key === 'ArrowLeft' || e.key === 'ArrowRight' || e.key === 'z' || e.key === 'y'))) {
                    e.preventDefault();
                    alert('Navigation and undo/redo are disabled for security reasons.');
                    return false;
                }
            });
        }

        // Call functions to disable navigation and shortcuts
        document.addEventListener('DOMContentLoaded', function() {
            preventNavigation();
            disableBrowserButtons();
        });

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
            } else {
                passwordInput.type = 'password';
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
            }
        }
    </script>
</body>
</html><?php /**PATH D:\xampp\htdocs\LEGAL CONNECT\resources\views/login.blade.php ENDPATH**/ ?>