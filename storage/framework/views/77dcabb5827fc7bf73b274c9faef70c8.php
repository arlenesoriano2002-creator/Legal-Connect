<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Walk-in Logbook Login</title>
    <meta name="description" content="Login to access Digital Walk-in Logbook System">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    
    <?php echo $__env->make('partials.global-error-handler', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
        }
        
        .login-header {
            background: #f8f9fa;
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        
        .login-body {
            padding: 30px;
        }
        
        .form-control {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 1px solid #ddd;
        }
        
        .logo-icon {
            color: #667eea;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 5px;
        }
        
        .branch-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .branch-diffun {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .branch-cordon {
            background: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="logo-icon">
                <i class="fas fa-scale-balanced"></i>
            </div>
            <h2>Walk-in Logbook</h2>
            <p class="text-muted mb-0">Legal Connect Visitor's Management Platform</p>
            
            <div class="mt-3">
                <span class="branch-badge branch-diffun">Diffun Branch</span>
                <span class="branch-badge branch-cordon">Cordon Branch</span>
            </div>
        </div>
        
        <div class="login-body">
            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('logbook.login')); ?>">
                <?php echo csrf_field(); ?>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        <input 
                            type="text" 
                            class="form-control <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                            name="username" 
                            placeholder="Enter username"
                            value="<?php echo e(old('username')); ?>"
                            required
                            autofocus
                        >
                    </div>
                    <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="error-message"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input 
                            type="password" 
                            class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                            name="password" 
                            id="logbook-password"
                            placeholder="Enter password"
                            required
                        >
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1" title="Show/Hide password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="error-message"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                
                <button type="submit" class="btn btn-login w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i> Login to Logbook
                </button>
                
                <div class="text-center text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Login credentials are branch-specific
                </div>
            </form>
            
            <!--<?php if(app()->environment('local')): ?>
            <div class="mt-4 p-3 bg-light rounded">
                <h6 class="mb-2">Test Credentials:</h6>
                <div class="row">
                    <div class="col-6">
                        <small><strong>Diffun:</strong><br>
                        User: diffun_admin<br>
                        Pass: password123</small>
                    </div>
                    <div class="col-6">
                        <small><strong>Cordon:</strong><br>
                        User: cordon_admin<br>
                        Pass: password123</small>
                    </div>
                </div>
            </div>
            <?php endif; ?>-->
        </div>
        
        <div class="login-footer text-center p-3 bg-light border-top">
            <small class="text-muted">
                <i class="fas fa-copyright me-1"></i> <?php echo e(date('Y')); ?> Legal Connect
            </small>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Clear form on page refresh
        if (window.performance.navigation.type === 1) {
            document.querySelector('form').reset();
        }
        
        // Focus on username field
        document.querySelector('[name="username"]').focus();
        
        // Add enter key support
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                if (e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                }
            }
        });
        
        // Show / hide password toggle
        (function() {
            const toggle = document.getElementById('togglePassword');
            if (!toggle) return;
            const pwd = document.getElementById('logbook-password');
            toggle.addEventListener('click', function() {
                if (!pwd) return;
                const icon = this.querySelector('i');
                if (pwd.type === 'password') {
                    pwd.type = 'text';
                    if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
                } else {
                    pwd.type = 'password';
                    if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
                }
            });
        })();
    </script>
</body>
</html><?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\logbook\login.blade.php ENDPATH**/ ?>