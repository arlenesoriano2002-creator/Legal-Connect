<!DOCTYPE html>
<html>
<head>
    <title>Appointment Status Update - LegalConnect</title>
</head>
<body>
    <h2>Appointment Request Update</h2>
    
    <p>Dear <?php echo e($appointment->fullname ?? $appointmentData['fullname'] ?? 'Valued Client'); ?>,</p>
    
    <p>
        We regret to inform you that your appointment request for
        <strong><?php echo e($appointment->case_name ?? $appointmentData['case_name'] ?? 'N/A'); ?></strong>
        under
        <strong><?php echo e($appointment->category ?? $appointmentData['category'] ?? 'N/A'); ?></strong>
        has been <strong>DENIED</strong>.
    </p>
    
    <p><strong>Original Request Details:</strong></p>
    <ul>
        <li><strong>Date:</strong> 
            <?php
                // Prioritize appointmentData (captured at instantiation), then fall back to appointment object
                $dateValue = $appointmentData['selected_date'] ?? $appointment->selected_date ?? $appointment->schedule_date ?? null;
            ?>
            <?php if(!empty($dateValue) && $dateValue !== ''): ?>
                <?php echo e($dateValue); ?>

            <?php else: ?>
                <em>Date not specified</em>
            <?php endif; ?>
        </li>
        <li><strong>Time:</strong> 
            <?php
                // Prioritize appointmentData (captured at instantiation), then fall back to appointment object
                $timeValue = $appointmentData['selected_time'] ?? $appointment->selected_time ?? $appointment->schedule_time ?? null;
            ?>
            <?php if(!empty($timeValue) && $timeValue !== ''): ?>
                <?php echo e($timeValue); ?>

            <?php else: ?>
                <em>Time not specified</em>
            <?php endif; ?>
        </li>
        <li><strong>Consultation Type:</strong> 
            <?php echo e($appointment->category ?? $appointmentData['category'] ?? 'N/A'); ?> - 
            <?php echo e($appointment->case_name ?? $appointmentData['case_name'] ?? 'N/A'); ?>

        </li>
        <li><strong>Branch:</strong> <?php echo e($appointment->selected_branch ?? $appointmentData['selected_branch'] ?? 'N/A'); ?></li>
    </ul>
    
    <p>This may be due to incomplete information, verification issues, or scheduling conflicts.</p>
    
    <p>We apologize for any inconvenience this may cause. Please feel free to submit another request with complete and accurate information, or contact us if you have any questions.</p>
    
    <p>Best regards,<br><strong>LegalConnect Team</strong></p>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\emails\appointment_denied.blade.php ENDPATH**/ ?>