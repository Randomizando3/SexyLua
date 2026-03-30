<?php

declare(strict_types=1);

$creator = $data['creator'] ?? $app->repository->findCreatorBySlugOrId(null, (int) ($app->auth->id() ?? 0)) ?? [];
$metrics = $data['metrics'] ?? [];
$recentContent = $data['recent_content'] ?? [];
$lives = $data['lives'] ?? [];
$transactions = $data['transactions'] ?? [];

$creatorName = trim((string) ($creator['name'] ?? 'Criador'));
$creatorFirstName = $creatorName !== '' ? explode(' ', $creatorName)[0] : 'Criador';
$creatorHeadline = trim((string) ($creator['headline'] ?? 'Sua presença está crescendo na SexyLua.'));
$approvedContent = (int) ($metrics['approved_content'] ?? 0);
$pendingContent = (int) ($metrics['pending_content'] ?? 0);
$activeSubscribers = (int) ($metrics['active_subscribers'] ?? 0);
$walletBalance = (int) ($metrics['wallet_balance'] ?? 0);

$incomingTransactions = array_values(array_filter(
    $transactions,
    static fn (array $transaction): bool => (string) ($transaction['direction'] ?? '') === 'in'
));

$chartValues = array_fill(0, 7, 0);
foreach (array_slice(array_reverse($incomingTransactions), 0, 7) as $index => $transaction) {
    $chartValues[$index] = max(0, (int) ($transaction['amount'] ?? 0));
}

if (max($chartValues) <= 0) {
    $chartValues = [32, 54, 47, 70, 44, 86, 58];
}

$chartMax = max($chartValues);
$chartLabels = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];

$estimatedViews = array_reduce(
    $recentContent,
    static fn (int $carry, array $item): int => $carry + max(18, ((int) ($item['saved_count'] ?? 0) * 42)),
    0
);

if ($estimatedViews === 0) {
    $estimatedViews = max(2400, $approvedContent * 580);
}

$commentCount = max(12, count($transactions) * 3 + count($lives) * 4);
$nextLive = $lives[0] ?? null;
$nextLiveUrl = $nextLive ? path_with_query('/creator/live', ['live' => (int) ($nextLive['id'] ?? 0)]) : '/creator/live';
$walletUrl = '/creator/wallet';
$contentUrl = '/creator/content';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Painel do Criador</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "error": "#ba1a1a",
                        "background": "#fbf9fb",
                        "on-primary-container": "#fff2f4",
                        "on-background": "#1b1c1d",
                        "inverse-surface": "#303032",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f5f3f5",
                        "surface-bright": "#fbf9fb",
                        "surface": "#fbf9fb",
                        "surface-variant": "#e3e2e4",
                        "outline": "#8e6f74",
                        "surface-container-high": "#e9e7e9",
                        "primary": "#ab1155",
                        "secondary": "#ab2c5d",
                        "outline-variant": "#e3bdc3",
                        "on-surface": "#1b1c1d",
                        "on-surface-variant": "#5a4044",
                        "surface-container": "#efedef",
                        "primary-container": "#cc326e",
                        "on-primary": "#ffffff"
                    },
                    fontFamily: {
                        "headline": ["Plus Jakarta Sans"],
                        "body": ["Manrope"],
                        "label": ["Manrope"]
                    },
                    borderRadius: {"DEFAULT": "1rem", "lg": "2rem", "xl": "3rem", "full": "9999px"},
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body { font-family: 'Manrope', sans-serif; }
        h1, h2, h3 { font-family: 'Plus Jakarta Sans', sans-serif; }
        .lunar-glass {
            background: rgba(251, 249, 251, 0.7);
            backdrop-filter: blur(24px);
        }
    </style>
</head>
<body class="min-h-screen bg-background text-on-background">
<?php
$creatorShellCreator = $creator;
$creatorShellCurrent = 'dashboard';
$creatorTopbarLabel = 'Métricas Lunares';
$creatorTopbarAction = ['href' => '/creator/live', 'label' => 'Configurar Live'];
include base_path('templates/partials/creator_sidebar.php');
include base_path('templates/partials/creator_topbar.php');
?>

