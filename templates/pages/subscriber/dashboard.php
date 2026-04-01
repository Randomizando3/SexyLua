<?php

declare(strict_types=1);

$subscriber = $data['subscriber'] ?? [];
$subscriptions = $data['subscriptions'] ?? [];
$upcomingLives = $data['upcoming_lives'] ?? [];
$conversations = $data['conversations'] ?? [];
$transactions = $data['transactions'] ?? [];
$availablePlans = $data['available_plans'] ?? [];
$walletBalance = (int) ($data['wallet_balance'] ?? 0);
$favoritesCount = (int) ($data['favorites_count'] ?? 0);
$savedCount = (int) ($data['saved_count'] ?? 0);
$recentMessagesCount = (int) ($data['recent_messages_count'] ?? 0);
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Area do Assinante</title>
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
        .material-symbols-outlined {
            font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24;
        }
        body {
            background: #fbf9fb;
            color: #1b1c1d;
            font-family: "Manrope", sans-serif;
        }
        h1, h2, h3, h4 {
            font-family: "Plus Jakarta Sans", sans-serif;
        }
        .signature-glow {
            background: linear-gradient(135deg, #ab1155 0%, #cc326e 100%);
        }
    </style>
</head>
<body class="min-h-screen">
<?php
$subscriberTopbarUser = $subscriber;
$subscriberTopbarAction = ['href' => '/subscriber/wallet', 'label' => 'Carteira'];
$subscriberTopbarNav = [
    ['href' => '/subscriber', 'label' => 'Inicio', 'active' => true],
    ['href' => '/subscriber/subscriptions', 'label' => 'Assinaturas'],
    ['href' => '/subscriber/messages', 'label' => 'Mensagens'],
    ['href' => '/explore', 'label' => 'Explorar'],
];
require BASE_PATH . '/templates/partials/subscriber_topbar.php';
?>

<aside class="fixed left-0 top-16 hidden h-[calc(100vh-64px)] w-64 flex-col bg-[#f5f3f5] p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] lg:flex">
    <nav class="space-y-2">
        <a class="flex items-center gap-4 rounded-full bg-white px-4 py-3 font-bold text-primary" href="/subscriber">
            <span class="material-symbols-outlined">home</span>
            <span>Inicio</span>
        </a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/subscriber/subscriptions">
            <span class="material-symbols-outlined">stars</span>
            <span>Minhas Assinaturas</span>
        </a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/subscriber/favorites">
            <span class="material-symbols-outlined">favorite</span>
            <span>Favoritos</span>
        </a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/subscriber/messages">
            <span class="material-symbols-outlined">chat</span>
            <span>Mensagens</span>
        </a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/subscriber/wallet">
            <span class="material-symbols-outlined">account_balance_wallet</span>
            <span>Carteira</span>
        </a>
    </nav>
    <div class="mt-auto rounded-3xl bg-white p-5 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary">Saldo atual</p>
        <div class="mt-3 text-3xl font-extrabold"><?= luacoin_amount_html($walletBalance, 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-8 w-8 shrink-0') ?></div>
        <p class="mt-2 text-sm text-on-surface-variant">Use suas LuaCoins para renovar planos, enviar gorjetas e entrar em experiencias especiais.</p>
    </div>
</aside>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Painel pessoal</p>
            <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Area do <span class="italic text-primary">Assinante</span></h2>
            <p class="mt-4 max-w-2xl text-on-surface-variant">Acompanhe seus planos ativos, favoritas, conversas abertas e os proximos eventos dos criadores que voce segue.</p>
        </div>
        <div class="signature-glow rounded-3xl px-6 py-5 text-white shadow-[0px_20px_40px_rgba(171,17,85,0.2)]">
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/70">Bem-vindo</p>
            <p class="mt-2 text-2xl font-extrabold"><?= e((string) ($subscriber['name'] ?? 'Assinante')) ?></p>
            <p class="mt-1 text-sm text-white/80"><?= e((string) ($subscriber['email'] ?? '')) ?></p>
        </div>
    </section>

    <section class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Assinaturas</p>
            <p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) count($subscriptions)) ?></p>
            <p class="mt-2 text-sm text-on-surface-variant">Planos ativos no momento.</p>
        </article>
        <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Favoritos</p>
            <p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) $favoritesCount) ?></p>
            <p class="mt-2 text-sm text-on-surface-variant">Criadores marcados no seu radar.</p>
        </article>
        <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Conteudos salvos</p>
            <p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) $savedCount) ?></p>
            <p class="mt-2 text-sm text-on-surface-variant">Itens guardados para ver depois.</p>
        </article>
        <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Conversas</p>
            <p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) $recentMessagesCount) ?></p>
            <p class="mt-2 text-sm text-on-surface-variant">Threads abertas com criadores.</p>
        </article>
    </section>

    <div class="grid grid-cols-1 gap-8 2xl:grid-cols-[1.1fr_0.9fr]">
        <section class="space-y-8">
            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-2xl font-extrabold">Minhas assinaturas</h3>
                    <a class="text-sm font-bold text-primary hover:underline" href="/subscriber/subscriptions">Gerenciar</a>
                </div>
                <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                    <?php foreach ($subscriptions as $subscription): ?>
                        <?php $creator = $subscription['creator'] ?? []; $plan = $subscription['plan'] ?? []; ?>
                        <article class="rounded-3xl bg-surface-container-low p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-lg font-bold"><?= e((string) ($creator['name'] ?? 'Criador')) ?></p>
                                    <p class="mt-1 text-sm text-on-surface-variant">@<?= e((string) ($creator['slug'] ?? 'criador')) ?></p>
                                </div>
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-emerald-700"><?= e((string) ($subscription['status'] ?? 'active')) ?></span>
                            </div>
                            <p class="mt-4 text-sm text-on-surface-variant"><?= e((string) ($plan['name'] ?? 'Plano ativo')) ?> • <?= luacoin_amount_html((int) ($plan['price_tokens'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?></p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Renova em <?= e((string) ($subscription['days_to_renew'] ?? 0)) ?> dias</p>
                        </article>
                    <?php endforeach; ?>
                    <?php if ($subscriptions === []): ?>
                        <p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma assinatura ativa no momento.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-2xl font-extrabold">Proximas lives</h3>
                    <a class="text-sm font-bold text-primary hover:underline" href="/explore">Ver mais</a>
                </div>
                <div class="space-y-4">
                    <?php foreach ($upcomingLives as $live): ?>
                        <article class="rounded-3xl bg-surface-container-low p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-lg font-bold"><?= e((string) ($live['title'] ?? 'Live')) ?></p>
                                    <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($live['creator']['name'] ?? 'Criador')) ?></p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest <?= (string) ($live['status'] ?? '') === 'live' ? 'bg-rose-100 text-rose-700' : 'bg-slate-200 text-slate-600' ?>"><?= e((string) ($live['status'] ?? 'scheduled')) ?></span>
                            </div>
                            <p class="mt-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400"><?= e(format_datetime((string) ($live['scheduled_for'] ?? ''), 'd/m/Y H:i')) ?></p>
                        </article>
                    <?php endforeach; ?>
                    <?php if ($upcomingLives === []): ?>
                        <p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma live agendada para os criadores que voce acompanha.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="space-y-8">
            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-2xl font-extrabold">Conversas recentes</h3>
                    <a class="text-sm font-bold text-primary hover:underline" href="/subscriber/messages">Abrir chat</a>
                </div>
                <div class="space-y-4">
                    <?php foreach ($conversations as $conversation): ?>
                        <a class="block rounded-3xl bg-surface-container-low p-5 transition-colors hover:bg-surface-container-high" href="<?= e('/subscriber/messages?conversation=' . (int) ($conversation['id'] ?? 0)) ?>">
                            <p class="font-bold"><?= e((string) ($conversation['creator']['name'] ?? 'Criador')) ?></p>
                            <p class="mt-1 text-sm text-on-surface-variant"><?= e(excerpt((string) ($conversation['latest_message']['body'] ?? 'Sem mensagens ainda.'), 80)) ?></p>
                            <p class="mt-3 text-xs font-bold uppercase tracking-[0.25em] text-slate-400"><?= e(format_datetime((string) ($conversation['updated_at'] ?? ''), 'd/m H:i')) ?></p>
                        </a>
                    <?php endforeach; ?>
                    <?php if ($conversations === []): ?>
                        <p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma conversa aberta ainda.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-2xl font-extrabold">Ultimas movimentacoes</h3>
                    <a class="text-sm font-bold text-primary hover:underline" href="/subscriber/wallet">Ver carteira</a>
                </div>
                <div class="space-y-4">
                    <?php foreach ($transactions as $transaction): ?>
                        <?php $isIn = (string) ($transaction['direction'] ?? 'in') === 'in'; ?>
                        <div class="rounded-3xl bg-surface-container-low p-5">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-bold"><?= e((string) ($transaction['note'] ?? 'Movimentacao')) ?></p>
                                    <p class="mt-1 text-xs font-bold uppercase tracking-[0.25em] text-slate-400"><?= e((string) ($transaction['type'] ?? 'mov')) ?></p>
                                </div>
                                <strong class="<?= $isIn ? 'text-emerald-600' : 'text-rose-700' ?>"><?= $isIn ? '+' : '-' ?><?= luacoin_amount_html((int) ($transaction['amount'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-[0.85em] w-[0.85em] shrink-0') ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($transactions === []): ?>
                        <p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Sem movimentacoes recentes.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-2xl font-extrabold">Novos planos</h3>
                    <a class="text-sm font-bold text-primary hover:underline" href="/subscriber/subscriptions">Assinar</a>
                </div>
                <div class="space-y-4">
                    <?php foreach ($availablePlans as $plan): ?>
                        <article class="rounded-3xl bg-surface-container-low p-5">
                            <p class="text-lg font-bold"><?= e((string) ($plan['creator']['name'] ?? 'Criador')) ?></p>
                            <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($plan['name'] ?? 'Plano')) ?> • <?= luacoin_amount_html((int) ($plan['price_tokens'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?></p>
                            <p class="mt-3 text-sm text-on-surface-variant"><?= e(excerpt((string) ($plan['description'] ?? ''), 90)) ?></p>
                        </article>
                    <?php endforeach; ?>
                    <?php if ($availablePlans === []): ?>
                        <p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Sem novas sugestoes de plano agora.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>
</body>
</html>
