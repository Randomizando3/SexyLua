<?php

declare(strict_types=1);

$subscriber = $data['subscriber'] ?? [];
$favoriteCreators = $data['favorite_creators'] ?? [];
$savedContent = $data['saved_content'] ?? [];
$trackedLives = $data['tracked_lives'] ?? [];
$suggestedCreators = $data['suggested_creators'] ?? [];
$suggestedContent = $data['suggested_content'] ?? [];
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Favoritos e Salvos</title>
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
$subscriberTopbarUser = $subscriber;
$subscriberTopbarAction = ['href' => '/explore', 'label' => 'Explorar'];
require BASE_PATH . '/templates/partials/subscriber_topbar.php';
$subscriberSidebarCurrent = 'favorites';
require BASE_PATH . '/templates/partials/subscriber_sidebar.php';
?>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Radar e colecao</p>
            <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Favoritos e <span class="italic text-primary">Salvos</span></h2>
            <p class="mt-4 max-w-2xl text-on-surface-variant">Monte seu radar pessoal de criadores, acompanhe lives e guarde os conteudos que merecem revisitacao.</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Criadores</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-primary md:text-3xl"><?= e((string) count($favoriteCreators)) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 text-center shadow-sm"><p class="min-h-[1.9rem] text-[10px] font-bold uppercase tracking-[0.18em] leading-tight text-slate-400">Salvos</p><p class="mt-2 text-[2rem] font-extrabold leading-tight text-primary md:text-3xl"><?= e((string) count($savedContent)) ?></p></article>
        </div>
    </section>

    <section class="mb-8">
        <div class="mb-5 flex items-center justify-between"><h3 class="text-2xl font-extrabold">Criadores favoritos</h3><a class="text-sm font-bold text-primary hover:underline" href="/explore">Explorar mais</a></div>
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <?php foreach ($favoriteCreators as $creator): ?>
                <article class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 font-bold text-primary"><?= e(avatar_initials((string) ($creator['name'] ?? 'Criador'))) ?></div>
                        <div class="min-w-0"><p class="truncate text-xl font-bold"><?= e((string) ($creator['name'] ?? 'Criador')) ?></p><p class="truncate text-sm text-on-surface-variant">@<?= e((string) ($creator['slug'] ?? 'criador')) ?></p></div>
                    </div>
                    <p class="mt-4 text-sm text-on-surface-variant"><?= e(excerpt((string) ($creator['headline'] ?? ''), 95)) ?></p>
                    <div class="mt-5 flex gap-3">
                        <a class="flex-1 rounded-full bg-surface-container-low px-4 py-3 text-center text-sm font-bold text-on-surface" href="<?= e('/profile?id=' . (int) ($creator['id'] ?? 0)) ?>">Perfil</a>
                        <form action="/subscriber/favorites/toggle" class="flex-1" method="post">
                            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                            <input name="creator_id" type="hidden" value="<?= e((string) ($creator['id'] ?? 0)) ?>">
                            <input name="redirect" type="hidden" value="/subscriber/favorites">
                            <button class="w-full rounded-full bg-primary px-4 py-3 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Remover</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if ($favoriteCreators === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Voce ainda nao marcou nenhum criador como favorito.</p><?php endif; ?>
        </div>
    </section>

    <div class="grid grid-cols-1 gap-8 2xl:grid-cols-[1.05fr_0.95fr]">
        <section class="space-y-8">
            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <div class="mb-6 flex items-center justify-between"><h3 class="text-2xl font-extrabold">Conteudos salvos</h3><span class="text-sm font-bold text-primary"><?= count($savedContent) ?> itens</span></div>
                <div class="space-y-4">
                    <?php foreach ($savedContent as $item): ?>
                        <div class="rounded-3xl bg-surface-container-low p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <a class="truncate text-lg font-bold hover:text-primary" href="<?= e(path_with_query('/profile', ['id' => (int) ($item['creator']['id'] ?? 0), 'content' => (int) ($item['id'] ?? 0)])) ?>"><?= e((string) ($item['title'] ?? 'Conteudo')) ?></a>
                                    <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($item['creator']['name'] ?? 'Criador')) ?></p>
                                    <p class="mt-3 text-sm text-on-surface-variant"><?= e(excerpt((string) ($item['excerpt'] ?? ''), 100)) ?></p>
                                </div>
                                <form action="/subscriber/saved/toggle" class="w-full max-w-[120px]" method="post">
                                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                    <input name="content_id" type="hidden" value="<?= e((string) ($item['id'] ?? 0)) ?>">
                                    <input name="redirect" type="hidden" value="/subscriber/favorites">
                                    <button class="w-full rounded-full bg-white px-4 py-3 text-sm font-bold text-primary" data-prototype-skip="1" type="submit">Remover</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($savedContent === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Voce nao tem conteudos salvos ainda.</p><?php endif; ?>
                </div>
            </div>

            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <div class="mb-6 flex items-center justify-between"><h3 class="text-2xl font-extrabold">Conteudos sugeridos</h3><span class="text-sm font-bold text-primary"><?= count($suggestedContent) ?> sugestoes</span></div>
                <div class="space-y-4">
                    <?php foreach ($suggestedContent as $item): ?>
                        <div class="rounded-3xl bg-surface-container-low p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <a class="truncate text-lg font-bold hover:text-primary" href="<?= e(path_with_query('/profile', ['id' => (int) ($item['creator']['id'] ?? 0), 'content' => (int) ($item['id'] ?? 0)])) ?>"><?= e((string) ($item['title'] ?? 'Conteudo')) ?></a>
                                    <p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($item['creator']['name'] ?? 'Criador')) ?></p>
                                    <p class="mt-3 text-sm text-on-surface-variant"><?= e(excerpt((string) ($item['excerpt'] ?? ''), 100)) ?></p>
                                </div>
                                <form action="/subscriber/saved/toggle" class="w-full max-w-[120px]" method="post">
                                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                    <input name="content_id" type="hidden" value="<?= e((string) ($item['id'] ?? 0)) ?>">
                                    <input name="redirect" type="hidden" value="/subscriber/favorites">
                                    <button class="w-full rounded-full bg-primary px-4 py-3 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Salvar</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="space-y-8">
            <div class="signature-glow rounded-3xl p-8 text-white shadow-[0px_20px_40px_rgba(171,17,85,0.2)]">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/70">Lives monitoradas</p>
                <h3 class="mt-3 text-3xl font-extrabold">Seu radar ao vivo</h3>
                <p class="mt-3 text-sm text-white/80">Acompanhe rapidamente os criadores que voce mais curte quando eles entram no ar ou marcam uma nova sessao.</p>
            </div>

            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <div class="mb-6 flex items-center justify-between"><h3 class="text-2xl font-extrabold">Lives dos favoritos</h3><a class="text-sm font-bold text-primary hover:underline" href="/explore">Ver todas</a></div>
                <div class="space-y-4">
                    <?php foreach ($trackedLives as $live): ?>
                        <a class="block rounded-3xl bg-surface-container-low p-5 transition-colors hover:bg-surface-container-high" href="<?= e('/live?id=' . (int) ($live['id'] ?? 0)) ?>">
                            <div class="flex items-start justify-between gap-4">
                                <div><p class="text-lg font-bold"><?= e((string) ($live['title'] ?? 'Live')) ?></p><p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($live['creator']['name'] ?? 'Criador')) ?></p></div>
                                <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest <?= (string) ($live['status'] ?? '') === 'live' ? 'bg-rose-100 text-rose-700' : 'bg-slate-200 text-slate-600' ?>"><?= e((string) ($live['status'] ?? 'scheduled')) ?></span>
                            </div>
                            <p class="mt-3 text-xs font-bold uppercase tracking-[0.25em] text-slate-400"><?= e(format_datetime((string) ($live['scheduled_for'] ?? ''), 'd/m/Y H:i')) ?></p>
                        </a>
                    <?php endforeach; ?>
                    <?php if ($trackedLives === []): ?><p class="rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhuma live rastreada agora.</p><?php endif; ?>
                </div>
            </div>

            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <div class="mb-6 flex items-center justify-between"><h3 class="text-2xl font-extrabold">Criadores sugeridos</h3><span class="text-sm font-bold text-primary"><?= count($suggestedCreators) ?> perfis</span></div>
                <div class="space-y-4">
                    <?php foreach ($suggestedCreators as $creator): ?>
                        <div class="rounded-3xl bg-surface-container-low p-5">
                            <p class="text-lg font-bold"><?= e((string) ($creator['name'] ?? 'Criador')) ?></p>
                            <p class="mt-1 text-sm text-on-surface-variant">@<?= e((string) ($creator['slug'] ?? 'criador')) ?></p>
                            <p class="mt-3 text-sm text-on-surface-variant"><?= e(excerpt((string) ($creator['headline'] ?? ''), 90)) ?></p>
                            <form action="/subscriber/favorites/toggle" class="mt-4" method="post">
                                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                <input name="creator_id" type="hidden" value="<?= e((string) ($creator['id'] ?? 0)) ?>">
                                <input name="redirect" type="hidden" value="/subscriber/favorites">
                                <button class="w-full rounded-full bg-primary px-4 py-3 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Adicionar</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </div>
</main>
</body>
</html>
