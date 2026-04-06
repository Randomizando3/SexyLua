<?php

declare(strict_types=1);

$live = $data['live'] ?? [];
$creator = $live['creator'] ?? [];
$messages = $data['messages'] ?? [];
$relatedLives = $data['related_lives'] ?? [];
$recentTips = $data['recent_tips'] ?? [];
$topSupporters = $data['top_supporters'] ?? [];
$priorityTipTiers = $data['priority_tip_tiers'] ?? [1, 10, 25, 50, 100, 150];
$priorityTipMessages = $data['priority_tip_messages'] ?? [];
$priorityAlert = $data['priority_alert'] ?? null;
$stream = $data['stream'] ?? [];
$viewerCount = (int) ($stream['viewer_count'] ?? $live['viewer_count'] ?? 0);
$canWatch = (bool) ($data['can_watch'] ?? false);
$requiresLogin = (bool) ($data['requires_login'] ?? false);
$requiresSubscription = (bool) ($data['requires_subscription'] ?? false);
$requiresVipUnlock = (bool) ($data['requires_vip_unlock'] ?? false);
$vipUnlocked = (bool) ($data['vip_unlocked'] ?? false);
$vipUnlockPrice = (int) ($data['vip_unlock_price'] ?? $live['price_tokens'] ?? 0);
$darkroomAvailable = (bool) ($data['darkroom_available'] ?? false);
$darkroomActive = (bool) ($data['darkroom_active'] ?? false);
$requiresDarkroomWait = (bool) ($data['requires_darkroom_wait'] ?? false);
$darkroomIsOwner = (bool) ($data['darkroom_is_owner'] ?? false);
$darkroomPrice = (int) ($data['darkroom_price_tokens'] ?? $live['darkroom_price_tokens'] ?? 0);
$darkroomDurationMinutes = (int) ($data['darkroom_duration_minutes'] ?? $live['darkroom_duration_minutes'] ?? 0);
$darkroomRemainingSeconds = (int) ($data['darkroom_remaining_seconds'] ?? 0);
$darkroomOwnerName = (string) ($data['darkroom_owner_name'] ?? '');
$darkroomEndsAt = (string) ($data['darkroom_ends_at'] ?? '');
$darkroomVisible = $darkroomAvailable || $darkroomPrice > 0 || $darkroomDurationMinutes > 0;
$canChat = (bool) ($data['can_chat'] ?? false);
$canTip = (bool) ($data['can_tip'] ?? false);
$isRoomLocked = ! $canWatch && ($requiresVipUnlock || $requiresDarkroomWait);
$visibleMessages = $isRoomLocked ? [] : $messages;
$visibleRecentTips = $isRoomLocked ? [] : $recentTips;
$visibleTopSupporters = $isRoomLocked ? [] : $topSupporters;
$cover = media_url((string) ($live['cover_url'] ?? ''));
$segmentDurationSeconds = (int) ($live['segment_duration_seconds'] ?? $stream['segment_duration_seconds'] ?? 10);
$iceServers = base64_encode((string) json_encode($app->config['app']['rtc_ice_servers'] ?? [], JSON_UNESCAPED_SLASHES));
$iceTransportPolicy = (string) ($app->config['app']['rtc_ice_transport_policy'] ?? 'all');
$profileUrl = path_with_query('/profile', ['id' => (int) ($creator['id'] ?? 0)]);
$messagesUrl = '/login';
$subscriptionsUrl = '/login';
$authUser = $app->auth->user();
$isCreatorViewer = ($authUser['role'] ?? '') === 'creator' && (int) ($authUser['id'] ?? 0) === (int) ($creator['id'] ?? 0);
$isAdminViewer = ($authUser['role'] ?? '') === 'admin';
$darkroomCanActivate = $darkroomAvailable && ! $darkroomActive && ! $isCreatorViewer && ! $isAdminViewer;
$mobileShortcutItems = [];

