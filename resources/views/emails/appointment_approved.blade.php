<!DOCTYPE html>
<html>
<head>
    <title>Appointment Approved - LegalConnect</title>
</head>
<body>
    <h2>Appointment Approved</h2>
    
    <p>Dear {{ $appointment->fullname ?? $appointmentData['fullname'] ?? 'Valued Client' }},</p>
    
    <p>
        We are pleased to inform you that your appointment request for
        <strong>{{ $appointment->case_name ?? $appointmentData['case_name'] ?? 'N/A' }}</strong>
        under
        <strong>{{ $appointment->category ?? $appointmentData['category'] ?? 'N/A' }}</strong>
        has been <strong>APPROVED</strong>.
    </p>
    
    <p><strong>Appointment Details:</strong></p>
    <ul>
        <li><strong>Date:</strong> 
            @php
                // Prioritize appointmentData (captured at instantiation), then fall back to appointment object
                $dateValue = $appointmentData['selected_date'] ?? $appointment->selected_date ?? $appointment->schedule_date ?? null;
            @endphp
            @if(!empty($dateValue) && $dateValue !== '')
                {{ $dateValue }}
            @else
                <em>Date to be scheduled</em>
            @endif
        </li>
        <li><strong>Time:</strong> 
            @php
                // Prioritize appointmentData (captured at instantiation), then fall back to appointment object
                $timeValue = $appointmentData['selected_time'] ?? $appointment->selected_time ?? $appointment->schedule_time ?? null;
            @endphp
            @if(!empty($timeValue) && $timeValue !== '')
                {{ $timeValue }}
            @else
                <em>Time to be scheduled</em>
            @endif
        </li>
        <li><strong>Consultation Type:</strong> 
            {{ $appointment->category ?? $appointmentData['category'] ?? 'N/A' }} - 
            {{ $appointment->case_name ?? $appointmentData['case_name'] ?? 'N/A' }}
        </li>
        <li><strong>Branch:</strong> {{ $appointment->selected_branch ?? $appointmentData['selected_branch'] ?? 'N/A' }}</li>
    </ul>
    
    <p>Please arrive on time for your appointment. If you need to reschedule, please contact us in advance.</p>
    
    <p>Thank you for choosing LegalConnect. We look forward to assisting you with your legal needs.</p>
    
    <p>Best regards,<br><strong>LegalConnect Team</strong></p>
</body>
</html>
