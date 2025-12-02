<!DOCTYPE html>
<html>
<head>
    <title>Appointment Approved - LegalConnect</title>
</head>
<body>
    <h2>Appointment Approved</h2>
    
    <p>Dear {{ $appointment->fullname }},</p>
    
    <p>We are pleased to inform you that your appointment request has been <strong>APPROVED</strong>.</p>
    
    <p><strong>Appointment Details:</strong></p>
    <ul>
        <li><strong>Date:</strong> {{ $appointment->selected_date }}</li>
        <li><strong>Time:</strong> {{ $appointment->selected_time }}</li>
        <li><strong>Consultation Type:</strong> {{ $appointment->consulting }}</li>
    </ul>
    
    <p>Please arrive on time for your appointment. If you need to reschedule, please contact us in advance.</p>
    
    <p>Thank you for choosing LegalConnect. We look forward to assisting you with your legal needs.</p>
    
    <p>Best regards,<br><strong>LegalConnect Team</strong></p>
</body>
</html>