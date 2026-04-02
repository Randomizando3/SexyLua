<?php

declare(strict_types=1);

$summary = $data['summary'] ?? [];
$filters = $data['filters'] ?? [];
$contentPagination = $data['content_pagination'] ?? ['items' => [], 'page' => 1, 'pages' => 1, 'total' => 0];
$planPagination = $data['plan_pagination'] ?? ['items' => [], 'page' => 1, 'pages' => 1, 'total' => 0];
$microPagination = $data['micro_pagination'] ?? ['items' => [], 'page' => 1, 'pages' => 1, 'total' => 0];
$livePagination = $data['live_pagination'] ?? ['items' => [], 'page' => 1, 'pages' => 1, 'total' => 0];
$contentFilters = $data['content_filters'] ?? [];
$planFilters = $data['plan_filters'] ?? [];
$microFilters = $data['micro_filters'] ?? [];
$liveFilters = $data['live_filters'] ?? [];
$contents = $contentPagination['items'] ?? [];
$plans = $planPagination['items'] ?? [];
$lives = $livePagination['items'] ?? [];
$microcontents = $microPagination['items'] ?? [];
$creators = $data['creators'] ?? [];
$admin = $app->auth->user() ?? [];
$paginationUrl = static function (array $params): string {
    return path_with_query('/admin/operations', $params);
};
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Operacoes</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#ab1155",
                        background: "#fbf9fb",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f5f3f5",
                        "on-surface": "#1b1c1d",
                        "on-surface-variant": "#5a4044",
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
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
        body { background: #fbf9fb; color: #1b1c1d; font-family: "Manrope", sans-serif; }
        h1, h2, h3, h4 { font-family: "Plus Jakarta Sans", sans-serif; }
        .admin-operation-toggle[aria-expanded="true"] .admin-operation-chevron { transform: rotate(180deg); }
    </style>
</head>
<body class="min-h-screen">
<?php
$adminTopbarUser = $admin;
require BASE_PATH . '/templates/partials/admin_topbar.php';
?>

<aside class="fixed left-0 top-16 hidden h-[calc(100vh-64px)] w-64 flex-col bg-[#f5f3f5] p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] lg:flex">
    <nav class="space-y-2">
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin"><span class="material-symbols-outlined">dashboard</span><span>Painel</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/users"><span class="material-symbols-outlined">group</span><span>Usuarios</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/moderation"><span class="material-symbols-outlined">gavel</span><span>Moderacao</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/finance"><span class="material-symbols-outlined">payments</span><span>Financeiro</span></a>
        <a class="flex items-center gap-4 rounded-full bg-white px-4 py-3 font-bold text-primary" href="/admin/operations"><span class="material-symbols-outlined">manufacturing</span><span>Operacoes</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/settings"><span class="material-symbols-outlined">settings</span><span>Configuracoes</span></a>
    </nav>
    <div class="mt-auto rounded-3xl bg-white p-5 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary">Escopo ativo</p>
        <h3 class="mt-3 text-3xl font-extrabold"><?= e((string) (($summary['content_count'] ?? 0) + ($summary['plan_count'] ?? 0) + ($summary['live_count'] ?? 0) + ($summary['microcontent_count'] ?? 0))) ?></h3>
        <p class="mt-2 text-sm text-on-surface-variant">Itens operacionais prontos para edicao manual pelo admin.</p>
    </div>
</aside>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Operacao central</p>
            <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Conteudo, planos, lives e <span class="italic text-primary">microconteudos</span></h2>
            <p class="mt-4 max-w-2xl text-on-surface-variant">Edite diretamente os ativos dos criadores sem sair do painel administrativo.</p>
        </div>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 2xl:grid-cols-5">
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Conteudos</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-primary md:text-3xl"><?= e((string) ($summary['content_count'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Planos</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-primary md:text-3xl"><?= e((string) ($summary['plan_count'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Lives</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-primary md:text-3xl"><?= e((string) ($summary['live_count'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Microconteudos</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-primary md:text-3xl"><?= e((string) ($summary['microcontent_count'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Ao vivo</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-emerald-600 md:text-3xl"><?= e((string) ($summary['live_now'] ?? 0)) ?></p></article>
        </div>
    </section>

    <form action="/admin/operations" class="mb-8 grid grid-cols-1 gap-4 rounded-3xl bg-surface-container-lowest p-6 shadow-sm md:grid-cols-[minmax(0,1fr)_auto_auto]" method="get">
        <select class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="creator_id">
            <option value="0">Todos os criadores</option>
            <?php foreach ($creators as $creator): ?>
                <option value="<?= e((string) ($creator['id'] ?? 0)) ?>" <?= (int) ($filters['creator_id'] ?? 0) === (int) ($creator['id'] ?? 0) ? 'selected' : '' ?>><?= e((string) ($creator['name'] ?? 'Criador')) ?></option>
            <?php endforeach; ?>
        </select>
        <div class="flex flex-wrap items-center gap-3">
            <button class="min-w-[120px] rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
            <a class="min-w-[110px] rounded-full bg-surface-container-low px-6 py-4 text-center text-sm font-bold text-on-surface-variant" href="/admin/operations">Reset</a>
        </div>
    </form>

    <section class="space-y-8">
        <div class="overflow-hidden rounded-3xl bg-surface-container-lowest shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-2xl font-extrabold">Conteudos</h3>
                    <p class="mt-1 text-sm text-on-surface-variant">Resumo operacional dos itens publicados pelos criadores.</p>
                </div>
                <span class="text-sm font-bold text-primary"><?= e((string) ($contentPagination['total'] ?? count($contents))) ?> itens</span>
            </div>
            <form action="/admin/operations" class="grid grid-cols-1 gap-3 border-b border-slate-100 px-6 py-5 md:grid-cols-[minmax(0,1fr)_minmax(0,0.55fr)_auto]" method="get">
                <input name="creator_id" type="hidden" value="<?= e((string) ($filters['creator_id'] ?? 0)) ?>">
                <input class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="content_q" placeholder="Buscar conteudo..." type="search" value="<?= e((string) ($contentFilters['q'] ?? '')) ?>">
                <select class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="content_status">
                    <option value="">Todos os status</option>
                    <option value="draft" <?= (string) ($contentFilters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="pending" <?= (string) ($contentFilters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendente</option>
                    <option value="approved" <?= (string) ($contentFilters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Aprovado</option>
                    <option value="rejected" <?= (string) ($contentFilters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejeitado</option>
                    <option value="archived" <?= (string) ($contentFilters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Arquivado</option>
                </select>
                <div class="flex items-center gap-3">
                    <button class="rounded-full bg-slate-900 px-5 py-3 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
                    <a class="rounded-full bg-surface-container-low px-5 py-3 text-sm font-bold text-on-surface-variant" href="<?= e($paginationUrl(['creator_id' => (int) ($filters['creator_id'] ?? 0)])) ?>">Reset</a>
                </div>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead class="bg-surface-container-low">
                    <tr class="text-left">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Conteudo</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Tipo</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Visibilidade</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Status</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Criado em</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Acoes</th>
                    </tr>
                    </thead>
                    <?php foreach ($contents as $item): ?>
                        <?php
                        $creator = $item['creator'] ?? [];
                        $rowId = 'operation-content-' . (int) ($item['id'] ?? 0);
                        $status = (string) ($item['status'] ?? 'draft');
                        $statusClass = match ($status) {
                            'approved' => 'bg-emerald-100 text-emerald-700',
                            'pending' => 'bg-amber-100 text-amber-700',
                            'rejected' => 'bg-rose-100 text-rose-700',
                            default => 'bg-slate-200 text-slate-600',
                        };
                        ?>
                        <tbody class="border-t border-slate-100 first:border-t-0">
                        <tr class="align-top">
                            <td class="px-6 py-5">
                                <div class="min-w-[18rem]">
                                    <p class="text-base font-extrabold"><?= e((string) ($item['title'] ?? 'Conteudo')) ?></p>
                                    <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($creator['name'] ?? 'Criador')) ?> • @<?= e((string) ($creator['slug'] ?? 'studio')) ?></p>
                                    <p class="mt-1 text-xs text-slate-400"><?= e(excerpt((string) ($item['excerpt'] ?? ''), 90)) ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-sm font-semibold text-on-surface"><?= e((string) ($item['kind'] ?? 'post')) ?></td>
                            <td class="px-6 py-5 text-sm font-semibold text-on-surface"><?= e((string) ($item['visibility'] ?? 'public')) ?></td>
                            <td class="px-6 py-5"><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] <?= e($statusClass) ?>"><?= e($status) ?></span></td>
                            <td class="px-6 py-5 text-sm font-semibold text-on-surface"><?= e(format_datetime((string) ($item['created_at'] ?? ''), 'd/m/Y H:i')) ?></td>
                            <td class="px-6 py-5">
                                <button class="admin-operation-toggle inline-flex items-center gap-2 rounded-full bg-surface-container-low px-4 py-2 text-sm font-bold text-on-surface transition-colors hover:bg-primary/10 hover:text-primary" data-operation-toggle aria-expanded="false" aria-controls="<?= e($rowId) ?>" type="button">
                                    <span>Editar</span>
                                    <span class="admin-operation-chevron material-symbols-outlined text-slate-400 transition-transform">expand_more</span>
                                </button>
                            </td>
                        </tr>
                        <tr class="hidden bg-surface-container-low/60" data-operation-panel id="<?= e($rowId) ?>">
                            <td class="px-6 py-6" colspan="6">
                                <form action="/admin/operations/content/save" class="grid grid-cols-1 gap-6 2xl:grid-cols-[1.2fr_0.8fr]" method="post">
                                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                    <input name="content_id" type="hidden" value="<?= e((string) ($item['id'] ?? 0)) ?>">
                                    <div class="space-y-4">
                                        <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="title" type="text" value="<?= e((string) ($item['title'] ?? '')) ?>">
                                        <textarea class="min-h-28 w-full rounded-3xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="excerpt"><?= e((string) ($item['excerpt'] ?? '')) ?></textarea>
                                        <textarea class="min-h-36 w-full rounded-3xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="body"><?= e((string) ($item['body'] ?? '')) ?></textarea>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <select class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="status">
                                                <?php foreach (['draft' => 'Draft', 'pending' => 'Pendente', 'approved' => 'Aprovado', 'rejected' => 'Rejeitado', 'archived' => 'Arquivado'] as $value => $label): ?>
                                                    <option value="<?= e($value) ?>" <?= (string) ($item['status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="visibility">
                                                <?php foreach (['public' => 'Publico', 'subscriber' => 'Assinante', 'premium' => 'Premium'] as $value => $label): ?>
                                                    <option value="<?= e($value) ?>" <?= (string) ($item['visibility'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="kind">
                                                <?php foreach (['gallery' => 'Galeria', 'video' => 'Video', 'audio' => 'Audio', 'article' => 'Artigo', 'live_teaser' => 'Teaser'] as $value => $label): ?>
                                                    <option value="<?= e($value) ?>" <?= (string) ($item['kind'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="duration" placeholder="Duracao" type="text" value="<?= e((string) ($item['duration'] ?? '')) ?>">
                                        </div>
                                        <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="media_url" placeholder="Media URL" type="text" value="<?= e((string) ($item['media_url'] ?? '')) ?>">
                                        <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="thumbnail_url" placeholder="Thumb URL" type="text" value="<?= e((string) ($item['thumbnail_url'] ?? '')) ?>">
                                        <div class="flex flex-wrap gap-3">
                                            <button class="min-w-[150px] rounded-full bg-primary px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Salvar conteudo</button>
                                            <button class="min-w-[130px] rounded-full bg-rose-100 px-6 py-4 text-sm font-bold text-rose-700" data-prototype-skip="1" formaction="/admin/operations/content/delete" type="submit">Excluir</button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        </tbody>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php if (($contentPagination['pages'] ?? 1) > 1): ?>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-6 py-5 text-sm font-bold">
                    <span class="text-slate-500">Página <?= e((string) ($contentPagination['page'] ?? 1)) ?> de <?= e((string) ($contentPagination['pages'] ?? 1)) ?></span>
                    <div class="flex gap-3">
                        <?php if (($contentPagination['page'] ?? 1) > 1): ?>
                            <a class="rounded-full bg-surface-container-low px-4 py-2 text-on-surface-variant" href="<?= e($paginationUrl(['creator_id' => (int) ($filters['creator_id'] ?? 0), 'content_q' => $contentFilters['q'] ?? '', 'content_status' => $contentFilters['status'] ?? '', 'content_page' => ((int) ($contentPagination['page'] ?? 1) - 1)])) ?>">Anterior</a>
                        <?php endif; ?>
                        <?php if (($contentPagination['page'] ?? 1) < ($contentPagination['pages'] ?? 1)): ?>
                            <a class="rounded-full bg-slate-900 px-4 py-2 text-white" href="<?= e($paginationUrl(['creator_id' => (int) ($filters['creator_id'] ?? 0), 'content_q' => $contentFilters['q'] ?? '', 'content_status' => $contentFilters['status'] ?? '', 'content_page' => ((int) ($contentPagination['page'] ?? 1) + 1)])) ?>">Próxima</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($contents === []): ?><p class="p-8 text-sm text-on-surface-variant">Nenhum conteudo encontrado com esse filtro.</p><?php endif; ?>
        </div>
        <div class="overflow-hidden rounded-3xl bg-surface-container-lowest shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-2xl font-extrabold">Planos</h3>
                    <p class="mt-1 text-sm text-on-surface-variant">Assinaturas e ofertas prontas para ajuste manual.</p>
                </div>
                <span class="text-sm font-bold text-primary"><?= e((string) ($planPagination['total'] ?? count($plans))) ?> planos</span>
            </div>
            <form action="/admin/operations" class="grid grid-cols-1 gap-3 border-b border-slate-100 px-6 py-5 md:grid-cols-[minmax(0,1fr)_minmax(0,0.45fr)_auto]" method="get">
                <input name="creator_id" type="hidden" value="<?= e((string) ($filters['creator_id'] ?? 0)) ?>">
                <input class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="plan_q" placeholder="Buscar plano..." type="search" value="<?= e((string) ($planFilters['q'] ?? '')) ?>">
                <select class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="plan_status">
                    <option value="">Todos os status</option>
                    <option value="active" <?= (string) ($planFilters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Ativos</option>
                    <option value="inactive" <?= (string) ($planFilters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inativos</option>
                </select>
                <div class="flex items-center gap-3">
                    <button class="rounded-full bg-slate-900 px-5 py-3 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
                    <a class="rounded-full bg-surface-container-low px-5 py-3 text-sm font-bold text-on-surface-variant" href="<?= e($paginationUrl(['creator_id' => (int) ($filters['creator_id'] ?? 0)])) ?>">Reset</a>
                </div>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead class="bg-surface-container-low">
                    <tr class="text-left">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Plano</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Preco</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Status</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Assinantes</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Label</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Acoes</th>
                    </tr>
                    </thead>
                    <?php foreach ($plans as $plan): ?>
                        <?php
                        $creator = $plan['creator'] ?? [];
                        $rowId = 'operation-plan-' . (int) ($plan['id'] ?? 0);
                        $statusClass = (bool) ($plan['active'] ?? false) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600';
                        ?>
                        <tbody class="border-t border-slate-100 first:border-t-0">
                        <tr class="align-top">
                            <td class="px-6 py-5">
                                <div class="min-w-[18rem]">
                                    <p class="text-base font-extrabold"><?= e((string) ($plan['name'] ?? 'Plano')) ?></p>
                                    <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($creator['name'] ?? 'Criador')) ?> • <?= e((string) ($creator['email'] ?? '')) ?></p>
                                    <p class="mt-1 text-xs text-slate-400"><?= e(excerpt((string) ($plan['description'] ?? ''), 90)) ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-sm font-extrabold text-primary"><?= luacoin_amount_html((int) ($plan['price_tokens'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?></td>
                            <td class="px-6 py-5"><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] <?= e($statusClass) ?>"><?= (bool) ($plan['active'] ?? false) ? 'Ativo' : 'Inativo' ?></span></td>
                            <td class="px-6 py-5 text-sm font-semibold text-on-surface"><?= e((string) ($plan['subscriber_count'] ?? 0)) ?></td>
                            <td class="px-6 py-5 text-sm font-semibold text-on-surface"><?= e((string) ($plan['label'] ?? '-')) ?></td>
                            <td class="px-6 py-5">
                                <button class="admin-operation-toggle inline-flex items-center gap-2 rounded-full bg-surface-container-low px-4 py-2 text-sm font-bold text-on-surface transition-colors hover:bg-primary/10 hover:text-primary" data-operation-toggle aria-expanded="false" aria-controls="<?= e($rowId) ?>" type="button">
                                    <span>Editar</span>
                                    <span class="admin-operation-chevron material-symbols-outlined text-slate-400 transition-transform">expand_more</span>
                                </button>
                            </td>
                        </tr>
                        <tr class="hidden bg-surface-container-low/60" data-operation-panel id="<?= e($rowId) ?>">
                            <td class="px-6 py-6" colspan="6">
                                <form action="/admin/operations/plan/save" class="grid grid-cols-1 gap-6 2xl:grid-cols-[1.15fr_0.85fr]" method="post">
                                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                    <input name="plan_id" type="hidden" value="<?= e((string) ($plan['id'] ?? 0)) ?>">
                                    <div class="space-y-4">
                                        <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="name" type="text" value="<?= e((string) ($plan['name'] ?? '')) ?>">
                                        <textarea class="min-h-28 w-full rounded-3xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="description"><?= e((string) ($plan['description'] ?? '')) ?></textarea>
                                        <textarea class="min-h-24 w-full rounded-3xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="perks"><?= e(implode(PHP_EOL, (array) ($plan['perks'] ?? []))) ?></textarea>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="1" name="price_luacoins" step="1" type="number" value="<?= e((string) ($plan['price_tokens'] ?? 0)) ?>">
                                            <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="label" placeholder="Label" type="text" value="<?= e((string) ($plan['label'] ?? '')) ?>">
                                        </div>
                                        <label class="flex items-center gap-3 rounded-2xl bg-white px-5 py-4 text-sm font-bold text-on-surface">
                                            <input class="rounded border-slate-300 text-primary focus:ring-primary/20" name="active" type="checkbox" value="1" <?= (bool) ($plan['active'] ?? false) ? 'checked' : '' ?>>
                                            <span>Plano ativo para novas assinaturas</span>
                                        </label>
                                        <p class="rounded-2xl bg-white px-5 py-4 text-sm text-on-surface-variant"><?= e((string) ($plan['subscriber_count'] ?? 0)) ?> assinantes ativos neste plano.</p>
                                        <div class="flex flex-wrap gap-3">
                                            <button class="min-w-[130px] rounded-full bg-primary px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Salvar plano</button>
                                            <button class="min-w-[120px] rounded-full bg-rose-100 px-6 py-4 text-sm font-bold text-rose-700" data-prototype-skip="1" formaction="/admin/operations/plan/delete" type="submit">Excluir</button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        </tbody>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php if (($planPagination['pages'] ?? 1) > 1): ?>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-6 py-5 text-sm font-bold">
                    <span class="text-slate-500">Página <?= e((string) ($planPagination['page'] ?? 1)) ?> de <?= e((string) ($planPagination['pages'] ?? 1)) ?></span>
                    <div class="flex gap-3">
                        <?php if (($planPagination['page'] ?? 1) > 1): ?>
                            <a class="rounded-full bg-surface-container-low px-4 py-2 text-on-surface-variant" href="<?= e($paginationUrl(['creator_id' => (int) ($filters['creator_id'] ?? 0), 'plan_q' => $planFilters['q'] ?? '', 'plan_status' => $planFilters['status'] ?? '', 'plan_page' => ((int) ($planPagination['page'] ?? 1) - 1)])) ?>">Anterior</a>
                        <?php endif; ?>
                        <?php if (($planPagination['page'] ?? 1) < ($planPagination['pages'] ?? 1)): ?>
                            <a class="rounded-full bg-slate-900 px-4 py-2 text-white" href="<?= e($paginationUrl(['creator_id' => (int) ($filters['creator_id'] ?? 0), 'plan_q' => $planFilters['q'] ?? '', 'plan_status' => $planFilters['status'] ?? '', 'plan_page' => ((int) ($planPagination['page'] ?? 1) + 1)])) ?>">Próxima</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($plans === []): ?><p class="p-8 text-sm text-on-surface-variant">Nenhum plano encontrado com esse filtro.</p><?php endif; ?>
        </div>
        <div class="overflow-hidden rounded-3xl bg-surface-container-lowest shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-2xl font-extrabold">Microconteudos</h3>
                    <p class="mt-1 text-sm text-on-surface-variant">Vendas internas disparadas no chat entre criadores e assinantes.</p>
                </div>
                <span class="text-sm font-bold text-primary"><?= e((string) ($microPagination['total'] ?? count($microcontents))) ?> microconteudos</span>
            </div>
            <form action="/admin/operations" class="grid grid-cols-1 gap-3 border-b border-slate-100 px-6 py-5 md:grid-cols-[minmax(0,1fr)_minmax(0,0.5fr)_auto]" method="get">
                <input name="creator_id" type="hidden" value="<?= e((string) ($filters['creator_id'] ?? 0)) ?>">
                <input class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="micro_q" placeholder="Buscar microconteudo..." type="search" value="<?= e((string) ($microFilters['q'] ?? '')) ?>">
                <select class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="micro_status">
                    <option value="">Todos os status</option>
                    <option value="pending" <?= (string) ($microFilters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Aguardando</option>
                    <option value="unlocked" <?= (string) ($microFilters['status'] ?? '') === 'unlocked' ? 'selected' : '' ?>>Desbloqueado</option>
                </select>
                <div class="flex items-center gap-3">
                    <button class="rounded-full bg-slate-900 px-5 py-3 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
                    <a class="rounded-full bg-surface-container-low px-5 py-3 text-sm font-bold text-on-surface-variant" href="<?= e($paginationUrl(['creator_id' => (int) ($filters['creator_id'] ?? 0)])) ?>">Reset</a>
                </div>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead class="bg-surface-container-low">
                    <tr class="text-left">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Microconteudo</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Criador</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Assinante</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Valor</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Status</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Enviado em</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Acoes</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($microcontents as $microcontent): ?>
                        <?php
                        $creator = $microcontent['creator'] ?? [];
                        $subscriber = $microcontent['subscriber'] ?? [];
                        $statusClass = (string) ($microcontent['unlock_status'] ?? 'pending') === 'unlocked'
                            ? 'bg-emerald-100 text-emerald-700'
                            : 'bg-amber-100 text-amber-700';
                        $attachment = $microcontent['attachment'] ?? [];
                        ?>
                        <tr class="border-t border-slate-100 first:border-t-0">
                            <td class="px-6 py-5">
                                <div class="min-w-[18rem]">
                                    <p class="text-base font-extrabold"><?= e((string) ($microcontent['body'] ?: 'Microconteudo sem descricao')) ?></p>
                                    <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($attachment['original_name'] ?? $microcontent['filename'] ?? 'Arquivo privado')) ?> â€¢ <?= e((string) ($attachment['kind'] ?? 'documento')) ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-sm font-semibold text-on-surface">
                                <p><?= e((string) ($creator['name'] ?? 'Criador')) ?></p>
                                <p class="mt-1 text-xs text-slate-400">@<?= e((string) ($creator['slug'] ?? 'studio')) ?></p>
                            </td>
                            <td class="px-6 py-5 text-sm font-semibold text-on-surface">
                                <p><?= e((string) ($subscriber['name'] ?? 'Assinante')) ?></p>
                                <p class="mt-1 text-xs text-slate-400"><?= e((string) ($subscriber['email'] ?? '')) ?></p>
                            </td>
                            <td class="px-6 py-5 text-sm font-extrabold text-primary"><?= luacoin_amount_html((int) ($microcontent['unlock_price'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?></td>
                            <td class="px-6 py-5">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] <?= e($statusClass) ?>"><?= e((string) ($microcontent['unlock_label'] ?? 'Aguardando desbloqueio')) ?></span>
                                <?php if ((string) ($microcontent['unlock_at'] ?? '') !== ''): ?>
                                    <p class="mt-2 text-xs text-slate-400"><?= e((string) ($microcontent['unlock_user_name'] ?? 'Assinante')) ?> â€¢ <?= e(format_datetime((string) ($microcontent['unlock_at'] ?? ''), 'd/m/Y H:i')) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-5 text-sm font-semibold text-on-surface"><?= e(format_datetime((string) ($microcontent['created_at'] ?? ''), 'd/m/Y H:i')) ?></td>
                            <td class="px-6 py-5">
                                <a class="inline-flex items-center gap-2 rounded-full bg-surface-container-low px-4 py-2 text-sm font-bold text-on-surface transition-colors hover:bg-primary/10 hover:text-primary" href="<?= e((string) ($microcontent['asset_href'] ?? '#')) ?>" target="_blank">
                                    <span class="material-symbols-outlined text-base">visibility</span>
                                    <span>Abrir anexo</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (($microPagination['pages'] ?? 1) > 1): ?>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-6 py-5 text-sm font-bold">
                    <span class="text-slate-500">Página <?= e((string) ($microPagination['page'] ?? 1)) ?> de <?= e((string) ($microPagination['pages'] ?? 1)) ?></span>
                    <div class="flex gap-3">
                        <?php if (($microPagination['page'] ?? 1) > 1): ?>
                            <a class="rounded-full bg-surface-container-low px-4 py-2 text-on-surface-variant" href="<?= e($paginationUrl(['creator_id' => (int) ($filters['creator_id'] ?? 0), 'micro_q' => $microFilters['q'] ?? '', 'micro_status' => $microFilters['status'] ?? '', 'micro_page' => ((int) ($microPagination['page'] ?? 1) - 1)])) ?>">Anterior</a>
                        <?php endif; ?>
                        <?php if (($microPagination['page'] ?? 1) < ($microPagination['pages'] ?? 1)): ?>
                            <a class="rounded-full bg-slate-900 px-4 py-2 text-white" href="<?= e($paginationUrl(['creator_id' => (int) ($filters['creator_id'] ?? 0), 'micro_q' => $microFilters['q'] ?? '', 'micro_status' => $microFilters['status'] ?? '', 'micro_page' => ((int) ($microPagination['page'] ?? 1) + 1)])) ?>">Próxima</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($microcontents === []): ?><p class="p-8 text-sm text-on-surface-variant">Nenhum microconteudo encontrado com esse filtro.</p><?php endif; ?>
        </div>
        <div class="overflow-hidden rounded-3xl bg-surface-container-lowest shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-2xl font-extrabold">Lives</h3>
                    <p class="mt-1 text-sm text-on-surface-variant">Controle central das salas, metas e configuracoes ao vivo.</p>
                </div>
                <span class="text-sm font-bold text-primary"><?= e((string) ($livePagination['total'] ?? count($lives))) ?> lives</span>
            </div>
            <form action="/admin/operations" class="grid grid-cols-1 gap-3 border-b border-slate-100 px-6 py-5 md:grid-cols-[minmax(0,1fr)_minmax(0,0.45fr)_auto]" method="get">
                <input name="creator_id" type="hidden" value="<?= e((string) ($filters['creator_id'] ?? 0)) ?>">
                <input class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="live_q" placeholder="Buscar live..." type="search" value="<?= e((string) ($liveFilters['q'] ?? '')) ?>">
                <select class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="live_status">
                    <option value="">Todos os status</option>
                    <option value="scheduled" <?= (string) ($liveFilters['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Agendadas</option>
                    <option value="live" <?= (string) ($liveFilters['status'] ?? '') === 'live' ? 'selected' : '' ?>>Ao vivo</option>
                    <option value="ended" <?= (string) ($liveFilters['status'] ?? '') === 'ended' ? 'selected' : '' ?>>Concluidas</option>
                    <option value="expired" <?= (string) ($liveFilters['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Expiradas</option>
                </select>
                <div class="flex items-center gap-3">
                    <button class="rounded-full bg-slate-900 px-5 py-3 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
                    <a class="rounded-full bg-surface-container-low px-5 py-3 text-sm font-bold text-on-surface-variant" href="<?= e($paginationUrl(['creator_id' => (int) ($filters['creator_id'] ?? 0)])) ?>">Reset</a>
                </div>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead class="bg-surface-container-low">
                    <tr class="text-left">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Live</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Status</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Acesso</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Agendada</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Meta</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Acoes</th>
                    </tr>
                    </thead>
                    <?php foreach ($lives as $live): ?>
                        <?php
                        $creator = $live['creator'] ?? [];
                        $rowId = 'operation-live-' . (int) ($live['id'] ?? 0);
                        $status = (string) ($live['status'] ?? 'scheduled');
                        $statusClass = match ($status) {
                            'live' => 'bg-rose-100 text-rose-700',
                            'ended' => 'bg-slate-200 text-slate-600',
                            default => 'bg-amber-100 text-amber-700',
                        };
                        ?>
                        <tbody class="border-t border-slate-100 first:border-t-0">
                        <tr class="align-top">
                            <td class="px-6 py-5">
                                <div class="min-w-[18rem]">
                                    <p class="text-base font-extrabold"><?= e((string) ($live['title'] ?? 'Live')) ?></p>
                                    <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($creator['name'] ?? 'Criador')) ?> • <?= e((string) ($creator['email'] ?? '')) ?></p>
                                    <p class="mt-1 text-xs text-slate-400"><?= e(excerpt((string) ($live['description'] ?? ''), 90)) ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-5"><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] <?= e($statusClass) ?>"><?= e($status) ?></span></td>
                            <td class="px-6 py-5 text-sm font-semibold text-on-surface"><?= e((string) ($live['access_mode'] ?? 'public')) ?></td>
                            <td class="px-6 py-5 text-sm font-semibold text-on-surface"><?= e(format_datetime((string) ($live['scheduled_for'] ?? ''), 'd/m/Y H:i')) ?></td>
                            <td class="px-6 py-5 text-sm font-extrabold text-primary"><?= luacoin_amount_html((int) ($live['goal_tokens'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?></td>
                            <td class="px-6 py-5">
                                <button class="admin-operation-toggle inline-flex items-center gap-2 rounded-full bg-surface-container-low px-4 py-2 text-sm font-bold text-on-surface transition-colors hover:bg-primary/10 hover:text-primary" data-operation-toggle aria-expanded="false" aria-controls="<?= e($rowId) ?>" type="button">
                                    <span>Editar</span>
                                    <span class="admin-operation-chevron material-symbols-outlined text-slate-400 transition-transform">expand_more</span>
                                </button>
                            </td>
                        </tr>
                        <tr class="hidden bg-surface-container-low/60" data-operation-panel id="<?= e($rowId) ?>">
                            <td class="px-6 py-6" colspan="6">
                                <form action="/admin/operations/live/save" class="grid grid-cols-1 gap-6 2xl:grid-cols-[1.15fr_0.85fr]" method="post">
                                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                    <input name="live_id" type="hidden" value="<?= e((string) ($live['id'] ?? 0)) ?>">
                                    <div class="space-y-4">
                                        <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="title" type="text" value="<?= e((string) ($live['title'] ?? '')) ?>">
                                        <textarea class="min-h-28 w-full rounded-3xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="description"><?= e((string) ($live['description'] ?? '')) ?></textarea>
                                        <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="cover_url" placeholder="Cover URL" type="text" value="<?= e((string) ($live['cover_url'] ?? '')) ?>">
                                        <textarea class="min-h-24 w-full rounded-3xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="pinned_notice" placeholder="Aviso fixado"><?= e((string) ($live['pinned_notice'] ?? '')) ?></textarea>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <select class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="status">
                                                <?php foreach (['scheduled' => 'Agendada', 'live' => 'Ao vivo', 'ended' => 'Encerrada', 'expired' => 'Expirada'] as $value => $label): ?>
                                                    <option value="<?= e($value) ?>" <?= (string) ($live['status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="access_mode">
                                                <option value="public" <?= (string) ($live['access_mode'] ?? '') === 'public' ? 'selected' : '' ?>>Publica</option>
                                                <option value="subscriber" <?= (string) ($live['access_mode'] ?? '') === 'subscriber' ? 'selected' : '' ?>>Assinantes</option>
                                            </select>
                                            <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="category" placeholder="Categoria" type="text" value="<?= e((string) ($live['category'] ?? '')) ?>">
                                            <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="scheduled_for" type="datetime-local" value="<?= e((string) (($live['scheduled_for'] ?? '') !== '' ? date('Y-m-d\TH:i', strtotime((string) $live['scheduled_for'])) : '')) ?>">
                                            <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="0" name="price_luacoins" step="1" type="number" value="<?= e((string) ($live['price_tokens'] ?? 0)) ?>">
                                            <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="0" name="goal_luacoins" step="1" type="number" value="<?= e((string) ($live['goal_tokens'] ?? 0)) ?>">
                                        </div>
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <label class="flex items-center gap-3 rounded-2xl bg-white px-5 py-4 text-sm font-bold text-on-surface">
                                                <input class="rounded border-slate-300 text-primary focus:ring-primary/20" name="chat_enabled" type="checkbox" value="1" <?= (bool) ($live['chat_enabled'] ?? false) ? 'checked' : '' ?>>
                                                <span>Chat habilitado</span>
                                            </label>
                                        </div>
                                        <div class="flex flex-wrap gap-3">
                                            <button class="min-w-[120px] rounded-full bg-primary px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Salvar live</button>
                                            <button class="min-w-[110px] rounded-full bg-rose-100 px-6 py-4 text-sm font-bold text-rose-700" data-prototype-skip="1" formaction="/admin/operations/live/delete" type="submit">Excluir</button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        </tbody>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php if (($livePagination['pages'] ?? 1) > 1): ?>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-6 py-5 text-sm font-bold">
                    <span class="text-slate-500">Pagina <?= e((string) ($livePagination['page'] ?? 1)) ?> de <?= e((string) ($livePagination['pages'] ?? 1)) ?></span>
                    <div class="flex gap-3">
                        <?php if (($livePagination['page'] ?? 1) > 1): ?>
                            <a class="rounded-full bg-surface-container-low px-4 py-2 text-on-surface-variant" href="<?= e($paginationUrl(['creator_id' => (int) ($filters['creator_id'] ?? 0), 'live_q' => $liveFilters['q'] ?? '', 'live_status' => $liveFilters['status'] ?? '', 'live_page' => ((int) ($livePagination['page'] ?? 1) - 1)])) ?>">Anterior</a>
                        <?php endif; ?>
                        <?php if (($livePagination['page'] ?? 1) < ($livePagination['pages'] ?? 1)): ?>
                            <a class="rounded-full bg-slate-900 px-4 py-2 text-white" href="<?= e($paginationUrl(['creator_id' => (int) ($filters['creator_id'] ?? 0), 'live_q' => $liveFilters['q'] ?? '', 'live_status' => $liveFilters['status'] ?? '', 'live_page' => ((int) ($livePagination['page'] ?? 1) + 1)])) ?>">Proxima</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($lives === []): ?><p class="p-8 text-sm text-on-surface-variant">Nenhuma live encontrada com esse filtro.</p><?php endif; ?>
        </div>
    </section>
</main>
<script>
    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('[data-operation-toggle]');

        if (! toggle) {
            return;
        }

        const panelId = toggle.getAttribute('aria-controls');
        const panel = panelId ? document.getElementById(panelId) : null;

        if (! panel) {
            return;
        }

        const expanded = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        panel.classList.toggle('hidden', expanded);
    });
</script>
</body>
</html>
