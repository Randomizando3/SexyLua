(() => {
    const root = document.querySelector('[data-live-rtc-mode]')
    if (!root) return

    const mode = root.dataset.liveRtcMode || 'viewer'
    const liveId = Number(root.dataset.liveId || 0)
    const csrf = root.dataset.csrf || ''
    const canBroadcast = root.dataset.canBroadcast === '1'
    const joinUrl = root.dataset.joinUrl || '/live/rtc/join'
    const stateUrl = root.dataset.stateUrl || '/live/state'
    const startUrl = root.dataset.startUrl || '/live/rtc/start'
    const stopUrl = root.dataset.stopUrl || '/live/rtc/stop'
    const pollUrl = root.dataset.pollUrl || '/live/rtc/poll'
    const heartbeatUrl = root.dataset.heartbeatUrl || '/live/rtc/heartbeat'
    const leaveUrl = root.dataset.leaveUrl || '/live/rtc/leave'
    const hlsUrl = root.dataset.hlsUrl || ''
    const bitrateKbps = Math.max(300, Number(root.dataset.maxBitrateKbps || 800))
    const videoWidth = Math.max(320, Number(root.dataset.videoWidth || 854))
    const videoHeight = Math.max(240, Number(root.dataset.videoHeight || 480))
    const videoFps = Math.max(12, Number(root.dataset.videoFps || 30))
    const segmentDurationSeconds = Math.max(2, Math.round(Number(root.dataset.segmentDurationMs || 10000) / 1000))
    const maxDurationSeconds = Math.max(300, Number(root.dataset.maxDurationSeconds || 1800))
    const priorityAlertDurationMs = Math.max(2000, Number(root.dataset.livePriorityAlertDurationMs || 8000))
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
        inlineAlert: document.querySelector('[data-live-inline-alert]'),
        inlineAlertText: document.querySelector('[data-live-inline-alert-text]'),
        inlineAlertKicker: document.querySelector('[data-live-inline-alert-kicker]'),
        darkroomBanner: document.querySelector('[data-live-darkroom-banner]'),
        darkroomBannerText: document.querySelector('[data-live-darkroom-banner-text]'),
        darkroomBannerKicker: document.querySelector('[data-live-darkroom-banner-kicker]'),
        viewerCounts: Array.from(document.querySelectorAll('[data-live-viewer-count]')),
        startButton: document.querySelector('[data-live-start]'),
        stopButton: document.querySelector('[data-live-stop]'),
        previewEmbed: document.querySelector('[data-live-local-embed]'),
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
        darkroomForm: document.querySelector('[data-live-darkroom-form]'),
        darkroomButton: document.querySelector('[data-live-darkroom-button]'),
        darkroomStatus: document.querySelector('[data-live-darkroom-status]'),
        tipAmountLabel: document.querySelector('[data-live-tip-amount-label]'),
        tipTotal: document.querySelector('[data-live-tip-total]'),
        liveStartedAt: document.querySelector('[data-live-started-at]'),
        liveElapsed: document.querySelector('[data-live-elapsed]'),
        walletModal: document.querySelector('[data-live-wallet-modal]'),
        walletModalText: document.querySelector('[data-live-wallet-modal-text]'),
        walletModalGo: document.querySelector('[data-live-wallet-modal-go]'),
        walletModalStay: document.querySelector('[data-live-wallet-modal-stay]'),
        walletModalClose: document.querySelector('[data-live-wallet-modal-close]'),
        studioDarkroomPanel: document.querySelector('[data-live-studio-darkroom-panel]'),
        studioDarkroomBody: document.querySelector('[data-live-studio-darkroom-body]'),
        studioDarkroomCancelForm: document.querySelector('[data-live-studio-darkroom-cancel-form]'),
    }

    const state = {
        peerId: '',
        joined: false,
        joining: null,
        rejoinTimer: null,
        pollTimer: null,
        heartbeatTimer: null,
        stateTimer: null,
        pollIntervalMs: 1500,
        heartbeatIntervalMs: 10000,
        stateIntervalMs: 1000,
        currentUrl: '',
        currentType: '',
        hls: null,
        broadcasting: false,
        liveStatus: root.dataset.initialStatus || '',
        startedAt: '',
        elapsedTimer: null,
        lastPriorityAlertId: 0,
        priorityAlertTimer: null,
        audioContext: null,
        previewEmbedUrl: '',
        canWatch: root.dataset.canWatch !== '0',
        canChat: false,
        canTip: false,
        accessMessage: root.dataset.accessMessage || '',
        darkroomActive: root.dataset.darkroomActive === '1',
        darkroomIsOwner: root.dataset.darkroomIsOwner === '1',
        requiresDarkroomWait: root.dataset.requiresDarkroomWait === '1',
        darkroomEndsAt: root.dataset.darkroomEndsAt || '',
        darkroomPriceTokens: 0,
        darkroomDurationMinutes: 0,
        activeDarkroom: null,
        darkroomCandidates: [],
        inlineAlertTimer: null,
        darkroomReloadTimer: null,
        darkroomBannerTimer: null,
        darkroomBannerTimerFor: '',
        darkroomOwnerBannerDismissedFor: '',
        walletModalUrl: '',
    }

    const video = () => mode === 'creator' ? el.previewVideo : el.remoteVideo
    const esc = (v) => String(v ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;')
    const setText = (node, value) => { if (node) node.textContent = String(value ?? '') }
    const luacoinHtml = (value, size = 'h-4 w-4') => `<span class="inline-flex items-center gap-1.5 whitespace-nowrap"><span>${Math.max(0, Number(value || 0))}</span><img alt="LuaCoin" class="${size} shrink-0" src="/assets/img/luacoin.png"><span class="sr-only">LuaCoins</span></span>`
    const setCount = (value) => el.viewerCounts.forEach((node) => { node.textContent = String(Math.max(0, Number(value || 0))) })
    const statusLabel = (status) => {
        if (status === 'live') return 'Ao vivo'
        if (status === 'ended') return 'Encerrada'
        if (status === 'scheduled') return 'Agendada'
        return 'Aguardando'
    }
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
    const studioDarkroomDurationLabel = (minutes) => `${Math.max(0, Number(minutes || 0))} min`

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

    const showInlineAlert = (message = '', tone = 'success', kicker = 'Atualizacao da sala', durationMs = 6500) => {
        if (!el.inlineAlert || !el.inlineAlertText || !message) return
        const toneClass = tone === 'info' ? 'bg-sky-600/90' : (tone === 'error' ? 'bg-rose-500/90' : 'bg-emerald-500/90')
        el.inlineAlert.classList.remove('hidden', 'bg-emerald-500/90', 'bg-sky-600/90', 'bg-rose-500/90')
        el.inlineAlert.classList.add(toneClass)
        el.inlineAlertText.textContent = String(message)
        if (el.inlineAlertKicker) el.inlineAlertKicker.textContent = String(kicker)
        if (state.inlineAlertTimer) window.clearTimeout(state.inlineAlertTimer)
        state.inlineAlertTimer = window.setTimeout(() => {
            if (el.inlineAlert) el.inlineAlert.classList.add('hidden')
        }, Math.max(1200, Number(durationMs || 6500)))
    }

    const closeWalletModal = () => {
        if (!el.walletModal) return
        el.walletModal.classList.add('hidden')
        el.walletModal.classList.remove('flex')
        state.walletModalUrl = ''
    }

    const openWalletModal = (message = '', walletUrl = '') => {
        if (!el.walletModal) {
            if (walletUrl) window.location.assign(String(walletUrl))
            return
        }
        state.walletModalUrl = String(walletUrl || '')
        if (el.walletModalText) {
            el.walletModalText.textContent = String(message || 'Voce nao tem saldo suficiente para concluir essa acao.')
        }
        el.walletModal.classList.remove('hidden')
        el.walletModal.classList.add('flex')
    }

    const payloadNeedsRejoin = (payload) => {
        const message = String(payload?.message || '')
        return payload?.ok === false && /sess[aã]o da live expirada|entre novamente/i.test(message)
    }

    const scheduleRejoin = () => {
        if (state.rejoinTimer) return
        state.joined = false
        state.peerId = ''
        state.rejoinTimer = window.setTimeout(() => {
            state.rejoinTimer = null
            ensureJoined().catch(() => {})
        }, 250)
    }

    const setWaiting = (message = '') => {
        if (el.waitBox) el.waitBox.classList.remove('hidden')
        setText(el.waitText, message)
    }

    const hideWaiting = () => {
        if (el.waitBox) el.waitBox.classList.add('hidden')
    }

    const darkroomBannerSignature = () => `${state.darkroomActive ? '1' : '0'}|${state.darkroomIsOwner ? '1' : '0'}|${String(state.darkroomEndsAt || '')}`

    const syncDarkroomBanner = () => {
        if (!el.darkroomBanner || !el.darkroomBannerText) return
        if (!state.darkroomActive) {
            if (state.darkroomBannerTimer) {
                window.clearTimeout(state.darkroomBannerTimer)
                state.darkroomBannerTimer = null
            }
            state.darkroomBannerTimerFor = ''
            state.darkroomOwnerBannerDismissedFor = ''
            el.darkroomBanner.classList.add('hidden')
            return
        }
        const signature = darkroomBannerSignature()
        if (state.darkroomIsOwner && state.darkroomOwnerBannerDismissedFor === signature) {
            el.darkroomBanner.classList.add('hidden')
            return
        }
        el.darkroomBanner.classList.remove('hidden')
        el.darkroomBannerText.textContent = String(state.accessMessage || (state.darkroomIsOwner ? 'Seu darkroom esta ativo agora.' : 'A live entrou em darkroom temporariamente.'))
        if (el.darkroomBannerKicker) {
            el.darkroomBannerKicker.textContent = state.darkroomIsOwner ? 'Darkroom ativo para voce' : 'Darkroom ativo'
        }
        if (state.darkroomIsOwner) {
            if (!state.darkroomBannerTimer || state.darkroomBannerTimerFor !== signature) {
                if (state.darkroomBannerTimer) {
                    window.clearTimeout(state.darkroomBannerTimer)
                }
                state.darkroomBannerTimerFor = signature
                state.darkroomBannerTimer = window.setTimeout(() => {
                    state.darkroomOwnerBannerDismissedFor = signature
                    state.darkroomBannerTimer = null
                    state.darkroomBannerTimerFor = ''
                    if (el.darkroomBanner) el.darkroomBanner.classList.add('hidden')
                }, 5000)
            }
        } else if (state.darkroomBannerTimer) {
            window.clearTimeout(state.darkroomBannerTimer)
            state.darkroomBannerTimer = null
            state.darkroomBannerTimerFor = ''
        }
    }

    const syncDarkroomUi = () => {
        if (el.darkroomButton) {
            const locked = state.darkroomActive
            el.darkroomButton.disabled = locked
            el.darkroomButton.classList.toggle('opacity-50', locked)
            el.darkroomButton.classList.toggle('cursor-not-allowed', locked)
            el.darkroomButton.textContent = locked ? 'Darkroom em andamento' : 'Ativar darkroom'
        }
        if (el.darkroomStatus) {
            if (state.darkroomActive) {
                el.darkroomStatus.textContent = state.darkroomIsOwner ? 'Darkroom ativo para voce' : 'Darkroom indisponivel no momento'
            } else {
                el.darkroomStatus.textContent = 'Disponivel para espectadores'
            }
        }
        syncDarkroomBanner()
    }

    const scheduleDarkroomReload = () => {
        if (mode !== 'viewer' || state.darkroomReloadTimer) return
        state.darkroomReloadTimer = window.setTimeout(() => {
            try {
                window.location.reload()
            } catch {}
        }, 220)
    }

    const isDarkroomBlockedViewer = () => mode === 'viewer' && state.darkroomActive && !state.darkroomIsOwner

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
        if (el.previewEmbed) {
            try { el.previewEmbed.src = 'about:blank' } catch {}
            el.previewEmbed.classList.add('hidden')
            state.previewEmbedUrl = ''
        }
        if (el.previewVideo && mode === 'creator') {
            el.previewVideo.classList.remove('hidden')
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

    const previewEmbedUrl = (playlistUrl) => {
        const base = String(playlistUrl || '').replace(/\/index\.m3u8(?:\?.*)?$/i, '/')
        if (!base) return ''
        return `${base}${base.includes('?') ? '&' : '?'}controls=true&muted=true&autoplay=true&playsInline=true`
    }

    const attachCreatorPreview = (playlistUrl) => {
        if (!el.previewEmbed) return false
        const embedUrl = previewEmbedUrl(playlistUrl)
        if (!embedUrl) return false
        if (state.previewEmbedUrl !== embedUrl) {
            try { el.previewEmbed.src = embedUrl } catch {}
            state.previewEmbedUrl = embedUrl
        }
        el.previewEmbed.classList.remove('hidden')
        if (el.previewVideo) el.previewVideo.classList.add('hidden')
        return true
    }

    const scheduleMediaRetry = (message = '') => {
        destroyPlayer()
        if (message) {
            setWaiting(message)
        }
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
                if (data && data.fatal) {
                    showError('Preparando preview da transmissao...')
                    scheduleMediaRetry('Sinal detectado. Preparando preview...')
                }
            })
            state.hls = hls
            return
        }

        node.src = url
        node.addEventListener('error', () => {
            showError('Preparando preview da transmissao...')
            scheduleMediaRetry('Sinal detectado. Preparando preview...')
        }, { once: true })
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

    const renderCreatorStudioDarkroom = (payload = {}) => {
        if (mode !== 'creator' || !el.studioDarkroomBody) return

        const active = payload.active_darkroom && typeof payload.active_darkroom === 'object' ? payload.active_darkroom : null
        const candidates = Array.isArray(payload.darkroom_candidates) ? payload.darkroom_candidates : []
        const liveIsLive = state.liveStatus === 'live'
        const durationMinutes = Math.max(0, Number(payload.darkroom_duration_minutes ?? state.darkroomDurationMinutes ?? 0))
        const priceTokens = Math.max(0, Number(payload.darkroom_price_tokens ?? state.darkroomPriceTokens ?? 0))

        state.activeDarkroom = active
        state.darkroomCandidates = candidates
        state.darkroomDurationMinutes = durationMinutes
        state.darkroomPriceTokens = priceTokens

        if (el.studioDarkroomCancelForm) {
            el.studioDarkroomCancelForm.classList.toggle('hidden', !active)
        }

        const activeCard = active ? `
            <div class="mt-5 rounded-3xl bg-white p-4 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="h-14 w-14 shrink-0 overflow-hidden rounded-full bg-[#f5f3f5]">
                        ${active.user_avatar_url ? `<img alt="${esc(active.user_name || 'Assinante')}" class="h-full w-full object-cover" src="${esc(active.user_avatar_url)}">` : `<div class="signature-glow flex h-full w-full items-center justify-center text-sm font-bold text-white">${esc((String(active.user_name || 'Assinante').match(/\b\w/g) || []).slice(0, 2).join('').toUpperCase() || 'AS')}</div>`}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Darkroom em andamento</p>
                        <p class="mt-2 text-base font-bold text-slate-800">${esc(active.user_name || 'Assinante')}</p>
                        <p class="mt-1 text-sm text-slate-500">@${esc(active.user_username || 'sem-username')}</p>
                    </div>
                    <div class="rounded-2xl bg-[#f5f3f5] px-4 py-3 text-right">
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Tempo restante</p>
                        <p class="mt-2 text-sm font-bold text-slate-800">${esc(formElapsed(Number(active.remaining_seconds || 0)))}</p>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-3 text-sm text-slate-500">
                    <span class="rounded-full bg-[#f5f3f5] px-4 py-2 font-semibold">${active.creator_initiated ? 'Iniciada pelo criador' : 'Ativada por pagamento'}</span>
                    <span class="rounded-full bg-[#f5f3f5] px-4 py-2 font-semibold">${Number(active.amount || 0) > 0 ? luacoinHtml(active.amount || 0, 'h-4 w-4') : 'Sem cobranca automatica'}</span>
                    <span class="rounded-full bg-[#f5f3f5] px-4 py-2 font-semibold">${esc(studioDarkroomDurationLabel(active.duration_minutes || 0))}</span>
                </div>
            </div>
        ` : `
            <div class="mt-5 rounded-3xl bg-white p-4 text-sm text-slate-500 shadow-sm">
                Nenhuma darkroom esta ativa agora. Quando quiser abrir uma sala privada manualmente, escolha um usuario abaixo.
            </div>
        `

        let detailsContent = ''
        if (active) {
            detailsContent = `<p class="rounded-2xl bg-[#f8f4f7] px-4 py-3 text-sm text-slate-500">Cancele a darkroom atual antes de iniciar outra.</p>`
        } else if (!liveIsLive) {
            detailsContent = `<p class="rounded-2xl bg-[#f8f4f7] px-4 py-3 text-sm text-slate-500">A darkroom manual fica disponivel assim que a live estiver ao vivo.</p>`
        } else if (candidates.length === 0) {
            detailsContent = `<p class="rounded-2xl bg-[#f8f4f7] px-4 py-3 text-sm text-slate-500">Nenhum assinante ou espectador elegivel apareceu ainda nesta sala.</p>`
        } else {
            const options = candidates.map((candidate, index) => {
                const name = String(candidate?.name || 'Usuario')
                const username = String(candidate?.username || '')
                const badge = String(candidate?.badge || '')
                const label = `${name}${username ? ` (@${username})` : ''}${badge ? ` • ${badge}` : ''}`
                const search = `${name} ${username} ${badge}`.trim().toLowerCase()
                return `<option ${index === 0 ? 'selected' : ''} data-search="${esc(search)}" value="${esc(candidate?.id || 0)}">${esc(label)}</option>`
            }).join('')

            detailsContent = `
                <form action="/creator/live/darkroom/start" class="space-y-4" method="post">
                    <input name="_token" type="hidden" value="${esc(csrf)}">
                    <input name="live_id" type="hidden" value="${esc(liveId)}">
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Pesquisar usuario</span>
                        <input class="rounded-2xl border-none bg-[#f5f3f5] px-4 py-3 text-sm font-semibold text-slate-700" data-darkroom-user-search placeholder="Digite nome ou @usuario" type="search">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Usuario para a darkroom</span>
                        <select class="min-h-[220px] rounded-2xl border-none bg-[#f5f3f5] px-4 py-3 text-sm font-semibold text-slate-700" data-darkroom-user-select name="target_user_id" size="6">
                            ${options}
                        </select>
                    </label>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500">
                        <span class="rounded-full bg-[#f5f3f5] px-4 py-2 font-semibold">${esc(studioDarkroomDurationLabel(durationMinutes))} configurados</span>
                        <span class="rounded-full bg-[#f5f3f5] px-4 py-2 font-semibold">${priceTokens > 0 ? luacoinHtml(priceTokens, 'h-4 w-4') : 'Sem cobranca automatica'}</span>
                    </div>
                    <button class="rounded-full bg-slate-900 px-5 py-3 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Iniciar darkroom agora</button>
                </form>
            `
        }

        el.studioDarkroomBody.innerHTML = `
            ${activeCard}
            <details class="group mt-5 rounded-3xl bg-white p-4 shadow-sm" data-live-studio-darkroom-details ${active ? '' : 'open'}>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 marker:content-none">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Nova darkroom manual</p>
                        <p class="mt-2 text-sm font-semibold text-slate-700">Escolha um espectador atual ou assinante com pesquisa</p>
                    </div>
                    <span class="material-symbols-outlined rounded-full bg-[#f5f3f5] p-2 text-slate-700 transition-transform group-open:rotate-180">expand_more</span>
                </summary>
                <div class="pt-4">
                    ${detailsContent}
                </div>
            </details>
        `

        if (typeof window.sexyluaBindDarkroomSearch === 'function') {
            window.sexyluaBindDarkroomSearch(el.studioDarkroomBody)
        }
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
        state.priorityAlertTimer = window.setTimeout(() => { el.priorityAlert.classList.add('hidden') }, priorityAlertDurationMs)
    }

    const tipAmountField = () => el.tipForm ? el.tipForm.querySelector('[name="amount"]') : null
    const tipMessageField = () => el.tipForm ? el.tipForm.querySelector('[name="message"]') : null
    const tipPresetButtons = () => el.tipForm ? Array.from(el.tipForm.querySelectorAll('[data-live-tip-preset]')) : []

    const applyTipPreset = (value, message = '') => {
        const normalized = Math.max(1, Number(value || 0))
        const amountField = tipAmountField()
        const messageField = tipMessageField()
        if (amountField) amountField.value = String(normalized)
        if (el.tipAmountLabel) el.tipAmountLabel.textContent = String(normalized)
        if (messageField) {
            const nextMessage = String(message || '').trim()
            if (nextMessage !== '') {
                messageField.value = nextMessage
            }
        }

        tipPresetButtons().forEach((button) => {
            const active = Number(button.dataset.liveTipPreset || 0) === normalized
            button.classList.toggle('signature-glow', active)
            button.classList.toggle('text-white', active)
            button.classList.toggle('bg-[#f5f3f5]', !active)
            button.classList.toggle('text-[#ab1155]', !active)
            button.setAttribute('aria-pressed', active ? 'true' : 'false')
        })
    }

    const applyStatus = (stream = {}, live = {}) => {
        const status = String(stream.status || live.status || 'idle')
        state.liveStatus = status
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
            if (streamReady && nextHlsUrl) {
                hideWaiting()
                if (!attachCreatorPreview(nextHlsUrl)) {
                    attachMedia(nextHlsUrl, 'hls', true).catch(() => showError('Nao foi possivel abrir o preview do MediaMTX.'))
                }
                if (status !== 'live') {
                    setText(el.streamState, 'Sinal detectado')
                }
            } else if (status === 'ended') {
                destroyPlayer()
                setWaiting('Live encerrada. O estúdio foi finalizado com sucesso.')
            } else {
                destroyPlayer()
                setWaiting('Assim que o MediaMTX detectar o sinal do OBS, o preview aparece aqui.')
            }
            return
        }

        if (isDarkroomBlockedViewer()) {
            state.canWatch = false
            destroyPlayer()
            setWaiting(state.accessMessage || 'A live entrou em darkroom temporariamente.')
            return
        }

        if (!state.canWatch) {
            destroyPlayer()
            setWaiting(state.accessMessage || 'Entre para assistir esta live.')
            return
        }

        if (status === 'live' && streamReady && nextHlsUrl) {
            hideWaiting()
            attachMedia(nextHlsUrl, 'hls', false).catch(() => showError('Nao foi possivel carregar a transmissao agora.'))
            return
        }

        destroyPlayer()
        setWaiting(status === 'ended' ? 'A live foi encerrada. Obrigado por assistir.' : (state.accessMessage || 'Aguardando o criador iniciar a live.'))
    }

    const applyPayload = (payload) => {
        if (!payload || payload.ok === false) {
            if (payload && payload.message) showError(payload.message)
            if (payloadNeedsRejoin(payload)) scheduleRejoin()
            return
        }
        const wasDarkroomActive = state.darkroomActive
        const wasDarkroomOwner = state.darkroomIsOwner
        const wasCanWatch = state.canWatch
        showError('')
        state.canWatch = payload.can_watch !== undefined ? Boolean(payload.can_watch) : state.canWatch
        state.canChat = payload.can_chat !== undefined ? Boolean(payload.can_chat) : state.canChat
        state.canTip = payload.can_tip !== undefined ? Boolean(payload.can_tip) : state.canTip
        state.accessMessage = String(payload.access_message || state.accessMessage || '')
        state.darkroomActive = payload.darkroom_active !== undefined ? Boolean(payload.darkroom_active) : state.darkroomActive
        state.darkroomIsOwner = payload.darkroom_is_owner !== undefined ? Boolean(payload.darkroom_is_owner) : state.darkroomIsOwner
        state.requiresDarkroomWait = payload.requires_darkroom_wait !== undefined ? Boolean(payload.requires_darkroom_wait) : state.requiresDarkroomWait
        state.darkroomEndsAt = payload.darkroom_ends_at !== undefined ? String(payload.darkroom_ends_at || '') : state.darkroomEndsAt
        state.darkroomPriceTokens = payload.darkroom_price_tokens !== undefined ? Math.max(0, Number(payload.darkroom_price_tokens || 0)) : state.darkroomPriceTokens
        state.darkroomDurationMinutes = payload.darkroom_duration_minutes !== undefined ? Math.max(0, Number(payload.darkroom_duration_minutes || 0)) : state.darkroomDurationMinutes
        state.activeDarkroom = payload.active_darkroom !== undefined ? (payload.active_darkroom || null) : state.activeDarkroom
        state.darkroomCandidates = payload.darkroom_candidates !== undefined ? (Array.isArray(payload.darkroom_candidates) ? payload.darkroom_candidates : []) : state.darkroomCandidates
        if (isDarkroomBlockedViewer()) {
            state.canWatch = false
            state.requiresDarkroomWait = true
        }
        syncDarkroomUi()
        const shouldForceDarkroomRefresh = mode === 'viewer'
            && Boolean(payload.darkroom_active)
            && (
                Boolean(el.darkroomForm)
                || (Boolean(payload.requires_darkroom_wait) && (!el.darkroomBanner || el.darkroomBanner.classList.contains('hidden')))
            )

        if (
            mode === 'viewer'
            && (
                wasDarkroomActive !== state.darkroomActive
                || (state.darkroomActive && wasDarkroomOwner !== state.darkroomIsOwner)
                || (state.darkroomActive && wasCanWatch !== state.canWatch)
            )
        ) {
            scheduleDarkroomReload()
        } else if (shouldForceDarkroomRefresh) {
            scheduleDarkroomReload()
        }
        if (!wasDarkroomActive && state.darkroomActive) {
            if (state.darkroomIsOwner) {
                showInlineAlert(state.accessMessage || 'Darkroom ativado com sucesso.', 'success', 'Darkroom ativado', 5000)
            } else if (state.requiresDarkroomWait) {
                showInlineAlert(state.accessMessage || 'A live entrou em darkroom temporariamente.', 'info', 'Darkroom ativo')
            }
        } else if (wasCanWatch && !state.canWatch && state.requiresDarkroomWait) {
            showInlineAlert(state.accessMessage || 'A live entrou em darkroom temporariamente.', 'info', 'Darkroom ativo')
        }
        renderChat(payload.chat_messages || [])
        renderTips(payload.recent_tips || [])
        renderSupporters(payload.top_supporters || [])
        renderCreatorStudioDarkroom(payload)
        if (el.tipTotal) el.tipTotal.innerHTML = luacoinHtml(payload.tip_total_amount || 0)
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
        if (payload && payload.ok) {
            applyStatus(payload.stream || {}, {})
            return
        }
        if (payloadNeedsRejoin(payload)) scheduleRejoin()
    }

    const startLoops = () => {
        if (state.pollTimer) window.clearInterval(state.pollTimer)
        if (state.heartbeatTimer) window.clearInterval(state.heartbeatTimer)
        state.pollTimer = window.setInterval(() => { poll().catch(() => {}) }, state.pollIntervalMs)
        state.heartbeatTimer = window.setInterval(() => { heartbeat().catch(() => {}) }, state.heartbeatIntervalMs)
    }

    const refreshRoomState = async () => {
        if (liveId <= 0 || !stateUrl) return
        const separator = stateUrl.includes('?') ? '&' : '?'
        applyPayload(await getJson(`${stateUrl}${separator}id=${encodeURIComponent(liveId)}`))
    }

    const startStateLoop = () => {
        if (!stateUrl || liveId <= 0) return
        if (state.stateTimer) window.clearInterval(state.stateTimer)
        state.stateTimer = window.setInterval(() => { refreshRoomState().catch(() => {}) }, state.stateIntervalMs)
    }

    const ensureJoined = async () => {
        if (state.joined && state.peerId) return true
        if (state.joining) return state.joining
        state.joining = (async () => {
            if (liveId <= 0) return false
            if (mode === 'viewer' && !state.canWatch && !state.requiresDarkroomWait) return false
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
        setWaiting('Live encerrada. A sala foi finalizada com sucesso.')
        const durationLabel = payload.duration_label || formElapsed(payload.duration_seconds || 0)
        window.alert(`Live "${payload.title || 'Live'}" encerrada com sucesso, duracao ${durationLabel}.`)
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
        const bodyField = el.chatForm.querySelector('[name="body"]')
        if (bodyField) bodyField.value = ''
        showError('')
        poll().catch(() => {})
    }

    const sendTip = async (event) => {
        event.preventDefault()
        if (!el.tipForm) return
        const formData = new FormData(el.tipForm)
        const amount = Math.max(1, Number(formData.get('amount') || 0))
        formData.set('amount', String(amount))
        const payload = await postForm(el.tipForm.action, Object.fromEntries(formData.entries()))
        if (!payload.ok) {
            if (payload.wallet_url) {
                openWalletModal(payload.message || 'Voce nao tem LuaCoins suficientes para enviar essa gorjeta.', String(payload.wallet_url))
                return
            }
            showError(payload.message || 'Nao foi possivel enviar a gorjeta.')
            return
        }
        showError('')
        poll().catch(() => {})
    }

    const sendDarkroom = async (event) => {
        event.preventDefault()
        if (!el.darkroomForm) return
        const payload = await postForm(el.darkroomForm.action, Object.fromEntries(new FormData(el.darkroomForm).entries()))
        if (!payload.ok) {
            if (payload.wallet_url) {
                openWalletModal(payload.message || 'Voce nao tem LuaCoins suficientes para ativar o darkroom.', String(payload.wallet_url))
                return
            }
            showError(payload.message || 'Nao foi possivel ativar o darkroom.')
            showInlineAlert(payload.message || 'Nao foi possivel ativar o darkroom.', 'error', 'Darkroom')
            return
        }
        applyPayload(payload)
        poll().catch(() => {})
    }

    if (el.startButton) el.startButton.addEventListener('click', () => { startCreatorBroadcast().catch((error) => showError(error instanceof Error ? error.message : 'Nao foi possivel iniciar a live.')) })
    if (el.stopButton) el.stopButton.addEventListener('click', () => { stopCreatorBroadcast('manual').catch((error) => showError(error instanceof Error ? error.message : 'Nao foi possivel encerrar a live.')) })
    if (el.chatForm) el.chatForm.addEventListener('submit', sendChat)
    if (el.tipForm) el.tipForm.addEventListener('submit', sendTip)
    if (el.darkroomForm) el.darkroomForm.addEventListener('submit', sendDarkroom)
    if (el.walletModalStay) el.walletModalStay.addEventListener('click', closeWalletModal)
    if (el.walletModalClose) el.walletModalClose.addEventListener('click', closeWalletModal)
    if (el.walletModalGo) el.walletModalGo.addEventListener('click', () => {
        const nextUrl = String(state.walletModalUrl || '')
        closeWalletModal()
        if (nextUrl) window.location.assign(nextUrl)
    })
    if (el.walletModal) el.walletModal.addEventListener('click', (event) => {
        if (event.target === el.walletModal) closeWalletModal()
    })
    tipPresetButtons().forEach((button) => {
        button.addEventListener('click', () => {
            applyTipPreset(button.dataset.liveTipPreset || '1', button.dataset.liveTipMessage || '')
        })
    })
    if (el.playbackButton) el.playbackButton.addEventListener('click', () => { const node = video(); if (node) tryPlay(node, false) })

    window.addEventListener('beforeunload', () => {
        if (!state.peerId) return
        try {
            const body = new URLSearchParams({ _token: csrf, live_id: String(liveId), peer_id: state.peerId })
            navigator.sendBeacon(leaveUrl, body)
        } catch {}
    })

    const boot = async () => {
        if (el.tipForm) {
            const amountField = tipAmountField()
            applyTipPreset(amountField ? amountField.value : '1')
        }
        syncDarkroomUi()
        startStateLoop()
        if (mode === 'viewer' && !state.canWatch) {
            setWaiting(state.accessMessage || 'Entre para assistir esta live.')
            if (!state.requiresDarkroomWait) {
                refreshRoomState().catch(() => {})
                return
            }
        }
        const joined = await ensureJoined()
        if (!joined && mode === 'viewer' && !state.requiresDarkroomWait) return
        if (mode === 'viewer') {
            refreshRoomState().catch(() => {})
        }
        startElapsed()
        poll().catch(() => {})
    }

    boot().catch((error) => showError(error instanceof Error ? error.message : 'Nao foi possivel carregar a live.'))
})()
