<!DOCTYPE html>
<html>
<head>
    <title>Appointment Status Update - LegalConnect</title>
</head>
<body>
    <h2>Appointment Request Update</h2>
    
    <p>Dear {{ $appointment->fullname }},</p>
    
    <p>We regret to inform you that your appointment request has been <strong>DENIED</strong>.</p>
    
    <p><strong>Original Request Details:</strong></p>
    <ul>
        <li><strong>Date:</strong> {{ $appointment->selected_date }}</li>
        <li><strong>Time:</strong> {{ $appointment->selected_time }}</li>
        <li><strong>Consultation Type:</strong> {{ $appointment->consulting }}</li>
    </ul>
    
    <p>This may be due to incomplete information, verification issues, or scheduling conflicts.</p>
    
    <p>We apologize for any inconvenience this may cause. Please feel free to submit another request with complete and accurate information, or contact us if you have any questions.</p>
    
    <p>Best regards,<br><strong>LegalConnect Team</strong></p>
</body>
</html>