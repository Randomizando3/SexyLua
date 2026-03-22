<?php

declare(strict_types=1);

$creator = $data['creator'] ?? [];
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
    <title>SexyLua - Favoritos do Criador</title>
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
                        "surface-container": "#efedef",
                        "surface-container-low": "#f5f3f5",
                        "surface-container-lowest": "#ffffff",
                        "secondary-container": "#fd6c9c",
                        "on-surface": "#1b1c1d",
                        "on-surface-variant": "#5a4044"
                    },
                    fontFamily: {
                        headline: ["Plus Jakarta Sans"],
                        body: ["Manrope"]
                    },
                    borderRadius: {
                        DEFAULT: "1rem",
                        lg: "2rem",
                        xl: "3rem",
                        full: "9999px"
                    }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24;
        }
        body {
            font-family: "Manrope", sans-serif;
        }
        h1, h2, h3, h4 {
            font-family: "Plus Jakarta Sans", sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-background text-on-surface">
<aside class="fixed left-0 top-0 hidden h-full w-64 flex-col bg-zinc-50 py-8 font-['Plus_Jakarta_Sans'] font-medium shadow-xl lg:flex">
    <div class="mb-12 px-8">
        <h1 class="text-2xl font-bold tracking-tighter text-pink-700">SexyLua</h1>
        <p class="mt-1 text-xs uppercase tracking-widest text-zinc-500">Hub Celestial</p>
    </div>
    <nav class="flex-1 space-y-1">
        <a class="mx-2 flex items-center gap-3 rounded-full px-4 py-3 text-zinc-600 transition-colors hover:bg-zinc-100" href="/creator/content">
            <span class="material-symbols-outlined">brightness_4</span>
            <span>Meu Conteudo</span>
        </a>
        <a class="mx-2 flex items-center gap-3 rounded-full px-4 py-3 text-zinc-600 transition-colors hover:bg-zinc-100" href="/creator">
            <span class="material-symbols-outlined">insights</span>
            <span>Metricas Lunares</span>
        </a>
        <a class="mx-2 flex items-center gap-3 rounded-full px-4 py-3 text-zinc-600 transition-colors hover:bg-zinc-100" href="/creator/live">
            <span class="material-symbols-outlined">settings_input_antenna</span>
            <span>Configurar Live</span>
        </a>
        <a class="mx-2 flex items-center gap-3 rounded-full px-4 py-3 text-zinc-600 transition-colors hover:bg-zinc-100" href="/creator/memberships">
            <span class="material-symbols-outlined">star</span>
            <span>Minhas Assinaturas</span>
        </a>
        <a class="mx-2 flex items-center gap-3 rounded-full bg-pink-50 px-4 py-3 text-pink-700" href="/creator/favorites">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">favorite</span>
            <span>Favoritos</span>
        </a>
        <a class="mx-2 flex items-center gap-3 rounded-full px-4 py-3 text-zinc-600 transition-colors hover:bg-zinc-100" href="/creator/wallet">
            <span class="material-symbols-outlined">account_balance_wallet</span>
            <span>Carteira</span>
        </a>
        <a class="mx-2 flex items-center gap-3 rounded-full px-4 py-3 text-zinc-600 transition-colors hover:bg-zinc-100" href="/creator/settings">
            <span class="material-symbols-outlined">settings</span>
            <span>Configuracoes</span>
        </a>
        <div class="px-8 pb-4 pt-8">
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-400">Administracao</p>
        </div>
        <a class="mx-2 flex items-center gap-3 rounded-full px-4 py-3 text-zinc-600 transition-colors hover:bg-zinc-100" href="/admin/users">
            <span class="material-symbols-outlined">group</span>
            <span>Gestao de Usuarios</span>
        </a>
        <a class="mx-2 flex items-center gap-3 rounded-full px-4 py-3 text-zinc-600 transition-colors hover:bg-zinc-100" href="/admin/finance">
            <span class="material-symbols-outlined">payments</span>
            <span>Financeiro</span>
        </a>
        <a class="mx-2 flex items-center gap-3 rounded-full px-4 py-3 text-zinc-600 transition-colors hover:bg-zinc-100" href="/admin/moderation">
            <span class="material-symbols-outlined">gavel</span>
            <span>Moderacao de Conteudo</span>
        </a>
    </nav>
    <div class="mt-auto px-6 py-4">
        <div class="flex items-center gap-3 rounded-xl bg-surface-container-low p-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 font-bold text-primary"><?= e(avatar_initials((string) ($creator['name'] ?? 'Criador'))) ?></div>
            <div class="overflow-hidden">
                <p class="truncate text-sm font-bold"><?= e((string) ($creator['name'] ?? 'Criador')) ?></p>
                <p class="text-xs text-pink-600">Radar pessoal</p>
            </div>
        </div>
    </div>
</aside>

<header class="sticky top-0 z-40 flex h-16 items-center bg-[#D81B60] shadow-lg shadow-[#D81B60]/20 lg:pl-64">
    <div class="mx-auto flex w-full max-w-screen-2xl items-center justify-between px-8 font-['Plus_Jakarta_Sans'] text-sm font-bold tracking-wide text-white">
        <div class="flex items-center gap-8">
            <h1 class="text-2xl font-black lg:hidden">SexyLua</h1>
            <nav class="hidden items-center gap-6 md:flex">
                <a class="rounded-full px-3 py-1 text-white/80 transition-colors hover:bg-white/10" href="/creator/content">Meu Conteudo</a>
                <a class="rounded-full px-3 py-1 text-white/80 transition-colors hover:bg-white/10" href="/creator">Metricas Lunares</a>
                <a class="border-b-2 border-white py-1 text-white" href="/creator/favorites">Favoritos</a>
            </nav>
        </div>
        <div class="flex items-center gap-4">
            <a class="rounded-full px-3 py-1 text-white/80 transition-colors hover:bg-white/10" href="/creator/live">Configurar Live</a>
            <a class="rounded-full px-3 py-1 text-white/80 transition-colors hover:bg-white/10" href="/creator/settings">Configuracoes</a>
        </div>
    </div>
</header>

<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-7xl px-8 py-12">
        <header class="mb-10 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="mb-2 text-4xl font-extrabold tracking-tight">Favoritos do Criador</h2>
                <p class="text-on-surface-variant">Perfis, conteudos e lives que voce acompanha para inspiracao, collabs e referencia diaria.</p>
            </div>
            <div class="rounded-full bg-surface-container-lowest px-6 py-3 shadow-sm">
                <span class="text-sm font-bold text-primary"><?= count($favoriteCreators) ?> perfis acompanhados</span>
            </div>
        </header>

        <section class="mb-12">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-2xl font-bold">Criadores salvos</h3>
                <a class="text-sm font-bold text-primary hover:underline" href="/explore">Explorar mais perfis</a>
            </div>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
                <?php foreach ($favoriteCreators as $favoriteCreator): ?>
                    <div class="group overflow-hidden rounded-2xl bg-surface-container-lowest p-5 shadow-[0px_20px_40px_rgba(27,28,29,0.05)] transition-transform hover:-translate-y-1">
                        <div class="mb-4 flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 font-bold text-primary"><?= e(avatar_initials((string) $favoriteCreator['name'])) ?></div>
                            <div class="min-w-0">
                                <h4 class="truncate text-lg font-bold"><?= e((string) $favoriteCreator['name']) ?></h4>
                                <p class="truncate text-sm text-on-surface-variant">@<?= e((string) ($favoriteCreator['slug'] ?? 'criador')) ?></p>
                            </div>
                        </div>
                        <p class="mb-4 text-sm leading-relaxed text-on-surface-variant"><?= e(excerpt((string) ($favoriteCreator['headline'] ?? ''), 90)) ?></p>
                        <div class="mb-4 flex items-center justify-between text-xs font-bold uppercase tracking-widest text-on-surface-variant">
                            <span><?= e((string) number_format((float) ($favoriteCreator['followers'] ?? 0), 0, ',', '.')) ?> fas</span>
                            <span><?= e((string) ($favoriteCreator['subscriber_count'] ?? 0)) ?> assinantes</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <a class="flex-1 rounded-full bg-surface-container-low px-4 py-3 text-center text-sm font-bold text-on-surface transition-colors hover:bg-surface-container" href="<?= e('/profile?id=' . (int) $favoriteCreator['id']) ?>">Abrir perfil</a>
                            <form action="/creator/favorites/toggle" method="post" class="flex-1" data-prototype-skip="1">
                                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                <input name="creator_id" type="hidden" value="<?= e((string) ($favoriteCreator['id'] ?? 0)) ?>">
                                <input name="redirect" type="hidden" value="/creator/favorites">
                                <button class="w-full rounded-full bg-primary px-4 py-3 text-sm font-bold text-white" type="submit">Remover</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($favoriteCreators === []): ?>
                <div class="mt-6 rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhum criador salvo ainda. Use as sugestoes abaixo para montar seu radar.</div>
            <?php endif; ?>
        </section>

        <div class="grid grid-cols-1 gap-8 xl:grid-cols-[1.2fr_0.8fr]">
            <section class="space-y-8">
                <div class="rounded-2xl bg-surface-container-lowest p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-2xl font-bold">Conteudos guardados</h3>
                        <span class="text-sm font-bold text-primary"><?= count($savedContent) ?> itens</span>
                    </div>
                    <div class="space-y-4">
                        <?php foreach ($savedContent as $item): ?>
                            <div class="flex items-start gap-4 rounded-2xl bg-surface-container-low p-4">
                                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-secondary-container/20 text-[#ab2c5d]">
                                    <span class="material-symbols-outlined"><?= e($item['kind'] === 'video' ? 'play_circle' : ($item['kind'] === 'audio' ? 'headphones' : 'photo_library')) ?></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <a class="truncate text-base font-bold hover:text-primary" href="<?= e('/profile?id=' . (int) ($item['creator']['id'] ?? $item['creator_id'])) ?>"><?= e((string) $item['title']) ?></a>
                                    <p class="mt-1 text-sm text-on-surface-variant"><?= e(excerpt((string) ($item['excerpt'] ?? ''), 110)) ?></p>
                                    <div class="mt-3 flex flex-wrap items-center gap-3 text-xs font-bold uppercase tracking-widest text-on-surface-variant">
                                        <span><?= e((string) ($item['creator']['name'] ?? 'Criador')) ?></span>
                                        <span><?= e(format_datetime((string) ($item['created_at'] ?? ''), 'd/m')) ?></span>
                                        <span><?= e((string) ($item['saved_count'] ?? 0)) ?> saves</span>
                                    </div>
                                </div>
                                <form action="/creator/saved/toggle" method="post" class="w-full max-w-[120px]" data-prototype-skip="1">
                                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                    <input name="content_id" type="hidden" value="<?= e((string) ($item['id'] ?? 0)) ?>">
                                    <input name="redirect" type="hidden" value="/creator/favorites">
                                    <button class="w-full rounded-full bg-white px-4 py-3 text-sm font-bold text-primary shadow-sm" type="submit">Remover</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($savedContent === []): ?>
                        <div class="mt-6 rounded-3xl bg-surface-container-low p-6 text-sm text-on-surface-variant">Nenhum conteudo salvo ainda. Use as sugestoes para testar o fluxo.</div>
                    <?php endif; ?>
                </div>

                <div class="rounded-2xl bg-surface-container-lowest p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-2xl font-bold">Conteudos sugeridos</h3>
                        <span class="text-sm font-bold text-primary"><?= count($suggestedContent) ?> recomendacoes</span>
                    </div>
                    <div class="space-y-4">
                        <?php foreach ($suggestedContent as $item): ?>
                            <div class="flex items-start gap-4 rounded-2xl bg-surface-container-low p-4">
                                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                    <span class="material-symbols-outlined"><?= e($item['kind'] === 'video' ? 'smart_display' : ($item['kind'] === 'audio' ? 'graphic_eq' : 'collections')) ?></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <a class="truncate text-base font-bold hover:text-primary" href="<?= e('/profile?id=' . (int) ($item['creator']['id'] ?? $item['creator_id'])) ?>"><?= e((string) ($item['title'] ?? 'Conteudo')) ?></a>
                                    <p class="mt-1 text-sm text-on-surface-variant"><?= e(excerpt((string) ($item['excerpt'] ?? ''), 110)) ?></p>
                                    <div class="mt-3 flex flex-wrap items-center gap-3 text-xs font-bold uppercase tracking-widest text-on-surface-variant">
                                        <span><?= e((string) ($item['creator']['name'] ?? 'Criador')) ?></span>
                                        <span><?= e((string) ($item['kind'] ?? 'post')) ?></span>
                                        <span><?= e((string) ($item['saved_count'] ?? 0)) ?> saves</span>
                                    </div>
                                </div>
                                <form action="/creator/saved/toggle" method="post" class="w-full max-w-[120px]" data-prototype-skip="1">
                                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                    <input name="content_id" type="hidden" value="<?= e((string) ($item['id'] ?? 0)) ?>">
                                    <input name="redirect" type="hidden" value="/creator/favorites">
                                    <button class="w-full rounded-full bg-primary px-4 py-3 text-sm font-bold text-white" type="submit">Salvar</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <section class="space-y-8">
                <div class="rounded-2xl bg-primary p-8 text-white shadow-[0px_20px_40px_rgba(171,17,85,0.18)]">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-white/70">Radar de Lives</p>
                    <h3 class="mt-3 text-3xl font-extrabold">Perfis em movimento</h3>
                    <p class="mt-3 text-sm leading-relaxed text-white/80">Use essa lista como atalho rapido para entrar em lives agendadas ou em andamento dos perfis que voce acompanha.</p>
                </div>

                <div class="rounded-2xl bg-surface-container-lowest p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="text-xl font-bold">Lives rastreadas</h3>
                        <a class="text-sm font-bold text-primary hover:underline" href="/explore">Ver todas</a>
                    </div>
                    <div class="space-y-4">
                        <?php foreach ($trackedLives as $live): ?>
                            <a class="block rounded-2xl bg-surface-container-low p-4 transition-colors hover:bg-surface-container" href="<?= e('/live?id=' . (int) $live['id']) ?>">
                                <div class="mb-2 flex items-center justify-between gap-4">
                                    <p class="font-bold"><?= e((string) $live['title']) ?></p>
                                    <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest <?= ($live['status'] ?? '') === 'live' ? 'bg-rose-100 text-rose-600' : 'bg-slate-200 text-slate-600' ?>"><?= e((string) ($live['status'] ?? 'scheduled')) ?></span>
                                </div>
                                <p class="text-sm text-on-surface-variant"><?= e((string) ($live['creator']['name'] ?? 'Criador')) ?> • <?= e((string) ($live['viewer_count'] ?? 0)) ?> viewers</p>
                                <p class="mt-2 text-xs font-bold uppercase tracking-widest text-on-surface-variant"><?= e(format_datetime((string) ($live['scheduled_for'] ?? ''), 'd/m H:i')) ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($trackedLives === []): ?>
                        <div class="mt-4 rounded-3xl bg-surface-container-low p-4 text-sm text-on-surface-variant">Nenhuma live rastreada no momento.</div>
                    <?php endif; ?>
                </div>

                <div class="rounded-2xl bg-surface-container-lowest p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="text-xl font-bold">Criadores sugeridos</h3>
                        <span class="text-sm font-bold text-primary"><?= count($suggestedCreators) ?> perfis</span>
                    </div>
                    <div class="space-y-4">
                        <?php foreach ($suggestedCreators as $suggestedCreator): ?>
                            <div class="rounded-2xl bg-surface-container-low p-4">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 font-bold text-primary"><?= e(avatar_initials((string) ($suggestedCreator['name'] ?? 'Criador'))) ?></div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-bold"><?= e((string) ($suggestedCreator['name'] ?? 'Criador')) ?></p>
                                        <p class="truncate text-sm text-on-surface-variant">@<?= e((string) ($suggestedCreator['slug'] ?? 'criador')) ?></p>
                                    </div>
                                </div>
                                <p class="mt-3 text-sm text-on-surface-variant"><?= e(excerpt((string) ($suggestedCreator['headline'] ?? ''), 80)) ?></p>
                                <form action="/creator/favorites/toggle" method="post" class="mt-4" data-prototype-skip="1">
                                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                                    <input name="creator_id" type="hidden" value="<?= e((string) ($suggestedCreator['id'] ?? 0)) ?>">
                                    <input name="redirect" type="hidden" value="/creator/favorites">
                                    <button class="w-full rounded-full bg-primary px-4 py-3 text-sm font-bold text-white" type="submit">Salvar no radar</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>
</body>
</html>
