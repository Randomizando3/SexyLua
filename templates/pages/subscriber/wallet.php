<?php

declare(strict_types=1);

$user = $app->auth->user() ?? [];
$balance = (int) ($data['balance'] ?? 0);
$inflow = (int) ($data['inflow'] ?? 0);
$outflow = (int) ($data['outflow'] ?? 0);
$transactions = $data['filtered_transactions'] ?? $data['transactions'] ?? [];
$summary = $data['summary'] ?? [];
$filters = $data['filters'] ?? [];
$platformSettings = $app->repository->settings();
$luacoinPrice = (float) ($platformSettings['luacoin_price_brl'] ?? 0.07);
$mercadoPagoEnabled = trim((string) ($platformSettings['mercadopago_access_token'] ?? '')) !== '';
$siteBaseUrl = (string) ($platformSettings['site_base_url'] ?? app_base_url($app->config, $platformSettings));
$paymentStatus = (string) ($_GET['payment_status'] ?? '');
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Carteira do Assinante</title>
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
        .signature-glow { background: linear-gradient(135deg, #ab1155 0%, #cc326e 100%); }
    </style>
</head>
<body class="min-h-screen">
<header class="fixed top-0 z-50 flex h-16 w-full items-center justify-between bg-[#D81B60] px-6 font-['Plus_Jakarta_Sans'] font-bold tracking-wide text-white shadow-lg shadow-[#D81B60]/20">
    <div class="flex items-center gap-4">
        <?= brand_logo_white('h-8 w-auto') ?>
        <span class="hidden border-l border-white/20 pl-4 text-xs uppercase tracking-widest opacity-80 md:block">Subscriber Club</span>
    </div>
    <div class="flex items-center gap-3">
        <a class="rounded-full border border-white/20 px-4 py-2 text-xs font-bold uppercase tracking-widest transition-colors hover:bg-white/10" href="/subscriber/subscriptions">Assinaturas</a>
        <div class="flex h-9 w-9 items-center justify-center rounded-full border border-white/20 bg-white/10 font-bold"><?= e(avatar_initials((string) ($user['name'] ?? 'Assinante'))) ?></div>
    </div>
</header>

<aside class="fixed left-0 top-16 hidden h-[calc(100vh-64px)] w-64 flex-col bg-[#f5f3f5] p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] lg:flex">
    <nav class="space-y-2">
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/subscriber"><span class="material-symbols-outlined">home</span><span>Inicio</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/subscriber/subscriptions"><span class="material-symbols-outlined">stars</span><span>Minhas Assinaturas</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/subscriber/favorites"><span class="material-symbols-outlined">favorite</span><span>Favoritos</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/subscriber/messages"><span class="material-symbols-outlined">chat</span><span>Mensagens</span></a>
        <a class="flex items-center gap-4 rounded-full bg-white px-4 py-3 font-bold text-primary" href="/subscriber/wallet"><span class="material-symbols-outlined">account_balance_wallet</span><span>Carteira</span></a>
    </nav>
</aside>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">LuaCoins e historico</p>
            <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Carteira do <span class="italic text-primary">Assinante</span></h2>
            <p class="mt-4 max-w-2xl text-on-surface-variant">Recarregue LuaCoins, acompanhe gastos com assinaturas e tenha visibilidade completa da sua movimentacao.</p>
        </div>
        <div class="signature-glow rounded-3xl px-6 py-5 text-white shadow-[0px_20px_40px_rgba(171,17,85,0.2)]">
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/70">Saldo disponivel</p>
            <div class="mt-3 text-4xl font-extrabold"><?= luacoin_amount_html($balance, 'inline-flex items-center gap-3 whitespace-nowrap', '', 'h-[0.9em] w-[0.9em] shrink-0') ?></div>
        </div>
    </section>

    <section class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Entradas</p><div class="mt-3 text-3xl font-extrabold text-emerald-600"><?= luacoin_amount_html($inflow, 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.9em] w-[0.9em] shrink-0') ?></div></article>
        <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Saidas</p><div class="mt-3 text-3xl font-extrabold text-rose-700"><?= luacoin_amount_html($outflow, 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.9em] w-[0.9em] shrink-0') ?></div></article>
        <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Recargas</p><div class="mt-3 text-3xl font-extrabold text-primary"><?= luacoin_amount_html((int) ($summary['top_up_total'] ?? 0), 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.9em] w-[0.9em] shrink-0') ?></div></article>
        <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Assinaturas</p><div class="mt-3 text-3xl font-extrabold text-primary"><?= luacoin_amount_html((int) ($summary['subscription_spend'] ?? 0), 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.9em] w-[0.9em] shrink-0') ?></div></article>
    </section>

    <?php if ($paymentStatus !== ''): ?>
        <section class="mb-8 rounded-3xl <?= $paymentStatus === 'success' ? 'bg-emerald-50 text-emerald-900' : ($paymentStatus === 'pending' ? 'bg-amber-50 text-amber-900' : 'bg-rose-50 text-rose-900') ?> p-5 shadow-sm">
            <p class="text-sm font-bold">
                <?= e($paymentStatus === 'success' ? 'Retorno do checkout recebido. Aguarde a confirmacao final do Mercado Pago para liberar as LuaCoins.' : ($paymentStatus === 'pending' ? 'Pagamento pendente. Assim que o Mercado Pago confirmar, as LuaCoins entram na carteira.' : 'Pagamento nao concluido. Confira o checkout e tente novamente.')) ?>
            </p>
        </section>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-8 2xl:grid-cols-[0.8fr_1.2fr]">
        <section class="space-y-6">
            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <h3 class="text-2xl font-extrabold">Recarregar LuaCoins</h3>
                <form action="/subscriber/wallet/add-funds" class="mt-6 space-y-4" method="post">
                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="1" name="luacoins" placeholder="Quantidade em LuaCoins" required type="number" value="100">
                    <p class="text-sm text-on-surface-variant">Valor estimado: <?= e(brl_amount(100 * $luacoinPrice)) ?>. <?= $mercadoPagoEnabled ? 'O checkout sera aberto no Mercado Pago.' : 'Sem chave configurada, a recarga segue em modo local para demo.' ?></p>
                    <?php if ($mercadoPagoEnabled && ! str_starts_with($siteBaseUrl, 'https://')): ?>
                        <p class="rounded-2xl bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">Configure uma Site URL HTTPS no admin para ativar o retorno automatico do Mercado Pago.</p>
                    <?php endif; ?>
                    <button class="signature-glow w-full rounded-full px-5 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit"><?= $mercadoPagoEnabled ? 'Comprar com Mercado Pago' : 'Adicionar saldo demo' ?></button>
                </form>
            </div>
            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <h3 class="text-2xl font-extrabold">Resumo de uso</h3>
                <div class="mt-5 space-y-4 text-sm">
                    <div class="rounded-3xl bg-surface-container-low p-5"><p class="text-on-surface-variant">Gasto com assinaturas</p><div class="mt-2 text-xl font-bold"><?= luacoin_amount_html((int) ($summary['subscription_spend'] ?? 0), 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.95em] w-[0.95em] shrink-0') ?></div></div>
                    <div class="rounded-3xl bg-surface-container-low p-5"><p class="text-on-surface-variant">Gasto com gorjetas</p><div class="mt-2 text-xl font-bold"><?= luacoin_amount_html((int) ($summary['tip_spend'] ?? 0), 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.95em] w-[0.95em] shrink-0') ?></div></div>
                </div>
            </div>
        </section>

        <section class="space-y-6">
            <form action="/subscriber/wallet" class="grid grid-cols-1 gap-4 rounded-3xl bg-surface-container-lowest p-6 shadow-sm xl:grid-cols-[1fr_0.45fr_auto]" method="get">
                <input class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="q" placeholder="Buscar transacao..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
                <select class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="type">
                    <option value="">Todos os tipos</option>
                    <option value="subscription" <?= (string) ($filters['type'] ?? '') === 'subscription' ? 'selected' : '' ?>>Assinaturas</option>
                    <option value="tip" <?= (string) ($filters['type'] ?? '') === 'tip' ? 'selected' : '' ?>>Gorjetas</option>
                    <option value="top_up" <?= (string) ($filters['type'] ?? '') === 'top_up' ? 'selected' : '' ?>>Recargas</option>
                </select>
                <div class="flex items-center gap-3">
                    <button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
                    <a class="rounded-full bg-surface-container-low px-6 py-4 text-sm font-bold text-on-surface-variant" href="/subscriber/wallet">Reset</a>
                </div>
            </form>

            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <h3 class="text-2xl font-extrabold">Historico financeiro</h3>
                <div class="mt-6 space-y-4">
                    <?php foreach ($transactions as $transaction): ?>
                        <?php $isIn = (string) ($transaction['direction'] ?? 'in') === 'in'; ?>
                        <div class="rounded-3xl bg-surface-container-low p-5">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-bold"><?= e((string) ($transaction['note'] ?? 'Movimentacao')) ?></p>
                                    <p class="mt-1 text-xs font-bold uppercase tracking-[0.25em] text-slate-400"><?= e((string) ($transaction['type'] ?? 'mov')) ?> • <?= e(format_datetime((string) ($transaction['created_at'] ?? ''), 'd/m/Y H:i')) ?></p>
                                </div>
                                <strong class="<?= $isIn ? 'text-emerald-600' : 'text-rose-700' ?>"><?= $isIn ? '+' : '-' ?><?= luacoin_amount_html((int) ($transaction['amount'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-[0.85em] w-[0.85em] shrink-0') ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($transactions === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma transacao encontrada nesse filtro.</p><?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>
</body>
</html>
