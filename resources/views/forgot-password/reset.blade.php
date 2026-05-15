<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/forgot-password/reset.css') }}">
    <title>Reset Password - Legal Connect</title>

    {{-- Global Error Handler --}}
    @include('partials.global-error-handler')

</head>
<body class="theme-dark-gold">
    <div class="forgot-container">
        <!-- Header -->
        <div class="form-header">
            <h1 class="form-title">NEW PASSWORD</h1>
            <p class="form-subtitle">Create your new password</p>
        </div>
        
        <div class="forgot-card">
            <!-- Success Message (when OTP is verified) -->
            @if (session('verified'))
                <div class="success-message">
                    The code was verified successfully!
                </div>
            @endif

            <!-- Error/Success Messages -->
            @if ($errors->any())
                <div class="error-list">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="success-message">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Reset Password Form -->
            <form method="POST" action="{{ route('password.reset') }}">
                @csrf
                <input type="hidden" name="email" value="{{ session('password_reset_email') ?? session('email') ?? old('email') }}">
                
                <!-- New Password -->
                <div class="form-group">
                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <input type="password" name="password" class="form-input" placeholder="New Password" required id="password">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <svg class="eye-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                    
                    <!-- Password Strength Meter (Simplified - No Requirements List) -->
                    <div class="password-strength">
                        <div class="strength-meter">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-text" id="strengthText">
                            <span>Password strength: </span>
                            <span id="strengthLevel">Too short</span>
                        </div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <input type="password" name="password_confirmation" class="form-input" placeholder="Confirm New Password" required id="password_confirmation">
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                        <svg class="eye-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>

                <!-- Buttons -->
                <div class="btn-group">
                    <button type="submit" class="submit-btn">Reset Password</button>
                    <button type="button" class="back-btn" onclick="window.location.href='{{ route('password.otp') }}'">Back</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const eyeIcon = passwordInput.parentNode.querySelector('.eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            }
        }

        // Password Strength Detection
        function checkPasswordStrength(password) {
            let strength = 0;
            const requirements = {
                length: password.length >= 8,
                lowercase: /[a-z]/.test(password),
                uppercase: /[A-Z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
            };

            // Calculate strength score
            if (requirements.length) strength++;
            if (requirements.lowercase) strength++;
            if (requirements.uppercase) strength++;
            if (requirements.number) strength++;
            if (requirements.special) strength++;

            // Determine strength level
            let level = 'too-short';
            let text = 'Too short';

            if (password.length > 0) {
                if (password.length < 8) {
                    level = 'too-short';
                    text = 'Too short';
                } else if (strength <= 2) {
                    level = 'weak';
                    text = 'Weak';
                } else if (strength === 3) {
                    level = 'fair';
                    text = 'Fair';
                } else if (strength === 4) {
                    level = 'good';
                    text = 'Good';
                } else if (strength === 5) {
                    level = 'strong';
                    text = 'Strong';
                }
            }

            return { level, text };
        }

        function updatePasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthContainer = document.querySelector('.password-strength');
            const strengthLevel = document.getElementById('strengthLevel');
            
            const { level, text } = checkPasswordStrength(password);
            
            // Update UI
            strengthContainer.className = `password-strength strength-${level}`;
            strengthLevel.textContent = text;
        }

        // Add event listener to password input
        document.getElementById('password').addEventListener('input', updatePasswordStrength);

        // Also update when page loads (in case of browser autofill)
        document.addEventListener('DOMContentLoaded', function() {
            updatePasswordStrength();
        });
    </script>
</body>
</html>
