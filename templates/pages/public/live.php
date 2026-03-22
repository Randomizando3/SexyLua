<?php

declare(strict_types=1);

$live = $data['live'] ?? [];
$creator = $live['creator'] ?? [];
$messages = $data['messages'] ?? [];
$relatedLives = $data['related_lives'] ?? [];
$recentTips = $data['recent_tips'] ?? [];
$topSupporters = $data['top_supporters'] ?? [];
$stream = $data['stream'] ?? [];
$viewerCount = (int) ($stream['viewer_count'] ?? $live['viewer_count'] ?? 0);
$canWatch = (bool) ($data['can_watch'] ?? false);
$requiresLogin = (bool) ($data['requires_login'] ?? false);
$requiresSubscription = (bool) ($data['requires_subscription'] ?? false);
$canChat = (bool) ($data['can_chat'] ?? false);
$canTip = (bool) ($data['can_tip'] ?? false);
$cover = media_url((string) ($live['cover_url'] ?? ''));
$replayUrl = media_url((string) ($live['recording_url'] ?? ''));
$replayDuration = (int) ($live['recording_duration_seconds'] ?? 0);
$hasReplay = $replayUrl !== '' && (bool) ($live['recording_enabled'] ?? false);
$iceServers = base64_encode((string) json_encode($app->config['app']['rtc_ice_servers'] ?? [], JSON_UNESCAPED_SLASHES));
$iceTransportPolicy = (string) ($app->config['app']['rtc_ice_transport_policy'] ?? 'all');
$profileUrl = path_with_query('/profile', ['id' => (int) ($creator['id'] ?? 0)]);
$messagesUrl = '/login';
$subscriptionsUrl = '/login';
$authUser = $app->auth->user();

if (($authUser['role'] ?? '') === 'subscriber') {
    $messagesUrl = '/subscriber/messages';
    $subscriptionsUrl = '/subscriber/subscriptions';
}

$accessMessage = $canWatch
    ? ''
    : ($requiresLogin
        ? 'Entre para assistir esta live exclusiva.'
        : ($requiresSubscription ? 'Esta live e exclusiva para assinantes ativos.' : 'A live ainda nao esta disponivel.'));

