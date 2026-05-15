<!DOCTYPE html>
<html>
<head>
    <title>Password Change Verification Code</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #352a6e;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .otp-code {
            background-color: #fff;
            border: 2px dashed #352a6e;
            padding: 20px;
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 10px;
            color: #352a6e;
            margin: 20px 0;
            border-radius: 5px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h2>Password Change Verification Code</h2>
    <p>Hello {{ $name }},</p>
    
    <p>You have requested to change your password. Please use the following verification code:</p>
    
    <div style="background: #f4f4f4; padding: 20px; text-align: center; margin: 20px 0;">
        <h1 style="margin: 0; color: #333; letter-spacing: 5px;">{{ $otp }}</h1>
    </div>
    
    <p><strong>This code will expire in 10 minutes.</strong></p>
    
    <p>If you did not request this password change, please ignore this email or contact support.</p>
    
    <p>Thank you,<br>
    LegalConnect Team</p>
    
    <hr>
    <p style="color: #666; font-size: 12px;">
        This is an automated message. Please do not reply to this email.
    </p>
</body>
</html>