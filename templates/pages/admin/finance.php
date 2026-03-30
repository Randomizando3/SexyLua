<?php

declare(strict_types=1);

$summary = $data['summary'] ?? [];
$filters = $data['filters'] ?? [];
$transactions = $data['filtered_transactions'] ?? [];
$pendingPayouts = $data['pending_payouts'] ?? [];
$pendingTopUps = $data['pending_topups'] ?? [];
$users = $data['users'] ?? [];
$admin = $app->auth->user() ?? [];
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Financeiro</title>
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
                        "surface-container-high": "#e9e7e9",
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
        <a class="flex items-center gap-4 rounded-full bg-white px-4 py-3 font-bold text-primary" href="/admin/finance"><span class="material-symbols-outlined">payments</span><span>Financeiro</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/operations"><span class="material-symbols-outlined">manufacturing</span><span>Operacoes</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/settings"><span class="material-symbols-outlined">settings</span><span>Configuracoes</span></a>
    </nav>
    <div class="mt-auto rounded-3xl bg-white p-5 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary">Resultado da plataforma</p>
        <h3 class="mt-3 text-3xl font-extrabold"><?= e(token_amount((int) ($summary['platform_result'] ?? 0))) ?></h3>
        <p class="mt-2 text-sm text-on-surface-variant">Margem liquida aproximada entre consumo dos assinantes e repasse aos criadores.</p>
    </div>
