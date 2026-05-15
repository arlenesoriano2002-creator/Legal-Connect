<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/register.blade.css') }}">
    <title>Legal Connect - Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Global Error Handler --}}
    @include('partials.global-error-handler')

</head>
<body>
    <div class="register-container">
        <div class="form-side">
            <!-- Header -->
            <div class="form-header">
                <div class="form-logo">
                    <img class="imgLogo" src="logo6.png" alt="LegalConnect logo" width="80" height="80" />
                </div>
                <h1 class="form-title">REGISTER</h1>
            </div>
            <div class="form-content">
                <!-- Success Message -->
                @if(session('success'))
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="error-list">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif 

                <!-- Register Form -->
                <form method="POST" action="{{ route('register') }}">
                    @csrf 
                    
                    <div class="inputs-form">
                        <div class="form-columns">
                            <div class="left-column">
                                <!-- Fullname Input -->
                                <div class="form-group">
                                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <input type="text" name="name" class="form-input" placeholder="Fullname (Firstname, M.I, Surname)" required
                                        oninput="validateFullName(event)" value="{{ old('name') }}">
                                    @error('name')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Phone Number Input with Country Code -->
                                <div class="form-group">
                                    <div class="phone-input-container">
                                        <select name="country_code" id="country_code" class="country-code-select" onchange="updatePhoneNumberLimit()">
                                            <option value="+63" {{ old('country_code') == '+63' ? 'selected' : '' }}>+63 Philippines</option>
                                            <option value="+1" {{ old('country_code') == '+1' ? 'selected' : '' }}>+1 USA/Canada</option>
                                            <option value="+44" {{ old('country_code') == '+44' ? 'selected' : '' }}>+44 UK</option>
                                            <option value="+61" {{ old('country_code') == '+61' ? 'selected' : '' }}>+61 Australia</option>
                                            <option value="+81" {{ old('country_code') == '+81' ? 'selected' : '' }}>+81 Japan</option>
                                            <option value="+86" {{ old('country_code') == '+86' ? 'selected' : '' }}>+86 China</option>
                                            <option value="+91" {{ old('country_code') == '+91' ? 'selected' : '' }}>+91 India</option>
                                            <option value="+49" {{ old('country_code') == '+49' ? 'selected' : '' }}>+49 Germany</option>
                                            <option value="+33" {{ old('country_code') == '+33' ? 'selected' : '' }}>+33 France</option>
                                            <option value="+34" {{ old('country_code') == '+34' ? 'selected' : '' }}>+34 Spain</option>
                                            <option value="+39" {{ old('country_code') == '+39' ? 'selected' : '' }}>+39 Italy</option>
                                            <option value="+7" {{ old('country_code') == '+7' ? 'selected' : '' }}>+7 Russia</option>
                                            <option value="+82" {{ old('country_code') == '+82' ? 'selected' : '' }}>+82 South Korea</option>
                                            <option value="+55" {{ old('country_code') == '+55' ? 'selected' : '' }}>+55 Brazil</option>
                                            <option value="+52" {{ old('country_code') == '+52' ? 'selected' : '' }}>+52 Mexico</option>
                                        </select>
                                        <input type="text" name="phone_number" class="form-input phone-number-input" placeholder="Phone number" required
                                            id="phone_number" oninput="validatePhoneNumber(event)" value="{{ old('phone_number') }}">
                                    </div>
                                    @error('country_code')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                    @error('phone_number')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                    <div class="phone-limit-info" id="phoneLimitInfo">Max: 10 digits</div>
                                </div>

                                <!-- Address Input -->
                                <div class="form-group">
                                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <input type="text" name="address" class="form-input" placeholder="Address" required value="{{ old('address') }}">
                                    @error('address')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="right-column">
                                <!-- Email Input -->
                                <div class="form-group">
                                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                    </svg>
                                    <input type="email" name="email" class="form-input" placeholder="Email" required value="{{ old('email') }}">
                                    @error('email')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Password Input -->
                                <div class="form-group">
                                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <input type="password" name="password" class="form-input" placeholder="Password" required id="password">
                                    <button type="button" class="password-toggle" onclick="togglePassword('password', 'eye-open-1', 'eye-closed-1')">
                                        <svg id="eye-closed-1" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg id="eye-open-1" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                                        </svg>
                                    </button>
                                    @error('password')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror

                                    <!-- Password Strength Meter -->
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

                                <!-- Confirm Password Input -->
                                <div class="form-group">
                                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <input type="password" name="password_confirmation" class="form-input" placeholder="Confirm Password" required id="password_confirmation">
                                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', 'eye-open-2', 'eye-closed-2')">
                                        <svg id="eye-closed-2" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg id="eye-open-2" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Register Button -->
                    <div class="btn">
                        <button type="submit" class="register-button">Register</button>
                        <button type="button" class="back-button" onclick="window.location.href='{{ url('/welcome') }}'">Back</button>
                    </div>
                    <div class="textLoginDescription">
                        <p>Already have an account?</p><a href="{{ url('/login') }}" class="signup-text">Log-in</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Phone number validation and limitation
        const phoneNumberLimits = {
            '+63': 10, // Philippines: 10 digits after +63
            '+1': 10,  // USA/Canada: 10 digits
            '+44': 10, // UK: 10 digits
            '+61': 9,  // Australia: 9 digits
            '+81': 10, // Japan: 10 digits
            '+86': 11, // China: 11 digits
            '+91': 10, // India: 10 digits
            '+49': 10, // Germany: 10-11 digits
            '+33': 9,  // France: 9 digits
            '+34': 9,  // Spain: 9 digits
            '+39': 10, // Italy: 10 digits
            '+7': 10,  // Russia: 10 digits
            '+82': 9,  // South Korea: 9-10 digits
            '+55': 11, // Brazil: 11 digits
            '+52': 10  // Mexico: 10 digits
        };

        function updatePhoneNumberLimit() {
            const countryCode = document.getElementById('country_code').value;
            const phoneInput = document.getElementById('phone_number');
            const limitInfo = document.getElementById('phoneLimitInfo');
            
            const maxLength = phoneNumberLimits[countryCode] || 15;
            
            // Update the maxlength attribute
            phoneInput.maxLength = maxLength;
            
            // Update the info text
            limitInfo.textContent = `Max: ${maxLength} digits`;
            
            // Truncate current value if it exceeds the new limit
            if (phoneInput.value.length > maxLength) {
                phoneInput.value = phoneInput.value.slice(0, maxLength);
            }
        }

        function validatePhoneNumber(event) {
            const input = event.target;
            const countryCode = document.getElementById('country_code').value;
            const maxLength = phoneNumberLimits[countryCode] || 15;
            
            // Remove all non-digit characters
            let numbers = input.value.replace(/\D/g, '');
            
            // Limit to maxLength digits
            if (numbers.length > maxLength) {
                numbers = numbers.slice(0, maxLength);
            }
            
            // Update the input value
            if (input.value !== numbers) {
                input.value = numbers;
            }
            
            // Visual feedback
            if (numbers.length === maxLength) {
                input.style.borderColor = '#10b981'; // Green when complete
            } else if (numbers.length > 0) {
                input.style.borderColor = '#3b82f6'; // Blue when typing
            } else {
                input.style.borderColor = '#e0e0e0'; // Default
            }
        }

        // Existing functions
        function togglePassword(inputId, eyeOpenId, eyeClosedId) {
            const passwordInput = document.getElementById(inputId);
            const eyeOpen = document.getElementById(eyeOpenId);
            const eyeClosed = document.getElementById(eyeClosedId);

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

        // Full name: allow letters, spaces, commas; auto-capitalize each word.
        function validateFullName(event) {
            const input = event.target;
            let s = input.value.replace(/[^A-Za-z\s,.]/g, '');
            s = s.replace(/\s+/g, ' ').replace(/^\s+/, '');
            s = s.replace(/\b([A-Za-z])([A-Za-z]*)/g, (_, first, rest) => first.toUpperCase() + rest.toLowerCase());
            if (input.value !== s) input.value = s;
        }

        // Real-time validation for email
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.querySelector('input[name="email"]');
            
            // Validate email on blur
            emailInput.addEventListener('blur', function() {
                validateEmail(this.value);
            });
            
            // Initialize phone number limit
            updatePhoneNumberLimit();
        });

        function validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showFieldError('email', 'Please enter a valid email address');
            } else {
                clearFieldError('email');
            }
        }

        function showFieldError(fieldName, message) {
            clearFieldError(fieldName);
            const errorElement = document.createElement('span');
            errorElement.className = 'error-text';
            errorElement.style.color = 'var(--error-color)';
            errorElement.style.fontSize = '14px';
            errorElement.style.marginTop = '5px';
            errorElement.style.display = 'block';
            errorElement.textContent = message;
            const input = document.querySelector(`input[name="${fieldName}"]`);
            input.parentNode.appendChild(errorElement);
            input.style.borderColor = 'var(--error-color)';
        }

        function clearFieldError(fieldName) {
            const input = document.querySelector(`input[name="${fieldName}"]`);
            const errorElement = input.parentNode.querySelector('.error-text');
            if (errorElement) {
                errorElement.remove();
            }
            input.style.borderColor = '#e0e0e0';
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

            if (requirements.length) strength++;
            if (requirements.lowercase) strength++;
            if (requirements.uppercase) strength++;
            if (requirements.number) strength++;
            if (requirements.special) strength++;

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
            strengthContainer.className = `password-strength strength-${level}`;
            strengthLevel.textContent = text;
        }

        document.getElementById('password').addEventListener('input', updatePasswordStrength);
        document.addEventListener('DOMContentLoaded', function() {
            updatePasswordStrength();
        });
    </script>
</body>
</html>