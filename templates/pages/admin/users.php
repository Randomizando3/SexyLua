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
        .admin-user-toggle[aria-expanded="true"] .admin-user-chevron { transform: rotate(180deg); }
        [data-modal-overlay] { display: none; }
        [data-modal-overlay]:target { display: flex; }
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
        <a class="flex items-center gap-4 rounded-full bg-white px-4 py-3 font-bold text-primary" href="/admin/users"><span class="material-symbols-outlined">group</span><span>Usuarios</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/moderation"><span class="material-symbols-outlined">gavel</span><span>Moderacao</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/finance"><span class="material-symbols-outlined">payments</span><span>Financeiro</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/operations"><span class="material-symbols-outlined">manufacturing</span><span>Operacoes</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/settings"><span class="material-symbols-outlined">settings</span><span>Configuracoes</span></a>
    </nav>
</aside>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Administracao de acesso</p>
            <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Gestao de <span class="italic text-primary">Usuarios</span></h2>
            <p class="mt-4 max-w-2xl text-on-surface-variant">Filtre a base, edite perfis completos, redefina senha, acompanhe carteira e mantenha o ecossistema do SexyLua sob controle.</p>
        </div>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Total</p><p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) ($summary['total'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Criadores</p><p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) ($summary['creators'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Assinantes</p><p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) ($summary['subscribers'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Suspensos</p><p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) ($summary['suspended'] ?? 0)) ?></p></article>
        </div>
    </section>

    <div class="mb-4 flex justify-end">
        <a class="rounded-full bg-primary px-6 py-4 text-sm font-bold text-white shadow-sm" href="#create-user-modal">Novo usuario</a>
    </div>

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

    <section class="overflow-hidden rounded-3xl bg-surface-container-lowest shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-surface-container-low">
                <tr class="text-left">
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Usuario</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Papel</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Status</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Carteira</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Cidade</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Criado em</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Acoes</th>
                </tr>
                </thead>
        <?php foreach ($users as $user): ?>
            <?php
            $role = (string) ($user['role'] ?? 'subscriber');
            $status = (string) ($user['status'] ?? 'active');
            $roleLabel = $role === 'creator' ? 'Criador' : ($role === 'admin' ? 'Admin' : 'Assinante');
            $statusLabel = $status === 'suspended' ? 'Suspenso' : 'Ativo';
            $statusClass = $status === 'suspended' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700';
            $rowId = 'user-row-' . (int) ($user['id'] ?? 0);
            ?>
            <tbody class="border-t border-slate-100 first:border-t-0">
                <tr class="align-top">
                    <td class="px-6 py-5">
                        <div class="flex min-w-[16rem] items-center gap-3">
                            <div class="flex h-12 w-12 flex-none items-center justify-center rounded-full bg-primary/10 text-sm font-extrabold text-primary"><?= e(avatar_initials((string) ($user['name'] ?? 'Usuario'))) ?></div>
                            <div class="min-w-0">
                                <p class="truncate text-base font-extrabold"><?= e((string) ($user['name'] ?? 'Usuario')) ?></p>
                                <p class="truncate text-sm text-on-surface-variant"><?= e((string) ($user['email'] ?? '')) ?></p>
                                <p class="mt-1 truncate text-xs text-slate-400"><?= e((string) ($user['headline'] ?? 'Sem headline')) ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-5">
                        <span class="inline-flex rounded-full bg-primary/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-primary"><?= e($roleLabel) ?></span>
                    </td>
                    <td class="px-6 py-5">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] <?= e($statusClass) ?>"><?= e($statusLabel) ?></span>
                    </td>
                    <td class="px-6 py-5 text-sm font-extrabold text-primary"><?= e(token_amount((int) ($user['wallet_balance'] ?? 0))) ?></td>
                    <td class="px-6 py-5 text-sm font-semibold text-on-surface"><?= e((string) ($user['city'] ?? 'Sem cidade')) ?></td>
                    <td class="px-6 py-5 text-sm font-semibold text-on-surface"><?= e(format_datetime((string) ($user['created_at'] ?? ''), 'd/m/Y')) ?></td>
                    <td class="px-6 py-5">
                        <div class="flex min-w-[11rem] items-center gap-3">
                            <button class="admin-user-toggle inline-flex items-center gap-2 rounded-full bg-surface-container-low px-4 py-2 text-sm font-bold text-on-surface transition-colors hover:bg-primary/10 hover:text-primary" data-user-toggle aria-expanded="false" aria-controls="<?= e($rowId) ?>" type="button">
                                <span>Editar</span>
                                <span class="admin-user-chevron material-symbols-outlined text-slate-400 transition-transform">expand_more</span>
                            </button>
                            <a class="inline-flex rounded-full bg-surface-container-low px-4 py-2 text-sm font-bold text-on-surface-variant" href="/admin/finance?q=<?= urlencode((string) ($user['email'] ?? '')) ?>">Financeiro</a>
                        </div>
                    </td>
                </tr>
                <tr class="hidden bg-surface-container-low/60" data-user-panel id="<?= e($rowId) ?>">
                    <td class="px-6 py-6" colspan="7">
                <form action="/admin/users/update" method="post">
                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                <input name="user_id" type="hidden" value="<?= e((string) ($user['id'] ?? 0)) ?>">
                <div class="grid grid-cols-1 gap-6 2xl:grid-cols-[1.25fr_0.75fr]">
                    <div class="space-y-5">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <label class="block space-y-2">
                                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Nome</span>
                                <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="name" type="text" value="<?= e((string) ($user['name'] ?? '')) ?>">
                            </label>
                            <label class="block space-y-2">
                                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">E-mail</span>
                                <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="email" type="email" value="<?= e((string) ($user['email'] ?? '')) ?>">
                            </label>
                            <label class="block space-y-2">
                                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Cidade</span>
                                <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="city" type="text" value="<?= e((string) ($user['city'] ?? '')) ?>">
                            </label>
                            <label class="block space-y-2">
                                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Headline</span>
                                <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="headline" type="text" value="<?= e((string) ($user['headline'] ?? '')) ?>">
                            </label>
                            <label class="block space-y-2 md:col-span-2">
                                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Bio</span>
                                <textarea class="min-h-32 w-full rounded-3xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="bio"><?= e((string) ($user['bio'] ?? '')) ?></textarea>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <label class="block space-y-2">
                                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Avatar URL</span>
                                <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="avatar_url" type="text" value="<?= e((string) ($user['avatar_url'] ?? '')) ?>">
                            </label>
                            <label class="block space-y-2">
                                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Cover URL</span>
                                <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="cover_url" type="text" value="<?= e((string) ($user['cover_url'] ?? '')) ?>">
                            </label>
                            <label class="block space-y-2 md:col-span-2">
                                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Nova senha</span>
                                <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="new_password" type="password" placeholder="Preencha apenas se quiser redefinir a senha">
                            </label>
                        </div>

                        <?php if ((string) ($user['role'] ?? '') === 'creator'): ?>
                            <div class="rounded-3xl bg-surface-container-low p-5">
                                <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary">Dados do criador</p>
                                <div class="mt-4 grid grid-cols-1 gap-5 md:grid-cols-2">
                                    <label class="block space-y-2">
                                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Slug</span>
                                        <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="slug" type="text" value="<?= e((string) ($user['slug'] ?? '')) ?>">
                                    </label>
                                    <label class="block space-y-2">
                                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Mood</span>
                                        <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="mood" type="text" value="<?= e((string) ($user['mood'] ?? '')) ?>">
                                    </label>
                                    <label class="block space-y-2">
                                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Capa visual</span>
                                        <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="cover_style" type="text" value="<?= e((string) ($user['cover_style'] ?? '')) ?>">
                                    </label>
                                    <label class="block space-y-2">
                                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Metodo de saque</span>
                                        <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="payout_method" type="text" value="<?= e((string) ($user['payout_method'] ?? 'pix')) ?>">
                                    </label>
                                    <label class="block space-y-2 md:col-span-2">
                                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Chave de pagamento</span>
                                        <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="payout_key" type="text" value="<?= e((string) ($user['payout_key'] ?? '')) ?>">
                                    </label>
                                    <label class="block space-y-2">
                                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Instagram</span>
                                        <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="instagram" type="text" value="<?= e((string) ($user['instagram'] ?? '')) ?>">
                                    </label>
                                    <label class="block space-y-2">
                                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Telegram</span>
                                        <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="telegram" type="text" value="<?= e((string) ($user['telegram'] ?? '')) ?>">
                                    </label>
                                    <label class="block space-y-2 md:col-span-2">
                                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Stream key</span>
                                        <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="stream_key" type="text" value="<?= e((string) ($user['stream_key'] ?? '')) ?>">
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="space-y-5">
                        <div class="rounded-3xl bg-surface-container-low p-5">
                            <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Conta</p>
                            <p class="mt-3 text-xl font-extrabold"><?= e((string) ($user['name'] ?? 'Usuario')) ?></p>
                            <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($user['email'] ?? '')) ?></p>
                            <div class="mt-5 grid grid-cols-2 gap-4">
                                <div class="rounded-2xl bg-white p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Carteira</p>
                                    <p class="mt-2 text-lg font-extrabold text-primary"><?= e(token_amount((int) ($user['wallet_balance'] ?? 0))) ?></p>
                                </div>
                                <div class="rounded-2xl bg-white p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Criado em</p>
                                    <p class="mt-2 text-lg font-extrabold"><?= e(format_datetime((string) ($user['created_at'] ?? ''), 'd/m/Y')) ?></p>
                                </div>
                            </div>
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

                        <button class="w-full rounded-full bg-primary px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Salvar usuario</button>
                        <a class="block rounded-full bg-surface-container-low px-6 py-4 text-center text-sm font-bold text-on-surface-variant" href="/admin/finance?q=<?= urlencode((string) ($user['email'] ?? '')) ?>">Ver no financeiro</a>
                    </div>
                </div>
                </form>
                    </td>
                </tr>
            </tbody>
        <?php endforeach; ?>
            </table>
        </div>
        <?php if ($users === []): ?><p class="p-8 text-sm text-on-surface-variant">Nenhum usuario encontrado com esse filtro.</p><?php endif; ?>
    </section>

    <div class="fixed inset-0 z-[60] items-center justify-center bg-slate-900/40 p-6" data-modal-overlay id="create-user-modal">
        <div class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-[2rem] bg-surface-container-lowest p-6 shadow-2xl md:p-8">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Novo usuario</p>
                    <h3 class="mt-2 text-3xl font-extrabold">Criar acesso manual</h3>
                    <p class="mt-3 max-w-2xl text-sm text-on-surface-variant">Cadastre um novo assinante, criador ou admin pelo painel. Os campos do criador so serao usados quando o papel for Criador.</p>
                </div>
                <a class="rounded-full bg-surface-container-low px-4 py-2 text-sm font-bold text-on-surface-variant" href="/admin/users">Fechar</a>
            </div>

            <form action="/admin/users/create" class="space-y-6" method="post">
                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Nome</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="name" required type="text">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">E-mail</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="email" required type="email">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Senha inicial</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="password" required type="password">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Cidade</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="city" type="text">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Papel</span>
                        <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="role">
                            <option value="subscriber">Assinante</option>
                            <option value="creator">Criador</option>
                            <option value="admin">Admin</option>
                        </select>
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Status</span>
                        <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="status">
                            <option value="active">Ativo</option>
                            <option value="suspended">Suspenso</option>
                        </select>
                    </label>
                    <label class="block space-y-2 md:col-span-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Headline</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="headline" type="text">
                    </label>
                    <label class="block space-y-2 md:col-span-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Bio</span>
                        <textarea class="min-h-28 w-full rounded-3xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="bio"></textarea>
                    </label>
                </div>

                <div class="rounded-3xl bg-surface-container-low p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary">Dados visuais e do criador</p>
                    <div class="mt-4 grid grid-cols-1 gap-5 md:grid-cols-2">
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Avatar URL</span>
                            <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="avatar_url" type="text">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Cover URL</span>
                            <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="cover_url" type="text">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Slug</span>
                            <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="slug" type="text">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Mood</span>
                            <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="mood" type="text">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Capa visual</span>
                            <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="cover_style" type="text">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Metodo de saque</span>
                            <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="payout_method" type="text" value="pix">
                        </label>
                        <label class="block space-y-2 md:col-span-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Chave de pagamento</span>
                            <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="payout_key" type="text">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Instagram</span>
                            <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="instagram" type="text">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Telegram</span>
                            <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="telegram" type="text">
                        </label>
                        <label class="block space-y-2 md:col-span-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Stream key</span>
                            <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="stream_key" type="text">
                        </label>
                    </div>
                </div>

                <div class="flex flex-col gap-3 md:flex-row md:justify-end">
                    <a class="rounded-full bg-surface-container-low px-6 py-4 text-center text-sm font-bold text-on-surface-variant" href="/admin/users">Cancelar</a>
                    <button class="rounded-full bg-primary px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Criar usuario</button>
                </div>
            </form>
        </div>
    </div>
</main>
<script>
    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('[data-user-toggle]');

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
