(() => {
    const config = window.SexyLuaPrototype || null

    if (!config) {
        return
    }

    const data = config.data || {}
    const settings = config.settings || {}
    const page = config.page || ''
    const now = new Date()
    const tokenPrice = Number(settings.token_price_brl || settings.tokenPriceBrl || 0.35)

    const q = (selector, root = document) => {
        if (!root || typeof root.querySelector !== 'function') {
            return null
        }

        try {
            return root.querySelector(selector)
        } catch (error) {
            return null
        }
    }

    const qa = (selector, root = document) => {
        if (!root || typeof root.querySelectorAll !== 'function') {
            return []
        }

        try {
            return Array.from(root.querySelectorAll(selector))
        } catch (error) {
            return []
        }
    }
    const normalize = (value) => String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/\s+/g, ' ')
        .trim()
        .toLowerCase()

    const escapeHtml = (value) => String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')

    const count = (left, right) => Math.min(left.length, right.length)

    const setText = (element, value) => {
        if (!element || value === undefined || value === null) {
            return
        }

        element.textContent = String(value)
    }

    const setValue = (element, value) => {
        if (!element || value === undefined || value === null) {
            return
        }

        element.value = String(value)
    }

    const setLeadingText = (element, value) => {
        if (!element || value === undefined || value === null) {
            return
        }

        const firstText = Array.from(element.childNodes).find((node) => node.nodeType === Node.TEXT_NODE)

        if (firstText) {
            firstText.nodeValue = `${String(value)} `
            return
        }

        element.prepend(document.createTextNode(`${String(value)} `))
    }

    const setInlineLabel = (element, value) => {
        if (!element || value === undefined || value === null) {
            return
        }

        const textNodes = Array.from(element.childNodes).filter((node) => node.nodeType === Node.TEXT_NODE)

        if (textNodes.length > 0) {
            textNodes[textNodes.length - 1].nodeValue = ` ${String(value)}`
            return
        }

        element.append(document.createTextNode(` ${String(value)}`))
    }

    const setRoute = (element, route) => {
        if (!element || !route) {
            return
        }

        element.dataset.prototypeRoute = route
        if (!element.classList.contains('cursor-pointer')) {
            element.classList.add('cursor-pointer')
        }
    }

    const slugify = (value) => normalize(value)
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')

    const compact = (value) => new Intl.NumberFormat('pt-BR', {
        notation: 'compact',
        maximumFractionDigits: 1,
    }).format(Number(value || 0))

    const integer = (value) => new Intl.NumberFormat('pt-BR').format(Number(value || 0))

    const money = (value) => new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(value || 0))

    const shortDate = (value) => {
        if (!value) {
            return '--'
        }

        const date = new Date(String(value).replace(' ', 'T'))

        if (Number.isNaN(date.getTime())) {
            return String(value)
        }

        return date.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: 'short',
        }).replace('.', '')
    }

    const fullDateTime = (value) => {
        if (!value) {
            return '--'
        }

        const date = new Date(String(value).replace(' ', 'T'))

        if (Number.isNaN(date.getTime())) {
            return String(value)
        }

        return date.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: 'short',
            hour: '2-digit',
            minute: '2-digit',
        }).replace(',', ' •')
    }

    const relativeLabel = (value) => {
        if (!value) {
            return '--'
        }

        const date = new Date(String(value).replace(' ', 'T'))

        if (Number.isNaN(date.getTime())) {
            return String(value)
        }

        const diff = now.getTime() - date.getTime()
        const hours = Math.round(diff / 3600000)

        if (hours < 1) {
            return 'Agora'
        }

        if (hours < 24) {
            return `Há ${hours}h`
        }

        return hours < 48 ? 'Ontem' : shortDate(value)
    }

    const daysUntil = (value) => {
        if (!value) {
            return 0
        }

        const date = new Date(String(value).replace(' ', 'T'))

        if (Number.isNaN(date.getTime())) {
            return 0
        }

        return Math.max(0, Math.ceil((date.getTime() - now.getTime()) / 86400000))
    }

    const handleOf = (user) => `@${(user && (user.slug || slugify(user.name || 'sexylua'))) || 'sexylua'}`
    const brlFromTokens = (tokens) => money(Number(tokens || 0) * tokenPrice)

    const phaseLabel = (value) => {
        const text = normalize(value)

        if (text.includes('meia noite')) {
            return 'Meia Noite'
        }

        if (text.includes('nova')) {
            return 'Lua Nova'
        }

        if (text.includes('cheia')) {
            return 'Lua Cheia'
        }

        if (text.includes('mingu')) {
            return 'Minguante'
        }

        if (text.includes('eclipse')) {
            return 'Eclipse'
        }

        if (text.includes('aurora')) {
            return 'Aurora'
        }

        return value || 'Crescente'
    }

    const titleCaseType = (value) => ({
        gallery: 'Galeria',
        video: 'Vídeo',
        audio: 'Áudio',
        article: 'Artigo',
        live_teaser: 'Live',
    })[value] || 'Conteúdo'

    const liveUrl = (id) => `/live?id=${id}`
    const profileUrl = (id) => `/profile?id=${id}`
    const conversationUrl = (id) => `/subscriber/messages?conversation=${id}`

    const attachRouteHandler = () => {
        document.addEventListener('click', (event) => {
            const target = event.target.closest('[data-prototype-route]')

            if (!target) {
                return
            }

            event.preventDefault()
            window.location.href = target.dataset.prototypeRoute
        })
    }

    const renderConversationMessages = (messages) => {
        const currentUserName = normalize(config.currentUserName)

        return messages.map((message) => {
            const senderName = message.sender && message.sender.name ? message.sender.name : 'Sistema'
            const ownMessage = normalize(senderName) === currentUserName

            if (ownMessage) {
                return `
                    <div class="flex flex-col items-end gap-1 self-end max-w-[80%]">
                        <div class="bg-primary text-white p-4 rounded-t-2xl rounded-bl-2xl text-sm shadow-md shadow-primary/20">${escapeHtml(message.body || '')}</div>
                        <span class="text-[10px] text-outline font-bold uppercase mr-2">${escapeHtml(relativeLabel(message.created_at))}</span>
                    </div>
                `
            }

            const initials = senderName.split(' ').map((part) => part[0]).slice(0, 2).join('').toUpperCase()

            return `
                <div class="flex items-end gap-3 max-w-[80%]">
                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary">${escapeHtml(initials)}</div>
                    <div class="bg-surface-container-low p-4 rounded-t-2xl rounded-br-2xl text-on-surface text-sm leading-relaxed">
                        <span class="block text-[10px] font-bold uppercase tracking-widest text-primary mb-1">${escapeHtml(senderName)}</span>
                        ${escapeHtml(message.body || '')}
                    </div>
                </div>
            `
        }).join('')
    }

    const renderLiveMessages = (messages) => messages.map((message) => `
        <div class="flex flex-col gap-1${normalize(message.sender && message.sender.name) === normalize(config.currentUserName) ? ' items-end' : ''}">
            <span class="text-xs font-bold text-primary tracking-wide">${escapeHtml(message.sender && message.sender.name ? message.sender.name : 'Convidado')}</span>
            <p class="${normalize(message.sender && message.sender.name) === normalize(config.currentUserName) ? 'signature-glow text-white rounded-2xl rounded-tr-none' : 'bg-surface-container-lowest rounded-2xl rounded-tl-none text-on-surface-variant'} p-3 text-sm shadow-sm">${escapeHtml(message.body || '')}</p>
        </div>
    `).join('')

    const patchPublicHome = () => {
        const stats = data.stats || {}
        const heroText = q('main section.relative.px-8.pt-20.pb-16 p.font-body.text-xl')
        const liveCards = qa('main section.px-8.py-16 .grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4 > div')
        const creatorCards = qa('section.px-8.py-20.bg-surface-container-low\\/30 .grid > div')

        setText(heroText, `Com ${integer(stats.creators)} criadores ativos, ${integer(stats.live_now)} lives em andamento e ${integer(stats.subscribers)} assinantes em órbita, cada fase da SexyLua já nasce conectada a dados demo reais.`)

        for (let index = 0; index < count(liveCards, data.live_now || []); index += 1) {
            const item = data.live_now[index]
            const card = liveCards[index]

            setText(q('h3', card), item.creator && item.creator.name ? item.creator.name : item.title)
            setText(q('p', card), `${compact(item.viewer_count)} assistindo`)
            setInlineLabel(q('.absolute.bottom-4.right-4 span', card), phaseLabel(item.creator && item.creator.mood))
            setRoute(card, liveUrl(item.id))
        }

        for (let index = 0; index < count(creatorCards, data.featured_creators || []); index += 1) {
            const creator = data.featured_creators[index]
            const card = creatorCards[index]

            setText(q('h4', card), creator.name)
            setText(q('p', card), `${compact(creator.followers || creator.subscriber_count || 0)} fãs`)
            setRoute(card, profileUrl(creator.id))
        }
    }

    const patchPublicExplore = () => {
        const primaryLive = (data.lives || [])[0]
        const cardGrids = qa('main .grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-4.gap-6')
        const liveCards = cardGrids[0] ? qa(':scope > div', cardGrids[0]) : []
        const creatorCards = cardGrids[1] ? qa(':scope > div', cardGrids[1]) : []

        if (primaryLive) {
            setText(q('main section.mb-16 h1'), `${primaryLive.creator && primaryLive.creator.name ? primaryLive.creator.name : 'Live'} em destaque.`)
            setText(q('main section.mb-16 p.text-lg'), primaryLive.description || (primaryLive.creator && primaryLive.creator.headline) || 'Nova sessão demo conectada ao backend.')
            setText(q('main section.mb-16 .flex.items-center.gap-2.mb-4 span:last-child'), `${compact(primaryLive.viewer_count)} assistindo agora`)
            setRoute(q('main section.mb-16 button'), liveUrl(primaryLive.id))
        }

        for (let index = 0; index < count(liveCards, data.lives || []); index += 1) {
            const item = data.lives[index]
            const card = liveCards[index]

            setText(q('h3', card), item.creator && item.creator.name ? item.creator.name : item.title)
            setText(q('p', card), item.description || (item.creator && item.creator.headline) || 'Live em andamento')
            setInlineLabel(q('.absolute.bottom-4.left-4 div:last-child', card), `${compact(item.viewer_count)} AO VIVO`)
            setInlineLabel(q('.absolute.top-4.left-4 span', card), phaseLabel(item.creator && item.creator.mood))
            setRoute(card, liveUrl(item.id))
        }

        for (let index = 0; index < count(creatorCards, data.creators || []); index += 1) {
            const creator = data.creators[index]
            const card = creatorCards[index]

            setText(q('h3', card), creator.name)
            setText(q('p', card), creator.headline || `${compact(creator.followers || 0)} fãs`)
            setInlineLabel(q('.absolute.top-4.left-4 span', card), phaseLabel(creator.mood))
            setRoute(card, profileUrl(creator.id))
        }
    }

    const patchPublicProfile = () => {
        const creator = data.creator || null
        const contents = (data.content || []).slice(0, 3)
        const metricsCard = q('div.bg-surface-container-low.p-8.rounded-lg')
        const moodCard = q('div.bg-primary.p-8.rounded-lg.text-on-primary.relative.overflow-hidden')

        if (!creator) {
            return
        }

        setText(q('h1.font-headline.text-5xl', document), creator.name)
        setLeadingText(q('p.font-body.text-lg.text-on-surface-variant.flex.items-center.gap-2'), handleOf(creator))
        setText(q('div.bg-surface-container-lowest.p-10.rounded-lg.shadow-sm p.font-body.text-xl'), `"${creator.bio || creator.headline || 'Bem-vindo ao meu espaço privado.'}"`)

        const tagNodes = qa('div.bg-surface-container-lowest.p-10.rounded-lg.shadow-sm .flex.gap-4.flex-wrap span')
        const dynamicTags = [`#${phaseLabel(creator.mood)}`, '#Exclusivo', `#${titleCaseType((contents[0] && contents[0].kind) || 'gallery')}`]

        for (let index = 0; index < count(tagNodes, dynamicTags); index += 1) {
            setText(tagNodes[index], dynamicTags[index])
        }

        const contentCards = qa('div.grid.grid-cols-1.md\\:grid-cols-2.gap-6 > div.group')
        for (let index = 0; index < count(contentCards, contents); index += 1) {
            const card = contentCards[index]
            const item = contents[index]

            setText(q('h3', card), item.title)
            setText(q('p', card), item.excerpt)
            setRoute(card, profileUrl(creator.id))
        }

        if (metricsCard) {
            const numbers = qa('p.text-2xl.font-headline.font-black.text-primary', metricsCard)
            const galleries = (data.content || []).filter((item) => item.kind === 'gallery').length
            const videos = (data.content || []).filter((item) => item.kind === 'video').length
            const saves = (data.content || []).reduce((total, item) => total + Number(item.saved_count || 0), 0)

            setText(numbers[0], integer(galleries))
            setText(numbers[1], integer(videos))
            setText(numbers[2], compact(saves))
            setText(numbers[3], compact(creator.subscriber_count || creator.followers || 0))
        }

        if (moodCard) {
            setText(q('p.font-bold.text-xl.italic', moodCard), phaseLabel(creator.mood))
            setText(q('p.text-sm.text-white\\/80', moodCard), `${compact(creator.subscriber_count || 0)} assinantes ativos nesta fase.`)
        }
    }

    const patchPublicLive = () => {
        const live = data.live || null
        const supporters = data.top_supporters || []
        const recentTips = data.recent_tips || []
        const related = data.related_lives || []

        if (!live) {
            return
        }

        setInlineLabel(q('div.absolute.top-6.left-6 span:last-child'), integer(live.viewer_count || 0))
        setText(q('div.flex.items-center.gap-5 h1'), live.creator && live.creator.name ? live.creator.name : live.title)
        setText(q('div.flex.items-center.gap-5 p.text-on-surface-variant.font-medium'), live.description || (live.creator && live.creator.headline) || 'Live demo conectada ao backend.')

        const supporterCards = qa('.px-4.py-3.bg-surface-container.border-b .flex.gap-5.px-1 > div')
        for (let index = 0; index < count(supporterCards, supporters); index += 1) {
            setText(q('span.text-\\[10px\\]', supporterCards[index]), supporters[index].user && supporters[index].user.name ? supporters[index].user.name : 'Fã')
        }

        const tipRows = qa('.px-4.py-3.bg-surface-container.border-b .flex.flex-col.gap-1\\.5 > div')
        for (let index = 0; index < count(tipRows, recentTips); index += 1) {
            setText(q('span.font-bold', tipRows[index]), recentTips[index].sender && recentTips[index].sender.name ? recentTips[index].sender.name : 'Apoiador')
            setText(q('span.text-secondary.font-black', tipRows[index]), integer(recentTips[index].amount || 0))
        }

        const messageStream = q('div.flex-1.overflow-y-auto.p-6.space-y-4.custom-scrollbar')
        if (messageStream && Array.isArray(data.messages) && data.messages.length > 0) {
            messageStream.innerHTML = renderLiveMessages(data.messages.slice(-6))
        }

        const relatedCards = qa('section.mt-20 .grid.grid-cols-2.md\\:grid-cols-4.lg\\:grid-cols-5.gap-6 > div')
        for (let index = 0; index < count(relatedCards, related); index += 1) {
            setText(q('h3', relatedCards[index]), related[index].creator && related[index].creator.name ? related[index].creator.name : related[index].title)
            setText(q('p', relatedCards[index]), related[index].description || (related[index].creator && related[index].creator.headline) || 'Live em destaque')
            setRoute(relatedCards[index], liveUrl(related[index].id))
        }
    }

    const patchSubscriberDashboard = () => {
        setText(q('section.mb-12 h1'), `Olá, ${config.currentUserName || 'Explorador Lunar'}`)
        setText(q('section.mb-12 h3.text-2xl.font-bold.text-primary'), `${integer((data.wallet_balance || 0))} Tokens`)

        const subscriptionCards = qa('section.md\\:col-span-8 .grid.grid-cols-1.sm\\:grid-cols-2.gap-6 > div')
        for (let index = 0; index < count(subscriptionCards, data.subscriptions || []); index += 1) {
            const item = data.subscriptions[index]
            const card = subscriptionCards[index]
            const buttons = qa('button', card)

            setText(q('h4', card), item.creator && item.creator.name ? item.creator.name : 'Criador')
            setText(q('p.text-white\\/70.text-sm', card), `Próxima renovação: ${shortDate(item.renews_at)}`)
            setRoute(card, profileUrl(item.creator.id))
            if (buttons[0]) {
                setRoute(buttons[0], profileUrl(item.creator.id))
            }
        }

        const favoriteRows = qa('section.md\\:col-span-4 .space-y-4 > div')
        for (let index = 0; index < count(favoriteRows, data.conversations || []); index += 1) {
            const item = data.conversations[index]

            setText(q('h5', favoriteRows[index]), item.creator && item.creator.name ? item.creator.name : 'Criador')
            setText(q('p', favoriteRows[index]), item.last_message && item.last_message.body ? item.last_message.body : 'Novo conteúdo disponível')
            setRoute(favoriteRows[index], conversationUrl(item.id))
        }
    }

    const patchSubscriberSubscriptions = () => {
        const subscriptions = data.active_subscriptions || []
        const cards = qa('main .grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-3.gap-8 > div.group')
        const total = subscriptions.reduce((sum, item) => sum + Number(item.plan && item.plan.price_tokens ? item.plan.price_tokens : 0), 0)

        for (let index = 0; index < count(cards, subscriptions); index += 1) {
            const card = cards[index]
            const item = subscriptions[index]
            const buttons = qa('button', card)

            setInlineLabel(q('.absolute.top-4.left-4 span', card), phaseLabel(item.creator && item.creator.mood))
            setText(q('h3', card), item.creator && item.creator.name ? item.creator.name : 'Criador')
            setText(q('p.text-on-surface-variant.text-sm.font-medium', card), handleOf(item.creator))
            setText(q('span.text-primary.font-bold.font-headline', card), brlFromTokens(item.plan && item.plan.price_tokens))
            setText(q('span.text-\\[10px\\].uppercase', card), `Renova em ${shortDate(item.renews_at)}`)
            setRoute(card, profileUrl(item.creator.id))
            if (buttons[0]) {
                setRoute(buttons[0], profileUrl(item.creator.id))
            }
            if (buttons[1]) {
                setRoute(buttons[1], profileUrl(item.creator.id))
            }
        }

        setText(q('section.mt-20 .space-y-4 .flex.justify-between.items-center.text-sm span:last-child'), brlFromTokens(total))
        setText(q('section.mt-20 .w-48.h-48 .text-4xl.font-black.text-primary'), String(subscriptions.length).padStart(2, '0'))
    }

    const patchSubscriberFavorites = () => {
        const favoriteCreators = data.favorite_creators || []
        const savedContent = data.saved_content || []
        const folderCards = qa('main section.mb-16 .flex.gap-6.overflow-x-auto.pb-4.-mx-2.px-2 > div.flex-none.w-64.group.cursor-pointer').slice(0, 3)
        const recentCards = qa('main section .grid.grid-cols-2.lg\\:grid-cols-4.gap-6 > div')

        setText(q('main section.mb-12 p.font-medium'), `${favoriteCreators.length} criadores favoritos e ${savedContent.length} itens salvos para revisitar.`)

        for (let index = 0; index < count(folderCards, favoriteCreators); index += 1) {
            setText(q('h3', folderCards[index]), favoriteCreators[index].name)
            setText(q('p', folderCards[index]), `${integer(favoriteCreators[index].content_count || 0)} itens`)
            setRoute(folderCards[index], profileUrl(favoriteCreators[index].id))
        }

        if (savedContent[0]) {
            setText(q('main section .grid.grid-cols-2.lg\\:grid-cols-4.gap-6 > div:first-child p.font-headline.font-bold.text-lg'), savedContent[0].title)
            setText(q('main section .grid.grid-cols-2.lg\\:grid-cols-4.gap-6 > div:first-child p.text-xs.opacity-80'), `${shortDate(savedContent[0].created_at)} • ${titleCaseType(savedContent[0].kind)}`)
            setRoute(recentCards[0], profileUrl(savedContent[0].creator.id))
        }

        for (let index = 1; index < count(recentCards, savedContent); index += 1) {
            setRoute(recentCards[index], profileUrl(savedContent[index].creator.id))
        }
    }

    const patchSubscriberMessages = () => {
        const conversations = data.conversations || []
        const selected = data.selected_conversation || null
        const rows = qa('aside.w-full.md\\:w-\\[400px\\] .flex-1.overflow-y-auto.hide-scrollbar.px-3.pb-6 > div')

        for (let index = 0; index < count(rows, conversations); index += 1) {
            setText(q('.font-headline.font-bold', rows[index]), conversations[index].creator && conversations[index].creator.name ? conversations[index].creator.name : 'Criador')
            setText(q('p.text-sm', rows[index]), conversations[index].last_message && conversations[index].last_message.body ? conversations[index].last_message.body : 'Sem mensagens ainda.')
            setText(q('.text-\\[10px\\]', rows[index]), relativeLabel(conversations[index].updated_at))
            setRoute(rows[index], conversationUrl(conversations[index].id))
        }

        if (selected) {
            setText(q('section.hidden.md\\:flex h2.font-headline.font-extrabold'), selected.creator && selected.creator.name ? selected.creator.name : 'Conversa')
            setText(q('section.hidden.md\\:flex .text-xs.font-medium.text-outline.uppercase.tracking-wider'), `Ativa • ${relativeLabel(selected.updated_at)}`)
            setValue(q('section.hidden.md\\:flex input[placeholder*="Escreva"]'), '')
        }

        const stream = q('section.hidden.md\\:flex .flex-1.overflow-y-auto.p-8.space-y-6.flex.flex-col.hide-scrollbar')
        if (stream && Array.isArray(data.messages) && data.messages.length > 0) {
            stream.innerHTML = renderConversationMessages(data.messages)
        }
    }

    const patchSubscriberWallet = () => {
        setText(q('section.mt-8 .text-5xl.md\\:text-7xl.font-black.font-headline'), integer(data.balance || 0))

        const packageCards = qa('section.mb-16 .grid.grid-cols-1.md\\:grid-cols-3.gap-8 > div')
        const packages = [500, 1500, 5000]

        for (let index = 0; index < count(packageCards, packages); index += 1) {
            setText(q('.text-3xl.font-black.text-primary, .text-4xl.font-black.text-primary', packageCards[index]), money(packages[index] * tokenPrice))
            setText(q('.bg-surface-container-high.w-full.py-3, .bg-primary-container\\/10.text-primary.w-full.py-3', packageCards[index]), `${integer(packages[index])} Tokens`)
        }

        const historyRows = qa('section:last-of-type .space-y-4 > div')
        for (let index = 0; index < count(historyRows, data.transactions || []); index += 1) {
            setText(q('p.font-bold', historyRows[index]), data.transactions[index].note || data.transactions[index].type || 'Movimentação')
            setText(q('p.text-xs', historyRows[index]), fullDateTime(data.transactions[index].created_at))
            setText(q('p.font-black', historyRows[index]), `${data.transactions[index].direction === 'in' ? '+' : '-'}${integer(data.transactions[index].amount || 0)}`)
        }
    }

    const patchCreatorDashboard = () => {
        const creator = data.creator || {}
        const live = (data.lives || [])[0] || null
        const recentContent = data.recent_content || []
        const inflow = (data.transactions || []).filter((item) => item.direction === 'in').reduce((sum, item) => sum + Number(item.amount || 0), 0)
        const viewBase = recentContent.reduce((sum, item) => sum + Number(item.saved_count || 0), 0)

        setText(q('main h2.text-4xl.font-extrabold'), `Olá, ${creator.name || config.currentUserName || 'Criador'} 👋`)
        setText(q('section.md\\:col-span-8 span.text-3xl.font-black.text-primary.tracking-tighter'), money(inflow * tokenPrice))
        setText(q('section.md\\:col-span-4 span.text-5xl.font-black.tracking-tighter'), compact(creator.followers || creator.subscriber_count || 0))

        const engagementNumbers = qa('section.md\\:col-span-4 span.font-black.text-lg')
        setText(engagementNumbers[0], `${compact(viewBase * 38)}`)
        setText(engagementNumbers[1], `${compact(viewBase * 9)}`)
        setText(engagementNumbers[2], `${compact(Math.max(12, viewBase * 2))}`)

        if (live) {
            setValue(q('input[placeholder*="Noite de Gala"]'), live.title)
            setValue(q('input[type="number"][placeholder="150"]'), live.price_tokens || 0)
        }

        const cards = qa('section.md\\:col-span-7 .grid.grid-cols-2.sm\\:grid-cols-3.gap-6 > div.group')
        for (let index = 0; index < count(cards, recentContent); index += 1) {
            setText(q('p.text-sm.font-bold.truncate', cards[index]), recentContent[index].title)
            setText(q('p.text-\\[10px\\]', cards[index]), `${compact((recentContent[index].saved_count || 0) * 42)} Visualizações`)
        }
    }

    const patchCreatorContent = () => {
        const items = data.items || []
        const totalViews = items.reduce((sum, item) => sum + Number(item.saved_count || 0) * 42, 0)
        const stats = qa('section.px-12.py-8 > div.grid.grid-cols-4.gap-6 > div')
        const cards = qa('section.px-12.py-8 .grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-3.xl\\:grid-cols-4.gap-8 > div.group')

        setText(q('span.text-3xl.font-headline.font-extrabold', stats[0]), integer(items.length))
        setText(q('span.text-3xl.font-headline.font-extrabold', stats[1]), compact(totalViews))
        setText(q('span.text-xl.font-bold', stats[2]), `${(Math.max(1, items.length) * 0.38).toFixed(1).replace('.', ',')} GB`)
        setText(q('span.text-xs.block.text-slate-500', stats[2]), 'de 10 GB')

        for (let index = 0; index < count(cards, items); index += 1) {
            const item = items[index]
            const statusText = item.status === 'approved' ? 'Publicado' : item.status === 'pending' ? 'Pendente' : item.status === 'rejected' ? 'Rejeitado' : 'Rascunho'

            setText(q('h3.font-bold.text-lg', cards[index]), item.title)
            setText(q('div.absolute.top-4.left-4 span', cards[index]), statusText)
            setText(q('div.flex.items-center.gap-4.text-sm.text-slate-500', cards[index]), `${compact((item.saved_count || 0) * 42)} visualizações`)
        }
    }

    const patchCreatorMemberships = () => {
        const subscribers = data.subscribers || []
        const recurring = subscribers.reduce((sum, item) => sum + Number(item.plan && item.plan.price_tokens ? item.plan.price_tokens : 0), 0)
        const metricCards = qa('section.relative .flex.gap-4 > div')
        const subscriberCards = qa('section.grid.grid-cols-1.lg\\:grid-cols-2.gap-8 > div.group')

        setText(q('span.text-primary.font-headline.text-2xl.font-bold', metricCards[0]), integer(data.active_subscribers || subscribers.length))
        setText(q('span.text-primary.font-headline.text-2xl.font-bold', metricCards[1]), brlFromTokens(recurring))

        for (let index = 0; index < count(subscriberCards, subscribers); index += 1) {
            const item = subscribers[index]
            const subscriber = item.subscriber || {}
            const smallStats = qa('p.text-sm.font-semibold.text-on-surface, p.text-sm.font-bold.text-primary', subscriberCards[index])

            setText(q('h3.font-headline.text-xl.font-bold', subscriberCards[index]), subscriber.name || 'Assinante')
            setText(q('span.px-3.py-1.rounded-full', subscriberCards[index]), item.plan && item.plan.name ? item.plan.name : 'Plano Ativo')
            setText(smallStats[0], `${daysUntil(item.renews_at)} dias`)
            setText(smallStats[1], `${brlFromTokens(item.plan && item.plan.price_tokens)}/mês`)
        }

        const bottomStats = qa('section.mt-20 .text-2xl.font-bold.text-on-surface')
        setText(bottomStats[0], `+${Math.max(4, subscribers.length) * 3}%`)
        setText(bottomStats[1], `${Math.min(99, 88 + subscribers.length)}%`)
    }

    const patchCreatorLive = () => {
        const active = data.active_live || (data.lives && data.lives[0]) || null

        if (!active) {
            return
        }

        setValue(q('input[placeholder*="Uma Noite sob a Lua Cheia"]'), active.title)
        setValue(q('input[type="number"][placeholder="0"]'), active.price_tokens || 0)
        setText(q('div.flex.items-center.gap-3 p.font-bold.text-white.shadow-sm'), handleOf(active.creator || { name: config.currentUserName }))
        setText(q('div.flex.items-center.gap-3 p.text-xs.text-white\\/70'), `${compact((active.creator && active.creator.followers) || 0)} Seguidores`)
        setText(q('div.p-4.bg-white\\/10.backdrop-blur-xl.rounded-2xl.text-white\\/90.text-sm.border p.font-medium.italic'), `"${active.description || 'Sessão configurada e pronta para entrar no ar.'}"`)
    }

    const patchCreatorWallet = () => {
        const transactions = data.transactions || []
        const inflow = Number(data.inflow || 0)
        const balance = Number(data.balance || 0)

        setText(q('div.md\\:col-span-2 h3.text-7xl.font-headline.font-extrabold.tracking-tighter'), money(balance * tokenPrice))
        setText(q('div.md\\:col-span-2 span.text-primary-fixed-dim.text-lg.font-bold'), `+${Math.max(4, Math.round((inflow || 1) / 20))}% este mês`)
        setText(q('div.p-10.rounded-xl.bg-surface-container-lowest h4.text-5xl.font-headline.font-extrabold'), integer(balance))
        setText(q('div.p-10.rounded-xl.bg-surface-container-lowest p.text-xs.text-slate-400.font-medium.px-8.leading-relaxed span.text-on-surface.font-bold'), money(balance * tokenPrice))

        const rows = qa('div.p-10.rounded-xl.bg-surface-container-low .space-y-6 > div.group.cursor-pointer')
        for (let index = 0; index < count(rows, transactions); index += 1) {
            setText(q('p.text-sm.font-bold.text-on-surface', rows[index]), transactions[index].note || transactions[index].type || 'Movimentação')
            setText(q('p.text-\\[11px\\].text-slate-500', rows[index]), fullDateTime(transactions[index].created_at))
            setText(q('p.text-sm.font-bold', rows[index]), `${transactions[index].direction === 'in' ? '+' : '-'} ${money((transactions[index].amount || 0) * tokenPrice)}`)
        }
    }

    const patchAdminDashboard = () => {
        const metrics = data.metrics || {}
        const creators = data.top_creators || []
        const rows = qa('tbody > tr.group')
        const activityRows = qa('div.bg-surface-container-low.rounded-xl.p-8 .space-y-6 > div.flex.gap-4')
        const recentUsers = data.recent_users || []
        const pending = data.pending_content || []
        const liveNow = data.live_now || []

        setText(q('div.md\\:col-span-5 h3.text-5xl.font-black.mt-6.tracking-tighter'), money(Number(metrics.platform_result || 0) * tokenPrice))
        setText(q('div.md\\:col-span-3 h3.text-4xl.font-bold.tracking-tight'), integer(metrics.pending_content || 0))
        setText(q('div.md\\:col-span-4 h3.text-5xl.font-black.tracking-tighter'), integer(metrics.creators || 0))

        for (let index = 0; index < count(rows, creators); index += 1) {
            const creatorLive = liveNow.find((item) => Number(item.creator_id) === Number(creators[index].id))

            setText(q('p.font-bold.text-on-surface', rows[index]), creators[index].name)
            setText(q('p.text-xs.text-on-surface-variant', rows[index]), handleOf(creators[index]))
            setText(q('td:nth-child(2) span', rows[index]), creatorLive ? 'Em Live' : creators[index].status === 'active' ? 'Online' : 'Pausado')
            setText(q('td:nth-child(3)', rows[index]), money(Number(creators[index].wallet_balance || 0) * tokenPrice))
        }

        const activities = [
            pending[0] ? { title: `Pendência de moderação - ${pending[0].title}`, meta: `${pending[0].creator && pending[0].creator.name ? pending[0].creator.name : 'Criador'} • ${relativeLabel(pending[0].created_at)}` } : null,
            recentUsers[0] ? { title: `Novo cadastro - ${recentUsers[0].name}`, meta: `${recentUsers[0].role} • ${relativeLabel(recentUsers[0].created_at)}` } : null,
            liveNow[0] ? { title: `Live em andamento - ${liveNow[0].title}`, meta: `${liveNow[0].creator && liveNow[0].creator.name ? liveNow[0].creator.name : 'Criador'} • ${compact(liveNow[0].viewer_count)} viewers` } : null,
        ].filter(Boolean)

        for (let index = 0; index < count(activityRows, activities); index += 1) {
            setText(q('p.text-sm.font-bold', activityRows[index]), activities[index].title)
            setText(q('p.text-xs.text-on-surface-variant', activityRows[index]), activities[index].meta)
        }

        setText(q('section.bg-surface-container-lowest .mt-6 span:first-child'), `Mostrando ${Math.min(3, creators.length)} de ${integer(metrics.creators || creators.length)} Astros`)
    }

    const patchAdminUsers = () => {
        const users = data.users || []
        const metricCards = qa('div.grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-4.gap-6 > div')
        const rows = qa('tbody > tr')

        setText(q('h3', metricCards[0]), integer(users.length))
        setText(q('h3', metricCards[1]), integer(users.filter((user) => user.role === 'creator' && user.status === 'active').length))
        setText(q('h3', metricCards[2]), integer(users.filter((user) => user.status !== 'active').length))
        setText(q('h3', metricCards[3]), integer(users.filter((user) => user.status === 'suspended').length))
        setValue(q('input[placeholder*="Buscar por nome"]'), data.query || '')
        setText(q('div.bg-surface-container-low span.text-sm.text-outline'), `Exibindo ${Math.min(25, users.length)} de ${integer(users.length)}`)

        for (let index = 0; index < count(rows, users); index += 1) {
            const roleText = users[index].role === 'creator' ? 'Criador' : users[index].role === 'subscriber' ? 'Assinante' : 'Admin'

            setText(q('div.text-sm.font-bold.text-on-surface', rows[index]), users[index].name)
            setText(q('div.text-xs.text-outline', rows[index]), users[index].email)
            setText(q('td:nth-child(2) span', rows[index]), roleText)
            setText(q('td:nth-child(3) span.text-sm.font-medium.text-on-surface', rows[index]), users[index].status === 'active' ? 'Ativo' : 'Pendente')
            setText(q('td:nth-child(4)', rows[index]), money(Number(users[index].wallet_balance || 0) * tokenPrice))
        }
    }

    const patchAdminModeration = () => {
        const pending = data.pending || []
        const stats = qa('div.grid.grid-cols-1.md\\:grid-cols-3.gap-6.mb-12 > div')
        const cards = qa('main .grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-3.gap-8 > div')

        setText(q('span.text-4xl', stats[0]), integer(pending.length))
        setText(q('span.text-4xl', stats[1]), String(Math.max(6, pending.length * 3)))
        setText(q('span.text-4xl', stats[2]), pending.length >= 4 ? 'Alto' : 'Moderado')

        for (let index = 0; index < 3 && index < pending.length; index += 1) {
            setText(q('p.text-sm.font-bold', cards[index]), `@${slugify(pending[index].creator && pending[index].creator.name ? pending[index].creator.name : 'criador')}`)
            setText(q('p.text-xs.text-on-surface-variant', cards[index]), `Postado ${relativeLabel(pending[index].created_at)}`)
            setText(q('p.text-on-surface.font-medium.leading-relaxed', cards[index]), pending[index].excerpt || pending[index].body || 'Conteúdo aguardando revisão.')
            setText(q('.absolute.top-4.left-4 span', cards[index]), pending[index].visibility === 'premium' ? 'Premium' : pending[index].kind === 'video' ? 'Vídeo' : 'Em revisão')
        }

        if (pending[3] && cards[3]) {
            setText(q('p.text-lg.font-bold', cards[3]), `@${slugify(pending[3].creator && pending[3].creator.name ? pending[3].creator.name : 'criador')}`)
            setText(q('p.text-sm.text-on-surface-variant', cards[3]), `Postado ${relativeLabel(pending[3].created_at)}`)
            setText(q('p.text-on-surface.font-medium.leading-relaxed.text-lg', cards[3]), pending[3].body || pending[3].excerpt || 'Conteúdo pendente aguardando decisão administrativa.')
            setText(q('.absolute.top-4.left-4 span', cards[3]), pending[3].kind === 'live_teaser' ? 'Teaser em análise' : 'Fila prioritária')
        }

        if (cards[4]) {
            setText(q('p.text-on-primary-container\\/80.font-medium.leading-relaxed', cards[4]), settings.announcement || 'Novas diretrizes e regras demo atualizadas para facilitar a análise do fluxo administrativo.')
        }
    }

    const patchAdminFinance = () => {
        const summary = data.summary || {}
        const transactions = data.transactions || []
        const metrics = qa('div.grid.grid-cols-1.md\\:grid-cols-4.gap-6.mb-10 > div')
        const withdrawals = transactions.filter((item) => item.type === 'payout_request').slice(0, 2)
        const withdrawalRows = qa('div.bg-surface-container-low.border.border-outline-variant\\/10 .space-y-4 > div')
        const tableRows = qa('section.bg-surface-container-lowest tbody > tr')

        setText(q('h3.text-2xl', metrics[0]), money(Number(summary.gross_volume || 0) * tokenPrice))
        setText(q('h3.text-2xl', metrics[1]), integer(summary.top_ups || 0))
        setText(q('h3.text-2xl', metrics[2]), money(Number(summary.creator_income || 0) * tokenPrice))
        setText(q('h3.text-2xl', metrics[3]), `${Math.max(1, Math.round((Number(summary.platform_result || 0) / Math.max(1, Number(summary.gross_volume || 1))) * 100))}%`)

        for (let index = 0; index < count(withdrawalRows, withdrawals); index += 1) {
            setText(q('p.font-bold.text-on-surface.text-sm', withdrawalRows[index]), withdrawals[index].user && withdrawals[index].user.name ? withdrawals[index].user.name : 'Criador')
            setText(q('p.text-xs.text-primary.font-bold', withdrawalRows[index]), money(Number(withdrawals[index].amount || 0) * tokenPrice))
        }

        for (let index = 0; index < count(tableRows, transactions); index += 1) {
            const personName = transactions[index].user && transactions[index].user.name ? transactions[index].user.name : transactions[index].creator && transactions[index].creator.name ? transactions[index].creator.name : 'Usuário'

            setText(q('p.text-sm.font-bold.text-on-surface', tableRows[index]), personName)
            setText(q('p.text-\\[10px\\].text-on-surface-variant', tableRows[index]), transactions[index].user && transactions[index].user.role ? transactions[index].user.role : 'Conta')
            setText(q('td:nth-child(2)', tableRows[index]), `#SL-${String(transactions[index].id).padStart(5, '0')}`)
            setText(q('td:nth-child(3)', tableRows[index]), fullDateTime(transactions[index].created_at))
            setText(q('td:nth-child(4) span', tableRows[index]), transactions[index].type === 'top_up' ? 'Recarga' : transactions[index].type === 'payout_request' ? 'Saque' : 'Assinatura')
            setText(q('td:nth-child(5)', tableRows[index]), money(Number(transactions[index].amount || 0) * tokenPrice))
            setText(q('td:nth-child(6) span', tableRows[index]), transactions[index].type === 'payout_request' ? 'PENDENTE' : 'CONCLUÍDO')
        }
    }

    const patchAdminSettings = () => {
        setValue(q('input[placeholder="20"]'), settings.platform_fee_percent || 20)
        setValue(q('input[placeholder="R$ 1,50"]'), money(tokenPrice).replace('.', ','))

        const ranges = qa('input[type="range"]')
        if (ranges[0]) {
            ranges[0].value = settings.withdraw_min_tokens || 50
        }
        if (ranges[1]) {
            ranges[1].value = settings.withdraw_max_tokens || 25000
        }

        const rangeLabels = qa('div.flex.justify-between.items-end.px-1 span.font-bold.text-primary')
        setText(rangeLabels[0], money(Number(settings.withdraw_min_tokens || 50) * tokenPrice))
        setText(rangeLabels[1], money(Number(settings.withdraw_max_tokens || 25000) * tokenPrice))

        setValue(q('input[value$="s"]'), `${settings.slow_mode_seconds || 0}s`)

        const toggles = qa('input[type="checkbox"]')
        if (toggles[0]) {
            toggles[0].checked = Boolean(settings.withdraw_max_tokens)
        }
        if (toggles[1]) {
            toggles[1].checked = Boolean(settings.slow_mode_seconds)
        }
        if (toggles[2]) {
            toggles[2].checked = Boolean(settings.auto_moderation)
        }
        if (toggles[3]) {
            toggles[3].checked = Boolean(settings.live_chat_enabled)
        }
    }

    attachRouteHandler()

    const pagePatches = {
        'public.home': patchPublicHome,
        'public.explore': patchPublicExplore,
        'public.profile': patchPublicProfile,
        'public.live': patchPublicLive,
        'subscriber.dashboard': patchSubscriberDashboard,
        'subscriber.subscriptions': patchSubscriberSubscriptions,
        'subscriber.favorites': patchSubscriberFavorites,
        'subscriber.messages': patchSubscriberMessages,
        'subscriber.wallet': patchSubscriberWallet,
        'creator.dashboard': patchCreatorDashboard,
        'creator.content': patchCreatorContent,
        'creator.memberships': patchCreatorMemberships,
        'creator.live': patchCreatorLive,
        'creator.wallet': patchCreatorWallet,
        'admin.dashboard': patchAdminDashboard,
        'admin.users': patchAdminUsers,
        'admin.moderation': patchAdminModeration,
        'admin.finance': patchAdminFinance,
        'admin.settings': patchAdminSettings,
    }

    if (typeof pagePatches[page] === 'function') {
        pagePatches[page]()
    }
})()
