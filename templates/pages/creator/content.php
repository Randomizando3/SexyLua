<?php

declare(strict_types=1);

$creator = $data['creator'] ?? [];
$items = $data['filtered_items'] ?? $data['items'] ?? [];
$plans = $data['plans'] ?? [];
$selectedItem = $data['selected_item'] ?? null;
$filters = $data['filters'] ?? [];
$counts = $data['counts'] ?? [];
$summary = $data['summary'] ?? [];
$openForm = (bool) ($open_form ?? false);
$formMode = (string) ($form_mode ?? '');
$formItem = ($openForm && $formMode === 'new') ? null : $selectedItem;
$formItemId = (int) ($formItem['id'] ?? 0);
$modalTitle = $formItemId > 0 ? 'Editar conteúdo' : 'Novo conteúdo';
$submitLabel = $formItemId > 0 ? 'Salvar alterações' : 'Salvar conteúdo';
$closeModalUrl = path_with_query('/creator/content', [
    'q' => $filters['q'] ?? '',
    'status' => $filters['status'] ?? '',
    'kind' => $filters['kind'] ?? '',
]);
$statusLabels = [
    'approved' => 'Publicado',
    'pending' => 'Pendente',
    'draft' => 'Rascunho',
    'rejected' => 'Rejeitado',
    'archived' => 'Arquivado',
];
$statusPillClasses = [
    'approved' => 'bg-emerald-500 text-white',
    'pending' => 'bg-amber-100 text-amber-700',
    'draft' => 'bg-slate-200 text-slate-700',
    'rejected' => 'bg-rose-100 text-rose-700',
    'archived' => 'bg-[#f2dce6] text-[#ab1155]',
];
$visibilityLabels = [
    'public' => 'Público',
    'subscriber' => 'Assinantes',
    'premium' => 'Plano vinculado',
];
$kindLabels = [
    'gallery' => 'Galeria',
    'video' => 'Vídeo',
    'audio' => 'Áudio',
    'article' => 'Artigo',
    'live_teaser' => 'Live',
];
$storageUsedBytes = (int) ($summary['storage_used_bytes'] ?? 0);
$storageLimitBytes = (int) ($summary['storage_limit_bytes'] ?? 524288000);
$storageRemainingBytes = max(0, (int) ($summary['storage_remaining_bytes'] ?? ($storageLimitBytes - $storageUsedBytes)));
$storagePercent = (float) ($summary['storage_percent'] ?? 0);
$selectedContentPayload = null;

