<?php

declare(strict_types=1);

$summary = $data['summary'] ?? [];
$filters = $data['filters'] ?? [];
$items = $data['filtered_items'] ?? [];
$recent = $data['recent'] ?? [];
$admin = $app->auth->user() ?? [];
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Moderacao de Conteudo</title>
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
<?php
$adminTopbarUser = $admin;
require BASE_PATH . '/templates/partials/admin_topbar.php';
?>

<?php
$adminSidebarCurrent = 'moderation';
$adminSidebarMetricTitle = 'Fila ativa';
$adminSidebarMetricValue = (string) ($summary['pending'] ?? 0);
$adminSidebarMetricDescription = 'Itens aguardando aprovacao ou rejeicao administrativa.';
require BASE_PATH . '/templates/partials/admin_sidebar.php';
?>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Curadoria e risco</p>
            <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Fila de <span class="italic text-primary">Moderacao</span></h2>
            <p class="mt-4 max-w-2xl text-on-surface-variant">Revise conteudos pendentes, acompanhe feedbacks anteriores e mantenha a politica da plataforma aplicada em tempo real.</p>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Pendentes</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-primary md:text-3xl"><?= e((string) ($summary['pending'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Aprovados</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-emerald-600 md:text-3xl"><?= e((string) ($summary['approved'] ?? 0)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Rejeitados</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-rose-700 md:text-3xl"><?= e((string) ($summary['rejected'] ?? 0)) ?></p></article>
        </div>
    </section>

    <form action="/admin/moderation" class="mb-8 grid grid-cols-1 gap-4 rounded-3xl bg-surface-container-lowest p-6 shadow-sm xl:grid-cols-[1fr_0.4fr_auto]" method="get">
        <input class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="q" placeholder="Buscar por titulo, criador ou tipo..." type="search" value="<?= e((string) ($filters['q'] ?? '')) ?>">
        <select class="rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="status">
            <option value="">Todos os status</option>
            <option value="pending" <?= (string) ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendente</option>
            <option value="approved" <?= (string) ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Aprovado</option>
            <option value="rejected" <?= (string) ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejeitado</option>
        </select>
        <div class="flex items-center gap-3">
            <button class="rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Filtrar</button>
            <a class="rounded-full bg-surface-container-low px-6 py-4 text-sm font-bold text-on-surface-variant" href="/admin/moderation">Reset</a>
        </div>
    </form>

    <div class="grid grid-cols-1 gap-8 2xl:grid-cols-[1.1fr_0.9fr]">
        <section class="space-y-5">
            <?php foreach ($items as $item): ?>
                <?php $creator = $item['creator'] ?? []; ?>
                <form action="/admin/moderation/review" class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm" method="post">
                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                    <input name="content_id" type="hidden" value="<?= e((string) ($item['id'] ?? 0)) ?>">
                    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1.2fr_0.8fr]">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="text-2xl font-extrabold"><?= e((string) ($item['title'] ?? 'Conteudo')) ?></h3>
                                <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest <?= (string) ($item['status'] ?? '') === 'approved' ? 'bg-emerald-100 text-emerald-700' : ((string) ($item['status'] ?? '') === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') ?>"><?= e((string) ($item['status'] ?? 'pending')) ?></span>
                                <span class="rounded-full bg-surface-container-low px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-slate-500"><?= e((string) ($item['kind'] ?? 'conteudo')) ?></span>
                            </div>
                            <p class="mt-3 text-sm text-on-surface-variant"><?= e((string) ($creator['name'] ?? 'Criador')) ?> • @<?= e((string) ($creator['slug'] ?? 'studio')) ?> • <?= e(format_datetime((string) ($item['created_at'] ?? ''), 'd/m/Y H:i')) ?></p>
                            <p class="mt-4 rounded-3xl bg-surface-container-low p-5 text-sm leading-7 text-on-surface-variant"><?= e(excerpt((string) (($item['excerpt'] ?? '') !== '' ? $item['excerpt'] : ($item['body'] ?? 'Sem descricao.')), 260)) ?></p>
                            <?php if ((string) ($item['moderation_feedback'] ?? '') !== ''): ?>
                                <div class="mt-4 rounded-3xl bg-surface-container-low p-5">
                                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Ultimo feedback</p>
                                    <p class="mt-3 text-sm text-on-surface-variant"><?= e((string) ($item['moderation_feedback'] ?? '')) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="space-y-4">
                            <label class="block space-y-2">
                                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Decisao</span>
                                <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="decision">
                                    <option value="approved" <?= (string) ($item['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Aprovar</option>
                                    <option value="rejected" <?= (string) ($item['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejeitar</option>
                                </select>
                            </label>
                            <label class="block space-y-2">
                                <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Feedback administrativo</span>
                                <textarea class="min-h-36 w-full rounded-3xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="moderation_feedback" placeholder="Explique a decisao para deixar historico e contexto."><?= e((string) ($item['moderation_feedback'] ?? '')) ?></textarea>
                            </label>
                            <button class="w-full rounded-full bg-primary px-6 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Salvar revisao</button>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
            <?php if ($items === []): ?><p class="rounded-3xl bg-surface-container-low p-8 text-sm text-on-surface-variant">Nenhum conteudo encontrado para esse filtro.</p><?php endif; ?>
        </section>

        <section class="space-y-8">
            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-2xl font-extrabold">Ultimas decisoes</h3>
                    <span class="text-sm font-bold text-primary"><?= count($recent) ?> itens</span>
                </div>
                <div class="space-y-4">
                    <?php foreach (array_slice($recent, 0, 8) as $item): ?>
                        <article class="rounded-3xl bg-surface-container-low p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-lg font-bold"><?= e((string) ($item['title'] ?? 'Conteudo')) ?></p>
                                    <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($item['creator']['name'] ?? 'Criador')) ?></p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest <?= (string) ($item['status'] ?? '') === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>"><?= e((string) ($item['status'] ?? 'reviewed')) ?></span>
                            </div>
                            <p class="mt-4 text-xs font-bold uppercase tracking-[0.25em] text-slate-400"><?= e(format_datetime((string) ($item['created_at'] ?? ''), 'd/m/Y H:i')) ?></p>
                        </article>
                    <?php endforeach; ?>
                    <?php if ($recent === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Ainda nao ha historico de moderacao concluida.</p><?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>
</body>
</html>
