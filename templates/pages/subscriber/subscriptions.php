<?php

declare(strict_types=1);

$subscriber = $data['subscriber'] ?? [];
$subscriptions = $data['filtered_subscriptions'] ?? $data['active_subscriptions'] ?? [];
$plans = $data['filtered_plans'] ?? $data['available_plans'] ?? [];
$filters = $data['filters'] ?? [];
$summary = $data['summary'] ?? [];
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Minhas Assinaturas</title>
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
require BASE_PATH . '/templates/partials/subscriber_topbar.php';
$subscriberSidebarCurrent = 'subscriptions';
$subscriberSidebarFooter = '<a class="signature-glow flex items-center justify-center rounded-full px-6 py-4 text-sm font-bold text-white shadow-lg" href="/explore">Explorar criadores</a>';
require BASE_PATH . '/templates/partials/subscriber_sidebar.php';
?>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Planos e renovacoes</p>
            <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Minhas <span class="italic text-primary">Assinaturas</span></h2>
            <p class="mt-4 max-w-2xl text-on-surface-variant">Gerencie seus planos ativos, descubra novas assinaturas e acompanhe o custo mensal da sua curadoria.</p>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm">
                <p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Ativas</p>
                <p class="mt-2 text-[2rem] font-extrabold leading-tight text-primary md:text-3xl"><?= e((string) ($summary['active_count'] ?? 0)) ?></p>
            </article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm">
                <p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Mensal</p>
                <div class="mt-2 text-[2rem] font-extrabold leading-tight text-primary md:text-3xl"><?= luacoin_amount_html((int) ($summary['monthly_spend'] ?? 0), 'inline-flex items-center justify-center gap-2 whitespace-nowrap', '', 'h-[0.9em] w-[0.9em] shrink-0') ?></div>
            </article>
        </div>
    </section>

    <form action="/subscriber/subscriptions" class="mb-8 grid grid-cols-1 gap-4 rounded-3xl bg-surface-container-lowest p-6 shadow-sm xl:grid-cols-[1fr_0.45fr_auto]" method="get">
        <input class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="q" placeholder="Buscar criador ou plano..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
        <select class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="status">
            <option value="">Todos os status</option>
            <option value="active" <?= (string) ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Ativo</option>
            <option value="cancelled" <?= (string) ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
        </select>
        <div class="flex items-center gap-3">
            <button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
            <a class="rounded-full bg-surface-container-low px-6 py-4 text-sm font-bold text-on-surface-variant" href="/subscriber/subscriptions">Reset</a>
        </div>
    </form>

    <div class="grid grid-cols-1 gap-8 2xl:grid-cols-[1.05fr_0.95fr]">
        <section class="space-y-6">
            <h3 class="text-2xl font-extrabold">Planos ativos</h3>
            <div class="space-y-5">
                <?php foreach ($subscriptions as $subscription): ?>
                    <?php $creator = $subscription['creator'] ?? []; $plan = $subscription['plan'] ?? []; ?>
                    <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                            <div class="flex items-start gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 font-bold text-primary"><?= e(avatar_initials((string) ($creator['name'] ?? 'Criador'))) ?></div>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="break-words text-xl font-bold"><?= e((string) ($creator['name'] ?? 'Criador')) ?></h4>
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-emerald-700"><?= e((string) ($subscription['status'] ?? 'active')) ?></span>
                                    </div>
                                    <p class="mt-1 text-sm text-on-surface-variant">@<?= e((string) ($creator['slug'] ?? 'criador')) ?></p>
                                    <p class="mt-3 text-sm text-on-surface-variant"><?= e((string) ($plan['name'] ?? 'Plano')) ?> • <?= luacoin_amount_html((int) ($plan['price_tokens'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?></p>
                                    <p class="mt-1 text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Renova em <?= e((string) ($subscription['days_to_renew'] ?? 0)) ?> dias</p>
                                </div>
                            </div>
                            <div class="flex w-full flex-col gap-3 lg:w-auto">
                                <a class="rounded-full bg-surface-container-low px-5 py-3 text-center text-sm font-bold text-on-surface" href="<?= e('/profile?id=' . (int) ($creator['id'] ?? 0)) ?>">Abrir perfil</a>
                                <form action="/subscriber/subscriptions/cancel" method="post" onsubmit="return confirm('Cancelar esta assinatura?');">
                                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                    <input name="subscription_id" type="hidden" value="<?= e((string) ($subscription['id'] ?? 0)) ?>">
                                    <button class="w-full rounded-full bg-rose-50 px-5 py-3 text-sm font-bold text-rose-700" data-prototype-skip="1" type="submit">Cancelar</button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if ($subscriptions === []): ?>
                    <div class="rounded-3xl bg-surface-container-low p-8 text-sm text-on-surface-variant">Nenhuma assinatura encontrada nesse filtro.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="space-y-6">
            <h3 class="text-2xl font-extrabold">Novos planos para explorar</h3>
            <div class="space-y-5">
                <?php foreach ($plans as $plan): ?>
                    <?php $creator = $plan['creator'] ?? []; ?>
                    <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <a class="text-xl font-bold hover:text-primary" href="<?= e('/profile?id=' . (int) ($creator['id'] ?? 0)) ?>"><?= e((string) ($creator['name'] ?? 'Criador')) ?></a>
                                <p class="mt-1 text-sm text-on-surface-variant">@<?= e((string) ($creator['slug'] ?? 'criador')) ?></p>
                            </div>
                            <span class="rounded-full bg-primary/10 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-primary"><?= luacoin_amount_html((int) ($plan['price_tokens'] ?? 0), 'inline-flex items-center gap-1 whitespace-nowrap', '', 'h-3 w-3 shrink-0') ?></span>
                        </div>
                        <p class="mt-4 text-lg font-bold"><?= e((string) ($plan['name'] ?? 'Plano')) ?></p>
                        <p class="mt-2 text-sm text-on-surface-variant"><?= e(excerpt((string) ($plan['description'] ?? ''), 120)) ?></p>
                        <?php if (! empty($plan['perks'])): ?>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <?php foreach ((array) $plan['perks'] as $perk): ?>
                                    <span class="rounded-full bg-surface-container-low px-3 py-2 text-[11px] font-bold uppercase tracking-widest text-on-surface-variant"><?= e((string) $perk) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <form action="/subscriber/subscriptions/subscribe" class="mt-5" method="post">
                            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                            <input name="plan_id" type="hidden" value="<?= e((string) ($plan['id'] ?? 0)) ?>">
                            <button class="signature-glow w-full rounded-full px-5 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Assinar agora</button>
                        </form>
                    </article>
                <?php endforeach; ?>
                <?php if ($plans === []): ?>
                    <div class="rounded-3xl bg-surface-container-low p-8 text-sm text-on-surface-variant">Nenhum novo plano disponivel nesse filtro.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>
</body>
</html>