if ($canWatch && $hasReplay && (string) ($stream['status'] ?? 'idle') !== 'live') {
    $accessMessage = 'Replay pronto para assistir enquanto a live estiver offline.';
}
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Live Room</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,700;0,800;1,800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <style>
        body { background-color: #fbf9fb; background-image: radial-gradient(circle at 10% 20%, rgba(216, 27, 96, 0.03) 0%, transparent 50%), radial-gradient(circle at 90% 80%, rgba(171, 17, 85, 0.03) 0%, transparent 50%); color: #1b1c1d; font-family: Manrope, sans-serif; }
        .headline { font-family: "Plus Jakarta Sans", sans-serif; }
        .signature-glow { background: linear-gradient(135deg, #ab1155 0%, #cc326e 100%); }
        .lunar-glass { background: rgba(251, 249, 251, 0.72); backdrop-filter: blur(24px); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(171, 17, 85, 0.2); border-radius: 999px; }
    </style>
</head>
<body class="antialiased">
<nav class="fixed top-0 z-50 flex h-20 w-full items-center justify-between bg-[#D81B60] px-8 text-white shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
    <div class="flex items-center gap-12">
        <a class="text-2xl font-black italic tracking-tighter" href="/">SexyLua</a>
        <div class="hidden items-center gap-8 md:flex">
            <a class="border-b-2 border-white pb-1 text-sm font-bold uppercase tracking-wide" href="<?= e(path_with_query('/live', ['id' => (int) ($live['id'] ?? 0)])) ?>">Live Cam</a>
            <a class="text-sm font-bold uppercase tracking-wide text-white/80 transition-colors hover:text-white" href="/explore">Explorar</a>
            <a class="text-sm font-bold uppercase tracking-wide text-white/80 transition-colors hover:text-white" href="<?= e($subscriptionsUrl) ?>">Assinaturas</a>
            <a class="text-sm font-bold uppercase tracking-wide text-white/80 transition-colors hover:text-white" href="<?= e($messagesUrl) ?>">Mensagens</a>
        </div>
    </div>
    <div class="flex items-center gap-4">
        <?php if ($authUser === null): ?>
            <a class="rounded-full px-6 py-2 text-sm font-bold uppercase tracking-widest text-white transition-transform hover:scale-105" href="/login">Entrar</a>
            <a class="rounded-full bg-white px-6 py-2 text-sm font-bold uppercase tracking-widest text-[#ab1155] shadow-lg transition-transform hover:scale-105" href="/register">Registro</a>
        <?php else: ?>
            <a class="rounded-full border border-white/20 px-6 py-2 text-sm font-bold uppercase tracking-widest" href="<?= e(($authUser['role'] ?? '') === 'creator' ? '/creator' : (($authUser['role'] ?? '') === 'admin' ? '/admin' : '/subscriber')) ?>">Painel</a>
            <form action="/logout" method="post"><input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>"><button class="rounded-full bg-white/10 px-6 py-2 text-sm font-bold uppercase tracking-widest text-white" data-prototype-skip="1" type="submit">Sair</button></form>
        <?php endif; ?>
    </div>
</nav>

<main class="max-w-7xl mx-auto px-4 pb-20 pt-24 md:px-8">
    <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <section class="space-y-6 lg:col-span-8">
            <div
                class="overflow-hidden rounded-[2rem] bg-white shadow-2xl"
                data-live-rtc-mode="viewer"
                data-live-id="<?= e((string) ((int) ($live['id'] ?? 0))) ?>"
                data-csrf="<?= e($app->csrf->token()) ?>"
                data-can-watch="<?= $canWatch ? '1' : '0' ?>"
                data-access-message="<?= e($accessMessage) ?>"
                data-join-url="/live/rtc/join"
                data-signal-url="/live/rtc/signal"
                data-poll-url="/live/rtc/poll"
                data-heartbeat-url="/live/rtc/heartbeat"
                data-leave-url="/live/rtc/leave"
                data-replay-url="<?= e($replayUrl) ?>"
                data-replay-enabled="<?= $hasReplay ? '1' : '0' ?>"
                data-ice-servers="<?= e($iceServers) ?>"
                data-ice-transport-policy="<?= e($iceTransportPolicy) ?>"
            >
                <div class="relative aspect-video bg-slate-950">
                    <video autoplay class="h-full w-full bg-slate-950 object-cover" controls data-live-remote-video playsinline></video>
                    <?php if ($cover !== ''): ?><img alt="Capa da live" class="absolute inset-0 h-full w-full object-cover opacity-25" src="<?= e($cover) ?>"><?php endif; ?>
                    <div class="absolute left-6 right-6 top-6 flex items-start justify-between gap-4 text-white">
                        <div class="flex items-center gap-3">
                            <span class="signature-glow rounded-full px-4 py-2 text-[10px] font-bold uppercase tracking-[0.25em]" data-live-status-text><?= e((string) ($stream['status'] ?? 'idle')) ?></span>
                            <span class="rounded-full bg-black/35 px-4 py-2 text-[10px] font-bold uppercase tracking-[0.25em]"><span data-live-viewer-count><?= e((string) $viewerCount) ?></span> viewers</span>
                        </div>
                        <span class="rounded-full bg-black/35 px-4 py-2 text-[10px] font-bold uppercase tracking-[0.25em]" data-live-stream-state><?= e((string) ($stream['stream_mode'] ?? 'p2p_mesh')) ?></span>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center bg-black/55 px-6 text-center text-white" data-live-waiting>
                        <div class="max-w-md">
                            <p class="headline text-4xl font-extrabold"><?= e((string) ($live['title'] ?? 'Live')) ?></p>
                            <p class="mt-3 text-sm text-white/75" data-live-waiting-text><?= e($accessMessage !== '' ? $accessMessage : 'Aguardando o criador iniciar a transmissao.') ?></p>
                            <button class="mt-5 hidden rounded-full bg-white px-6 py-3 text-sm font-bold uppercase tracking-widest text-[#ab1155]" data-live-playback data-prototype-skip="1" type="button">Ativar playback</button>
                        </div>
                    </div>
                </div>
                <div class="space-y-5 p-6">
                    <div class="hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800" data-live-error></div>
                    <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center gap-5">
                            <div class="h-20 w-20 rounded-full border-2 border-[#ab1155] p-1">
                                <?php if ((string) ($creator['avatar_url'] ?? '') !== ''): ?>
                                    <img alt="<?= e((string) ($creator['name'] ?? 'Criador')) ?>" class="h-full w-full rounded-full object-cover" src="<?= e(media_url((string) ($creator['avatar_url'] ?? ''))) ?>">
                                <?php else: ?>
                                    <div class="signature-glow flex h-full w-full items-center justify-center rounded-full text-lg font-bold text-white"><?= e(avatar_initials((string) ($creator['name'] ?? 'Criador'))) ?></div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h1 class="headline text-3xl font-extrabold tracking-tight"><?= e((string) ($creator['name'] ?? 'Criador')) ?></h1>
                                <p class="mt-1 text-on-surface-variant"><?= e((string) ($creator['headline'] ?? 'Criando experiencias exclusivas ao vivo.')) ?></p>
                                <a class="mt-3 inline-block text-sm font-bold text-[#ab1155] underline" href="<?= e($profileUrl) ?>">Abrir perfil</a>
                            </div>
                        </div>
                        <div class="flex w-full flex-col gap-3 md:w-auto">
                            <?php if ($canTip): ?>
                                <form action="/tip" class="flex flex-col gap-3 sm:flex-row" method="post">
                                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                    <input name="creator_id" type="hidden" value="<?= e((string) ((int) ($creator['id'] ?? 0))) ?>">
                                    <input name="live_id" type="hidden" value="<?= e((string) ((int) ($live['id'] ?? 0))) ?>">
                                    <input class="rounded-full border-none bg-surface-container-high px-5 py-3 text-sm text-on-surface shadow-sm focus:ring-2 focus:ring-[#ab1155]/20" min="1" name="amount" placeholder="LuaCoins" required type="number" value="25">
                                    <button class="signature-glow rounded-full px-8 py-3 text-sm font-bold text-white shadow-lg" data-prototype-skip="1" type="submit">Enviar gorjeta</button>
                                </form>
                            <?php elseif ($requiresLogin): ?>
                                <a class="signature-glow rounded-full px-8 py-3 text-center text-sm font-bold text-white shadow-lg" href="/login">Entrar para assistir</a>
                            <?php elseif ($requiresSubscription): ?>
                                <a class="signature-glow rounded-full px-8 py-3 text-center text-sm font-bold text-white shadow-lg" href="<?= e($profileUrl) ?>">Assinar para liberar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div class="rounded-2xl bg-surface-container-low p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Categoria</p>
                            <p class="mt-2 text-sm font-bold text-slate-700"><?= e((string) ($live['category'] ?? 'Studio')) ?></p>
                        </div>
                        <div class="rounded-2xl bg-surface-container-low p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Acesso</p>
                            <p class="mt-2 text-sm font-bold text-slate-700"><?= e((string) ($live['access_mode'] ?? 'public')) ?></p>
                        </div>
                        <div class="rounded-2xl bg-surface-container-low p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Bitrate</p>
                            <p class="mt-2 text-sm font-bold text-slate-700"><?= e((string) ($live['max_bitrate_kbps'] ?? 1500)) ?> kbps</p>
                        </div>
                        <div class="rounded-2xl bg-surface-container-low p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Meta</p>
                            <p class="mt-2 text-sm font-bold text-slate-700"><?= e(token_amount((int) ($live['goal_tokens'] ?? 0))) ?></p>
                        </div>
                    </div>
                    <?php if ($hasReplay): ?>
                        <div class="rounded-2xl bg-surface-container-low p-4">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Replay disponivel</p>
                                    <p class="mt-2 text-sm font-bold text-slate-700"><?= e($replayDuration > 0 ? gmdate('H:i:s', $replayDuration) : 'Duracao em processamento') ?></p>
                                </div>
                                <a class="rounded-full bg-[#D81B60]/10 px-5 py-3 text-center text-sm font-bold text-[#ab1155]" href="<?= e($replayUrl) ?>" target="_blank">Abrir replay</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <section class="rounded-[2rem] bg-white p-6 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="headline text-2xl font-extrabold">Outras lives em destaque</h2>
                        <p class="mt-2 text-sm text-on-surface-variant">Criadores que estao brilhando agora.</p>
                    </div>
                    <a class="text-sm font-bold text-[#ab1155] underline" href="/explore">Ver mais</a>
                </div>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <?php foreach (array_slice($relatedLives, 0, 6) as $item): ?>
                        <?php $itemCover = media_url((string) ($item['cover_url'] ?? '')); ?>
                        <a class="overflow-hidden rounded-3xl bg-surface-container-low transition-transform hover:-translate-y-1" href="<?= e(path_with_query('/live', ['id' => (int) ($item['id'] ?? 0)])) ?>">
                            <div class="aspect-[4/3] bg-slate-900">
                                <?php if ($itemCover !== ''): ?><img alt="<?= e((string) ($item['title'] ?? 'Live')) ?>" class="h-full w-full object-cover" src="<?= e($itemCover) ?>"><?php else: ?><div class="signature-glow flex h-full w-full items-center justify-center text-white"><span class="headline text-xl font-extrabold">LIVE</span></div><?php endif; ?>
                            </div>
                            <div class="space-y-2 p-4">
                                <p class="headline text-lg font-extrabold"><?= e((string) ($item['title'] ?? 'Live')) ?></p>
                                <p class="text-sm text-on-surface-variant"><?= e((string) ($item['creator']['name'] ?? 'Criador')) ?></p>
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400"><?= e((string) ($item['stream_status'] ?? $item['status'] ?? 'idle')) ?> • <?= e((string) ($item['viewer_count'] ?? 0)) ?> viewers</p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    <?php if ($relatedLives === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma outra live em destaque agora.</p><?php endif; ?>
                </div>
            </section>
        </section>

        <aside class="custom-scrollbar flex h-[760px] flex-col overflow-hidden rounded-[2rem] bg-surface-container-low shadow-sm lg:col-span-4">
            <div class="border-b border-slate-200/70 bg-surface-container-high px-5 py-4">
                <div class="flex items-center justify-between">
                    <span class="headline text-sm font-bold uppercase tracking-[0.2em]">Chat da Lua</span>
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-[#ab1155]"><span data-live-viewer-count><?= e((string) $viewerCount) ?></span> online</span>
                </div>
            </div>
            <div class="space-y-4 border-b border-slate-200/70 px-5 py-4">
                <div>
                    <h4 class="text-[10px] font-bold uppercase tracking-[0.25em] text-on-surface-variant">Top supporters</h4>
                    <div class="mt-3 flex gap-4">
                        <?php foreach (array_slice($topSupporters, 0, 3) as $index => $supporter): ?>
                            <div class="flex flex-col items-center">
                                <div class="signature-glow flex h-12 w-12 items-center justify-center rounded-full text-sm font-bold text-white"><?= e(avatar_initials((string) ($supporter['user']['name'] ?? 'Fan'))) ?></div>
                                <span class="mt-2 text-[10px] font-bold text-[#ab1155]"><?= e((string) ($supporter['user']['name'] ?? 'Fan')) ?></span>
                                <span class="text-[10px] text-slate-500"><?= e(token_amount((int) ($supporter['amount'] ?? 0))) ?></span>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($topSupporters === []): ?><p class="text-sm text-slate-500">Sem ranking ainda.</p><?php endif; ?>
                    </div>
                </div>
                <div>
                    <h4 class="text-[10px] font-bold uppercase tracking-[0.25em] text-on-surface-variant">Doacoes recentes</h4>
                    <div class="mt-3 space-y-2">
                        <?php foreach (array_slice($recentTips, 0, 4) as $tip): ?>
                            <div class="flex items-center justify-between rounded-full bg-white px-4 py-2 text-xs">
                                <span class="font-bold text-on-surface"><?= e((string) ($tip['sender']['name'] ?? 'Fan')) ?></span>
                                <span class="font-black text-[#ab1155]"><?= e(token_amount((int) ($tip['amount'] ?? 0))) ?></span>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($recentTips === []): ?><p class="text-sm text-slate-500">Sem gorjetas recentes.</p><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="custom-scrollbar flex-1 space-y-4 overflow-y-auto px-5 py-5">
                <?php foreach ($messages as $message): ?>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-bold tracking-wide text-[#ab1155]"><?= e((string) ($message['sender']['name'] ?? 'Visitante')) ?></span>
                        <p class="rounded-2xl rounded-tl-none bg-white p-3 text-sm text-on-surface-variant shadow-sm"><?= e((string) ($message['body'] ?? '')) ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if ($messages === []): ?><p class="text-sm text-slate-500">Ainda nao ha mensagens nesta live.</p><?php endif; ?>
            </div>
            <div class="border-t border-slate-200/70 bg-white p-4">
                <?php if ($canChat): ?>
                    <form action="/live/chat" class="space-y-3" method="post">
                        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                        <input name="live_id" type="hidden" value="<?= e((string) ((int) ($live['id'] ?? 0))) ?>">
                        <textarea class="min-h-[110px] w-full rounded-3xl border-none bg-surface-container-low px-5 py-4 text-sm shadow-sm focus:ring-2 focus:ring-[#ab1155]/20" name="body" placeholder="Diga algo no chat..." required></textarea>
                        <button class="signature-glow w-full rounded-full px-5 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Mandar no chat</button>
                    </form>
                <?php elseif ($requiresLogin): ?>
                    <a class="signature-glow block w-full rounded-full px-5 py-4 text-center text-sm font-bold text-white" href="/login">Entrar para falar no chat</a>
                <?php elseif ($requiresSubscription): ?>
                    <a class="signature-glow block w-full rounded-full px-5 py-4 text-center text-sm font-bold text-white" href="<?= e($profileUrl) ?>">Assinar para falar no chat</a>
                <?php else: ?>
                    <p class="text-sm text-slate-500">O chat esta fechado nesta live.</p>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</main>

<script src="<?= e(asset('js/live-rtc.js')) ?>"></script>
</body>
</html>
