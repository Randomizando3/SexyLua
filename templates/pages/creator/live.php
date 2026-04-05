<?php

declare(strict_types=1);

$creator = $data['creator'] ?? [];
$lives = $data['filtered_lives'] ?? $data['lives'] ?? [];
$selected = $data['selected_live'] ?? null;
$filters = $data['filters'] ?? [];
$summary = $data['summary'] ?? [];
$openForm = (bool) ($open_form ?? false);
$formMode = (string) ($form_mode ?? '');

$selectedLiveId = (int) ($selected['id'] ?? 0);
$selectedStatus = (string) ($selected['status'] ?? 'scheduled');
$selectedStatusBucket = (string) ($selected['status_bucket'] ?? 'scheduled');
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
$selectedIsConcluded = $selectedStatus === 'ended';
$viewerCount = (int) ($selected['viewer_count'] ?? 0);
$selectedTipTotalAmount = (int) ($selected['tip_total_amount'] ?? 0);
$chatAudienceLabels = [
    'all' => 'Assinantes e não assinantes',
    'subscriber' => 'Só assinantes',
    'off' => 'Chat desabilitado',
];
$selectedChatAudience = (string) ($selected['chat_audience'] ?? 'all');
$selectedAccessMode = (string) ($selected['access_mode'] ?? 'public');
$selectedAccessLabel = $selectedAccessMode === 'subscriber' ? 'Assinantes' : 'Público';
$selectedMaxDurationMinutes = max(5, (int) ($selected['max_live_duration_minutes'] ?? 30));
$categories = audience_category_options();
$statusTabIcons = [
    'scheduled' => 'event',
    'ended' => 'task_alt',
    'expired' => 'alarm',
];
$statusTabs = [
    'scheduled' => ['label' => 'Agendadas', 'count' => (int) ($summary['scheduled'] ?? 0)],
    'ended' => ['label' => 'Concluídas', 'count' => (int) ($summary['ended'] ?? 0)],
    'expired' => ['label' => 'Expiradas', 'count' => (int) ($summary['expired'] ?? 0)],
];

$newLiveUrl = path_with_query('/creator/live', ['status' => $filters['status'] ?? 'scheduled', 'q' => $filters['q'] ?? '', 'open_form' => 1, 'form_mode' => 'new']);
$editLiveUrl = $selectedLiveId > 0
    ? path_with_query('/creator/live', ['status' => $selectedStatusBucket, 'q' => $filters['q'] ?? '', 'live' => $selectedLiveId, 'open_form' => 1, 'form_mode' => 'edit'])
    : '';
$selectedStudioUrl = $selectedLiveId > 0 ? path_with_query('/creator/live/studio', ['live' => $selectedLiveId]) : '';
$closeModalUrl = path_with_query('/creator/live', ['status' => $filters['status'] ?? 'scheduled', 'q' => $filters['q'] ?? '', 'live' => $selectedLiveId > 0 ? $selectedLiveId : null]);
$formLive = ($openForm && $formMode === 'new') ? null : $selected;
$formLiveId = (int) ($formLive['id'] ?? 0);
$formLiveType = (string) ($formLive['live_type'] ?? 'scheduled');
$formSchedule = $formLive && (string) ($formLive['scheduled_for'] ?? '') !== '' ? date('Y-m-d\TH:i', strtotime((string) ($formLive['scheduled_for'] ?? ''))) : '';
$formPriceValueRaw = (int) ($formLive['price_tokens'] ?? 0);
$formGoalValueRaw = (int) ($formLive['goal_tokens'] ?? 0);
$formPriceValue = $formPriceValueRaw > 0 ? (string) $formPriceValueRaw : '';
$formGoalValue = $formGoalValueRaw > 0 ? (string) $formGoalValueRaw : '';
$modalTitle = $formLiveId > 0 ? 'Editar live' : 'Nova live';
$modalSubmitLabel = $formLiveId > 0 ? 'Salvar alterações' : 'Salvar live';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SexyLua - Lives do Criador</title>
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
$creatorTopbarLabel = 'Lives';
$creatorTopbarAction = ['href' => '/creator/content', 'label' => 'Conteúdo'];
include base_path('templates/partials/creator_sidebar.php');
include base_path('templates/partials/creator_topbar.php');
?>