</aside>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Fluxo de caixa</p>
            <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Visao <span class="italic text-primary">Financeira</span></h2>
            <p class="mt-4 max-w-2xl text-on-surface-variant">Acompanhe volume bruto, repasses, recargas, ajuste carteiras manualmente e opere o funil financeiro completo com mais seguranca.</p>
        </div>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5">
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Volume</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-primary md:text-3xl"><?= e(token_amount((int) ($summary['gross_volume'] ?? 0))) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Repasse</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-emerald-600 md:text-3xl"><?= e(token_amount((int) ($summary['creator_income'] ?? 0))) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Resultado</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-primary md:text-3xl"><?= e(token_amount((int) ($summary['platform_result'] ?? 0))) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Recargas</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-primary md:text-3xl"><?= e((string) ($summary['top_ups'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Saques pendentes</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-amber-600 md:text-3xl"><?= e((string) ($summary['pending_payout_count'] ?? 0)) ?></p></article>
        </div>
    </section>

    <form action="/admin/finance" class="mb-8 grid grid-cols-1 gap-4 rounded-3xl bg-surface-container-lowest p-6 shadow-sm xl:grid-cols-[1fr_0.45fr_0.45fr_auto]" method="get">
        <input class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="q" placeholder="Buscar por nota, usuario ou tipo..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
        <select class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="type">
            <option value="">Todos os tipos</option>
            <option value="top_up" <?= (string) ($filters['type'] ?? '') === 'top_up' ? 'selected' : '' ?>>Recarga</option>
            <option value="top_up_pending" <?= (string) ($filters['type'] ?? '') === 'top_up_pending' ? 'selected' : '' ?>>Recarga pendente</option>
            <option value="subscription" <?= (string) ($filters['type'] ?? '') === 'subscription' ? 'selected' : '' ?>>Assinatura</option>
            <option value="tip" <?= (string) ($filters['type'] ?? '') === 'tip' ? 'selected' : '' ?>>Gorjeta</option>
            <option value="payout" <?= (string) ($filters['type'] ?? '') === 'payout' ? 'selected' : '' ?>>Saque</option>
            <option value="admin_" <?= (string) ($filters['type'] ?? '') === 'admin_' ? 'selected' : '' ?>>Ajuste manual</option>
        </select>
        <select class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="status">
            <option value="">Todos os status</option>
            <option value="completed" <?= (string) ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Concluido</option>
            <option value="pending" <?= (string) ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendente</option>
            <option value="processing" <?= (string) ($filters['status'] ?? '') === 'processing' ? 'selected' : '' ?>>Em processamento</option>
            <option value="paid" <?= (string) ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Pago</option>
            <option value="rejected" <?= (string) ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejeitado</option>
        </select>
        <div class="flex items-center gap-3">
            <button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
            <a class="rounded-full bg-surface-container-low px-6 py-4 text-sm font-bold text-on-surface-variant" href="/admin/finance">Reset</a>
        </div>
    </form>

    <div class="grid grid-cols-1 gap-8 2xl:grid-cols-[0.95fr_1.05fr]">
        <section class="space-y-5">
            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-2xl font-extrabold">Ajuste manual de carteira</h3>
                    <span class="text-sm font-bold text-primary">LuaCoins</span>
                </div>
                <form action="/admin/finance/adjust-wallet" class="grid grid-cols-1 gap-4 xl:grid-cols-[1fr_0.35fr_0.35fr]" method="post">
                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                    <label class="block space-y-2 xl:col-span-3">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Usuario</span>
                        <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="user_id">
                            <?php foreach ($users as $user): ?>
                                <option value="<?= e((string) ($user['id'] ?? 0)) ?>"><?= e((string) ($user['name'] ?? 'Usuario')) ?> • <?= e((string) ($user['email'] ?? '')) ?> • <?= e(token_amount((int) ($user['wallet_balance'] ?? 0))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Direcao</span>
                        <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="direction">
                            <option value="credit">Creditar</option>
                            <option value="debit">Debitar</option>
                        </select>
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">LuaCoins</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="1" name="luacoins" step="1" type="number" value="50">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Nota interna</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="note" type="text" value="Ajuste manual do admin">
                    </label>
                    <button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white xl:col-span-3" data-prototype-skip="1" type="submit">Aplicar ajuste</button>
                </form>
            </div>

            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-2xl font-extrabold">Recargas aguardando decisao</h3>
                    <span class="text-sm font-bold text-primary"><?= e((string) ($summary['pending_top_up_count'] ?? 0)) ?> pendentes</span>
                </div>
                <div class="space-y-4">
                    <?php foreach ($pendingTopUps as $transaction): ?>
                        <form action="/admin/finance/review-topup" class="rounded-3xl bg-surface-container-low p-5" method="post">
                            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                            <input name="transaction_id" type="hidden" value="<?= e((string) ($transaction['id'] ?? 0)) ?>">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <p class="text-lg font-bold"><?= e((string) ($transaction['user']['name'] ?? 'Assinante')) ?></p>
                                    <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($transaction['user']['email'] ?? '')) ?></p>
                                    <p class="mt-3 text-xs font-bold uppercase tracking-[0.25em] text-slate-400"><?= e((string) ($transaction['provider'] ?? 'checkout')) ?> • <?= e(format_datetime((string) ($transaction['created_at'] ?? ''), 'd/m/Y H:i')) ?></p>
                                </div>
                                <strong class="text-2xl font-extrabold text-primary"><?= e(token_amount((int) ($transaction['amount'] ?? 0))) ?></strong>
                            </div>
                            <div class="mt-5 grid grid-cols-1 gap-4 xl:grid-cols-[0.45fr_1fr]">
                                <label class="block space-y-2">
                                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Status</span>
                                    <select class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="status">
                                        <option value="approved">Aprovar</option>
                                        <option value="rejected">Rejeitar</option>
                                    </select>
                                </label>
                                <label class="block space-y-2">
                                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Nota interna</span>
                                    <textarea class="min-h-24 w-full rounded-3xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="admin_note" placeholder="Ex.: comprovante validado manualmente."><?= e((string) ($transaction['admin_note'] ?? '')) ?></textarea>
                                </label>
                            </div>
                            <button class="mt-5 w-full rounded-full bg-primary px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Revisar recarga</button>
                        </form>
                    <?php endforeach; ?>
                    <?php if ($pendingTopUps === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma recarga pendente agora.</p><?php endif; ?>
                </div>
            </div>

            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-2xl font-extrabold">Saques aguardando revisao</h3>
                    <span class="text-sm font-bold text-primary"><?= e(token_amount((int) ($summary['pending_payout_tokens'] ?? 0))) ?></span>
                </div>
                <div class="space-y-4">
                    <?php foreach ($pendingPayouts as $transaction): ?>
                        <form action="/admin/finance/review-payout" class="rounded-3xl bg-surface-container-low p-5" method="post">
                            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                            <input name="transaction_id" type="hidden" value="<?= e((string) ($transaction['id'] ?? 0)) ?>">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <p class="text-lg font-bold"><?= e((string) ($transaction['user']['name'] ?? 'Criador')) ?></p>
                                    <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($transaction['user']['email'] ?? '')) ?></p>
                                    <p class="mt-3 text-xs font-bold uppercase tracking-[0.25em] text-slate-400"><?= e((string) ($transaction['payout_method'] ?? 'pix')) ?> • <?= e(format_datetime((string) ($transaction['created_at'] ?? ''), 'd/m/Y H:i')) ?></p>
                                </div>
                                <strong class="text-2xl font-extrabold text-primary"><?= e(token_amount((int) ($transaction['amount'] ?? 0))) ?></strong>
                            </div>
                            <div class="mt-5 grid grid-cols-1 gap-4 xl:grid-cols-[0.45fr_1fr]">
                                <label class="block space-y-2">
                                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Status</span>
                                    <select class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="status">
                                        <option value="processing">Em processamento</option>
                                        <option value="paid">Pago</option>
                                        <option value="rejected">Rejeitado</option>
                                    </select>
                                </label>
                                <label class="block space-y-2">
                                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Nota interna</span>
                                    <textarea class="min-h-24 w-full rounded-3xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="admin_note" placeholder="Ex.: PIX confirmado, aguardando comprovante ou saque devolvido."><?= e((string) ($transaction['admin_note'] ?? '')) ?></textarea>
                                </label>
                            </div>
                            <button class="mt-5 w-full rounded-full bg-primary px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Atualizar saque</button>
                        </form>
                    <?php endforeach; ?>
                    <?php if ($pendingPayouts === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhum saque pendente agora.</p><?php endif; ?>
                </div>
            </div>
        </section>

        <section class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-2xl font-extrabold">Transacoes filtradas</h3>
                <span class="text-sm font-bold text-primary"><?= count($transactions) ?> registros</span>
            </div>
            <div class="space-y-4">
                <?php foreach ($transactions as $transaction): ?>
                    <?php
                    $direction = (string) ($transaction['direction'] ?? 'in');
                    $isIn = $direction === 'in';
                    $user = $transaction['user'] ?? [];
                    $creator = $transaction['creator'] ?? [];
                    $status = (string) ($transaction['status'] ?? 'completed');
                    $statusClass = match ($status) {
                        'completed', 'paid' => 'bg-emerald-100 text-emerald-700',
                        'processing', 'pending' => 'bg-amber-100 text-amber-700',
                        'rejected' => 'bg-rose-100 text-rose-700',
                        default => 'bg-slate-200 text-slate-600',
                    };
                    ?>
                    <article class="rounded-3xl bg-surface-container-low p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-lg font-bold"><?= e((string) ($transaction['note'] ?? 'Transacao')) ?></p>
                                    <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest <?= $statusClass ?>"><?= e($status) ?></span>
                                </div>
                                <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($user['name'] ?? 'Usuario')) ?><?php if (($creator['name'] ?? '') !== ''): ?> • <?= e((string) ($creator['name'] ?? '')) ?><?php endif; ?></p>
                                <p class="mt-3 text-xs font-bold uppercase tracking-[0.25em] text-slate-400"><?= e((string) ($transaction['type'] ?? 'mov')) ?> • <?= e(format_datetime((string) ($transaction['created_at'] ?? ''), 'd/m/Y H:i')) ?></p>
                            </div>
                            <strong class="<?= $isIn ? 'text-emerald-600' : 'text-rose-700' ?> text-xl font-extrabold"><?= $isIn ? '+' : '-' ?><?= e(token_amount((int) ($transaction['amount'] ?? 0))) ?></strong>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if ($transactions === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma transacao encontrada com esse filtro.</p><?php endif; ?>
            </div>
        </section>
    </div>
</main>
</body>
</html>
