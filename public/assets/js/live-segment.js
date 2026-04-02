(() => {
    const root = document.querySelector('[data-live-rtc-mode]')
    if (!root) return

    const mode = root.dataset.liveRtcMode || 'viewer'
    const liveId = Number(root.dataset.liveId || 0)
    const csrf = root.dataset.csrf || ''
    const canWatch = root.dataset.canWatch !== '0'
    const canBroadcast = root.dataset.canBroadcast === '1'
    const accessMessage = root.dataset.accessMessage || ''
    const joinUrl = root.dataset.joinUrl || '/live/rtc/join'
    const startUrl = root.dataset.startUrl || '/live/rtc/start'
    const stopUrl = root.dataset.stopUrl || '/live/rtc/stop'
    const pollUrl = root.dataset.pollUrl || '/live/rtc/poll'
    const heartbeatUrl = root.dataset.heartbeatUrl || '/live/rtc/heartbeat'
    const leaveUrl = root.dataset.leaveUrl || '/live/rtc/leave'
    const hlsUrl = root.dataset.hlsUrl || ''
    const replayUrl = root.dataset.replayUrl || ''
    const replayEnabled = root.dataset.replayEnabled === '1' || replayUrl !== ''
    const bitrateKbps = Math.max(300, Number(root.dataset.maxBitrateKbps || 800))
    const videoWidth = Math.max(320, Number(root.dataset.videoWidth || 854))
    const videoHeight = Math.max(240, Number(root.dataset.videoHeight || 480))
    const videoFps = Math.max(12, Number(root.dataset.videoFps || 30))
    const segmentDurationSeconds = Math.max(2, Math.round(Number(root.dataset.segmentDurationMs || 10000) / 1000))
    const maxDurationSeconds = Math.max(300, Number(root.dataset.maxDurationSeconds || 1800))
    const creatorEndedUrl = liveId > 0 ? `/creator/live?status=ended&live=${liveId}` : '/creator/live?status=ended'

    const el = {
        error: document.querySelector('[data-live-error]'),
        waitBox: document.querySelector('[data-live-waiting]'),
        waitText: document.querySelector('[data-live-waiting-text]'),
        statusText: document.querySelector('[data-live-status-text]'),
        streamState: document.querySelector('[data-live-stream-state]'),
        endedBanner: document.querySelector('[data-live-ended-banner]'),
        priorityAlert: document.querySelector('[data-live-priority-alert]'),
        priorityAlertText: document.querySelector('[data-live-priority-alert-text]'),
        viewerCounts: Array.from(document.querySelectorAll('[data-live-viewer-count]')),
        startButton: document.querySelector('[data-live-start]'),
        stopButton: document.querySelector('[data-live-stop]'),
        previewVideo: document.querySelector('[data-live-local-video]'),
        remoteVideo: document.querySelector('[data-live-remote-video]'),
        playbackButton: document.querySelector('[data-live-playback]'),
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
        joining: null,
        pollTimer: null,
        heartbeatTimer: null,
        pollIntervalMs: 1500,
        heartbeatIntervalMs: 10000,
        currentUrl: '',
        currentType: '',
        hls: null,
        broadcasting: false,
        startedAt: '',
        elapsedTimer: null,
        lastPriorityAlertId: 0,
        priorityAlertTimer: null,
        audioContext: null,
    }

    const video = () => mode === 'creator' ? el.previewVideo : el.remoteVideo
    const esc = (v) => String(v ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;')
    const setText = (node, value) => { if (node) node.textContent = String(value ?? '') }
    const setCount = (value) => el.viewerCounts.forEach((node) => { node.textContent = String(Math.max(0, Number(value || 0))) })
    const statusLabel = (status) => status === 'live' ? 'Ao vivo' : (status === 'ended' ? 'Encerrada' : 'Aguardando')
    const formDate = (value) => {
        if (!value) return ''
        const d = new Date(String(value).replace(' ', 'T'))
        if (Number.isNaN(d.getTime())) return String(value)
        return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()} ${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`
    }
    const formElapsed = (seconds) => {
        const value = Math.max(0, Number(seconds || 0))
        const h = Math.floor(value / 3600)
        const m = Math.floor((value % 3600) / 60)
        const s = value % 60
        return h > 0 ? `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}` : `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
    }

    const showError = (message = '') => {
        if (!el.error) return
        if (!message) {
            el.error.classList.add('hidden')
            el.error.textContent = ''
            return
        }
        el.error.classList.remove('hidden')
        el.error.textContent = String(message)
    }

    const setWaiting = (message = '') => {
        if (el.waitBox) el.waitBox.classList.remove('hidden')
        setText(el.waitText, message)
    }

    const hideWaiting = () => {
        if (el.waitBox) el.waitBox.classList.add('hidden')
    }

    const postForm = async (url, payload) => {
        const body = new URLSearchParams()
        Object.entries(payload).forEach(([key, value]) => {
            if (value !== undefined && value !== null) body.append(key, String(value))
        })
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: body.toString(),
        })
        const text = await response.text()
        try { return text ? JSON.parse(text) : {} } catch { return { ok: false, message: text || 'Resposta invalida do servidor.' } }
    }

    const getJson = async (url) => {
        const response = await fetch(url, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            cache: 'no-store',
        })
        const text = await response.text()
        try { return text ? JSON.parse(text) : {} } catch { return { ok: false, message: text || 'Resposta invalida do servidor.' } }
    }

    const hidePlaybackButton = () => { if (el.playbackButton) el.playbackButton.classList.add('hidden') }
    const showPlaybackButton = () => { if (el.playbackButton) el.playbackButton.classList.remove('hidden') }

    const stopAlertAudio = () => {
        if (!state.audioContext) return
        try { state.audioContext.close() } catch {}
        state.audioContext = null
    }

    const beepAlert = () => {
        const Ctx = window.AudioContext || window.webkitAudioContext
        if (!Ctx) return
        const ctx = new Ctx()
        const osc = ctx.createOscillator()
        const gain = ctx.createGain()
        osc.type = 'sine'
        osc.frequency.value = 660
        gain.gain.setValueAtTime(0.0001, ctx.currentTime)
        gain.gain.exponentialRampToValueAtTime(0.08, ctx.currentTime + 0.02)
        gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.45)
        osc.connect(gain)
        gain.connect(ctx.destination)
        osc.start()
        osc.stop(ctx.currentTime + 0.48)
        state.audioContext = ctx
        osc.addEventListener('ended', () => stopAlertAudio(), { once: true })
    }

    const destroyPlayer = () => {
        if (state.hls) {
            try { state.hls.destroy() } catch {}
            state.hls = null
        }
        const node = video()
        if (!node) {
            state.currentUrl = ''
            state.currentType = ''
            return
        }
        try { node.pause() } catch {}
        try {
            node.removeAttribute('src')
            node.srcObject = null
            node.load()
        } catch {}
        state.currentUrl = ''
        state.currentType = ''
        hidePlaybackButton()
    }

    const tryPlay = async (node, allowButton = true) => {
        if (!node) return
        try {
            await node.play()
            hidePlaybackButton()
        } catch {
            if (allowButton && mode === 'viewer') showPlaybackButton()
        }
    }

    const attachMedia = async (url, type, muted = false) => {
        const node = video()
        if (!node || !url) return
        if (state.currentUrl === url && state.currentType === type) return

        destroyPlayer()
        state.currentUrl = url
        state.currentType = type
        node.muted = !!muted
        node.controls = true
        node.playsInline = true
        node.preload = 'auto'

        const useHlsJs = type === 'hls' && typeof window.Hls !== 'undefined' && window.Hls.isSupported()
        if (useHlsJs) {
            const hls = new window.Hls({ enableWorker: true, lowLatencyMode: false, backBufferLength: 90 })
            hls.loadSource(url)
            hls.attachMedia(node)
            hls.on(window.Hls.Events.MANIFEST_PARSED, () => { tryPlay(node) })
            hls.on(window.Hls.Events.ERROR, (_event, data) => {
                if (data && data.fatal) showError('Nao foi possivel carregar a transmissao agora.')
            })
            state.hls = hls
            return
        }

        node.src = url
        node.addEventListener('loadedmetadata', () => { tryPlay(node) }, { once: true })
        tryPlay(node)
    }

    const renderChat = (messages) => {
        if (!el.chatStream) return
        const items = Array.isArray(messages) ? messages : []
        const variant = el.chatStream.dataset.liveChatVariant || mode

        if (items.length === 0) {
            el.chatStream.innerHTML = ''
            if (el.chatEmpty) el.chatEmpty.classList.remove('hidden')
            return
        }

        el.chatStream.innerHTML = items.map((message) => {
            const sender = esc(message?.sender?.name || 'Visitante')
            const body = esc(message?.body || '')
            const theme = message?.highlight_theme || {}
            const isHighlighted = Boolean(message?.is_highlighted)
            const label = esc(message?.highlight_label || 'Destaque')
            const bg = esc(theme.background || '#fff6cf')
            const border = esc(theme.border || '#fde68a')
            const labelBg = esc(theme.label_background || '#f59e0b')
            const labelColor = esc(theme.label_text || '#ffffff')

            if (variant === 'creator') {
                return `<div class="rounded-2xl border p-4 text-sm" style="${isHighlighted ? `background:${bg};border-color:${border};` : 'background:#f5f3f5;border-color:transparent;'}"><div class="flex items-center justify-between gap-3"><span class="block text-[10px] font-bold uppercase tracking-widest text-[#D81B60]">${sender}</span>${isHighlighted ? `<span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em]" style="background:${labelBg};color:${labelColor}">${label}</span>` : ''}</div><p class="mt-2">${body}</p></div>`
            }

            return `<div class="flex flex-col gap-1"><div class="flex items-center justify-between gap-3"><span class="text-xs font-bold tracking-wide text-[#ab1155]">${sender}</span>${isHighlighted ? `<span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em]" style="background:${labelBg};color:${labelColor}">${label}</span>` : ''}</div><p class="rounded-2xl rounded-tl-none border bg-white p-3 text-sm text-slate-600 shadow-sm" style="${isHighlighted ? `background:${bg};border-color:${border};` : 'border-color:transparent;'}">${body}</p></div>`
        }).join('')

        el.chatStream.scrollTop = el.chatStream.scrollHeight
        if (el.chatEmpty) el.chatEmpty.classList.add('hidden')
    }

    const renderTips = (tips) => {
        if (!el.tipsStream) return
        const items = Array.isArray(tips) ? tips : []
        const variant = el.tipsStream.dataset.liveTipsVariant || mode

        if (items.length === 0) {
            el.tipsStream.innerHTML = ''
            if (el.tipsEmpty) el.tipsEmpty.classList.remove('hidden')
            return
        }

        el.tipsStream.innerHTML = items.map((tip) => {
            const sender = esc(tip?.sender?.name || 'Fan')
            const amount = Number(tip?.amount || 0)
            return variant === 'creator'
                ? `<div class="flex items-center justify-between rounded-2xl bg-[#f5f3f5] px-4 py-3 text-sm"><span class="font-bold text-slate-700">${sender}</span><span class="font-black text-[#D81B60] inline-flex items-center gap-1 whitespace-nowrap">${amount}<img alt="LuaCoin" class="h-4 w-4 shrink-0" src="/assets/img/luacoin.png"></span></div>`
                : `<div class="flex items-center justify-between rounded-full bg-white px-4 py-2 text-xs"><span class="font-bold text-slate-800">${sender}</span><span class="font-black text-[#ab1155] inline-flex items-center gap-1 whitespace-nowrap">${amount}<img alt="LuaCoin" class="h-3.5 w-3.5 shrink-0" src="/assets/img/luacoin.png"></span></div>`
        }).join('')

        if (el.tipsEmpty) el.tipsEmpty.classList.add('hidden')
    }

    const renderSupporters = (supporters) => {
        if (!el.supportersStream) return
        const items = Array.isArray(supporters) ? supporters : []
        const variant = el.supportersStream.dataset.liveSupportersVariant || mode

        if (items.length === 0) {
            el.supportersStream.innerHTML = ''
            if (el.supportersEmpty) el.supportersEmpty.classList.remove('hidden')
            return
        }

        el.supportersStream.innerHTML = items.map((supporter) => {
            const name = esc(supporter?.user?.name || 'Fan')
            const amount = Number(supporter?.amount || 0)
            const initials = esc((String(name).match(/\b\w/g) || []).slice(0, 2).join('').toUpperCase() || 'FN')
            return variant === 'creator'
                ? `<div class="rounded-2xl bg-[#f5f3f5] p-4 text-center"><div class="signature-glow mx-auto flex h-12 w-12 items-center justify-center rounded-full text-sm font-bold text-white">${initials}</div><p class="mt-3 text-sm font-bold text-slate-800">${name}</p><p class="mt-1 text-xs font-semibold text-[#D81B60] inline-flex items-center gap-1 whitespace-nowrap">${amount}<img alt="LuaCoin" class="h-4 w-4 shrink-0" src="/assets/img/luacoin.png"></p></div>`
                : `<div class="flex flex-col items-center"><div class="signature-glow flex h-12 w-12 items-center justify-center rounded-full text-sm font-bold text-white">${initials}</div><span class="mt-2 text-[10px] font-bold text-[#ab1155]">${name}</span><span class="text-[10px] text-slate-500 inline-flex items-center gap-1 whitespace-nowrap">${amount}<img alt="LuaCoin" class="h-3 w-3 shrink-0" src="/assets/img/luacoin.png"></span></div>`
        }).join('')

        if (el.supportersEmpty) el.supportersEmpty.classList.add('hidden')
    }

    const updateElapsed = () => {
        if (!state.startedAt) {
            if (el.liveElapsed) el.liveElapsed.textContent = '00:00'
            return
        }

        if (el.liveStartedAt) el.liveStartedAt.textContent = formDate(state.startedAt)
        if (!el.liveElapsed) return

        const started = new Date(String(state.startedAt).replace(' ', 'T'))
        if (Number.isNaN(started.getTime())) {
            el.liveElapsed.textContent = '00:00'
            return
        }

        const elapsedSeconds = Math.max(0, Math.floor((Date.now() - started.getTime()) / 1000))
        el.liveElapsed.textContent = formElapsed(elapsedSeconds)

        if (mode === 'creator' && state.broadcasting && elapsedSeconds >= maxDurationSeconds) {
            stopCreatorBroadcast('limit').catch(() => {})
        }
    }

    const startElapsed = () => {
        if (state.elapsedTimer) window.clearInterval(state.elapsedTimer)
        updateElapsed()
        state.elapsedTimer = window.setInterval(updateElapsed, 1000)
    }

    const stopElapsed = () => {
        if (!state.elapsedTimer) return
        window.clearInterval(state.elapsedTimer)
        state.elapsedTimer = null
    }

    const showPriorityAlert = (alert) => {
        if (!el.priorityAlert || !el.priorityAlertText || !alert || !alert.id) return
        if (Number(alert.id) === state.lastPriorityAlertId) return
        state.lastPriorityAlertId = Number(alert.id)
        el.priorityAlertText.textContent = String(alert.alert_text || alert.body || '')
        el.priorityAlert.classList.remove('hidden')
        beepAlert()
        if (state.priorityAlertTimer) window.clearTimeout(state.priorityAlertTimer)
        state.priorityAlertTimer = window.setTimeout(() => { el.priorityAlert.classList.add('hidden') }, 8000)
    }

    const applyStatus = (stream = {}, live = {}) => {
        const status = String(stream.status || live.status || 'idle')
        state.broadcasting = status === 'live'
        state.startedAt = String(stream.started_at || live.started_at || '')
        setCount(stream.viewer_count || live.viewer_count || 0)
        setText(el.statusText, statusLabel(status))
        setText(el.streamState, statusLabel(status))
        if (el.endedBanner) el.endedBanner.classList.toggle('hidden', status !== 'ended')
        if (el.startButton) el.startButton.classList.toggle('hidden', status === 'live')
        if (el.stopButton) el.stopButton.classList.toggle('hidden', status !== 'live')
        updateElapsed()

        const nextHlsUrl = String(stream.hls_url || hlsUrl || '')
        const streamReady = Boolean(stream.ready)

        if (mode === 'creator') {
            if (status === 'live' && streamReady && nextHlsUrl) {
                hideWaiting()
                attachMedia(nextHlsUrl, 'hls', true).catch(() => showError('Nao foi possivel abrir o preview do MediaMTX.'))
            } else if (status === 'ended') {
                destroyPlayer()
                setWaiting('Live encerrada. O replay automatico sera anexado aos seus conteudos.')
            } else {
                destroyPlayer()
                setWaiting('Assim que o MediaMTX detectar o sinal do OBS, o preview aparece aqui.')
            }
            return
        }

        if (!canWatch) {
            destroyPlayer()
            setWaiting(accessMessage || 'Entre para assistir esta live.')
            return
        }

        if (status === 'live' && streamReady && nextHlsUrl) {
            hideWaiting()
            attachMedia(nextHlsUrl, 'hls', false).catch(() => showError('Nao foi possivel carregar a transmissao agora.'))
            return
        }

        if (status === 'ended' && replayEnabled && replayUrl) {
            hideWaiting()
            attachMedia(replayUrl, 'replay', false).catch(() => showError('Nao foi possivel carregar o replay.'))
            return
        }

        destroyPlayer()
        setWaiting(accessMessage || 'Aguardando o criador iniciar a live.')
    }

    const applyPayload = (payload) => {
        if (!payload || payload.ok === false) {
            if (payload && payload.message) showError(payload.message)
            return
        }
        showError('')
        renderChat(payload.chat_messages || [])
        renderTips(payload.recent_tips || [])
        renderSupporters(payload.top_supporters || [])
        showPriorityAlert(payload.priority_alert || null)
        applyStatus(payload.stream || {}, payload.live || {})
    }

    const poll = async () => {
        if (!state.joined || !state.peerId || liveId <= 0) return
        applyPayload(await getJson(`${pollUrl}?live_id=${encodeURIComponent(liveId)}&peer_id=${encodeURIComponent(state.peerId)}&after_id=0`))
    }

    const heartbeat = async () => {
        if (!state.joined || !state.peerId || liveId <= 0) return
        const payload = await postForm(heartbeatUrl, { _token: csrf, live_id: liveId, peer_id: state.peerId })
        if (payload && payload.ok) applyStatus(payload.stream || {}, {})
    }

    const startLoops = () => {
        if (state.pollTimer) window.clearInterval(state.pollTimer)
        if (state.heartbeatTimer) window.clearInterval(state.heartbeatTimer)
        state.pollTimer = window.setInterval(() => { poll().catch(() => {}) }, state.pollIntervalMs)
        state.heartbeatTimer = window.setInterval(() => { heartbeat().catch(() => {}) }, state.heartbeatIntervalMs)
    }

    const ensureJoined = async () => {
        if (state.joined && state.peerId) return true
        if (state.joining) return state.joining
        state.joining = (async () => {
            if (liveId <= 0) return false
            if (mode === 'viewer' && !canWatch) return false
            const payload = await postForm(joinUrl, { _token: csrf, live_id: liveId, role: mode === 'creator' ? 'creator' : 'viewer' })
            if (!payload.ok) {
                showError(payload.message || 'Nao foi possivel entrar na live.')
                return false
            }
            state.joined = true
            state.peerId = String(payload.peer_id || '')
            state.pollIntervalMs = Math.max(800, Number(payload.poll_interval_ms || state.pollIntervalMs))
            state.heartbeatIntervalMs = Math.max(5000, Number(payload.heartbeat_interval_ms || state.heartbeatIntervalMs))
            applyPayload(payload)
            startLoops()
            return true
        })()
        try { return await state.joining } finally { state.joining = null }
    }

    const stopCreatorBroadcast = async (reason = 'manual') => {
        if (!state.peerId || liveId <= 0) return
        const payload = await postForm(stopUrl, { _token: csrf, live_id: liveId, peer_id: state.peerId })
        if (!payload.ok) {
            showError(payload.message || 'Nao foi possivel encerrar a live.')
            return
        }
        applyStatus(payload.stream || {}, payload.live || {})
        stopElapsed()
        destroyPlayer()
        setWaiting('Live encerrada. O replay automatico sera processado em seguida.')
        const durationLabel = payload.duration_label || formElapsed(payload.duration_seconds || 0)
        window.alert(`Live "${payload.title || 'Live'}" encerrada com sucesso, duracao ${durationLabel}. Para controlar o replay va em Meus Conteudos.`)
        if (reason === 'limit') showError('A live foi encerrada automaticamente porque atingiu o limite maximo configurado.')
        window.setTimeout(() => { window.location.assign(creatorEndedUrl) }, 220)
    }

    const startCreatorBroadcast = async () => {
        if (!canBroadcast || liveId <= 0) return
        const joined = await ensureJoined()
        if (!joined) return
        const payload = await postForm(startUrl, {
            _token: csrf,
            live_id: liveId,
            peer_id: state.peerId,
            segment_duration_seconds: segmentDurationSeconds,
            max_bitrate_kbps: bitrateKbps,
            video_width: videoWidth,
            video_height: videoHeight,
            video_fps: videoFps,
        })
        if (!payload.ok) {
            showError(payload.message || 'Nao foi possivel iniciar a live.')
            return
        }
        applyStatus(payload.stream || {}, payload.live || {})
        setWaiting('Live aberta. Agora envie o sinal pelo OBS para o MediaMTX.')
        startElapsed()
        poll().catch(() => {})
    }

    const sendChat = async (event) => {
        event.preventDefault()
        if (!el.chatForm) return
        const formData = new FormData(el.chatForm)
        const body = String(formData.get('body') || '').trim()
        if (!body) return
        const payload = await postForm(el.chatForm.action, Object.fromEntries(formData.entries()))
        if (!payload.ok) {
            showError(payload.message || 'Nao foi possivel enviar a mensagem.')
            return
        }
        const textarea = el.chatForm.querySelector('textarea')
        if (textarea) textarea.value = ''
        showError('')
        poll().catch(() => {})
    }

    const sendTip = async (event) => {
        event.preventDefault()
        if (!el.tipForm) return
        const payload = await postForm(el.tipForm.action, Object.fromEntries(new FormData(el.tipForm).entries()))
        if (!payload.ok) {
            showError(payload.message || 'Nao foi possivel enviar a gorjeta.')
            return
        }
        showError('')
        poll().catch(() => {})
    }

    if (el.startButton) el.startButton.addEventListener('click', () => { startCreatorBroadcast().catch((error) => showError(error instanceof Error ? error.message : 'Nao foi possivel iniciar a live.')) })
    if (el.stopButton) el.stopButton.addEventListener('click', () => { stopCreatorBroadcast('manual').catch((error) => showError(error instanceof Error ? error.message : 'Nao foi possivel encerrar a live.')) })
    if (el.chatForm) el.chatForm.addEventListener('submit', sendChat)
    if (el.tipForm) el.tipForm.addEventListener('submit', sendTip)
    if (el.playbackButton) el.playbackButton.addEventListener('click', () => { const node = video(); if (node) tryPlay(node, false) })

    window.addEventListener('beforeunload', () => {
        if (!state.peerId) return
        try {
            const body = new URLSearchParams({ _token: csrf, live_id: String(liveId), peer_id: state.peerId })
            navigator.sendBeacon(leaveUrl, body)
        } catch {}
    })

    const boot = async () => {
        if (mode === 'viewer' && !canWatch) {
            setWaiting(accessMessage || 'Entre para assistir esta live.')
            return
        }
        const joined = await ensureJoined()
        if (!joined && mode === 'viewer') return
        startElapsed()
        poll().catch(() => {})
    }

    boot().catch((error) => showError(error instanceof Error ? error.message : 'Nao foi possivel carregar a live.'))
})()
