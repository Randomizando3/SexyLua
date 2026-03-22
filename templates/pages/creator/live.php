<?php

declare(strict_types=1);

$creator = $data['creator'] ?? [];
$lives = $data['filtered_lives'] ?? $data['lives'] ?? [];
$selected = $data['selected_live'] ?? null;
$messages = $data['messages'] ?? [];
$filters = $data['filters'] ?? [];
$summary = $data['summary'] ?? [];
$categories = ['Chatting & Chill', 'Dancing', 'ASMR Lunar', 'Cosplay', 'Editorial', 'Backstage'];
$schedule = $selected && (string) ($selected['scheduled_for'] ?? '') !== '' ? date('Y-m-d\TH:i', strtotime((string) $selected['scheduled_for'])) : '';
$redirect = path_with_query('/creator/live', ['q' => $filters['q'] ?? '', 'status' => $filters['status'] ?? '', 'live' => (int) ($selected['id'] ?? 0)]);
$cover = media_url((string) ($selected['cover_url'] ?? ''));
$selectedLiveId = (int) ($selected['id'] ?? 0);
$roomUrl = $selectedLiveId > 0 ? path_with_query('/live', ['id' => $selectedLiveId]) : '';
$streamStatus = (string) ($selected['stream_status'] ?? 'idle');
$viewerCount = (int) ($selected['viewer_count'] ?? 0);
$bitrate = (int) ($selected['max_bitrate_kbps'] ?? 1500);
$videoWidth = (int) ($selected['video_width'] ?? 960);
$videoHeight = (int) ($selected['video_height'] ?? 540);
$videoFps = (int) ($selected['video_fps'] ?? 24);
$replayUrl = media_url((string) ($selected['recording_url'] ?? ''));
$replayDuration = (int) ($selected['recording_duration_seconds'] ?? 0);
$hasReplay = $replayUrl !== '';
$iceServers = base64_encode((string) json_encode($app->config['app']['rtc_ice_servers'] ?? [], JSON_UNESCAPED_SLASHES));
$iceTransportPolicy = (string) ($app->config['app']['rtc_ice_transport_policy'] ?? 'all');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SexyLua - Live Studio</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet"/>
    <style>
        body { font-family: Manrope, sans-serif; background: #fbf9fb; color: #1b1c1d; }
        .headline { font-family: "Plus Jakarta Sans", sans-serif; }
        .signature-glow { background: linear-gradient(135deg, #D81B60 0%, #ab1155 100%); }
    </style>
</head>
<body>
<?php
ob_start();
?>
<form action="/creator/live" class="hidden items-center gap-4 lg:flex" method="get">
    <div class="relative">
        <input class="w-72 rounded-full border-none bg-white/10 px-5 py-2 pr-12 text-sm text-white outline-none placeholder:text-white/70 focus:ring-1 focus:ring-white/40" name="q" placeholder="Buscar lives..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
        <span class="material-symbols-outlined absolute right-4 top-2 text-white/70">search</span>
    </div>
    <?php if (($filters['status'] ?? '') !== ''): ?>
        <input name="status" type="hidden" value="<?= e((string) $filters['status']) ?>">
    <?php endif; ?>
</form>
<?php
$creatorTopbarSearch = (string) ob_get_clean();
$creatorShellCreator = $creator;
$creatorShellCurrent = 'live';
$creatorTopbarLabel = 'Live Studio';
$creatorTopbarAction = ['href' => '/creator/content', 'label' => 'Conteudo'];
include base_path('templates/partials/creator_sidebar.php');
include base_path('templates/partials/creator_topbar.php');
?>

<main class="px-6 pb-12 pt-24 lg:ml-64 lg:px-10">
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#D81B60]">Creator Studio</p>
            <h2 class="headline mt-2 text-4xl font-extrabold">Transmissao inicial P2P</h2>
            <p class="mt-3 max-w-3xl text-slate-500">Nesta etapa a live sai direto da conexao do criador para os viewers. O bitrate esta limitado a 1.5 Mbps para ficar leve e trocavel por um terceiro depois.</p>
        </div>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div class="rounded-2xl bg-white p-4 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Agendadas</p><p class="headline mt-2 text-2xl font-extrabold text-[#D81B60]"><?= e((string) ($summary['scheduled'] ?? 0)) ?></p></div>
            <div class="rounded-2xl bg-white p-4 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Ao vivo</p><p class="headline mt-2 text-2xl font-extrabold text-[#D81B60]"><?= e((string) ($summary['live'] ?? 0)) ?></p></div>
            <div class="rounded-2xl bg-white p-4 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Viewers</p><p class="headline mt-2 text-2xl font-extrabold text-[#D81B60]" data-live-viewer-count><?= e((string) $viewerCount) ?></p></div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-[1.05fr_0.95fr]">
        <section class="space-y-6">
            <div
                class="overflow-hidden rounded-3xl bg-white shadow-[0px_20px_40px_rgba(27,28,29,0.06)]"
                data-live-rtc-mode="creator"
                data-live-id="<?= e((string) $selectedLiveId) ?>"
                data-csrf="<?= e($app->csrf->token()) ?>"
                data-can-broadcast="<?= $selectedLiveId > 0 ? '1' : '0' ?>"
                data-join-url="/live/rtc/join"
                data-start-url="/live/rtc/start"
                data-stop-url="/live/rtc/stop"
                data-signal-url="/live/rtc/signal"
                data-poll-url="/live/rtc/poll"
                data-heartbeat-url="/live/rtc/heartbeat"
                data-leave-url="/live/rtc/leave"
                data-recording-upload-url="/live/rtc/recording"
                data-recording-enabled="<?= (bool) ($selected['recording_enabled'] ?? false) ? '1' : '0' ?>"
                data-replay-url="<?= e($replayUrl) ?>"
                data-ice-servers="<?= e($iceServers) ?>"
                data-ice-transport-policy="<?= e($iceTransportPolicy) ?>"
                data-max-bitrate-kbps="<?= e((string) $bitrate) ?>"
                data-video-width="<?= e((string) $videoWidth) ?>"
                data-video-height="<?= e((string) $videoHeight) ?>"
                data-video-fps="<?= e((string) $videoFps) ?>"
            >
                <div class="relative aspect-video bg-slate-950">
                    <video autoplay class="h-full w-full object-cover" data-live-local-video muted playsinline></video>
                    <?php if ($cover !== ''): ?><img alt="Capa da live" class="absolute inset-0 h-full w-full object-cover opacity-25" src="<?= e($cover) ?>"><?php endif; ?>
                    <div class="absolute inset-0 flex flex-col justify-between bg-gradient-to-t from-black/80 via-black/20 to-black/40 p-6 text-white">
                        <div class="flex items-start justify-between gap-4">
                            <span class="rounded-full bg-[#D81B60] px-4 py-2 text-[10px] font-bold uppercase tracking-[0.25em]" data-live-status-text><?= e($streamStatus === 'live' ? 'ao vivo' : ($selectedLiveId > 0 ? 'aguardando' : 'sem live')) ?></span>
                            <span class="rounded-full bg-white/15 px-4 py-2 text-[10px] font-bold uppercase tracking-[0.25em]"><span data-live-viewer-count><?= e((string) $viewerCount) ?></span> viewers</span>
                        </div>
                        <div>
                            <p class="headline text-3xl font-extrabold"><?= e((string) ($selected['title'] ?? 'Selecione uma live')) ?></p>
                            <p class="mt-2 max-w-2xl text-sm text-white/80"><?= e((string) ($selected['description'] ?? 'Escolha uma live para abrir o console da transmissao.')) ?></p>
                        </div>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center bg-black/50 px-6 text-center text-white" data-live-waiting>
                        <div class="max-w-md">
                            <p class="headline text-3xl font-extrabold">Studio local</p>
                            <p class="mt-3 text-sm text-white/75" data-live-waiting-text><?= $selectedLiveId > 0 ? 'Preview local pronto. Clique em iniciar transmissao para abrir a live.' : 'Crie ou selecione uma live para abrir a transmissao local.' ?></p>
                        </div>
                    </div>
                </div>
                <div class="space-y-5 p-6">
                    <div class="hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800" data-live-error></div>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div class="rounded-2xl bg-[#f5f3f5] p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Modo</p>
                            <p class="mt-2 text-sm font-bold text-slate-700"><?= e((string) ($selected['stream_mode'] ?? 'p2p_mesh')) ?></p>
                        </div>
                        <div class="rounded-2xl bg-[#f5f3f5] p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Bitrate</p>
                            <p class="mt-2 text-sm font-bold text-slate-700"><span data-live-stream-state><?= e($streamStatus) ?></span> • <?= e((string) $bitrate) ?> kbps</p>
                        </div>
                        <div class="rounded-2xl bg-[#f5f3f5] p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Resolucao</p>
                            <p class="mt-2 text-sm font-bold text-slate-700"><?= e((string) $videoWidth) ?>x<?= e((string) $videoHeight) ?></p>
                        </div>
                        <div class="rounded-2xl bg-[#f5f3f5] p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">FPS</p>
                            <p class="mt-2 text-sm font-bold text-slate-700"><?= e((string) $videoFps) ?> fps</p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button class="signature-glow flex-1 rounded-full px-6 py-4 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-50" data-live-start data-prototype-skip="1" type="button">Iniciar transmissao local</button>
                        <button class="flex-1 rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-50" data-live-stop data-prototype-skip="1" type="button">Encerrar transmissao local</button>
                    </div>
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div class="rounded-2xl bg-[#f5f3f5] p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Preview local</p>
                            <p class="mt-2 text-sm text-slate-600">Controle o espelho da camera e escute o retorno local quando precisar validar enquadramento e audio.</p>
                            <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                                <button class="rounded-full bg-white px-5 py-3 text-sm font-bold text-slate-700" data-live-preview-audio data-prototype-skip="1" type="button">Ouvir preview</button>
                                <button class="rounded-full bg-white px-5 py-3 text-sm font-bold text-slate-700" data-live-preview-mirror data-prototype-skip="1" type="button">Desespelhar camera</button>
                            </div>
                        </div>
                        <div class="rounded-2xl bg-[#f5f3f5] p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Replay local</p>
                            <p class="mt-2 text-sm font-bold text-slate-800" data-live-record-status><?= $hasReplay ? 'Replay pronto para uso' : 'Sem replay salvo ainda' ?></p>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Duracao <span data-live-record-duration><?= e($replayDuration > 0 ? gmdate('H:i:s', $replayDuration) : '00:00:00') ?></span></p>
                            <a class="<?= $hasReplay ? '' : 'hidden ' ?>mt-3 block break-all text-sm font-bold text-[#D81B60] underline" data-live-record-link href="<?= e($replayUrl) ?>" target="_blank"><?= e($replayUrl !== '' ? $replayUrl : '') ?></a>
                            <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                                <button class="rounded-full bg-white px-5 py-3 text-sm font-bold text-slate-700 disabled:cursor-not-allowed disabled:opacity-50" data-live-record-start data-prototype-skip="1" type="button">Gravar local</button>
                                <button class="rounded-full bg-white px-5 py-3 text-sm font-bold text-slate-700 disabled:cursor-not-allowed disabled:opacity-50" data-live-record-stop data-prototype-skip="1" type="button">Parar gravacao</button>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-[#f5f3f5] p-4 text-sm text-slate-600">
                        <p class="font-bold text-slate-800">Sala publica da live</p>
                        <?php if ($roomUrl !== ''): ?>
                            <a class="mt-2 block break-all text-[#D81B60] underline" data-live-room-link href="<?= e($roomUrl) ?>" target="_blank"><?= e($roomUrl) ?></a>
                        <?php else: ?>
                            <p class="mt-2">Salve uma live para gerar a sala publica.</p>
                        <?php endif; ?>
                        <p class="mt-3 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Recomendacao inicial: conexao de upload acima de 20 Mbps para testar com folga.</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                <h4 class="headline text-xl font-extrabold">Ultimas mensagens da live selecionada</h4>
                <div class="mt-4 space-y-3">
                    <?php foreach (array_slice($messages, -6) as $message): ?>
                        <div class="rounded-2xl bg-[#f5f3f5] p-4 text-sm">
                            <span class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-[#D81B60]"><?= e((string) ($message['sender']['name'] ?? 'Convidado')) ?></span>
                            <?= e((string) ($message['body'] ?? '')) ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($messages === []): ?><p class="text-sm text-slate-500">Sem mensagens para esta live ainda.</p><?php endif; ?>
                </div>
            </div>
        </section>

        <section class="rounded-3xl bg-white p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
            <h3 class="headline text-2xl font-extrabold"><?= $selected ? 'Editar transmissao' : 'Nova transmissao' ?></h3>
            <form action="/creator/live/save" class="mt-6 space-y-4" enctype="multipart/form-data" method="post">
                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                <?php if ($selected): ?><input name="id" type="hidden" value="<?= e((string) ($selected['id'] ?? 0)) ?>"><?php endif; ?>
                <input class="w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="title" placeholder="Titulo da live" required type="text" value="<?= e((string) ($selected['title'] ?? '')) ?>">
                <textarea class="min-h-[120px] w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="description" placeholder="Descricao"><?= e((string) ($selected['description'] ?? '')) ?></textarea>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="scheduled_for" type="datetime-local" value="<?= e($schedule) ?>">
                    <select class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="category"><?php foreach ($categories as $category): ?><option value="<?= e($category) ?>" <?= (string) ($selected['category'] ?? 'Chatting & Chill') === $category ? 'selected' : '' ?>><?= e($category) ?></option><?php endforeach; ?></select>
                    <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" min="0" name="price_luacoins" placeholder="Preco em LuaCoins" type="number" value="<?= e((string) ($selected['price_tokens'] ?? 0)) ?>">
                    <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" min="0" name="goal_luacoins" placeholder="Meta em LuaCoins" type="number" value="<?= e((string) ($selected['goal_tokens'] ?? 0)) ?>">
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="cover_url" placeholder="Cover URL" type="url" value="<?= e((string) ($selected['cover_url'] ?? '')) ?>">
                    <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4 file:mr-4 file:rounded-full file:border-0 file:bg-[#D81B60] file:px-4 file:py-2 file:text-sm file:font-bold file:text-white" name="cover_file" type="file">
                    <input name="max_bitrate_kbps" type="hidden" value="<?= e((string) $bitrate) ?>">
                    <input name="video_width" type="hidden" value="<?= e((string) $videoWidth) ?>">
                    <input name="video_height" type="hidden" value="<?= e((string) $videoHeight) ?>">
                    <input name="video_fps" type="hidden" value="<?= e((string) $videoFps) ?>">
                </div>
                <textarea class="min-h-[92px] w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="pinned_notice" placeholder="Aviso fixado"><?= e((string) ($selected['pinned_notice'] ?? '')) ?></textarea>
                <div class="grid grid-cols-2 gap-4">
                    <label class="rounded-2xl bg-[#f5f3f5] p-4 text-sm font-semibold"><input <?= (string) ($selected['access_mode'] ?? 'public') === 'public' ? 'checked' : '' ?> class="mr-3" name="access_mode" type="radio" value="public"> Publico</label>
                    <label class="rounded-2xl bg-[#f5f3f5] p-4 text-sm font-semibold"><input <?= (string) ($selected['access_mode'] ?? '') === 'subscriber' ? 'checked' : '' ?> class="mr-3" name="access_mode" type="radio" value="subscriber"> Assinantes</label>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <label class="rounded-2xl bg-[#f5f3f5] p-4 text-sm font-semibold"><input <?= !isset($selected['chat_enabled']) || (bool) ($selected['chat_enabled'] ?? false) ? 'checked' : '' ?> class="mr-3" name="chat_enabled" type="checkbox" value="1"> Chat habilitado</label>
                    <label class="rounded-2xl bg-[#f5f3f5] p-4 text-sm font-semibold"><input <?= (bool) ($selected['recording_enabled'] ?? false) ? 'checked' : '' ?> class="mr-3" name="recording_enabled" type="checkbox" value="1"> Gravar replay</label>
                </div>
                <div class="rounded-2xl bg-[#f5f3f5] p-4 text-sm text-slate-600">
                    <p class="font-bold text-slate-800">Preset inicial da transmissao</p>
                    <p class="mt-2">Bitrate maximo: <strong><?= e((string) $bitrate) ?> kbps</strong> • Resolucao: <strong><?= e((string) $videoWidth) ?>x<?= e((string) $videoHeight) ?></strong> • FPS: <strong><?= e((string) $videoFps) ?></strong></p>
                </div>
                <button class="signature-glow w-full rounded-full px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit"><?= $selected ? 'Salvar alteracoes' : 'Salvar live' ?></button>
            </form>
        </section>
    </div>

    <form action="/creator/live" class="mt-10 grid grid-cols-1 gap-4 rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] md:grid-cols-[1fr_0.6fr_auto]" method="get">
        <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="q" placeholder="Buscar live..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
        <select class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="status"><option value="">Todos os status</option><option value="scheduled" <?= (string) ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Agendada</option><option value="live" <?= (string) ($filters['status'] ?? '') === 'live' ? 'selected' : '' ?>>Ao vivo</option><option value="ended" <?= (string) ($filters['status'] ?? '') === 'ended' ? 'selected' : '' ?>>Encerrada</option></select>
        <div class="flex items-end gap-3"><button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button><a class="rounded-full bg-[#f5f3f5] px-5 py-4 text-sm font-bold text-slate-600" href="/creator/live">Reset</a></div>
    </form>

    <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($lives as $live): ?>
            <?php
            $status = (string) ($live['status'] ?? 'scheduled');
            $next = $status === 'live' ? ['ended', 'Encerrar'] : ($status === 'ended' ? ['scheduled', 'Reagendar'] : ['live', 'Entrar no ar']);
            $liveRoomUrl = path_with_query('/live', ['id' => (int) ($live['id'] ?? 0)]);
            ?>
            <article class="overflow-hidden rounded-3xl bg-white shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                <div class="signature-glow p-5 text-white">
                    <p class="headline truncate text-xl font-extrabold"><?= e((string) ($live['title'] ?? 'Live')) ?></p>
                    <p class="mt-2 text-sm text-white/80"><?= e(format_datetime((string) ($live['scheduled_for'] ?? ''), 'd/m H:i')) ?></p>
                </div>
                <div class="space-y-4 p-5">
                    <p class="text-sm text-slate-500"><?= e(excerpt((string) ($live['description'] ?? ''), 120)) ?></p>
                    <div class="flex items-center justify-between text-xs font-bold uppercase tracking-widest text-slate-500">
                        <span><?= e((string) ($live['category'] ?? 'Studio')) ?></span>
                        <span><?= e((string) ($live['viewer_count'] ?? 0)) ?> viewers</span>
                    </div>
                    <div class="rounded-2xl bg-[#f5f3f5] px-4 py-3 text-xs font-bold uppercase tracking-[0.25em] text-slate-500">
                        stream <?= e((string) ($live['stream_status'] ?? 'idle')) ?> • <?= e((string) ($live['max_bitrate_kbps'] ?? 1500)) ?> kbps
                    </div>
                    <?php if ((string) ($live['recording_url'] ?? '') !== ''): ?>
                        <a class="block rounded-2xl bg-[#D81B60]/10 px-4 py-3 text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]" href="<?= e(media_url((string) ($live['recording_url'] ?? ''))) ?>" target="_blank">Replay salvo</a>
                    <?php endif; ?>
                    <div class="grid grid-cols-2 gap-3">
                        <a class="rounded-full bg-[#f5f3f5] px-4 py-3 text-center text-xs font-bold text-slate-700" href="<?= e(path_with_query('/creator/live', ['q' => $filters['q'] ?? '', 'status' => $filters['status'] ?? '', 'live' => (int) ($live['id'] ?? 0)])) ?>">Editar</a>
                        <a class="rounded-full bg-[#D81B60]/10 px-4 py-3 text-center text-xs font-bold text-[#D81B60]" href="<?= e($liveRoomUrl) ?>" target="_blank">Abrir sala</a>
                        <form action="/creator/live/status" method="post"><input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>"><input name="live_id" type="hidden" value="<?= e((string) ($live['id'] ?? 0)) ?>"><input name="status" type="hidden" value="<?= e((string) $next[0]) ?>"><input name="redirect" type="hidden" value="<?= e($redirect) ?>"><button class="w-full rounded-full bg-slate-900 px-4 py-3 text-xs font-bold text-white" data-prototype-skip="1" type="submit"><?= e((string) $next[1]) ?></button></form>
                        <form action="/creator/live/delete" method="post" onsubmit="return confirm('Remover esta live?');"><input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>"><input name="live_id" type="hidden" value="<?= e((string) ($live['id'] ?? 0)) ?>"><input name="redirect" type="hidden" value="<?= e($redirect) ?>"><button class="w-full rounded-full bg-rose-50 px-4 py-3 text-xs font-bold text-rose-700" data-prototype-skip="1" type="submit">Excluir</button></form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if ($lives === []): ?><p class="rounded-3xl bg-white p-8 text-sm text-slate-500 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">Nenhuma live cadastrada ainda.</p><?php endif; ?>
    </div>
</main>

<script src="<?= e(asset('js/live-rtc.js')) ?>"></script>
</body>
</html>
