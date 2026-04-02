<?php

declare(strict_types=1);

$currentUser = $app->auth->user();
$featuredCreators = $data['featured_creators'] ?? [];
$liveNow = $data['live_now'] ?? [];
$upcomingLives = $data['upcoming_lives'] ?? [];
$featuredContent = $data['featured_content'] ?? [];
$stats = $data['stats'] ?? [];
$liveShowcase = $liveNow !== [] ? $liveNow : $upcomingLives;
$liveSectionTitle = $liveNow !== [] ? 'Lives ao vivo agora' : 'Próximas lives';
$liveSectionDescription = $liveNow !== [] ? 'Quem já está no ar neste momento.' : 'As próximas transmissões agendadas pelos criadores.';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua | Home</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style>
        body { background: #fbf9fb; color: #1b1c1d; font-family: Manrope, sans-serif; }
        .headline { font-family: "Plus Jakarta Sans", sans-serif; }
        .signature-glow { background: linear-gradient(135deg, #D81B60 0%, #ab1155 100%); }
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
    </style>
</head>
<body class="overflow-x-hidden">
<?php require BASE_PATH . '/templates/partials/public_topbar.php'; ?>

<main class="pb-20 pt-20">
    <section class="relative overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(216,27,96,0.12),_transparent_35%),radial-gradient(circle_at_bottom_right,_rgba(171,17,85,0.12),_transparent_35%),linear-gradient(180deg,#fff5f8_0%,#fbf9fb_100%)] px-8 py-20">
        <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)] lg:items-center">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60] shadow-sm">
                    <span class="material-symbols-outlined text-sm" style="font-variation-settings:'FILL' 1;">nightlight</span>
                    Plataforma ativa
                </div>
                <h1 class="headline mt-6 max-w-3xl text-4xl font-extrabold leading-tight text-slate-950 md:text-6xl">Experiências reais, criadores reais e tudo conectado ao backend.</h1>
                <p class="mt-6 max-w-2xl text-lg leading-relaxed text-slate-600">
                    A SexyLua agora mostra só o que existe de verdade na plataforma: criadores cadastrados, conteúdos publicados, assinantes ativos e lives que já estão acontecendo ou foram agendadas.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a class="signature-glow rounded-full px-8 py-4 text-sm font-bold uppercase tracking-widest text-white shadow-lg shadow-[#ab1155]/20" href="/explore">Explorar agora</a>
                    <?php if (! $currentUser): ?>
                        <a class="rounded-full bg-white px-8 py-4 text-sm font-bold uppercase tracking-widest text-slate-700 shadow-sm" href="/register">Criar conta</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Criadores</p>
                    <p class="headline mt-3 text-4xl font-extrabold text-[#D81B60]"><?= e(number_format((int) ($stats['creators'] ?? 0), 0, ',', '.')) ?></p>
                </div>
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Ao vivo</p>
                    <p class="headline mt-3 text-4xl font-extrabold text-[#D81B60]"><?= e(number_format((int) ($stats['live_now'] ?? 0), 0, ',', '.')) ?></p>
                </div>
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Conteúdos</p>
                    <p class="headline mt-3 text-4xl font-extrabold text-[#D81B60]"><?= e(number_format((int) ($stats['approved_content'] ?? 0), 0, ',', '.')) ?></p>
                </div>
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Assinantes</p>
                    <p class="headline mt-3 text-4xl font-extrabold text-[#D81B60]"><?= e(number_format((int) ($stats['subscribers'] ?? 0), 0, ',', '.')) ?></p>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-8 py-16">
        <div class="mb-8 flex items-end justify-between gap-6">
            <div>
                <h2 class="headline text-3xl font-extrabold"><?= e($liveSectionTitle) ?></h2>
                <p class="mt-2 text-sm text-slate-500"><?= e($liveSectionDescription) ?></p>
            </div>
            <a class="text-sm font-bold text-[#ab1155] underline" href="/explore">Ver tudo</a>
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
            <?php foreach (array_slice($liveShowcase, 0, 4) as $live): ?>
                <?php $cover = media_url((string) ($live['cover_url'] ?? '')); ?>
                <a class="group overflow-hidden rounded-3xl bg-white shadow-[0px_20px_40px_rgba(27,28,29,0.06)] transition-transform hover:-translate-y-1" href="<?= e(path_with_query('/live', ['id' => (int) ($live['id'] ?? 0)])) ?>">
                    <div class="relative aspect-[3/4] bg-slate-900">
                        <?php if ($cover !== ''): ?>
                            <img alt="<?= e((string) ($live['title'] ?? 'Live')) ?>" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" src="<?= e($cover) ?>">
                        <?php else: ?>
                            <div class="signature-glow flex h-full w-full items-center justify-center p-6 text-center text-white">
                                <span class="headline text-2xl font-extrabold"><?= e((string) ($live['title'] ?? 'Live')) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="absolute left-4 top-4 rounded-full bg-black/45 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.25em] text-white">
                            <?= e((string) (($live['status'] ?? '') === 'live' ? 'Ao vivo' : 'Agendada')) ?>
                        </div>
                    </div>
                    <div class="space-y-2 p-5">
                        <p class="headline truncate text-xl font-extrabold"><?= e((string) ($live['title'] ?? 'Live')) ?></p>
                        <p class="text-sm text-slate-500"><?= e((string) ($live['creator']['name'] ?? 'Criador')) ?></p>
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">
                            <?= e((string) (($live['status'] ?? '') === 'live' ? number_format((int) ($live['viewer_count'] ?? 0), 0, ',', '.') . ' viewers' : format_datetime((string) ($live['scheduled_for'] ?? ''), 'd/m H:i'))) ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php if ($liveShowcase === []): ?>
                <div class="col-span-full rounded-3xl bg-white p-8 text-sm text-slate-500 shadow-sm">
                    Nenhuma live disponível no momento. Quando um criador entrar ao vivo ou agendar uma sessão, ela aparece aqui.
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="bg-[#fff6f8] py-16">
        <div class="mx-auto max-w-7xl px-8">
            <div class="mb-8 flex items-end justify-between gap-6">
                <div>
                    <h2 class="headline text-3xl font-extrabold">Criadores em destaque</h2>
                    <p class="mt-2 text-sm text-slate-500">Perfis reais com conteúdo e presença ativa na plataforma.</p>
                </div>
                <a class="text-sm font-bold text-[#ab1155] underline" href="/explore">Explorar criadores</a>
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-5">
                <?php foreach (array_slice($featuredCreators, 0, 5) as $creator): ?>
                    <?php $avatar = media_url((string) ($creator['avatar_url'] ?? '')); ?>
                    <a class="rounded-3xl bg-white p-5 text-center shadow-sm transition-transform hover:-translate-y-1" href="<?= e(path_with_query('/profile', ['id' => (int) ($creator['id'] ?? 0)])) ?>">
                        <div class="mx-auto mb-4 flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border-4 border-[#f4d9e3] bg-[#f7edf2]">
                            <?php if ($avatar !== ''): ?>
                                <img alt="<?= e((string) ($creator['name'] ?? 'Criador')) ?>" class="h-full w-full object-cover" src="<?= e($avatar) ?>">
                            <?php else: ?>
                                <span class="headline text-2xl font-extrabold text-[#ab1155]"><?= e(avatar_initials((string) ($creator['name'] ?? 'Criador'))) ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="headline truncate text-lg font-extrabold"><?= e((string) ($creator['name'] ?? 'Criador')) ?></p>
                        <p class="mt-2 line-clamp-2 min-h-[2.75rem] text-sm text-slate-500"><?= e((string) ($creator['headline'] ?? 'Perfil criativo na SexyLua.')) ?></p>
                        <p class="mt-3 text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400"><?= e(number_format((int) ($creator['subscriber_count'] ?? 0), 0, ',', '.')) ?> assinantes</p>
                    </a>
                <?php endforeach; ?>
                <?php if ($featuredCreators === []): ?>
                    <div class="col-span-full rounded-3xl bg-white p-8 text-sm text-slate-500 shadow-sm">
                        Ainda não há criadores visíveis na plataforma pública.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-8 py-16">
        <div class="mb-8 flex items-end justify-between gap-6">
            <div>
                <h2 class="headline text-3xl font-extrabold">Conteúdos publicados</h2>
                <p class="mt-2 text-sm text-slate-500">Amostra do que já foi publicado e aprovado de verdade.</p>
            </div>
            <a class="text-sm font-bold text-[#ab1155] underline" href="/explore">Ver catálogo</a>
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach (array_slice($featuredContent, 0, 6) as $item): ?>
                <?php
                $thumbnail = media_url((string) ($item['thumbnail_url'] ?? $item['media_url'] ?? ''));
                $creatorUrl = path_with_query('/profile', ['id' => (int) ($item['creator']['id'] ?? 0)]);
                ?>
                <a class="overflow-hidden rounded-3xl bg-white shadow-[0px_20px_40px_rgba(27,28,29,0.06)] transition-transform hover:-translate-y-1" href="<?= e($creatorUrl) ?>">
                    <div class="aspect-[4/3] bg-slate-900">
                        <?php if ($thumbnail !== ''): ?>
                            <img alt="<?= e((string) ($item['title'] ?? 'Conteúdo')) ?>" class="h-full w-full object-cover" src="<?= e($thumbnail) ?>">
                        <?php else: ?>
                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#cc326e] via-[#ab1155] to-[#5a0d31] p-6 text-center text-white">
                                <span class="headline text-2xl font-extrabold"><?= e((string) strtoupper((string) ($item['kind'] ?? 'conteúdo'))) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-2 p-5">
                        <div class="flex items-center justify-between gap-3">
                            <p class="headline truncate text-xl font-extrabold"><?= e((string) ($item['title'] ?? 'Conteúdo')) ?></p>
                            <span class="rounded-full bg-[#f8e8ef] px-3 py-1 text-[10px] font-bold uppercase tracking-[0.25em] text-[#ab1155]"><?= e((string) ($item['visibility'] ?? 'public')) ?></span>
                        </div>
                        <p class="line-clamp-2 min-h-[2.75rem] text-sm text-slate-500"><?= e((string) excerpt((string) ($item['excerpt'] ?? ''), 110)) ?></p>
                        <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">
                            <span><?= e((string) ($item['creator']['name'] ?? 'Criador')) ?></span>
                            <span><?= luacoin_amount_html((int) ($item['price_tokens'] ?? 0), 'inline-flex items-center gap-1 whitespace-nowrap', '', 'h-3 w-3 shrink-0') ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php if ($featuredContent === []): ?>
                <div class="col-span-full rounded-3xl bg-white p-8 text-sm text-slate-500 shadow-sm">
                    Ainda não há conteúdos aprovados para mostrar na home.
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<footer class="flex w-full flex-col items-center gap-6 bg-[#D81B60] px-10 py-12 text-white">
    <?= brand_logo_white('h-8 w-auto') ?>
    <div class="flex flex-wrap justify-center gap-8">
        <a class="text-xs uppercase tracking-widest text-white/70 transition-all duration-300 hover:text-white" href="/terms">Termos</a>
        <a class="text-xs uppercase tracking-widest text-white/70 transition-all duration-300 hover:text-white" href="/privacy">Privacidade</a>
        <a class="text-xs uppercase tracking-widest text-white/70 transition-all duration-300 hover:text-white" href="/help">Ajuda</a>
        <a class="text-xs uppercase tracking-widest text-white/70 transition-all duration-300 hover:text-white" href="#">Carreiras</a>
    </div>
    <p class="text-[10px] uppercase tracking-[0.2em] text-white/80">© 2026 SexyLua. Plataforma conectada aos dados reais.</p>
</footer>
</body>
</html>
