<?php
session_start();
include "../mypbra_connect.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WebRTC Test Page</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .video-container { display: flex; margin: 20px 0; }
        video { width: 400px; height: 300px; background: #000; margin-right: 10px; }
        .controls { margin: 20px 0; }
        button { padding: 8px 16px; margin-right: 10px; cursor: pointer; }
        .log { background: #f4f4f4; padding: 10px; height: 200px; overflow-y: auto; font-family: monospace; }
        .status { margin: 10px 0; font-weight: bold; }
        .warning { color: #ff0000; }
    </style>
</head>
<body>
    <div class="container">
        <h1>WebRTC Connection Test</h1>
        
        <div class="warning">
            <p>For testing, open this page in two separate browsers or private windows and click "Start Test"</p>
        </div>
        
        <div class="status">Status: <span id="connection-status">Not Connected</span></div>
        
        <div class="video-container">
            <div>
                <h3>Local Video</h3>
                <video id="localVideo" autoplay playsinline muted></video>
            </div>
            <div>
                <h3>Remote Video</h3>
                <video id="remoteVideo" autoplay playsinline></video>
            </div>
        </div>
        
        <div class="controls">
            <button id="startTest">Start Test</button>
            <button id="toggleVideo">Toggle Video</button>
            <button id="toggleAudio">Toggle Audio</button>
        </div>
        
        <h3>Connection Log</h3>
        <div id="logBox" class="log"></div>
    </div>

    <script>
        // Configuration
        const user_id = <?php echo isset($_SESSION['id']) ? $_SESSION['id'] : 'Math.floor(Math.random() * 10000)'; ?>;
        const meeting_id = 999999; // Special testing meeting ID
        
        // WebRTC variables
        let localStream = null;
        let pc = null;
        let isInitiator = false;
        let pollingInterval = null;
        
        // DOM elements
        const localVideo = document.getElementById('localVideo');
        const remoteVideo = document.getElementById('remoteVideo');
        const startButton = document.getElementById('startTest');
        const toggleVideoButton = document.getElementById('toggleVideo');
        const toggleAudioButton = document.getElementById('toggleAudio');
        const connectionStatus = document.getElementById('connection-status');
        const logBox = document.getElementById('logBox');
        
        // Log helper function
        function log(message) {
            console.log(message);
            const entry = document.createElement('div');
            entry.textContent = new Date().toLocaleTimeString() + ': ' + message;
            logBox.appendChild(entry);
            logBox.scrollTop = logBox.scrollHeight;
        }
        
        // Initialize media streams
        async function initMedia() {
            try {
                log('Requesting camera and microphone...');
                localStream = await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: true
                });
                localVideo.srcObject = localStream;
                log('Local media ready');
                return true;
            } catch (error) {
                log('Error accessing media devices: ' + error.message);
                return false;
            }
        }
        
        // Create WebRTC peer connection
        function createPeerConnection() {
            const config = {
                iceServers: [
                    { urls: 'stun:stun.l.google.com:19302' }
                ]
            };
            
            pc = new RTCPeerConnection(config);
            log('Created peer connection');
            
            pc.onicecandidate = event => {
                if (event.candidate) {
                    log('Sending ICE candidate');
                    sendSignal(meeting_id, user_id, 'candidate', null, event.candidate);
                }
            };
            
            pc.ontrack = event => {
                log('Received remote track');
                remoteVideo.srcObject = event.streams[0];
                connectionStatus.textContent = 'Connected';
            };
            
            pc.oniceconnectionstatechange = () => {
                log('ICE connection state: ' + pc.iceConnectionState);
                if (pc.iceConnectionState === 'disconnected' || 
                    pc.iceConnectionState === 'failed' || 
                    pc.iceConnectionState === 'closed') {
                    connectionStatus.textContent = 'Disconnected';
                }
            };
            
            // Add local stream to connection
            localStream.getTracks().forEach(track => {
                pc.addTrack(track, localStream);
                log('Added local track: ' + track.kind);
            });
        }
        
        // Signaling functions
        function sendSignal(meetingId, senderId, type, sdp = null, candidate = null, receiverId = 0) {
            const data = new FormData();
            data.append('meeting_id', meetingId);
            data.append('sender_id', senderId);
            data.append('type', type);
            data.append('receiver_id', receiverId);
            
            if (sdp) data.append('sdp', JSON.stringify(sdp));
            if (candidate) data.append('candidate', JSON.stringify(candidate));
            
            log('Sending signal: ' + type);
            
            fetch('send_signal.php', {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(result => {
                if (!result.success) {
                    log('Signal send error: ' + (result.error || 'Unknown error'));
                }
            })
            .catch(error => {
                log('Signal send failed: ' + error.message);
            });
        }
        
        function startSignalPolling() {
            if (pollingInterval) clearInterval(pollingInterval);
            
            pollingInterval = setInterval(() => {
                fetch(`gets_signal.php?meeting_id=${meeting_id}&user_id=${user_id}`)
                    .then(response => response.json())
                    .then(signals => {
                        if (signals && signals.length) {
                            signals.forEach(processSignal);
                        }
                    })
                    .catch(error => {
                        log('Signal polling error: ' + error.message);
                    });
            }, 1000);
            
            log('Started signal polling');
        }
        
        async function processSignal(signal) {
            log('Processing signal: ' + signal.type + ' from user ' + signal.sender_id);
            
            switch(signal.type) {
                case 'join':
                    if (!isInitiator) {
                        isInitiator = true;
                        createPeerConnection();
                        log('Creating offer as initiator');
                        const offer = await pc.createOffer();
                        await pc.setLocalDescription(offer);
                        sendSignal(meeting_id, user_id, 'offer', pc.localDescription);
                    }
                    break;
                
                case 'offer':
                    if (!pc) createPeerConnection();
                    const offer = JSON.parse(signal.sdp);
                    log('Received offer, setting remote description');
                    await pc.setRemoteDescription(new RTCSessionDescription(offer));
                    log('Creating answer');
                    const answer = await pc.createAnswer();
                    await pc.setLocalDescription(answer);
                    sendSignal(meeting_id, user_id, 'answer', pc.localDescription, null, signal.sender_id);
                    break;
                
                case 'answer':
                    if (pc) {
                        const answer = JSON.parse(signal.sdp);
                        log('Received answer, setting remote description');
                        await pc.setRemoteDescription(new RTCSessionDescription(answer));
                    }
                    break;
                
                case 'candidate':
                    if (pc) {
                        const candidate = JSON.parse(signal.candidate);
                        log('Adding ICE candidate');
                        await pc.addIceCandidate(new RTCIceCandidate(candidate));
                    }
                    break;
            }
        }
        
        // Button handlers
        startButton.addEventListener('click', async () => {
            startButton.disabled = true;
            connectionStatus.textContent = 'Connecting...';
            
            if (!localStream) {
                const success = await initMedia();
                if (!success) {
                    startButton.disabled = false;
                    connectionStatus.textContent = 'Media Error';
                    return;
                }
            }
            
            // Start signaling
            startSignalPolling();
            
            // Send join signal
            sendSignal(meeting_id, user_id, 'join');
            log('Sent join signal');
        });
        
        toggleVideoButton.addEventListener('click', () => {
            if (localStream) {
                const videoTrack = localStream.getVideoTracks()[0];
                if (videoTrack) {
                    videoTrack.enabled = !videoTrack.enabled;
                    log('Video ' + (videoTrack.enabled ? 'enabled' : 'disabled'));
                }
            }
        });
        
        toggleAudioButton.addEventListener('click', () => {
            if (localStream) {
                const audioTrack = localStream.getAudioTracks()[0];
                if (audioTrack) {
                    audioTrack.enabled = !audioTrack.enabled;
                    log('Audio ' + (audioTrack.enabled ? 'enabled' : 'disabled'));
                }
            }
        });
        
        // Connection cleanup
        window.addEventListener('beforeunload', () => {
            if (pollingInterval) clearInterval(pollingInterval);
            if (pc) pc.close();
            if (localStream) localStream.getTracks().forEach(track => track.stop());
        });
    </script>
</body>
</html>
