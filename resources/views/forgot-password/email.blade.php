<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/forgot-password/email.css') }}">
    <title>Forgot Password - Legal Connect</title>
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

            <!-- Email Form -->
            <form method="POST" action="{{ route('password.send-otp') }}">
                @csrf
                
                <!-- Email Input -->
                <div class="form-group">
                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                    </svg>
                    <input type="email" name="email" class="form-input" placeholder="Enter your email" value="{{ old('email') }}" required>
                </div>

                <!-- Buttons -->
                <div class="btn-group">
                    <button type="submit" class="submit-btn">Send Code</button>
                    <button type="button" class="back-btn" onclick="window.location.href='{{ route('login') }}'">Back to Login</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>