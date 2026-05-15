<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Changed Successfully</title>
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
            background-color: #4CAF50;
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
        .success-box {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box {
            background-color: #e8f4fd;
            border: 1px solid #b8daff;
            color: #004085;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LegalConnect</h1>
        <h2>Password Changed Successfully</h2>
    </div>
    
    <div class="content">
        <p>Hello {{ $name }},</p>
        
        <div class="success-box">
            <p><strong>✅ Your password has been changed successfully!</strong></p>
        </div>
        
        <div class="info-box">
            <p><strong>Change Details:</strong></p>
            <ul>
                <li>Account: {{ $email }}</li>
                <li>Changed on: {{ $timestamp }}</li>
                <li>IP Address: {{ $ip_address }}</li>
            </ul>
        </div>
        
        <p><strong>Security Notice:</strong></p>
        <ul>
            <li>If you did not make this change, please contact our support team immediately</li>
            <li>Ensure you're using a strong, unique password</li>
            <li>Never share your password with anyone</li>
            <li>Consider enabling two-factor authentication for added security</li>
        </ul>
        
        <p>For security reasons, we recommend that you:</p>
        <ol>
            <li>Log out of all other sessions</li>
            <li>Update your password on any other devices where you use LegalConnect</li>
            <li>Keep your new password secure</li>
        </ol>
        
        <p>If you have any questions or concerns, please contact our support team.</p>
        
        <p>Best regards,<br>
        LegalConnect Security Team</p>
    </div>
    
    <div class="footer">
        <p>This is an automated security notification. Please do not reply to this email.</p>
        <p>© {{ date('Y') }} LegalConnect. All rights reserved.</p>
    </div>
</body>
</html>