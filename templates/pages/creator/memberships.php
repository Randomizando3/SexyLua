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
$selectedPlanPayload = null;

if ($selectedPlan) {
    $selectedPlanPayload = [
        'id' => (int) ($selectedPlan['id'] ?? 0),
        'name' => (string) ($selectedPlan['name'] ?? ''),
        'label' => (string) ($selectedPlan['label'] ?? ''),
        'description' => (string) ($selectedPlan['description'] ?? ''),
        'price_luacoins' => (int) ($selectedPlan['price_tokens'] ?? 49),
        'active' => (bool) ($selectedPlan['active'] ?? true),
        'perks' => implode("\n", (array) ($selectedPlan['perks'] ?? [])),
    ];
}
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Gest&atilde;o de Assinaturas</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style>
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
        body { background-color: #fbf9fb; color: #1b1c1d; font-family: "Manrope", sans-serif; }
        h1, h2, h3, h4 { font-family: "Plus Jakarta Sans", sans-serif; }
        .signature-glow { background: linear-gradient(135deg, #ab1155 0%, #cc326e 100%); }
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
$creatorTopbarLabel = 'Gestao de Assinaturas';
$creatorTopbarAction = ['href' => '/creator/live', 'label' => 'Entrar ao vivo'];
include base_path('templates/partials/creator_sidebar.php');
include base_path('templates/partials/creator_topbar.php');
?>

<main class="min-h-screen pt-20 lg:ml-64">
    <div class="space-y-8 px-6 pb-10 lg:px-10">
        <section class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#ab1155]">Dashboard de membros</p>
                <h2 class="mt-2 text-4xl font-extrabold tracking-tight lg:text-5xl">Assinaturas e membros</h2>
                <p class="mt-3 max-w-3xl text-slate-500">Agora os planos ficam em uma vis&atilde;o mais compacta, no mesmo clima de gest&atilde;o da base de assinantes, enquanto a cria&ccedil;&atilde;o e a edi&ccedil;&atilde;o acontecem em modal.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button class="inline-flex items-center justify-center rounded-full bg-[#f5f3f5] px-6 py-3 text-sm font-bold text-[#5a4044]" data-plan-open="new" type="button">Novo plano</button>
                <a class="inline-flex items-center justify-center rounded-full bg-[#f5f3f5] px-6 py-3 text-sm font-bold text-[#5a4044]" href="#members-dashboard">Ir para membros</a>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]"><p class="text-[10px] font-bold uppercase tracking-[0.28em] text-slate-400">Assinantes ativos</p><p class="mt-3 text-4xl font-extrabold text-[#ab1155]"><?= e((string) $activeSubscribers) ?></p><p class="mt-2 text-sm text-slate-500"><?= e((string) $subscriberCount) ?> membros na base</p></article>
            <article class="rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]"><p class="text-[10px] font-bold uppercase tracking-[0.28em] text-slate-400">Recorr&ecirc;ncia</p><p class="mt-3 text-4xl font-extrabold text-[#ab1155]"><?= e(luacoins_amount($monthlyTokens)) ?></p><p class="mt-2 text-sm text-slate-500">Estimativa mensal dos planos ativos</p></article>
            <article class="rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]"><p class="text-[10px] font-bold uppercase tracking-[0.28em] text-slate-400">Planos</p><p class="mt-3 text-4xl font-extrabold text-[#ab1155]"><?= e((string) $planCount) ?></p><p class="mt-2 text-sm text-slate-500">Ofertas dispon&iacute;veis hoje</p></article>
            <article class="rounded-3xl bg-white p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]"><p class="text-[10px] font-bold uppercase tracking-[0.28em] text-slate-400">VIP e pausados</p><p class="mt-3 text-4xl font-extrabold text-[#ab1155]"><?= e((string) $vipCount) ?></p><p class="mt-2 text-sm text-slate-500"><?= e((string) $pausedCount) ?> pausados no filtro geral</p></article>
        </section>

        <section class="rounded-3xl bg-white p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#ab1155]">Planos ativos</p>
                    <h3 class="mt-2 text-3xl font-extrabold">Vis&atilde;o geral dos planos</h3>
                    <p class="mt-2 text-sm text-slate-500">Cada plano aparece no mesmo padr&atilde;o visual dos membros: status, valor, benef&iacute;cios, base ativa e a&ccedil;&otilde;es r&aacute;pidas.</p>
                </div>
                <button class="inline-flex items-center justify-center rounded-full bg-[#f5f3f5] px-6 py-3 text-sm font-bold text-[#5a4044]" data-plan-open="new" type="button">Criar plano</button>
            </div>

            <div class="mt-6 space-y-4">
                <?php foreach ($plans as $plan): ?>
                    <?php
                    $planPayload = [
                        'id' => (int) ($plan['id'] ?? 0),
                        'name' => (string) ($plan['name'] ?? ''),
                        'label' => (string) ($plan['label'] ?? ''),
                        'description' => (string) ($plan['description'] ?? ''),
                        'price_luacoins' => (int) ($plan['price_tokens'] ?? 49),
                        'active' => (bool) ($plan['active'] ?? true),
                        'perks' => implode("\n", (array) ($plan['perks'] ?? [])),
                    ];
                    ?>
                    <article class="rounded-2xl bg-[#f5f3f5] p-5">
                        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)_minmax(0,0.55fr)_auto]">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="truncate text-lg font-bold"><?= e((string) ($plan['name'] ?? 'Plano')) ?></h4>
                                    <?php if ((string) ($plan['label'] ?? '') !== ''): ?>
                                        <span class="rounded-full bg-[#fd6c9c]/20 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-[#ab1155]"><?= e((string) $plan['label']) ?></span>
                                    <?php endif; ?>
                                    <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest <?= (bool) ($plan['active'] ?? false) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' ?>"><?= (bool) ($plan['active'] ?? false) ? 'Ativo' : 'Inativo' ?></span>
                                </div>
                                <p class="mt-2 text-sm text-[#5a4044]"><?= e(excerpt((string) ($plan['description'] ?? ''), 140)) ?></p>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Benef&iacute;cios</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <?php foreach ((array) ($plan['perks'] ?? []) as $perk): ?>
                                        <span class="rounded-full bg-white px-3 py-2 text-[11px] font-bold uppercase tracking-widest text-[#5a4044]"><?= e((string) $perk) ?></span>
                                    <?php endforeach; ?>
                                    <?php if (empty($plan['perks'])): ?>
                                        <span class="rounded-full bg-white px-3 py-2 text-[11px] font-bold uppercase tracking-widest text-slate-400">Sem benef&iacute;cios extras</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="rounded-2xl bg-white px-5 py-4 text-sm">
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Mensalidade</p>
                                <p class="mt-2 text-2xl font-extrabold text-[#ab1155]"><?= e(token_amount((int) ($plan['price_tokens'] ?? 0))) ?></p>
                                <p class="mt-2 font-semibold text-[#5a4044]"><?= e((string) ($plan['subscriber_count'] ?? 0)) ?> assinantes</p>
                            </div>

                            <div class="flex flex-col gap-3">
                                <button class="rounded-full bg-white px-5 py-3 text-xs font-bold uppercase tracking-widest text-[#1b1c1d]" data-plan-open="edit" data-plan="<?= e((string) json_encode($planPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>" type="button">Editar</button>
                                <form action="/creator/memberships/delete" method="post" onsubmit="return confirm('Remover ou desativar este plano?');">
                                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                    <input name="plan_id" type="hidden" value="<?= e((string) ($plan['id'] ?? 0)) ?>">
                                    <input name="redirect" type="hidden" value="<?= e($redirectBase) ?>">
                                    <button class="w-full rounded-full bg-rose-50 px-5 py-3 text-xs font-bold uppercase tracking-widest text-rose-700" data-prototype-skip="1" type="submit"><?= (int) ($plan['subscriber_count'] ?? 0) > 0 ? 'Desativar' : 'Excluir' ?></button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>

                <?php if ($plans === []): ?>
                    <div class="rounded-2xl border border-dashed border-[#e3bdc3] bg-[#f5f3f5] p-8 text-center text-sm text-[#5a4044]">
                        Nenhum plano foi criado ainda. Use o bot&atilde;o acima para abrir o modal e montar a primeira assinatura.
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <section class="rounded-3xl bg-white p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)]" id="members-dashboard">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#ab1155]">Base de assinantes</p>
                    <h3 class="mt-2 text-3xl font-extrabold">Gerenciar membros</h3>
                    <p class="mt-2 text-sm text-slate-500"><?= e((string) $filteredCount) ?> resultado(s) no filtro atual.</p>
                </div>
                <div class="flex flex-wrap gap-3 text-sm text-slate-500">
                    <span class="rounded-full bg-[#f5f3f5] px-4 py-2 font-semibold"><?= e((string) $vipCount) ?> VIP</span>
                    <span class="rounded-full bg-[#f5f3f5] px-4 py-2 font-semibold"><?= e((string) $pausedCount) ?> pausados</span>
                </div>
            </div>

            <form action="/creator/memberships" class="mt-6 grid grid-cols-1 gap-4 rounded-3xl bg-[#f5f3f5] p-5 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_minmax(0,0.6fr)_auto]" method="get">
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-[#5a4044]">Busca</span>
                    <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-[#ab1155]/20" name="q" placeholder="Nome, email ou plano" type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
                </label>
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-[#5a4044]">Status</span>
                    <select class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-[#ab1155]/20" name="subscriber_status">
                        <option value="">Todos</option>
                        <option value="active" <?= (string) ($filters['subscriber_status'] ?? '') === 'active' ? 'selected' : '' ?>>Ativo</option>
                        <option value="paused" <?= (string) ($filters['subscriber_status'] ?? '') === 'paused' ? 'selected' : '' ?>>Pausado</option>
                        <option value="cancelled" <?= (string) ($filters['subscriber_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </label>
                <div class="flex flex-wrap items-end gap-3">
                    <button class="min-w-[120px] rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
                    <a class="min-w-[110px] rounded-full bg-white px-5 py-4 text-center text-sm font-bold text-[#5a4044]" href="/creator/memberships">Reset</a>
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
                    <article class="rounded-2xl bg-[#f5f3f5] p-5">
                        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.7fr)_minmax(0,1.15fr)_auto]">
                            <div class="flex min-w-0 gap-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-[#ab1155]/20 to-[#fd6c9c]/35 text-xl font-bold text-[#ab1155]"><?= e(avatar_initials((string) ($subscriber['name'] ?? 'Assinante'))) ?></div>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="truncate text-lg font-bold"><?= e((string) ($subscriber['name'] ?? 'Assinante')) ?></h4>
                                        <span class="<?= e($statusClass) ?> rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest"><?= e($status) ?></span>
                                        <?php if ((bool) ($subscription['vip'] ?? false)): ?>
                                            <span class="rounded-full bg-[#ab1155]/10 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-[#ab1155]">VIP</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mt-2 break-all text-sm text-[#5a4044]"><?= e((string) ($subscriber['email'] ?? '')) ?></p>
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
                                    <textarea class="min-h-[110px] w-full rounded-2xl border-none bg-white px-5 py-4 text-sm shadow-sm focus:ring-2 focus:ring-[#ab1155]/20" name="creator_note"><?= e((string) ($subscription['creator_note'] ?? '')) ?></textarea>
                                </label>
                                <button class="mt-3 rounded-full bg-white px-5 py-3 text-xs font-bold uppercase tracking-widest text-[#1b1c1d]" data-prototype-skip="1" type="submit">Salvar nota</button>
                            </form>

                            <div class="flex flex-col gap-3">
                                <form action="/creator/memberships/subscription" method="post">
                                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                    <input name="subscription_id" type="hidden" value="<?= e((string) ($subscription['id'] ?? 0)) ?>">
                                    <input name="action" type="hidden" value="toggle_vip">
                                    <input name="redirect" type="hidden" value="<?= e($redirectBase) ?>">
                                    <button class="w-full rounded-full bg-[#ab1155]/10 px-4 py-3 text-xs font-bold uppercase tracking-widest text-[#ab1155]" data-prototype-skip="1" type="submit"><?= (bool) ($subscription['vip'] ?? false) ? 'Remover VIP' : 'Marcar VIP' ?></button>
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
                <div class="mt-6 rounded-2xl border border-dashed border-[#e3bdc3] bg-[#f5f3f5] p-12 text-center">
                    <span class="material-symbols-outlined text-5xl text-[#ab1155]">groups</span>
                    <h3 class="mt-4 text-2xl font-extrabold">Nenhum membro neste filtro</h3>
                    <p class="mt-2 text-[#5a4044]">Mude o filtro de status ou limpe a busca para visualizar toda a base.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<div class="fixed inset-0 z-[90] hidden items-center justify-center bg-slate-950/40 px-4 py-6 backdrop-blur-sm" data-plan-modal>
    <div class="w-full max-w-3xl rounded-3xl bg-white p-8 shadow-[0px_30px_80px_rgba(27,28,29,0.22)]">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#ab1155]" data-plan-modal-kicker>Novo plano</p>
                <h3 class="mt-2 text-3xl font-extrabold" data-plan-modal-title>Montar novo plano</h3>
                <p class="mt-2 text-sm text-slate-500">Preencha as informa&ccedil;&otilde;es do plano e salve sem sair desta tela.</p>
            </div>
            <button class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-[#f5f3f5] text-slate-500" data-plan-close type="button">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="/creator/memberships/save" class="mt-8 space-y-5" method="post" data-plan-form>
            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
            <input name="id" type="hidden" value="" data-plan-field="id">

            <div class="grid gap-5 md:grid-cols-2">
                <label class="block space-y-2 md:col-span-2">
                    <span class="text-sm font-semibold text-[#5a4044]">Nome do plano</span>
                    <input class="w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4 shadow-sm focus:ring-2 focus:ring-[#ab1155]/20" data-plan-field="name" name="name" required type="text" value="">
                </label>
                <label class="block space-y-2">
                    <span class="text-sm font-semibold text-[#5a4044]">Label curta</span>
                    <input class="w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4 shadow-sm focus:ring-2 focus:ring-[#ab1155]/20" data-plan-field="label" name="label" placeholder="Ex: VIP" type="text" value="">
                </label>
                <label class="block space-y-2">
                    <span class="text-sm font-semibold text-[#5a4044]">Pre&ccedil;o em LuaCoins</span>
                    <input class="w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4 shadow-sm focus:ring-2 focus:ring-[#ab1155]/20" data-plan-field="price_luacoins" min="1" name="price_luacoins" required type="number" value="49">
                </label>
                <label class="block space-y-2 md:col-span-2">
                    <span class="text-sm font-semibold text-[#5a4044]">Descri&ccedil;&atilde;o</span>
                    <textarea class="min-h-[130px] w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4 shadow-sm focus:ring-2 focus:ring-[#ab1155]/20" data-plan-field="description" name="description"></textarea>
                </label>
                <label class="block space-y-2 md:col-span-2">
                    <span class="text-sm font-semibold text-[#5a4044]">Benef&iacute;cios (uma linha por item)</span>
                    <textarea class="min-h-[150px] w-full rounded-2xl border-none bg-[#f5f3f5] px-5 py-4 shadow-sm focus:ring-2 focus:ring-[#ab1155]/20" data-plan-field="perks" name="perks"></textarea>
                </label>
            </div>

            <label class="flex items-center gap-3 text-sm font-semibold text-[#1b1c1d]">
                <input checked class="rounded border-none text-[#ab1155] focus:ring-[#ab1155]/20" data-plan-field="active" name="active" type="checkbox" value="1">
                Plano ativo para novas assinaturas
            </label>

            <div class="flex flex-wrap gap-3">
                <button class="signature-glow rounded-full px-7 py-4 text-sm font-bold text-white shadow-[0px_20px_40px_rgba(171,17,85,0.2)]" data-prototype-skip="1" type="submit">Salvar plano</button>
                <button class="rounded-full bg-[#f5f3f5] px-6 py-4 text-sm font-bold text-[#5a4044]" data-plan-close type="button">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<?php if ($selectedPlanPayload !== null): ?>
    <script id="plan-selected-data" type="application/json"><?= e((string) json_encode($selectedPlanPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></script>
<?php endif; ?>
<script>
    (() => {
        const modal = document.querySelector('[data-plan-modal]');
        const form = document.querySelector('[data-plan-form]');
        if (!modal || !form) return;

        const title = modal.querySelector('[data-plan-modal-title]');
        const kicker = modal.querySelector('[data-plan-modal-kicker]');
        const fields = {
            id: form.querySelector('[data-plan-field="id"]'),
            name: form.querySelector('[data-plan-field="name"]'),
            label: form.querySelector('[data-plan-field="label"]'),
            description: form.querySelector('[data-plan-field="description"]'),
            price: form.querySelector('[data-plan-field="price_luacoins"]'),
            perks: form.querySelector('[data-plan-field="perks"]'),
            active: form.querySelector('[data-plan-field="active"]'),
        };

        const emptyPlan = { id: '', name: '', label: '', description: '', price_luacoins: 49, perks: '', active: true };

        const applyPlan = (plan) => {
            const payload = { ...emptyPlan, ...(plan || {}) };
            fields.id.value = payload.id || '';
            fields.name.value = payload.name || '';
            fields.label.value = payload.label || '';
            fields.description.value = payload.description || '';
            fields.price.value = payload.price_luacoins || 49;
            fields.perks.value = payload.perks || '';
            fields.active.checked = payload.active !== false;
            kicker.textContent = payload.id ? 'Editar plano' : 'Novo plano';
            title.textContent = payload.id ? (payload.name || 'Editar plano') : 'Montar novo plano';
        };

        const openModal = (plan) => {
            applyPlan(plan);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        };

        document.querySelectorAll('[data-plan-open]').forEach((button) => {
            button.addEventListener('click', () => {
                if (button.getAttribute('data-plan-open') === 'edit') {
                    try {
                        openModal(JSON.parse(button.getAttribute('data-plan') || '{}'));
                        return;
                    } catch (error) {
                    }
                }
                openModal(emptyPlan);
            });
        });

        modal.querySelectorAll('[data-plan-close]').forEach((button) => button.addEventListener('click', closeModal));
        modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });
        document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });

        const selectedPlanNode = document.getElementById('plan-selected-data');
        if (selectedPlanNode) {
            try { openModal(JSON.parse(selectedPlanNode.textContent || '{}')); } catch (error) {}
        }
    })();
</script>
</body>
</html>
