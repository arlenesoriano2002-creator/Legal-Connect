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
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 1200px;
        }

        .test-header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }

        .test-header h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .test-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .user-info {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .user-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            background: var(--primary);
            color: white;
            border-radius: 20px;
            font-weight: 500;
        }

        .test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .test-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .test-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .test-card-header {
            padding: 20px;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .test-card-icon {
            font-size: 1.8rem;
            opacity: 0.8;
        }

        .test-card-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin: 0;
        }

        .test-card-body {
            padding: 20px;
        }

        .test-card-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .test-card-footer {
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }

        .btn-test {
            flex: 1;
            padding: 10px;
            font-size: 0.9rem;
            border-radius: 5px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-test-primary {
            background: var(--primary);
            color: white;
        }

        .btn-test-primary:hover {
            background: #5568d3;
            color: white;
        }

        .test-all-btn {
            background: var(--success);
            color: white;
            padding: 15px 30px;
            border-radius: 5px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            justify-content: center;
        }

        .test-all-btn:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .status-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .status-section h3 {
            color: var(--primary);
            margin-bottom: 20px;
            font-weight: 600;
        }

        .status-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #ddd;
        }

        .status-item.pass {
            border-left-color: var(--success);
            background: #f0f9f6;
        }

        .status-item.fail {
            border-left-color: var(--danger);
            background: #fef5f5;
        }

        .status-item.warning {
            border-left-color: var(--warning);
            background: #fffbf0;
        }

        .status-icon {
            font-size: 1.3rem;
            margin-right: 12px;
            min-width: 30px;
            text-align: center;
        }

        .status-item.pass .status-icon {
            color: var(--success);
        }

        .status-item.fail .status-icon {
            color: var(--danger);
        }

        .status-item.warning .status-icon {
            color: var(--warning);
        }

        .status-text {
            flex: 1;
        }

        .status-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 2px;
        }

        .status-message {
            font-size: 0.9rem;
            color: #666;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .quick-links {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .quick-links h3 {
            color: var(--primary);
            margin-bottom: 20px;
            font-weight: 600;
        }

        .quick-links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .quick-link {
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            color: var(--primary);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .quick-link:hover {
            border-color: var(--primary);
            background: #f8f9ff;
            color: var(--primary);
        }

        .quick-link i {
            display: block;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .footer-text {
            text-align: center;
            color: white;
            margin-top: 40px;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .test-header h1 {
                font-size: 1.8rem;
            }

            .test-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="test-header">
            <h1><i class="fas fa-flask-vial me-2"></i>System Test Dashboard</h1>
            <p>Verify your LegalConnect WebRTC calling system is properly configured</p>
        </div>

        <!-- User Info -->
        <div class="user-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-2">Authenticated User</h5>
                    <div class="user-badge">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo e($user->name ?? 'User'); ?> (ID: <?php echo e($user->id); ?>)</span>
                    </div>
                </div>
                <div class="text-muted">
                    <small><?php echo e($user->email ?? 'No email'); ?></small>
                </div>
            </div>
        </div>

        <!-- Test Cards -->
        <div class="test-grid">
            <!-- Basic Connection Test -->
            <div class="test-card">
                <div class="test-card-header">
                    <i class="fas fa-plug test-card-icon"></i>
                    <h5 class="test-card-title">Basic Connection</h5>
                </div>
                <div class="test-card-body">
                    <p class="test-card-description">Verify Laravel routing is working</p>
                    <div class="test-card-footer">
                        <a href="<?php echo e(route('test.connection')); ?>" class="btn-test btn-test-primary" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Test
                        </a>
                    </div>
                </div>
            </div>

            <!-- Authentication Test -->
            <div class="test-card">
                <div class="test-card-header">
                    <i class="fas fa-lock test-card-icon"></i>
                    <h5 class="test-card-title">Authentication</h5>
                </div>
                <div class="test-card-body">
                    <p class="test-card-description">Verify user authentication status</p>
                    <div class="test-card-footer">
                        <a href="<?php echo e(route('test.auth')); ?>" class="btn-test btn-test-primary" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Test
                        </a>
                    </div>
                </div>
            </div>

            <!-- Broadcasting Test -->
            <div class="test-card">
                <div class="test-card-header">
                    <i class="fas fa-broadcast-tower test-card-icon"></i>
                    <h5 class="test-card-title">Broadcasting</h5>
                </div>
                <div class="test-card-body">
                    <p class="test-card-description">Check broadcasting/Pusher configuration</p>
                    <div class="test-card-footer">
                        <a href="<?php echo e(route('test.broadcasting')); ?>" class="btn-test btn-test-primary" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Test
                        </a>
                    </div>
                </div>
            </div>

            <!-- Database Test -->
            <div class="test-card">
                <div class="test-card-header">
                    <i class="fas fa-database test-card-icon"></i>
                    <h5 class="test-card-title">Database</h5>
                </div>
                <div class="test-card-body">
                    <p class="test-card-description">Verify database connection and calls table</p>
                    <div class="test-card-footer">
                        <a href="<?php echo e(route('test.database')); ?>" class="btn-test btn-test-primary" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Test
                        </a>
                    </div>
                </div>
            </div>

            <!-- Call Controller Test -->
            <div class="test-card">
                <div class="test-card-header">
                    <i class="fas fa-phone-alt test-card-icon"></i>
                    <h5 class="test-card-title">Call Controller</h5>
                </div>
                <div class="test-card-body">
                    <p class="test-card-description">Check call controller accessibility</p>
                    <div class="test-card-footer">
                        <a href="<?php echo e(route('test.call-controller')); ?>" class="btn-test btn-test-primary" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Test
                        </a>
                    </div>
                </div>
            </div>

            <!-- Call Page Test -->
            <div class="test-card">
                <div class="test-card-header">
                    <i class="fas fa-video test-card-icon"></i>
                    <h5 class="test-card-title">Call Page</h5>
                </div>
                <div class="test-card-body">
                    <p class="test-card-description">Verify call page is accessible</p>
                    <div class="test-card-footer">
                        <a href="<?php echo e(route('test.call-page')); ?>" class="btn-test btn-test-primary" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Test
                        </a>
                    </div>
                </div>
            </div>

            <!-- Broadcasting Auth Test -->
            <div class="test-card">
                <div class="test-card-header">
                    <i class="fas fa-key test-card-icon"></i>
                    <h5 class="test-card-title">Broadcasting Auth</h5>
                </div>
                <div class="test-card-body">
                    <p class="test-card-description">Test broadcasting authentication</p>
                    <div class="test-card-footer">
                        <a href="<?php echo e(route('test.broadcasting-auth')); ?>" class="btn-test btn-test-primary" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Test
                        </a>
                    </div>
                </div>
            </div>

            <!-- WebRTC Test -->
            <div class="test-card">
                <div class="test-card-header">
                    <i class="fas fa-wifi test-card-icon"></i>
                    <h5 class="test-card-title">WebRTC Config</h5>
                </div>
                <div class="test-card-body">
                    <p class="test-card-description">Verify WebRTC configuration</p>
                    <div class="test-card-footer">
                        <a href="<?php echo e(route('test.webrtc')); ?>" class="btn-test btn-test-primary" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Test
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Full System Test -->
        <div class="status-section">
            <h3><i class="fas fa-star me-2"></i>Complete System Check</h3>
            <p class="mb-3 text-muted">Run all tests at once to get a complete status report</p>
            <button class="test-all-btn" onclick="runFullSystemTest()">
                <i class="fas fa-play-circle"></i> Run Full System Test
            </button>
            <div id="full-test-results" style="margin-top: 20px;"></div>
        </div>

        <!-- Quick Links -->
        <div class="quick-links">
            <h3><i class="fas fa-link me-2"></i>Quick Links</h3>
            <div class="quick-links-grid">
                <a href="/diagnostics/webrtc" class="quick-link">
                    <i class="fas fa-stethoscope"></i>
                    <span>WebRTC Diagnostics</span>
                </a>
                <a href="/welcome" class="quick-link">
                    <i class="fas fa-home"></i>
                    <span>Home Page</span>
                </a>
                <a href="/admin/system-chat" class="quick-link">
                    <i class="fas fa-comments"></i>
                    <span>System Chat</span>
                </a>
                <a href="/admindashboard" class="quick-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Admin Dashboard</span>
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-text">
            <p>LegalConnect WebRTC System Test Dashboard | Last Updated: <?php echo e(now()->format('Y-m-d H:i:s')); ?></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function runFullSystemTest() {
            const resultsDiv = document.getElementById('full-test-results');
            resultsDiv.innerHTML = '<div class="loading"></div> Running system tests...';

            fetch('<?php echo e(route("test.system")); ?>')
                .then(response => response.json())
                .then(data => {
                    let html = '';

                    // Overall status
                    const isAllPassed = data.overall_status.includes('PASSED');
                    const statusClass = isAllPassed ? 'pass' : 'fail';
                    
                    html += `
                        <div class="status-item ${statusClass}">
                            <div class="status-icon">
                                ${isAllPassed ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>'}
                            </div>
                            <div class="status-text">
                                <div class="status-label">Overall Status</div>
                                <div class="status-message">${data.overall_status}</div>
                            </div>
                        </div>
                    `;

                    // Individual test results
                    for (const [test, result] of Object.entries(data.tests)) {
                        const isPassed = result === 'PASS';
                        const isWarning = result.includes('NOT LOGGED IN') || (result.includes('NOT') && result !== 'PASS');
                        const testClass = isPassed ? 'pass' : isWarning ? 'warning' : 'fail';
                        const icon = isPassed ? 'check-circle' : isWarning ? 'exclamation-circle' : 'times-circle';

                        html += `
                            <div class="status-item ${testClass}">
                                <div class="status-icon">
                                    <i class="fas fa-${icon}"></i>
                                </div>
                                <div class="status-text">
                                    <div class="status-label">${formatTestName(test)}</div>
                                    <div class="status-message">${result}</div>
                                </div>
                            </div>
                        `;
                    }

                    // Authenticated user info if available
                    if (data.authenticated_user) {
                        html += `
                            <div class="status-item pass" style="margin-top: 15px;">
                                <div class="status-icon">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="status-text">
                                    <div class="status-label">Authenticated As</div>
                                    <div class="status-message">${data.authenticated_user.name} (${data.authenticated_user.email})</div>
                                </div>
                            </div>
                        `;
                    }

                    resultsDiv.innerHTML = html;
                })
                .catch(error => {
                    resultsDiv.innerHTML = `
                        <div class="status-item fail">
                            <div class="status-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="status-text">
                                <div class="status-label">Error</div>
                                <div class="status-message">${error.message}</div>
                            </div>
                        </div>
                    `;
                });
        }

        function formatTestName(testName) {
            return testName
                .replace(/_/g, ' ')
                .replace(/\b\w/g, l => l.toUpperCase());
        }
    </script>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\test-dashboard.blade.php ENDPATH**/ ?>