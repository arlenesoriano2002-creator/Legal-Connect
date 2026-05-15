<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Unauthorized Access</title>
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    
    <?php echo $__env->make('partials.global-error-handler', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Inter', sans-serif;
        }
        .error-container {
            text-align: center;
            background: white;
            padding: 60px 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            margin: 20px;
        }
        .error-code {
            font-size: 72px;
            font-weight: 700;
            color: #dc3545;
            margin: 0;
            line-height: 1;
        }
        .error-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin: 20px 0;
        }
        .error-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .role-badge {
            display: inline-block;
            background-color: #ffebee;
            color: #c62828;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin: 20px 0;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .btn-group a, .btn-group button {
            flex: 1;
            min-width: 150px;
        }
        .security-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 13px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">403</h1>
        <h2 class="error-title">Unauthorized Access</h2>
        
        <p class="error-message">
            You do not have permission to access this page.
        </p>
        
        <?php if(isset($userRole)): ?>
            <div class="role-badge">
                <i class="fas fa-user-lock me-2"></i>
                Your Role: <strong><?php echo e($userRole); ?></strong>
            </div>
        <?php endif; ?>
        
        <p class="error-message" style="margin-top: 20px; margin-bottom: 20px;">
            This page is restricted to <strong>client users only</strong>. 
            If you believe this is an error, please contact the administrator.
        </p>
        
        <div class="btn-group">
            <a href="/" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>Home
            </a>
            <button onclick="history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Go Back
            </button>
        </div>
        
        <div class="security-info">
            <p>
                <i class="fas fa-shield-alt me-2"></i>
                Access is controlled by Role-Based Access Control (RBAC) middleware
            </p>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\errors\403.blade.php ENDPATH**/ ?>