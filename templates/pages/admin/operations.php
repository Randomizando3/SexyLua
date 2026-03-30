<?php

declare(strict_types=1);

$summary = $data['summary'] ?? [];
$filters = $data['filters'] ?? [];
$contents = $data['contents'] ?? [];
$plans = $data['plans'] ?? [];
$lives = $data['lives'] ?? [];
$creators = $data['creators'] ?? [];
$admin = $app->auth->user() ?? [];
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
    </style>
</head>
<body class="min-h-screen">
<header class="fixed top-0 z-50 flex h-16 w-full items-center justify-between bg-[#D81B60] px-6 font-['Plus_Jakarta_Sans'] font-bold tracking-wide text-white shadow-lg shadow-[#D81B60]/20">
    <div class="flex items-center gap-4">
        <?= brand_logo_white('h-8 w-auto') ?>
        <span class="hidden border-l border-white/20 pl-4 text-xs uppercase tracking-widest opacity-80 md:block">Control Room</span>
    </div>
    <div class="flex items-center gap-3">
        <a class="rounded-full border border-white/20 px-4 py-2 text-xs font-bold uppercase tracking-widest transition-colors hover:bg-white/10" href="/admin">Dashboard</a>
        <div class="flex h-9 w-9 items-center justify-center rounded-full border border-white/20 bg-white/10 font-bold"><?= e(avatar_initials((string) ($admin['name'] ?? 'Admin'))) ?></div>
    </div>
</header>

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
        <h3 class="mt-3 text-3xl font-extrabold"><?= e((string) (($summary['content_count'] ?? 0) + ($summary['plan_count'] ?? 0) + ($summary['live_count'] ?? 0))) ?></h3>
        <p class="mt-2 text-sm text-on-surface-variant">Itens operacionais prontos para edicao manual pelo admin.</p>
    </div>
