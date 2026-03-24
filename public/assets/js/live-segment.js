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
    const accessMessage = root.dataset.accessMessage || ''
    const replayUrl = root.dataset.replayUrl || ''
    const replayEnabled = root.dataset.replayEnabled === '1' || replayUrl !== ''
    const joinUrl = root.dataset.joinUrl || '/live/rtc/join'
    const startUrl = root.dataset.startUrl || '/live/rtc/start'
    const stopUrl = root.dataset.stopUrl || '/live/rtc/stop'
    const pollUrl = root.dataset.pollUrl || '/live/rtc/poll'
    const heartbeatUrl = root.dataset.heartbeatUrl || '/live/rtc/heartbeat'
    const leaveUrl = root.dataset.leaveUrl || '/live/rtc/leave'
    const chunkUploadUrl = root.dataset.chunkUploadUrl || '/live/rtc/chunk'
    const bitrateKbps = Math.max(300, Number(root.dataset.maxBitrateKbps || 1200))
    const videoWidth = Math.max(320, Number(root.dataset.videoWidth || 960))
    const videoHeight = Math.max(240, Number(root.dataset.videoHeight || 540))
    const videoFps = Math.max(12, Number(root.dataset.videoFps || 24))
    const segmentDurationMs = Math.max(2000, Number(root.dataset.segmentDurationMs || 6000))

    const elements = {
        error: document.querySelector('[data-live-error]'),
        waitBox: document.querySelector('[data-live-waiting]'),
        waitText: document.querySelector('[data-live-waiting-text]'),
        statusText: document.querySelector('[data-live-status-text]'),
        streamState: document.querySelector('[data-live-stream-state]'),
        viewerCounts: document.querySelectorAll('[data-live-viewer-count]'),
        startButton: document.querySelector('[data-live-start]'),
        stopButton: document.querySelector('[data-live-stop]'),
        localVideo: document.querySelector('[data-live-local-video]'),
        remoteVideo: document.querySelector('[data-live-remote-video]'),
        playbackButton: document.querySelector('[data-live-playback]'),
        previewAudioButton: document.querySelector('[data-live-preview-audio]'),
        previewMirrorButton: document.querySelector('[data-live-preview-mirror]'),
        roomLink: document.querySelector('[data-live-room-link]'),
        recordStartButton: document.querySelector('[data-live-record-start]'),
        recordStopButton: document.querySelector('[data-live-record-stop]'),
        recordStatus: document.querySelector('[data-live-record-status]'),
    }

    const state = {
        peerId: '',
        joined: false,
        broadcasting: false,
        lastStreamStatus: 'idle',
        localStream: null,
        recorder: null,
        previewAudio: false,
        previewMirrored: true,
        nextSegmentSequence: 1,
        pendingUploads: [],
        uploadInFlight: false,
        drainResolvers: [],
        pollTimer: null,
        heartbeatTimer: null,
        lastKnownSequence: 0,
        initializedViewerQueue: false,
        segmentQueue: [],
        currentSegmentSequence: 0,
        queuedSegmentIds: new Set(),
        replayActive: false,
    }

    const liveStatusLabel = (status) => {
        if (status === 'live') {
            return 'ao vivo'
        }

        if (status === 'ended') {
            return 'encerrada'
        }

        if (liveId <= 0) {
            return 'sem live'
        }

        return 'aguardando'
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
        if (elements.waitBox) {
            elements.waitBox.classList.remove('hidden')
        }

        setText(elements.waitText, message)
    }

    const hideWaiting = () => {
        if (elements.waitBox) {
            elements.waitBox.classList.add('hidden')
        }
    }

    const updateViewerCount = (value) => {
        elements.viewerCounts.forEach((node) => {
            node.textContent = String(Number(value || 0))
        })
    }

    const setPlaybackButtonVisible = (visible) => {
        if (!elements.playbackButton) {
            return
        }

        elements.playbackButton.classList.toggle('hidden', !visible)
    }

    const setReplayStatus = (label) => {
        if (elements.recordStatus) {
            elements.recordStatus.textContent = label
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
            elements.previewMirrorButton.textContent = state.previewMirrored ? 'Desespelhar câmera' : 'Espelhar câmera'
        }
    }

    const updateStatus = (stream) => {
        const status = stream && stream.status ? String(stream.status) : 'idle'
        state.lastStreamStatus = status

        setText(elements.statusText, liveStatusLabel(status))
        setText(elements.streamState, status === 'live' ? 'transmitindo' : status === 'ended' ? 'live encerrada' : 'aguardando live')
        updateViewerCount(stream && Number.isFinite(Number(stream.viewer_count)) ? Number(stream.viewer_count) : 0)

        if (mode === 'creator') {
            if (elements.startButton) {
                elements.startButton.disabled = !canBroadcast || liveId <= 0 || status === 'live'
            }

            if (elements.stopButton) {
                elements.stopButton.disabled = !canBroadcast || liveId <= 0 || status !== 'live'
            }
        }
    }

    const responseJson = async (response) => {
        try {
            return await response.json()
        } catch (error) {
            return {
                ok: false,
                message: 'Resposta inválida da live.',
            }
        }
    }

    const postForm = async (url, payload) => {
        const body = new URLSearchParams()
        body.set('_token', csrf)

        Object.entries(payload || {}).forEach(([key, value]) => {
            if (value === undefined || value === null || value === '') {
                return
            }

            body.set(key, String(value))
        })

        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body,
        })

        return responseJson(response)
    }

    const postMultipart = async (url, formData) => {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
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

    const ensureJoined = async () => {
        if (state.joined && state.peerId) {
            return true
        }

        const data = await postForm(joinUrl, {
            live_id: liveId,
            role: mode === 'creator' ? 'creator' : 'viewer',
        })

        if (!data.ok) {
            showError(data.message || 'Não foi possível entrar na live.')
            if (mode === 'viewer') {
                setWaiting(data.message || accessMessage || 'Você não tem acesso a esta live.')
            }
            return false
        }

        state.peerId = data.peer_id || ''
        state.joined = state.peerId !== ''
        state.lastKnownSequence = mode === 'viewer' ? 0 : Number((data.stream && data.stream.latest_sequence) || 0)
        updateStatus(data.stream || {})
        showError('')

        return state.joined
    }

    const ensureLocalStream = async () => {
        if (state.localStream) {
            return state.localStream
        }

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

        state.localStream = stream

        if (elements.localVideo) {
            elements.localVideo.srcObject = stream
            elements.localVideo.play().catch(() => {})
            updatePreviewControls()
        }

        return stream
    }

    const stopLocalStream = () => {
        if (!state.localStream) {
            return
        }

        state.localStream.getTracks().forEach((track) => track.stop())
        state.localStream = null

        if (elements.localVideo) {
            elements.localVideo.srcObject = null
        }
    }

    const chooseRecorderMimeType = () => {
        if (typeof window.MediaRecorder === 'undefined') {
            return ''
        }

        const candidates = [
            'video/webm;codecs=vp9,opus',
            'video/webm;codecs=vp8,opus',
            'video/webm',
        ]

        for (const mimeType of candidates) {
            if (typeof window.MediaRecorder.isTypeSupported !== 'function' || window.MediaRecorder.isTypeSupported(mimeType)) {
                return mimeType
            }
        }

        return ''
    }

    const notifyQueueDrained = () => {
        if (state.uploadInFlight || state.pendingUploads.length > 0) {
            return
        }

        while (state.drainResolvers.length > 0) {
            const resolve = state.drainResolvers.shift()
            if (typeof resolve === 'function') {
                resolve()
            }
        }
    }

    const waitForUploadDrain = () => {
        if (!state.uploadInFlight && state.pendingUploads.length === 0) {
            return Promise.resolve()
        }

        return new Promise((resolve) => {
            state.drainResolvers.push(resolve)
        })
    }

    const processUploadQueue = async () => {
        if (state.uploadInFlight || state.pendingUploads.length === 0) {
            return
        }

        state.uploadInFlight = true

        while (state.pendingUploads.length > 0) {
            const item = state.pendingUploads.shift()

            if (!item) {
                continue
            }

            const formData = new FormData()
            formData.append('_token', csrf)
            formData.append('live_id', String(liveId))
            formData.append('peer_id', state.peerId)
            formData.append('segment_sequence', String(item.sequence))
            formData.append('segment_duration_ms', String(item.durationMs))
            formData.append('segment_file', item.blob, `live-${liveId}-${String(item.sequence).padStart(6, '0')}.${item.extension}`)
            formData.append('segment_mime_type', item.mimeType)

            const data = await postMultipart(chunkUploadUrl, formData)

            if (!data.ok) {
                showError(data.message || 'Falha ao enviar um trecho da live.')
                continue
            }

            updateStatus(data.stream || {})
            showError('')
        }

        state.uploadInFlight = false
        notifyQueueDrained()
    }

    const queueSegmentUpload = (blob, mimeType) => {
        const sequence = state.nextSegmentSequence
        state.nextSegmentSequence += 1
        const extension = mimeType.includes('mp4') ? 'mp4' : 'webm'

        state.pendingUploads.push({
            sequence,
            durationMs: segmentDurationMs,
            blob,
            mimeType,
            extension,
        })

        processUploadQueue().catch((error) => {
            showError(error instanceof Error ? error.message : 'Falha ao enviar partes da live.')
        })
    }

    const startSegmentRecorder = async () => {
        if (state.recorder || mode !== 'creator') {
            return true
        }

        await ensureLocalStream()

        if (typeof window.MediaRecorder === 'undefined') {
            showError('Seu navegador não suporta a captura desta live.')
            return false
        }

        const mimeType = chooseRecorderMimeType()
        const recorderOptions = {
            videoBitsPerSecond: Math.max(650000, Math.floor(bitrateKbps * 1000 * 0.88)),
            audioBitsPerSecond: 96000,
        }

        if (mimeType) {
            recorderOptions.mimeType = mimeType
        }

        state.recorder = new MediaRecorder(state.localStream, recorderOptions)
        state.recorder.ondataavailable = (event) => {
            if (event.data && event.data.size > 0) {
                queueSegmentUpload(event.data, state.recorder && state.recorder.mimeType ? state.recorder.mimeType : (mimeType || 'video/webm'))
            }
        }
        state.recorder.onerror = () => {
            showError('Falha ao capturar um trecho da live.')
        }
        state.recorder.start(segmentDurationMs)
        setReplayStatus('Live em andamento. A gravação local pode virar replay depois.')

        return true
    }

    const stopSegmentRecorder = async () => {
        if (!state.recorder) {
            await waitForUploadDrain()
            return
        }

        const recorder = state.recorder
        state.recorder = null

        await new Promise((resolve) => {
            recorder.addEventListener('stop', () => resolve(), { once: true })

            try {
                recorder.stop()
            } catch (error) {
                resolve()
            }
        })

        await waitForUploadDrain()
    }

    const startCreatorBroadcast = async () => {
        if (!canBroadcast || liveId <= 0) {
            return
        }

        const joined = await ensureJoined()
        if (!joined) {
            return
        }

        await ensureLocalStream()

        const data = await postForm(startUrl, {
            live_id: liveId,
            peer_id: state.peerId,
            segment_duration_seconds: Math.round(segmentDurationMs / 1000),
            max_bitrate_kbps: bitrateKbps,
            video_width: videoWidth,
            video_height: videoHeight,
            video_fps: videoFps,
        })

        if (!data.ok) {
            showError(data.message || 'Não foi possível iniciar a live.')
            return
        }

        state.nextSegmentSequence = 1
        state.pendingUploads = []
        state.broadcasting = true
        updateStatus(data.stream || {})
        setWaiting('Câmera aberta. Sua live está entrando no ar.')
        hideWaiting()
        await startSegmentRecorder()
        startLoops()
    }

    const stopCreatorBroadcast = async () => {
        if (!state.peerId) {
            stopLocalStream()
            return
        }

        await stopSegmentRecorder()

        const data = await postForm(stopUrl, {
            live_id: liveId,
            peer_id: state.peerId,
        })

        if (!data.ok) {
            showError(data.message || 'Não foi possível encerrar a live.')
            return
        }

        state.broadcasting = false
        stopLocalStream()
        updateStatus(data.stream || {})
        setWaiting('Live encerrada. Se houver replay salvo, ele continua disponível.')
    }

    const enqueueViewerSegments = (segments, bootstrap = false) => {
        if (!Array.isArray(segments) || segments.length === 0) {
            return
        }

        const normalized = segments
            .map((segment) => ({
                sequence: Number(segment.sequence || 0),
                url: segment.url || '',
                durationMs: Number(segment.duration_ms || segmentDurationMs),
            }))
            .filter((segment) => segment.sequence > 0 && segment.url !== '')

        const selected = bootstrap && normalized.length > 2 ? normalized.slice(-2) : normalized

        selected.forEach((segment) => {
            if (state.queuedSegmentIds.has(segment.sequence) || segment.sequence <= state.currentSegmentSequence) {
                return
            }

            state.queuedSegmentIds.add(segment.sequence)
            state.segmentQueue.push(segment)
        })

        state.segmentQueue.sort((left, right) => left.sequence - right.sequence)
    }

    const playReplayFallback = () => {
        if (!elements.remoteVideo || !replayEnabled || !replayUrl || state.replayActive) {
            return
        }

        state.replayActive = true
        elements.remoteVideo.srcObject = null
        elements.remoteVideo.src = replayUrl
        elements.remoteVideo.load()
        elements.remoteVideo.play().then(() => {
            hideWaiting()
            setPlaybackButtonVisible(false)
        }).catch(() => {
            setPlaybackButtonVisible(true)
        })
    }

    const playNextSegment = () => {
        if (!elements.remoteVideo) {
            return
        }

        if (state.segmentQueue.length === 0) {
            if (state.lastStreamStatus === 'live') {
                setWaiting('Aguardando a live continuar...')
            } else if (replayEnabled && replayUrl) {
                playReplayFallback()
            }
            return
        }

        const nextSegment = state.segmentQueue.shift()
        if (!nextSegment) {
            return
        }

        state.queuedSegmentIds.delete(nextSegment.sequence)
        state.currentSegmentSequence = nextSegment.sequence
        state.replayActive = false
        elements.remoteVideo.srcObject = null
        elements.remoteVideo.src = `${nextSegment.url}${nextSegment.url.includes('?') ? '&' : '?'}seq=${nextSegment.sequence}`
        elements.remoteVideo.load()
        elements.remoteVideo.play().then(() => {
            hideWaiting()
            setPlaybackButtonVisible(false)
        }).catch(() => {
            setWaiting('Toque para continuar assistindo a live.')
            setPlaybackButtonVisible(true)
        })
    }

    const poll = async () => {
        if (!state.peerId) {
            return
        }

        const data = await getJson(pollUrl, {
            live_id: liveId,
            peer_id: state.peerId,
            after_id: state.lastKnownSequence,
        })

        if (!data.ok) {
            showError(data.message || 'Não foi possível acompanhar a live.')
            return
        }

        const stream = data.stream || {}
        const segments = Array.isArray(data.segments) ? data.segments : []
        updateStatus(stream)
        showError('')

        if (mode === 'viewer' && canWatch) {
            if (state.replayActive && stream.status === 'live' && elements.remoteVideo) {
                state.replayActive = false
                elements.remoteVideo.pause()
                elements.remoteVideo.removeAttribute('src')
                elements.remoteVideo.load()
            }

            if (!state.initializedViewerQueue) {
                enqueueViewerSegments(segments, true)
                state.initializedViewerQueue = true
            } else {
                enqueueViewerSegments(segments, false)
            }

            const latestSequence = Number(stream.latest_sequence || 0)
            if (latestSequence > 0) {
                state.lastKnownSequence = Math.max(state.lastKnownSequence, latestSequence)
            } else if (segments.length > 0) {
                state.lastKnownSequence = Math.max(state.lastKnownSequence, ...segments.map((segment) => Number(segment.sequence || 0)))
            }

            if (state.currentSegmentSequence === 0 && elements.remoteVideo && elements.remoteVideo.paused) {
                playNextSegment()
            }
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
            return
        }

        showError(data.message || 'Não foi possível manter a live conectada.')
    }

    const startLoops = () => {
        if (!state.pollTimer) {
            state.pollTimer = window.setInterval(() => {
                poll().catch((error) => {
                    console.warn('Polling da live falhou', error)
                })
            }, 2500)
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

    if (elements.previewAudioButton) {
        elements.previewAudioButton.addEventListener('click', async (event) => {
            event.preventDefault()
            state.previewAudio = !state.previewAudio

            if (state.previewAudio && !state.localStream) {
                try {
                    await ensureLocalStream()
                } catch (error) {
                    state.previewAudio = false
                    showError(error instanceof Error ? error.message : 'Não foi possível abrir o preview local.')
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

    if (elements.remoteVideo) {
        elements.remoteVideo.addEventListener('ended', () => {
            state.currentSegmentSequence = 0
            playNextSegment()
        })

        elements.remoteVideo.addEventListener('playing', () => {
            hideWaiting()
            setPlaybackButtonVisible(false)
        })
    }

    if (elements.playbackButton && elements.remoteVideo) {
        elements.playbackButton.addEventListener('click', () => {
            elements.remoteVideo.play().then(() => {
                hideWaiting()
                setPlaybackButtonVisible(false)
            }).catch((error) => {
                showError(error instanceof Error ? error.message : 'Não foi possível continuar a reprodução.')
            })
        })
    }

    if (elements.roomLink) {
        elements.roomLink.textContent = elements.roomLink.getAttribute('href') || ''
    }

    if (elements.recordStartButton) {
        elements.recordStartButton.disabled = true
    }

    if (elements.recordStopButton) {
        elements.recordStopButton.disabled = true
    }

    setReplayStatus(replayEnabled ? 'Replay disponível pelo arquivo salvo.' : 'Replay desativado nesta live.')
    updatePreviewControls()

    window.addEventListener('beforeunload', () => {
        sendLeaveBeacon()
        stopLoops()
    })

    if (mode === 'creator') {
        if (!canBroadcast || liveId <= 0) {
            setWaiting('Crie ou selecione uma live para abrir o estúdio.')
            return
        }

        ensureJoined().then((joined) => {
            if (!joined) {
                return
            }

            startLoops()
            setWaiting('Preview pronto. Ao iniciar, sua live entra no ar para o público.')
        }).catch((error) => {
            showError(error instanceof Error ? error.message : 'Não foi possível preparar o estúdio.')
        })

        return
    }

    if (!canWatch) {
        setWaiting(accessMessage || 'Você não tem acesso a esta live.')
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