if (($authUser['role'] ?? '') === 'subscriber') {
    $messagesUrl = '/subscriber/messages';
    $subscriptionsUrl = '/subscriber/subscriptions';
    $mobileShortcutItems = [
        ['href' => '/subscriber', 'label' => 'Inicio', 'icon' => 'home'],
        ['href' => '/subscriber/subscriptions', 'label' => 'Minhas Assinaturas', 'icon' => 'stars'],
        ['href' => '/subscriber/favorites', 'label' => 'Favoritos', 'icon' => 'favorite'],
        ['href' => '/subscriber/messages', 'label' => 'Mensagens', 'icon' => 'chat'],
        ['href' => '/subscriber/wallet', 'label' => 'Carteira', 'icon' => 'account_balance_wallet'],
        ['href' => '/subscriber/settings', 'label' => 'Configuracoes', 'icon' => 'settings'],
    ];
} elseif (($authUser['role'] ?? '') === 'creator') {
    $messagesUrl = '/creator/messages';
    $subscriptionsUrl = '/creator/memberships';
    $mobileShortcutItems = [
        ['href' => '/creator', 'label' => 'Metricas', 'icon' => 'insights'],
        ['href' => '/profile?id=' . (int) ($authUser['id'] ?? 0), 'label' => 'Minha Pagina', 'icon' => 'public'],
        ['href' => '/creator/content', 'label' => 'Meu Conteudo', 'icon' => 'movie'],
        ['href' => '/creator/messages', 'label' => 'Mensagens', 'icon' => 'chat'],
        ['href' => '/creator/live', 'label' => 'Configurar Live', 'icon' => 'settings_input_antenna'],
        ['href' => '/creator/memberships', 'label' => 'Minhas Assinaturas', 'icon' => 'star'],
        ['href' => '/creator/favorites', 'label' => 'Favoritos', 'icon' => 'favorite'],
        ['href' => '/creator/wallet', 'label' => 'Carteira', 'icon' => 'account_balance_wallet'],
        ['href' => '/creator/settings', 'label' => 'Configuracoes', 'icon' => 'settings'],
    ];
} elseif (($authUser['role'] ?? '') === 'admin') {
    $messagesUrl = '/admin/messages';
    $subscriptionsUrl = '/admin';
    $mobileShortcutItems = [
        ['href' => '/admin', 'label' => 'Dashboard', 'icon' => 'dashboard'],
        ['href' => '/admin/users', 'label' => 'Usuarios', 'icon' => 'group'],
        ['href' => '/admin/moderation', 'label' => 'Moderacao', 'icon' => 'policy'],
        ['href' => '/admin/finance', 'label' => 'Financeiro', 'icon' => 'monitoring'],
        ['href' => '/admin/operations', 'label' => 'Operacoes', 'icon' => 'dataset'],
        ['href' => '/admin/messages', 'label' => 'Mensagens', 'icon' => 'chat'],
        ['href' => '/admin/settings', 'label' => 'Configuracoes', 'icon' => 'settings'],
    ];
}

$liveStatus = (string) ($stream['status'] ?? 'idle');
$liveStatusLabel = $liveStatus === 'live' ? 'Ao vivo' : ($liveStatus === 'ended' ? 'Encerrada' : 'Aguardando');
$defaultTipAmount = max(1, (int) ($priorityTipTiers[0] ?? 10));
$defaultTipMessage = trim((string) ($priorityTipMessages[(string) $defaultTipAmount] ?? ''));
$tipTotalAmount = (int) ($data['tip_total_amount'] ?? 0);
$accessModeLabel = match ((string) ($live['access_mode'] ?? 'public')) {
    'subscriber' => 'Assinantes',
    'vip' => 'Live VIP',
    default => 'Publico',
};

