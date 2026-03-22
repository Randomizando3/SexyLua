<?php

declare(strict_types=1);

$users = $data['items'] ?? [];
$summary = $data['summary'] ?? [];
$filters = $data['filters'] ?? [];
$admin = $app->auth->user() ?? [];
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Gestao de Usuarios</title>
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
        <h1 class="text-2xl font-black">SexyLua Admin</h1>
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
        <a class="flex items-center gap-4 rounded-full bg-white px-4 py-3 font-bold text-primary" href="/admin/users"><span class="material-symbols-outlined">group</span><span>Usuarios</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/moderation"><span class="material-symbols-outlined">gavel</span><span>Moderacao</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/finance"><span class="material-symbols-outlined">payments</span><span>Financeiro</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/settings"><span class="material-symbols-outlined">settings</span><span>Configuracoes</span></a>
    </nav>
</aside>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Administracao de acesso</p>
            <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Gestao de <span class="italic text-primary">Usuarios</span></h2>
            <p class="mt-4 max-w-2xl text-on-surface-variant">Filtre a base, ajuste papeis e status, e mantenha o ecossistema do SexyLua sob controle.</p>
        </div>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Total</p><p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) ($summary['total'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Criadores</p><p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) ($summary['creators'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Assinantes</p><p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) ($summary['subscribers'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Suspensos</p><p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) ($summary['suspended'] ?? 0)) ?></p></article>
        </div>
    </section>

    <form action="/admin/users" class="mb-8 grid grid-cols-1 gap-4 rounded-3xl bg-surface-container-lowest p-6 shadow-sm xl:grid-cols-[1fr_0.4fr_0.4fr_auto]" method="get">
        <input class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="q" placeholder="Buscar usuario..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
        <select class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="role">
            <option value="">Todos os papeis</option>
            <option value="subscriber" <?= (string) ($filters['role'] ?? '') === 'subscriber' ? 'selected' : '' ?>>Assinante</option>
            <option value="creator" <?= (string) ($filters['role'] ?? '') === 'creator' ? 'selected' : '' ?>>Criador</option>
            <option value="admin" <?= (string) ($filters['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
        <select class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="status">
            <option value="">Todos os status</option>
            <option value="active" <?= (string) ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Ativo</option>
            <option value="suspended" <?= (string) ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspenso</option>
        </select>
        <div class="flex items-center gap-3">
            <button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
            <a class="rounded-full bg-surface-container-low px-6 py-4 text-sm font-bold text-on-surface-variant" href="/admin/users">Reset</a>
        </div>
    </form>

    <div class="space-y-5">
        <?php foreach ($users as $user): ?>
            <form action="/admin/users/update" class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm" method="post">
                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                <input name="user_id" type="hidden" value="<?= e((string) ($user['id'] ?? 0)) ?>">
                <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1.3fr_0.7fr_0.7fr_1fr_auto]">
                    <div>
                        <p class="text-lg font-bold"><?= e((string) ($user['name'] ?? 'Usuario')) ?></p>
                        <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($user['email'] ?? '')) ?></p>
                        <input class="mt-4 w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="headline" type="text" value="<?= e((string) ($user['headline'] ?? '')) ?>">
                    </div>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Papel</span>
                        <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="role">
                            <option value="subscriber" <?= (string) ($user['role'] ?? '') === 'subscriber' ? 'selected' : '' ?>>Assinante</option>
                            <option value="creator" <?= (string) ($user['role'] ?? '') === 'creator' ? 'selected' : '' ?>>Criador</option>
                            <option value="admin" <?= (string) ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Status</span>
                        <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="status">
                            <option value="active" <?= (string) ($user['status'] ?? '') === 'active' ? 'selected' : '' ?>>Ativo</option>
                            <option value="suspended" <?= (string) ($user['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspenso</option>
                        </select>
                    </label>
                    <div class="rounded-3xl bg-surface-container-low p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Criado em</p>
                        <p class="mt-3 font-bold"><?= e(format_datetime((string) ($user['created_at'] ?? ''), 'd/m/Y')) ?></p>
                    </div>
                    <div class="flex items-end">
                        <button class="w-full rounded-full bg-primary px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Salvar</button>
                    </div>
                </div>
            </form>
        <?php endforeach; ?>
        <?php if ($users === []): ?><p class="rounded-3xl bg-surface-container-low p-8 text-sm text-on-surface-variant">Nenhum usuario encontrado com esse filtro.</p><?php endif; ?>
    </div>
</main>
</body>
</html>
