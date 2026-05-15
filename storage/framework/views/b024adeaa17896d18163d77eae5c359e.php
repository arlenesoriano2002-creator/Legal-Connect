<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($receiver->name); ?> - Video Call | Legal Connect</title>
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #000;
            color: #fff;
            overflow: hidden;
        }

        .call-container {
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: #1a1a1a;
        }

        .call-header {
            background: rgba(0, 0, 0, 0.7);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #333;
            z-index: 10;
        }

        .header-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .call-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        .call-user-info h2 {
            font-size: 16px;
            margin: 0;
        }

        .call-status {
            font-size: 12px;
            color: #aaa;
            margin-top: 2px;
        }

        .call-duration {
            font-size: 14px;
            font-weight: bold;
            color: #4CAF50;
        }

        /* Video Container */
        .video-container {
            flex: 1;
            display: flex;
            gap: 10px;
            padding: 10px;
            background: #000;
            position: relative;
            overflow: hidden;
        }

        .video-wrapper {
            flex: 1;
            position: relative;
            background: #222;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .remote-wrapper {
            flex: 2;
        }

        .local-wrapper {
            flex: 0.8;
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: 25%;
            min-width: 200px;
            max-width: 400px;
            border: 3px solid #667eea;
            z-index: 5;
        }

        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #000;
        }

        /* No Video Placeholder */
        .no-video {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 15px;
            color: #aaa;
        }

        .no-video-icon {
            font-size: 48px;
        }

        /* Call Controls */
        .call-controls {
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
            border-top: 1px solid #333;
            z-index: 10;
        }

        .call-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all 0.3s ease;
            color: #fff;
            background: #333;
        }

        .call-btn:hover {
            transform: scale(1.1);
            background: #444;
        }

        .call-btn.active {
            background: #667eea;
        }

        .call-btn.active:hover {
            background: #5568d3;
        }

        .call-btn.muted {
            background: #f44336;
        }

        .call-btn.muted:hover {
            background: #da190b;
        }

        .call-btn.end-btn {
            background: #f44336;
        }

        .call-btn.end-btn:hover {
            background: #da190b;
            transform: scale(1.15);
        }

        /* Call Status Indicator */
        .status-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.7);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            color: #4CAF50;
            text-transform: uppercase;
            z-index: 3;
        }

        /* Loading Spinner */
        .spinner {
            border: 4px solid #333;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Incoming Call Notification */
        .incoming-call-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
            display: none;
        }

        .incoming-call-modal.show {
            display: flex;
        }

        .incoming-call-card {
            background: #1a1a1a;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            max-width: 400px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }

        .incoming-caller-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 40px;
            margin: 0 auto 20px;
        }

        .incoming-caller-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .incoming-call-type {
            font-size: 14px;
            color: #aaa;
            margin-bottom: 30px;
        }

        .incoming-call-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .incoming-call-actions .call-btn {
            width: 60px;
            height: 60px;
        }

        .accept-btn {
            background: #4CAF50 !important;
        }

        .accept-btn:hover {
            background: #45a049 !important;
        }

        .reject-btn {
            background: #f44336 !important;
        }

        .reject-btn:hover {
            background: #da190b !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .local-wrapper {
                width: 35%;
                bottom: 100px;
                right: 10px;
            }

            .call-controls {
                padding: 15px;
                gap: 10px;
            }

            .call-btn {
                width: 45px;
                height: 45px;
                font-size: 18px;
            }

            .call-header {
                padding: 10px 15px;
            }

            .call-user-info h2 {
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .local-wrapper {
                width: 45%;
            }

            .call-btn {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }

            .call-controls {
                padding: 10px;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="call-container">
        <!-- Call Header -->
        <div class="call-header">
            <div class="header-info">
                <div class="call-user-avatar"><?php echo e(substr($receiver->name, 0, 1)); ?></div>
                <div class="call-user-info">
                    <h2><?php echo e($receiver->name); ?></h2>
                    <div class="call-status" id="callStatus">Connecting...</div>
                </div>
            </div>
            <div class="call-duration" id="callDuration" style="display: none;">00:00</div>
        </div>

        <!-- Video Container -->
        <div class="video-container">
            <!-- Remote Video -->
            <div class="video-wrapper remote-wrapper">
                <div class="status-badge" id="remoteStatusBadge">Waiting for connection...</div>
                <video id="remoteVideo" autoplay playsinline muted></video>
                <div class="no-video" id="remoteNoVideo">
                    <div class="no-video-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <div>Waiting for <?php echo e($receiver->name); ?>...</div>
                </div>
            </div>

            <!-- Local Video -->
            <div class="video-wrapper local-wrapper">
                <video id="localVideo" autoplay playsinline muted></video>
                <div class="no-video" id="localNoVideo">
                    <div class="spinner"></div>
                    <div>Initializing camera...</div>
                </div>
            </div>
        </div>

        <!-- Call Controls -->
        <div class="call-controls">
            <button class="call-btn active" id="toggleAudio" title="Toggle Audio">
                <i class="fas fa-microphone"></i>
            </button>
            <button class="call-btn active" id="toggleVideo" title="Toggle Video">
                <i class="fas fa-video"></i>
            </button>
            <button class="call-btn end-btn" id="endCall" title="End Call">
                <i class="fas fa-phone"></i>
            </button>
        </div>
    </div>

    <!-- Incoming Call Modal -->
    <div class="incoming-call-modal" id="incomingCallModal">
        <div class="incoming-call-card">
            <div class="incoming-caller-avatar" id="incomingCallerAvatar">?</div>
            <div class="incoming-caller-name" id="incomingCallerName">Incoming Call</div>
            <div class="incoming-call-type" id="incomingCallType">Video call</div>
            <div class="incoming-call-actions">
                <button class="call-btn accept-btn" id="acceptCallBtn" title="Accept Call">
                    <i class="fas fa-phone"></i>
                </button>
                <button class="call-btn reject-btn" id="rejectCallBtn" title="Reject Call">
                    <i class="fas fa-phone"></i>
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/pusher-js@7.2.0/dist/web/pusher.min.js"></script>
    <script>
        // Global state
        const callState = {
            callId: null,
            receiverId: <?php echo e($receiver->id); ?>,
            currentUserId: <?php echo e(auth()->id()); ?>,
            isInitiator: null,
            peerConnection: null,
            localStream: null,
            remoteStream: null,
            audioEnabled: true,
            videoEnabled: true,
            callStatus: 'initializing',
            startTime: null,
            pusher: null,
            channel: null,
        };

        // STUN and TURN servers
        const iceServers = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' },
                { urls: 'stun:stun2.l.google.com:19302' },
                { urls: 'stun:stun3.l.google.com:19302' },
                { urls: 'stun:stun4.l.google.com:19302' },
            ]
        };

        // Initialize Pusher
        function initializePusher() {
            callState.pusher = new Pusher('<?php echo e(env('PUSHER_APP_KEY')); ?>', {
                cluster: '<?php echo e(env('PUSHER_APP_CLUSTER')); ?>',
                forceTLS: true,
                authEndpoint: '<?php echo e(route('broadcasting.auth')); ?>',
                auth: {
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    }
                }
            });

            // Subscribe to private channel
            callState.channel = callState.pusher.subscribe(`private-call.user.${callState.currentUserId}`);

            // Listen for call events
            callState.channel.bind('call.initiated', onCallInitiated);
            callState.channel.bind('call.answered', onCallAnswered);
            callState.channel.bind('call.rejected', onCallRejected);
            callState.channel.bind('call.ended', onCallEnded);
            callState.channel.bind('webrtc.sdp.offer', onSdpOffer);
            callState.channel.bind('webrtc.sdp.answer', onSdpAnswer);
            callState.channel.bind('webrtc.ice.candidate', onIceCandidate);
        }

        // Initialize WebRTC
        async function initializeWebRTC() {
            try {
                // Get local media stream
                const stream = await navigator.mediaDevices.getUserMedia({
                    audio: true,
                    video: { width: { min: 640, ideal: 1280, max: 1920 }, height: { min: 480, ideal: 720, max: 1080 } }
                });

                callState.localStream = stream;
                const localVideo = document.getElementById('localVideo');
                localVideo.srcObject = stream;

                // Hide loading spinner
                document.getElementById('localNoVideo').style.display = 'none';

                // Create peer connection
                createPeerConnection();

                // Add tracks to peer connection
                stream.getTracks().forEach(track => {
                    callState.peerConnection.addTrack(track, stream);
                });

                // Update status
                updateCallStatus('Ready to call');

                return true;
            } catch (error) {
                console.error('Error accessing media devices:', error);
                alert('Unable to access camera/microphone. Please check permissions.');
                updateCallStatus('Error: ' + error.message);
                return false;
            }
        }

        // Create peer connection
        function createPeerConnection() {
            callState.peerConnection = new RTCPeerConnection(iceServers);

            // Handle incoming remote tracks
            callState.peerConnection.ontrack = (event) => {
                console.log('Remote track received:', event.track.kind);
                if (!callState.remoteStream) {
                    callState.remoteStream = new MediaStream();
                }
                callState.remoteStream.addTrack(event.track);
                const remoteVideo = document.getElementById('remoteVideo');
                remoteVideo.srcObject = callState.remoteStream;
                document.getElementById('remoteNoVideo').style.display = 'none';
            };

            // Handle ICE candidates
            callState.peerConnection.onicecandidate = (event) => {
                if (event.candidate) {
                    sendIceCandidate(event.candidate);
                }
            };

            // Monitor connection state
            callState.peerConnection.onconnectionstatechange = () => {
                console.log('Connection state:', callState.peerConnection.connectionState);
                if (callState.peerConnection.connectionState === 'disconnected' ||
                    callState.peerConnection.connectionState === 'failed' ||
                    callState.peerConnection.connectionState === 'closed') {
                    console.log('Peer connection disconnected');
                }
            };
        }

        // Initiate call
        async function initiateCall() {
            try {
                // Create call in database
                const response = await fetch('<?php echo e(route('call.initiate')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        receiver_id: callState.receiverId,
                        call_type: 'video'
                    })
                });

                const data = await response.json();
                if (data.success) {
                    callState.callId = data.call_id;
                    callState.isInitiator = true;
                    updateCallStatus('Calling <?php echo e($receiver->name); ?>...');

                    // Create and send SDP offer
                    const offer = await callState.peerConnection.createOffer();
                    await callState.peerConnection.setLocalDescription(offer);
                    sendSdpOffer(offer);
                }
            } catch (error) {
                console.error('Error initiating call:', error);
                alert('Failed to initiate call');
            }
        }

        // Accept call
        async function acceptCall() {
            try {
                // Answer call
                const response = await fetch('<?php echo e(route('call.answer')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        call_id: callState.callId
                    })
                });

                if (response.ok) {
                    document.getElementById('incomingCallModal').classList.remove('show');
                    updateCallStatus('Call connected');
                    startCallTimer();
                }
            } catch (error) {
                console.error('Error accepting call:', error);
            }
        }

        // Reject call
        async function rejectCall() {
            try {
                await fetch('<?php echo e(route('call.reject')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        call_id: callState.callId,
                        reason: 'User rejected'
                    })
                });
                endCallSession();
            } catch (error) {
                console.error('Error rejecting call:', error);
            }
        }

        // End call
        async function endCallSession() {
            try {
                if (callState.callId) {
                    await fetch('<?php echo e(route('call.end')); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            call_id: callState.callId
                        })
                    });
                }

                // Close peer connection
                if (callState.peerConnection) {
                    callState.peerConnection.close();
                }

                // Stop local stream
                if (callState.localStream) {
                    callState.localStream.getTracks().forEach(track => track.stop());
                }

                // Close window after 2 seconds
                setTimeout(() => {
                    window.close();
                }, 2000);
            } catch (error) {
                console.error('Error ending call:', error);
                window.close();
            }
        }

        // Send SDP offer
        async function sendSdpOffer(offer) {
            try {
                await fetch('<?php echo e(route('call.sdp-offer')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        call_id: callState.callId,
                        sdp: offer.sdp
                    })
                });
            } catch (error) {
                console.error('Error sending SDP offer:', error);
            }
        }

        // Send SDP answer
        async function sendSdpAnswer(answer) {
            try {
                await fetch('<?php echo e(route('call.sdp-answer')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        call_id: callState.callId,
                        sdp: answer.sdp
                    })
                });
            } catch (error) {
                console.error('Error sending SDP answer:', error);
            }
        }

        // Send ICE candidate
        async function sendIceCandidate(candidate) {
            try {
                await fetch('<?php echo e(route('call.ice-candidate')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        call_id: callState.callId,
                        recipient_id: callState.receiverId,
                        candidate: {
                            candidate: candidate.candidate,
                            sdpMLineIndex: candidate.sdpMLineIndex,
                            sdpMid: candidate.sdpMid
                        }
                    })
                });
            } catch (error) {
                console.error('Error sending ICE candidate:', error);
            }
        }

        // Event handlers for broadcast messages
        function onCallInitiated(data) {
            console.log('Call initiated:', data);
            callState.callId = data.call_id;
            callState.isInitiator = false;
            document.getElementById('incomingCallerName').textContent = data.initiator_name;
            document.getElementById('incomingCallerAvatar').textContent = data.initiator_name.charAt(0);
            document.getElementById('incomingCallModal').classList.add('show');
        }

        async function onCallAnswered(data) {
            console.log('Call answered:', data);
            updateCallStatus('Call connected');
            startCallTimer();
        }

        function onCallRejected(data) {
            console.log('Call rejected:', data);
            updateCallStatus('Call rejected');
            setTimeout(endCallSession, 2000);
        }

        function onCallEnded(data) {
            console.log('Call ended:', data);
            updateCallStatus('Call ended');
            setTimeout(endCallSession, 2000);
        }

        async function onSdpOffer(data) {
            console.log('SDP Offer received');
            try {
                await callState.peerConnection.setRemoteDescription(new RTCSessionDescription({
                    type: 'offer',
                    sdp: data.sdp
                }));

                const answer = await callState.peerConnection.createAnswer();
                await callState.peerConnection.setLocalDescription(answer);
                sendSdpAnswer(answer);
            } catch (error) {
                console.error('Error handling SDP offer:', error);
            }
        }

        async function onSdpAnswer(data) {
            console.log('SDP Answer received');
            try {
                await callState.peerConnection.setRemoteDescription(new RTCSessionDescription({
                    type: 'answer',
                    sdp: data.sdp
                }));
            } catch (error) {
                console.error('Error handling SDP answer:', error);
            }
        }

        async function onIceCandidate(data) {
            try {
                if (data.candidate && data.candidate.candidate) {
                    await callState.peerConnection.addIceCandidate(new RTCIceCandidate({
                        candidate: data.candidate.candidate,
                        sdpMLineIndex: data.candidate.sdpMLineIndex,
                        sdpMid: data.candidate.sdpMid
                    }));
                }
            } catch (error) {
                console.error('Error adding ICE candidate:', error);
            }
        }

        // Toggle audio
        document.getElementById('toggleAudio').addEventListener('click', function() {
            if (callState.localStream) {
                callState.audioEnabled = !callState.audioEnabled;
                callState.localStream.getAudioTracks().forEach(track => {
                    track.enabled = callState.audioEnabled;
                });
                this.classList.toggle('muted');
            }
        });

        // Toggle video
        document.getElementById('toggleVideo').addEventListener('click', function() {
            if (callState.localStream) {
                callState.videoEnabled = !callState.videoEnabled;
                callState.localStream.getVideoTracks().forEach(track => {
                    track.enabled = callState.videoEnabled;
                });
                this.classList.toggle('muted');
            }
        });

        // End call button
        document.getElementById('endCall').addEventListener('click', endCallSession);

        // Accept incoming call
        document.getElementById('acceptCallBtn').addEventListener('click', acceptCall);

        // Reject incoming call
        document.getElementById('rejectCallBtn').addEventListener('click', rejectCall);

        // Update call status
        function updateCallStatus(status) {
            callState.callStatus = status;
            document.getElementById('callStatus').textContent = status;
        }

        // Start call timer
        function startCallTimer() {
            callState.startTime = Date.now();
            setInterval(() => {
                if (callState.startTime) {
                    const elapsed = Math.floor((Date.now() - callState.startTime) / 1000);
                    const mins = Math.floor(elapsed / 60);
                    const secs = elapsed % 60;
                    document.getElementById('callDuration').textContent = 
                        `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                    document.getElementById('callDuration').style.display = 'block';
                }
            }, 1000);
        }

        // Initialize on page load
        async function initializeCall() {
            try {
                // Initialize Pusher for signaling
                initializePusher();

                // Initialize WebRTC and media
                const mediaInitialized = await initializeWebRTC();
                if (!mediaInitialized) return;

                // Check URL query params to determine if we're initiating or receiving
                const urlParams = new URLSearchParams(window.location.search);
                const isInitiating = urlParams.get('initiate') === 'true';

                if (isInitiating) {
                    // Initiate the call
                    initiateCall();
                }
            } catch (error) {
                console.error('Error initializing call:', error);
                alert('Failed to initialize call');
            }
        }

        // Start on page load
        document.addEventListener('DOMContentLoaded', initializeCall);

        // Handle page unload
        window.addEventListener('beforeunload', (e) => {
            if (callState.peerConnection) {
                endCallSession();
            }
        });
    </script>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\call\room.blade.php ENDPATH**/ ?>