<!DOCTYPE html>
<html>
<head>
    <title>Re: {{ $reply_subject }} - LegalConnect</title>
</head>
<body>
    <h2>Message Reply from LegalConnect</h2>
    
    <p>Dear {{ $inquiry_name }},</p>
    
    <p>Thank you for contacting LegalConnect. We have received your inquiry and are providing a response below:</p>
    
    <div style="border-left: 4px solid #007bff; padding: 15px; background-color: #f8f9fa; margin: 20px 0;">
        <h3 style="margin-top: 0;">{{ $reply_subject }}</h3>
        <p style="white-space: pre-wrap; line-height: 1.6;">{{ $reply_message }}</p>
    </div>
    
    <p>If you have any further questions or need additional assistance, please feel free to contact us.</p>
    
    <p>Best regards,<br><strong>{{ $sender_name }}</strong><br><strong>LegalConnect Team</strong></p>
    
    <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
    <p style="color: #666; font-size: 12px; text-align: center;">
        This is an automated email reply. Please do not respond directly to this email.
    </p>
</body>
</html>
