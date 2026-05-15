<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Admin Account Recovery</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin-account-setting/forgotPassword.css') }}">

    <style>
        .recovery-card { max-width: 720px; margin: 0 auto; }
        .recovery-step-badges { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 18px; }
        .recovery-step-badge { background: #e9eef5; border-radius: 999px; color: #5b6777; font-size: 13px; font-weight: 600; padding: 8px 14px; }
        .recovery-step-badge.active { background: #19aa8d; color: #fff; }
        .recovery-meta { background: #f8fafc; border: 1px solid #e3e8ef; border-radius: 10px; color: #4c5663; font-size: 14px; margin-bottom: 20px; padding: 14px 16px; }
        .verification-code-input { font-size: 28px; font-weight: 700; letter-spacing: 0.5rem; text-align: center; }
    </style>
</head>
<body>
    @php
        $isEmailStep = $mode === 'email';
        $isVerifyStep = $mode === 'verify';
        $isResetStep = $mode === 'reset';
        $recoveryEmail = old('email', $resetState['email'] ?? $user->email);
    @endphp



        <div id="page-content-wrapper">
            

            <div class="container-fluid mt-4">
                <div class="page-description">
                    <h4 class="fw-bold">Password Recovery</h4>
                    <p class="text-muted">Verify your email, confirm the code, and set a new password without leaving the admin area.</p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-10 col-xl-8">
                        <div class="settings-wrapper">
                            <div class="settings-card recovery-card">
                                <div class="recovery-step-badges">
                                    <span class="recovery-step-badge {{ $isEmailStep ? 'active' : '' }}">1. Email</span>
                                    <span class="recovery-step-badge {{ $isVerifyStep ? 'active' : '' }}">2. Verification</span>
                                    <span class="recovery-step-badge {{ $isResetStep ? 'active' : '' }}">3. Reset Password</span>
                                </div>

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if (session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif

                                @if ($isEmailStep)
                                    <h5 class="fw-semibold mb-2">Send Verification Code</h5>
                                    <p class="text-muted mb-4">
                                        Enter the email address for your current admin account. A 6-digit verification code will be sent using the configured SMTP mailer.
                                    </p>

                                    <form method="POST" action="{{ route('admin.account.settings.forgot-password.send') }}">
                                        @csrf
                                        <div class="form-group mb-4">
                                            <label class="form-label fw-semibold">Email Address</label>
                                            <div class="input-wrapper">
                                                <input type="email" name="email" class="form-control" value="{{ $recoveryEmail }}" required>
                                            </div>
                                        </div>

                                        <div class="actions d-flex gap-2">
                                            <a href="{{ route('admin.account.settings') }}" class="btn btn-secondary btn-lg w-50">Back to Settings</a>
                                            <button type="submit" class="btn btn-save btn-lg w-50">Send Code</button>
                                        </div>
                                    </form>
                                @endif

                                @if ($isVerifyStep)
                                    <h5 class="fw-semibold mb-2">Verify Email Code</h5>
                                    <div class="recovery-meta">
                                        <div><strong>Email:</strong> {{ $resetState['email'] ?? $user->email }}</div>
                                        <div><strong>Code Length:</strong> 6 digits</div>
                                        <div><strong>Expiration:</strong> {{ \Carbon\Carbon::parse($resetState['expires_at'])->format('M d, Y h:i A') }}</div>
                                    </div>

                                    <form method="POST" action="{{ route('admin.account.settings.forgot-password.verify.submit') }}">
                                        @csrf
                                        <div class="form-group mb-4">
                                            <label class="form-label fw-semibold">Verification Code</label>
                                            <div class="input-wrapper">
                                                <input type="text" name="verification_code" class="form-control verification-code-input" inputmode="numeric" maxlength="6" pattern="\d{6}" value="{{ old('verification_code') }}" required>
                                            </div>
                                            <small class="text-muted d-block mt-2">Enter the exact 6-digit code sent to your email. The code expires automatically.</small>
                                        </div>

                                        <div class="actions d-flex gap-2">
                                            <a href="{{ route('admin.account.settings.forgot-password.email') }}" class="btn btn-secondary btn-lg w-50">Change Email</a>
                                            <button type="submit" class="btn btn-save btn-lg w-50">Verify Code</button>
                                        </div>
                                    </form>
                                @endif

                                @if ($isResetStep)
                                    <h5 class="fw-semibold mb-2">Set a New Password</h5>
                                    <div class="recovery-meta">
                                        <div><strong>Verified Email:</strong> {{ $resetState['email'] ?? $user->email }}</div>
                                        <div><strong>Reset Window Ends:</strong> {{ \Carbon\Carbon::parse($resetState['reset_expires_at'])->format('M d, Y h:i A') }}</div>
                                    </div>

                                    <form method="POST" action="{{ route('admin.account.settings.forgot-password.reset.submit') }}">
                                        @csrf
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-semibold">New Password</label>
                                            <div class="input-wrapper position-relative">
                                                <input type="password" name="password" class="form-control js-password-toggle-target" required>
                                                <button type="button" class="btn btn-link position-absolute top-50 end-0 translate-middle-y me-2 text-secondary js-password-toggle">
                                                    <i class="fas fa-eye-slash"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="form-group mb-4">
                                            <label class="form-label fw-semibold">Confirm Password</label>
                                            <div class="input-wrapper position-relative">
                                                <input type="password" name="password_confirmation" class="form-control js-password-toggle-target" required>
                                                <button type="button" class="btn btn-link position-absolute top-50 end-0 translate-middle-y me-2 text-secondary js-password-toggle">
                                                    <i class="fas fa-eye-slash"></i>
                                                </button>
                                            </div>
                                            <small class="text-muted d-block mt-2">Use at least 8 characters with uppercase, lowercase, number, and special character.</small>
                                        </div>

                                        <div class="actions d-flex gap-2">
                                            <a href="{{ route('admin.account.settings.forgot-password.verify') }}" class="btn btn-secondary btn-lg w-50">Back to Verification</a>
                                            <button type="submit" class="btn btn-save btn-lg w-50">Update Password</button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('wrapper');
            const menuToggle = document.getElementById('menu-toggle');
            const topBar = document.querySelector('.top-bar');

            function updateTopBarPosition() {
                if (!wrapper || !topBar) return;
                topBar.style.left = wrapper.classList.contains('toggled') ? '70px' : '220px';
            }

            if (menuToggle && wrapper) {
                menuToggle.addEventListener('click', function (event) {
                    event.preventDefault();
                    wrapper.classList.toggle('toggled');
                    updateTopBarPosition();
                });
            }

            updateTopBarPosition();
            window.addEventListener('resize', updateTopBarPosition);

            document.querySelectorAll('.js-password-toggle').forEach((button) => {
                button.addEventListener('click', function () {
                    const input = this.parentElement.querySelector('.js-password-toggle-target');
                    const icon = this.querySelector('i');

                    if (!input || !icon) return;

                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';
                    icon.classList.toggle('fa-eye', isPassword);
                    icon.classList.toggle('fa-eye-slash', !isPassword);
                });
            });

            const verificationInput = document.querySelector('input[name="verification_code"]');
            if (verificationInput) {
                verificationInput.addEventListener('input', function () {
                    this.value = this.value.replace(/\D/g, '').slice(0, 6);
                });
            }
        });

        function showLogoutModal() {
            const logoutModal = new bootstrap.Modal(document.getElementById('logoutConfirmationModal'));
            logoutModal.show();
        }
    </script>
</body>
</html>