<main class="px-6 pb-12 pt-24 lg:ml-64 lg:px-10">
    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#D81B60]">Creator Studio</p>
            <h1 class="headline mt-2 text-4xl font-extrabold">Minhas lives</h1>
            <p class="mt-3 max-w-3xl text-slate-500">Crie lives instantâneas ou agendadas, acompanhe a agenda em ordem cronológica e abra o estúdio só quando a sala estiver pronta.</p>
        </div>
        <a class="signature-glow inline-flex w-full items-center justify-center gap-2 rounded-full px-6 py-3 text-sm font-bold text-white xl:w-auto" href="<?= e($newLiveUrl) ?>">
            <span class="material-symbols-outlined text-lg">add</span>
            Nova live
        </a>
    </div>

    <section class="mt-8 rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] lg:p-8">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="flex flex-wrap gap-3">
                <?php foreach ($statusTabs as $key => $tab): ?>
                    <?php $tabUrl = path_with_query('/creator/live', ['status' => $key, 'q' => $filters['q'] ?? '']); ?>
                    <a aria-label="<?= e((string) $tab['label']) ?>" class="<?= ($filters['status'] ?? 'scheduled') === $key ? 'signature-glow text-white' : 'bg-[#f7f4f7] text-slate-600' ?> inline-flex h-12 items-center gap-2 rounded-full px-4 text-sm font-bold" href="<?= e($tabUrl) ?>" title="<?= e((string) $tab['label']) ?>">
                        <span class="material-symbols-outlined text-[20px]"><?= e((string) ($statusTabIcons[$key] ?? 'event')) ?></span>
                        <span class="sr-only"><?= e((string) $tab['label']) ?></span>
                        <span class="ml-2 rounded-full <?= ($filters['status'] ?? 'scheduled') === $key ? 'bg-white/20 text-white' : 'bg-white text-slate-500' ?> px-2.5 py-1 text-[11px]"><?= e((string) $tab['count']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <form action="/creator/live" class="grid w-full grid-cols-1 gap-3 md:w-auto md:grid-cols-[minmax(0,340px)_auto_auto]" method="get">
                <input name="status" type="hidden" value="<?= e((string) ($filters['status'] ?? 'scheduled')) ?>">
                <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-3.5" name="q" placeholder="Buscar live..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
                <button class="rounded-full bg-slate-900 px-6 py-3 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
                <a class="rounded-full bg-[#f5f3f5] px-6 py-3 text-center text-sm font-bold text-slate-600" href="<?= e(path_with_query('/creator/live', ['status' => $filters['status'] ?? 'scheduled'])) ?>">Reset</a>
            </form>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-2 2xl:grid-cols-3">
            <?php foreach ($lives as $live): ?>
                <?php
                $liveId = (int) ($live['id'] ?? 0);
                $isSelected = $selectedLiveId > 0 && $selectedLiveId === $liveId;
                $status = (string) ($live['status'] ?? 'scheduled');
                $statusLabel = match ($status) {
                    'live' => 'Ao vivo',
                    'ended' => 'Concluída',
                    'expired' => 'Expirada',
                    default => 'Agendada',
                };
                $selectUrl = path_with_query('/creator/live', ['status' => (string) ($live['status_bucket'] ?? 'scheduled'), 'q' => $filters['q'] ?? '', 'live' => $liveId]);
                ?>
                <a class="<?= $isSelected ? 'ring-2 ring-[#D81B60]' : 'ring-1 ring-[#f0e8ee]' ?> overflow-hidden rounded-3xl bg-[#fbf9fb] transition-transform hover:-translate-y-1" href="<?= e($selectUrl) ?>">
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="headline truncate text-xl font-extrabold"><?= e((string) ($live['title'] ?? 'Live')) ?></p>
                                <p class="mt-2 text-sm text-slate-500"><?= e(excerpt((string) ($live['description'] ?? ''), 110)) ?></p>
                            </div>
                            <span class="<?= $status === 'live' ? 'bg-emerald-500 text-white' : ($status === 'expired' ? 'bg-amber-100 text-amber-700' : 'bg-[#f2dce6] text-[#ab1155]') ?> shrink-0 rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.22em]"><?= e($statusLabel) ?></span>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-2xl bg-white px-4 py-3">
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Agenda</p>
                                <p class="mt-2 font-bold text-slate-700"><?= e(format_datetime((string) ($live['scheduled_for'] ?? ''))) ?></p>
                            </div>
                            <div class="rounded-2xl bg-white px-4 py-3">
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Viewers</p>
                                <p class="mt-2 font-bold text-slate-700"><?= e((string) ((int) ($live['viewer_count'] ?? 0))) ?></p>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>

            <?php if ($lives === []): ?>
                <div class="rounded-3xl bg-[#fbf9fb] p-8 text-sm text-slate-500 ring-1 ring-[#f0e8ee] xl:col-span-2 2xl:col-span-3">
                    Nenhuma live encontrada neste filtro ainda.
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="mt-8" data-live-details>
        <?php if ($selected === null): ?>
            <div class="rounded-3xl bg-white p-10 text-center shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                <h2 class="headline text-3xl font-extrabold">Escolha uma live para ver os detalhes</h2>
                <p class="mt-3 text-slate-500">A agenda fica separada do estúdio. Selecione uma live acima para abrir os detalhes e, quando estiver pronta, entrar no estúdio.</p>
                <a class="signature-glow mt-6 inline-flex items-center gap-2 rounded-full px-6 py-3 text-sm font-bold text-white" href="<?= e($newLiveUrl) ?>">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Criar nova live
                </a>
            </div>
        <?php elseif ($selectedIsConcluded): ?>
            <section class="rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] lg:p-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]">Resumo da transmissão</p>
                        <h2 class="headline mt-2 text-3xl font-extrabold"><?= e((string) ($selected['title'] ?? 'Live')) ?></h2>
                        <p class="mt-3 max-w-3xl text-sm text-slate-500"><?= e((string) ($selected['description'] ?? '')) ?></p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <?php if ($editLiveUrl !== ''): ?>
                            <a class="rounded-full bg-[#f7f4f7] px-5 py-3 text-sm font-bold text-slate-600" href="<?= e($editLiveUrl) ?>">Editar dados</a>
                        <?php endif; ?>
                        <span class="rounded-full bg-[#f2dce6] px-4 py-2 text-[11px] font-bold uppercase tracking-[0.25em] text-[#ab1155]"><?= e($selectedStatusLabel) ?></span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl bg-[#f5f3f5] p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Agendada</p>
                        <p class="mt-2 text-sm font-bold text-slate-700"><?= e($selectedScheduleLabel !== '' ? $selectedScheduleLabel : 'Sem agenda') ?></p>
                    </div>
                    <div class="rounded-2xl bg-[#f5f3f5] p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Iniciada em</p>
                        <p class="mt-2 text-sm font-bold text-slate-700"><?= e($selectedStartedAt !== '' ? $selectedStartedAt : 'Não iniciou') ?></p>
                    </div>
                    <div class="rounded-2xl bg-[#f5f3f5] p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Encerrada em</p>
                        <p class="mt-2 text-sm font-bold text-slate-700"><?= e($selectedEndedAt !== '' ? $selectedEndedAt : 'Sem registro') ?></p>
                    </div>
                    <div class="rounded-2xl bg-[#f5f3f5] p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Duração</p>
                        <p class="mt-2 text-sm font-bold text-slate-700"><?= e($selectedDurationLabel) ?></p>
                    </div>
                    <div class="rounded-2xl bg-[#f5f3f5] p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Gorjetas</p>
                        <div class="mt-2 text-sm font-bold text-slate-700"><?= luacoin_amount_html($selectedTipTotalAmount, 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?></div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[minmax(320px,0.9fr)_minmax(0,1.1fr)]">
                    <div class="overflow-hidden rounded-3xl bg-[#f7f4f7]">
                        <div class="relative aspect-video bg-slate-950">
                            <?php if ($selectedCover !== ''): ?>
                                <img alt="Capa da live" class="h-full w-full object-cover" src="<?= e($selectedCover) ?>">
                            <?php else: ?>
                                <div class="signature-glow flex h-full w-full items-center justify-center text-white">
                                    <span class="headline text-2xl font-extrabold"><?= e(mb_strtoupper(mb_substr((string) ($selected['title'] ?? 'LIVE'), 0, 18))) ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-black/10 to-transparent"></div>
                            <div class="absolute inset-x-0 bottom-0 p-5 text-white">
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-white/75">Transmissão encerrada</p>
                                <p class="mt-2 text-sm text-white/80">Confira abaixo os dados finais desta live e use a agenda para programar a próxima sessão.</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-3xl bg-[#f7f4f7] p-5">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Sala e visibilidade</p>
                            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="rounded-2xl bg-white px-4 py-4">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Acesso</p>
                                    <p class="mt-2 text-sm font-bold text-slate-700"><?= e($selectedAccessLabel) ?></p>
                                </div>
                                <div class="rounded-2xl bg-white px-4 py-4">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Chat</p>
                                    <p class="mt-2 text-sm font-bold text-slate-700"><?= e($chatAudienceLabels[$selectedChatAudience] ?? 'Assinantes e não assinantes') ?></p>
                                </div>
                                <div class="rounded-2xl bg-white px-4 py-4 md:col-span-2">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Resumo</p>
                                    <p class="mt-2 text-sm text-slate-600">A gravação automática está desabilitada para reduzir uso de armazenamento. O foco desta sala é a transmissão ao vivo.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl bg-[#f7f4f7] p-5">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Sala pública</p>
                            <a class="mt-3 block break-all text-sm font-bold text-[#D81B60] underline" href="<?= e($selectedRoomUrl) ?>" target="_blank"><?= e($selectedRoomUrl) ?></a>
                            <p class="mt-4 text-sm text-slate-500">Use esta URL para compartilhar a sala pública desta transmissão sempre que quiser divulgar a próxima live.</p>
                        </div>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <section class="rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] lg:p-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]">Detalhes da live</p>
                        <h2 class="headline mt-2 text-3xl font-extrabold"><?= e((string) ($selected['title'] ?? 'Live')) ?></h2>
                        <p class="mt-3 max-w-3xl text-sm text-slate-500"><?= e((string) ($selected['description'] ?? '')) ?></p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <?php if ($editLiveUrl !== ''): ?>
                            <a class="rounded-full bg-[#f7f4f7] px-5 py-3 text-sm font-bold text-slate-600" href="<?= e($editLiveUrl) ?>">Editar dados</a>
                        <?php endif; ?>
                        <?php if ($selectedStatus === 'expired'): ?>
                            <a class="rounded-full bg-slate-900 px-5 py-3 text-sm font-bold text-white" href="<?= e($editLiveUrl) ?>">Editar live</a>
                        <?php elseif ($selectedStudioUrl !== ''): ?>
                            <a class="signature-glow rounded-full px-5 py-3 text-sm font-bold text-white" href="<?= e($selectedStudioUrl) ?>"><?= e($selectedStatus === 'live' ? 'Abrir estúdio' : 'Iniciar live agendada') ?></a>
                        <?php endif; ?>
                        <span class="<?= $selectedStatus === 'live' ? 'bg-emerald-500 text-white' : ($selectedStatus === 'expired' ? 'bg-amber-100 text-amber-700' : 'bg-[#f2dce6] text-[#ab1155]') ?> rounded-full px-4 py-2 text-[11px] font-bold uppercase tracking-[0.25em]"><?= e($selectedStatusLabel) ?></span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl bg-[#f5f3f5] p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Agenda</p>
                        <p class="mt-2 text-sm font-bold text-slate-700"><?= e($selectedScheduleLabel !== '' ? $selectedScheduleLabel : 'Sem agenda') ?></p>
                    </div>
                    <div class="rounded-2xl bg-[#f5f3f5] p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Acesso</p>
                        <p class="mt-2 text-sm font-bold text-slate-700"><?= e($selectedAccessLabel) ?></p>
                    </div>
                    <div class="rounded-2xl bg-[#f5f3f5] p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Chat</p>
                        <p class="mt-2 text-sm font-bold text-slate-700"><?= e($chatAudienceLabels[$selectedChatAudience] ?? 'Assinantes e não assinantes') ?></p>
                    </div>
                    <div class="rounded-2xl bg-[#f5f3f5] p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Duração máxima</p>
                        <p class="mt-2 text-sm font-bold text-slate-700"><?= e((string) $selectedMaxDurationMinutes) ?> min</p>
                    </div>
                    <div class="rounded-2xl bg-[#f5f3f5] p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Meta</p>
                        <div class="mt-2 text-sm font-bold text-slate-700"><?= luacoin_amount_html((int) ($selected['goal_tokens'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?></div>
                    </div>
                    <div class="rounded-2xl bg-[#f5f3f5] p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Ingresso</p>
                        <div class="mt-2 text-sm font-bold text-slate-700"><?= luacoin_amount_html((int) ($selected['price_tokens'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?></div>
                    </div>
                    <div class="rounded-2xl bg-[#f5f3f5] p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Viewers</p>
                        <p class="mt-2 text-sm font-bold text-slate-700"><?= e((string) $viewerCount) ?></p>
                    </div>
                    <div class="rounded-2xl bg-[#f5f3f5] p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Gorjetas</p>
                        <div class="mt-2 text-sm font-bold text-slate-700"><?= luacoin_amount_html($selectedTipTotalAmount, 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?></div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[minmax(320px,0.9fr)_minmax(0,1.1fr)]">
                    <div class="overflow-hidden rounded-3xl bg-[#f7f4f7]">
                        <div class="relative aspect-video bg-slate-950">
                            <?php if ($selectedCover !== ''): ?>
                                <img alt="Capa da live" class="h-full w-full object-cover" src="<?= e($selectedCover) ?>">
                            <?php else: ?>
                                <div class="signature-glow flex h-full w-full items-center justify-center text-white">
                                    <span class="headline text-2xl font-extrabold"><?= e(mb_strtoupper(mb_substr((string) ($selected['title'] ?? 'LIVE'), 0, 18))) ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-black/10 to-transparent"></div>
                            <div class="absolute inset-x-0 bottom-0 p-5 text-white">
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-white/75"><?= e($selectedStatusLabel) ?></p>
                                <p class="mt-2 text-sm text-white/80"><?= $selectedStatus === 'expired' ? 'Esta live expirou porque a data passou sem transmissão.' : 'Quando estiver pronta, abra o estúdio em uma tela separada para ajustar câmera, microfone e iniciar a sala.' ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-3xl bg-[#f7f4f7] p-5">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Sala pública</p>
                            <a class="mt-3 block break-all text-sm font-bold text-[#D81B60] underline" href="<?= e($selectedRoomUrl) ?>" target="_blank"><?= e($selectedRoomUrl) ?></a>
                            <p class="mt-4 text-sm text-slate-500"><?= $selectedStatus === 'expired' ? 'Reagende ou edite esta live antes de tentar entrar no ar novamente.' : 'Ao iniciar a live no estúdio, esta será a sala aberta para o público.' ?></p>
                        </div>

                        <?php if (trim((string) ($selected['pinned_notice'] ?? '')) !== ''): ?>
                            <div class="rounded-3xl bg-[#f7f4f7] p-5">
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Aviso fixado</p>
                                <p class="mt-3 text-sm text-slate-600"><?= e((string) ($selected['pinned_notice'] ?? '')) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($selectedStatus === 'live'): ?>
                            <div class="rounded-3xl bg-emerald-50 p-5">
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-emerald-600">Live em andamento</p>
                                <p class="mt-3 text-sm text-emerald-800">Esta live já está no ar. Abra o estúdio para acompanhar preview, chat e encerrar quando quiser.</p>
                                <?php if ($selectedStartedAt !== ''): ?>
                                    <p class="mt-3 text-sm font-bold text-emerald-900">Iniciada em <?= e($selectedStartedAt) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </section>
</main>

<div class="<?= $openForm ? '' : 'hidden ' ?>fixed inset-0 z-[80] flex items-start justify-center overflow-y-auto bg-slate-950/45 px-4 py-8" data-live-modal>
    <div class="relative w-full max-w-4xl rounded-3xl bg-white p-6 shadow-[0px_30px_80px_rgba(27,28,29,0.18)] sm:p-8">
        <a aria-label="Fechar modal" class="absolute right-4 top-4 inline-flex h-11 w-11 items-center justify-center rounded-full bg-[#f5f3f5] text-slate-500 transition-colors hover:bg-[#ebe6eb] sm:right-6 sm:top-6" href="<?= e($closeModalUrl) ?>">
            <span class="material-symbols-outlined">close</span>
        </a>
        <div class="flex flex-col gap-3 pr-12 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h3 class="headline text-2xl font-extrabold"><?= e($modalTitle) ?></h3>
                <p class="mt-2 text-sm text-slate-500">Crie uma live instantânea para entrar no ar hoje ou agende uma sessão completa para depois.</p>
            </div>
        </div>

        <form action="/creator/live/save" class="mt-6 space-y-4" enctype="multipart/form-data" method="post" data-live-form>
            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
            <?php if ($formLiveId > 0): ?><input name="id" type="hidden" value="<?= e((string) $formLiveId) ?>"><?php endif; ?>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <label class="rounded-2xl bg-[#f5f3f5] p-4 text-sm font-semibold">
                    <input <?= $formLiveType === 'instant' ? 'checked' : '' ?> class="mr-3" name="live_type" type="radio" value="instant">
                    Live instantânea
                </label>
                <label class="rounded-2xl bg-[#f5f3f5] p-4 text-sm font-semibold">
                    <input <?= $formLiveType !== 'instant' ? 'checked' : '' ?> class="mr-3" name="live_type" type="radio" value="scheduled">
                    Agendar live
                </label>
            </div>

            <input class="w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="title" placeholder="Título da live" required type="text" value="<?= e((string) ($formLive['title'] ?? '')) ?>">
            <textarea class="min-h-[120px] w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="description" placeholder="Descrição"><?= e((string) ($formLive['description'] ?? '')) ?></textarea>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div data-live-form-schedule-group>
                    <input class="w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="scheduled_for" type="datetime-local" value="<?= e($formSchedule) ?>">
                </div>
                <select class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="category">
                    <?php foreach ($categories as $categoryValue => $categoryLabel): ?>
                        <option value="<?= e($categoryValue) ?>" <?= (string) ($formLive['category'] ?? 'todos') === $categoryValue ? 'selected' : '' ?>><?= e($categoryLabel) ?></option>
                    <?php endforeach; ?>
                </select>
                <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" min="0" name="price_luacoins" placeholder="Preço em LuaCoins" type="number" value="<?= e($formPriceValue) ?>">
                <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" min="0" name="goal_luacoins" placeholder="Meta em LuaCoins" type="number" value="<?= e($formGoalValue) ?>">
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <select class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="access_mode">
                    <option value="public" <?= (string) ($formLive['access_mode'] ?? 'public') === 'public' ? 'selected' : '' ?>>Público</option>
                    <option value="subscriber" <?= (string) ($formLive['access_mode'] ?? '') === 'subscriber' ? 'selected' : '' ?>>Assinantes</option>
                </select>
                <select class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="chat_audience">
                    <option value="all" <?= (string) ($formLive['chat_audience'] ?? 'all') === 'all' ? 'selected' : '' ?>>Chat para assinantes e não assinantes</option>
                    <option value="subscriber" <?= (string) ($formLive['chat_audience'] ?? '') === 'subscriber' ? 'selected' : '' ?>>Chat só para assinantes</option>
                    <option value="off" <?= (string) ($formLive['chat_audience'] ?? '') === 'off' ? 'selected' : '' ?>>Chat desabilitado</option>
                </select>
                <div class="flex items-center rounded-2xl bg-[#f5f3f5] px-5 py-4 text-sm font-semibold text-slate-500">
                    O chat segue automaticamente a opção escolhida acima.
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="cover_url" placeholder="URL da capa (opcional)" type="url" value="<?= e((string) ($formLive['cover_url'] ?? '')) ?>">
                <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4 file:mr-4 file:rounded-full file:border-0 file:bg-[#D81B60] file:px-4 file:py-2 file:text-sm file:font-bold file:text-white" name="cover_file" type="file">
            </div>

            <textarea class="min-h-[92px] w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="pinned_notice" placeholder="Aviso fixado"><?= e((string) ($formLive['pinned_notice'] ?? '')) ?></textarea>

            <input name="max_bitrate_kbps" type="hidden" value="<?= e((string) ((int) ($formLive['max_bitrate_kbps'] ?? 800))) ?>">
            <input name="video_width" type="hidden" value="<?= e((string) ((int) ($formLive['video_width'] ?? 854))) ?>">
            <input name="video_height" type="hidden" value="<?= e((string) ((int) ($formLive['video_height'] ?? 480))) ?>">
            <input name="video_fps" type="hidden" value="<?= e((string) ((int) ($formLive['video_fps'] ?? 30))) ?>">

            <div class="rounded-2xl bg-[#f5f3f5] p-4 text-sm text-slate-600">
                <p class="font-bold text-slate-800">Fluxo da live</p>
                <p class="mt-2">1. Salve a live. 2. Selecione a live na agenda. 3. Abra o estúdio em uma tela separada. 4. Ajuste câmera e microfone. 5. Clique em começar live quando tudo estiver pronto.</p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a class="rounded-full bg-[#f5f3f5] px-6 py-4 text-center text-sm font-bold text-slate-600" href="<?= e($closeModalUrl) ?>">Cancelar</a>
                <button class="signature-glow rounded-full px-8 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit"><?= e($modalSubmitLabel) ?></button>
            </div>
        </form>
    </div>
</div>

<script>
    (() => {
        const form = document.querySelector('[data-live-form]');
        if (!form) {
            const detailsSection = document.querySelector('[data-live-details]');
            const params = new URLSearchParams(window.location.search);
            if (detailsSection && window.matchMedia('(max-width: 1023px)').matches && params.has('live')) {
                window.setTimeout(() => {
                    detailsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 120);
            }
            return;
        }

        const updateScheduleVisibility = () => {
            const selected = form.querySelector('input[name="live_type"]:checked');
            const scheduleGroup = form.querySelector('[data-live-form-schedule-group]');
            const scheduleInput = form.querySelector('input[name="scheduled_for"]');

            if (!scheduleGroup || !(scheduleInput instanceof HTMLInputElement)) {
                return;
            }

            const isInstant = selected && selected.value === 'instant';
            scheduleGroup.classList.toggle('hidden', !!isInstant);
            scheduleInput.required = !isInstant;
            if (isInstant) {
                scheduleInput.value = '';
            }
        };

        form.querySelectorAll('input[name="live_type"]').forEach((field) => {
            field.addEventListener('change', updateScheduleVisibility);
        });

        updateScheduleVisibility();

        const detailsSection = document.querySelector('[data-live-details]');
        const params = new URLSearchParams(window.location.search);
        if (detailsSection && window.matchMedia('(max-width: 1023px)').matches && params.has('live')) {
            window.setTimeout(() => {
                detailsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 120);
        }
    })();
</script>
</body>
</html>
