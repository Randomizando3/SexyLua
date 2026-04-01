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
    const recordingUrl = root.dataset.recordingUrl || '/live/rtc/recording'
    const bitrateKbps = Math.max(300, Number(root.dataset.maxBitrateKbps || 1200))
    const videoWidth = Math.max(320, Number(root.dataset.videoWidth || 960))
    const videoHeight = Math.max(240, Number(root.dataset.videoHeight || 540))
    const videoFps = Math.max(12, Number(root.dataset.videoFps || 24))
    const segmentDurationMs = Math.max(2000, Number(root.dataset.segmentDurationMs || 10000))
    const maxDurationSeconds = Math.max(300, Number(root.dataset.maxDurationSeconds || 1800))
    const luacoinIconUrl = '/assets/img/luacoin.png'
    const viewerStartupMinSegments = 2
    const viewerAutoReloadEnabled = false
    const creatorEndedUrl = liveId > 0 ? `/creator/live?status=ended&live=${liveId}` : '/creator/live?status=ended'

    const elements = {
        error: document.querySelector('[data-live-error]'),
        waitBox: document.querySelector('[data-live-waiting]'),
        waitText: document.querySelector('[data-live-waiting-text]'),
        statusText: document.querySelector('[data-live-status-text]'),
        streamState: document.querySelector('[data-live-stream-state]'),
        endedBanner: document.querySelector('[data-live-ended-banner]'),
        priorityAlert: document.querySelector('[data-live-priority-alert]'),
        priorityAlertText: document.querySelector('[data-live-priority-alert-text]'),
        viewerCounts: document.querySelectorAll('[data-live-viewer-count]'),
        startButton: document.querySelector('[data-live-start]'),
        stopButton: document.querySelector('[data-live-stop]'),
        localVideo: document.querySelector('[data-live-local-video]'),
        remoteVideo: document.querySelector('[data-live-remote-video]'),
        remoteVideos: Array.from(document.querySelectorAll('[data-live-remote-video]')),
        playbackButton: document.querySelector('[data-live-playback]'),
        previewAudioButton: document.querySelector('[data-live-preview-audio]'),
        previewMirrorButton: document.querySelector('[data-live-preview-mirror]'),
        toggleAudioButton: document.querySelector('[data-live-toggle-audio]'),
        toggleVideoButton: document.querySelector('[data-live-toggle-video]'),
        videoDeviceSelect: document.querySelector('[data-live-video-device]'),
        audioDeviceSelect: document.querySelector('[data-live-audio-device]'),
        roomLink: document.querySelector('[data-live-room-link]'),
        recordStartButton: document.querySelector('[data-live-record-start]'),
        recordStopButton: document.querySelector('[data-live-record-stop]'),
        recordStatus: document.querySelector('[data-live-record-status]'),
        chatStream: document.querySelector('[data-live-chat-stream]'),
        chatEmpty: document.querySelector('[data-live-chat-empty]'),
        tipsStream: document.querySelector('[data-live-recent-tips]'),
        tipsEmpty: document.querySelector('[data-live-recent-tips-empty]'),
        supportersStream: document.querySelector('[data-live-top-supporters]'),
        supportersEmpty: document.querySelector('[data-live-top-supporters-empty]'),
        chatForm: document.querySelector('[data-live-chat-form]'),
        tipForm: document.querySelector('[data-live-tip-form]'),
        liveStartedAt: document.querySelector('[data-live-started-at]'),
        liveElapsed: document.querySelector('[data-live-elapsed]'),
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
        audioMuted: false,
        videoDisabled: false,
        selectedVideoDeviceId: '',
        selectedAudioDeviceId: '',
        videoDevices: [],
        audioDevices: [],
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
        viewerPlaybackStarted: false,
        archiveRecorder: null,
        archiveChunks: [],
        archiveBlobs: [],
        archiveMimeType: '',
        broadcastStartedAt: null,
        elapsedTimer: null,
        pollIntervalMs: 1000,
        heartbeatIntervalMs: 10000,
        viewerResumeTimer: null,
        viewerWatchdogTimer: null,
        segmentCycleTimer: null,
        segmentRestartTimer: null,
        activeViewerIndex: 0,
        preloadedSegment: null,
        preloadingSegment: null,
        viewerSwapPending: false,
        viewerStandbyPrimed: false,
        viewerStandbySequence: 0,
        viewerMseFailed: false,
        viewerMediaSource: null,
        viewerSourceBuffer: null,
        viewerMseObjectUrl: '',
        viewerMseMimeType: '',
        viewerMsePendingSegment: null,
        viewerMseAppending: false,
        viewerMseAppendedSegments: 0,
        viewerMseBufferedDurationMs: 0,
        viewerMseLastDurationMs: 0,
        viewerMseStartupTargetSeconds: 0,
        viewerMseStartupSeekApplied: false,
        viewerMseNeedsKick: false,
        viewerAutoReloadTriggered: false,
        viewerReloadTimer: null,
        priorityAlertTimer: null,
        priorityAlertBooted: false,
        lastPriorityAlertId: 0,
        priorityAlertAudioContext: null,
    }

    const viewerReloadStorageKey = liveId > 0 ? `sexylua-live-reload-${liveId}` : ''
    const viewerStallReloadStorageKey = liveId > 0 ? `sexylua-live-stall-reload-${liveId}` : ''
    const viewerResumeStorageKey = liveId > 0 ? `sexylua-live-resume-${liveId}` : ''

    const hasRecentStorageTimestamp = (storageKey, ttlMs = 20000) => {
        if (!storageKey || typeof window.sessionStorage === 'undefined') {
            return false
        }

        const storedValue = window.sessionStorage.getItem(storageKey)
        if (!storedValue) {
            return false
        }

        const timestamp = Number(storedValue)
        if (!Number.isFinite(timestamp)) {
            window.sessionStorage.removeItem(storageKey)
            return false
        }

        if ((Date.now() - timestamp) > ttlMs) {
            window.sessionStorage.removeItem(storageKey)
            return false
        }

        return true
    }

    const hasRecentViewerAutoReload = () => {
        return hasRecentStorageTimestamp(viewerReloadStorageKey)
    }

    const hasRecentViewerStallReload = () => {
        return hasRecentStorageTimestamp(viewerStallReloadStorageKey, 15000)
    }

    const markStorageTimestamp = (storageKey) => {
        if (!storageKey || typeof window.sessionStorage === 'undefined') {
            return
        }

        window.sessionStorage.setItem(storageKey, String(Date.now()))
    }

    const markViewerAutoReload = () => {
        markStorageTimestamp(viewerReloadStorageKey)
    }

    const markViewerStallReload = () => {
        markStorageTimestamp(viewerStallReloadStorageKey)
    }

    const markViewerResumeAfterReload = () => {
        markStorageTimestamp(viewerResumeStorageKey)
    }

    const shouldViewerResumeAfterReload = () => {
        return hasRecentStorageTimestamp(viewerResumeStorageKey, 20000)
    }

    const clearViewerResumeAfterReload = () => {
        if (!viewerResumeStorageKey || typeof window.sessionStorage === 'undefined') {
            return
        }

        window.sessionStorage.removeItem(viewerResumeStorageKey)
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

    const setControlButtonVisual = (button, icon, label) => {
        if (!button) {
            return
        }

        button.setAttribute('title', label)
        button.setAttribute('aria-label', label)

        const iconNode = button.querySelector('[data-live-control-icon]')
        const labelNode = button.querySelector('[data-live-control-label]')

        if (iconNode) {
            iconNode.textContent = icon
        }

        if (labelNode) {
            labelNode.textContent = label
            return
        }

        button.textContent = label
    }

    const setPlaybackButtonVisible = (visible) => {
        if (!elements.playbackButton) {
            return
        }

        elements.playbackButton.classList.toggle('hidden', !visible)
    }

    const updateViewerWarmupState = () => {
        if (mode !== 'viewer' || state.viewerPlaybackStarted) {
            return
        }

        if (state.lastStreamStatus === 'live' && !canStartViewerPlayback()) {
            setWaiting('A live está aquecendo. Entrando assim que os próximos segundos ficarem prontos...')
            setPlaybackButtonVisible(false)
        }
    }

    const setReplayStatus = (label) => {
        if (elements.recordStatus) {
            elements.recordStatus.textContent = label
        }
    }

    const hidePriorityAlert = () => {
        if (!elements.priorityAlert) {
            return
        }

        elements.priorityAlert.classList.add('hidden')
    }

    const playPriorityAlertTone = async () => {
        if (typeof window.AudioContext === 'undefined' && typeof window.webkitAudioContext === 'undefined') {
            return
        }

        const AudioContextClass = window.AudioContext || window.webkitAudioContext
        state.priorityAlertAudioContext = state.priorityAlertAudioContext || new AudioContextClass()

        if (state.priorityAlertAudioContext.state === 'suspended') {
            try {
                await state.priorityAlertAudioContext.resume()
            } catch (error) {
                return
            }
        }

        const context = state.priorityAlertAudioContext
        const now = context.currentTime
        const gain = context.createGain()
        gain.connect(context.destination)
        gain.gain.setValueAtTime(0.0001, now)
        gain.gain.exponentialRampToValueAtTime(0.16, now + 0.02)
        gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.75)

        const oscillator = context.createOscillator()
        oscillator.type = 'triangle'
        oscillator.frequency.setValueAtTime(740, now)
        oscillator.frequency.linearRampToValueAtTime(880, now + 0.18)
        oscillator.frequency.linearRampToValueAtTime(660, now + 0.42)
        oscillator.connect(gain)
        oscillator.start(now)
        oscillator.stop(now + 0.78)
    }

    const showPriorityAlert = (message) => {
        if (!elements.priorityAlert || !elements.priorityAlertText || !message) {
            return
        }

        elements.priorityAlertText.textContent = String(message)
        elements.priorityAlert.classList.remove('hidden')

        if (state.priorityAlertTimer) {
            window.clearTimeout(state.priorityAlertTimer)
        }

        state.priorityAlertTimer = window.setTimeout(() => {
            hidePriorityAlert()
        }, 5200)

        playPriorityAlertTone().catch(() => {})
    }

    const syncPriorityAlert = (alert) => {
        if (!state.priorityAlertBooted) {
            state.priorityAlertBooted = true
            state.lastPriorityAlertId = Number((alert && alert.id) || 0)
            return
        }

        if (!alert || typeof alert !== 'object') {
            return
        }

        const alertId = Number(alert.id || 0)
        if (!alertId || alertId <= state.lastPriorityAlertId) {
            return
        }

        state.lastPriorityAlertId = alertId
        showPriorityAlert(alert.alert_text || alert.body || 'Nova mensagem em destaque.')
    }

    const clearSegmentTimers = () => {
        if (state.segmentCycleTimer) {
            window.clearTimeout(state.segmentCycleTimer)
            state.segmentCycleTimer = null
        }

        if (state.segmentRestartTimer) {
            window.clearTimeout(state.segmentRestartTimer)
            state.segmentRestartTimer = null
        }
    }

    const stopViewerWatchdog = () => {
        if (state.viewerWatchdogTimer) {
            window.clearInterval(state.viewerWatchdogTimer)
            state.viewerWatchdogTimer = null
        }
    }

    const getReadyViewerSegmentCount = () => state.segmentQueue.length + (state.preloadedSegment ? 1 : 0)
    const getViewerStartupBufferCount = () => getReadyViewerSegmentCount() + state.viewerMseAppendedSegments + (state.viewerMsePendingSegment ? 1 : 0)

    const hasViewerDeck = mode === 'viewer' && elements.remoteVideos.length > 1
    const viewerCanUseMse = mode === 'viewer' && typeof window.MediaSource !== 'undefined' && !!elements.remoteVideo
    const viewerMseMimeCandidates = ['video/webm; codecs="vp9,opus"', 'video/webm; codecs="vp8,opus"', 'video/webm; codecs="vp8"', 'video/webm']

    const getViewerVideo = (index) => elements.remoteVideos[index] || null

    const getActiveViewerVideo = () => {
        if (!hasViewerDeck) {
            return elements.remoteVideo
        }

        return getViewerVideo(state.activeViewerIndex)
    }

    const getStandbyViewerIndex = () => (state.activeViewerIndex === 0 ? 1 : 0)

    const getStandbyViewerVideo = () => {
        if (!hasViewerDeck) {
            return null
        }

        return getViewerVideo(getStandbyViewerIndex())
    }

    const setViewerActiveIndex = (index) => {
        if (!hasViewerDeck) {
            return
        }

        state.activeViewerIndex = index
        elements.remoteVideos.forEach((video, videoIndex) => {
            const isActive = videoIndex === index
            video.classList.toggle('opacity-100', isActive)
            video.classList.toggle('opacity-0', !isActive)
            video.classList.toggle('pointer-events-none', !isActive)
            if (isActive) {
                video.classList.remove('pointer-events-none')
            }
        })
    }

    const clearViewerVideoElement = (video) => {
        if (!video) {
            return
        }

        try {
            video.pause()
        } catch (error) {
        }
        video.removeAttribute('src')
        video.srcObject = null
        video.load()
    }

    const getViewerRemainingSeconds = (video = getActiveViewerVideo()) => {
        if (!video || !Number.isFinite(video.duration) || video.duration <= 0) {
            return Number.POSITIVE_INFINITY
        }

        return Math.max(0, video.duration - video.currentTime)
    }

    const getViewerPrimeLeadSeconds = (video = getActiveViewerVideo()) => {
        const durationSeconds = Number.isFinite(video && video.duration) && video.duration > 0
            ? video.duration
            : segmentDurationMs / 1000

        return Math.max(1.65, Math.min(2.2, durationSeconds * 0.34))
    }

    const getViewerSwapLeadSeconds = (video = getActiveViewerVideo()) => {
        const primeLeadSeconds = getViewerPrimeLeadSeconds(video)
        return Math.max(0.55, Math.min(1.1, primeLeadSeconds * 0.55))
    }

    const resetViewerBufferedStart = () => {
        if (!hasViewerDeck) {
            return
        }

        const standbyVideo = getStandbyViewerVideo()
        if (state.preloadedSegment) {
            state.queuedSegmentIds.delete(state.preloadedSegment.sequence)
        }
        if (state.preloadingSegment) {
            state.queuedSegmentIds.delete(state.preloadingSegment.sequence)
        }
        state.preloadedSegment = null
        state.preloadingSegment = null
        state.viewerStandbyPrimed = false
        state.viewerStandbySequence = 0
        if (standbyVideo) {
            clearViewerVideoElement(standbyVideo)
        }
    }

    const playViewerSegment = (segment) => {
        if (!segment) {
            return false
        }

        const activeVideo = getActiveViewerVideo()
        if (!activeVideo) {
            return false
        }

        state.queuedSegmentIds.delete(Number(segment.sequence || 0))
        state.currentSegmentSequence = Number(segment.sequence || 0)
        state.replayActive = false
        clearViewerVideoElement(activeVideo)
        activeVideo.srcObject = null
        activeVideo.muted = false
        activeVideo.src = `${segment.url}${segment.url.includes('?') ? '&' : '?'}seq=${segment.sequence}`
        activeVideo.preload = 'auto'
        activeVideo.load()
        preloadStandbyViewerSegment()
        activeVideo.play().then(() => {
            state.viewerPlaybackStarted = true
            hideWaiting()
            setPlaybackButtonVisible(false)
        }).catch(() => {
            setWaiting('Toque para continuar assistindo a live.')
            setPlaybackButtonVisible(true)
        })

        return true
    }

    const startViewerFromLiveEdge = () => {
        if (mode !== 'viewer' || !canStartViewerPlayback()) {
            updateViewerWarmupState()
            return false
        }

        if (viewerCanUseMse && state.viewerMediaSource) {
            disableViewerMse()
        }

        const readySegments = []
        if (state.preloadedSegment) {
            readySegments.push(state.preloadedSegment)
        }
        state.segmentQueue.forEach((segment) => {
            readySegments.push(segment)
        })
        readySegments.sort((left, right) => Number(left.sequence || 0) - Number(right.sequence || 0))

        if (readySegments.length === 0) {
            updateViewerWarmupState()
            return false
        }

        const entrySegment = readySegments[readySegments.length - 1]
        const entrySequence = Number(entrySegment.sequence || 0)

        if (state.preloadedSegment && entrySequence === Number(state.preloadedSegment.sequence || 0)) {
            state.segmentQueue = state.segmentQueue.filter((segment) => {
                const keep = Number(segment.sequence || 0) > entrySequence
                if (!keep) {
                    state.queuedSegmentIds.delete(Number(segment.sequence || 0))
                }
                return keep
            })

            if (swapToPreloadedSegment()) {
                return true
            }
        }

        state.segmentQueue = state.segmentQueue.filter((segment) => {
            const keep = Number(segment.sequence || 0) > entrySequence
            if (!keep) {
                state.queuedSegmentIds.delete(Number(segment.sequence || 0))
            }
            return keep
        })
        resetViewerBufferedStart()

        return playViewerSegment(entrySegment)
    }

    const getViewerAvailableSegmentCount = () => {
        return state.segmentQueue.length + (state.preloadedSegment ? 1 : 0) + (state.preloadingSegment ? 1 : 0)
    }

    const resetViewerDeck = () => {
        state.preloadedSegment = null
        state.preloadingSegment = null
        state.viewerSwapPending = false
        state.viewerStandbyPrimed = false
        state.viewerStandbySequence = 0
        if (!hasViewerDeck) {
            return
        }

        clearViewerVideoElement(getViewerVideo(0))
        clearViewerVideoElement(getViewerVideo(1))
        setViewerActiveIndex(0)
    }

    const isViewerMseActive = () => viewerCanUseMse && !state.viewerMseFailed && !!state.viewerMediaSource

    const pickViewerMseMimeType = (segment) => {
        if (!viewerCanUseMse || typeof window.MediaSource === 'undefined') {
            return ''
        }

        const candidates = []
        const segmentMime = segment && typeof segment.mimeType === 'string' ? segment.mimeType.trim() : ''
        if (segmentMime !== '') {
            candidates.push(segmentMime)
        }
        viewerMseMimeCandidates.forEach((candidate) => candidates.push(candidate))

        const uniqueCandidates = candidates.filter((candidate, index) => candidate !== '' && candidates.indexOf(candidate) === index)
        return uniqueCandidates.find((candidate) => window.MediaSource.isTypeSupported(candidate)) || ''
    }

    const resetViewerMse = () => {
        state.viewerMseAppending = false
        state.viewerMsePendingSegment = null
        state.viewerMseAppendedSegments = 0
        state.viewerMseBufferedDurationMs = 0
        state.viewerMseLastDurationMs = 0
        state.viewerMseStartupTargetSeconds = 0
        state.viewerMseStartupSeekApplied = false
        state.viewerMseNeedsKick = false

        if (state.viewerSourceBuffer) {
            try {
                if (!state.viewerSourceBuffer.updating && state.viewerMediaSource && state.viewerMediaSource.readyState === 'open') {
                    state.viewerMediaSource.removeSourceBuffer(state.viewerSourceBuffer)
                }
            } catch (error) {
            }
        }

        const activeVideo = getViewerVideo(0) || elements.remoteVideo
        if (activeVideo) {
            try {
                activeVideo.pause()
            } catch (error) {
            }
            activeVideo.removeAttribute('src')
            activeVideo.load()
        }

        if (state.viewerMseObjectUrl) {
            try {
                window.URL.revokeObjectURL(state.viewerMseObjectUrl)
            } catch (error) {
            }
        }

        state.viewerMediaSource = null
        state.viewerSourceBuffer = null
        state.viewerMseObjectUrl = ''
        state.viewerMseMimeType = ''
    }

    const applyViewerMseStartupSeek = (video) => {
        if (!video || state.viewerMseStartupSeekApplied || state.viewerMseStartupTargetSeconds <= 0) {
            return
        }

        const targetSeconds = state.viewerMseStartupTargetSeconds

        const trySeek = () => {
            if (!Number.isFinite(video.duration) || video.duration < (targetSeconds - 0.05)) {
                return false
            }

            try {
                video.currentTime = targetSeconds
                state.viewerMseStartupSeekApplied = true
                return true
            } catch (error) {
                return false
            }
        }

        if (trySeek()) {
            return
        }

        const handleStartupSeek = () => {
            if (trySeek()) {
                video.removeEventListener('loadedmetadata', handleStartupSeek)
                video.removeEventListener('canplay', handleStartupSeek)
            }
        }

        video.addEventListener('loadedmetadata', handleStartupSeek, { once: true })
        video.addEventListener('canplay', handleStartupSeek, { once: true })
    }

    const kickViewerMsePlayback = (video) => {
        if (!video || !video.buffered || video.buffered.length === 0) {
            return false
        }

        try {
            const lastRangeIndex = video.buffered.length - 1
            const bufferedStart = Number(video.buffered.start(0))
            const bufferedEnd = Number(video.buffered.end(lastRangeIndex))

            if (!Number.isFinite(bufferedStart) || !Number.isFinite(bufferedEnd) || bufferedEnd <= bufferedStart) {
                return false
            }

            const currentTime = Number(video.currentTime || 0)
            const safeHeadroom = Math.max(0.05, Math.min(0.14, (state.viewerMseLastDurationMs / 1000) * 0.02 || 0.08))
            let targetTime = currentTime

            if (!Number.isFinite(targetTime) || targetTime < bufferedStart) {
                targetTime = bufferedStart + 0.05
            } else if ((bufferedEnd - targetTime) <= 0.04) {
                targetTime = Math.max(bufferedStart + 0.05, bufferedEnd - safeHeadroom)
            } else {
                targetTime = Math.min(bufferedEnd - 0.05, targetTime + 0.03)
            }

            if (Math.abs(targetTime - currentTime) >= 0.005) {
                video.currentTime = targetTime
            }

            return true
        } catch (error) {
            return false
        }
    }

    const disableViewerMse = () => {
        state.viewerMseFailed = true
        resetViewerMse()
        resetViewerDeck()
    }

    const startViewerWatchdog = () => {
        if (mode !== 'viewer' || state.viewerWatchdogTimer) {
            return
        }

        state.viewerWatchdogTimer = window.setInterval(() => {
            if (mode !== 'viewer' || isViewerMseActive()) {
                return
            }

            const activeVideo = getActiveViewerVideo()
            if (!activeVideo) {
                return
            }

            if (hasViewerDeck && !state.preloadingSegment && !state.preloadedSegment && state.segmentQueue.length > 0 && !state.viewerSwapPending) {
                preloadStandbyViewerSegment()
            }

            if (!hasViewerDeck || !state.preloadedSegment || state.viewerSwapPending) {
                return
            }

            const remaining = getViewerRemainingSeconds(activeVideo)
            if (Number.isFinite(remaining) && remaining <= getViewerPrimeLeadSeconds(activeVideo)) {
                primeStandbyViewerSegment()
            }

            if (canSwapToReadyViewerSegment(activeVideo)) {
                swapToPreloadedSegment()
                return
            }

            if (state.lastStreamStatus === 'live' && activeVideo.currentSrc && activeVideo.readyState < 3 && Number(activeVideo.currentTime || 0) >= 0.5) {
                scheduleViewerResume(45)
            }
        }, 70)
    }

    const triggerViewerHardRefresh = () => {
        if (mode !== 'viewer' || liveId <= 0 || typeof window.location === 'undefined') {
            return false
        }

        const currentUrl = new URL(window.location.href)
        currentUrl.searchParams.set('_live_refresh', String(Date.now()))
        markViewerStallReload()
        markViewerResumeAfterReload()
        window.location.replace(currentUrl.toString())
        return true
    }

    const shouldTriggerViewerHardRefresh = (video = getActiveViewerVideo()) => {
        if (mode !== 'viewer' || state.lastStreamStatus !== 'live' || !video) {
            return false
        }

        if (hasRecentViewerStallReload()) {
            return false
        }

        if (state.currentSegmentSequence > 1 || state.currentSegmentSequence <= 0) {
            return false
        }

        if (getViewerAvailableSegmentCount() <= 0) {
            return false
        }

        if (Number(video.currentTime || 0) < 0.9) {
            return false
        }

        return video.readyState < 3
    }

    const attemptViewerResumeAfterReload = () => {
        if (!shouldViewerResumeAfterReload()) {
            return
        }

        const activeVideo = getActiveViewerVideo()
        if (!activeVideo) {
            return
        }

        if (!activeVideo.currentSrc && !state.preloadedSegment && state.segmentQueue.length === 0) {
            return
        }

        if (!state.viewerPlaybackStarted && canStartViewerPlayback()) {
            if (startViewerFromLiveEdge()) {
                clearViewerResumeAfterReload()
                return
            }
        }

        if (hasViewerDeck && state.preloadedSegment && swapToPreloadedSegment()) {
            clearViewerResumeAfterReload()
            return
        }

        if (!activeVideo.currentSrc && canStartViewerPlayback()) {
            if (!startViewerFromLiveEdge()) {
                playNextSegment()
            }
            clearViewerResumeAfterReload()
            return
        }

        activeVideo.play().then(() => {
            hideWaiting()
            setPlaybackButtonVisible(false)
            clearViewerResumeAfterReload()
        }).catch(() => {
            setPlaybackButtonVisible(true)
        })
    }

    const finalizeViewerMseAppend = () => {
        if (!state.viewerMsePendingSegment) {
            state.viewerMseAppending = false
            return
        }

        const appendedSequence = Number(state.viewerMsePendingSegment.sequence || 0)
        const appendedDurationMs = Math.max(1000, Number(state.viewerMsePendingSegment.durationMs || segmentDurationMs))
        if (state.segmentQueue[0] && Number(state.segmentQueue[0].sequence || 0) === appendedSequence) {
            state.segmentQueue.shift()
        } else {
            state.segmentQueue = state.segmentQueue.filter((segment) => Number(segment.sequence || 0) !== appendedSequence)
        }

        state.queuedSegmentIds.delete(appendedSequence)
        state.currentSegmentSequence = appendedSequence
        state.viewerMsePendingSegment = null
        state.viewerMseAppending = false
        state.viewerMseAppendedSegments += 1
        state.viewerMseBufferedDurationMs += appendedDurationMs
        state.viewerMseLastDurationMs = appendedDurationMs

        if (!state.viewerPlaybackStarted && state.lastStreamStatus === 'live' && state.viewerMseAppendedSegments < viewerStartupMinSegments) {
            setWaiting('Preparando os primeiros segundos da live...')
            setPlaybackButtonVisible(false)
            pumpViewerMse()
            return
        }

        if (!state.viewerPlaybackStarted && state.lastStreamStatus === 'live' && !state.viewerMseStartupSeekApplied) {
            const startupTargetSeconds = Math.max(0, ((state.viewerMseBufferedDurationMs - state.viewerMseLastDurationMs) / 1000) + 0.08)
            if (startupTargetSeconds > 0) {
                state.viewerMseStartupTargetSeconds = startupTargetSeconds
            }
        }

        state.viewerPlaybackStarted = true

        const activeVideo = getViewerVideo(0) || elements.remoteVideo
        if (activeVideo) {
            activeVideo.muted = false
            applyViewerMseStartupSeek(activeVideo)
            if (state.viewerMseNeedsKick || activeVideo.readyState < 3) {
                kickViewerMsePlayback(activeVideo)
                state.viewerMseNeedsKick = false
            }
            activeVideo.play().then(() => {
                hideWaiting()
                setPlaybackButtonVisible(false)
            }).catch(() => {
                setPlaybackButtonVisible(true)
            })
        }

        pumpViewerMse()
    }

    const ensureViewerMse = (segment) => {
        if (!viewerCanUseMse || state.viewerMseFailed) {
            return false
        }

        if (state.viewerMediaSource && state.viewerSourceBuffer) {
            return true
        }

        const mimeType = pickViewerMseMimeType(segment)
        if (mimeType === '') {
            state.viewerMseFailed = true
            return false
        }

        if (state.viewerMediaSource) {
            return true
        }

        const activeVideo = getViewerVideo(0) || elements.remoteVideo
        if (!activeVideo) {
            state.viewerMseFailed = true
            return false
        }

        resetViewerDeck()
        setViewerActiveIndex(0)

        const mediaSource = new window.MediaSource()
        const objectUrl = window.URL.createObjectURL(mediaSource)
        state.viewerMediaSource = mediaSource
        state.viewerMseObjectUrl = objectUrl
        state.viewerMseMimeType = mimeType

        mediaSource.addEventListener('sourceopen', () => {
            if (!state.viewerMediaSource || state.viewerSourceBuffer) {
                return
            }

            try {
                const sourceBuffer = mediaSource.addSourceBuffer(mimeType)
                sourceBuffer.mode = 'sequence'
                sourceBuffer.addEventListener('updateend', finalizeViewerMseAppend)
                sourceBuffer.addEventListener('error', () => {
                    disableViewerMse()
                    scheduleViewerResume(120)
                })
                state.viewerSourceBuffer = sourceBuffer
                pumpViewerMse()
            } catch (error) {
                disableViewerMse()
                scheduleViewerResume(120)
            }
        }, { once: true })

        activeVideo.srcObject = null
        activeVideo.autoplay = false
        activeVideo.muted = false
        activeVideo.src = objectUrl
        activeVideo.load()

        return true
    }

    const pumpViewerMse = async () => {
        if (!viewerCanUseMse || state.viewerMseFailed || state.viewerMseAppending || !state.segmentQueue.length) {
            return
        }

        const nextSegment = state.segmentQueue[0]
        if (!nextSegment || !ensureViewerMse(nextSegment) || !state.viewerSourceBuffer || state.viewerSourceBuffer.updating) {
            return
        }

        state.viewerMseAppending = true
        state.viewerMsePendingSegment = nextSegment

        try {
            const response = await fetch(`${nextSegment.url}${nextSegment.url.includes('?') ? '&' : '?'}seq=${nextSegment.sequence}&mse=1`, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })

            if (!response.ok) {
                throw new Error('Falha ao carregar o segmento da live.')
            }

            const buffer = await response.arrayBuffer()
            if (!state.viewerSourceBuffer || state.viewerSourceBuffer.updating) {
                throw new Error('Buffer da live indisponível.')
            }

            state.viewerSourceBuffer.appendBuffer(buffer)
        } catch (error) {
            state.viewerMseAppending = false
            state.viewerMsePendingSegment = null
            disableViewerMse()
            scheduleViewerResume(120)
        }
    }

    const canStartViewerPlayback = (streamStatus = state.lastStreamStatus) => {
        if (mode !== 'viewer') {
            return true
        }

        if (isViewerMseActive() && streamStatus === 'live' && !state.viewerPlaybackStarted) {
            return state.viewerMseAppendedSegments >= viewerStartupMinSegments
        }

        const readySegments = getViewerStartupBufferCount()

        if (readySegments === 0) {
            return false
        }

        if (streamStatus !== 'live') {
            return true
        }

        if (state.viewerPlaybackStarted) {
            return true
        }

        return readySegments >= viewerStartupMinSegments
    }

    const activateReadyViewerSegment = (videoIndex) => {
        if (mode !== 'viewer' || !hasViewerDeck || isViewerMseActive()) {
            return
        }

        if (videoIndex !== getStandbyViewerIndex() || !state.preloadingSegment || state.preloadedSegment) {
            return
        }

        state.preloadedSegment = state.preloadingSegment
        state.preloadingSegment = null

        const activeVideo = getActiveViewerVideo()
        const activeIdle = !activeVideo || !activeVideo.currentSrc || activeVideo.ended || state.currentSegmentSequence === 0
        const remaining = getViewerRemainingSeconds(activeVideo)

        if (activeVideo && remaining <= getViewerPrimeLeadSeconds(activeVideo)) {
            primeStandbyViewerSegment()
        }

        const shouldSwapImmediately = activeIdle
            || remaining <= getViewerSwapLeadSeconds(activeVideo)
            || (activeVideo && activeVideo.readyState < 3 && Number(activeVideo.currentTime || 0) >= 0.75)

        if (shouldSwapImmediately && (state.viewerPlaybackStarted || canStartViewerPlayback())) {
            swapToPreloadedSegment()
            return
        }

        if (!state.viewerPlaybackStarted && canStartViewerPlayback()) {
            playNextSegment()
        }
    }

    const scheduleViewerResume = (delayMs = 220) => {
        if (mode !== 'viewer') {
            return
        }

        if (state.viewerResumeTimer) {
            window.clearTimeout(state.viewerResumeTimer)
        }

        state.viewerResumeTimer = window.setTimeout(() => {
            state.viewerResumeTimer = null

            const currentVideo = getActiveViewerVideo()
            if (!currentVideo) {
                return
            }

            if (hasViewerDeck && state.preloadedSegment) {
                primeStandbyViewerSegment()
            }

            if (canSwapToReadyViewerSegment(currentVideo)) {
                if (swapToPreloadedSegment()) {
                    return
                }
            }

            if (state.currentSegmentSequence !== 0 && !currentVideo.ended && !currentVideo.paused) {
                return
            }

            if (hasViewerDeck && state.preloadedSegment && canStartViewerPlayback() && (state.currentSegmentSequence === 0 || currentVideo.ended || !currentVideo.currentSrc)) {
                if (swapToPreloadedSegment()) {
                    return
                }
            }

            if (!canStartViewerPlayback()) {
                return
            }

            playNextSegment()
        }, delayMs)
    }

    const escapeHtml = (value) => String(value || '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;')

    const luacoinHtml = (amount, sizeClass = 'h-3.5 w-3.5') => {
        const numericAmount = Number(amount || 0)
        return `<span class="inline-flex items-center gap-1 whitespace-nowrap"><img alt="" class="${sizeClass} shrink-0" src="${luacoinIconUrl}"><span>${escapeHtml(numericAmount)}</span></span>`
    }

    const renderChatMessages = (messages) => {
        if (!elements.chatStream) {
            return
        }

        if (!Array.isArray(messages) || messages.length === 0) {
            elements.chatStream.innerHTML = ''
            if (elements.chatEmpty) {
                elements.chatEmpty.classList.remove('hidden')
            }
            return
        }

        const variant = elements.chatStream.dataset.liveChatVariant || 'viewer'
        const html = messages.map((message) => {
            const senderName = escapeHtml((message && message.sender && message.sender.name) || 'Visitante')
            const body = escapeHtml((message && message.body) || '')
            const highlighted = Boolean(message && message.is_highlighted)
            const theme = message && message.highlight_theme ? message.highlight_theme : {}
            const badgeLabel = escapeHtml((message && message.highlight_label) || 'Destaque')
            const wrapperStyle = highlighted
                ? `background:${escapeHtml(theme.background || '#fff6cf')};border-color:${escapeHtml(theme.border || '#fde68a')};`
                : ''
            const badgeStyle = `background:${escapeHtml(theme.label_background || '#f59e0b')};color:${escapeHtml(theme.label_text || '#ffffff')};`

            if (variant === 'creator') {
                return `
                    <div class="rounded-2xl border p-4 text-sm" style="${highlighted ? wrapperStyle : 'background:#f5f3f5;border-color:transparent;'}">
                        <div class="flex items-center justify-between gap-3">
                            <span class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-[#D81B60]">${senderName}</span>
                            ${highlighted ? `<span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em]" style="${badgeStyle}">${badgeLabel}</span>` : ''}
                        </div>
                        ${body}
                    </div>
                `
            }

            return `
                <div class="flex flex-col gap-1">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-bold tracking-wide text-[#ab1155]">${senderName}</span>
                        ${highlighted ? `<span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em]" style="${badgeStyle}">${badgeLabel}</span>` : ''}
                    </div>
                    <p class="rounded-2xl rounded-tl-none border bg-white p-3 text-sm text-on-surface-variant shadow-sm" style="${highlighted ? wrapperStyle : 'border-color:transparent;'}">${body}</p>
                </div>
            `
        }).join('')

        elements.chatStream.innerHTML = html
        if (elements.chatEmpty) {
            elements.chatEmpty.classList.add('hidden')
        }
    }

    const renderRecentTips = (tips) => {
        if (!elements.tipsStream) {
            return
        }

        if (!Array.isArray(tips) || tips.length === 0) {
            elements.tipsStream.innerHTML = ''
            if (elements.tipsEmpty) {
                elements.tipsEmpty.classList.remove('hidden')
            }
            return
        }

        const variant = elements.tipsStream.dataset.liveTipsVariant || 'viewer'
        const html = tips.map((tip) => {
            const senderName = escapeHtml((tip && tip.sender && tip.sender.name) || 'Fan')
            const amountHtml = luacoinHtml((tip && tip.amount) || 0, variant === 'creator' ? 'h-4 w-4' : 'h-3.5 w-3.5')

            if (variant === 'creator') {
                return `
                    <div class="flex items-center justify-between rounded-2xl bg-[#f5f3f5] px-4 py-3 text-sm">
                        <span class="font-bold text-slate-700">${senderName}</span>
                        <span class="font-black text-[#D81B60]">${amountHtml}</span>
                    </div>
                `
            }

            return `
                <div class="flex items-center justify-between rounded-full bg-white px-4 py-2 text-xs">
                    <span class="font-bold text-on-surface">${senderName}</span>
                    <span class="font-black text-[#ab1155]">${amountHtml}</span>
                </div>
            `
        }).join('')

        elements.tipsStream.innerHTML = html
        if (elements.tipsEmpty) {
            elements.tipsEmpty.classList.add('hidden')
        }
    }

    const renderTopSupporters = (supporters) => {
        if (!elements.supportersStream) {
            return
        }

        if (!Array.isArray(supporters) || supporters.length === 0) {
            elements.supportersStream.innerHTML = ''
            if (elements.supportersEmpty) {
                elements.supportersEmpty.classList.remove('hidden')
            }
            return
        }

        const variant = elements.supportersStream.dataset.liveSupportersVariant || 'viewer'
        const html = supporters.map((supporter) => {
            const user = supporter && supporter.user ? supporter.user : {}
            const name = escapeHtml(user.name || 'Fan')
            const initials = escapeHtml(String(user.name || 'Fan').split(/\s+/).filter(Boolean).slice(0, 2).map((part) => part.charAt(0).toUpperCase()).join('') || 'F')
            const amountHtml = luacoinHtml((supporter && supporter.amount) || 0, variant === 'creator' ? 'h-4 w-4' : 'h-3 w-3')

            if (variant === 'creator') {
                return `
                    <div class="rounded-2xl bg-[#f5f3f5] p-4 text-center">
                        <div class="signature-glow mx-auto flex h-12 w-12 items-center justify-center rounded-full text-sm font-bold text-white">${initials}</div>
                        <p class="mt-3 text-sm font-bold text-slate-800">${name}</p>
                        <p class="mt-1 text-xs font-semibold text-[#D81B60]">${amountHtml}</p>
                    </div>
                `
            }

            return `
                <div class="flex flex-col items-center">
                    <div class="signature-glow flex h-12 w-12 items-center justify-center rounded-full text-sm font-bold text-white">${initials}</div>
                    <span class="mt-2 text-[10px] font-bold text-[#ab1155]">${name}</span>
                    <span class="text-[10px] text-slate-500">${amountHtml}</span>
                </div>
            `
        }).join('')

        elements.supportersStream.innerHTML = html
        if (elements.supportersEmpty) {
            elements.supportersEmpty.classList.add('hidden')
        }
    }

    const refreshLivePanels = (payload) => {
        if (!payload || typeof payload !== 'object') {
            return
        }

        if (Array.isArray(payload.chat_messages)) {
            renderChatMessages(payload.chat_messages)
        }

        if (Array.isArray(payload.recent_tips)) {
            renderRecentTips(payload.recent_tips)
        }

        if (Array.isArray(payload.top_supporters)) {
            renderTopSupporters(payload.top_supporters)
        }

        syncPriorityAlert(payload.priority_alert || null)
    }

    const updatePreviewControls = () => {
        if (elements.localVideo) {
            elements.localVideo.muted = !state.previewAudio
            elements.localVideo.style.transform = state.previewMirrored ? 'scaleX(-1)' : 'none'
        }

        if (elements.previewAudioButton) {
            setControlButtonVisual(elements.previewAudioButton, state.previewAudio ? 'volume_off' : 'volume_up', state.previewAudio ? 'Mutar preview' : 'Ouvir preview')
        }

        if (elements.previewMirrorButton) {
            setControlButtonVisual(elements.previewMirrorButton, state.previewMirrored ? 'flip_camera_android' : 'flip_camera_ios', state.previewMirrored ? 'Desespelhar câmera' : 'Espelhar câmera')
        }
    }

    const syncExtraPreviewControls = () => {
        if (elements.toggleAudioButton) {
            setControlButtonVisual(elements.toggleAudioButton, state.audioMuted ? 'mic_off' : 'mic', state.audioMuted ? 'Desmutar microfone' : 'Mutar microfone')
        }

        if (elements.toggleVideoButton) {
            setControlButtonVisual(elements.toggleVideoButton, state.videoDisabled ? 'videocam_off' : 'videocam', state.videoDisabled ? 'Ligar câmera' : 'Desligar câmera')
        }
    }

    const updateStatus = (stream) => {
        const status = stream && stream.status ? String(stream.status) : 'idle'
        state.lastStreamStatus = status
        if (status === 'live' && stream && stream.started_at) {
            const startedAt = new Date(stream.started_at)
            if (!Number.isNaN(startedAt.getTime())) {
                state.broadcastStartedAt = startedAt.getTime()
            }
        } else if (status !== 'live' && !state.broadcasting) {
            state.broadcastStartedAt = null
        }

        setText(elements.statusText, liveStatusLabel(status))
        setText(elements.streamState, status === 'live' ? 'transmitindo' : status === 'ended' ? 'live encerrada' : 'aguardando live')
        updateViewerCount(stream && Number.isFinite(Number(stream.viewer_count)) ? Number(stream.viewer_count) : 0)

        if (elements.endedBanner) {
            elements.endedBanner.classList.toggle('hidden', status !== 'ended')
        }

        if (status === 'ended') {
            hidePriorityAlert()
        }

        if (mode === 'creator') {
            if (elements.startButton) {
                elements.startButton.disabled = !canBroadcast || liveId <= 0 || status === 'live'
                elements.startButton.classList.toggle('hidden', status === 'live')
            }

            if (elements.stopButton) {
                elements.stopButton.disabled = !canBroadcast || liveId <= 0 || status !== 'live'
                elements.stopButton.classList.toggle('hidden', status !== 'live')
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

    const formPayload = (form) => {
        const payload = {}
        const formData = new FormData(form)

        formData.forEach((value, key) => {
            payload[key] = value
        })

        return payload
    }

    const toggleFormBusy = (form, busy) => {
        Array.from(form.elements || []).forEach((field) => {
            if (field instanceof HTMLElement && 'disabled' in field) {
                field.disabled = busy
            }
        })
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
        state.pollIntervalMs = Math.max(800, Number(data.poll_interval_ms || state.pollIntervalMs || 1000))
        state.heartbeatIntervalMs = Math.max(5000, Number(data.heartbeat_interval_ms || state.heartbeatIntervalMs || 10000))
        updateStatus(data.stream || {})
        showError('')

        return state.joined
    }

    const updateLiveTimeIndicators = () => {
        if (elements.liveStartedAt && state.broadcastStartedAt) {
            const startedAt = new Date(state.broadcastStartedAt)
            if (!Number.isNaN(startedAt.getTime())) {
                elements.liveStartedAt.textContent = startedAt.toLocaleString('pt-BR')
            }
        }

        if (elements.liveElapsed) {
            if (!state.broadcastStartedAt) {
                elements.liveElapsed.textContent = '00:00'
                return
            }

            const elapsedSeconds = Math.max(0, Math.floor((Date.now() - state.broadcastStartedAt) / 1000))
            const minutes = String(Math.floor(elapsedSeconds / 60)).padStart(2, '0')
            const seconds = String(elapsedSeconds % 60).padStart(2, '0')
            elements.liveElapsed.textContent = `${minutes}:${seconds}`
        }
    }

    const startElapsedTimer = () => {
        if (state.elapsedTimer) {
            window.clearInterval(state.elapsedTimer)
        }

        updateLiveTimeIndicators()
        state.elapsedTimer = window.setInterval(() => {
            updateLiveTimeIndicators()

            if (state.broadcastStartedAt && state.broadcasting) {
                const elapsedSeconds = Math.max(0, Math.floor((Date.now() - state.broadcastStartedAt) / 1000))
                if (elapsedSeconds >= maxDurationSeconds) {
                    stopCreatorBroadcast('limit').catch((error) => {
                        showError(error instanceof Error ? error.message : 'Falha ao encerrar a live no limite configurado.')
                    })
                }
            }
        }, 1000)
    }

    const stopElapsedTimer = () => {
        if (state.elapsedTimer) {
            window.clearInterval(state.elapsedTimer)
            state.elapsedTimer = null
        }

        if (!state.broadcasting) {
            updateLiveTimeIndicators()
        }
    }

    const populateDeviceSelect = (select, devices, selectedId, placeholder) => {
        if (!select) {
            return
        }

        const options = devices.map((device, index) => {
            const selected = device.deviceId === selectedId ? ' selected' : ''
            const label = escapeHtml(device.label || `${placeholder} ${index + 1}`)
            return `<option value="${escapeHtml(device.deviceId)}"${selected}>${label}</option>`
        }).join('')

        select.innerHTML = options || `<option value="">${placeholder}</option>`
    }

    const refreshDeviceLists = async () => {
        if (!navigator.mediaDevices || typeof navigator.mediaDevices.enumerateDevices !== 'function') {
            return
        }

        const devices = await navigator.mediaDevices.enumerateDevices()
        state.videoDevices = devices.filter((device) => device.kind === 'videoinput')
        state.audioDevices = devices.filter((device) => device.kind === 'audioinput')

        const currentVideoTrack = state.localStream ? state.localStream.getVideoTracks()[0] : null
        const currentAudioTrack = state.localStream ? state.localStream.getAudioTracks()[0] : null

        state.selectedVideoDeviceId = state.selectedVideoDeviceId || ((currentVideoTrack && currentVideoTrack.getSettings && currentVideoTrack.getSettings().deviceId) || '')
        state.selectedAudioDeviceId = state.selectedAudioDeviceId || ((currentAudioTrack && currentAudioTrack.getSettings && currentAudioTrack.getSettings().deviceId) || '')

        populateDeviceSelect(elements.videoDeviceSelect, state.videoDevices, state.selectedVideoDeviceId, 'Nenhuma câmera encontrada')
        populateDeviceSelect(elements.audioDeviceSelect, state.audioDevices, state.selectedAudioDeviceId, 'Nenhum microfone encontrado')
    }

    const buildMediaConstraints = () => {
        const video = {
            width: { ideal: videoWidth, max: videoWidth },
            height: { ideal: videoHeight, max: videoHeight },
            frameRate: { ideal: videoFps, max: videoFps },
        }

        if (state.selectedVideoDeviceId) {
            video.deviceId = { exact: state.selectedVideoDeviceId }
        }

        const audio = {
            echoCancellation: true,
            noiseSuppression: true,
            autoGainControl: true,
        }

        if (state.selectedAudioDeviceId) {
            audio.deviceId = { exact: state.selectedAudioDeviceId }
        }

        return { video, audio }
    }

    const ensureLocalStream = async (forceRefresh = false) => {
        if (state.localStream && !forceRefresh) {
            return state.localStream
        }

        if (forceRefresh && state.localStream) {
            state.localStream.getTracks().forEach((track) => track.stop())
            state.localStream = null
        }

        const stream = await navigator.mediaDevices.getUserMedia(buildMediaConstraints())
        state.localStream = stream

        const audioTrack = stream.getAudioTracks()[0]
        const videoTrack = stream.getVideoTracks()[0]
        if (audioTrack) {
            audioTrack.enabled = !state.audioMuted
        }
        if (videoTrack) {
            videoTrack.enabled = !state.videoDisabled
        }

        if (elements.localVideo) {
            elements.localVideo.srcObject = stream
            elements.localVideo.play().catch(() => {})
            updatePreviewControls()
            syncExtraPreviewControls()
        }

        await refreshDeviceLists()

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

    const startArchiveRecorder = async () => {
        if (state.archiveRecorder || !state.localStream || typeof window.MediaRecorder === 'undefined') {
            return
        }

        const mimeType = chooseRecorderMimeType()
        const options = {
            videoBitsPerSecond: Math.max(650000, Math.floor(bitrateKbps * 1000 * 0.88)),
            audioBitsPerSecond: 96000,
        }

        if (mimeType) {
            options.mimeType = mimeType
        }

        state.archiveChunks = []
        state.archiveMimeType = mimeType || 'video/webm'
        state.archiveRecorder = new MediaRecorder(state.localStream, options)
        state.archiveRecorder.ondataavailable = (event) => {
            if (event.data && event.data.size > 0) {
                state.archiveChunks.push(event.data)
            }
        }
        state.archiveRecorder.start()
    }

    const stopArchiveRecorder = async () => {
        if (!state.archiveRecorder) {
            return null
        }

        const recorder = state.archiveRecorder
        state.archiveRecorder = null

        await new Promise((resolve) => {
            recorder.addEventListener('stop', () => resolve(), { once: true })

            try {
                recorder.stop()
            } catch (error) {
                resolve()
            }
        })

        const mimeType = recorder.mimeType || state.archiveMimeType || 'video/webm'
        if (state.archiveChunks.length === 0) {
            return null
        }

        const blob = new Blob(state.archiveChunks, { type: mimeType })
        state.archiveChunks = []

        return {
            blob,
            mimeType,
            durationSeconds: state.broadcastStartedAt ? Math.max(0, Math.floor((Date.now() - state.broadcastStartedAt) / 1000)) : 0,
        }
    }

    const captureReplayThumbnail = async () => {
        if (!elements.localVideo) {
            return null
        }

        const width = Number(elements.localVideo.videoWidth || videoWidth)
        const height = Number(elements.localVideo.videoHeight || videoHeight)
        if (width <= 0 || height <= 0) {
            return null
        }

        const canvas = document.createElement('canvas')
        canvas.width = width
        canvas.height = height
        const context = canvas.getContext('2d')
        if (!context) {
            return null
        }

        context.drawImage(elements.localVideo, 0, 0, width, height)

        return new Promise((resolve) => {
            canvas.toBlob((blob) => resolve(blob || null), 'image/jpeg', 0.82)
        })
    }

    const uploadReplayRecording = async (recording, thumbnailBlob = null) => {
        if (!recording || !recording.blob || recording.blob.size === 0) {
            return null
        }

        const extension = recording.mimeType.includes('mp4') ? 'mp4' : 'webm'
        const formData = new FormData()
        formData.append('_token', csrf)
        formData.append('live_id', String(liveId))
        formData.append('recording_duration_seconds', String(recording.durationSeconds || 0))
        formData.append('recording_label', 'Replay automático da live')
        formData.append('recording_file', recording.blob, `replay-live-${liveId}.${extension}`)
        if (thumbnailBlob && thumbnailBlob.size > 0) {
            formData.append('thumbnail_file', thumbnailBlob, `replay-live-${liveId}.jpg`)
        }

        const data = await postMultipart(recordingUrl, formData)
        if (!data.ok) {
            if (data.code === 'storage_quota_exceeded') {
                const manageReplay = window.confirm(data.message || 'Sem espaço para salvar o replay automático. Deseja abrir Meus Conteúdos para excluir um replay antigo?')
                if (manageReplay) {
                    const targetUrl = data.redirect_url || '/creator/content'
                    const separator = targetUrl.includes('?') ? '&' : '?'
                    window.location.href = `${targetUrl}${separator}kind=video`
                    return null
                }
            }
            showError(data.message || 'A live encerrou, mas não foi possível salvar o replay.')
            return null
        }

        return data
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

    const queueSegmentUpload = (blob, mimeType, durationMs = segmentDurationMs) => {
        const sequence = state.nextSegmentSequence
        state.nextSegmentSequence += 1
        const extension = mimeType.includes('mp4') ? 'mp4' : 'webm'

        state.pendingUploads.push({
            sequence,
            durationMs: Math.max(1000, Number(durationMs || segmentDurationMs)),
            blob,
            mimeType,
            extension,
        })

        processUploadQueue().catch((error) => {
            showError(error instanceof Error ? error.message : 'Falha ao enviar partes da live.')
        })
    }

    const startSegmentRecorder = async () => {
        if (mode !== 'creator') {
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

        if (state.recorder || state.segmentCycleTimer || state.segmentRestartTimer) {
            return true
        }

        const beginRecorderSlice = () => {
            if (!state.broadcasting || !state.localStream) {
                return
            }

            const sliceDurationMs = segmentDurationMs

            const recorder = new MediaRecorder(state.localStream, recorderOptions)
            state.recorder = recorder

            recorder.ondataavailable = (event) => {
                if (event.data && event.data.size > 0) {
                    queueSegmentUpload(event.data, recorder.mimeType || mimeType || 'video/webm', sliceDurationMs)
                }
            }
            recorder.onerror = () => {
                showError('Falha ao capturar um trecho da live.')
            }
            recorder.onstop = () => {
                if (state.recorder === recorder) {
                    state.recorder = null
                }

                if (!state.broadcasting) {
                    return
                }

                state.segmentRestartTimer = window.setTimeout(() => {
                    state.segmentRestartTimer = null
                    beginRecorderSlice()
                }, 60)
            }

            recorder.start()
            state.segmentCycleTimer = window.setTimeout(() => {
                state.segmentCycleTimer = null

                try {
                    if (recorder.state !== 'inactive') {
                        recorder.stop()
                    }
                } catch (error) {
                    state.recorder = null
                }
            }, sliceDurationMs)
        }

        beginRecorderSlice()
        setReplayStatus('Live em andamento.')

        return true
    }

    const stopSegmentRecorder = async () => {
        clearSegmentTimers()

        if (!state.recorder) {
            await waitForUploadDrain()
            return
        }

        const recorder = state.recorder
        state.recorder = null

        await new Promise((resolve) => {
            recorder.addEventListener('stop', () => resolve(), { once: true })

            try {
                if (recorder.state !== 'inactive') {
                    recorder.stop()
                } else {
                    resolve()
                }
            } catch (error) {
                resolve()
            }
        })

        await waitForUploadDrain()
    }

    const restartCaptureAfterDeviceChange = async () => {
        const wasBroadcasting = state.broadcasting
        const previewAudioWasOn = state.previewAudio

        if (wasBroadcasting) {
            state.broadcasting = false
            await stopSegmentRecorder()
            const partialRecording = await stopArchiveRecorder()
            if (partialRecording && partialRecording.blob && partialRecording.blob.size > 0) {
                state.archiveBlobs.push(partialRecording.blob)
            }
        }

        await ensureLocalStream(true)

        if (previewAudioWasOn && elements.localVideo) {
            elements.localVideo.muted = false
        }

        if (wasBroadcasting) {
            state.broadcasting = true
            await startSegmentRecorder()
            await startArchiveRecorder()
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
        state.nextSegmentSequence = 1
        state.broadcastStartedAt = Date.now()
        updateStatus(data.stream || {})
        setWaiting('Câmera aberta. Sua live está entrando no ar.')
        hideWaiting()
        await startSegmentRecorder()
        await startArchiveRecorder()
        startElapsedTimer()
        startLoops()
    }

    const stopCreatorBroadcast = async (reason = 'manual') => {
        if (!state.peerId) {
            stopLocalStream()
            return
        }

        const replayThumbnail = await captureReplayThumbnail()
        state.broadcasting = false
        await stopSegmentRecorder()
        const recording = await stopArchiveRecorder()

        const data = await postForm(stopUrl, {
            live_id: liveId,
            peer_id: state.peerId,
        })

        if (!data.ok) {
            showError(data.message || 'Não foi possível encerrar a live.')
            return
        }

        stopLoops()
        const replayBlobParts = []
        if (state.archiveBlobs.length > 0) {
            replayBlobParts.push(...state.archiveBlobs)
        }
        if (recording && recording.blob && recording.blob.size > 0) {
            replayBlobParts.push(recording.blob)
        }
        state.archiveBlobs = []
        const mergedRecording = replayBlobParts.length > 0
            ? {
                ...(recording || { durationSeconds: 0, mimeType: 'video/webm' }),
                blob: new Blob(replayBlobParts, { type: (recording && recording.mimeType) || 'video/webm' }),
            }
            : recording
        const replayResult = await uploadReplayRecording(mergedRecording, replayThumbnail)
        stopLocalStream()
        state.joined = false
        state.peerId = ''
        updateStatus(data.stream || {})
        stopElapsedTimer()
        setWaiting('Live encerrada. Você já pode preparar a próxima sessão.')
        const recordingDuration = mergedRecording ? Number(mergedRecording.durationSeconds || 0) : 0
        const durationLabel = data.duration_label || `${Math.floor(recordingDuration / 60)} minutos e ${String(recordingDuration % 60).padStart(2, '0')} segundos`
        const replaySuffix = replayResult && replayResult.content_id ? ' Para controlar o replay vá em Meus Conteúdos.' : ''
        window.alert(`Live "${data.title || 'Live'}" encerrada com sucesso, duração ${durationLabel}.${replaySuffix}`)
        if (reason === 'limit') {
            showError('A live foi encerrada automaticamente porque atingiu o limite máximo configurado.')
        }
        state.broadcastStartedAt = null
        updateLiveTimeIndicators()
        window.setTimeout(() => {
            window.location.assign(creatorEndedUrl)
        }, 180)
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
                mimeType: segment.mime_type || segment.mimeType || '',
            }))
            .filter((segment) => segment.sequence > 0 && segment.url !== '')

        normalized.forEach((segment) => {
            if (state.queuedSegmentIds.has(segment.sequence) || segment.sequence <= state.currentSegmentSequence) {
                return
            }

            state.queuedSegmentIds.add(segment.sequence)
            state.segmentQueue.push(segment)
        })

        state.segmentQueue.sort((left, right) => left.sequence - right.sequence)

        if (state.segmentQueue.length > 0) {
            scheduleViewerResume(120)
        }
    }

    const preloadStandbyViewerSegment = () => {
        if (!hasViewerDeck || state.replayActive || state.viewerSwapPending) {
            return
        }

        if (state.preloadedSegment || state.preloadingSegment || state.segmentQueue.length === 0) {
            return
        }

        const standbyVideo = getStandbyViewerVideo()
        if (!standbyVideo) {
            return
        }

        const nextSegment = state.segmentQueue.shift()
        if (!nextSegment) {
            return
        }

        state.preloadingSegment = nextSegment
        state.viewerStandbyPrimed = false
        state.viewerStandbySequence = 0
        clearViewerVideoElement(standbyVideo)
        standbyVideo.muted = true
        standbyVideo.preload = 'auto'
        standbyVideo.src = `${nextSegment.url}${nextSegment.url.includes('?') ? '&' : '?'}seq=${nextSegment.sequence}&preload=1`
        standbyVideo.load()
    }

    const primeStandbyViewerSegment = () => {
        if (!hasViewerDeck || !state.preloadedSegment || state.viewerSwapPending || state.viewerStandbyPrimed) {
            return
        }

        const standbyVideo = getStandbyViewerVideo()
        if (!standbyVideo || !standbyVideo.currentSrc) {
            return
        }

        state.viewerStandbyPrimed = true
        state.viewerStandbySequence = state.preloadedSegment.sequence
        standbyVideo.muted = true
        standbyVideo.play().catch(() => {
            state.viewerStandbyPrimed = false
            state.viewerStandbySequence = 0
        })
    }

    const playReplayFallback = () => {
        if (!elements.remoteVideo || !replayEnabled || !replayUrl || state.replayActive) {
            return
        }

        state.replayActive = true
        state.viewerPlaybackStarted = true
        resetViewerMse()
        resetViewerDeck()
        const video = getActiveViewerVideo()
        if (!video) {
            return
        }

        video.srcObject = null
        video.src = replayUrl
        video.load()
        video.play().then(() => {
            hideWaiting()
            setPlaybackButtonVisible(false)
        }).catch(() => {
            setPlaybackButtonVisible(true)
        })
    }

    const canSwapToReadyViewerSegment = (video = getActiveViewerVideo()) => {
        if (mode !== 'viewer' || !hasViewerDeck || !state.preloadedSegment || state.viewerSwapPending || !video) {
            return false
        }

        if (video.ended) {
            return true
        }

        const remaining = getViewerRemainingSeconds(video)
        if (Number.isFinite(remaining)) {
            return remaining <= getViewerSwapLeadSeconds(video)
        }

        return video.readyState < 3 && Number(video.currentTime || 0) >= 0.75
    }

    const swapToPreloadedSegment = () => {
        if (!hasViewerDeck || !state.preloadedSegment || state.viewerSwapPending) {
            return false
        }

        const standbyIndex = getStandbyViewerIndex()
        const standbyVideo = getViewerVideo(standbyIndex)
        const activeVideo = getActiveViewerVideo()
        const nextSegment = state.preloadedSegment

        if (!standbyVideo || !activeVideo) {
            return false
        }

        state.viewerSwapPending = true
        state.replayActive = false

        const finishSwap = () => {
            activeVideo.muted = true
            state.currentSegmentSequence = nextSegment.sequence
            state.queuedSegmentIds.delete(nextSegment.sequence)
            state.preloadedSegment = null
            state.preloadingSegment = null
            state.viewerStandbyPrimed = false
            state.viewerStandbySequence = 0
            state.viewerPlaybackStarted = true
            setViewerActiveIndex(standbyIndex)
            hideWaiting()
            setPlaybackButtonVisible(false)
            window.requestAnimationFrame(() => {
                standbyVideo.muted = false
                window.setTimeout(() => {
                    try {
                        activeVideo.pause()
                    } catch (error) {
                    }
                    clearViewerVideoElement(activeVideo)
                    activeVideo.muted = false
                    state.viewerSwapPending = false
                    preloadStandbyViewerSegment()
                }, 220)
            })
        }

        if (state.viewerStandbyPrimed && state.viewerStandbySequence === nextSegment.sequence) {
            finishSwap()
        } else {
            standbyVideo.play().then(() => {
                finishSwap()
            }).catch(() => {
                state.viewerStandbyPrimed = false
                state.viewerStandbySequence = 0
                state.viewerSwapPending = false
                setWaiting('Toque para continuar assistindo a live.')
                setPlaybackButtonVisible(true)
            })
        }

        return true
    }

    const playNextSegment = () => {
        if (viewerCanUseMse && !state.viewerMseFailed) {
            pumpViewerMse()
            return
        }

        const activeVideo = getActiveViewerVideo()
        if (!activeVideo) {
            return
        }

        if (!canStartViewerPlayback()) {
            setWaiting(state.lastStreamStatus === 'live' ? 'Preparando transmissão ao vivo...' : 'Preparando replay...')
            return
        }

        if (hasViewerDeck && state.preloadedSegment && (state.viewerPlaybackStarted || canStartViewerPlayback()) && (state.currentSegmentSequence === 0 || activeVideo.ended || activeVideo.paused || activeVideo.currentSrc === '')) {
            if (swapToPreloadedSegment()) {
                return
            }
        }

        if (state.segmentQueue.length === 0) {
            if (state.lastStreamStatus === 'live') {
                setWaiting('Preparando transmissão ao vivo...')
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
        clearViewerVideoElement(activeVideo)
        activeVideo.srcObject = null
        activeVideo.muted = false
        activeVideo.src = `${nextSegment.url}${nextSegment.url.includes('?') ? '&' : '?'}seq=${nextSegment.sequence}`
        activeVideo.preload = 'auto'
        activeVideo.load()
        preloadStandbyViewerSegment()
        activeVideo.play().then(() => {
            state.viewerPlaybackStarted = true
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

        const previousStatus = state.lastStreamStatus
        const stream = data.stream || {}
        const segments = Array.isArray(data.segments) ? data.segments : []
        updateStatus(stream)
        refreshLivePanels(data)
        showError('')

        if (mode === 'viewer' && canWatch) {
            const liveHasStartupBuffer = Number(stream.latest_sequence || 0) >= viewerStartupMinSegments || segments.length >= viewerStartupMinSegments
            if (viewerAutoReloadEnabled && previousStatus !== 'live' && stream.status === 'live' && liveHasStartupBuffer && !state.viewerPlaybackStarted && !state.viewerAutoReloadTriggered && !hasRecentViewerAutoReload()) {
                state.viewerAutoReloadTriggered = true
                setWaiting('A live começou. Atualizando a sala...')
                if (state.viewerReloadTimer) {
                    window.clearTimeout(state.viewerReloadTimer)
                }
                state.viewerReloadTimer = window.setTimeout(() => {
                    markViewerAutoReload()
                    window.location.reload()
                }, 450)
                return
            }

            if (state.replayActive && stream.status === 'live' && elements.remoteVideo) {
                state.replayActive = false
                resetViewerMse()
                resetViewerDeck()
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

            if (!state.viewerPlaybackStarted && stream.status === 'live') {
                if (viewerCanUseMse && !state.viewerMseFailed) {
                    pumpViewerMse()
                    updateViewerWarmupState()
                } else {
                    preloadStandbyViewerSegment()
                    if (!startViewerFromLiveEdge()) {
                        updateViewerWarmupState()
                    }
                }
                attemptViewerResumeAfterReload()
                return
            }

            if (viewerCanUseMse && !state.viewerMseFailed && stream.status === 'live') {
                pumpViewerMse()
            } else {
                preloadStandbyViewerSegment()

                const currentVideo = getActiveViewerVideo()
                if (currentVideo && (state.currentSegmentSequence === 0 || currentVideo.paused || currentVideo.ended || currentVideo.currentSrc === '')) {
                    playNextSegment()
                }
            }

            attemptViewerResumeAfterReload()
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
            }, state.pollIntervalMs)
        }

        if (!state.heartbeatTimer) {
            state.heartbeatTimer = window.setInterval(() => {
                heartbeat().catch((error) => {
                    console.warn('Heartbeat da live falhou', error)
                })
            }, state.heartbeatIntervalMs)
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

        if (state.viewerResumeTimer) {
            window.clearTimeout(state.viewerResumeTimer)
            state.viewerResumeTimer = null
        }

        if (state.viewerReloadTimer) {
            window.clearTimeout(state.viewerReloadTimer)
            state.viewerReloadTimer = null
        }

        stopViewerWatchdog()
        resetViewerMse()
        clearSegmentTimers()
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

    if (elements.toggleAudioButton) {
        elements.toggleAudioButton.addEventListener('click', async (event) => {
            event.preventDefault()

            if (!state.localStream) {
                await ensureLocalStream()
            }

            const audioTrack = state.localStream ? state.localStream.getAudioTracks()[0] : null
            if (!audioTrack) {
                return
            }

            audioTrack.enabled = !audioTrack.enabled
            state.audioMuted = !audioTrack.enabled
            syncExtraPreviewControls()
        })
    }

    if (elements.toggleVideoButton) {
        elements.toggleVideoButton.addEventListener('click', async (event) => {
            event.preventDefault()

            if (!state.localStream) {
                await ensureLocalStream()
            }

            const videoTrack = state.localStream ? state.localStream.getVideoTracks()[0] : null
            if (!videoTrack) {
                return
            }

            videoTrack.enabled = !videoTrack.enabled
            state.videoDisabled = !videoTrack.enabled
            syncExtraPreviewControls()
        })
    }

    if (elements.videoDeviceSelect) {
        elements.videoDeviceSelect.addEventListener('change', (event) => {
            state.selectedVideoDeviceId = event.target.value || ''
            restartCaptureAfterDeviceChange().catch((error) => {
                showError(error instanceof Error ? error.message : 'Não foi possível trocar a câmera.')
            })
        })
    }

    if (elements.audioDeviceSelect) {
        elements.audioDeviceSelect.addEventListener('change', (event) => {
            state.selectedAudioDeviceId = event.target.value || ''
            restartCaptureAfterDeviceChange().catch((error) => {
                showError(error instanceof Error ? error.message : 'Não foi possível trocar o microfone.')
            })
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

    if (elements.chatForm) {
        elements.chatForm.addEventListener('submit', async (event) => {
            event.preventDefault()

            const bodyField = elements.chatForm.querySelector('[name="body"]')
            if (!(bodyField instanceof HTMLTextAreaElement) || bodyField.value.trim() === '') {
                return
            }

            toggleFormBusy(elements.chatForm, true)

            try {
                const data = await postForm(elements.chatForm.getAttribute('action') || '/live/chat', formPayload(elements.chatForm))

                if (!data.ok) {
                    showError(data.message || 'Não foi possível enviar sua mensagem.')
                    return
                }

                bodyField.value = ''
                showError('')
                await poll()
            } catch (error) {
                showError(error instanceof Error ? error.message : 'Não foi possível enviar sua mensagem.')
            } finally {
                toggleFormBusy(elements.chatForm, false)
            }
        })
    }

    if (elements.tipForm) {
        elements.tipForm.querySelectorAll('[data-live-tip-preset]').forEach((button) => {
            button.addEventListener('click', () => {
                const amountField = elements.tipForm.querySelector('[name="amount"]')
                if (amountField instanceof HTMLInputElement) {
                    amountField.value = button.getAttribute('data-live-tip-preset') || '25'
                }
            })
        })

        elements.tipForm.addEventListener('submit', async (event) => {
            event.preventDefault()

            toggleFormBusy(elements.tipForm, true)

            try {
                const data = await postForm(elements.tipForm.getAttribute('action') || '/tip', formPayload(elements.tipForm))

                if (!data.ok) {
                    showError(data.message || 'Não foi possível enviar a gorjeta.')
                    return
                }

                const amountField = elements.tipForm.querySelector('[name="amount"]')
                if (amountField instanceof HTMLInputElement) {
                    amountField.value = amountField.defaultValue || '25'
                }

                showError('')
                await poll()
            } catch (error) {
                showError(error instanceof Error ? error.message : 'Não foi possível enviar a gorjeta.')
            } finally {
                toggleFormBusy(elements.tipForm, false)
            }
        })
    }

    elements.remoteVideos.forEach((video, index) => {
        video.addEventListener('canplay', () => {
            activateReadyViewerSegment(index)
        })

        video.addEventListener('canplaythrough', () => {
            activateReadyViewerSegment(index)
        })

        video.addEventListener('timeupdate', () => {
            if (mode !== 'viewer' || !hasViewerDeck || isViewerMseActive() || index !== state.activeViewerIndex || !state.preloadedSegment || state.viewerSwapPending) {
                return
            }

            const remaining = getViewerRemainingSeconds(video)
            if (!Number.isFinite(remaining)) {
                return
            }

            if (remaining <= getViewerPrimeLeadSeconds(video)) {
                primeStandbyViewerSegment()
            }

            if (remaining <= getViewerSwapLeadSeconds(video)) {
                swapToPreloadedSegment()
            }
        })

        video.addEventListener('ended', () => {
            if (mode !== 'viewer') {
                return
            }

            if (isViewerMseActive()) {
                return
            }

            if (hasViewerDeck && index !== state.activeViewerIndex) {
                return
            }

            state.currentSegmentSequence = 0
            if (hasViewerDeck && state.preloadedSegment) {
                if (swapToPreloadedSegment()) {
                    return
                }
            }

            if (!canStartViewerPlayback()) {
                setWaiting(state.lastStreamStatus === 'live' ? 'Preparando transmissão ao vivo...' : 'Replay pronto para assistir.')
                return
            }
            playNextSegment()
        })

        video.addEventListener('playing', () => {
            if (mode === 'viewer' && hasViewerDeck && !isViewerMseActive() && index !== state.activeViewerIndex && !state.viewerSwapPending) {
                return
            }

            hideWaiting()
            setPlaybackButtonVisible(false)
        })

        video.addEventListener('waiting', () => {
            if (mode !== 'viewer') {
                return
            }

            if (isViewerMseActive()) {
                state.viewerMseNeedsKick = true
                pumpViewerMse()
                return
            }

            if (hasViewerDeck && index !== state.activeViewerIndex) {
                return
            }

            if (hasViewerDeck && state.preloadedSegment) {
                primeStandbyViewerSegment()
                if (canSwapToReadyViewerSegment(video) && swapToPreloadedSegment()) {
                    return
                }
            }

            if (state.lastStreamStatus === 'live') {
                setWaiting('Sincronizando os próximos segundos da live...')
                scheduleViewerResume(90)
            }
        })

        video.addEventListener('error', () => {
            if (mode !== 'viewer') {
                return
            }

            if (isViewerMseActive()) {
                disableViewerMse()
                scheduleViewerResume(120)
                return
            }

            if (hasViewerDeck && index !== state.activeViewerIndex) {
                if (state.preloadedSegment) {
                    state.queuedSegmentIds.delete(state.preloadedSegment.sequence)
                }
                state.preloadingSegment = null
                state.preloadedSegment = null
                state.viewerStandbyPrimed = false
                state.viewerStandbySequence = 0
                clearViewerVideoElement(video)
                preloadStandbyViewerSegment()
                return
            }

            state.currentSegmentSequence = 0
            clearViewerVideoElement(video)
            if (state.lastStreamStatus === 'live') {
                setWaiting('Reconectando a transmissão...')
                scheduleViewerResume(180)
            }
        })

        video.addEventListener('stalled', () => {
            if (mode !== 'viewer') {
                return
            }

            if (isViewerMseActive()) {
                state.viewerMseNeedsKick = true
                pumpViewerMse()
                return
            }

            if (hasViewerDeck && index !== state.activeViewerIndex) {
                return
            }

            if (hasViewerDeck && state.preloadedSegment) {
                primeStandbyViewerSegment()
                if (canSwapToReadyViewerSegment(video) && swapToPreloadedSegment()) {
                    return
                }
            }

            if (state.lastStreamStatus === 'live') {
                setWaiting('Sincronizando os próximos segundos da live...')
                scheduleViewerResume(140)
            }
        })
    })

    if (elements.playbackButton && elements.remoteVideo) {
        elements.playbackButton.addEventListener('click', () => {
            const video = getActiveViewerVideo()
            if (!video) {
                return
            }

            if (viewerCanUseMse && !state.viewerMseFailed) {
                pumpViewerMse()
                video.play().catch(() => {
                })
            }

            video.play().then(() => {
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

    setReplayStatus(replayEnabled ? 'Arquivo salvo.' : '')
    updatePreviewControls()
    syncExtraPreviewControls()
    if (hasViewerDeck) {
        setViewerActiveIndex(0)
    }
    if (mode === 'viewer') {
        elements.remoteVideos.forEach((video) => {
            video.autoplay = false
        })
    }

    window.addEventListener('beforeunload', () => {
        sendLeaveBeacon()
        stopLoops()
        stopElapsedTimer()
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

            ensureLocalStream().catch((error) => {
                showError(error instanceof Error ? error.message : 'Não foi possível abrir câmera e microfone.')
            })
            startLoops()
            startElapsedTimer()
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
        startViewerWatchdog()
        state.viewerPlaybackStarted = false
        poll().catch((error) => {
            showError(error instanceof Error ? error.message : 'Falha ao carregar a live.')
        })
    }).catch((error) => {
        showError(error instanceof Error ? error.message : 'Falha ao entrar na live.')
    })
})()
