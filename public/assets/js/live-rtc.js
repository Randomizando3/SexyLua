(() => {
    const root = document.querySelector('[data-live-rtc-mode]')

    if (!root) {
        return
    }

    const mode = root.dataset.liveRtcMode || 'viewer'
    const liveId = Number(root.dataset.liveId || 0)
    const csrf = root.dataset.csrf || ''
    const canWatch = root.dataset.canWatch !== '0'
    const canBroadcast = root.dataset.canBroadcast === '1'
    const joinUrl = root.dataset.joinUrl || '/live/rtc/join'
    const startUrl = root.dataset.startUrl || '/live/rtc/start'
    const stopUrl = root.dataset.stopUrl || '/live/rtc/stop'
    const signalUrl = root.dataset.signalUrl || '/live/rtc/signal'
    const pollUrl = root.dataset.pollUrl || '/live/rtc/poll'
    const heartbeatUrl = root.dataset.heartbeatUrl || '/live/rtc/heartbeat'
    const leaveUrl = root.dataset.leaveUrl || '/live/rtc/leave'
    const bitrateKbps = Number(root.dataset.maxBitrateKbps || 1500)
    const videoWidth = Number(root.dataset.videoWidth || 960)
    const videoHeight = Number(root.dataset.videoHeight || 540)
    const videoFps = Number(root.dataset.videoFps || 24)
    const accessMessage = root.dataset.accessMessage || ''

    const state = {
        peerId: '',
        joined: false,
        broadcasting: false,
        joinInFlight: null,
        pollAfterId: 0,
        viewerPeerConnection: null,
        viewerTargetPeerId: '',
        creatorPeerConnections: new Map(),
        pendingCandidates: new Map(),
        localStream: null,
        remoteStream: null,
        pollTimer: null,
        heartbeatTimer: null,
    }

    const elements = {
        error: document.querySelector('[data-live-error]'),
        waitBox: document.querySelector('[data-live-waiting]'),
        waitText: document.querySelector('[data-live-waiting-text]'),
        statusText: document.querySelector('[data-live-status-text]'),
        streamState: document.querySelector('[data-live-stream-state]'),
        viewerCounts: document.querySelectorAll('[data-live-viewer-count]'),
        startButton: document.querySelector('[data-live-start]'),
        stopButton: document.querySelector('[data-live-stop]'),
        playbackButton: document.querySelector('[data-live-playback]'),
        localVideo: document.querySelector('[data-live-local-video]'),
        remoteVideo: document.querySelector('[data-live-remote-video]'),
        roomLink: document.querySelector('[data-live-room-link]'),
    }

    const rtcConfig = {
        iceServers: [
            { urls: ['stun:stun.l.google.com:19302', 'stun:stun1.l.google.com:19302'] },
        ],
    }

    const setText = (element, value) => {
        if (element) {
            element.textContent = String(value || '')
        }
    }

    const showError = (message) => {
        if (!elements.error) {
            return
        }

        if (!message) {
            elements.error.classList.add('hidden')
            elements.error.textContent = ''
            return
        }

        elements.error.textContent = String(message)
        elements.error.classList.remove('hidden')
    }

    const setWaiting = (message) => {
        if (!elements.waitBox) {
            return
        }

        elements.waitBox.classList.remove('hidden')
        setText(elements.waitText, message)
    }

    const hideWaiting = () => {
        if (elements.waitBox) {
            elements.waitBox.classList.add('hidden')
        }
    }

    const updateViewerCount = (value) => {
        elements.viewerCounts.forEach((node) => {
            node.textContent = String(value || 0)
        })
    }

    const updateStatus = (stream) => {
        const status = stream && stream.status ? String(stream.status) : 'idle'
        setText(elements.statusText, status === 'live' ? 'ao vivo' : status === 'ended' ? 'encerrada' : 'aguardando')
        setText(elements.streamState, status)
        updateViewerCount(stream && Number.isFinite(Number(stream.viewer_count)) ? Number(stream.viewer_count) : 0)

        if (elements.startButton) {
            elements.startButton.disabled = mode !== 'creator' || !canBroadcast || status === 'live'
        }

        if (elements.stopButton) {
            elements.stopButton.disabled = mode !== 'creator' || !canBroadcast || status !== 'live'
        }

        if (mode === 'viewer') {
            if (status === 'live' && stream && stream.broadcaster_online) {
                hideWaiting()
            } else if (canWatch) {
                setWaiting('A live ainda nao esta transmitindo. Deixe esta pagina aberta que ela conecta sozinha.')
            }
        }
    }

    const postForm = async (url, payload) => {
        const body = new URLSearchParams()
        body.set('_token', csrf)

        Object.entries(payload || {}).forEach(([key, value]) => {
            if (value === undefined || value === null) {
                return
            }

            body.set(key, String(value))
        })

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body,
            credentials: 'same-origin',
        })

        return response.json()
    }

    const getJson = async (url, params) => {
        const query = new URLSearchParams()
        Object.entries(params || {}).forEach(([key, value]) => {
            if (value === undefined || value === null || value === '') {
                return
            }

            query.set(key, String(value))
        })

        const response = await fetch(`${url}?${query.toString()}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })

        return response.json()
    }

    const ensureJoined = async () => {
        if (state.joined && state.peerId) {
            return true
        }

        if (state.joinInFlight) {
            return state.joinInFlight
        }

        state.joinInFlight = postForm(joinUrl, {
            live_id: liveId,
            role: mode === 'creator' ? 'creator' : 'viewer',
        }).then((data) => {
            if (!data.ok) {
                showError(data.message || 'Nao foi possivel entrar na sessao da live.')
                if (mode === 'viewer') {
                    setWaiting(data.message || accessMessage || 'Voce nao tem acesso a esta live.')
                }
                return false
            }

            state.peerId = data.peer_id || ''
            state.joined = state.peerId !== ''
            updateStatus(data.stream || {})
            showError('')

            if (mode === 'creator') {
                setWaiting('Preview local pronto. Clique em iniciar transmissao para abrir a live.')
            }

            return state.joined
        }).catch((error) => {
            showError(error instanceof Error ? error.message : 'Falha ao conectar a live.')
            return false
        }).finally(() => {
            state.joinInFlight = null
        })

        return state.joinInFlight
    }

    const sendSignal = async (toPeerId, kind, payload) => {
        if (!state.peerId || !toPeerId) {
            return
        }

        const response = await postForm(signalUrl, {
            live_id: liveId,
            from_peer_id: state.peerId,
            to_peer_id: toPeerId,
            kind,
            payload: JSON.stringify(payload || {}),
        })

        if (!response.ok) {
            showError(response.message || 'Falha ao enviar sinal da live.')
        }
    }

    const queueCandidate = (remotePeerId, candidate) => {
        if (!candidate) {
            return
        }

        const current = state.pendingCandidates.get(remotePeerId) || []
        current.push(candidate)
        state.pendingCandidates.set(remotePeerId, current)
    }

    const flushCandidates = async (remotePeerId, peerConnection) => {
        const queued = state.pendingCandidates.get(remotePeerId) || []
        if (!peerConnection || !peerConnection.remoteDescription) {
            return
        }

        while (queued.length > 0) {
            const candidate = queued.shift()
            try {
                await peerConnection.addIceCandidate(new RTCIceCandidate(candidate))
            } catch (error) {
                console.warn('Falha ao aplicar candidate pendente', error)
            }
        }

        state.pendingCandidates.delete(remotePeerId)
    }

    const tuneSender = (sender) => {
        if (!sender || typeof sender.getParameters !== 'function' || typeof sender.setParameters !== 'function') {
            return
        }

        const params = sender.getParameters() || {}
        params.encodings = params.encodings && params.encodings.length > 0 ? params.encodings : [{}]

        if (sender.track && sender.track.kind === 'video') {
            params.encodings[0].maxBitrate = bitrateKbps * 1000
            params.encodings[0].maxFramerate = videoFps
            params.degradationPreference = 'maintain-framerate'
        }

        if (sender.track && sender.track.kind === 'audio') {
            params.encodings[0].maxBitrate = 96000
        }

        sender.setParameters(params).catch(() => {})
    }

    const attachLocalTracks = (peerConnection) => {
        if (!peerConnection || !state.localStream) {
            return
        }

        state.localStream.getTracks().forEach((track) => {
            const alreadyAttached = peerConnection.getSenders().some((sender) => sender.track && sender.track.id === track.id)
            if (alreadyAttached) {
                return
            }

            const sender = peerConnection.addTrack(track, state.localStream)
            tuneSender(sender)
        })
    }

    const createPeerConnection = (remotePeerId, asCreator) => {
        const peerConnection = new RTCPeerConnection(rtcConfig)

        peerConnection.onicecandidate = (event) => {
            if (!event.candidate) {
                return
            }

            const candidate = typeof event.candidate.toJSON === 'function'
                ? event.candidate.toJSON()
                : {
                    candidate: event.candidate.candidate,
                    sdpMid: event.candidate.sdpMid,
                    sdpMLineIndex: event.candidate.sdpMLineIndex,
                }

            sendSignal(remotePeerId, 'candidate', candidate).catch((error) => {
                console.warn('Falha ao enviar candidate', error)
            })
        }

        peerConnection.onconnectionstatechange = () => {
            const connectionState = peerConnection.connectionState

            if (connectionState === 'failed' || connectionState === 'closed') {
                if (asCreator) {
                    state.creatorPeerConnections.delete(remotePeerId)
                } else if (state.viewerPeerConnection === peerConnection) {
                    state.viewerPeerConnection = null
                    state.viewerTargetPeerId = ''
                }
            }
        }

        if (!asCreator) {
            peerConnection.ontrack = (event) => {
                if (!state.remoteStream) {
                    state.remoteStream = new MediaStream()
                }

                event.streams.forEach((stream) => {
                    stream.getTracks().forEach((track) => {
                        const hasTrack = state.remoteStream.getTracks().some((current) => current.id === track.id)
                        if (!hasTrack) {
                            state.remoteStream.addTrack(track)
                        }
                    })
                })

                if (elements.remoteVideo) {
                    elements.remoteVideo.srcObject = state.remoteStream
                    elements.remoteVideo.play().catch(() => {
                        if (elements.playbackButton) {
                            elements.playbackButton.classList.remove('hidden')
                        }
                    })
                }
            }
        }

        return peerConnection
    }

    const creatorPeerConnectionFor = (remotePeerId) => {
        if (state.creatorPeerConnections.has(remotePeerId)) {
            return state.creatorPeerConnections.get(remotePeerId)
        }

        const peerConnection = createPeerConnection(remotePeerId, true)
        state.creatorPeerConnections.set(remotePeerId, peerConnection)
        attachLocalTracks(peerConnection)
        return peerConnection
    }

    const viewerPeerConnectionFor = (remotePeerId) => {
        if (state.viewerPeerConnection && state.viewerTargetPeerId === remotePeerId) {
            return state.viewerPeerConnection
        }

        if (state.viewerPeerConnection) {
            state.viewerPeerConnection.close()
        }

        state.viewerPeerConnection = createPeerConnection(remotePeerId, false)
        state.viewerTargetPeerId = remotePeerId
        return state.viewerPeerConnection
    }

    const handleCreatorOffer = async (message) => {
        if (!state.localStream) {
            return
        }

        const remotePeerId = message.from_peer_id
        const peerConnection = creatorPeerConnectionFor(remotePeerId)
        attachLocalTracks(peerConnection)

        await peerConnection.setRemoteDescription(new RTCSessionDescription(message.payload))
        await flushCandidates(remotePeerId, peerConnection)

        const answer = await peerConnection.createAnswer()
        await peerConnection.setLocalDescription(answer)
        await sendSignal(remotePeerId, 'answer', {
            type: peerConnection.localDescription.type,
            sdp: peerConnection.localDescription.sdp,
        })
    }

    const handleCandidate = async (remotePeerId, payload, peerConnectionFactory) => {
        if (!payload || !payload.candidate) {
            return
        }

        const peerConnection = peerConnectionFactory(remotePeerId)
        if (!peerConnection.remoteDescription) {
            queueCandidate(remotePeerId, payload)
            return
        }

        try {
            await peerConnection.addIceCandidate(new RTCIceCandidate(payload))
        } catch (error) {
            queueCandidate(remotePeerId, payload)
        }
    }

    const connectViewerToBroadcaster = async (broadcasterPeerId) => {
        if (!broadcasterPeerId) {
            return
        }

        const peerConnection = viewerPeerConnectionFor(broadcasterPeerId)
        peerConnection.addTransceiver('video', { direction: 'recvonly' })
        peerConnection.addTransceiver('audio', { direction: 'recvonly' })

        const offer = await peerConnection.createOffer()
        await peerConnection.setLocalDescription(offer)

        await sendSignal(broadcasterPeerId, 'offer', {
            type: peerConnection.localDescription.type,
            sdp: peerConnection.localDescription.sdp,
        })
    }

    const stopLocalStream = () => {
        if (state.localStream) {
            state.localStream.getTracks().forEach((track) => track.stop())
        }

        state.localStream = null

        if (elements.localVideo) {
            elements.localVideo.srcObject = null
        }
    }

    const closeAllCreatorPeers = () => {
        state.creatorPeerConnections.forEach((peerConnection) => {
            peerConnection.close()
        })
        state.creatorPeerConnections.clear()
    }

    const closeViewerPeer = () => {
        if (state.viewerPeerConnection) {
            state.viewerPeerConnection.close()
            state.viewerPeerConnection = null
        }

        state.viewerTargetPeerId = ''
        state.remoteStream = null

        if (elements.remoteVideo) {
            elements.remoteVideo.srcObject = null
        }
    }

    const heartbeat = async () => {
        if (!state.peerId) {
            return
        }

        const data = await postForm(heartbeatUrl, {
            live_id: liveId,
            peer_id: state.peerId,
        })

        if (data.ok) {
            updateStatus(data.stream || {})
        }
    }

    const poll = async () => {
        if (!state.peerId) {
            return
        }

        const data = await getJson(pollUrl, {
            live_id: liveId,
            peer_id: state.peerId,
            after_id: state.pollAfterId,
        })

        if (!data.ok) {
            showError(data.message || 'Nao foi possivel acompanhar a live.')
            return
        }

        updateStatus(data.stream || {})
        showError('')

        if (Array.isArray(data.messages)) {
            for (const message of data.messages) {
                state.pollAfterId = Math.max(state.pollAfterId, Number(message.id || 0))

                if (mode === 'creator') {
                    if (message.kind === 'offer') {
                        await handleCreatorOffer(message)
                    }

                    if (message.kind === 'candidate') {
                        await handleCandidate(message.from_peer_id, message.payload, creatorPeerConnectionFor)
                    }
                } else {
                    if (message.kind === 'answer' && state.viewerPeerConnection) {
                        await state.viewerPeerConnection.setRemoteDescription(new RTCSessionDescription(message.payload))
                        await flushCandidates(message.from_peer_id, state.viewerPeerConnection)
                    }

                    if (message.kind === 'candidate') {
                        await handleCandidate(message.from_peer_id, message.payload, viewerPeerConnectionFor)
                    }
                }
            }
        }

        if (mode === 'viewer') {
            const stream = data.stream || {}
            const broadcasterPeerId = stream.broadcaster_peer_id || ''
            const shouldConnect = canWatch && stream.status === 'live' && stream.broadcaster_online && broadcasterPeerId !== ''

            if (!shouldConnect) {
                closeViewerPeer()
                return
            }

            if (!state.viewerPeerConnection || state.viewerTargetPeerId !== broadcasterPeerId) {
                await connectViewerToBroadcaster(broadcasterPeerId)
            }
        }
    }

    const startLoops = () => {
        if (!state.pollTimer) {
            state.pollTimer = window.setInterval(() => {
                poll().catch((error) => {
                    console.warn('Polling da live falhou', error)
                })
            }, 1500)
        }

        if (!state.heartbeatTimer) {
            state.heartbeatTimer = window.setInterval(() => {
                heartbeat().catch((error) => {
                    console.warn('Heartbeat da live falhou', error)
                })
            }, 10000)
        }
    }

    const stopLoops = () => {
        if (state.pollTimer) {
            window.clearInterval(state.pollTimer)
            state.pollTimer = null
        }

        if (state.heartbeatTimer) {
            window.clearInterval(state.heartbeatTimer)
            state.heartbeatTimer = null
        }
    }

    const startCreatorBroadcast = async () => {
        if (!canBroadcast || liveId <= 0) {
            return
        }

        const joined = await ensureJoined()
        if (!joined) {
            return
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: videoWidth, max: videoWidth },
                    height: { ideal: videoHeight, max: videoHeight },
                    frameRate: { ideal: videoFps, max: videoFps },
                },
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true,
                },
            })

            const videoTrack = stream.getVideoTracks()[0]
            if (videoTrack && typeof videoTrack.applyConstraints === 'function') {
                videoTrack.applyConstraints({
                    width: videoWidth,
                    height: videoHeight,
                    frameRate: videoFps,
                }).catch(() => {})
            }

            state.localStream = stream

            if (elements.localVideo) {
                elements.localVideo.srcObject = stream
                elements.localVideo.muted = true
                elements.localVideo.play().catch(() => {})
            }

            const data = await postForm(startUrl, {
                live_id: liveId,
                peer_id: state.peerId,
                max_bitrate_kbps: bitrateKbps,
                video_width: videoWidth,
                video_height: videoHeight,
                video_fps: videoFps,
            })

            if (!data.ok) {
                stopLocalStream()
                showError(data.message || 'Nao foi possivel iniciar a transmissao.')
                return
            }

            state.broadcasting = true
            updateStatus(data.stream || {})
            hideWaiting()
            startLoops()
            await poll()
        } catch (error) {
            showError(error instanceof Error ? error.message : 'Nao foi possivel abrir camera e microfone.')
        }
    }

    const stopCreatorBroadcast = async () => {
        if (!state.peerId) {
            return
        }

        const data = await postForm(stopUrl, {
            live_id: liveId,
            peer_id: state.peerId,
        })

        if (!data.ok) {
            showError(data.message || 'Nao foi possivel encerrar a transmissao.')
            return
        }

        state.broadcasting = false
        stopLocalStream()
        closeAllCreatorPeers()
        updateStatus(data.stream || {})
        setWaiting('Transmissao encerrada. Voce pode iniciar novamente quando quiser.')
    }

    const sendLeaveBeacon = () => {
        if (!state.peerId || !navigator.sendBeacon) {
            return
        }

        const body = new URLSearchParams()
        body.set('_token', csrf)
        body.set('live_id', String(liveId))
        body.set('peer_id', state.peerId)
        navigator.sendBeacon(leaveUrl, body)
    }

    if (elements.startButton) {
        elements.startButton.addEventListener('click', (event) => {
            event.preventDefault()
            startCreatorBroadcast().catch((error) => {
                showError(error instanceof Error ? error.message : 'Falha ao iniciar a live.')
            })
        })
    }

    if (elements.stopButton) {
        elements.stopButton.addEventListener('click', (event) => {
            event.preventDefault()
            stopCreatorBroadcast().catch((error) => {
                showError(error instanceof Error ? error.message : 'Falha ao encerrar a live.')
            })
        })
    }

    if (elements.playbackButton && elements.remoteVideo) {
        elements.playbackButton.addEventListener('click', () => {
            elements.remoteVideo.muted = false
            elements.remoteVideo.play().then(() => {
                elements.playbackButton.classList.add('hidden')
            }).catch((error) => {
                showError(error instanceof Error ? error.message : 'Toque para liberar o audio da live.')
            })
        })
    }

    window.addEventListener('beforeunload', () => {
        sendLeaveBeacon()
    })

    if (elements.roomLink) {
        elements.roomLink.textContent = elements.roomLink.getAttribute('href') || ''
    }

    if (mode === 'creator') {
        if (!canBroadcast || liveId <= 0) {
            setWaiting('Crie ou selecione uma live para abrir a transmissao local.')
            return
        }

        ensureJoined().then((joined) => {
            if (!joined) {
                return
            }

            setWaiting('Preview local pronto. Clique em iniciar transmissao para abrir a live.')
        }).catch((error) => {
            showError(error instanceof Error ? error.message : 'Nao foi possivel preparar o studio.')
        })

        return
    }

    if (!canWatch) {
        setWaiting(accessMessage || 'Voce nao tem acesso a esta live.')
        return
    }

    ensureJoined().then((joined) => {
        if (!joined) {
            return
        }

        startLoops()
        poll().catch((error) => {
            showError(error instanceof Error ? error.message : 'Falha ao carregar a live.')
        })
    }).catch((error) => {
        showError(error instanceof Error ? error.message : 'Falha ao entrar na live.')
    })
})()
