<?php

declare(strict_types=1);

$creator = $data['creator'] ?? [];
$plans = $data['plans'] ?? [];
$selectedPlan = $data['selected_plan'] ?? null;
$allSubscribers = $data['subscribers'] ?? [];
$subscribers = $data['filtered_subscribers'] ?? $allSubscribers;
$filters = $data['filters'] ?? [];
$summary = $data['summary'] ?? [];
$activeSubscribers = (int) ($data['active_subscribers'] ?? 0);
$selectedPerks = $selectedPlan ? implode("\n", (array) ($selectedPlan['perks'] ?? [])) : '';
$redirectBase = path_with_query('/creator/memberships', [
    'q' => $filters['q'] ?? '',
    'subscriber_status' => $filters['subscriber_status'] ?? '',
]);
$planCount = count($plans);
$subscriberCount = count($allSubscribers);
$filteredCount = count($subscribers);
$vipCount = (int) ($summary['vip_count'] ?? 0);
$pausedCount = (int) ($summary['paused_count'] ?? 0);
$monthlyTokens = (int) ($summary['monthly_tokens'] ?? 0);
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Gestão de Assinaturas</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
<?php
ob_start();
?>
<form action="/creator/memberships" class="hidden items-center gap-4 lg:flex" method="get">
    <div class="relative">
        <input class="w-72 rounded-full border-none bg-white/10 py-2 pl-10 pr-4 text-sm text-white placeholder:text-white/60 focus:ring-1 focus:ring-white/30" name="q" placeholder="Buscar membros..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
        <span class="material-symbols-outlined absolute left-3 top-2 text-lg text-white/60">search</span>
    </div>
    <?php if (($filters['subscriber_status'] ?? '') !== ''): ?>
        <input name="subscriber_status" type="hidden" value="<?= e((string) $filters['subscriber_status']) ?>">
    <?php endif; ?>
</form>
<?php
$creatorTopbarSearch = (string) ob_get_clean();
$creatorShellCreator = $creator;
$creatorShellCurrent = 'memberships';
$creatorShellCta = ['href' => '#plan-editor', 'label' => $selectedPlan ? 'Editar plano' : 'Novo plano', 'icon' => 'add_circle'];
$creatorTopbarLabel = 'Gestão de Assinaturas';
$creatorTopbarAction = ['href' => '/creator/live', 'label' => 'Entrar ao vivo'];
include base_path('templates/partials/creator_sidebar.php');
include base_path('templates/partials/creator_topbar.php');
?>

