<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebRTC Call System - Diagnostics</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .check {
            margin: 15px 0;
            padding: 15px;
            border-left: 4px solid #ccc;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .check.pass {
            border-left-color: #4CAF50;
            background: #f1f8f4;
        }
        .check.fail {
            border-left-color: #f44336;
            background: #fef1f1;
        }
        .check.warning {
            border-left-color: #ff9800;
            background: #fff8f1;
        }
        .status {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .pass .status::before {
            content: "✓ ";
            color: #4CAF50;
        }
        .fail .status::before {
            content: "✗ ";
            color: #f44336;
        }
        .warning .status::before {
            content: "⚠ ";
            color: #ff9800;
        }
        .details {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            font-family: monospace;
        }
        button {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
            font-size: 14px;
        }
        button:hover {
            background: #5568d3;
        }
        .button-group {
            margin: 20px 0;
        }
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .summary {
            margin: 20px 0;
            padding: 15px;
            background: #f0f7ff;
            border-radius: 4px;
            border-left: 4px solid #2196F3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 WebRTC Calling System - Diagnostics</h1>
        
        <div class="summary">
            <strong>Quick Check:</strong> This page runs diagnostics to verify the WebRTC call system is properly configured.
        </div>

        <div class="button-group">
            <button onclick="runAllChecks()">Run All Diagnostics</button>
            <button onclick="resetResults()">Clear Results</button>
        </div>

        <div id="results"></div>
    </div>

    <script>
        const checks = [];
        const resultsDiv = document.getElementById('results');

        function addCheck(name, passed, details) {
            checks.push({ name, passed, details });
            displayResult(name, passed, details);
        }

        function displayResult(name, passed, details) {
            const status = passed ? 'pass' : 'fail';
            const html = `
                <div class="check ${status}">
                    <div class="status">${name}</div>
                    <div class="details">${details}</div>
                </div>
            `;
            resultsDiv.innerHTML += html;
        }

        function resetResults() {
            resultsDiv.innerHTML = '';
            checks.length = 0;
        }

        async function runAllChecks() {
            resetResults();
            resultsDiv.innerHTML = '<div class="check"><div class="status">Running diagnostics...</div></div>';
            
            // Wait a moment for UI update
            await new Promise(r => setTimeout(r, 100));
            resetResults();

            // Check 1: HTTPS/LocalHost
            checkSecurityContext();

            // Check 2: LocalStorage
            checkLocalStorage();

            // Check 3: Cookies
            checkCookies();

            // Check 4: WebRTC Support
            checkWebRTC();

            // Check 5: Current User
            checkCurrentUser();

            // Check 6: WebRTC Call Manager
            checkCallManager();

            // Check 7: WebSocket/Pusher
            await checkPusher();

            // Check 8: Broadcasting Auth Endpoint
            await checkBroadcastingAuth();

            // Check 9: Call Buttons
            checkCallButtons();

            // Check 10: Database Route
            await checkCallInitEndpoint();

            // Summary
            displaySummary();
        }

        function checkSecurityContext() {
            const isHttps = window.location.protocol === 'https:';
            const isLocalhost = window.location.hostname === 'localhost' || 
                                window.location.hostname === '127.0.0.1';
            const isLocal = isHttps || isLocalhost;
            
            addCheck(
                'Security Context',
                isLocal,
                `Protocol: ${window.location.protocol}, Hostname: ${window.location.hostname}<br>` +
                `Status: ${isLocal ? 'OK - Local or HTTPS' : 'WARNING - Should use HTTPS in production'}`
            );
        }

        function checkLocalStorage() {
            try {
                localStorage.setItem('test', 'test');
                localStorage.removeItem('test');
                addCheck('LocalStorage', true, 'LocalStorage is available');
            } catch (e) {
                addCheck('LocalStorage', false, 'LocalStorage is not available: ' + e.message);
            }
        }

        function checkCookies() {
            try {
                document.cookie = 'test=test;path=/';
                const hasCookie = document.cookie.includes('test');
                document.cookie = 'test=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;';
                addCheck('Cookies', hasCookie, 'Cookies are ' + (hasCookie ? 'enabled' : 'disabled'));
            } catch (e) {
                addCheck('Cookies', false, 'Error checking cookies: ' + e.message);
            }
        }

        function checkWebRTC() {
            const hasWebRTC = !!(
                navigator.mediaDevices &&
                navigator.mediaDevices.getUserMedia &&
                window.RTCPeerConnection
            );
            
            addCheck(
                'WebRTC Support',
                hasWebRTC,
                'Browser: ' + navigator.userAgent.substring(0, 50) + '...<br>' +
                'MediaDevices: ' + (navigator.mediaDevices ? 'Yes' : 'No') + '<br>' +
                'RTCPeerConnection: ' + (window.RTCPeerConnection ? 'Yes' : 'No')
            );
        }

        function checkCurrentUser() {
            const hasCurrentUser = typeof window.currentUser !== 'undefined' && 
                                   window.currentUser && 
                                   window.currentUser.id;
            
            let details = '';
            if (hasCurrentUser) {
                details = `User ID: ${window.currentUser.id}, Name: ${window.currentUser.name}`;
            } else {
                details = 'window.currentUser not found. Make sure you are logged in.';
            }
            
            addCheck('Current User', hasCurrentUser, details);
        }

        function checkCallManager() {
            const hasCallManager = typeof initiateVideoCall === 'function' && 
                                   typeof initiateVideoCallAdmin === 'function';
            
            const details = hasCallManager 
                ? 'initiateVideoCall and initiateVideoCallAdmin functions are defined'
                : 'WebRTC Call Manager functions not found. Check if webrtc-call.js is loaded.';
            
            addCheck('WebRTC Call Manager', hasCallManager, details);
        }

        async function checkPusher() {
            const hasPusher = typeof Pusher !== 'undefined';
            
            let details = 'Pusher: ' + (hasPusher ? 'Loaded' : 'Not loaded');
            
            if (hasPusher) {
                details += `<br>Pusher version: ${Pusher.VERSION}`;
            }
            
            addCheck('Pusher Library', hasPusher, details);
        }

        async function checkBroadcastingAuth() {
            try {
                const response = await fetch('/broadcasting/auth', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': getCsrfToken()
                    },
                    body: JSON.stringify({ channel_name: 'private-test' })
                });
                
                const isOk = response.status === 200 || response.status === 401 || response.status === 403;
                const details = `Status: ${response.status}<br>Route exists: ${isOk ? 'Yes' : 'No'}`;
                
                addCheck('Broadcasting Auth Endpoint', isOk, details);
            } catch (e) {
                addCheck('Broadcasting Auth Endpoint', false, `Error: ${e.message}`);
            }
        }

        function checkCallButtons() {
            const buttons = [];
            
            if (document.querySelector('button[onclick="initiateVideoCall()"]')) {
                buttons.push('Client call button found');
            }
            if (document.querySelector('button[onclick="initiateVideoCallAdmin()"]')) {
                buttons.push('Admin SMS chat button found');
            }
            
            const hasButtons = buttons.length > 0;
            const details = hasButtons 
                ? buttons.join('<br>')
                : 'No call buttons found on this page';
            
            addCheck('Call Buttons', hasButtons, details);
        }

        async function checkCallInitEndpoint() {
            try {
                // This endpoint returns 404 without auth, but that's OK - we just want to verify it exists
                const response = await fetch('/call/history', {
                    method: 'GET'
                });
                
                // 401/403 means route exists but auth failed (expected)
                // 404 means route doesn't exist (bad)
                const isOk = response.status !== 404;
                const details = `Status: ${response.status}<br>Route exists: ${isOk ? 'Yes' : 'No'}`;
                
                addCheck('Call API Endpoints', isOk, details);
            } catch (e) {
                addCheck('Call API Endpoints', false, `Error: ${e.message}`);
            }
        }

        function displaySummary() {
            const passed = checks.filter(c => c.passed).length;
            const total = checks.length;
            const percentage = Math.round((passed / total) * 100);
            
            const status = percentage === 100 ? 'pass' : percentage >= 70 ? 'warning' : 'fail';
            const message = percentage === 100 
                ? 'All checks passed! Your WebRTC system is ready.'
                : percentage >= 70
                ? 'Most checks passed. Some features may not work correctly.'
                : 'Several checks failed. Review the issues above.';
            
            const html = `
                <div class="check ${status}">
                    <div class="status">Summary: ${passed}/${total} checks passed (${percentage}%)</div>
                    <div class="details">${message}</div>
                </div>
            `;
            
            resultsDiv.innerHTML += html;
        }

        function getCsrfToken() {
            const token = document.querySelector('meta[name="csrf-token"]');
            return token ? token.getAttribute('content') : '';
        }

        // Run checks automatically on page load
        window.addEventListener('load', () => {
            console.log('Diagnostics page loaded. Click "Run All Diagnostics" to start.');
        });
    </script>
</body>
</html>