$accessMessage = (string) ($data['access_message'] ?? '');
if ($accessMessage === '') {
    $accessMessage = $canWatch ? 'Aguardando o criador iniciar a live.' : 'A live ainda nao esta disponivel.';
}
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Sala da Live</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,700;0,800;1,800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style>
        body { background-color: #fbf9fb; background-image: radial-gradient(circle at 10% 20%, rgba(216, 27, 96, 0.03) 0%, transparent 50%), radial-gradient(circle at 90% 80%, rgba(171, 17, 85, 0.03) 0%, transparent 50%); color: #1b1c1d; font-family: Manrope, sans-serif; }
        .headline { font-family: "Plus Jakarta Sans", sans-serif; }
        .signature-glow { background: linear-gradient(135deg, #ab1155 0%, #cc326e 100%); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(171, 17, 85, 0.2); border-radius: 999px; }
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 500, "GRAD" 0, "opsz" 24; }
    </style>
</head>
<body class="antialiased">
<?php require BASE_PATH . '/templates/partials/public_topbar.php'; ?>

<main class="mx-auto max-w-7xl px-4 pb-20 pt-24 md:px-8">
    <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <section class="order-1 space-y-6 lg:col-span-8">
            <div
                class="overflow-hidden rounded-[2rem] bg-white shadow-2xl"
                data-live-rtc-mode="viewer"
                data-live-id="<?= e((string) ((int) ($live['id'] ?? 0))) ?>"
                data-csrf="<?= e($app->csrf->token()) ?>"
                data-can-watch="<?= $canWatch ? '1' : '0' ?>"
                data-access-message="<?= e($accessMessage) ?>"
                data-darkroom-active="<?= $darkroomActive ? '1' : '0' ?>"
                data-darkroom-is-owner="<?= $darkroomIsOwner ? '1' : '0' ?>"
                data-requires-darkroom-wait="<?= $requiresDarkroomWait ? '1' : '0' ?>"
                data-join-url="/live/rtc/join"
                data-signal-url="/live/rtc/signal"
                data-poll-url="/live/rtc/poll"
                data-heartbeat-url="/live/rtc/heartbeat"
                data-leave-url="/live/rtc/leave"
                data-hls-url="<?= e((string) ($stream['hls_url'] ?? '')) ?>"
                data-ice-servers="<?= e($iceServers) ?>"
                data-ice-transport-policy="<?= e($iceTransportPolicy) ?>"
                data-segment-duration-ms="<?= e((string) ($segmentDurationSeconds * 1000)) ?>"
                data-live-priority-alert-duration-ms="<?= e((string) ((int) site_setting('live_priority_alert_duration_ms', 8000))) ?>"
            >
                <div class="relative aspect-video bg-slate-950">
                    <video class="absolute inset-0 z-[1] h-full w-full bg-slate-950 object-cover transition-opacity duration-500 opacity-100" controls data-live-remote-video playsinline></video>
                    <?php if ($cover !== ''): ?><img alt="Capa da live" class="absolute inset-0 h-full w-full object-cover opacity-25" src="<?= e($cover) ?>"><?php endif; ?>
                    <div class="<?= $liveStatus === 'ended' ? '' : 'hidden ' ?>absolute left-1/2 top-24 z-[4] w-[min(92%,36rem)] -translate-x-1/2 rounded-3xl border border-white/20 bg-[#D81B60]/90 px-6 py-4 text-center text-white shadow-xl backdrop-blur-md" data-live-ended-banner>
                        <p class="headline text-lg font-extrabold">Encerramos por aqui. Obrigado por assistir, ate a proxima!</p>
                    </div>
                    <div class="hidden absolute left-1/2 top-24 z-[5] w-[min(92%,40rem)] -translate-x-1/2 rounded-3xl border border-white/25 bg-white/95 px-5 py-4 text-slate-900 shadow-2xl backdrop-blur-md" data-live-priority-alert>
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[#fff4d6]">
                                <img alt="LuaCoin" class="h-7 w-7" src="<?= e(asset('img/luacoin.png')) ?>">
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-[0.28em] text-[#D81B60]">Mensagem em destaque</p>
                                <p class="mt-1 text-sm font-semibold text-slate-700" data-live-priority-alert-text><?= e((string) ($priorityAlert['alert_text'] ?? '')) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="hidden absolute left-1/2 top-24 z-[6] w-[min(92%,40rem)] -translate-x-1/2 rounded-3xl border border-white/20 bg-emerald-500/90 px-5 py-4 text-white shadow-2xl backdrop-blur-md" data-live-inline-alert>
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white/15">
                                <img alt="LuaCoin" class="h-7 w-7" src="<?= e(asset('img/luacoin.png')) ?>">
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-[0.28em] text-white/75" data-live-inline-alert-kicker>Atualizacao da sala</p>
                                <p class="mt-1 text-sm font-semibold text-white" data-live-inline-alert-text></p>
                            </div>
                        </div>
                    </div>
                    <div class="<?= $darkroomActive ? '' : 'hidden ' ?>absolute left-1/2 top-24 z-[5] w-[min(92%,42rem)] -translate-x-1/2 rounded-3xl border border-white/20 bg-slate-950/80 px-5 py-4 text-white shadow-2xl backdrop-blur-md" data-live-darkroom-banner>
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white/10">
                                <span class="material-symbols-outlined text-[30px] text-white">visibility_lock</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-[0.28em] text-white/70" data-live-darkroom-banner-kicker><?= e($darkroomIsOwner ? 'Darkroom ativo para voce' : 'Darkroom ativo') ?></p>
                                <p class="mt-1 text-sm font-semibold text-white" data-live-darkroom-banner-text><?= e($accessMessage) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute left-6 right-6 top-6 flex items-start justify-between gap-4 text-white">
                        <div class="flex items-center gap-3">
                            <span class="signature-glow rounded-full px-4 py-2 text-[10px] font-bold uppercase tracking-[0.25em]" data-live-status-text><?= e($liveStatusLabel) ?></span>
                            <span class="rounded-full bg-black/35 px-4 py-2 text-[10px] font-bold uppercase tracking-[0.25em]"><span data-live-viewer-count><?= e((string) $viewerCount) ?></span> viewers</span>
                        </div>
                        <span class="rounded-full bg-black/35 px-4 py-2 text-[10px] font-bold uppercase tracking-[0.25em]" data-live-stream-state><?= e($liveStatusLabel) ?></span>
                    </div>
                    <div class="absolute inset-0 z-[3] flex items-center justify-center bg-black/55 px-6 text-center text-white backdrop-blur-sm" data-live-waiting>
                        <div class="max-w-md">
                            <p class="headline text-4xl font-extrabold"><?= e((string) ($live['title'] ?? 'Live')) ?></p>
                            <p class="mt-3 text-sm text-white/75" data-live-waiting-text><?= e($accessMessage) ?></p>
                            <?php if ($requiresLogin): ?>
                                <a class="signature-glow mt-5 inline-flex items-center justify-center rounded-full px-6 py-3 text-sm font-bold text-white shadow-lg" href="/login">Entrar para assistir</a>
                            <?php elseif ($requiresDarkroomWait): ?>
                                <div class="mt-5 rounded-3xl bg-white/10 p-4 backdrop-blur-md">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.28em] text-white/70">Darkroom ativo</p>
                                    <p class="mt-2 text-sm text-white/80"><?= e($accessMessage) ?></p>
                                </div>
                            <?php elseif ($requiresVipUnlock): ?>
                                <div class="mt-5 rounded-3xl bg-white/10 p-4 backdrop-blur-md">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.28em] text-white/70">Live VIP</p>
                                    <p class="mt-2 text-sm text-white/80">Desbloqueie esta sala por <?= luacoin_amount_html($vipUnlockPrice, 'inline-flex items-center gap-1.5 whitespace-nowrap', 'text-white', 'h-4 w-4 shrink-0') ?> e assista normalmente daqui em diante.</p>
                                    <form action="/live/unlock" class="mt-4" method="post">
                                        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                        <input name="live_id" type="hidden" value="<?= e((string) ((int) ($live['id'] ?? 0))) ?>">
                                        <input name="redirect" type="hidden" value="<?= e(path_with_query('/live', ['id' => (int) ($live['id'] ?? 0)])) ?>">
                                        <button class="signature-glow inline-flex items-center justify-center rounded-full px-6 py-3 text-sm font-bold text-white shadow-lg" data-prototype-skip="1" type="submit">Desbloquear live VIP</button>
                                    </form>
                                </div>
                            <?php elseif ($requiresSubscription): ?>
                                <a class="signature-glow mt-5 inline-flex items-center justify-center rounded-full px-6 py-3 text-sm font-bold text-white shadow-lg" href="<?= e($profileUrl) ?>">Assinar para liberar</a>
                            <?php endif; ?>
                            <button class="mt-5 hidden rounded-full bg-white px-6 py-3 text-sm font-bold uppercase tracking-widest text-[#ab1155]" data-live-playback data-prototype-skip="1" type="button">Continuar assistindo</button>
                        </div>
                    </div>
                </div>

                <div class="space-y-5 p-6">
                    <div class="hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800" data-live-error></div>

                    <details class="group rounded-3xl bg-[#fbf7fa]" open>
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 rounded-3xl px-1 py-1 marker:content-none">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-[#ab1155]">Sala ao vivo</p>
                                <p class="mt-2 text-sm font-semibold text-slate-700">Perfil, gorjeta rapida e detalhes da live</p>
                            </div>
                            <span class="material-symbols-outlined rounded-full bg-white p-2 text-slate-700 transition-transform group-open:rotate-180">expand_more</span>
                        </summary>
                        <div class="space-y-5 px-1 pb-1 pt-4">
                            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                                <a class="group flex min-w-0 items-center gap-5 rounded-3xl bg-white p-4 shadow-sm xl:col-span-2" href="<?= e($profileUrl) ?>">
                                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-full border-2 border-[#ab1155] p-1">
                                        <?php if ((string) ($creator['avatar_url'] ?? '') !== ''): ?>
                                            <img alt="<?= e((string) ($creator['name'] ?? 'Criador')) ?>" class="h-full w-full rounded-full object-cover" src="<?= e(media_url((string) ($creator['avatar_url'] ?? ''))) ?>">
                                        <?php else: ?>
                                            <div class="signature-glow flex h-full w-full items-center justify-center rounded-full text-lg font-bold text-white"><?= e(avatar_initials((string) ($creator['name'] ?? 'Criador'))) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="min-w-0">
                                        <h1 class="headline text-3xl font-extrabold tracking-tight transition-colors group-hover:text-[#ab1155]"><?= e((string) ($creator['name'] ?? 'Criador')) ?></h1>
                                        <p class="mt-1 text-slate-500"><?= e((string) ($creator['headline'] ?? 'Criando experiencias exclusivas ao vivo.')) ?></p>
                                    </div>
                                </a>
                                <div class="contents">
                                    <?php if ($canTip): ?>
                                        <form action="/tip" class="<?= $darkroomVisible ? '' : 'xl:col-span-2 ' ?>flex flex-col gap-3 rounded-3xl bg-white p-4 shadow-sm" data-live-tip-form method="post">
                                            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                            <input name="creator_id" type="hidden" value="<?= e((string) ((int) ($creator['id'] ?? 0))) ?>">
                                            <input name="live_id" type="hidden" value="<?= e((string) ((int) ($live['id'] ?? 0))) ?>">
                                            <input name="amount" type="hidden" value="<?= e((string) $defaultTipAmount) ?>">
                                            <div class="flex flex-wrap gap-2">
                                                <?php foreach (array_slice($priorityTipTiers, 0, 6) as $tier): ?>
                                                    <?php $tierKey = (string) ((int) $tier); ?>
                                                    <?php $tierMessage = trim((string) ($priorityTipMessages[$tierKey] ?? ('Mensagem em destaque de ' . $tierKey . ' LuaCoins.'))); ?>
                                                    <button aria-pressed="<?= (int) $tier === $defaultTipAmount ? 'true' : 'false' ?>" class="<?= (int) $tier === $defaultTipAmount ? 'signature-glow text-white' : 'bg-[#f5f3f5] text-[#ab1155]' ?> rounded-full px-4 py-2 text-xs font-bold transition-colors" data-live-tip-preset="<?= e($tierKey) ?>" data-live-tip-message="<?= e($tierMessage) ?>" data-prototype-skip="1" type="button"><?= e($tierKey) ?></button>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="rounded-3xl bg-[#f8f4f7] p-3">
                                                <label class="block text-[10px] font-bold uppercase tracking-[0.22em] text-slate-400">Mensagem da gorjeta</label>
                                                <input class="mt-2 w-full rounded-full border-none bg-white px-5 py-3 text-sm text-slate-800 shadow-sm focus:ring-2 focus:ring-[#ab1155]/20" maxlength="180" name="message" placeholder="Edite a mensagem que vai aparecer sobre o player" type="text" value="<?= e($defaultTipMessage !== '' ? $defaultTipMessage : 'Mensagem em destaque de ' . $defaultTipAmount . ' LuaCoins.') ?>">
                                            </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="rounded-full bg-[#f5f3f5] px-4 py-3 text-xs font-bold uppercase tracking-[0.22em] text-slate-500">
                                            Valor selecionado:
                                            <span class="ml-1 text-[#ab1155]" data-live-tip-amount-label><?= e((string) $defaultTipAmount) ?></span>
                                            <img alt="LuaCoin" class="ml-1 inline h-3.5 w-3.5" src="<?= e(asset('img/luacoin.png')) ?>">
                                        </div>
                                        <button class="signature-glow rounded-full px-8 py-3 text-sm font-bold text-white shadow-lg" data-prototype-skip="1" type="submit">Enviar gorjeta</button>
                                    </div>
                                        </form>
                                    <?php elseif ($requiresLogin): ?>
                                        <a class="signature-glow rounded-3xl px-8 py-4 text-center text-sm font-bold text-white shadow-lg xl:col-span-2" href="/login">Entrar para assistir</a>
                                    <?php elseif ($requiresSubscription): ?>
                                        <a class="signature-glow rounded-3xl px-8 py-4 text-center text-sm font-bold text-white shadow-lg xl:col-span-2" href="<?= e($profileUrl) ?>">Assinar para liberar</a>
                                    <?php elseif ($requiresVipUnlock): ?>
                                        <form action="/live/unlock" class="flex flex-col gap-3 rounded-3xl bg-white p-4 shadow-sm xl:col-span-2" method="post">
                                            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                            <input name="live_id" type="hidden" value="<?= e((string) ((int) ($live['id'] ?? 0))) ?>">
                                            <input name="redirect" type="hidden" value="<?= e(path_with_query('/live', ['id' => (int) ($live['id'] ?? 0)])) ?>">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-[0.22em] text-slate-400">Live VIP</label>
                                                <p class="mt-2 text-sm font-semibold text-slate-700">Desbloqueie por <?= luacoin_amount_html($vipUnlockPrice, 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?> e entre nesta transmissão.</p>
                                            </div>
                                            <button class="signature-glow w-fit rounded-full px-8 py-3 text-sm font-bold text-white shadow-lg" data-prototype-skip="1" type="submit">Desbloquear live VIP</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($darkroomVisible): ?>
                                        <div class="rounded-3xl bg-white p-4 shadow-sm">
                                            <div class="flex h-full flex-col justify-between gap-4">
                                                <div>
                                                    <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-slate-400">Darkroom</p>
                                                    <?php if ($darkroomActive): ?>
                                                        <p class="mt-2 text-sm font-semibold text-slate-700"><?= e($darkroomIsOwner ? 'Seu darkroom esta ativo agora.' : $accessMessage) ?></p>
                                                        <p class="mt-2 text-xs text-slate-500"><?= e($darkroomIsOwner ? ('Duracao de ' . $darkroomDurationMinutes . ' minuto(s).') : 'A live volta automaticamente ao fim deste periodo.') ?></p>
                                                    <?php elseif ($darkroomCanActivate): ?>
                                                        <p class="mt-2 text-sm font-semibold text-slate-700">Ative a sala privada por <?= luacoin_amount_html($darkroomPrice, 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?> durante <?= e((string) $darkroomDurationMinutes) ?> min.</p>
                                                        <p class="mt-2 text-xs text-slate-500">Quando ativado, a live fica exclusiva para voce neste periodo.</p>
                                                    <?php elseif (! $darkroomAvailable): ?>
                                                        <p class="mt-2 text-sm font-semibold text-slate-700">Darkroom indisponivel nesta live.</p>
                                                        <p class="mt-2 text-xs text-slate-500">A configuracao desta sala nao foi concluida com valor e duracao validos.</p>
                                                    <?php elseif ($requiresLogin): ?>
                                                        <p class="mt-2 text-sm font-semibold text-slate-700">Entre na sua conta para ativar o darkroom desta live.</p>
                                                        <p class="mt-2 text-xs text-slate-500">Depois do login, voce podera desbloquear a sala privada usando LuaCoins.</p>
                                                    <?php elseif ($requiresVipUnlock): ?>
                                                        <p class="mt-2 text-sm font-semibold text-slate-700">Desbloqueie a Live VIP antes de ativar o darkroom.</p>
                                                        <p class="mt-2 text-xs text-slate-500">O darkroom funciona como uma camada extra sobre o acesso base desta live.</p>
                                                    <?php elseif ($requiresSubscription): ?>
                                                        <p class="mt-2 text-sm font-semibold text-slate-700">Assine este perfil para poder ativar o darkroom.</p>
                                                        <p class="mt-2 text-xs text-slate-500">Apos liberar o acesso da live, o darkroom pode ser ativado por tempo limitado.</p>
                                                    <?php else: ?>
                                                        <p class="mt-2 text-sm font-semibold text-slate-700">Darkroom disponivel para espectadores elegiveis.</p>
                                                        <p class="mt-2 text-xs text-slate-500">Assim que a live estiver liberada para voce, sera possivel ativar a sala privada.</p>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($darkroomActive): ?>
                                                    <span class="inline-flex w-fit rounded-full bg-[#f5f3f5] px-4 py-3 text-xs font-bold uppercase tracking-[0.22em] text-slate-500" data-live-darkroom-status><?= e($darkroomIsOwner ? 'Darkroom ativo para voce' : 'Darkroom indisponivel no momento') ?></span>
                                                <?php elseif ($darkroomCanActivate): ?>
                                                    <form action="/live/darkroom" data-live-darkroom-form method="post">
                                                        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                                        <input name="live_id" type="hidden" value="<?= e((string) ((int) ($live['id'] ?? 0))) ?>">
                                                        <input name="redirect" type="hidden" value="<?= e(path_with_query('/live', ['id' => (int) ($live['id'] ?? 0)])) ?>">
                                                        <button class="rounded-full bg-slate-900 px-6 py-3 text-sm font-bold text-white" data-live-darkroom-button data-prototype-skip="1" type="submit">Ativar darkroom</button>
                                                    </form>
                                                <?php elseif (! $darkroomAvailable): ?>
                                                    <span class="inline-flex w-fit rounded-full bg-[#f5f3f5] px-4 py-3 text-xs font-bold uppercase tracking-[0.22em] text-slate-500" data-live-darkroom-status>Configuracao incompleta</span>
                                                <?php elseif ($requiresLogin): ?>
                                                    <a class="inline-flex w-fit items-center justify-center rounded-full bg-slate-900 px-6 py-3 text-sm font-bold text-white" href="/login">Entrar para ativar</a>
                                                <?php elseif ($requiresSubscription): ?>
                                                    <a class="inline-flex w-fit items-center justify-center rounded-full bg-slate-900 px-6 py-3 text-sm font-bold text-white" href="<?= e($profileUrl) ?>">Assinar perfil</a>
                                                <?php else: ?>
                                                    <span class="inline-flex w-fit rounded-full bg-[#f5f3f5] px-4 py-3 text-xs font-bold uppercase tracking-[0.22em] text-slate-500" data-live-darkroom-status>Disponivel para espectadores</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
                                <div class="rounded-2xl bg-[#f5f3f5] p-4">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Categoria</p>
                            <p class="mt-2 text-sm font-bold text-slate-700"><?= e((string) ($live['category_label'] ?? 'Todos')) ?></p>
                                </div>
                                <div class="rounded-2xl bg-[#f5f3f5] p-4">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Acesso</p>
                                    <p class="mt-2 text-sm font-bold text-slate-700"><?= e($accessModeLabel) ?></p>
                                </div>
                                <div class="rounded-2xl bg-[#f5f3f5] p-4">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Agendada</p>
                                    <p class="mt-2 text-sm font-bold text-slate-700"><?= e(format_datetime((string) ($live['scheduled_for'] ?? ''), 'd/m H:i')) ?></p>
                                </div>
                                <div class="rounded-2xl bg-[#f5f3f5] p-4">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Status</p>
                                    <p class="mt-2 text-sm font-bold text-slate-700"><?= e($liveStatusLabel) ?></p>
                                </div>
                                <div class="rounded-2xl bg-[#f5f3f5] p-4">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Gorjetas</p>
                                    <div class="mt-2 text-sm font-bold text-slate-700" data-live-tip-total><?= luacoin_amount_html($tipTotalAmount, 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?></div>
                                </div>
                                <div class="rounded-2xl bg-[#f5f3f5] p-4">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Meta</p>
                                    <div class="mt-2 text-sm font-bold text-slate-700"><?= luacoin_amount_html((int) ($live['goal_tokens'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?></div>
                                </div>
                            </div>
                        </div>
                    </details>
                </div>
            </div>
        </section>

        <aside class="order-2 custom-scrollbar flex h-[760px] flex-col overflow-hidden rounded-[2rem] bg-[#f5f3f5] shadow-sm lg:col-span-4">
            <div class="border-b border-slate-200/70 bg-white px-5 py-4">
                <div class="flex items-center justify-between">
                    <span class="headline text-sm font-bold uppercase tracking-[0.2em]">Chat da Lua</span>
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-[#ab1155]"><span data-live-viewer-count><?= e((string) $viewerCount) ?></span> online</span>
                </div>
            </div>

            <details class="group border-b border-slate-200/70 px-5 py-4" data-live-support-expander>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 marker:content-none">
                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Apoio da live</h4>
                        <p class="mt-2 text-sm font-semibold text-slate-700">Top supporters e doacoes recentes</p>
                    </div>
                    <span class="material-symbols-outlined rounded-full bg-white p-2 text-slate-700 transition-transform group-open:rotate-180">expand_more</span>
                </summary>
                <div class="space-y-4 pt-4">
                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Top supporters</h4>
                        <div class="mt-3 flex gap-4" data-live-top-supporters data-live-supporters-variant="viewer">
                            <?php foreach (array_slice($visibleTopSupporters, 0, 3) as $supporter): ?>
                                <div class="flex flex-col items-center">
                                    <div class="signature-glow flex h-12 w-12 items-center justify-center rounded-full text-sm font-bold text-white"><?= e(avatar_initials((string) ($supporter['user']['name'] ?? 'Fan'))) ?></div>
                                    <span class="mt-2 text-[10px] font-bold text-[#ab1155]"><?= e((string) ($supporter['user']['name'] ?? 'Fan')) ?></span>
                                    <span class="text-[10px] text-slate-500"><?= luacoin_amount_html((int) ($supporter['amount'] ?? 0), 'inline-flex items-center gap-1 whitespace-nowrap', '', 'h-3 w-3 shrink-0') ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="<?= $visibleTopSupporters === [] ? '' : 'hidden ' ?>mt-3 text-sm text-slate-500" data-live-top-supporters-empty>Sem ranking ainda.</p>
                    </div>

                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Doacoes recentes</h4>
                        <div class="mt-3 space-y-2" data-live-recent-tips data-live-tips-variant="viewer">
                            <?php foreach (array_slice($visibleRecentTips, 0, 4) as $tip): ?>
                                <div class="flex items-center justify-between rounded-full bg-white px-4 py-2 text-xs">
                                    <span class="font-bold text-slate-800"><?= e((string) ($tip['sender']['name'] ?? 'Fan')) ?></span>
                                    <span class="font-black text-[#ab1155]"><?= luacoin_amount_html((int) ($tip['amount'] ?? 0), 'inline-flex items-center gap-1 whitespace-nowrap', '', 'h-3.5 w-3.5 shrink-0') ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="<?= $visibleRecentTips === [] ? '' : 'hidden ' ?>mt-3 text-sm text-slate-500" data-live-recent-tips-empty>Sem gorjetas recentes.</p>
                    </div>
                </div>
            </details>

            <div class="custom-scrollbar flex-1 space-y-4 overflow-y-auto px-5 py-5" data-live-chat-stream data-live-chat-variant="viewer">
                <?php foreach ($visibleMessages as $message): ?>
                    <?php
                    $theme = $message['highlight_theme'] ?? [];
                    $isHighlighted = (bool) ($message['is_highlighted'] ?? false);
                    ?>
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-bold tracking-wide text-[#ab1155]"><?= e((string) ($message['sender']['name'] ?? 'Visitante')) ?></span>
                            <?php if ($isHighlighted): ?>
                                <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em]" style="background:<?= e((string) ($theme['label_background'] ?? '#f59e0b')) ?>;color:<?= e((string) ($theme['label_text'] ?? '#ffffff')) ?>"><?= e((string) ($message['highlight_label'] ?? 'Destaque')) ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="rounded-2xl rounded-tl-none border bg-white p-3 text-sm text-slate-600 shadow-sm" style="<?= $isHighlighted ? 'background:' . e((string) ($theme['background'] ?? '#fff6cf')) . ';border-color:' . e((string) ($theme['border'] ?? '#fde68a')) . ';' : 'border-color:transparent;' ?>"><?= e((string) ($message['body'] ?? '')) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="<?= $visibleMessages === [] ? '' : 'hidden ' ?>px-5 pb-4 text-sm text-slate-500" data-live-chat-empty>Ainda nao ha mensagens nesta live.</p>

            <div class="border-t border-slate-200/70 bg-white p-4">
                <?php if ($canChat): ?>
                    <form action="/live/chat" class="flex items-center gap-3" data-live-chat-form method="post">
                        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                        <input name="live_id" type="hidden" value="<?= e((string) ((int) ($live['id'] ?? 0))) ?>">
                        <input class="min-w-0 flex-1 rounded-full border-none bg-[#f5f3f5] px-5 py-3 text-sm shadow-sm focus:ring-2 focus:ring-[#ab1155]/20" name="body" placeholder="Diga algo no chat..." required type="text">
                        <button class="signature-glow flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-white shadow-lg" data-prototype-skip="1" type="submit">
                            <span class="material-symbols-outlined text-[20px]">send</span>
                        </button>
                    </form>
                <?php elseif ($requiresDarkroomWait): ?>
                    <p class="text-sm text-slate-500"><?= e($accessMessage) ?></p>
                <?php elseif ($requiresLogin): ?>
                    <a class="signature-glow block w-full rounded-full px-5 py-4 text-center text-sm font-bold text-white" href="/login">Entrar para falar no chat</a>
                <?php elseif ($requiresSubscription): ?>
                    <a class="signature-glow block w-full rounded-full px-5 py-4 text-center text-sm font-bold text-white" href="<?= e($profileUrl) ?>">Assinar para falar no chat</a>
                <?php elseif ($requiresVipUnlock): ?>
                    <form action="/live/unlock" class="flex flex-col gap-3" method="post">
                        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                        <input name="live_id" type="hidden" value="<?= e((string) ((int) ($live['id'] ?? 0))) ?>">
                        <input name="redirect" type="hidden" value="<?= e(path_with_query('/live', ['id' => (int) ($live['id'] ?? 0)])) ?>">
                        <button class="signature-glow block w-full rounded-full px-5 py-4 text-center text-sm font-bold text-white" data-prototype-skip="1" type="submit">Desbloquear chat da Live VIP</button>
                    </form>
                <?php else: ?>
                    <p class="text-sm text-slate-500">O chat esta fechado nesta live.</p>
                <?php endif; ?>
            </div>
        </aside>

        <section class="order-3 rounded-[2rem] bg-white p-6 shadow-sm lg:col-span-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="headline text-2xl font-extrabold">Outras lives em destaque</h2>
                    <p class="mt-2 text-sm text-slate-500">Criadores que estao brilhando agora.</p>
                </div>
                <a class="text-sm font-bold text-[#ab1155] underline" href="/explore">Ver mais</a>
            </div>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach (array_slice($relatedLives, 0, 6) as $item): ?>
                    <?php
                    $itemCover = media_url((string) ($item['cover_url'] ?? ''));
                    $itemStatus = (string) ($item['stream_status'] ?? $item['status'] ?? 'idle');
                    ?>
                    <a class="overflow-hidden rounded-3xl bg-[#f5f3f5] transition-transform hover:-translate-y-1" href="<?= e(path_with_query('/live', ['id' => (int) ($item['id'] ?? 0)])) ?>">
                        <div class="aspect-[4/3] bg-slate-900">
                            <?php if ($itemCover !== ''): ?><img alt="<?= e((string) ($item['title'] ?? 'Live')) ?>" class="h-full w-full scale-105 object-cover blur-[2px]" src="<?= e($itemCover) ?>"><?php else: ?><div class="signature-glow flex h-full w-full items-center justify-center text-white"><span class="headline text-xl font-extrabold">LIVE</span></div><?php endif; ?>
                        </div>
                        <div class="space-y-2 p-4">
                            <p class="headline text-lg font-extrabold"><?= e((string) ($item['title'] ?? 'Live')) ?></p>
                            <p class="text-sm text-slate-500"><?= e((string) ($item['creator']['name'] ?? 'Criador')) ?></p>
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400"><?= e($itemStatus) ?> &bull; <?= e((string) ($item['viewer_count'] ?? 0)) ?> viewers</p>
                        </div>
                    </a>
                <?php endforeach; ?>
                <?php if ($relatedLives === []): ?><p class="rounded-3xl bg-[#f5f3f5] p-6 text-sm text-slate-500">Nenhuma outra live em destaque agora.</p><?php endif; ?>
            </div>
        </section>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
<script src="<?= e(asset('js/live-segment.js')) ?>"></script>
</body>
</html>