<main class="min-h-screen pt-20 lg:ml-64">
    <div class="space-y-8 px-6 pb-10 lg:px-10">
        <section class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Dashboard de membros</p>
                <h2 class="mt-2 text-4xl font-extrabold tracking-tight lg:text-5xl">Assinaturas e membros</h2>
                <p class="mt-3 max-w-3xl text-slate-500">Organize seus planos em um bloco só e gerencie os membros com uma visão mais direta de status, renovação, nota interna e ações rápidas.</p>
            </div>
            <a class="inline-flex items-center justify-center rounded-full bg-surface-container-low px-6 py-3 text-sm font-bold text-on-surface-variant" href="#members-dashboard">Ir para membros</a>
        </section>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                <p class="text-[10px] font-bold uppercase tracking-[0.28em] text-slate-400">Assinantes ativos</p>
                <p class="mt-3 text-4xl font-extrabold text-primary"><?= e((string) $activeSubscribers) ?></p>
                <p class="mt-2 text-sm text-slate-500"><?= e((string) $subscriberCount) ?> membros na base</p>
            </article>
            <article class="rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                <p class="text-[10px] font-bold uppercase tracking-[0.28em] text-slate-400">Recorrência</p>
                <p class="mt-3 text-4xl font-extrabold text-primary"><?= e(luacoins_amount($monthlyTokens)) ?></p>
                <p class="mt-2 text-sm text-slate-500">Estimativa mensal dos planos ativos</p>
            </article>
            <article class="rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                <p class="text-[10px] font-bold uppercase tracking-[0.28em] text-slate-400">Planos</p>
                <p class="mt-3 text-4xl font-extrabold text-primary"><?= e((string) $planCount) ?></p>
                <p class="mt-2 text-sm text-slate-500">Ofertas cadastradas no momento</p>
            </article>
            <article class="rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                <p class="text-[10px] font-bold uppercase tracking-[0.28em] text-slate-400">VIP e pausados</p>
                <p class="mt-3 text-4xl font-extrabold text-primary"><?= e((string) $vipCount) ?></p>
                <p class="mt-2 text-sm text-slate-500"><?= e((string) $pausedCount) ?> pausados no filtro geral</p>
            </article>
        </section>

        <div class="grid grid-cols-1 gap-8 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
            <section class="space-y-6" id="plan-editor">
                <form action="/creator/memberships/save" class="rounded-3xl bg-white p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]" method="post">
                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                    <?php if ($selectedPlan): ?>
                        <input name="id" type="hidden" value="<?= e((string) ($selectedPlan['id'] ?? 0)) ?>">
                    <?php endif; ?>

                    <div class="mb-6">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary"><?= $selectedPlan ? 'Editar plano' : 'Novo plano' ?></p>
                        <h3 class="mt-2 text-3xl font-extrabold"><?= $selectedPlan ? e((string) ($selectedPlan['name'] ?? 'Plano')) : 'Montar novo plano' ?></h3>
                        <p class="mt-2 text-sm text-slate-500">Defina o valor, a descrição e os benefícios sem espalhar essa gestão pela tela toda.</p>
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
                            <span class="text-sm font-semibold text-on-surface-variant">Preço em LuaCoins</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="1" name="price_luacoins" required type="number" value="<?= e((string) ($selectedPlan['price_tokens'] ?? 49)) ?>">
                        </label>
                        <label class="block space-y-2 md:col-span-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Descrição</span>
                            <textarea class="min-h-[130px] w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="description"><?= e((string) ($selectedPlan['description'] ?? '')) ?></textarea>
                        </label>
                        <label class="block space-y-2 md:col-span-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Benefícios (uma linha por item)</span>
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
            </section>

            <section class="space-y-6">
                <div class="rounded-3xl bg-white p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary">Planos ativos</p>
                            <h3 class="mt-2 text-3xl font-extrabold">Visão geral dos planos</h3>
                        </div>
                        <p class="text-sm text-slate-500"><?= e((string) $planCount) ?> plano(s) cadastrados</p>
                    </div>

                    <div class="mt-6 space-y-4">
                        <?php foreach ($plans as $plan): ?>
                            <article class="rounded-2xl bg-surface-container-low p-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="text-xl font-bold"><?= e((string) ($plan['name'] ?? 'Plano')) ?></h4>
                                            <?php if ((string) ($plan['label'] ?? '') !== ''): ?>
                                                <span class="rounded-full bg-secondary-container/20 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-primary"><?= e((string) $plan['label']) ?></span>
                                            <?php endif; ?>
                                            <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest <?= (bool) ($plan['active'] ?? false) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' ?>"><?= (bool) ($plan['active'] ?? false) ? 'Ativo' : 'Inativo' ?></span>
                                        </div>
                                        <p class="mt-2 text-sm text-on-surface-variant"><?= e(excerpt((string) ($plan['description'] ?? ''), 130)) ?></p>
                                        <?php if (! empty($plan['perks'])): ?>
                                            <div class="mt-4 flex flex-wrap gap-2">
                                                <?php foreach ((array) $plan['perks'] as $perk): ?>
                                                    <span class="rounded-full bg-white px-3 py-2 text-[11px] font-bold uppercase tracking-widest text-on-surface-variant"><?= e((string) $perk) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="min-w-[180px] rounded-2xl bg-white px-5 py-4 text-sm">
                                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Mensalidade</p>
                                        <p class="mt-2 text-2xl font-extrabold text-primary"><?= e(token_amount((int) ($plan['price_tokens'] ?? 0))) ?></p>
                                        <p class="mt-2 font-semibold text-on-surface-variant"><?= e((string) ($plan['subscriber_count'] ?? 0)) ?> assinantes</p>
                                    </div>
                                </div>

                                <div class="mt-5 flex flex-wrap gap-3">
                                    <a class="rounded-full bg-white px-5 py-3 text-xs font-bold uppercase tracking-widest text-on-surface" href="<?= e(path_with_query('/creator/memberships', ['plan' => (int) ($plan['id'] ?? 0), 'q' => $filters['q'] ?? '', 'subscriber_status' => $filters['subscriber_status'] ?? ''])) ?>">Editar</a>
                                    <form action="/creator/memberships/delete" method="post" onsubmit="return confirm('Remover ou desativar este plano?');">
                                        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                        <input name="plan_id" type="hidden" value="<?= e((string) ($plan['id'] ?? 0)) ?>">
                                        <input name="redirect" type="hidden" value="<?= e($redirectBase) ?>">
                                        <button class="rounded-full bg-rose-50 px-5 py-3 text-xs font-bold uppercase tracking-widest text-rose-700" data-prototype-skip="1" type="submit"><?= (int) ($plan['subscriber_count'] ?? 0) > 0 ? 'Desativar' : 'Excluir' ?></button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>

                        <?php if ($plans === []): ?>
                            <div class="rounded-2xl border border-dashed border-outline-variant bg-surface-container-low p-8 text-center text-sm text-on-surface-variant">
                                Nenhum plano foi criado ainda. Use o editor ao lado para montar a primeira assinatura.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>

        <section class="rounded-3xl bg-white p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]" id="members-dashboard">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary">Base de assinantes</p>
                    <h3 class="mt-2 text-3xl font-extrabold">Gerenciar membros</h3>
                    <p class="mt-2 text-sm text-slate-500"><?= e((string) $filteredCount) ?> resultado(s) no filtro atual.</p>
                </div>
                <div class="flex flex-wrap gap-3 text-sm text-slate-500">
                    <span class="rounded-full bg-surface-container-low px-4 py-2 font-semibold"><?= e((string) $vipCount) ?> VIP</span>
                    <span class="rounded-full bg-surface-container-low px-4 py-2 font-semibold"><?= e((string) $pausedCount) ?> pausados</span>
                </div>
            </div>

            <form action="/creator/memberships" class="mt-6 grid grid-cols-1 gap-4 rounded-3xl bg-surface-container-low p-5 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_minmax(0,0.6fr)_auto]" method="get">
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-on-surface-variant">Busca</span>
                    <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="q" placeholder="Nome, email ou plano" type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
                </label>
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-on-surface-variant">Status</span>
                    <select class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="subscriber_status">
                        <option value="">Todos</option>
                        <option value="active" <?= (string) ($filters['subscriber_status'] ?? '') === 'active' ? 'selected' : '' ?>>Ativo</option>
                        <option value="paused" <?= (string) ($filters['subscriber_status'] ?? '') === 'paused' ? 'selected' : '' ?>>Pausado</option>
                        <option value="cancelled" <?= (string) ($filters['subscriber_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </label>
                <div class="flex flex-wrap items-end gap-3">
                    <button class="min-w-[120px] rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
                    <a class="min-w-[110px] rounded-full bg-white px-5 py-4 text-center text-sm font-bold text-on-surface-variant" href="/creator/memberships">Reset</a>
                </div>
            </form>

            <div class="mt-6 space-y-4">
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
                    <article class="rounded-2xl bg-surface-container-low p-5">
                        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.7fr)_minmax(0,1.15fr)_auto]">
                            <div class="flex min-w-0 gap-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-primary/20 to-secondary-container/40 text-xl font-bold text-primary"><?= e(avatar_initials((string) ($subscriber['name'] ?? 'Assinante'))) ?></div>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="truncate text-lg font-bold"><?= e((string) ($subscriber['name'] ?? 'Assinante')) ?></h4>
                                        <span class="<?= e($statusClass) ?> rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest"><?= e($status) ?></span>
                                        <?php if ((bool) ($subscription['vip'] ?? false)): ?>
                                            <span class="rounded-full bg-primary/10 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-primary">VIP</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mt-2 break-all text-sm text-on-surface-variant"><?= e((string) ($subscriber['email'] ?? '')) ?></p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 text-sm xl:grid-cols-1">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Plano</p>
                                    <p class="mt-1 break-words font-semibold"><?= e((string) ($plan['name'] ?? 'Plano ativo')) ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Renova em</p>
                                    <p class="mt-1 font-semibold"><?= e((string) ($subscription['days_to_renew'] ?? 0)) ?> dias</p>
                                </div>
                            </div>

                            <form action="/creator/memberships/subscription" method="post">
                                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                <input name="subscription_id" type="hidden" value="<?= e((string) ($subscription['id'] ?? 0)) ?>">
                                <input name="action" type="hidden" value="note">
                                <input name="redirect" type="hidden" value="<?= e($redirectBase) ?>">
                                <label class="block space-y-2">
                                    <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Nota interna</span>
                                    <textarea class="min-h-[110px] w-full rounded-2xl border-none bg-white px-5 py-4 text-sm shadow-sm focus:ring-2 focus:ring-primary/20" name="creator_note"><?= e((string) ($subscription['creator_note'] ?? '')) ?></textarea>
                                </label>
                                <button class="mt-3 rounded-full bg-white px-5 py-3 text-xs font-bold uppercase tracking-widest text-on-surface" data-prototype-skip="1" type="submit">Salvar nota</button>
                            </form>

                            <div class="flex flex-col gap-3">
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
                                <form action="/creator/memberships/subscription" method="post" onsubmit="return confirm('Cancelar o acesso deste assinante?');">
                                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                    <input name="subscription_id" type="hidden" value="<?= e((string) ($subscription['id'] ?? 0)) ?>">
                                    <input name="action" type="hidden" value="cancel">
                                    <input name="redirect" type="hidden" value="<?= e($redirectBase) ?>">
                                    <button class="w-full rounded-full bg-rose-50 px-4 py-3 text-xs font-bold uppercase tracking-widest text-rose-700" data-prototype-skip="1" type="submit">Cancelar</button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($subscribers === []): ?>
                <div class="mt-6 rounded-2xl border border-dashed border-outline-variant bg-surface-container-low p-12 text-center">
                    <span class="material-symbols-outlined text-5xl text-primary">groups</span>
                    <h3 class="mt-4 text-2xl font-extrabold">Nenhum membro neste filtro</h3>
                    <p class="mt-2 text-on-surface-variant">Mude o filtro de status ou limpe a busca para visualizar toda a base.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>
</body>
</html>