</aside>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Operacao central</p>
            <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Conteudo, planos e <span class="italic text-primary">lives</span></h2>
            <p class="mt-4 max-w-2xl text-on-surface-variant">Edite diretamente os ativos dos criadores sem sair do painel administrativo.</p>
        </div>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Conteudos</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-primary md:text-3xl"><?= e((string) ($summary['content_count'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Planos</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-primary md:text-3xl"><?= e((string) ($summary['plan_count'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Lives</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-primary md:text-3xl"><?= e((string) ($summary['live_count'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Ao vivo</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-emerald-600 md:text-3xl"><?= e((string) ($summary['live_now'] ?? 0)) ?></p></article>
        </div>
    </section>

    <form action="/admin/operations" class="mb-8 grid grid-cols-1 gap-4 rounded-3xl bg-surface-container-lowest p-6 shadow-sm xl:grid-cols-[1fr_0.5fr_0.35fr_0.35fr_auto]" method="get">
        <input class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="q" placeholder="Buscar titulo, descricao ou criador..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
        <select class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="creator_id">
            <option value="0">Todos os criadores</option>
            <?php foreach ($creators as $creator): ?>
                <option value="<?= e((string) ($creator['id'] ?? 0)) ?>" <?= (int) ($filters['creator_id'] ?? 0) === (int) ($creator['id'] ?? 0) ? 'selected' : '' ?>><?= e((string) ($creator['name'] ?? 'Criador')) ?></option>
            <?php endforeach; ?>
        </select>
        <select class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="content_status">
            <option value="">Todos os status de conteudo</option>
            <option value="draft" <?= (string) ($filters['content_status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="pending" <?= (string) ($filters['content_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendente</option>
            <option value="approved" <?= (string) ($filters['content_status'] ?? '') === 'approved' ? 'selected' : '' ?>>Aprovado</option>
            <option value="rejected" <?= (string) ($filters['content_status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejeitado</option>
            <option value="archived" <?= (string) ($filters['content_status'] ?? '') === 'archived' ? 'selected' : '' ?>>Arquivado</option>
        </select>
        <select class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="live_status">
            <option value="">Todos os status de live</option>
            <option value="scheduled" <?= (string) ($filters['live_status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Agendada</option>
            <option value="live" <?= (string) ($filters['live_status'] ?? '') === 'live' ? 'selected' : '' ?>>Ao vivo</option>
            <option value="ended" <?= (string) ($filters['live_status'] ?? '') === 'ended' ? 'selected' : '' ?>>Encerrada</option>
        </select>
        <div class="flex items-center gap-3">
            <button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
            <a class="rounded-full bg-surface-container-low px-6 py-4 text-sm font-bold text-on-surface-variant" href="/admin/operations">Reset</a>
        </div>
    </form>

    <section class="space-y-8">
        <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-2xl font-extrabold">Conteudos</h3>
                <span class="text-sm font-bold text-primary"><?= count($contents) ?> itens</span>
            </div>
            <div class="space-y-5">
                <?php foreach ($contents as $item): ?>
                    <?php $creator = $item['creator'] ?? []; ?>
                    <form action="/admin/operations/content/save" class="rounded-3xl bg-surface-container-low p-5" method="post">
                        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                        <input name="content_id" type="hidden" value="<?= e((string) ($item['id'] ?? 0)) ?>">
                        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1.2fr_0.8fr]">
                            <div class="space-y-4">
                                <div>
                                    <p class="text-lg font-bold"><?= e((string) ($creator['name'] ?? 'Criador')) ?> • <?= e((string) ($item['title'] ?? 'Conteudo')) ?></p>
                                    <p class="mt-1 text-sm text-on-surface-variant">@<?= e((string) ($creator['slug'] ?? 'studio')) ?> • <?= e(format_datetime((string) ($item['created_at'] ?? ''), 'd/m/Y H:i')) ?></p>
                                </div>
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
                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                    <button class="rounded-full bg-primary px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Salvar conteudo</button>
                                    <button class="rounded-full bg-rose-100 px-6 py-4 text-sm font-bold text-rose-700" data-prototype-skip="1" formaction="/admin/operations/content/delete" type="submit">Excluir</button>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php endforeach; ?>
                <?php if ($contents === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhum conteudo encontrado com esse filtro.</p><?php endif; ?>
            </div>
        </div>

        <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-2xl font-extrabold">Planos</h3>
                <span class="text-sm font-bold text-primary"><?= count($plans) ?> planos</span>
            </div>
            <div class="space-y-5">
                <?php foreach ($plans as $plan): ?>
                    <?php $creator = $plan['creator'] ?? []; ?>
                    <form action="/admin/operations/plan/save" class="rounded-3xl bg-surface-container-low p-5" method="post">
                        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                        <input name="plan_id" type="hidden" value="<?= e((string) ($plan['id'] ?? 0)) ?>">
                        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1.15fr_0.85fr]">
                            <div class="space-y-4">
                                <div>
                                    <p class="text-lg font-bold"><?= e((string) ($plan['name'] ?? 'Plano')) ?></p>
                                    <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($creator['name'] ?? 'Criador')) ?> • <?= e((string) ($creator['email'] ?? '')) ?></p>
                                </div>
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
                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                    <button class="rounded-full bg-primary px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Salvar plano</button>
                                    <button class="rounded-full bg-rose-100 px-6 py-4 text-sm font-bold text-rose-700" data-prototype-skip="1" formaction="/admin/operations/plan/delete" type="submit">Excluir</button>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php endforeach; ?>
                <?php if ($plans === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhum plano encontrado com esse filtro.</p><?php endif; ?>
            </div>
        </div>

        <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-2xl font-extrabold">Lives</h3>
                <span class="text-sm font-bold text-primary"><?= count($lives) ?> lives</span>
            </div>
            <div class="space-y-5">
                <?php foreach ($lives as $live): ?>
                    <?php $creator = $live['creator'] ?? []; ?>
                    <form action="/admin/operations/live/save" class="rounded-3xl bg-surface-container-low p-5" method="post">
                        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                        <input name="live_id" type="hidden" value="<?= e((string) ($live['id'] ?? 0)) ?>">
                        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1.15fr_0.85fr]">
                            <div class="space-y-4">
                                <div>
                                    <p class="text-lg font-bold"><?= e((string) ($live['title'] ?? 'Live')) ?></p>
                                    <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($creator['name'] ?? 'Criador')) ?> • <?= e((string) ($creator['email'] ?? '')) ?></p>
                                </div>
                                <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="title" type="text" value="<?= e((string) ($live['title'] ?? '')) ?>">
                                <textarea class="min-h-28 w-full rounded-3xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="description"><?= e((string) ($live['description'] ?? '')) ?></textarea>
                                <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="cover_url" placeholder="Cover URL" type="text" value="<?= e((string) ($live['cover_url'] ?? '')) ?>">
                                <textarea class="min-h-24 w-full rounded-3xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="pinned_notice" placeholder="Aviso fixado"><?= e((string) ($live['pinned_notice'] ?? '')) ?></textarea>
                            </div>
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <select class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="status">
                                        <?php foreach (['scheduled' => 'Agendada', 'live' => 'Ao vivo', 'ended' => 'Encerrada'] as $value => $label): ?>
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
                                    <label class="flex items-center gap-3 rounded-2xl bg-white px-5 py-4 text-sm font-bold text-on-surface">
                                        <input class="rounded border-slate-300 text-primary focus:ring-primary/20" name="recording_enabled" type="checkbox" value="1" <?= (bool) ($live['recording_enabled'] ?? false) ? 'checked' : '' ?>>
                                        <span>Replay habilitado</span>
                                    </label>
                                </div>
                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                    <button class="rounded-full bg-primary px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Salvar live</button>
                                    <button class="rounded-full bg-rose-100 px-6 py-4 text-sm font-bold text-rose-700" data-prototype-skip="1" formaction="/admin/operations/live/delete" type="submit">Excluir</button>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php endforeach; ?>
                <?php if ($lives === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma live encontrada com esse filtro.</p><?php endif; ?>
            </div>
        </div>
    </section>
</main>
</body>
</html>