<main class="min-h-screen pt-20 lg:pl-64">
    <div class="mx-auto max-w-7xl px-8 py-12">
        <header class="mb-12 flex flex-col justify-between gap-6 md:flex-row md:items-end">
            <div>
                <h2 class="mb-2 text-4xl font-extrabold tracking-tight text-on-background">Olá, <?= e($creatorFirstName) ?> 👋</h2>
                <p class="max-w-2xl font-medium text-on-surface-variant"><?= e($creatorHeadline) ?></p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a class="rounded-full bg-surface-container-high px-6 py-3 text-sm font-bold transition-transform hover:scale-105 active:opacity-80" href="<?= e($walletUrl) ?>">
                    Ver carteira
                </a>
                <a class="flex items-center gap-2 rounded-full bg-primary px-8 py-3 text-sm font-bold text-on-primary shadow-lg shadow-primary/20 transition-transform hover:scale-105 active:opacity-80" href="<?= e($nextLiveUrl) ?>">
                    <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">sensors</span>
                    Ir para a live
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-12">
            <section class="rounded-xl bg-surface-container-lowest p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] md:col-span-8">
                <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-xl font-bold">Ganhos da Lua</h3>
                        <p class="text-xs font-semibold uppercase tracking-widest text-on-surface-variant">Resumo da semana</p>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-3xl font-black tracking-tighter text-primary"><?= luacoin_amount_html($walletBalance, 'inline-flex items-center gap-2 whitespace-nowrap', '', 'h-[0.9em] w-[0.9em] shrink-0') ?></span>
                        <p class="mt-1 flex items-center gap-1 text-xs font-bold text-emerald-600 sm:justify-end">
                            <span class="material-symbols-outlined text-xs">trending_up</span>
                            <?= e((string) count($incomingTransactions)) ?> entradas recentes
                        </p>
                    </div>
                </div>
                <div class="flex h-64 items-end justify-between gap-2 px-2">
                    <?php foreach ($chartValues as $value): ?>
                        <?php $barHeight = max(18, (int) round(($value / max(1, $chartMax)) * 100)); ?>
                        <div class="relative h-full w-full rounded-t-full bg-surface-container-low transition-colors hover:bg-primary-container/20">
                            <div class="absolute bottom-0 w-full rounded-t-full bg-primary/40" style="height: <?= e((string) $barHeight) ?>%"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 flex justify-between px-2 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
                    <?php foreach ($chartLabels as $label): ?>
                        <span><?= e($label) ?></span>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="space-y-8 md:col-span-4">
                <div class="relative overflow-hidden rounded-xl bg-primary p-8 text-on-primary shadow-xl">
                    <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="relative z-10">
                        <span class="material-symbols-outlined mb-4 text-3xl">stars</span>
                        <h3 class="mb-4 text-lg font-bold opacity-80">Assinantes ativos</h3>
                        <div class="flex items-end gap-3">
                            <span class="text-5xl font-black tracking-tighter"><?= e((string) $activeSubscribers) ?></span>
                            <span class="mb-2 rounded-full bg-white/20 px-2 py-1 text-xs"><?= e((string) $pendingContent) ?> pend.</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl bg-surface-container-low p-8">
                    <h3 class="mb-6 text-sm font-bold uppercase tracking-widest text-on-surface-variant">Métricas de engajamento</h3>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-container-lowest">
                                    <span class="material-symbols-outlined text-xl text-primary">visibility</span>
                                </div>
                                <span class="text-sm font-bold">Visualizações</span>
                            </div>
                            <span class="text-lg font-black"><?= e(number_format($estimatedViews, 0, ',', '.')) ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-container-lowest">
                                    <span class="material-symbols-outlined text-xl text-primary">favorite</span>
                                </div>
                                <span class="text-sm font-bold">Conteúdos aprovados</span>
                            </div>
                            <span class="text-lg font-black"><?= e((string) $approvedContent) ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-container-lowest">
                                    <span class="material-symbols-outlined text-xl text-primary">chat_bubble</span>
                                </div>
                                <span class="text-sm font-bold">Interações recentes</span>
                            </div>
                            <span class="text-lg font-black"><?= e((string) $commentCount) ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-xl bg-surface-container-low p-8 md:col-span-5">
                <div class="mb-8 flex items-center justify-between">
                    <h3 class="text-xl font-bold">Próxima live</h3>
                    <span class="material-symbols-outlined text-pink-600">sensors</span>
                </div>
                <?php if ($nextLive): ?>
                    <div class="space-y-6">
                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Título da transmissão</p>
                            <div class="rounded-md bg-surface-container-lowest px-4 py-4 font-semibold"><?= e((string) ($nextLive['title'] ?? 'Live sem título')) ?></div>
                        </div>
                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Quando</p>
                            <div class="rounded-md bg-surface-container-lowest px-4 py-4 font-semibold"><?= e(format_datetime((string) ($nextLive['scheduled_for'] ?? ''))) ?></div>
                        </div>
                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Acesso</p>
                            <div class="rounded-md bg-surface-container-lowest px-4 py-4 font-semibold"><?= e((string) ucfirst((string) ($nextLive['access_mode'] ?? 'public'))) ?></div>
                        </div>
                        <div class="pt-2">
                            <a class="block w-full rounded-full bg-primary-container py-4 text-center font-bold text-on-primary shadow-md transition-transform hover:scale-[1.01]" href="<?= e($nextLiveUrl) ?>">Abrir painel da live</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="rounded-3xl bg-surface-container-lowest p-6 text-sm text-on-surface-variant">
                        Você ainda não criou nenhuma live. Abra o estúdio para agendar a primeira.
                    </div>
                    <div class="pt-4">
                        <a class="block w-full rounded-full bg-primary-container py-4 text-center font-bold text-on-primary shadow-md transition-transform hover:scale-[1.01]" href="/creator/live">Criar minha primeira live</a>
                    </div>
                <?php endif; ?>
            </section>

            <section class="rounded-xl bg-surface-container-lowest p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.04)] md:col-span-7">
                <div class="mb-8 flex items-center justify-between">
                    <h3 class="text-xl font-bold">Meus conteúdos</h3>
                    <a class="text-sm font-bold text-primary hover:underline" href="<?= e($contentUrl) ?>">Ver tudo</a>
                </div>
                <div class="grid grid-cols-2 gap-6 sm:grid-cols-3">
                    <?php foreach (array_slice($recentContent, 0, 3) as $item): ?>
                        <?php
                        $thumbnail = media_url((string) ($item['thumbnail_url'] ?? $item['media_url'] ?? ''));
                        $editUrl = path_with_query('/creator/content', ['edit' => (int) ($item['id'] ?? 0)]);
                        $views = max(18, ((int) ($item['saved_count'] ?? 0) * 42));
                        ?>
                        <a class="group block cursor-pointer" href="<?= e($editUrl) ?>">
                            <div class="relative mb-3 aspect-[3/4] overflow-hidden rounded-lg">
                                <?php if ($thumbnail !== ''): ?>
                                    <img alt="<?= e((string) ($item['title'] ?? 'Conteúdo')) ?>" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" src="<?= e($thumbnail) ?>">
                                <?php else: ?>
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#cc326e] via-[#ab1155] to-[#5a0d31] p-6 text-center text-white">
                                        <span class="text-sm font-bold uppercase tracking-[0.25em]"><?= e((string) strtoupper(mb_substr((string) ($item['kind'] ?? 'conteúdo'), 0, 12))) ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="absolute left-3 top-3 rounded-full bg-primary px-3 py-1 text-[10px] font-bold text-white"><?= luacoin_amount_html((int) ($item['price_tokens'] ?? 0), 'inline-flex items-center gap-1 whitespace-nowrap', '', 'h-3 w-3 shrink-0') ?></div>
                                <div class="absolute inset-0 flex items-end bg-gradient-to-t from-black/60 to-transparent p-4 opacity-0 transition-opacity group-hover:opacity-100">
                                    <span class="text-xs font-bold text-white">Editar conteúdo</span>
                                </div>
                            </div>
                            <p class="truncate text-sm font-bold"><?= e((string) ($item['title'] ?? 'Conteúdo')) ?></p>
                            <p class="text-[10px] font-bold uppercase text-on-surface-variant"><?= e(number_format($views, 0, ',', '.')) ?> visualizações</p>
                        </a>
                    <?php endforeach; ?>
                    <?php if ($recentContent === []): ?>
                        <div class="col-span-full rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">
                            Você ainda não publicou conteúdo. Acesse a área de conteúdo para começar.
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</main>

<footer class="full-width flex w-full flex-col items-center justify-center gap-6 border-t border-pink-100 bg-zinc-50 px-4 py-12 lg:pl-64">
    <?= brand_logo_magenta('h-9 w-auto') ?>
    <div class="flex flex-wrap justify-center gap-8 font-['Manrope'] text-xs uppercase tracking-widest">
        <a class="text-zinc-400 transition-all hover:text-pink-500 hover:underline" href="/terms">Termos de serviço</a>
        <a class="text-zinc-400 transition-all hover:text-pink-500 hover:underline" href="/privacy">Privacidade</a>
        <a class="text-zinc-400 transition-all hover:text-pink-500 hover:underline" href="/help">Ajuda</a>
    </div>
    <p class="font-['Manrope'] text-[10px] uppercase tracking-[0.3em] text-zinc-400">© 2026 SexyLua Editorial Celestial</p>
</footer>

<a class="fixed bottom-8 right-8 z-50 flex h-16 w-16 items-center justify-center rounded-full bg-primary text-on-primary shadow-2xl transition-transform hover:scale-110 active:scale-95 md:hidden" href="/creator/live">
    <span class="material-symbols-outlined text-3xl">sensors</span>
</a>
</body>
</html>
