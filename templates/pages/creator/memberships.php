<?php

declare(strict_types=1);

$creator = $data['creator'] ?? [];
$plans = $data['plans'] ?? [];
$selectedPlan = $data['selected_plan'] ?? null;
$subscribers = $data['filtered_subscribers'] ?? $data['subscribers'] ?? [];
$filters = $data['filters'] ?? [];
$summary = $data['summary'] ?? [];
$activeSubscribers = (int) ($data['active_subscribers'] ?? 0);
$selectedPerks = $selectedPlan ? implode("\n", (array) ($selectedPlan['perks'] ?? [])) : '';
$redirectBase = path_with_query('/creator/memberships', [
    'q' => $filters['q'] ?? '',
    'subscriber_status' => $filters['subscriber_status'] ?? '',
]);
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Gestao de Assinaturas</title>
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
                        "secondary-container": "#fd6c9c",
                        "outline-variant": "#e3bdc3",
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
            background-color: #fbf9fb;
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
<header class="fixed top-0 z-[60] flex h-16 w-full items-center justify-between bg-[#D81B60] px-6 font-['Plus_Jakarta_Sans'] font-bold tracking-wide text-white shadow-lg shadow-[#D81B60]/20">
    <div class="flex items-center gap-4">
        <h1 class="text-2xl font-black">SexyLua</h1>
        <span class="hidden border-l border-white/20 pl-4 text-xs uppercase tracking-widest opacity-80 md:block">Creator Studio</span>
    </div>
    <form action="/creator/memberships" class="hidden items-center gap-4 md:flex" method="get">
        <div class="relative">
            <input class="w-64 rounded-full border-none bg-white/10 py-2 pl-10 pr-4 text-sm text-white placeholder:text-white/60 focus:ring-1 focus:ring-white/30" name="q" placeholder="Buscar assinantes..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
            <span class="material-symbols-outlined absolute left-3 top-2 text-lg text-white/60">search</span>
        </div>
        <?php if (($filters['subscriber_status'] ?? '') !== ''): ?>
            <input name="subscriber_status" type="hidden" value="<?= e((string) $filters['subscriber_status']) ?>">
        <?php endif; ?>
    </form>
    <div class="flex items-center gap-3">
        <a class="rounded-full border border-white/20 px-4 py-2 text-xs font-bold uppercase tracking-widest transition-colors hover:bg-white/10" href="/creator/live">Entrar ao vivo</a>
        <div class="flex h-9 w-9 items-center justify-center rounded-full border border-white/20 bg-white/10 font-bold"><?= e(avatar_initials((string) ($creator['name'] ?? 'Criador'))) ?></div>
    </div>
</header>

