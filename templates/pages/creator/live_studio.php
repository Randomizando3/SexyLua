<?php

declare(strict_types=1);

$creator = $data['creator'] ?? [];
$selected = $data['selected_live'] ?? null;
$messages = $data['messages'] ?? [];
$recentTips = $data['recent_tips'] ?? [];
$topSupporters = $data['top_supporters'] ?? [];

$selectedLiveId = (int) ($selected['id'] ?? 0);
$selectedStatus = (string) ($selected['status'] ?? 'scheduled');
$selectedStatusLabel = match ($selectedStatus) {
    'live' => 'Ao vivo',
    'ended' => 'Concluída',
    'expired' => 'Expirada',
    default => 'Agendada',
};
$selectedScheduleLabel = $selected ? format_datetime((string) ($selected['scheduled_for'] ?? '')) : '';
$selectedStartedAt = $selected ? format_datetime((string) ($selected['started_at'] ?? '')) : '';
$selectedEndedAt = $selected ? format_datetime((string) ($selected['ended_at'] ?? '')) : '';
$selectedLiveDuration = (int) ($selected['duration_seconds'] ?? 0);
$selectedDurationLabel = $selectedLiveDuration > 0 ? gmdate($selectedLiveDuration >= 3600 ? 'H:i:s' : 'i:s', $selectedLiveDuration) : '00:00';
$selectedRoomUrl = $selectedLiveId > 0 ? path_with_query('/live', ['id' => $selectedLiveId]) : '';
$selectedCover = media_url((string) ($selected['cover_url'] ?? ''));
$viewerCount = (int) ($selected['viewer_count'] ?? 0);
$priorityTiers = is_array($selected['priority_tip_tiers'] ?? null) ? array_values($selected['priority_tip_tiers']) : [1, 10, 25, 50, 100, 150];
$selectedChatAudience = (string) ($selected['chat_audience'] ?? 'all');
$selectedMaxDurationMinutes = max(5, (int) ($selected['max_live_duration_minutes'] ?? 30));
$selectedStatusBucket = (string) ($selected['status_bucket'] ?? 'scheduled');
$backUrl = path_with_query('/creator/live', ['status' => $selectedStatusBucket, 'live' => $selectedLiveId > 0 ? $selectedLiveId : null]);
$editLiveUrl = $selectedLiveId > 0
    ? path_with_query('/creator/live', ['status' => $selectedStatusBucket, 'live' => $selectedLiveId, 'open_form' => 1, 'form_mode' => 'edit'])
    : '/creator/live';
