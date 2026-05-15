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

    
    <?php echo $__env->make('partials.global-error-handler', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</head>
<body>
    <div class="login-container">
        <!--<a href="<?php echo e(url('/register')); ?>"class="signin-text">REGISTER</a>-->

        <!-- Right Side - Login Form -->
        <div class="form-side">
            <!-- Header -->
                <div class="form-header">
                    <div class="form-logo">
                         <img class="imgLogo" src="logo6.png" alt="LegalConnect logo" width="80" height="80" />
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
                <form method="POST" action="<?php echo e(route('login')); ?>" id="loginForm">
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
  <script src="<?php echo e(asset('js/login.js')); ?>"></script>
  <script>
    window.loginSessionConflictConfig = {
        sessionStateUrl: <?php echo json_encode(route('login.session-state'), 15, 512) ?>
    };
  </script>
  
  <!-- Per-Tab Auth Manager (automatically sends X-Tab-Token header on all requests) -->
  <script src="<?php echo e(asset('js/per-tab-auth-manager.js')); ?>"></script>
  
  <!-- Session Conflict Prevention - Prevent multiple active sessions for same user -->
  <script src="<?php echo e(asset('js/session-conflict-prevention.js')); ?>"></script>

  <!-- Session Timeout Manager - Auto-logout after inactivity -->
  <script src="<?php echo e(asset('js/session-timeout-manager.js')); ?>"></script>

  <!-- History Control Manager - Prevent back navigation to protected pages -->
  <script src="<?php echo e(asset('js/history-control-manager.js')); ?>"></script>
  
  <script>
    // Initialize PerTabAuthManager after DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        PerTabAuthManager.init();
        
        // Check for existing sessions and warn user
        SessionConflictPrevention.init();
    });
  </script>
  
  <!-- Tab Session Manager (same as welcome.blade.php) -->
  <script>
    const TabSessionManager = {
        TAB_ID_KEY: 'legal_connect_tab_id',
        TAB_TOKEN_KEY: 'legal_connect_tab_token',
        TAB_EXPIRY_KEY: 'legal_connect_tab_expiry',
        
        init() {
            let tabId = sessionStorage.getItem(this.TAB_ID_KEY);
            if (!tabId) {
                tabId = this.generateTabId();
                sessionStorage.setItem(this.TAB_ID_KEY, tabId);
                console.log('Generated new tab ID for login:', tabId);
            }
            this.setupFetchInterceptor();
            this.setupBeforeUnloadHandler();
        },
        
        generateTabId() {
            return 'tab_' + 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                const r = (Math.random() * 16) | 0;
                const v = c === 'x' ? r : (r & 0x3) | 0x8;
                return v.toString(16);
            });
        },
        
        setTabToken(tabToken, expiresAt) {
            sessionStorage.setItem(this.TAB_TOKEN_KEY, tabToken);
            sessionStorage.setItem(this.TAB_EXPIRY_KEY, expiresAt);
            console.log('Stored per-tab token:', tabToken.substring(0, 10) + '...');
        },
        
        getTabToken() {
            return sessionStorage.getItem(this.TAB_TOKEN_KEY);
        },
        
        getTabId() {
            return sessionStorage.getItem(this.TAB_ID_KEY);
        },
        
        isTokenExpired() {
            const expiry = sessionStorage.getItem(this.TAB_EXPIRY_KEY);
            if (!expiry) return true;
            return new Date() > new Date(expiry);
        },
        
        setupFetchInterceptor() {
            const originalFetch = window.fetch;
            const self = this;
            
            window.fetch = function(...args) {
                let [resource, config] = args;
                
                if (typeof resource === 'string' && resource.startsWith('/')) {
                    const tabToken = self.getTabToken();
                    if (tabToken && !self.isTokenExpired()) {
                        config = config || {};
                        config.headers = config.headers || {};
                        config.headers['X-Tab-Token'] = tabToken;
                    }
                }
                
                return originalFetch.apply(this, [resource, config]);
            };
        },
        
        setupBeforeUnloadHandler() {
            const self = this;
            window.addEventListener('beforeunload', function() {
                const tabToken = self.getTabToken();
                if (tabToken) {
                    const data = new FormData();
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (csrfToken) {
                        data.append('_token', csrfToken.content);
                    }
                    navigator.sendBeacon('/tab-session/logout', data);
                }
            });
        }
    };
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => TabSessionManager.init());
  </script>
  
  <!-- Login Form Enhancement for Per-Tab Sessions -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.querySelector('form');
        if (!loginForm) return;
        
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.querySelector('input[name="email"]').value;
            const password = document.querySelector('input[name="password"]').value;
            const tabId = TabSessionManager.getTabId();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                            document.querySelector('input[name="_token"]')?.value;
            
            try {
                if (typeof SessionConflictPrevention !== 'undefined') {
                    await SessionConflictPrevention.checkForExistingSession(email);
                }

                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password,
                        tab_id: tabId
                    })
                });
                
                const data = await response.json();
                
                if (data.success && data.tab_session) {
                    // Store per-tab token
                    TabSessionManager.setTabToken(
                        data.tab_session.tab_token,
                        data.tab_session.expires_at
                    );
                    
                    // Record session for conflict prevention
                    if (data.user && typeof PerTabAuthManager !== 'undefined') {
                        PerTabAuthManager.recordSession(data.user);
                        console.log('Session recorded for conflict prevention');
                    }
                    
                    console.log('Login successful, tab token stored');
                    
                    // Redirect to appropriate page based on role
                    window.location.href = data.redirect;
                } else if (data.success && !data.tab_session) {
                    // Fallback if no tab session data (regular form submission)
                    
                    // Record session for conflict prevention
                    if (data.user && typeof PerTabAuthManager !== 'undefined') {
                        PerTabAuthManager.recordSession(data.user);
                        console.log('Session recorded for conflict prevention (fallback)');
                    }
                    
                    window.location.href = data.redirect;
                } else {
                    // Show error
                    const errorList = document.querySelector('.error-list');
                    if (errorList) {
                        errorList.innerHTML = `<ul><li>${data.message || 'Login failed'}</li></ul>`;
                    } else {
                        alert(data.message || 'Login failed');
                    }
                }
            } catch (error) {
                console.error('Login error:', error);
                // Fallback to regular form submission in case of fetch error
                loginForm.submit();
            }
        });
    });
  </script>

</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\login.blade.php ENDPATH**/ ?>