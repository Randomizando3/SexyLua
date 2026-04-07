<?php

declare(strict_types=1);

$currentUser = $app->auth->user();
$featuredCreators = $data['featured_creators'] ?? [];
$liveNow = $data['live_now'] ?? [];
$featuredContent = $data['featured_content'] ?? [];
$stats = $data['stats'] ?? [];
$settings = $data['settings'] ?? [];
$audienceCategory = (string) ($data['audience_category'] ?? 'todos');
$audienceLabel = audience_category_label($audienceCategory);
$guestPreviewLocked = ! is_array($currentUser) || $currentUser === [];
$liveShowcase = $liveNow;
$liveSectionTitle = $liveNow !== [] ? 'Ao vivo agora' : 'Lives em destaque';
$liveSectionDescription = $liveNow !== [] 
    ? 'Entre nas salas que ja estao acontecendo agora na SexyLua.'
    : 'No momento nao ha transmissoes no ar para esta categoria, mas a vitrine segue pronta para quando a proxima live comecar.';
$bannerEnabled = !empty($settings['home_banner_enabled']);
$bannerBackground = media_url((string) ($settings['home_banner_background_url'] ?? ''));
$bannerBackground = $bannerBackground !== '' ? $bannerBackground : home_banner_default_image_url();
$bannerMobileBackground = media_url((string) ($settings['home_banner_background_mobile_url'] ?? ''));
$bannerMobileBackground = $bannerMobileBackground !== '' ? $bannerMobileBackground : $bannerBackground;
$bannerTitle = (string) ($settings['home_banner_title'] ?? 'Cadastre-se hoje e ganhe 10 LuaCoins gratis');
$bannerSubtitle = (string) ($settings['home_banner_subtitle'] ?? 'Crie sua conta agora, receba 10 LuaCoins no cadastro e aproveite bonus extra em cada deposito para entrar na SexyLua com mais liberdade.');
$bannerPrimaryText = (string) ($settings['home_banner_primary_text'] ?? 'Explorar agora');
$bannerPrimaryLink = (string) ($settings['home_banner_primary_link'] ?? '/explore');
$bannerSecondaryText = (string) ($settings['home_banner_secondary_text'] ?? 'Criar conta');
$bannerSecondaryLink = (string) ($settings['home_banner_secondary_link'] ?? '/register');
$bannerCountdownEnabled = !empty($settings['home_banner_countdown_enabled']);
$bannerCountdownSeconds = max(0, (int) ($settings['home_banner_countdown_seconds'] ?? 172800));
$bannerCountdownTargetAt = trim((string) ($settings['home_banner_countdown_target_at'] ?? ''));
$bannerCountdownTargetTimestamp = $bannerCountdownTargetAt !== '' ? strtotime($bannerCountdownTargetAt) : false;
if ($bannerCountdownTargetTimestamp === false || $bannerCountdownTargetTimestamp <= 0) {
    $bannerCountdownTargetTimestamp = time() + $bannerCountdownSeconds;
}
$bannerCountdownRemaining = max(0, $bannerCountdownTargetTimestamp - time());
$bannerCountdownHours = (int) floor($bannerCountdownRemaining / 3600);
$bannerCountdownMinutes = (int) floor(($bannerCountdownRemaining % 3600) / 60);
$bannerCountdownDisplay = sprintf('%02d:%02d:%02d', $bannerCountdownHours, $bannerCountdownMinutes, $bannerCountdownRemaining % 60);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= e(site_meta_title('Home')) ?></title>
    <meta name="description" content="<?= e(site_meta_description('Descubra criadores, conteudos exclusivos e lives em destaque na SexyLua.')) ?>"/>
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
    <?php if ($bannerEnabled): ?>
        <section class="relative overflow-hidden px-4 pt-6 sm:px-8">
            <div class="mx-auto max-w-7xl overflow-hidden rounded-[2.25rem] bg-slate-950 shadow-[0px_24px_56px_rgba(27,28,29,0.16)]">
                <div class="relative min-h-[340px] md:min-h-[390px]">
                    <picture class="absolute inset-0 block h-full w-full">
                        <source media="(max-width: 767px)" srcset="<?= e($bannerMobileBackground) ?>">
                        <img alt="Banner SexyLua" class="h-full w-full object-cover" src="<?= e($bannerBackground) ?>">
                    </picture>
                    <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(33,7,20,0.88)_0%,rgba(33,7,20,0.55)_42%,rgba(33,7,20,0.2)_100%)]"></div>
                    <div class="relative z-10 flex min-h-[340px] flex-col justify-between gap-8 px-6 py-7 md:min-h-[390px] md:px-10 md:py-9">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/12 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.28em] text-white/90">
                                <span class="material-symbols-outlined text-base" style="font-variation-settings:'FILL' 1;">local_fire_department</span>
                                Destaque da semana
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/12 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.28em] text-white/90">
                                Categoria atual: <?= e($audienceLabel) ?>
                            </span>
                            <?php if ($bannerCountdownEnabled): ?>
                                <span class="inline-flex items-center gap-3 rounded-full bg-[#2a0815]/80 px-4 py-2 text-white shadow-lg" data-home-banner-countdown data-target-ts="<?= e((string) $bannerCountdownTargetTimestamp) ?>">
                                    <span class="text-[10px] font-bold uppercase tracking-[0.28em] text-white/65">Ultima chance</span>
                                    <span class="headline text-2xl font-extrabold tracking-[0.18em]"><?= e($bannerCountdownDisplay) ?></span>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="max-w-2xl">
                            <h1 class="headline text-4xl font-extrabold leading-tight text-white md:text-6xl"><?= e($bannerTitle) ?></h1>
                            <p class="mt-5 max-w-xl text-base leading-relaxed text-white/80 md:text-lg"><?= e($bannerSubtitle) ?></p>
                            <div class="mt-8 flex flex-wrap gap-4">
                                <a class="rounded-full bg-white px-7 py-4 text-sm font-bold uppercase tracking-[0.22em] text-[#ab1155] shadow-lg" href="<?= e($bannerPrimaryLink !== '' ? $bannerPrimaryLink : '/explore') ?>">
                                    <?= e($bannerPrimaryText !== '' ? $bannerPrimaryText : 'Explorar agora') ?>
                                </a>
                                <a class="rounded-full border border-white/20 bg-white/10 px-7 py-4 text-sm font-bold uppercase tracking-[0.22em] text-white" href="<?= e($bannerSecondaryLink !== '' ? $bannerSecondaryLink : '/register') ?>">
                                    <?= e($bannerSecondaryText !== '' ? $bannerSecondaryText : 'Criar conta') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="relative overflow-hidden px-8 py-20">
        <div class="mx-auto max-w-5xl text-center">
            <div class="flex justify-center">
                <div class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60] shadow-sm">
                    <span class="material-symbols-outlined text-sm" style="font-variation-settings:'FILL' 1;">nightlight</span>
                    Curadoria SexyLua
                </div>
            </div>
            <h2 class="headline mx-auto mt-6 max-w-4xl text-3xl font-extrabold leading-tight text-slate-950 md:text-5xl">Encontre criadores, conteudos e salas ao vivo do jeito que voce quer explorar.</h2>
            <p class="mx-auto mt-5 max-w-3xl text-base leading-relaxed text-slate-600 md:text-lg">
                Entre para desbloquear a experiencia completa, conversar com criadores, salvar favoritos, assinar planos e assistir sem restricoes visuais.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a class="signature-glow rounded-full px-8 py-4 text-sm font-bold uppercase tracking-widest text-white shadow-lg shadow-[#ab1155]/20" href="<?= e(path_with_query('/explore', ['category' => $audienceCategory])) ?>">Explorar agora</a>
                    <?php if (! $currentUser): ?>
                        <a class="rounded-full bg-white px-8 py-4 text-sm font-bold uppercase tracking-widest text-slate-700 shadow-sm" href="/register">Criar conta</a>
                    <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-8 py-16">
        <div class="mb-8 flex items-end justify-between gap-6">
            <div>
                <h2 class="headline text-3xl font-extrabold"><?= e($liveSectionTitle) ?></h2>
                <p class="mt-2 text-sm text-slate-500"><?= e($liveSectionDescription) ?></p>
            </div>
            <a class="text-sm font-bold text-[#ab1155] underline" href="<?= e(path_with_query('/explore', ['category' => $audienceCategory])) ?>">Ver tudo</a>
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
            <?php foreach (array_slice($liveShowcase, 0, 4) as $live): ?>
                <?php $cover = media_url((string) ($live['cover_url'] ?? '')); ?>
                <a class="group overflow-hidden rounded-3xl bg-white shadow-[0px_20px_40px_rgba(27,28,29,0.06)] transition-transform hover:-translate-y-1" href="<?= e(path_with_query('/live', ['id' => (int) ($live['id'] ?? 0)])) ?>">
                    <div class="relative aspect-[3/4] bg-slate-900">
                        <?php if ($cover !== ''): ?>
                            <img alt="<?= e((string) ($live['title'] ?? 'Live')) ?>" class="h-full w-full scale-105 object-cover transition-transform duration-500 group-hover:scale-[1.08] <?= $guestPreviewLocked ? 'scale-110 blur-[30px] brightness-70' : '' ?>" src="<?= e($cover) ?>">
                        <?php else: ?>
                            <div class="signature-glow flex h-full w-full items-center justify-center p-6 text-center text-white">
                                <span class="headline text-2xl font-extrabold"><?= e((string) ($live['title'] ?? 'Live')) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($guestPreviewLocked): ?>
                            <div class="absolute inset-0 bg-slate-950/45 backdrop-blur-[4px]"></div>
                        <?php endif; ?>
                        <div class="absolute left-4 top-4 rounded-full bg-black/45 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.25em] text-white">Ao vivo</div>
                        <div class="absolute right-4 top-4 rounded-full bg-white/90 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.25em] text-[#ab1155]"><?= e((string) ($live['category_label'] ?? 'Todos')) ?></div>
                        <?php if ($guestPreviewLocked): ?>
                            <div class="absolute inset-x-4 bottom-4 rounded-full bg-white/90 px-4 py-2 text-center text-[10px] font-bold uppercase tracking-[0.25em] text-[#ab1155]">
                                Entre para liberar
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-2 p-5">
                        <p class="headline truncate text-xl font-extrabold"><?= e((string) ($live['title'] ?? 'Live')) ?></p>
                        <p class="text-sm text-slate-500"><?= e((string) ($live['creator']['name'] ?? 'Criador')) ?></p>
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400"><?= e(number_format((int) ($live['viewer_count'] ?? 0), 0, ',', '.')) ?> viewers</p>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php if ($liveShowcase === []): ?>
                <div class="col-span-full rounded-3xl bg-white p-8 text-sm text-slate-500 shadow-sm">
                    Nenhuma live ao vivo neste momento para a categoria <?= e($audienceLabel) ?>. Explore criadores e acompanhe as proximas salas pelo explorar.
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="bg-[#fff6f8] py-16">
        <div class="mx-auto max-w-7xl px-8">
            <div class="mb-8 flex items-end justify-between gap-6">
                <div>
                    <h2 class="headline text-3xl font-extrabold">Criadores em destaque</h2>
                    <p class="mt-2 text-sm text-slate-500">Perfis para descobrir, acompanhar e favoritar dentro da sua atmosfera atual.</p>
                </div>
                <a class="text-sm font-bold text-[#ab1155] underline" href="<?= e(path_with_query('/explore', ['category' => $audienceCategory])) ?>">Explorar criadores</a>
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-5">
                <?php foreach (array_slice($featuredCreators, 0, 5) as $creator): ?>
                    <?php $avatar = media_url((string) ($creator['avatar_url'] ?? '')); ?>
                    <a class="rounded-3xl bg-white p-5 text-center shadow-sm transition-transform hover:-translate-y-1" href="<?= e(path_with_query('/profile', ['id' => (int) ($creator['id'] ?? 0)])) ?>">
                        <div class="mx-auto mb-4 flex h-28 w-28 items-center justify-center overflow-hidden rounded-[1.75rem] border border-[#f4d9e3] bg-[#f7edf2]">
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
                        Ainda nao ha criadores publicos em destaque.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-8 py-16">
        <div class="mb-8 flex items-end justify-between gap-6">
            <div>
                <h2 class="headline text-3xl font-extrabold">Conteudos publicados</h2>
                <p class="mt-2 text-sm text-slate-500">Uma selecao de fotos, videos e publicacoes para explorar agora.</p>
            </div>
            <a class="text-sm font-bold text-[#ab1155] underline" href="<?= e(path_with_query('/explore', ['category' => $audienceCategory])) ?>">Ver catalogo</a>
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach (array_slice($featuredContent, 0, 6) as $item): ?>
                <?php
                $thumbnail = media_url((string) ($item['thumbnail_url'] ?? $item['media_url'] ?? ''));
                $creatorUrl = path_with_query('/profile', ['id' => (int) ($item['creator']['id'] ?? 0)]);
                ?>
                <a class="overflow-hidden rounded-3xl bg-white shadow-[0px_20px_40px_rgba(27,28,29,0.06)] transition-transform hover:-translate-y-1" href="<?= e($creatorUrl) ?>">
                    <div class="relative aspect-[4/3] bg-slate-900">
                        <?php if ($thumbnail !== ''): ?>
                            <img alt="<?= e((string) ($item['title'] ?? 'Conteudo')) ?>" class="h-full w-full object-cover <?= $guestPreviewLocked ? 'scale-105 blur-[22px] brightness-85' : '' ?>" src="<?= e($thumbnail) ?>">
                        <?php else: ?>
                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#cc326e] via-[#ab1155] to-[#5a0d31] p-6 text-center text-white">
                                <span class="headline text-2xl font-extrabold"><?= e((string) strtoupper((string) ($item['kind'] ?? 'conteudo'))) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($guestPreviewLocked): ?>
                            <div class="absolute inset-0 bg-slate-950/42 backdrop-blur-[4px]"></div>
                            <div class="absolute inset-x-4 bottom-4 rounded-full bg-white/90 px-4 py-2 text-center text-[10px] font-bold uppercase tracking-[0.25em] text-[#ab1155]">
                                Entre para desbloquear
                            </div>
                        <?php endif; ?>
                        <div class="absolute right-4 top-4 rounded-full bg-white/90 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.25em] text-[#ab1155]"><?= e((string) ($item['category_label'] ?? 'Todos')) ?></div>
                    </div>
                    <div class="space-y-2 p-5">
                        <div class="flex items-center justify-between gap-3">
                            <p class="headline truncate text-xl font-extrabold"><?= e((string) ($item['title'] ?? 'Conteudo')) ?></p>
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
                    Ainda nao ha conteudos aprovados para a categoria <?= e($audienceLabel) ?>.
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
    </div>
    <p class="text-[10px] uppercase tracking-[0.2em] text-white/80">© 2026 SexyLua. Criadores, conteudos e lives em um so lugar.</p>
</footer>

<?php if ($bannerEnabled && $bannerCountdownEnabled): ?>
    <script>
        (() => {
            const nodes = document.querySelectorAll('[data-home-banner-countdown]');
            nodes.forEach((node) => {
                const targetTimestamp = Number.parseInt(node.getAttribute('data-target-ts') || '0', 10);
                if (!Number.isFinite(targetTimestamp) || targetTimestamp <= 0) {
                    return;
                }

                const value = node.querySelector('.headline');
                const render = () => {
                    const remaining = Math.max(0, targetTimestamp - Math.floor(Date.now() / 1000));
                    const hours = Math.floor(remaining / 3600);
                    const minutes = Math.floor((remaining % 3600) / 60);
                    const seconds = remaining % 60;
                    if (value) {
                        value.textContent = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                    }
                };

                render();
                window.setInterval(() => {
                    render();
                }, 1000);
            });
        })();
    </script>
<?php endif; ?>
</body>
</html>
