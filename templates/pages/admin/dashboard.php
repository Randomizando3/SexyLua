<?php

declare(strict_types=1);

$metrics = $data['metrics'] ?? [];
$pendingContent = $data['pending_content'] ?? [];
$recentUsers = $data['recent_users'] ?? [];
$liveNow = $data['live_now'] ?? [];
$topCreators = $data['top_creators'] ?? [];
$pendingPayouts = $data['pending_payouts'] ?? [];
$admin = $app->auth->user() ?? [];
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Dashboard Admin</title>
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
<?php
$adminTopbarUser = $admin;
$adminTopbarAction = ['href' => '/admin/settings', 'label' => 'Configuracoes'];
require BASE_PATH . '/templates/partials/admin_topbar.php';
?>

<?php
$adminSidebarCurrent = 'dashboard';
$adminSidebarMetricTitle = 'Resultado da plataforma';
$adminSidebarMetricValue = token_amount((int) ($metrics['platform_result'] ?? 0));
$adminSidebarMetricDescription = 'Resumo do resultado liquido atual da plataforma.';
require BASE_PATH . '/templates/partials/admin_sidebar.php';
?>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Visao executiva</p>
            <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Dashboard <span class="italic text-primary">Admin</span></h2>
            <p class="mt-4 max-w-2xl text-on-surface-variant">Monitore usuarios, conteudos, payout requests, criadores em destaque e o pulso financeiro da plataforma.</p>
        </div>
        <div class="rounded-3xl bg-surface-container-lowest px-6 py-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Payouts pendentes</p>
            <p class="mt-3 text-4xl font-extrabold text-primary"><?= e((string) count($pendingPayouts)) ?></p>
        </div>
    </section>

    <section class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
        <article class="rounded-3xl bg-surface-container-lowest p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Usuarios</p><p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) ($metrics['users'] ?? 0)) ?></p></article>
        <article class="rounded-3xl bg-surface-container-lowest p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Criadores</p><p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) ($metrics['creators'] ?? 0)) ?></p></article>
        <article class="rounded-3xl bg-surface-container-lowest p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Assinantes</p><p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) ($metrics['subscribers'] ?? 0)) ?></p></article>
        <article class="rounded-3xl bg-surface-container-lowest p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Pendencias</p><p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) ($metrics['pending_content'] ?? 0)) ?></p></article>
        <article class="rounded-3xl bg-surface-container-lowest p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Lives ao vivo</p><p class="mt-3 text-3xl font-extrabold text-primary"><?= e((string) ($metrics['live_now'] ?? 0)) ?></p></article>
        <article class="rounded-3xl bg-surface-container-lowest p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Resultado</p><div class="mt-3 text-3xl font-extrabold text-primary"><?= luacoin_amount_html((int) ($metrics['platform_result'] ?? 0), 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.9em] w-[0.9em] shrink-0') ?></div></article>
    </section>

    <div class="grid grid-cols-1 gap-8 2xl:grid-cols-[1.05fr_0.95fr]">
        <section class="space-y-8">
            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <div class="mb-6 flex items-center justify-between"><h3 class="text-2xl font-extrabold">Conteudos pendentes</h3><a class="text-sm font-bold text-primary hover:underline" href="/admin/moderation">Abrir moderacao</a></div>
                <div class="space-y-4">
                    <?php foreach ($pendingContent as $item): ?>
                        <article class="rounded-3xl bg-surface-container-low p-5">
                            <p class="text-lg font-bold"><?= e((string) ($item['title'] ?? 'Conteudo')) ?></p>
                            <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($item['creator']['name'] ?? 'Criador')) ?> • <?= e((string) ($item['kind'] ?? 'post')) ?></p>
                        </article>
                    <?php endforeach; ?>
                    <?php if ($pendingContent === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhum conteudo aguardando moderacao.</p><?php endif; ?>
                </div>
            </div>

            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <div class="mb-6 flex items-center justify-between"><h3 class="text-2xl font-extrabold">Criadores em destaque</h3><a class="text-sm font-bold text-primary hover:underline" href="/admin/users">Ver usuarios</a></div>
                <div class="space-y-4">
                    <?php foreach ($topCreators as $creator): ?>
                        <article class="rounded-3xl bg-surface-container-low p-5">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-lg font-bold"><?= e((string) ($creator['name'] ?? 'Criador')) ?></p>
                                    <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($creator['subscriber_count'] ?? 0)) ?> assinantes • <?= luacoin_amount_html((int) ($creator['wallet_balance'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?></p>
                                </div>
                                <span class="rounded-full bg-primary/10 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-primary"><?= e((string) ($creator['followers'] ?? 0)) ?> fans</span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="space-y-8">
            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <div class="mb-6 flex items-center justify-between"><h3 class="text-2xl font-extrabold">Recentes na plataforma</h3><a class="text-sm font-bold text-primary hover:underline" href="/admin/users">Gestao de usuarios</a></div>
                <div class="space-y-4">
                    <?php foreach ($recentUsers as $user): ?>
                        <article class="rounded-3xl bg-surface-container-low p-5">
                            <p class="text-lg font-bold"><?= e((string) ($user['name'] ?? 'Usuario')) ?></p>
                            <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($user['email'] ?? '')) ?></p>
                            <p class="mt-3 text-xs font-bold uppercase tracking-[0.25em] text-slate-400"><?= e((string) ($user['role'] ?? 'user')) ?> • <?= e((string) ($user['status'] ?? 'active')) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <div class="mb-6 flex items-center justify-between"><h3 class="text-2xl font-extrabold">Lives em andamento</h3><span class="text-sm font-bold text-primary"><?= count($liveNow) ?> agora</span></div>
                <div class="space-y-4">
                    <?php foreach ($liveNow as $live): ?>
                        <article class="rounded-3xl bg-surface-container-low p-5">
                            <p class="text-lg font-bold"><?= e((string) ($live['title'] ?? 'Live')) ?></p>
                            <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($live['creator']['name'] ?? 'Criador')) ?> • <?= e((string) ($live['viewer_count'] ?? 0)) ?> viewers</p>
                        </article>
                    <?php endforeach; ?>
                    <?php if ($liveNow === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma live ao vivo neste momento.</p><?php endif; ?>
                </div>
            </div>

            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <div class="mb-6 flex items-center justify-between"><h3 class="text-2xl font-extrabold">Saques aguardando acao</h3><a class="text-sm font-bold text-primary hover:underline" href="/admin/finance">Abrir financeiro</a></div>
                <div class="space-y-4">
                    <?php foreach ($pendingPayouts as $transaction): ?>
                        <article class="rounded-3xl bg-surface-container-low p-5">
                            <p class="text-lg font-bold"><?= e((string) ($transaction['user']['name'] ?? 'Criador')) ?></p>
                            <p class="mt-1 text-sm text-on-surface-variant"><?= luacoin_amount_html((int) ($transaction['amount'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?> • <?= e((string) ($transaction['payout_method'] ?? 'pix')) ?></p>
                        </article>
                    <?php endforeach; ?>
                    <?php if ($pendingPayouts === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhum saque pendente agora.</p><?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>
</body>
</html>
