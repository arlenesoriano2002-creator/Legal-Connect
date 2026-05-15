<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Password Reset Code</title>
</head>
<body style="margin:0; padding:24px; background:#f5f7fb; font-family:Arial, sans-serif; color:#1f2937;">
    <div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:12px; padding:32px; border:1px solid #e5e7eb;">
        <h2 style="margin-top:0; color:#2f4050;">LegalConnect Admin Password Reset</h2>
        <p>Hello <?php echo e($user->name); ?>,</p>
        <p>We received a request to reset the password for your admin account.</p>

        <div style="margin:24px 0; padding:20px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; text-align:center;">
            <div style="font-size:13px; letter-spacing:1px; color:#166534; text-transform:uppercase; margin-bottom:8px;">Verification Code</div>
            <div style="font-size:32px; font-weight:700; letter-spacing:8px; color:#14532d;"><?php echo e($verificationCode); ?></div>
        </div>

        <p>This code will expire on <strong><?php echo e($expiresAt->format('M d, Y h:i A')); ?></strong>.</p>
        <p>If you did not request this change, you can ignore this email and your password will remain unchanged.</p>

        <p style="margin-bottom:0;">LegalConnect</p>
    </div>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\emails\admin_password_reset_code.blade.php ENDPATH**/ ?>