<aside class="fixed left-0 top-0 z-50 flex h-full w-64 flex-col rounded-r-[3rem] bg-[#f5f3f5] p-6 pt-20 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
    <nav class="flex-1 space-y-2">
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-all duration-300 hover:bg-[#ffffff]/50" href="/creator/content">
            <span class="material-symbols-outlined">movie</span>
            <span class="text-sm font-medium">Conteudo</span>
        </a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-all duration-300 hover:bg-[#ffffff]/50" href="/creator/live">
            <span class="material-symbols-outlined">live_tv</span>
            <span class="text-sm font-medium">Ao vivo</span>
        </a>
        <a class="relative flex items-center gap-4 rounded-full bg-[#ffffff]/50 px-4 py-3 font-bold text-[#ab1155]" href="/creator/memberships">
            <span class="material-symbols-outlined">group</span>
            <span class="text-sm">Assinantes</span>
        </a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-all duration-300 hover:bg-[#ffffff]/50" href="/creator/wallet">
            <span class="material-symbols-outlined">account_balance_wallet</span>
            <span class="text-sm font-medium">Carteira</span>
        </a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-all duration-300 hover:bg-[#ffffff]/50" href="/creator/settings">
            <span class="material-symbols-outlined">settings</span>
            <span class="text-sm font-medium">Configuracoes</span>
        </a>
    </nav>
    <div class="mt-auto">
        <a class="signature-glow flex w-full items-center justify-center gap-2 rounded-full py-4 text-sm font-bold text-white shadow-lg" href="#plan-editor">
            <span class="material-symbols-outlined">add_circle</span>
            <?= $selectedPlan ? 'Editar plano' : 'Novo plano' ?>
        </a>
    </div>
</aside>

<main class="ml-64 min-h-screen pt-16">
    <div class="space-y-10 px-12 py-8">
        <section class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-5xl font-extrabold tracking-tight">Gestao de <span class="italic text-primary">Assinaturas</span></h2>
                <p class="mt-4 max-w-2xl text-slate-500">Crie planos, ajuste precos e acompanhe cada membro com acoes reais de pausa, reativacao, cancelamento, VIP e notas internas.</p>
            </div>
            <div class="flex gap-4">
                <div class="flex flex-col items-center rounded-xl bg-surface-container-lowest p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                    <span class="text-2xl font-bold text-primary"><?= e((string) $activeSubscribers) ?></span>
                    <span class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">Assinantes ativos</span>
                </div>
                <div class="flex flex-col items-center rounded-xl bg-surface-container-lowest p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                    <span class="text-2xl font-bold text-primary"><?= e(token_amount((int) ($summary['monthly_tokens'] ?? 0))) ?></span>
                    <span class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">Recorrencia</span>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-8 xl:grid-cols-[0.95fr_1.05fr]">
            <section class="space-y-6" id="plan-editor">
                <form action="/creator/memberships/save" class="rounded-2xl bg-surface-container-lowest p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]" method="post">
                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                    <?php if ($selectedPlan): ?>
                        <input name="id" type="hidden" value="<?= e((string) ($selectedPlan['id'] ?? 0)) ?>">
                    <?php endif; ?>
                    <div class="mb-6">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary"><?= $selectedPlan ? 'Editar plano' : 'Novo plano' ?></p>
                        <h3 class="mt-2 text-3xl font-extrabold"><?= $selectedPlan ? e((string) ($selectedPlan['name'] ?? 'Plano')) : 'Construa sua oferta' ?></h3>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block space-y-2 md:col-span-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Nome do plano</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="name" required type="text" value="<?= e((string) ($selectedPlan['name'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Label curta</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="label" placeholder="Ex: VIP" type="text" value="<?= e((string) ($selectedPlan['label'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Preco em tokens</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="1" name="price_tokens" required type="number" value="<?= e((string) ($selectedPlan['price_tokens'] ?? 49)) ?>">
                        </label>
                        <label class="block space-y-2 md:col-span-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Descricao</span>
                            <textarea class="min-h-[130px] w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="description"><?= e((string) ($selectedPlan['description'] ?? '')) ?></textarea>
                        </label>
                        <label class="block space-y-2 md:col-span-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Beneficios (uma linha por item)</span>
                            <textarea class="min-h-[150px] w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="perks"><?= e($selectedPerks) ?></textarea>
                        </label>
                    </div>
                    <label class="mt-5 flex items-center gap-3 text-sm font-semibold text-on-surface">
                        <input <?= $selectedPlan === null || (bool) ($selectedPlan['active'] ?? true) ? 'checked' : '' ?> class="rounded border-none text-primary focus:ring-primary/20" name="active" type="checkbox" value="1">
                        Plano ativo para novas assinaturas
                    </label>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <button class="signature-glow rounded-full px-7 py-4 text-sm font-bold text-white shadow-[0px_20px_40px_rgba(171,17,85,0.2)]" data-prototype-skip="1" type="submit">Salvar plano</button>
                        <a class="rounded-full bg-surface-container-low px-6 py-4 text-sm font-bold text-on-surface-variant" href="/creator/memberships">Novo</a>
                    </div>
                </form>

                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($plans as $plan): ?>
                        <article class="rounded-2xl bg-surface-container-lowest p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-xl font-bold"><?= e((string) ($plan['name'] ?? 'Plano')) ?></h4>
                                        <?php if ((string) ($plan['label'] ?? '') !== ''): ?>
                                            <span class="rounded-full bg-secondary-container/20 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-primary"><?= e((string) $plan['label']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mt-2 text-sm text-on-surface-variant"><?= e(excerpt((string) ($plan['description'] ?? ''), 110)) ?></p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest <?= (bool) ($plan['active'] ?? false) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' ?>"><?= (bool) ($plan['active'] ?? false) ? 'Ativo' : 'Inativo' ?></span>
                            </div>
                            <div class="mt-4 flex items-center justify-between text-sm">
                                <strong class="text-primary"><?= e(token_amount((int) ($plan['price_tokens'] ?? 0))) ?></strong>
                                <span class="font-bold text-on-surface-variant"><?= e((string) ($plan['subscriber_count'] ?? 0)) ?> assinantes</span>
                            </div>
                            <?php if (! empty($plan['perks'])): ?>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <?php foreach ((array) $plan['perks'] as $perk): ?>
                                        <span class="rounded-full bg-surface-container-low px-3 py-2 text-[11px] font-bold uppercase tracking-widest text-on-surface-variant"><?= e((string) $perk) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div class="mt-5 grid grid-cols-2 gap-3">
                                <a class="rounded-full bg-surface-container-low px-4 py-3 text-center text-xs font-bold text-on-surface" href="<?= e(path_with_query('/creator/memberships', ['plan' => (int) ($plan['id'] ?? 0), 'q' => $filters['q'] ?? '', 'subscriber_status' => $filters['subscriber_status'] ?? ''])) ?>">Editar</a>
                                <form action="/creator/memberships/delete" method="post" onsubmit="return confirm('Remover ou desativar este plano?');">
                                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                    <input name="plan_id" type="hidden" value="<?= e((string) ($plan['id'] ?? 0)) ?>">
                                    <input name="redirect" type="hidden" value="<?= e($redirectBase) ?>">
                                    <button class="w-full rounded-full bg-rose-50 px-4 py-3 text-xs font-bold text-rose-700" data-prototype-skip="1" type="submit"><?= (int) ($plan['subscriber_count'] ?? 0) > 0 ? 'Desativar' : 'Excluir' ?></button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="space-y-6">
                <form action="/creator/memberships" class="rounded-2xl bg-surface-container-lowest p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]" method="get">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_0.6fr_auto]">
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-on-surface-variant">Busca</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="q" placeholder="Nome, email ou plano" type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-on-surface-variant">Status</span>
                            <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="subscriber_status">
                                <option value="">Todos</option>
                                <option value="active" <?= (string) ($filters['subscriber_status'] ?? '') === 'active' ? 'selected' : '' ?>>Ativo</option>
                                <option value="paused" <?= (string) ($filters['subscriber_status'] ?? '') === 'paused' ? 'selected' : '' ?>>Pausado</option>
                                <option value="cancelled" <?= (string) ($filters['subscriber_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                        </label>
                        <div class="flex items-end gap-3">
                            <button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
                            <a class="rounded-full bg-surface-container-low px-5 py-4 text-sm font-bold text-on-surface-variant" href="/creator/memberships">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <?php foreach ($subscribers as $subscription): ?>
                        <?php
                        $subscriber = $subscription['subscriber'] ?? [];
                        $plan = $subscription['plan'] ?? [];
                        $status = (string) ($subscription['status'] ?? 'active');
                        $statusClass = match ($status) {
                            'active' => 'bg-emerald-100 text-emerald-700',
                            'paused' => 'bg-amber-100 text-amber-700',
                            default => 'bg-slate-200 text-slate-600',
                        };
                        ?>
                        <article class="rounded-xl bg-surface-container-lowest p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                            <div class="flex items-start gap-5">
                                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-primary/20 to-secondary-container/40 text-2xl font-bold text-primary"><?= e(avatar_initials((string) ($subscriber['name'] ?? 'Assinante'))) ?></div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="truncate text-xl font-bold"><?= e((string) ($subscriber['name'] ?? 'Assinante')) ?></h3>
                                        <span class="<?= e($statusClass) ?> rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest"><?= e($status) ?></span>
                                        <?php if ((bool) ($subscription['vip'] ?? false)): ?>
                                            <span class="rounded-full bg-primary/10 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-primary">VIP</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mt-2 text-sm text-on-surface-variant"><?= e((string) ($subscriber['email'] ?? '')) ?></p>
                                    <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Plano</p>
                                            <p class="font-semibold"><?= e((string) ($plan['name'] ?? 'Plano ativo')) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Renova em</p>
                                            <p class="font-semibold"><?= e((string) ($subscription['days_to_renew'] ?? 0)) ?> dias</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form action="/creator/memberships/subscription" class="mt-5" method="post">
                                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                <input name="subscription_id" type="hidden" value="<?= e((string) ($subscription['id'] ?? 0)) ?>">
                                <input name="action" type="hidden" value="note">
                                <input name="redirect" type="hidden" value="<?= e($redirectBase) ?>">
                                <label class="block space-y-2">
                                    <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Nota interna</span>
                                    <textarea class="min-h-[96px] w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-sm shadow-sm focus:ring-2 focus:ring-primary/20" name="creator_note"><?= e((string) ($subscription['creator_note'] ?? '')) ?></textarea>
                                </label>
                                <button class="mt-3 rounded-full bg-surface-container-high px-5 py-3 text-xs font-bold uppercase tracking-widest text-on-surface" data-prototype-skip="1" type="submit">Salvar nota</button>
                            </form>

                            <div class="mt-5 grid grid-cols-2 gap-3">
                                <form action="/creator/memberships/subscription" method="post">
                                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                    <input name="subscription_id" type="hidden" value="<?= e((string) ($subscription['id'] ?? 0)) ?>">
                                    <input name="action" type="hidden" value="toggle_vip">
                                    <input name="redirect" type="hidden" value="<?= e($redirectBase) ?>">
                                    <button class="w-full rounded-full bg-primary/10 px-4 py-3 text-xs font-bold uppercase tracking-widest text-primary" data-prototype-skip="1" type="submit"><?= (bool) ($subscription['vip'] ?? false) ? 'Remover VIP' : 'Marcar VIP' ?></button>
                                </form>
                                <form action="/creator/memberships/subscription" method="post">
                                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                    <input name="subscription_id" type="hidden" value="<?= e((string) ($subscription['id'] ?? 0)) ?>">
                                    <input name="action" type="hidden" value="<?= $status === 'paused' ? 'reactivate' : 'pause' ?>">
                                    <input name="redirect" type="hidden" value="<?= e($redirectBase) ?>">
                                    <button class="w-full rounded-full bg-slate-900 px-4 py-3 text-xs font-bold uppercase tracking-widest text-white" data-prototype-skip="1" type="submit"><?= $status === 'paused' ? 'Reativar' : 'Pausar' ?></button>
                                </form>
                                <form action="/creator/memberships/subscription" class="col-span-2" method="post" onsubmit="return confirm('Cancelar o acesso deste assinante?');">
                                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                    <input name="subscription_id" type="hidden" value="<?= e((string) ($subscription['id'] ?? 0)) ?>">
                                    <input name="action" type="hidden" value="cancel">
                                    <input name="redirect" type="hidden" value="<?= e($redirectBase) ?>">
                                    <button class="w-full rounded-full bg-rose-50 px-4 py-3 text-xs font-bold uppercase tracking-widest text-rose-700" data-prototype-skip="1" type="submit">Cancelar assinatura</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($subscribers === []): ?>
                    <div class="rounded-2xl border border-dashed border-outline-variant bg-surface-container-low p-12 text-center">
                        <span class="material-symbols-outlined text-5xl text-primary">groups</span>
                        <h3 class="mt-4 text-2xl font-extrabold">Nenhum assinante neste filtro</h3>
                        <p class="mt-2 text-on-surface-variant">Mude o filtro de status ou limpe a busca para visualizar toda a base.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>
</body>
</html>