$canBroadcast = $selected !== null && ! in_array($selectedStatus, ['ended', 'expired'], true);
$ingestUrl = (string) ($selected['ingest_url'] ?? '');
$hlsUrl = (string) ($selected['hls_url'] ?? '');
$ingestServer = $ingestUrl !== '' ? preg_replace('~/[^/]+$~', '', $ingestUrl) : '';
$streamKey = $ingestUrl !== '' ? basename(parse_url($ingestUrl, PHP_URL_PATH) ?: '') : (string) ($selected['stream_key'] ?? '');
$audioSampleRate = (int) ($selected['audio_sample_rate'] ?? 48000);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SexyLua - Estúdio da Live</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style>
        body { font-family: Manrope, sans-serif; background: #fbf9fb; color: #1b1c1d; }
        .headline { font-family: "Plus Jakarta Sans", sans-serif; }
        .signature-glow { background: linear-gradient(135deg, #D81B60 0%, #ab1155 100%); }
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
    </style>
</head>
<body>
<?php
$creatorTopbarSearch = '';
$creatorShellCreator = $creator;
$creatorShellCurrent = 'live';
$creatorTopbarLabel = 'Estúdio';
$creatorTopbarAction = ['href' => '/creator/live', 'label' => 'Minhas lives'];
include base_path('templates/partials/creator_sidebar.php');
include base_path('templates/partials/creator_topbar.php');
?>

<main class="px-6 pb-12 pt-24 lg:ml-64 lg:px-10">
    <?php if ($selected === null): ?>
        <section class="mt-8 rounded-3xl bg-white p-10 text-center shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
            <h2 class="headline text-3xl font-extrabold">Nenhuma live selecionada</h2>
            <p class="mt-3 text-slate-500">Volte para a agenda, escolha uma live e abra o estúdio por lá.</p>
            <a class="signature-glow mt-6 inline-flex rounded-full px-6 py-3 text-sm font-bold text-white" href="/creator/live">Ir para minhas lives</a>
        </section>
    <?php elseif (! $canBroadcast): ?>
        <section class="mt-8 rounded-3xl bg-white p-10 text-center shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
            <h2 class="headline text-3xl font-extrabold"><?= $selectedStatus === 'ended' ? 'Esta live já foi encerrada' : 'Esta live expirou' ?></h2>
            <p class="mt-3 text-slate-500"><?= $selectedStatus === 'ended' ? 'O estúdio não fica disponível para lives concluídas. Use a agenda para ver o resumo da transmissão.' : 'Reagende ou edite a live antes de tentar abrir o estúdio novamente.' ?></p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a class="rounded-full bg-[#f7f4f7] px-6 py-3 text-sm font-bold text-slate-600" href="<?= e($backUrl) ?>">Voltar para agenda</a>
                <a class="signature-glow rounded-full px-6 py-3 text-sm font-bold text-white" href="<?= e($editLiveUrl) ?>">Editar live</a>
            </div>
        </section>
    <?php else: ?>
        <div
            class="mt-8 space-y-6"
            data-live-rtc-mode="creator"
            data-live-id="<?= e((string) $selectedLiveId) ?>"
            data-csrf="<?= e($app->csrf->token()) ?>"
            data-can-broadcast="<?= $canBroadcast ? '1' : '0' ?>"
            data-join-url="/live/rtc/join"
            data-start-url="/live/rtc/start"
            data-stop-url="/live/rtc/stop"
            data-poll-url="/live/rtc/poll"
            data-heartbeat-url="/live/rtc/heartbeat"
            data-leave-url="/live/rtc/leave"
            data-hls-url="<?= e($hlsUrl) ?>"
            data-max-bitrate-kbps="<?= e((string) ((int) ($selected['max_bitrate_kbps'] ?? 800))) ?>"
            data-video-width="<?= e((string) ((int) ($selected['video_width'] ?? 854))) ?>"
            data-video-height="<?= e((string) ((int) ($selected['video_height'] ?? 480))) ?>"
            data-video-fps="<?= e((string) ((int) ($selected['video_fps'] ?? 30))) ?>"
            data-segment-duration-ms="<?= e((string) (((int) ($selected['segment_duration_seconds'] ?? 10)) * 1000)) ?>"
            data-max-duration-seconds="<?= e((string) ($selectedMaxDurationMinutes * 60)) ?>"
        >
            <section class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
                <div class="rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                    <a class="inline-flex items-center gap-2 text-sm font-bold text-[#D81B60]" href="<?= e($backUrl) ?>">
                        <span class="material-symbols-outlined text-base">arrow_back</span>
                        Voltar para agenda
                    </a>
                    <p class="mt-4 text-xs font-bold uppercase tracking-[0.3em] text-[#D81B60]">Estúdio da live</p>
                    <h1 class="headline mt-2 text-3xl font-extrabold"><?= e((string) ($selected['title'] ?? 'Estúdio')) ?></h1>
                    <p class="mt-3 max-w-3xl text-sm text-slate-500">Agora a transmissão entra por RTMP no MediaMTX e o público assiste por HLS, o que deixa a reprodução bem mais estável no celular e em tela cheia.</p>
                </div>

                <div class="rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]">Sala pública</p>
                    <a class="mt-3 block break-all text-sm font-bold text-[#D81B60] underline" data-live-room-link href="<?= e($selectedRoomUrl) ?>" target="_blank"><?= e($selectedRoomUrl) ?></a>
                    <p class="mt-3 text-sm text-slate-500">O chat, as gorjetas e as métricas continuam no SexyLua. O vídeo passa a vir do MediaMTX.</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a class="rounded-full bg-[#f7f4f7] px-5 py-3 text-sm font-bold text-slate-600" href="<?= e($editLiveUrl) ?>">Editar dados</a>
                        <a class="rounded-full bg-slate-900 px-5 py-3 text-sm font-bold text-white" href="<?= e($selectedRoomUrl) ?>" target="_blank">Abrir sala pública</a>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(340px,1fr)] xl:items-stretch">
                <section class="overflow-hidden rounded-3xl bg-white shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                    <div class="hidden border-b border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-800" data-live-error></div>
                    <div class="relative h-[340px] bg-slate-950 md:h-[420px] xl:h-[480px]">
                        <iframe
                            allow="autoplay; fullscreen; picture-in-picture"
                            class="absolute inset-0 hidden h-full w-full border-0 bg-slate-950"
                            data-live-local-embed
                            loading="eager"
                            referrerpolicy="same-origin"
                            title="Preview da live"
                        ></iframe>
                        <video class="h-full w-full object-cover" controls data-live-local-video muted playsinline></video>
                        <?php if ($selectedCover !== ''): ?><img alt="Capa da live" class="absolute inset-0 h-full w-full object-cover opacity-20" src="<?= e($selectedCover) ?>"><?php endif; ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-black/35"></div>
                        <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-4 p-6 text-white">
                            <div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="<?= $selectedStatus === 'live' ? 'bg-emerald-500' : 'bg-[#D81B60]' ?> rounded-full px-4 py-2 text-[10px] font-bold uppercase tracking-[0.25em]" data-live-status-text><?= e($selectedStatus === 'live' ? 'ao vivo' : 'agendada') ?></span>
                                    <span class="rounded-full bg-white/15 px-4 py-2 text-[10px] font-bold uppercase tracking-[0.25em]" data-live-stream-state><?= e($selectedStatusLabel) ?></span>
                                </div>
                                <p class="headline mt-5 text-3xl font-extrabold"><?= e((string) ($selected['title'] ?? 'Live')) ?></p>
                                <p class="mt-2 max-w-2xl text-sm text-white/80"><?= e((string) ($selected['description'] ?? '')) ?></p>
                            </div>
                            <span class="rounded-full bg-white/15 px-4 py-2 text-[10px] font-bold uppercase tracking-[0.25em]"><span data-live-viewer-count><?= e((string) $viewerCount) ?></span> viewers</span>
                        </div>
                        <div class="absolute inset-0 flex items-center justify-center bg-black/45 px-6 text-center text-white" data-live-waiting>
                            <div class="max-w-xl">
                                <p class="headline text-3xl font-extrabold">Aguardando ingest</p>
                                <p class="mt-3 text-sm text-white/75" data-live-waiting-text>Inicie sua transmissão no OBS usando o servidor RTMP e a chave abaixo. Assim que o MediaMTX detectar o sinal, o preview aparece aqui.</p>
                            </div>
                        </div>
                        <div class="absolute inset-x-0 bottom-0 flex flex-wrap items-center justify-between gap-3 p-5">
                            <div class="flex flex-wrap items-center gap-3 rounded-full bg-black/45 px-4 py-3 text-white backdrop-blur-md">
                                <span class="text-xs font-bold uppercase tracking-[0.25em]">RTMP estável</span>
                                <span class="rounded-full bg-white/15 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em]">854x480</span>
                                <span class="rounded-full bg-white/15 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em]">800 kbps</span>
                                <span class="rounded-full bg-white/15 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em]">30 fps</span>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button class="<?= $selectedStatus === 'live' ? 'hidden ' : '' ?>signature-glow inline-flex h-12 items-center justify-center rounded-full px-5 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-50" data-live-start data-prototype-skip="1" type="button">Começar live</button>
                                <button class="<?= $selectedStatus === 'live' ? '' : 'hidden ' ?>inline-flex h-12 items-center justify-center rounded-full bg-slate-900 px-5 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-50" data-live-stop data-prototype-skip="1" type="button">Encerrar live</button>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="grid min-h-0 grid-cols-1 gap-6 xl:h-[480px] xl:grid-rows-[minmax(0,1fr)_minmax(0,1fr)]">
                    <section class="flex min-h-0 flex-col rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="headline text-xl font-extrabold">Chat da live</h3>
                                <p class="mt-1 text-sm text-slate-500">As últimas mensagens da sala aparecem aqui em tempo real.</p>
                            </div>
                            <span class="rounded-full bg-[#f7f4f7] px-4 py-2 text-[10px] font-bold uppercase tracking-[0.25em] text-slate-500"><span data-live-viewer-count><?= e((string) $viewerCount) ?></span> online</span>
                        </div>
                        <div class="mt-5 min-h-0 flex-1 space-y-3 overflow-y-auto pr-1" data-live-chat-stream data-live-chat-variant="creator">
                            <?php foreach ($messages as $message): ?>
                                <?php
                                $theme = $message['highlight_theme'] ?? [];
                                $isHighlighted = (bool) ($message['is_highlighted'] ?? false);
                                ?>
                                <div class="rounded-2xl border p-4 text-sm" style="<?= $isHighlighted ? 'background:' . e((string) ($theme['background'] ?? '#fff6cf')) . ';border-color:' . e((string) ($theme['border'] ?? '#fde68a')) . ';' : 'background:#f5f3f5;border-color:transparent;' ?>">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="block text-[10px] font-bold uppercase tracking-widest text-[#D81B60]"><?= e((string) ($message['sender']['name'] ?? 'Convidado')) ?></span>
                                        <?php if ($isHighlighted): ?>
                                            <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em]" style="background:<?= e((string) ($theme['label_background'] ?? '#f59e0b')) ?>;color:<?= e((string) ($theme['label_text'] ?? '#ffffff')) ?>"><?= e((string) ($message['highlight_label'] ?? 'Destaque')) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mt-2"><?= e((string) ($message['body'] ?? '')) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="<?= $messages === [] ? '' : 'hidden ' ?>mt-4 text-sm text-slate-500" data-live-chat-empty>Sem mensagens para esta live ainda.</p>
                    </section>

                    <section class="flex min-h-0 flex-col rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                        <div>
                            <h3 class="headline text-xl font-extrabold">Apoio da sala</h3>
                            <p class="mt-1 text-sm text-slate-500">Acompanhe os maiores apoiadores e as gorjetas mais recentes.</p>
                        </div>

                        <div class="mt-5 min-h-0 flex-1 overflow-y-auto pr-1">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Top supporters</p>
                                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2" data-live-top-supporters data-live-supporters-variant="creator">
                                    <?php foreach ($topSupporters as $supporter): ?>
                                        <div class="rounded-2xl bg-[#f5f3f5] p-4 text-center">
                                            <div class="signature-glow mx-auto flex h-12 w-12 items-center justify-center rounded-full text-sm font-bold text-white"><?= e(avatar_initials((string) ($supporter['user']['name'] ?? 'Fan'))) ?></div>
                                            <p class="mt-3 text-sm font-bold text-slate-800"><?= e((string) ($supporter['user']['name'] ?? 'Fan')) ?></p>
                                            <p class="mt-1 text-xs font-semibold text-[#D81B60]"><?= luacoin_amount_html((int) ($supporter['amount'] ?? 0), 'inline-flex items-center gap-1 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <p class="<?= $topSupporters === [] ? '' : 'hidden ' ?>mt-3 text-sm text-slate-500" data-live-top-supporters-empty>Sem apoiadores ainda.</p>
                            </div>

                            <div class="mt-6">
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Gorjetas recentes</p>
                                <div class="mt-3 space-y-3" data-live-recent-tips data-live-tips-variant="creator">
                                    <?php foreach ($recentTips as $tip): ?>
                                        <div class="flex items-center justify-between rounded-2xl bg-[#f5f3f5] px-4 py-3 text-sm">
                                            <span class="font-bold text-slate-700"><?= e((string) ($tip['sender']['name'] ?? 'Fan')) ?></span>
                                            <span class="font-black text-[#D81B60]"><?= luacoin_amount_html((int) ($tip['amount'] ?? 0), 'inline-flex items-center gap-1 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <p class="<?= $recentTips === [] ? '' : 'hidden ' ?>mt-3 text-sm text-slate-500" data-live-recent-tips-empty>Sem gorjetas recentes.</p>
                            </div>
                        </div>
                    </section>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
                <section class="rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]">Configurações da sala</p>
                            <h2 class="headline mt-2 text-2xl font-extrabold"><?= e((string) ($selected['title'] ?? 'Live')) ?></h2>
                        </div>
                        <span class="<?= $selectedStatus === 'live' ? 'bg-emerald-500 text-white' : 'bg-[#f7f4f7] text-slate-600' ?> rounded-full px-4 py-2 text-[10px] font-bold uppercase tracking-[0.25em]"><?= e($selectedStatusLabel) ?></span>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-2xl bg-[#f5f3f5] p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Agendada</p>
                            <p class="mt-2 text-sm font-bold text-slate-700"><?= e($selectedScheduleLabel !== '' ? $selectedScheduleLabel : 'Sem agenda') ?></p>
                        </div>
                        <div class="rounded-2xl bg-[#f5f3f5] p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Iniciada em</p>
                            <p class="mt-2 text-sm font-bold text-slate-700" data-live-started-at><?= e($selectedStartedAt !== '' ? $selectedStartedAt : 'Ainda não iniciou') ?></p>
                        </div>
                        <div class="rounded-2xl bg-[#f5f3f5] p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Duração</p>
                            <p class="mt-2 text-sm font-bold text-slate-700" data-live-elapsed><?= e($selectedDurationLabel) ?></p>
                        </div>
                        <div class="rounded-2xl bg-[#f5f3f5] p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Último encerramento</p>
                            <p class="mt-2 text-sm font-bold text-slate-700"><?= e($selectedEndedAt !== '' ? $selectedEndedAt : 'Sem histórico') ?></p>
                        </div>
                    </div>

                    <form action="/creator/live/studio" class="mt-6 space-y-4" method="post">
                        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                        <input name="live_id" type="hidden" value="<?= e((string) $selectedLiveId) ?>">

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label class="block space-y-2">
                                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Acesso da sala</span>
                                <select class="rounded-2xl border-none bg-[#f5f3f5] px-4 py-3 font-semibold text-slate-700" name="access_mode">
                                    <option value="public" <?= (string) ($selected['access_mode'] ?? 'public') === 'public' ? 'selected' : '' ?>>Público</option>
                                    <option value="subscriber" <?= (string) ($selected['access_mode'] ?? '') === 'subscriber' ? 'selected' : '' ?>>Assinantes</option>
                                    <option value="vip" <?= (string) ($selected['access_mode'] ?? '') === 'vip' ? 'selected' : '' ?>>Live VIP</option>
                                </select>
                            </label>
                            <label class="block space-y-2">
                                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Valor da Live VIP</span>
                                <input class="rounded-2xl border-none bg-[#f5f3f5] px-4 py-3 font-semibold text-slate-700" min="0" name="price_luacoins" placeholder="Ex.: 25" type="number" value="<?= e((string) ((int) ($selected['price_tokens'] ?? 0) > 0 ? (int) ($selected['price_tokens'] ?? 0) : '')) ?>">
                            </label>
                            <label class="block space-y-2">
                                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Quem pode falar</span>
                                <select class="rounded-2xl border-none bg-[#f5f3f5] px-4 py-3 font-semibold text-slate-700" name="chat_audience">
                                    <option value="all" <?= $selectedChatAudience === 'all' ? 'selected' : '' ?>>Assinantes e não assinantes</option>
                                    <option value="subscriber" <?= $selectedChatAudience === 'subscriber' ? 'selected' : '' ?>>Só assinantes</option>
                                    <option value="off" <?= $selectedChatAudience === 'off' ? 'selected' : '' ?>>Chat desabilitado</option>
                                </select>
                            </label>
                            <div class="rounded-2xl bg-[#f5f3f5] px-4 py-3 text-sm text-slate-500">As métricas, gorjetas e o chat permanecem ao vivo mesmo com o vídeo entrando pelo MediaMTX.</div>
                            <div class="rounded-2xl bg-[#f5f3f5] px-4 py-3 text-sm text-slate-500">A gravação automática da live está desabilitada neste ambiente para preservar espaço na VPS.</div>
                        </div>

                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Valor para tornar a sala DarkRoom</span>
                            <input class="rounded-2xl border-none bg-[#f5f3f5] px-4 py-3 font-semibold text-slate-700" min="0" name="darkroom_price_luacoins" placeholder="Ex.: 80" type="number" value="<?= e((string) ((int) ($selected['darkroom_price_tokens'] ?? 0) > 0 ? (int) ($selected['darkroom_price_tokens'] ?? 0) : '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Duração da Sala DarkRoom</span>
                            <input class="rounded-2xl border-none bg-[#f5f3f5] px-4 py-3 font-semibold text-slate-700" min="0" name="darkroom_duration_minutes" placeholder="Ex.: 5" type="number" value="<?= e((string) ((int) ($selected['darkroom_duration_minutes'] ?? 0) > 0 ? (int) ($selected['darkroom_duration_minutes'] ?? 0) : '')) ?>">
                        </label>
                        <div class="rounded-2xl bg-[#f5f3f5] px-4 py-3 text-sm text-slate-500">Quando o darkroom estiver ativo, a live fica exclusiva para quem pagou durante o periodo configurado.</div>
                        <div class="flex flex-wrap gap-3">
                            <button class="rounded-full bg-slate-900 px-5 py-3 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Salvar sala</button>
                        </div>
                    </form>
                </section>

                <section class="rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]">Ingest do MediaMTX</p>
                    <h3 class="headline mt-2 text-2xl font-extrabold">Servidor RTMP e chave</h3>
                    <p class="mt-3 text-sm text-slate-500">Use estes dados no OBS ou no seu encoder. O público assiste por HLS, mas a entrada é RTMP.</p>

                    <div class="mt-6 space-y-4">
                        <div class="rounded-2xl bg-[#f5f3f5] p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Servidor</p>
                            <p class="mt-2 break-all text-sm font-bold text-slate-700"><?= e($ingestServer) ?></p>
                        </div>
                        <div class="rounded-2xl bg-[#f5f3f5] p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Chave de stream</p>
                            <p class="mt-2 break-all text-sm font-bold text-slate-700"><?= e($streamKey) ?></p>
                        </div>
                        <div class="rounded-2xl bg-[#f5f3f5] p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Padrão recomendado</p>
                            <ul class="mt-3 space-y-2 text-sm text-slate-700">
                                <li>Resolução: <?= e((string) ((int) ($selected['video_width'] ?? 854))) ?>x<?= e((string) ((int) ($selected['video_height'] ?? 480))) ?></li>
                                <li>Vídeo: <?= e((string) ((int) ($selected['max_bitrate_kbps'] ?? 800))) ?> kbps</li>
                                <li>FPS: <?= e((string) ((int) ($selected['video_fps'] ?? 30))) ?></li>
                                <li>Keyframe / GOP: <?= e((string) ((int) ($selected['video_gop_seconds'] ?? 2))) ?>s</li>
                                <li>Áudio AAC: <?= e((string) ((int) ($selected['audio_bitrate_kbps'] ?? 96))) ?> kbps, <?= e($audioSampleRate === 44100 ? '44.1' : '48') ?> kHz</li>
                            </ul>
                        </div>
                    </div>
                </section>
            </section>
        </div>
    <?php endif; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
<script src="<?= e(asset('js/live-segment.js')) ?>"></script>
<script>
    document.querySelectorAll('div.rounded-2xl.bg-\\[\\#f5f3f5\\].px-4.py-3.text-sm.text-slate-500').forEach((node) => {
        if (!node.textContent || !node.textContent.toLowerCase().includes('grava')) return;
        node.textContent = 'O foco deste estudio esta na transmissao ao vivo e no acompanhamento do chat em tempo real.'
    })
</script>
</body>
</html>
