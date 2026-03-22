(() => {
    const root = document.querySelector('[data-live-rtc-mode]')

    if (!root) {
        return
    }

    const defaultIceServers = [
        { urls: ['stun:stun.l.google.com:19302', 'stun:stun1.l.google.com:19302'] },
    ]

    const parseIceServers = () => {
        const encoded = root.dataset.iceServers || ''

        if (!encoded) {
            return defaultIceServers
        }

        try {
            const parsed = JSON.parse(window.atob(encoded))

            if (Array.isArray(parsed) && parsed.length > 0) {
                return parsed
            }
        } catch (error) {
            console.warn('Falha ao ler servidores ICE configurados', error)
        }

        return defaultIceServers
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
    const recordingUploadUrl = root.dataset.recordingUploadUrl || '/live/rtc/recording'
    const bitrateKbps = Number(root.dataset.maxBitrateKbps || 1500)
    const videoWidth = Number(root.dataset.videoWidth || 960)
    const videoHeight = Number(root.dataset.videoHeight || 540)
    const videoFps = Number(root.dataset.videoFps || 24)
    const accessMessage = root.dataset.accessMessage || ''
    const replayUrl = root.dataset.replayUrl || ''
    const replayEnabled = root.dataset.replayEnabled === '1' || replayUrl !== ''
    const recordingEnabled = root.dataset.recordingEnabled === '1'
    const iceTransportPolicy = root.dataset.iceTransportPolicy === 'relay' ? 'relay' : 'all'

    const state = {
        peerId: '',
        joined: false,
        broadcasting: false,
        joinInFlight: null,
        rejoinInFlight: null,
        pollAfterId: 0,
        viewerPeerConnection: null,
        viewerTargetPeerId: '',
        creatorPeerConnections: new Map(),
        pendingCandidates: new Map(),
        localStream: null,
        remoteStream: null,
        pollTimer: null,
        heartbeatTimer: null,
        replayActive: false,
        previewAudio: false,
        previewMirrored: true,
        lastStreamStatus: 'idle',
        recorder: null,
        recordingMimeType: 'video/webm',
        recordingChunks: [],
        recordingStartedAt: 0,
        recordingTimer: null,
        recordingShouldUpload: true,
        recordingStopResolver: null,
        recordingUploadInFlight: false,
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
        previewAudioButton: document.querySelector('[data-live-preview-audio]'),
        previewMirrorButton: document.querySelector('[data-live-preview-mirror]'),
        recordStartButton: document.querySelector('[data-live-record-start]'),
        recordStopButton: document.querySelector('[data-live-record-stop]'),
        recordStatus: document.querySelector('[data-live-record-status]'),
        recordDuration: document.querySelector('[data-live-record-duration]'),
        recordLink: document.querySelector('[data-live-record-link]'),
    }

    const rtcConfig = {
        iceServers: parseIceServers(),
        iceTransportPolicy,
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

    const setRecordLink = (url, label) => {
        if (!elements.recordLink) {
            return
        }

        if (!url) {
            elements.recordLink.classList.add('hidden')
            elements.recordLink.removeAttribute('href')
            elements.recordLink.textContent = ''
            return
        }

        elements.recordLink.href = url
        elements.recordLink.textContent = label || url
        elements.recordLink.classList.remove('hidden')
    }

    const formatDuration = (totalSeconds) => {
        const safe = Math.max(0, Number(totalSeconds || 0))
        const hours = String(Math.floor(safe / 3600)).padStart(2, '0')
        const minutes = String(Math.floor((safe % 3600) / 60)).padStart(2, '0')
        const seconds = String(Math.floor(safe % 60)).padStart(2, '0')

        return `${hours}:${minutes}:${seconds}`
    }

    const updateRecordingDuration = () => {
        if (!elements.recordDuration) {
            return
        }

        if (!state.recordingStartedAt) {
            if (!elements.recordDuration.textContent) {
                elements.recordDuration.textContent = '00:00:00'
            }
            return
        }

        const elapsedSeconds = Math.max(0, Math.floor((Date.now() - state.recordingStartedAt) / 1000))
        elements.recordDuration.textContent = formatDuration(elapsedSeconds)
    }

    const startRecordingTimer = () => {
        updateRecordingDuration()

        if (state.recordingTimer) {
            window.clearInterval(state.recordingTimer)
        }

        state.recordingTimer = window.setInterval(updateRecordingDuration, 1000)
    }

    const stopRecordingTimer = () => {
        if (state.recordingTimer) {
            window.clearInterval(state.recordingTimer)
            state.recordingTimer = null
        }
    }

    const updatePreviewControls = () => {
        if (elements.localVideo) {
            elements.localVideo.muted = !state.previewAudio
            elements.localVideo.style.transform = state.previewMirrored ? 'scaleX(-1)' : 'none'
        }

        if (elements.previewAudioButton) {
            elements.previewAudioButton.textContent = state.previewAudio ? 'Mutar preview' : 'Ouvir preview'
        }

        if (elements.previewMirrorButton) {
            elements.previewMirrorButton.textContent = state.previewMirrored ? 'Desespelhar camera' : 'Espelhar camera'
        }
    }

    const updateRecordingControls = () => {
        if (elements.recordStartButton) {
            elements.recordStartButton.disabled = !canBroadcast || liveId <= 0 || state.recordingUploadInFlight || state.recorder !== null
        }

        if (elements.recordStopButton) {
            elements.recordStopButton.disabled = state.recorder === null
        }
    }

    const clearReplayPlayback = () => {
        if (!elements.remoteVideo || !state.replayActive) {
            return
        }

        state.replayActive = false

        try {
            elements.remoteVideo.pause()
        } catch (error) {
            console.warn('Falha ao pausar replay', error)
        }

        elements.remoteVideo.removeAttribute('src')
        elements.remoteVideo.load()
    }

    const applyReplayPlayback = () => {
        if (!elements.remoteVideo || !replayEnabled || !replayUrl || state.replayActive) {
            return
        }

        state.replayActive = true
        elements.remoteVideo.srcObject = null
        elements.remoteVideo.src = replayUrl
        elements.remoteVideo.load()
        elements.remoteVideo.play().then(() => {
            hideWaiting()
            if (elements.playbackButton) {
                elements.playbackButton.classList.add('hidden')
            }
        }).catch(() => {
            hideWaiting()
            if (elements.playbackButton) {
                elements.playbackButton.classList.remove('hidden')
            }
        })
    }

    const updateStatus = (stream) => {
        const status = stream && stream.status ? String(stream.status) : 'idle'
        state.lastStreamStatus = status

        const replayMode = mode === 'viewer' && replayEnabled && replayUrl && status !== 'live'
        setText(elements.statusText, replayMode ? 'replay' : status === 'live' ? 'ao vivo' : status === 'ended' ? 'encerrada' : 'aguardando')
        setText(elements.streamState, replayMode ? 'replay' : status)
        updateViewerCount(stream && Number.isFinite(Number(stream.viewer_count)) ? Number(stream.viewer_count) : 0)

        if (elements.startButton) {
            elements.startButton.disabled = mode !== 'creator' || !canBroadcast || status === 'live'
        }

        if (elements.stopButton) {
            elements.stopButton.disabled = mode !== 'creator' || !canBroadcast || status !== 'live'
        }

        updateRecordingControls()

        if (mode === 'viewer') {
            if (status === 'live' && stream && stream.broadcaster_online) {
                clearReplayPlayback()
                hideWaiting()
            } else if (replayMode) {
                applyReplayPlayback()
            } else if (canWatch) {
                setWaiting('A live ainda nao esta transmitindo. Deixe esta pagina aberta que ela conecta sozinha.')
            }
        }
    }

    const responseJson = async (response) => {
        try {
            return await response.json()
        } catch (error) {
            return {
                ok: false,
                message: 'Resposta invalida da live.',
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

        return responseJson(response)
    }

    const postMultipart = async (url, formData) => {
        const response = await fetch(url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })

        return responseJson(response)
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

        return responseJson(response)
    }

    const shouldRejoin = (data) => {
        const message = String(data && data.message ? data.message : '').toLowerCase()

        return message.includes('sessao') || message.includes('entre novamente')
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
            state.pollAfterId = 0
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
            if (shouldRejoin(response)) {
                await rejoinSession('signal')
                return
            }

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

    const closePeerConnection = (peerConnection) => {
        if (!peerConnection) {
            return
        }

        try {
            peerConnection.onicecandidate = null
            peerConnection.ontrack = null
            peerConnection.onconnectionstatechange = null
            peerConnection.oniceconnectionstatechange = null
            peerConnection.close()
        } catch (error) {
            console.warn('Falha ao fechar peer connection', error)
        }
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

        peerConnection.oniceconnectionstatechange = () => {
            if (peerConnection.iceConnectionState === 'failed' && typeof peerConnection.restartIce === 'function') {
                try {
                    peerConnection.restartIce()
                } catch (error) {
                    console.warn('Falha ao reiniciar ICE', error)
                }
            }
        }

        peerConnection.onconnectionstatechange = () => {
            const connectionState = peerConnection.connectionState

            if (connectionState === 'failed' || connectionState === 'disconnected' || connectionState === 'closed') {
                if (asCreator) {
                    state.creatorPeerConnections.delete(remotePeerId)
                    closePeerConnection(peerConnection)
                } else if (state.viewerPeerConnection === peerConnection) {
                    closeViewerPeer()

                    if (state.lastStreamStatus === 'live') {
                        setWaiting('Conexao oscilou. Reconectando a transmissao...')
                        window.setTimeout(() => {
                            poll().catch((error) => {
                                console.warn('Reconexao da live falhou', error)
                            })
                        }, 500)
                    }
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
                    clearReplayPlayback()
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
            closePeerConnection(state.viewerPeerConnection)
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

        if (peerConnection.getTransceivers().length === 0) {
            peerConnection.addTransceiver('video', { direction: 'recvonly' })
            peerConnection.addTransceiver('audio', { direction: 'recvonly' })
        }

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
            closePeerConnection(peerConnection)
        })
        state.creatorPeerConnections.clear()
    }

    const closeViewerPeer = () => {
        if (state.viewerPeerConnection) {
            closePeerConnection(state.viewerPeerConnection)
            state.viewerPeerConnection = null
        }

        state.viewerTargetPeerId = ''
        state.remoteStream = null

        if (elements.remoteVideo) {
            if (!state.replayActive) {
                elements.remoteVideo.srcObject = null
            }
        }
    }

    const ensureLocalStream = async () => {
        const hasActiveLocalStream = state.localStream && state.localStream.getTracks().some((track) => track.readyState === 'live')

        if (hasActiveLocalStream) {
            return state.localStream
        }

        stopLocalStream()

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
            updatePreviewControls()
            elements.localVideo.play().catch(() => {})
        }

        return stream
    }

    const chooseRecorderMimeType = () => {
        if (typeof window.MediaRecorder === 'undefined') {
            return ''
        }

        const candidates = [
            'video/webm;codecs=vp9,opus',
            'video/webm;codecs=vp8,opus',
            'video/webm',
            'video/mp4',
        ]

        for (const mimeType of candidates) {
            if (typeof window.MediaRecorder.isTypeSupported !== 'function' || window.MediaRecorder.isTypeSupported(mimeType)) {
                return mimeType
            }
        }

        return ''
    }

    const uploadRecording = async (blob, durationSeconds) => {
        if (!recordingUploadUrl || liveId <= 0) {
            return { ok: false, message: 'Live invalida para salvar replay.' }
        }

        state.recordingUploadInFlight = true
        updateRecordingControls()

        const extension = state.recordingMimeType.includes('mp4') ? 'mp4' : 'webm'
        const formData = new FormData()
        formData.append('_token', csrf)
        formData.append('live_id', String(liveId))
        formData.append('recording_duration_seconds', String(durationSeconds))
        formData.append('recording_label', `Replay local ${new Date().toLocaleString('pt-BR')}`)
        formData.append('recording_file', blob, `live-${liveId}-${Date.now()}.${extension}`)

        const data = await postMultipart(recordingUploadUrl, formData)
        state.recordingUploadInFlight = false
        updateRecordingControls()

        if (data.ok && data.live) {
            const uploadedReplayUrl = data.live.recording_url || replayUrl
            setRecordLink(uploadedReplayUrl || '', uploadedReplayUrl || '')
            if (data.live.recording_duration_seconds) {
                setText(elements.recordDuration, formatDuration(data.live.recording_duration_seconds))
            }
        }

        return data
    }

    const setRecordingStatus = (label) => {
        setText(elements.recordStatus, label)
    }

    const startLocalRecording = async (autoMode = false) => {
        if (mode !== 'creator' || liveId <= 0) {
            return false
        }

        if (state.recorder !== null) {
            return true
        }

        if (typeof window.MediaRecorder === 'undefined') {
            showError('Seu navegador nao suporta gravacao local da live.')
            return false
        }

        const joined = await ensureJoined()
        if (!joined) {
            return false
        }

        await ensureLocalStream()

        const mimeType = chooseRecorderMimeType()
        const options = {}

        if (mimeType) {
            options.mimeType = mimeType
        }

        options.videoBitsPerSecond = Math.max(350000, bitrateKbps * 1000)

        try {
            state.recordingMimeType = mimeType || 'video/webm'
            state.recordingChunks = []
            state.recordingStartedAt = Date.now()
            state.recorder = new MediaRecorder(state.localStream, options)
            state.recorder.ondataavailable = (event) => {
                if (event.data && event.data.size > 0) {
                    state.recordingChunks.push(event.data)
                }
            }

            state.recorder.onstop = async () => {
                const chunks = [...state.recordingChunks]
                const durationSeconds = Math.max(1, Math.floor((Date.now() - state.recordingStartedAt) / 1000))
                state.recordingChunks = []
                state.recordingStartedAt = 0
                state.recorder = null
                stopRecordingTimer()
                updateRecordingControls()

                let success = true

                if (chunks.length > 0 && state.recordingShouldUpload) {
                    setRecordingStatus('Enviando replay para o servidor...')
                    const blob = new Blob(chunks, { type: state.recordingMimeType || 'video/webm' })
                    const uploadResult = await uploadRecording(blob, durationSeconds)

                    if (uploadResult.ok) {
                        setRecordingStatus('Replay salvo com sucesso.')
                    } else {
                        success = false
                        setRecordingStatus('Falha ao salvar replay.')
                        showError(uploadResult.message || 'Nao foi possivel salvar o replay local.')
                    }
                } else if (chunks.length > 0) {
                    setRecordingStatus('Gravacao encerrada localmente.')
                    setText(elements.recordDuration, formatDuration(durationSeconds))
                } else {
                    setRecordingStatus('Gravacao encerrada sem dados suficientes.')
                    success = false
                }

                const resolve = state.recordingStopResolver
                state.recordingStopResolver = null

                if (typeof resolve === 'function') {
                    resolve(success)
                }
            }

            state.recorder.start(1000)
            startRecordingTimer()
            setRecordingStatus(autoMode ? 'Replay automatico em gravacao...' : 'Gravacao local em andamento...')
            setRecordLink(replayUrl || '', replayUrl || '')
            showError('')
            updateRecordingControls()
            return true
        } catch (error) {
            state.recorder = null
            state.recordingChunks = []
            state.recordingStartedAt = 0
            stopRecordingTimer()
            updateRecordingControls()
            showError(error instanceof Error ? error.message : 'Nao foi possivel iniciar a gravacao local.')
            return false
        }
    }

    const stopLocalRecording = async (shouldUpload = true) => {
        if (!state.recorder) {
            return true
        }

        state.recordingShouldUpload = shouldUpload

        return new Promise((resolve) => {
            state.recordingStopResolver = resolve

            try {
                state.recorder.stop()
            } catch (error) {
                state.recordingStopResolver = null
                resolve(false)
            }
        })
    }

    const rejoinSession = async (reason) => {
        if (state.rejoinInFlight) {
            return state.rejoinInFlight
        }

        state.rejoinInFlight = (async () => {
            state.joined = false
            state.peerId = ''
            state.pollAfterId = 0

            if (mode === 'viewer') {
                closeViewerPeer()
            }

            const joined = await ensureJoined()
            if (!joined) {
                return false
            }

            if (mode === 'creator' && state.broadcasting && state.localStream) {
                const restart = await postForm(startUrl, {
                    live_id: liveId,
                    peer_id: state.peerId,
                    max_bitrate_kbps: bitrateKbps,
                    video_width: videoWidth,
                    video_height: videoHeight,
                    video_fps: videoFps,
                })

                if (!restart.ok) {
                    showError(restart.message || `Nao foi possivel restaurar a live apos ${reason}.`)
                    return false
                }

                updateStatus(restart.stream || {})
            }

            if (mode === 'viewer') {
                await poll()
            }

            return true
        })().finally(() => {
            state.rejoinInFlight = null
        })

        return state.rejoinInFlight
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
            return
        }

        if (shouldRejoin(data)) {
            await rejoinSession('heartbeat')
            return
        }

        showError(data.message || 'Nao foi possivel manter a live conectada.')
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
            if (shouldRejoin(data)) {
                await rejoinSession('poll')
                return
            }

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

                if (replayEnabled && replayUrl) {
                    applyReplayPlayback()
                }

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
            await ensureLocalStream()

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

            if (recordingEnabled) {
                await startLocalRecording(true)
            }

            await poll()
        } catch (error) {
            showError(error instanceof Error ? error.message : 'Nao foi possivel abrir camera e microfone.')
        }
    }

    const stopCreatorBroadcast = async () => {
        if (!state.peerId) {
            return
        }

        const recordingPromise = state.recorder ? stopLocalRecording(true) : Promise.resolve(true)

        const data = await postForm(stopUrl, {
            live_id: liveId,
            peer_id: state.peerId,
        })

        if (!data.ok) {
            showError(data.message || 'Nao foi possivel encerrar a transmissao.')
            return
        }

        await recordingPromise

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

    if (elements.previewAudioButton) {
        elements.previewAudioButton.addEventListener('click', async (event) => {
            event.preventDefault()
            state.previewAudio = !state.previewAudio

            if (state.previewAudio && !state.localStream) {
                try {
                    await ensureLocalStream()
                } catch (error) {
                    state.previewAudio = false
                    showError(error instanceof Error ? error.message : 'Nao foi possivel abrir o preview local.')
                }
            }

            updatePreviewControls()
        })
    }

    if (elements.previewMirrorButton) {
        elements.previewMirrorButton.addEventListener('click', (event) => {
            event.preventDefault()
            state.previewMirrored = !state.previewMirrored
            updatePreviewControls()
        })
    }

    if (elements.recordStartButton) {
        elements.recordStartButton.addEventListener('click', (event) => {
            event.preventDefault()
            startLocalRecording(false).catch((error) => {
                showError(error instanceof Error ? error.message : 'Falha ao iniciar a gravacao local.')
            })
        })
    }

    if (elements.recordStopButton) {
        elements.recordStopButton.addEventListener('click', (event) => {
            event.preventDefault()
            stopLocalRecording(true).catch((error) => {
                showError(error instanceof Error ? error.message : 'Falha ao encerrar a gravacao local.')
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
        if (state.recorder) {
            try {
                state.recordingShouldUpload = false
                state.recorder.stop()
            } catch (error) {
                console.warn('Falha ao encerrar a gravacao local', error)
            }
        }

        sendLeaveBeacon()
        stopLoops()
    })

    window.addEventListener('online', () => {
        if (!state.joined) {
            return
        }

        heartbeat().catch((error) => {
            console.warn('Falha ao retomar a live ao voltar a rede', error)
        })

        poll().catch((error) => {
            console.warn('Falha ao repoll da live ao voltar a rede', error)
        })
    })

    document.addEventListener('visibilitychange', () => {
        if (document.hidden || !state.joined) {
            return
        }

        heartbeat().catch((error) => {
            console.warn('Falha ao sincronizar a live depois de voltar para a aba', error)
        })
    })

    if (elements.roomLink) {
        elements.roomLink.textContent = elements.roomLink.getAttribute('href') || ''
    }

    setRecordLink(replayUrl || '', replayUrl || '')
    updatePreviewControls()
    updateRecordingControls()
    updateRecordingDuration()

    if (mode === 'creator') {
        if (!canBroadcast || liveId <= 0) {
            setWaiting('Crie ou selecione uma live para abrir a transmissao local.')
            return
        }

        ensureJoined().then((joined) => {
            if (!joined) {
                return
            }

            startLoops()
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