if ($formItem) {
    $selectedContentPayload = [
        'id' => (int) ($formItem['id'] ?? 0),
        'title' => (string) ($formItem['title'] ?? ''),
        'duration' => (string) ($formItem['duration'] ?? ''),
        'kind' => (string) ($formItem['kind'] ?? 'gallery'),
        'visibility' => (string) ($formItem['visibility'] ?? 'subscriber'),
        'status' => (string) ($formItem['status'] ?? 'pending'),
        'media_url' => (string) ($formItem['media_url'] ?? ''),
        'excerpt' => (string) ($formItem['excerpt'] ?? ''),
        'body' => (string) ($formItem['body'] ?? ''),
        'plan_id' => (int) ($formItem['plan_id'] ?? 0),
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SexyLua - Meu Conte&uacute;do</title>
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
$creatorShellCreator = $creator;
$creatorShellCurrent = 'content';
$creatorTopbarLabel = 'Meu Conte&uacute;do';
$creatorTopbarAction = ['href' => '/creator/live', 'label' => 'Go Live'];
include base_path('templates/partials/creator_sidebar.php');
include base_path('templates/partials/creator_topbar.php');
?>

<main class="px-6 pb-12 pt-24 lg:ml-64 lg:px-10">
    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#D81B60]">Conte&uacute;do do criador</p>
            <h1 class="headline mt-2 text-4xl font-extrabold">Meu conte&uacute;do</h1>
            <p class="mt-3 max-w-3xl text-slate-500">Gerencie posts e arquivos reais do seu perfil com a cota correta de armazenamento.</p>
        </div>
        <button class="signature-glow inline-flex w-full items-center justify-center gap-2 rounded-full px-6 py-3 text-sm font-bold text-white xl:w-auto" data-content-open="new" type="button">
            <span class="material-symbols-outlined text-lg">add</span>
            Novo conte&uacute;do
        </button>
    </div>

    <section class="mt-8 grid grid-cols-1 gap-4 xl:grid-cols-[repeat(3,minmax(0,0.8fr))_minmax(0,1.6fr)]">
        <div class="rounded-3xl bg-white p-5 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Total de posts</p>
            <p class="headline mt-3 text-4xl font-extrabold text-slate-900"><?= e((string) ((int) ($summary['total_posts'] ?? 0))) ?></p>
            <p class="mt-2 text-sm text-slate-500"><?= e((string) ((int) ($counts['pending'] ?? 0))) ?> pendentes</p>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Visualiza&ccedil;&otilde;es</p>
            <p class="headline mt-3 text-4xl font-extrabold text-slate-900"><?= e(number_format((int) ($summary['estimated_views'] ?? 0), 0, ',', '.')) ?></p>
            <p class="mt-2 text-sm text-slate-500">Estimativa com base nos salvos reais.</p>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Dispon&iacute;vel</p>
            <p class="headline mt-3 text-3xl font-extrabold text-[#D81B60]"><?= e(human_file_size($storageRemainingBytes)) ?></p>
            <p class="mt-2 text-sm text-slate-500">Limite total de <?= e(human_file_size($storageLimitBytes, 0)) ?> por criador.</p>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Uso de armazenamento</p>
                    <p class="headline mt-3 text-3xl font-extrabold text-slate-900"><?= e(human_file_size($storageUsedBytes)) ?></p>
                </div>
                <div class="rounded-full bg-[#f7f4f7] px-4 py-2 text-xs font-bold uppercase tracking-[0.22em] text-slate-500"><?= e(number_format($storagePercent, 0, ',', '.')) ?>%</div>
            </div>
            <div class="mt-5 h-3 overflow-hidden rounded-full bg-[#f1edf1]">
                <div class="signature-glow h-full rounded-full" style="width: <?= e((string) min(100, max(0, $storagePercent))) ?>%;"></div>
            </div>
            <p class="mt-3 text-sm text-slate-500">O c&aacute;lculo usa o tamanho real dos arquivos salvos em seu perfil.</p>
        </div>
    </section>

    <section class="mt-8 rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] lg:p-8">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]">Biblioteca</p>
                <h2 class="headline mt-2 text-2xl font-extrabold">Filtros do conte&uacute;do</h2>
            </div>
            <form action="/creator/content" class="grid w-full grid-cols-1 gap-3 xl:max-w-5xl xl:grid-cols-[minmax(0,1.4fr)_minmax(0,0.7fr)_minmax(0,0.7fr)_auto_auto]" method="get">
                <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="q" placeholder="Buscar posts..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
                <select class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="status">
                    <option value="">Todos os status</option>
                    <?php foreach ($statusLabels as $statusValue => $statusLabel): ?>
                        <option value="<?= e($statusValue) ?>" <?= (string) ($filters['status'] ?? '') === $statusValue ? 'selected' : '' ?>><?= e($statusLabel) ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="kind">
                    <option value="">Todos os tipos</option>
                    <?php foreach ($kindLabels as $kindValue => $kindLabel): ?>
                        <option value="<?= e($kindValue) ?>" <?= (string) ($filters['kind'] ?? '') === $kindValue ? 'selected' : '' ?>><?= e($kindLabel) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" type="submit">Filtrar</button>
                <a class="rounded-full bg-[#f5f3f5] px-6 py-4 text-center text-sm font-bold text-slate-600" href="/creator/content">Reset</a>
            </form>
        </div>
    </section>

    <section class="mt-8 rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] lg:p-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]">Itens publicados</p>
                <h2 class="headline mt-2 text-2xl font-extrabold">Sua biblioteca</h2>
            </div>
            <div class="rounded-full bg-[#f7f4f7] px-4 py-2 text-xs font-bold uppercase tracking-[0.22em] text-slate-500"><?= e((string) count($items)) ?> itens</div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <?php foreach ($items as $item): ?>
                <?php
                $itemId = (int) ($item['id'] ?? 0);
                $status = (string) ($item['status'] ?? 'draft');
                $visibility = (string) ($item['visibility'] ?? 'public');
                $kind = (string) ($item['kind'] ?? 'gallery');
                $mediaUrl = media_url((string) ($item['media_url'] ?? ''));
                $thumbnailUrl = media_url((string) ($item['thumbnail_url'] ?? ''));
                $coverUrl = $thumbnailUrl !== '' ? $thumbnailUrl : $mediaUrl;
                $plan = is_array($item['plan'] ?? null) ? $item['plan'] : null;
                $itemBytes = max(
                    public_media_file_bytes((string) ($item['media_url'] ?? '')),
                    (int) ($item['media_bytes'] ?? 0)
                ) + max(
                    public_media_file_bytes((string) ($item['thumbnail_url'] ?? '')),
                    (int) ($item['thumbnail_bytes'] ?? 0)
                );
                $contentPayload = [
                    'id' => $itemId,
                    'title' => (string) ($item['title'] ?? ''),
                    'duration' => (string) ($item['duration'] ?? ''),
                    'kind' => $kind,
                    'visibility' => $visibility,
                    'status' => $status,
                    'media_url' => (string) ($item['media_url'] ?? ''),
                    'excerpt' => (string) ($item['excerpt'] ?? ''),
                    'body' => (string) ($item['body'] ?? ''),
                    'plan_id' => (int) ($item['plan_id'] ?? 0),
                ];
                $archiveStatus = $status === 'archived' ? 'approved' : 'archived';
                ?>
                <article class="overflow-hidden rounded-3xl bg-[#fbf9fb] shadow-sm ring-1 ring-[#f0e8ee] transition-transform hover:-translate-y-1">
                    <div class="relative aspect-[4/5] bg-slate-950">
                        <?php if ($coverUrl !== ''): ?>
                            <?php if ($kind === 'video' || $kind === 'live_teaser'): ?>
                                <video class="h-full w-full object-cover" muted playsinline preload="metadata" src="<?= e($mediaUrl !== '' ? $mediaUrl : $coverUrl) ?>"></video>
                            <?php else: ?>
                                <img alt="<?= e((string) ($item['title'] ?? 'Conte&uacute;do')) ?>" class="h-full w-full object-cover" src="<?= e($coverUrl) ?>">
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="signature-glow flex h-full w-full items-center justify-center text-white">
                                <span class="headline text-2xl font-extrabold"><?= e(mb_strtoupper(mb_substr($kindLabels[$kind] ?? 'POST', 0, 10))) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="absolute inset-x-0 top-0 flex items-center justify-between gap-3 p-4">
                            <span class="<?= e($statusPillClasses[$status] ?? 'bg-slate-200 text-slate-700') ?> rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em]"><?= e($statusLabels[$status] ?? 'Rascunho') ?></span>
                            <span class="rounded-full bg-black/45 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em] text-white"><?= e($visibilityLabels[$visibility] ?? 'P&uacute;blico') ?></span>
                        </div>
                        <?php if ($kind === 'video' || $kind === 'live_teaser'): ?>
                            <div class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center">
                                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-black/55 text-white shadow-lg">
                                    <span class="material-symbols-outlined text-3xl">play_arrow</span>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if ((bool) ($item['auto_generated'] ?? false) && $mediaUrl !== ''): ?>
                            <a class="absolute right-4 top-14 flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-[#ab1155] shadow-lg" href="<?= e($mediaUrl) ?>" target="_blank" title="Assistir replay">
                                <span class="material-symbols-outlined text-xl">play_arrow</span>
                            </a>
                        <?php endif; ?>
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent p-4 text-white">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-white/70"><?= e($kindLabels[$kind] ?? 'Conte&uacute;do') ?></p>
                            <p class="headline mt-2 text-xl font-extrabold"><?= e((string) ($item['title'] ?? 'Novo conte&uacute;do')) ?></p>
                            <div class="mt-2 flex items-center justify-between gap-3 text-xs text-white/80">
                                <span><?= e((string) ($item['duration'] ?? 'Sem dura&ccedil;&atilde;o')) ?></span>
                                <span><?= e(human_file_size($itemBytes > 0 ? $itemBytes : 0)) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4 p-5">
                        <p class="min-h-[48px] text-sm text-slate-500"><?= e(excerpt((string) ($item['excerpt'] ?? ''), 90)) ?></p>
                        <div class="flex min-h-[2rem] flex-wrap items-center gap-2">
                            <?php if ($plan): ?>
                                <span class="rounded-full bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-[0.22em] text-[#ab1155]"><?= e((string) ($plan['name'] ?? 'Plano')) ?></span>
                            <?php endif; ?>
                            <span class="rounded-full bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500"><?= e((string) ($item['saved_count'] ?? 0)) ?> salvos</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-t border-[#f0e8ee] pt-4">
                            <button class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-slate-700 shadow-sm transition hover:bg-[#f7f4f7]" data-content-open="edit" data-content="<?= e((string) json_encode($contentPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>" title="Editar conte&uacute;do" type="button">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>
                            <form action="/creator/content/status" method="post">
                                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                <input name="content_id" type="hidden" value="<?= e((string) $itemId) ?>">
                                <input name="status" type="hidden" value="<?= e($archiveStatus) ?>">
                                <input name="redirect" type="hidden" value="/creator/content">
                                <button class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-slate-700 shadow-sm transition hover:bg-[#f7f4f7]" title="<?= e($status === 'archived' ? 'Desarquivar' : 'Arquivar') ?>" type="submit">
                                    <span class="material-symbols-outlined text-[20px]"><?= e($status === 'archived' ? 'unarchive' : 'archive') ?></span>
                                </button>
                            </form>
                            <form action="/creator/content/delete" method="post" onsubmit="return confirm('Excluir este conte&uacute;do?');">
                                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                <input name="content_id" type="hidden" value="<?= e((string) $itemId) ?>">
                                <input name="redirect" type="hidden" value="/creator/content">
                                <button class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-rose-600 shadow-sm transition hover:bg-rose-50" title="Excluir conte&uacute;do" type="submit">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>

            <?php if ($items === []): ?>
                <div class="rounded-3xl bg-[#fbf9fb] p-10 text-center text-sm text-slate-500 ring-1 ring-[#f0e8ee] md:col-span-2 xl:col-span-4">
                    Voc&ecirc; ainda n&atilde;o publicou nenhum conte&uacute;do.
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<div class="hidden fixed inset-0 z-[80] flex items-start justify-center overflow-y-auto bg-slate-950/45 px-4 py-8" data-content-modal>
    <div class="my-auto w-full max-w-4xl rounded-3xl bg-white p-6 shadow-[0px_30px_80px_rgba(27,28,29,0.18)] sm:p-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h3 class="headline text-2xl font-extrabold" data-content-modal-title><?= e($modalTitle) ?></h3>
                <p class="mt-2 text-sm text-slate-500">Cadastre galerias, v&iacute;deos, &aacute;udios e artigos da sua &aacute;rea de criador.</p>
            </div>
            <button class="rounded-full bg-[#f5f3f5] px-5 py-3 text-sm font-bold text-slate-600" data-content-close type="button">Fechar</button>
        </div>

        <form action="/creator/content/save" class="mt-6 space-y-4" enctype="multipart/form-data" method="post" data-content-form>
            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
            <input data-content-field="id" name="id" type="hidden" value="<?= e((string) $formItemId) ?>">

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" data-content-field="title" name="title" placeholder="T&iacute;tulo do conte&uacute;do" required type="text" value="<?= e((string) ($formItem['title'] ?? '')) ?>">
                <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" data-content-field="duration" name="duration" placeholder="Dura&ccedil;&atilde;o (ex.: 12:45)" type="text" value="<?= e((string) ($formItem['duration'] ?? '')) ?>">
                <select class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" data-content-field="kind" name="kind">
                    <?php foreach ($kindLabels as $kindValue => $kindLabel): ?>
                        <option value="<?= e($kindValue) ?>" <?= (string) ($formItem['kind'] ?? 'gallery') === $kindValue ? 'selected' : '' ?>><?= e($kindLabel) ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" data-content-field="visibility" name="visibility">
                    <?php foreach ($visibilityLabels as $visibilityValue => $visibilityLabel): ?>
                        <option value="<?= e($visibilityValue) ?>" <?= (string) ($formItem['visibility'] ?? 'subscriber') === $visibilityValue ? 'selected' : '' ?>><?= e($visibilityLabel) ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" data-content-field="status" name="status">
                    <?php foreach ($statusLabels as $statusValue => $statusLabel): ?>
                        <option value="<?= e($statusValue) ?>" <?= (string) ($formItem['status'] ?? 'pending') === $statusValue ? 'selected' : '' ?>><?= e($statusLabel) ?></option>
                    <?php endforeach; ?>
                </select>
                <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" data-content-field="media_url" name="media_url" placeholder="URL da m&iacute;dia (opcional)" type="url" value="<?= e((string) ($formItem['media_url'] ?? '')) ?>">
            </div>

            <label class="block space-y-2">
                <span class="text-sm font-semibold text-slate-700">Plano vinculado</span>
                <select class="w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" data-content-field="plan_id" name="plan_id">
                    <option value="">Sem plano vinculado</option>
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?= e((string) ((int) ($plan['id'] ?? 0))) ?>" <?= (int) ($formItem['plan_id'] ?? 0) === (int) ($plan['id'] ?? 0) ? 'selected' : '' ?>>
                            <?= e((string) ($plan['name'] ?? 'Plano')) ?> - <?= e(luacoin_value((int) ($plan['price_tokens'] ?? 0))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-slate-500">Use um plano real j&aacute; criado para liberar este conte&uacute;do via assinatura.</p>
            </label>

            <textarea class="min-h-[96px] w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" data-content-field="excerpt" name="excerpt" placeholder="Resumo"><?= e((string) ($formItem['excerpt'] ?? '')) ?></textarea>
            <textarea class="min-h-[140px] w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" data-content-field="body" name="body" placeholder="Descri&ccedil;&atilde;o completa"><?= e((string) ($formItem['body'] ?? '')) ?></textarea>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <label class="block space-y-2 rounded-2xl bg-[#f5f3f5] p-4">
                    <span class="text-sm font-semibold text-slate-700">Arquivo principal</span>
                    <p class="text-xs text-slate-500">Envie o v&iacute;deo, imagem, &aacute;udio ou arquivo principal do post.</p>
                    <input class="w-full rounded-2xl border-none bg-white px-5 py-4 file:mr-4 file:rounded-full file:border-0 file:bg-[#D81B60] file:px-4 file:py-2 file:text-sm file:font-bold file:text-white" name="media_file" type="file">
                </label>
                <label class="block space-y-2 rounded-2xl bg-[#f5f3f5] p-4">
                    <span class="text-sm font-semibold text-slate-700">Capa / thumbnail</span>
                    <p class="text-xs text-slate-500">Opcional. Use uma imagem de capa para destacar o conte&uacute;do na grade p&uacute;blica.</p>
                    <input class="w-full rounded-2xl border-none bg-white px-5 py-4 file:mr-4 file:rounded-full file:border-0 file:bg-[#D81B60] file:px-4 file:py-2 file:text-sm file:font-bold file:text-white" name="thumbnail_file" type="file">
                </label>
            </div>

            <div class="rounded-2xl bg-[#f5f3f5] p-4 text-sm text-slate-600">
                <p class="font-bold text-slate-800">Espa&ccedil;o restante</p>
                <p class="mt-2">Voc&ecirc; tem <?= e(human_file_size($storageRemainingBytes)) ?> livres de <?= e(human_file_size($storageLimitBytes, 0)) ?> para publicar novos arquivos.</p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <button class="rounded-full bg-[#f5f3f5] px-6 py-4 text-center text-sm font-bold text-slate-600" data-content-close type="button">Cancelar</button>
                <button class="signature-glow rounded-full px-8 py-4 text-sm font-bold text-white" type="submit"><?= e($submitLabel) ?></button>
            </div>
        </form>
    </div>
</div>

<?php if ($selectedContentPayload !== null): ?>
    <script id="content-selected-data" type="application/json"><?= e((string) json_encode($selectedContentPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></script>
<?php endif; ?>
<script>
    (() => {
        const modal = document.querySelector('[data-content-modal]');
        const form = document.querySelector('[data-content-form]');
        if (!modal || !form) return;

        const closeUrl = <?= json_encode($closeModalUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const titleNode = modal.querySelector('[data-content-modal-title]');
        const emptyContent = {
            id: '',
            title: '',
            duration: '',
            kind: 'gallery',
            visibility: 'subscriber',
            status: 'pending',
            media_url: '',
            excerpt: '',
            body: '',
            plan_id: '',
        };
        const fields = {
            id: form.querySelector('[data-content-field="id"]'),
            title: form.querySelector('[data-content-field="title"]'),
            duration: form.querySelector('[data-content-field="duration"]'),
            kind: form.querySelector('[data-content-field="kind"]'),
            visibility: form.querySelector('[data-content-field="visibility"]'),
            status: form.querySelector('[data-content-field="status"]'),
            media_url: form.querySelector('[data-content-field="media_url"]'),
            excerpt: form.querySelector('[data-content-field="excerpt"]'),
            body: form.querySelector('[data-content-field="body"]'),
            plan_id: form.querySelector('[data-content-field="plan_id"]'),
        };

        const applyContent = (content) => {
            const payload = { ...emptyContent, ...(content || {}) };
            Object.entries(fields).forEach(([key, field]) => {
                if (!field) return;
                field.value = payload[key] ?? '';
            });
            titleNode.textContent = payload.id ? 'Editar conte\u00fado' : 'Novo conte\u00fado';
        };

        const openModal = (content) => {
            applyContent(content);
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            if (window.location.search.includes('edit=') || window.location.search.includes('open_form=')) {
                window.history.replaceState({}, '', closeUrl);
            }
        };

        document.querySelectorAll('[data-content-open]').forEach((button) => {
            button.addEventListener('click', () => {
                const mode = button.getAttribute('data-content-open');
                if (mode === 'edit') {
                    try {
                        openModal(JSON.parse(button.getAttribute('data-content') || '{}'));
                        return;
                    } catch (error) {
                    }
                }
                openModal(emptyContent);
            });
        });

        modal.querySelectorAll('[data-content-close]').forEach((button) => button.addEventListener('click', closeModal));
        modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });
        document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });

        const selectedNode = document.getElementById('content-selected-data');
        if (selectedNode) {
            try {
                openModal(JSON.parse(selectedNode.textContent || '{}'));
            } catch (error) {
            }
        } else if (<?= $openForm ? 'true' : 'false' ?> && <?= json_encode($formMode, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?> === 'new') {
            openModal(emptyContent);
        }
    })();
</script>
</body>
</html>
