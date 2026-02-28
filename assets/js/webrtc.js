// WebRTC Module - Complete version with Google Meet layout support
(function() {
    'use strict';
    
    // WebRTC Configuration
    const configuration = {
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            { urls: 'stun:stun2.l.google.com:19302' },
            { urls: 'stun:stun3.l.google.com:19302' },
            { urls: 'stun:stun4.l.google.com:19302' }
        ]
    };

    // Module variables
    let localStream = null;
    let peerConnections = {};
    let webrtcRoomId = null;
    let webrtcUserId = null;
    let webrtcUserRole = null;
    let webrtcUserName = null;
    let signalingInterval = null;
    let participants = {};
    let isInitialized = false;
    let processedMessages = new Set(); // Track processed messages to avoid duplicates
    let screenStream = null;
    let isScreenSharing = false;
    
    // Get base URL
    const baseUrl = window.location.origin + '/dance-learning-platform';

    // Initialize WebRTC
    window.initializeWebRTC = async function(role, classId, currentUserId, currentUserName) {
        if (isInitialized) {
            console.log('WebRTC already initialized');
            return;
        }
        
        console.log('Initializing WebRTC for', role, 'in class', classId);
        
        webrtcRoomId = classId;
        webrtcUserRole = role;
        webrtcUserId = currentUserId;
        webrtcUserName = currentUserName;
        
        try {
            // Get user media with error handling
            localStream = await navigator.mediaDevices.getUserMedia({ 
                video: {
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: 'user'
                },
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true
                }
            }).catch(error => {
                console.error('Media device error:', error);
                if (error.name === 'NotAllowedError') {
                    showWebRTCError('Camera and microphone access denied. Please allow permissions and refresh.');
                } else if (error.name === 'NotFoundError') {
                    showWebRTCError('No camera or microphone found. Please connect devices and refresh.');
                } else {
                    showWebRTCError('Could not access camera/microphone: ' + error.message);
                }
                throw error;
            });
            
            console.log('Local stream obtained');
            
            // Display local video
            const localVideo = document.getElementById('localVideo');
            if (localVideo) {
                localVideo.srcObject = localStream;
                localVideo.addEventListener('loadedmetadata', () => {
                    localVideo.play().catch(e => console.log('Video play error:', e));
                });
            }
            
            // Initialize controls
            initializeControls();
            
            // Join the room
            await joinRoom();
            
            // Start polling for signaling messages
            startSignalingPolling();
            
            // Start polling for participants
            startParticipantPolling();
            
            isInitialized = true;
            console.log('WebRTC initialized successfully');
            
        } catch (error) {
            console.error('Error initializing WebRTC:', error);
        }
    };

    // Show error message in UI
    function showWebRTCError(message) {
        const videoContainer = document.getElementById('video-container');
        if (videoContainer) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'col-span-full bg-red-900/50 border border-red-700 text-red-200 px-4 py-3 rounded-lg';
            errorDiv.innerHTML = `<strong>Error:</strong> ${message}`;
            videoContainer.parentNode.insertBefore(errorDiv, videoContainer);
        }
        alert(message);
    }

    // Join the WebRTC room
    async function joinRoom() {
        const formData = new FormData();
        formData.append('action', 'join');
        formData.append('room_id', webrtcRoomId);
        
        try {
            const response = await fetch(baseUrl + '/api/signaling.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            console.log('Joined room:', data);
        } catch (error) {
            console.error('Error joining room:', error);
        }
    }

    // Leave the room
    async function leaveRoom() {
        if (signalingInterval) {
            clearInterval(signalingInterval);
        }
        
        const formData = new FormData();
        formData.append('action', 'leave');
        
        try {
            await fetch(baseUrl + '/api/signaling.php', {
                method: 'POST',
                body: formData
            });
        } catch (error) {
            console.error('Error leaving room:', error);
        }
        
        // Close all peer connections
        Object.values(peerConnections).forEach(pc => {
            if (pc) pc.close();
        });
        peerConnections = {};
        processedMessages.clear();
        
        // Stop screen sharing if active
        if (screenStream) {
            screenStream.getTracks().forEach(track => track.stop());
            screenStream = null;
        }
    }

    // Start polling for signaling messages
    function startSignalingPolling() {
        signalingInterval = setInterval(async () => {
            try {
                const response = await fetch(baseUrl + '/api/signaling.php?action=get_messages&t=' + Date.now());
                const data = await response.json();
                
                if (data.success && data.messages) {
                    processSignalingMessages(data.messages);
                }
            } catch (error) {
                console.error('Error polling signaling messages:', error);
            }
        }, 2000);
    }

    // Start polling for participants
    function startParticipantPolling() {
        setInterval(async () => {
            try {
                const response = await fetch(baseUrl + '/api/signaling.php?action=get_participants&t=' + Date.now());
                const data = await response.json();
                
                if (data.success && data.participants) {
                    updateParticipantsList(data.participants);
                }
            } catch (error) {
                console.error('Error polling participants:', error);
            }
        }, 3000);
    }

    // Process incoming signaling messages
    function processSignalingMessages(messages) {
        messages.forEach(message => {
            // Skip messages from self
            if (message.user_id == webrtcUserId) {
                return;
            }
            
            // Create a unique ID for this message to avoid duplicates
            const messageId = `${message.id}-${message.message_type}`;
            if (processedMessages.has(messageId)) {
                return;
            }
            processedMessages.add(messageId);
            
            // Limit the size of processed messages set
            if (processedMessages.size > 100) {
                const iterator = processedMessages.values();
                processedMessages.delete(iterator.next().value);
            }
            
            const remoteId = message.user_id;
            
            switch (message.message_type) {
                case 'join':
                    handleUserJoined(remoteId, message.user_role, message.user_name);
                    break;
                    
                case 'leave':
                    handleUserLeft(remoteId);
                    break;
                    
                case 'offer':
                    handleOffer(remoteId, message.message);
                    break;
                    
                case 'answer':
                    handleAnswer(remoteId, message.message);
                    break;
                    
                case 'candidate':
                    handleCandidate(remoteId, message.message);
                    break;
            }
        });
    }

    // Handle new user joined
    function handleUserJoined(remoteId, role, name) {
        console.log('User joined:', remoteId, role, name);
        
        // Only create peer connection for relevant pairs
        const shouldConnect = (
            (webrtcUserRole === 'instructor' && role === 'student') || 
            (webrtcUserRole === 'student' && role === 'instructor')
        );
        
        if (shouldConnect) {
            // Only instructor initiates connections to avoid conflicts
            if (webrtcUserRole === 'instructor') {
                setTimeout(() => {
                    createPeerConnection(remoteId, true);
                }, 1000); // Delay to avoid race conditions
            }
        }
        
        // Add to UI
        addRemoteVideo(remoteId, name || (role === 'instructor' ? 'Instructor' : 'Student'), role);
        
        // Update students list for instructor
        if (webrtcUserRole === 'instructor' && role === 'student') {
            updateStudentsList();
        }
        
        // Update video grid layout
        updateVideoGridLayout();
    }

    // Handle user left
    function handleUserLeft(remoteId) {
        console.log('User left:', remoteId);
        
        // Close peer connection
        if (peerConnections[remoteId]) {
            peerConnections[remoteId].close();
            delete peerConnections[remoteId];
        }
        
        // Remove from UI
        const remoteVideo = document.getElementById(`remote-${remoteId}`);
        if (remoteVideo) {
            remoteVideo.remove();
        }
        
        // Update students list for instructor
        if (webrtcUserRole === 'instructor') {
            updateStudentsList();
        }
        
        // Update video grid layout
        updateVideoGridLayout();
    }

    // Create peer connection
    async function createPeerConnection(remoteId, isInitiator = false) {
        // Check if connection already exists and is not closed
        if (peerConnections[remoteId] && 
            peerConnections[remoteId].connectionState !== 'closed' &&
            peerConnections[remoteId].connectionState !== 'failed') {
            console.log('Peer connection already exists for', remoteId);
            return peerConnections[remoteId];
        }
        
        console.log('Creating peer connection for', remoteId, 'initiator:', isInitiator);
        
        const peerConnection = new RTCPeerConnection(configuration);
        
        // Add local stream tracks
        if (localStream) {
            localStream.getTracks().forEach(track => {
                peerConnection.addTrack(track, localStream);
            });
        }
        
        // Handle ICE candidates
        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                console.log('Sending ICE candidate to', remoteId);
                sendSignalingMessage('candidate', event.candidate, remoteId);
            }
        };
        
        // Handle connection state changes
        peerConnection.onconnectionstatechange = () => {
            console.log('Connection state for', remoteId, ':', peerConnection.connectionState);
            
            if (peerConnection.connectionState === 'connected') {
                // Update participant status in UI
                updateParticipantStatus(remoteId, 'connected');
            } else if (peerConnection.connectionState === 'failed') {
                console.log('Connection failed, attempting to reconnect...');
                delete peerConnections[remoteId];
                updateParticipantStatus(remoteId, 'disconnected');
                if (webrtcUserRole === 'instructor') {
                    setTimeout(() => createPeerConnection(remoteId, true), 3000);
                }
            } else if (peerConnection.connectionState === 'closed' || 
                       peerConnection.connectionState === 'disconnected') {
                delete peerConnections[remoteId];
                updateParticipantStatus(remoteId, 'disconnected');
            }
        };
        
        // Handle ICE connection state
        peerConnection.oniceconnectionstatechange = () => {
            console.log('ICE connection state for', remoteId, ':', peerConnection.iceConnectionState);
            if (peerConnection.iceConnectionState === 'connected') {
                updateParticipantStatus(remoteId, 'connected');
            }
        };
        
        // Handle remote stream
        peerConnection.ontrack = (event) => {
            console.log('Received remote stream from', remoteId);
            const remoteVideoDiv = document.getElementById(`remote-${remoteId}`);
            if (remoteVideoDiv) {
                // Remove canvas if exists
                const canvas = remoteVideoDiv.querySelector('canvas');
                if (canvas) canvas.remove();
                
                // Add or update video element
                let videoElement = remoteVideoDiv.querySelector('video');
                if (!videoElement) {
                    videoElement = document.createElement('video');
                    videoElement.className = 'w-full h-full object-cover';
                    videoElement.autoplay = true;
                    videoElement.playsinline = true;
                    
                    // Clear the div and add video
                    remoteVideoDiv.innerHTML = '';
                    remoteVideoDiv.appendChild(videoElement);
                    
                    // Add label back
                    const labelDiv = document.createElement('div');
                    labelDiv.className = 'video-label';
                    labelDiv.textContent = remoteVideoDiv.dataset.label || 'Remote User';
                    remoteVideoDiv.appendChild(labelDiv);
                    
                    // Add status indicator
                    const statusDiv = document.createElement('div');
                    statusDiv.className = `video-status ${remoteVideoDiv.dataset.role === 'instructor' ? 'instructor' : ''}`;
                    statusDiv.id = `status-${remoteId}`;
                    statusDiv.innerHTML = '<span class="inline-block h-2 w-2 bg-green-500 rounded-full animate-pulse mr-1"></span>Live';
                    remoteVideoDiv.appendChild(statusDiv);
                }
                
                videoElement.srcObject = event.streams[0];
                videoElement.play().catch(e => console.log('Remote video play error:', e));
            }
        };
        
        peerConnections[remoteId] = peerConnection;
        
        // If initiator, create and send offer after a short delay
        if (isInitiator) {
            setTimeout(async () => {
                try {
                    console.log('Creating offer for', remoteId);
                    const offer = await peerConnection.createOffer({
                        offerToReceiveAudio: true,
                        offerToReceiveVideo: true
                    });
                    await peerConnection.setLocalDescription(offer);
                    console.log('Sending offer to', remoteId);
                    sendSignalingMessage('offer', offer, remoteId);
                } catch (error) {
                    console.error('Error creating offer:', error);
                }
            }, 500);
        }
        
        return peerConnection;
    }

    // Handle incoming offer
    async function handleOffer(remoteId, offer) {
        console.log('Received offer from', remoteId);
        
        try {
            // Create or get peer connection
            let peerConnection = peerConnections[remoteId];
            
            // Check if connection is in a valid state
            if (peerConnection) {
                if (peerConnection.signalingState !== 'stable') {
                    console.log('Peer connection not in stable state, current state:', peerConnection.signalingState);
                    // Close old connection and create new one
                    peerConnection.close();
                    delete peerConnections[remoteId];
                }
            }
            
            // Create new connection if needed
            if (!peerConnections[remoteId]) {
                peerConnection = await createPeerConnection(remoteId, false);
            }
            
            // Set remote description
            await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));
            
            // Create answer
            console.log('Creating answer for', remoteId);
            const answer = await peerConnection.createAnswer();
            await peerConnection.setLocalDescription(answer);
            
            console.log('Sending answer to', remoteId);
            sendSignalingMessage('answer', answer, remoteId);
            
        } catch (error) {
            console.error('Error handling offer:', error);
        }
    }

    // Handle incoming answer
    async function handleAnswer(remoteId, answer) {
        console.log('Received answer from', remoteId);
        
        try {
            const peerConnection = peerConnections[remoteId];
            if (peerConnection && peerConnection.signalingState !== 'closed') {
                if (peerConnection.signalingState === 'have-local-offer') {
                    await peerConnection.setRemoteDescription(new RTCSessionDescription(answer));
                    console.log('Remote description set for', remoteId);
                } else {
                    console.log('Cannot set answer in state:', peerConnection.signalingState);
                }
            }
        } catch (error) {
            console.error('Error handling answer:', error);
        }
    }

    // Handle incoming ICE candidate
    async function handleCandidate(remoteId, candidate) {
        try {
            const peerConnection = peerConnections[remoteId];
            if (peerConnection && peerConnection.remoteDescription) {
                await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                console.log('ICE candidate added for', remoteId);
            }
        } catch (error) {
            console.error('Error handling candidate:', error);
        }
    }

    // Send signaling message
    async function sendSignalingMessage(type, message, targetUserId = null) {
        const formData = new FormData();
        formData.append('action', 'send_message');
        formData.append('room_id', webrtcRoomId);
        formData.append('message_type', type);
        formData.append('message', JSON.stringify(message));
        
        if (targetUserId) {
            formData.append('target_user_id', targetUserId);
        }
        
        try {
            await fetch(baseUrl + '/api/signaling.php', {
                method: 'POST',
                body: formData
            });
        } catch (error) {
            console.error('Error sending signaling message:', error);
        }
    }

    // Update participants list
    function updateParticipantsList(newParticipants) {
        // Check for new participants
        newParticipants.forEach(participant => {
            if (!participants[participant.user_id] && participant.user_id != webrtcUserId) {
                // New participant joined
                handleUserJoined(
                    participant.user_id, 
                    participant.user_role, 
                    participant.full_name
                );
            }
        });
        
        // Check for participants who left
        Object.keys(participants).forEach(participantId => {
            const stillExists = newParticipants.some(p => p.user_id == participantId);
            if (!stillExists) {
                handleUserLeft(participantId);
            }
        });
        
        // Update participants object
        participants = {};
        newParticipants.forEach(p => {
            if (p.user_id != webrtcUserId) {
                participants[p.user_id] = p;
            }
        });
        
        // Update sidebar participants list
        updateSidebarParticipants();
        
        // Update students list in UI if instructor
        if (webrtcUserRole === 'instructor') {
            updateStudentsList();
        }
    }

    // Update sidebar participants list
    function updateSidebarParticipants() {
        const participantsList = document.getElementById('participants-list');
        if (!participantsList) return;
        
        let html = '';
        
        // Add current user
        html += `
            <div class="participant-item">
                <div class="participant-avatar ${webrtcUserRole === 'instructor' ? 'instructor' : ''}">
                    ${webrtcUserName.charAt(0).toUpperCase()}
                </div>
                <div class="participant-info">
                    <div class="participant-name">You (${webrtcUserRole === 'instructor' ? 'Instructor' : 'Student'})</div>
                    <div class="participant-status online">● Online</div>
                </div>
            </div>
        `;
        
        // Add other participants
        Object.values(participants).forEach(participant => {
            const isConnected = peerConnections[participant.user_id] && 
                               peerConnections[participant.user_id].connectionState === 'connected';
            
            html += `
                <div class="participant-item">
                    <div class="participant-avatar ${participant.user_role === 'instructor' ? 'instructor' : ''}">
                        ${participant.full_name ? participant.full_name.charAt(0).toUpperCase() : '?'}
                    </div>
                    <div class="participant-info">
                        <div class="participant-name">${participant.full_name || 'Unknown'} 
                            ${participant.user_role === 'instructor' ? '(Instructor)' : ''}
                        </div>
                        <div class="participant-status ${isConnected ? 'online' : ''}">
                            ${isConnected ? '● Online' : '○ Offline'}
                        </div>
                    </div>
                </div>
            `;
        });
        
        participantsList.innerHTML = html;
        
        // Update participant count
        const countSpan = document.getElementById('participant-count');
        if (countSpan) {
            countSpan.textContent = Object.keys(participants).length + 1; // +1 for self
        }
    }

    // Update participant status in UI
    function updateParticipantStatus(remoteId, status) {
        const statusDiv = document.getElementById(`status-${remoteId}`);
        if (statusDiv) {
            if (status === 'connected') {
                statusDiv.innerHTML = '<span class="inline-block h-2 w-2 bg-green-500 rounded-full animate-pulse mr-1"></span>Live';
            } else {
                statusDiv.innerHTML = '<span class="inline-block h-2 w-2 bg-gray-500 rounded-full mr-1"></span>Disconnected';
            }
        }
        
        // Update sidebar
        updateSidebarParticipants();
    }

    // Update students list in instructor UI
    function updateStudentsList() {
        const studentsList = document.getElementById('students-list');
        if (!studentsList) return;
        
        const students = Object.values(participants).filter(p => p.user_role === 'student');
        
        if (students.length === 0) {
            studentsList.innerHTML = 'No students connected yet.';
        } else {
            let html = '<ul class="space-y-2">';
            students.forEach(student => {
                const isConnected = peerConnections[student.user_id] && 
                                   peerConnections[student.user_id].connectionState === 'connected';
                html += `
                    <li class="flex items-center justify-between text-sm">
                        <span class="text-gray-300">${student.full_name}</span>
                        <span class="${isConnected ? 'text-green-500' : 'text-gray-500'} text-xs">
                            ${isConnected ? '● Connected' : '○ Disconnected'}
                        </span>
                    </li>
                `;
            });
            html += '</ul>';
            studentsList.innerHTML = html;
        }
    }

    // Initialize meeting controls
    function initializeControls() {
        let audioEnabled = true;
        let videoEnabled = true;
        
        const muteAudioBtn = document.getElementById('muteAudio');
        const muteVideoBtn = document.getElementById('muteVideo');
        const shareScreenBtn = document.getElementById('shareScreen');
        const endCallBtn = document.getElementById('endCall');
        
        if (muteAudioBtn) {
            muteAudioBtn.addEventListener('click', () => {
                audioEnabled = !audioEnabled;
                if (localStream) {
                    localStream.getAudioTracks().forEach(track => {
                        track.enabled = audioEnabled;
                    });
                }
                
                // Update button appearance
                if (!audioEnabled) {
                    muteAudioBtn.classList.add('muted');
                    muteAudioBtn.innerHTML = `
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"></path>
                        </svg>
                    `;
                } else {
                    muteAudioBtn.classList.remove('muted');
                    muteAudioBtn.innerHTML = `
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                        </svg>
                    `;
                }
            });
        }
        
        if (muteVideoBtn) {
            muteVideoBtn.addEventListener('click', () => {
                videoEnabled = !videoEnabled;
                if (localStream) {
                    localStream.getVideoTracks().forEach(track => {
                        track.enabled = videoEnabled;
                    });
                }
                
                // Update button appearance
                if (!videoEnabled) {
                    muteVideoBtn.classList.add('muted');
                    muteVideoBtn.innerHTML = `
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"></path>
                        </svg>
                    `;
                    
                    // Show placeholder in local video
                    const localVideo = document.getElementById('localVideo');
                    if (localVideo) {
                        localVideo.style.display = 'none';
                        const localTile = document.getElementById('local-tile');
                        if (localTile && !localTile.querySelector('canvas')) {
                            const canvas = document.createElement('canvas');
                            canvas.className = 'w-full h-full object-cover absolute top-0 left-0';
                            canvas.width = 640;
                            canvas.height = 480;
                            const ctx = canvas.getContext('2d');
                            ctx.fillStyle = '#3c4043';
                            ctx.fillRect(0, 0, canvas.width, canvas.height);
                            ctx.fillStyle = '#9aa0a6';
                            ctx.font = 'bold 24px Arial';
                            ctx.textAlign = 'center';
                            ctx.fillText('Camera Off', canvas.width/2, canvas.height/2);
                            localTile.appendChild(canvas);
                        }
                    }
                } else {
                    muteVideoBtn.classList.remove('muted');
                    muteVideoBtn.innerHTML = `
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    `;
                    
                    // Remove placeholder
                    const localTile = document.getElementById('local-tile');
                    const canvas = localTile?.querySelector('canvas');
                    if (canvas) canvas.remove();
                    const localVideo = document.getElementById('localVideo');
                    if (localVideo) {
                        localVideo.style.display = 'block';
                    }
                }
            });
        }
        
        if (shareScreenBtn) {
            shareScreenBtn.addEventListener('click', toggleScreenShare);
        }
        
        if (endCallBtn) {
            endCallBtn.addEventListener('click', async () => {
                if (confirm('Are you sure you want to end the class?')) {
                    await leaveRoom();
                    window.location.href = baseUrl + '/' + webrtcUserRole + '/dashboard.php';
                }
            });
        }
    }

    // Toggle screen sharing
    async function toggleScreenShare() {
        if (!isScreenSharing) {
            try {
                screenStream = await navigator.mediaDevices.getDisplayMedia({
                    video: true,
                    audio: false
                });
                
                // Replace video track with screen share track
                const videoTrack = screenStream.getVideoTracks()[0];
                const sender = peerConnections[Object.keys(peerConnections)[0]]?.getSenders().find(s => s.track?.kind === 'video');
                if (sender) {
                    sender.replaceTrack(videoTrack);
                }
                
                // Update local video
                const localVideo = document.getElementById('localVideo');
                if (localVideo) {
                    localVideo.srcObject = screenStream;
                }
                
                videoTrack.onended = () => {
                    stopScreenShare();
                };
                
                isScreenSharing = true;
                document.getElementById('shareScreen').classList.add('bg-blue-600');
                
            } catch (error) {
                console.error('Error sharing screen:', error);
            }
        } else {
            stopScreenShare();
        }
    }

    // Stop screen sharing
    function stopScreenShare() {
        if (screenStream) {
            screenStream.getTracks().forEach(track => track.stop());
            screenStream = null;
        }
        
        // Switch back to camera
        const videoTrack = localStream.getVideoTracks()[0];
        const sender = peerConnections[Object.keys(peerConnections)[0]]?.getSenders().find(s => s.track?.kind === 'video');
        if (sender) {
            sender.replaceTrack(videoTrack);
        }
        
        // Update local video
        const localVideo = document.getElementById('localVideo');
        if (localVideo) {
            localVideo.srcObject = localStream;
        }
        
        isScreenSharing = false;
        document.getElementById('shareScreen').classList.remove('bg-blue-600');
    }

    // Add remote video to grid
    function addRemoteVideo(remoteId, label, role) {
        const videoContainer = document.getElementById('video-container');
        if (!videoContainer) return;
        
        // Check if this remote video already exists
        if (document.getElementById(`remote-${remoteId}`)) {
            return;
        }
        
        const videoDiv = document.createElement('div');
        videoDiv.className = 'video-tile';
        videoDiv.id = `remote-${remoteId}`;
        videoDiv.dataset.label = label;
        videoDiv.dataset.role = role;
        
        // Create placeholder canvas
        const canvas = document.createElement('canvas');
        canvas.className = 'w-full h-full object-cover';
        canvas.width = 640;
        canvas.height = 480;
        const ctx = canvas.getContext('2d');
        
        // Draw connecting placeholder
        ctx.fillStyle = '#3c4043';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#9aa0a6';
        ctx.font = 'bold 20px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Connecting...', canvas.width/2, canvas.height/2 - 20);
        ctx.fillText(label, canvas.width/2, canvas.height/2 + 20);
        
        videoDiv.appendChild(canvas);
        
        // Add label
        const labelDiv = document.createElement('div');
        labelDiv.className = 'video-label';
        labelDiv.textContent = label;
        videoDiv.appendChild(labelDiv);
        
        // Add status indicator
        const statusDiv = document.createElement('div');
        statusDiv.className = `video-status ${role === 'instructor' ? 'instructor' : ''}`;
        statusDiv.id = `status-${remoteId}`;
        statusDiv.innerHTML = '<span class="inline-block h-2 w-2 bg-yellow-500 rounded-full animate-pulse mr-1"></span>Connecting...';
        videoDiv.appendChild(statusDiv);
        
        videoContainer.appendChild(videoDiv);
        
        // Update grid layout
        updateVideoGridLayout();
    }

    // Update video grid layout based on number of participants
    function updateVideoGridLayout() {
        const videoContainer = document.getElementById('video-container');
        if (!videoContainer) return;
        
        const videos = videoContainer.children;
        const count = videos.length;
        
        // Remove any existing grid class
        videoContainer.style.gridTemplateColumns = '';
        
        // Apply appropriate grid layout based on count
        if (count === 1) {
            videoContainer.style.gridTemplateColumns = '1fr';
        } else if (count === 2) {
            videoContainer.style.gridTemplateColumns = 'repeat(2, 1fr)';
        } else if (count === 3) {
            videoContainer.style.gridTemplateColumns = 'repeat(3, 1fr)';
        } else if (count <= 4) {
            videoContainer.style.gridTemplateColumns = 'repeat(2, 1fr)';
        } else if (count <= 6) {
            videoContainer.style.gridTemplateColumns = 'repeat(3, 1fr)';
        } else if (count <= 9) {
            videoContainer.style.gridTemplateColumns = 'repeat(3, 1fr)';
        } else {
            videoContainer.style.gridTemplateColumns = 'repeat(auto-fit, minmax(300px, 1fr))';
        }
        
        // Adjust for different screen sizes
        if (window.innerWidth < 768) {
            if (count === 1) {
                videoContainer.style.gridTemplateColumns = '1fr';
            } else {
                videoContainer.style.gridTemplateColumns = '1fr';
            }
        }
    }

    // Handle window resize
    window.addEventListener('resize', () => {
        updateVideoGridLayout();
    });

    // Clean up on page unload
    window.addEventListener('beforeunload', function() {
        if (signalingInterval) {
            clearInterval(signalingInterval);
        }
        
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
        }
        
        if (screenStream) {
            screenStream.getTracks().forEach(track => track.stop());
        }
        
        // Leave room
        const formData = new FormData();
        formData.append('action', 'leave');
        navigator.sendBeacon(baseUrl + '/api/signaling.php', formData);
    });

    console.log('WebRTC module loaded with Google Meet layout support');
})();