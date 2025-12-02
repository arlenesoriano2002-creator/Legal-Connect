<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>OTP Code - Legal Connect</title>
    <link rel="stylesheet" href="{{ asset('css/emails/otp.css') }}">
</head>
<body>
    <div class="header">
        <h1>Legal Connect</h1>
        <p>Online Legal Appointments</p>
    </div>
    
    <div class="content">
        <h2>Password Reset Code!!!</h2>
        <p>Hello,</p>
        <p>You have requested to reset your password. Use the following verification code to proceed:</p>
        
        <div class="otp-code">{{ $otp }}</div>
        
        <p>This Verification code is valid for 10 minutes.</p>
        <p>If you didn't request this password reset, please ignore this email.</p>
        
        <p>Best regards,<br>Legal Connect Team</p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} Legal Connect. All rights reserved.</p>
    </div>
</body>
</html>