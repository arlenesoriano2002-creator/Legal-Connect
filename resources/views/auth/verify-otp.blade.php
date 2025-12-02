<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --success-color: #4cc9f0;
            --error-color: #f72585;
            --background-color: #f8f9fa;
            --card-background: #ffffff;
            --text-color: #333333;
            --border-radius: 12px;
            --box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #e2e0e0;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 450px;
        }

        .card {
            background: var(--card-background);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background: black;
            color: white;
            text-align: center;
            padding: 30px 20px;
            color:#D4AF37;
        }

        .card-header h1 {
            font-weight: 600;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .card-header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .card-body {
            padding: 30px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .alert-error {
            background-color: #ffebee;
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }

        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }

        .alert i {
            margin-right: 10px;
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        input[type="text"] {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus {
            border-color: rgba(246, 182, 19, 0.79);
            outline: none;
            box-shadow: 0 0 0 3px rgba(162, 152, 127, 0.59);
        }

        .btn {
            display: block;
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: black;
            color: white;
            margin-bottom: 15px;
        }

        .btn-primary:hover {
            background: rgba(254, 183, 3, 0.99);
            color: black;
        }

        .btn-secondary {
            background: transparent;
            color: black;
            border: 2px solid black;
        }

        .btn-secondary:hover {
            background: rgba(0, 0, 0, 1);
            color:white;
        }

        .otp-instructions {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
            font-size: 14px;
        }

        .countdown {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 15px;
            color: #6c757d;
            font-size: 14px;
        }

        .countdown i {
            margin-right: 5px;
        }

        /* Responsive design */
        @media (max-width: 480px) {
            .card-header {
                padding: 20px 15px;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .card-header h1 {
                font-size: 24px;
            }
            
            input[type="text"] {
                padding: 12px 12px 12px 40px;
            }
            
            .btn {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>Verify Your Identity</h1>
                <p>Enter the 4-digit code sent to your email</p>
            </div>
            
            <div class="card-body">
                <!-- Success Message -->
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Error Messages -->
                @if(session('error'))
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        @foreach ($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('otp.verify') }}">
                    @csrf
                    <div class="form-group">
                        <label for="otp">Verification Code</label>
                        <div class="input-wrapper">
                            <i class="fas fa-key"></i>
                            <input type="text" id="otp" name="otp" placeholder="Enter 4-digit Code" required maxlength="4" value="{{ old('otp') }}">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Verify Code</button>
                    <button type="button" class="btn btn-primary" onclick="window.location.href='{{ url('/register') }}'">Back</button>
                </form>

                <form method="POST" action="{{ route('otp.resend') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Resend Code</button>
                </form>
                
                <div class="otp-instructions">
                    <p>Enter the 4-digit verification code sent to your email address.</p>
                </div>
                
                <div class="countdown">
                    <i class="far fa-clock"></i>
                    <span id="countdown-text">Code expires in: 10:00</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Countdown timer simulation
            let timeLeft = 600; // 10 minutes in seconds
            const countdownElement = document.getElementById('countdown-text');
            
            const countdown = setInterval(function() {
                timeLeft--;
                
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    countdownElement.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Code has expired';
                    countdownElement.style.color = '#f72585';
                } else {
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    countdownElement.textContent = `Code expires in: ${minutes}:${seconds.toString().padStart(2, '0')}`;
                }
            }, 1000);
            
            // OTP input validation
            const otpInput = document.getElementById('otp');
            otpInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                
                if (this.value.length === 4) {
                    this.style.borderColor = '#4caf50';
                } else {
                    this.style.borderColor = '#e0e0e0';
                }
            });

            // Auto-focus on OTP input
            otpInput.focus();
        });
    </script>
</body>
</html>