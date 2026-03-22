<?php

declare(strict_types=1);

$creator = $data['creator'] ?? [];
$items = $data['filtered_items'] ?? $data['items'] ?? [];
$selectedItem = $data['selected_item'] ?? null;
$filters = $data['filters'] ?? [];
$counts = $data['counts'] ?? [];
$summary = $data['summary'] ?? [];
$statuses = [
    'draft' => 'Rascunho',
    'pending' => 'Pendente',
    'approved' => 'Publicado',
    'rejected' => 'Rejeitado',
    'archived' => 'Arquivado',
];
$kinds = [
    'gallery' => 'Galeria',
    'video' => 'Video',
    'audio' => 'Audio',
    'article' => 'Artigo',
    'live_teaser' => 'Teaser Live',
];
$visibilities = [
    'public' => 'Publico',
    'subscriber' => 'Assinantes',
    'premium' => 'Premium',
];
$redirectBase = path_with_query('/creator/content', [
    'q' => $filters['q'] ?? '',
    'status' => $filters['status'] ?? '',
    'kind' => $filters['kind'] ?? '',
]);
$selectedStatus = (string) ($selectedItem['status'] ?? 'draft');
$selectedKind = (string) ($selectedItem['kind'] ?? 'gallery');
$selectedVisibility = (string) ($selectedItem['visibility'] ?? 'subscriber');
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Gestao de Conteudo</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#D81B60",
                        background: "#fbf9fb",
                        surface: "#fbf9fb",
                        "surface-container": "#efedef",
                        "surface-container-low": "#f5f3f5",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-high": "#e9e7e9",
                        "outline-variant": "#e3bdc3",
                        "on-surface": "#1b1c1d",
                        "on-surface-variant": "#5a4044",
                        "secondary-container": "#fd6c9c",
                        "tertiary-fixed": "#eddcff",
                        "tertiary": "#6c3eaf",
                    },
                    fontFamily: {
                        headline: ["Plus Jakarta Sans"],
                        body: ["Manrope"],
                    },
                    borderRadius: {
                        DEFAULT: "1rem",
                        lg: "2rem",
                        xl: "3rem",
                        full: "9999px",
                    },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24;
        }
        body {
            font-family: "Manrope", sans-serif;
        }
        h1, h2, h3, h4 {
            font-family: "Plus Jakarta Sans", sans-serif;
        }
        .signature-glow {
            background: linear-gradient(135deg, #D81B60 0%, #ab1155 100%);
        }
    </style>
</head>
<body class="bg-surface text-on-surface">
<?php
ob_start();
?>
<form action="/creator/content" class="hidden items-center gap-4 lg:flex" method="get">
    <div class="relative">
        <input class="w-72 rounded-full border-none bg-white/10 px-5 py-2 pr-12 text-sm text-white outline-none placeholder:text-white/70 focus:ring-1 focus:ring-white/40" name="q" placeholder="Buscar posts..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
        <span class="material-symbols-outlined absolute right-4 top-2 text-white/70">search</span>
    </div>
    <?php if (($filters['status'] ?? '') !== ''): ?>
        <input name="status" type="hidden" value="<?= e((string) $filters['status']) ?>">
    <?php endif; ?>
    <?php if (($filters['kind'] ?? '') !== ''): ?>
        <input name="kind" type="hidden" value="<?= e((string) $filters['kind']) ?>">
    <?php endif; ?>
</form>
<?php
$creatorTopbarSearch = (string) ob_get_clean();
$creatorShellCreator = $creator;
$creatorShellCurrent = 'content';
$creatorShellCta = ['href' => '#content-editor', 'label' => $selectedItem ? 'Editar Conteudo' : 'Novo Conteudo', 'icon' => 'edit_square'];
$creatorTopbarLabel = 'Gestao de Conteudo';
$creatorTopbarAction = ['href' => '/creator/live', 'label' => 'Go Live'];
include base_path('templates/partials/creator_sidebar.php');
include base_path('templates/partials/creator_topbar.php');
?>
<main class="min-h-screen pt-16 lg:ml-64">
    <section class="px-12 py-8">
        <header class="mb-10 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#D81B60]">Creator Studio</p>
                <h2 class="mt-3 text-4xl font-extrabold tracking-tight">Gestao de Conteudo</h2>
                <p class="mt-3 max-w-2xl text-on-surface-variant">Cadastre, edite, publique e arquive seus drops sem sair do layout do criador.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a class="rounded-full bg-surface-container-lowest px-6 py-3 text-sm font-bold shadow-sm" href="/creator/content">Limpar filtros</a>
                <a class="signature-glow rounded-full px-8 py-3 text-sm font-bold text-white shadow-[0px_20px_40px_rgba(171,17,85,0.18)]" href="#content-editor">Abrir editor</a>
            </div>
        </header>

        <div class="mb-10 grid grid-cols-1 gap-6 md:grid-cols-4">
            <div class="rounded-xl bg-surface-container-lowest p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.04)]">
                <span class="block text-sm font-bold text-[#D81B60]">Total de Posts</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold"><?= e((string) ($summary['total_posts'] ?? 0)) ?></span>
                    <span class="text-xs font-bold text-emerald-600"><?= e((string) (($counts['pending'] ?? 0) > 0 ? '+' . (int) ($counts['pending'] ?? 0) . ' pend.' : 'estavel')) ?></span>
                </div>
            </div>
            <div class="rounded-xl bg-surface-container-lowest p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.04)]">
                <span class="block text-sm font-bold text-[#D81B60]">Visualizacoes</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold"><?= e(number_format((int) ($summary['estimated_views'] ?? 0), 0, ',', '.')) ?></span>
                    <span class="text-xs font-bold text-emerald-600">demo real</span>
                </div>
            </div>
            <div class="rounded-xl bg-surface-container-low p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.04)] md:col-span-2">
                <div class="flex items-center justify-between gap-6">
                    <div>
                        <span class="block text-sm font-bold text-on-surface-variant">Espaco estimado</span>
                        <div class="mt-3 h-2 w-48 rounded-full bg-surface-container-high">
                            <div class="signature-glow h-full rounded-full" style="width: <?= e((string) min(100, max(8, (((float) ($summary['storage_gb'] ?? 0.2)) / 10) * 100))) ?>%"></div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-bold"><?= e(number_format((float) ($summary['storage_gb'] ?? 0.2), 1, ',', '.')) ?> GB</span>
                        <span class="block text-xs text-slate-500">de 10 GB</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 xl:grid-cols-[1.05fr_1.45fr]">
            <section class="rounded-2xl bg-surface-container-lowest p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]" id="content-editor">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]"><?= $selectedItem ? 'Editar item' : 'Novo item' ?></p>
                        <h3 class="mt-2 text-2xl font-extrabold"><?= $selectedItem ? e((string) ($selectedItem['title'] ?? 'Conteudo')) : 'Cadastro rapido' ?></h3>
                    </div>
                    <?php if ($selectedItem): ?>
                        <a class="rounded-full bg-surface-container-low px-4 py-2 text-xs font-bold uppercase tracking-widest text-on-surface-variant" href="/creator/content">Novo</a>
                    <?php endif; ?>
                </div>

                <form action="/creator/content/save" class="space-y-5" enctype="multipart/form-data" method="post">
                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                    <?php if ($selectedItem): ?>
                        <input name="id" type="hidden" value="<?= e((string) ($selectedItem['id'] ?? 0)) ?>">
                    <?php endif; ?>

                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block space-y-2 md:col-span-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Titulo</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="title" required type="text" value="<?= e((string) ($selectedItem['title'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2 md:col-span-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Resumo</span>
                            <textarea class="min-h-[110px] w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="excerpt"><?= e((string) ($selectedItem['excerpt'] ?? '')) ?></textarea>
                        </label>
                        <label class="block space-y-2 md:col-span-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Descricao longa</span>
                            <textarea class="min-h-[160px] w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="body"><?= e((string) ($selectedItem['body'] ?? '')) ?></textarea>
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Tipo</span>
                            <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="kind">
                                <?php foreach ($kinds as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= $selectedKind === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Visibilidade</span>
                            <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="visibility">
                                <?php foreach ($visibilities as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= $selectedVisibility === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Status</span>
                            <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="status">
                                <?php foreach ($statuses as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= $selectedStatus === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Duracao</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="duration" placeholder="Ex: 04:12" type="text" value="<?= e((string) ($selectedItem['duration'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2 md:col-span-2">
                            <span class="text-sm font-semibold text-on-surface-variant">URL da midia</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="media_url" placeholder="https://..." type="url" value="<?= e((string) ($selectedItem['media_url'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Arquivo de midia</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-sm shadow-sm file:mr-4 file:rounded-full file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-bold file:text-white" name="media_file" type="file">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Thumbnail</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-sm shadow-sm file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white" name="thumbnail_file" type="file">
                        </label>
                        <label class="block space-y-2 md:col-span-2">
                            <span class="text-sm font-semibold text-on-surface-variant">URL do thumbnail</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="thumbnail_url" placeholder="https://..." type="url" value="<?= e((string) ($selectedItem['thumbnail_url'] ?? '')) ?>">
                        </label>
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button class="signature-glow flex items-center justify-center gap-2 rounded-full px-7 py-4 text-sm font-bold text-white shadow-[0px_20px_40px_rgba(171,17,85,0.2)]" data-prototype-skip="1" type="submit">
                            <span class="material-symbols-outlined">save</span>
                            <?= $selectedItem ? 'Atualizar conteudo' : 'Salvar conteudo' ?>
                        </button>
                        <a class="rounded-full bg-surface-container-low px-6 py-4 text-sm font-bold text-on-surface-variant" href="/creator/content">Cancelar</a>
                    </div>
                </form>
            </section>

            <section class="space-y-6">
                <form action="/creator/content" class="rounded-2xl bg-surface-container-lowest p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]" method="get">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-[1.2fr_0.6fr_0.6fr_auto]">
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-on-surface-variant">Busca</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="q" placeholder="Titulo, resumo ou descricao" type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-on-surface-variant">Status</span>
                            <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="status">
                                <option value="">Todos</option>
                                <?php foreach ($statuses as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= (string) ($filters['status'] ?? '') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-on-surface-variant">Tipo</span>
                            <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="kind">
                                <option value="">Todos</option>
                                <?php foreach ($kinds as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= (string) ($filters['kind'] ?? '') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <div class="flex items-end gap-3">
                            <button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
                            <a class="rounded-full bg-surface-container-low px-5 py-4 text-sm font-bold text-on-surface-variant" href="/creator/content">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <?php foreach ($items as $item): ?>
                        <?php
                        $preview = media_url((string) (($item['thumbnail_url'] ?? '') !== '' ? $item['thumbnail_url'] : ($item['media_url'] ?? '')));
                        $statusKey = (string) ($item['status'] ?? 'draft');
                        $statusLabel = $statuses[$statusKey] ?? ucfirst($statusKey);
                        $statusClass = match ($statusKey) {
                            'approved' => 'bg-emerald-500/90',
                            'pending' => 'bg-amber-500/90',
                            'rejected' => 'bg-rose-600/90',
                            'archived' => 'bg-slate-600/90',
                            default => 'bg-slate-900/90',
                        };
                        $nextStatus = match ($statusKey) {
                            'draft' => 'pending',
                            'pending' => 'draft',
                            'approved' => 'archived',
                            'archived' => 'approved',
                            'rejected' => 'draft',
                            default => 'pending',
                        };
                        $nextStatusLabel = $statuses[$nextStatus] ?? ucfirst($nextStatus);
                        ?>
                        <article class="overflow-hidden rounded-2xl bg-surface-container-lowest shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                            <div class="relative aspect-[3/4] bg-surface-container">
                                <?php if ($preview !== ''): ?>
                                    <img alt="<?= e((string) ($item['title'] ?? 'Conteudo')) ?>" class="h-full w-full object-cover" src="<?= e($preview) ?>">
                                <?php else: ?>
                                    <div class="signature-glow flex h-full w-full items-center justify-center text-center text-white">
                                        <div>
                                            <span class="material-symbols-outlined text-5xl">perm_media</span>
                                            <p class="mt-3 text-sm font-bold uppercase tracking-[0.25em]"><?= e($kinds[(string) ($item['kind'] ?? 'gallery')] ?? 'Conteudo') ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="absolute left-4 top-4">
                                    <span class="<?= e($statusClass) ?> rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-white"><?= e($statusLabel) ?></span>
                                </div>
                                <div class="absolute bottom-4 right-4 rounded-full bg-black/50 px-3 py-1 text-xs font-bold text-white">
                                    <?= e((string) (($item['duration'] ?? '') !== '' ? $item['duration'] : strtoupper((string) ($item['visibility'] ?? 'PUBLICO')))) ?>
                                </div>
                            </div>
                            <div class="space-y-4 p-5">
                                <div>
                                    <h3 class="truncate text-lg font-bold"><?= e((string) ($item['title'] ?? 'Conteudo')) ?></h3>
                                    <p class="mt-2 text-sm leading-relaxed text-on-surface-variant"><?= e(excerpt((string) ($item['excerpt'] ?? ''), 108)) ?></p>
                                </div>
                                <div class="flex items-center justify-between text-xs font-bold uppercase tracking-widest text-on-surface-variant">
                                    <span><?= e($kinds[(string) ($item['kind'] ?? 'gallery')] ?? 'Conteudo') ?></span>
                                    <span><?= e(number_format(((int) ($item['saved_count'] ?? 0)) * 42, 0, ',', '.')) ?> views</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <a class="rounded-full bg-surface-container-low px-3 py-3 text-center text-xs font-bold text-on-surface" href="<?= e(path_with_query('/creator/content', [
                                        'q' => $filters['q'] ?? '',
                                        'status' => $filters['status'] ?? '',
                                        'kind' => $filters['kind'] ?? '',
                                        'edit' => (int) ($item['id'] ?? 0),
                                    ])) ?>">Editar</a>
                                    <form action="/creator/content/status" method="post">
                                        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                        <input name="content_id" type="hidden" value="<?= e((string) ($item['id'] ?? 0)) ?>">
                                        <input name="status" type="hidden" value="<?= e($nextStatus) ?>">
                                        <input name="redirect" type="hidden" value="<?= e($redirectBase) ?>">
                                        <button class="w-full rounded-full bg-slate-900 px-3 py-3 text-xs font-bold text-white" data-prototype-skip="1" type="submit"><?= e($nextStatusLabel) ?></button>
                                    </form>
                                    <form action="/creator/content/delete" method="post" onsubmit="return confirm('Remover este conteudo?');">
                                        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                        <input name="content_id" type="hidden" value="<?= e((string) ($item['id'] ?? 0)) ?>">
                                        <input name="redirect" type="hidden" value="<?= e($redirectBase) ?>">
                                        <button class="w-full rounded-full bg-rose-50 px-3 py-3 text-xs font-bold text-rose-700" data-prototype-skip="1" type="submit">Excluir</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($items === []): ?>
                    <div class="rounded-2xl border border-dashed border-outline-variant bg-surface-container-low p-12 text-center">
                        <span class="material-symbols-outlined text-5xl text-[#D81B60]">inventory_2</span>
                        <h3 class="mt-4 text-2xl font-extrabold">Nenhum conteudo encontrado</h3>
                        <p class="mt-2 text-on-surface-variant">Ajuste os filtros ou cadastre um novo item no painel ao lado.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </section>
</main>
</body>
</html>
