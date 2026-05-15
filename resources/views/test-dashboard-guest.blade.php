<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Test Dashboard - LegalConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #667eea;
            --success: #28a745;
            --danger: #dc3545;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 600px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .card-body {
            padding: 40px;
            text-align: center;
        }

        .test-icon {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        h1 {
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 15px;
        }

        .status-message {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .test-list {
            text-align: left;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .test-list h5 {
            color: var(--primary);
            margin-bottom: 15px;
            font-weight: 600;
        }

        .test-item {
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .test-item:last-child {
            border-bottom: none;
        }

        .test-item i {
            color: var(--success);
            font-size: 1.2rem;
            min-width: 25px;
        }

        .btn-login {
            background: var(--primary);
            color: white;
            padding: 12px 40px;
            border-radius: 5px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
        }

        .btn-login:hover {
            background: #5568d3;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-test-public {
            background: var(--success);
            color: white;
            padding: 12px 40px;
            border-radius: 5px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
        }

        .btn-test-public:hover {
            background: #218838;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .button-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid var(--primary);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: left;
        }

        .info-box strong {
            color: var(--primary);
        }

        .info-box p {
            margin: 0;
            color: #333;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <div class="test-icon">
                    <i class="fas fa-flask-vial"></i>
                </div>

                <h1>System Test Dashboard</h1>

                <p class="status-message">
                    To access the full test dashboard and verify your WebRTC calling system configuration, 
                    you need to log in first.
                </p>

                <div class="info-box">
                    <p><strong><i class="fas fa-info-circle me-2"></i>Public Test Available</strong></p>
                    <p>You can run a basic connection test without logging in to verify Laravel routing is working.</p>
                </div>

                <div class="test-list">
                    <h5><i class="fas fa-list-check me-2"></i>Available Tests</h5>
                    <div class="test-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Basic Connection (Public)</span>
                    </div>
                    <div class="test-item">
                        <i class="fas fa-lock me-1"></i>
                        <span>Authentication Status (Login required)</span>
                    </div>
                    <div class="test-item">
                        <i class="fas fa-lock me-1"></i>
                        <span>Broadcasting/Pusher Config (Login required)</span>
                    </div>
                    <div class="test-item">
                        <i class="fas fa-lock me-1"></i>
                        <span>Database Connection (Login required)</span>
                    </div>
                    <div class="test-item">
                        <i class="fas fa-lock me-1"></i>
                        <span>Call Controller Status (Login required)</span>
                    </div>
                    <div class="test-item">
                        <i class="fas fa-lock me-1"></i>
                        <span>Call Page Accessibility (Login required)</span>
                    </div>
                    <div class="test-item">
                        <i class="fas fa-lock me-1"></i>
                        <span>WebRTC Configuration (Login required)</span>
                    </div>
                    <div class="test-item">
                        <i class="fas fa-lock me-1"></i>
                        <span>Complete System Check (Login required)</span>
                    </div>
                </div>

                <div class="button-group">
                    <a href="{{ route('test.connection') }}" target="_blank" class="btn-test-public">
                        <i class="fas fa-play-circle me-1"></i> Test Connection
                    </a>
                    <a href="{{ route('login') }}" class="btn-login">
                        <i class="fas fa-sign-in-alt me-1"></i> Log In
                    </a>
                </div>

                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
                    <p style="color: #999; font-size: 0.9rem; margin-bottom: 0;">
                        <i class="fas fa-shield-alt me-1"></i> 
                        Your security and privacy are important to us. All tests are safe and don't affect production data.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
