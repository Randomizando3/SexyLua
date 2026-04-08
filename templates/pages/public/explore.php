<?php

declare(strict_types=1);

$currentUser = $app->auth->user();
$creators = $data['creators'] ?? [];
$content = $data['content'] ?? [];
$lives = $data['lives'] ?? [];
$filters = $data['filters'] ?? [];
$query = (string) ($filters['q'] ?? '');
$kind = (string) ($filters['kind'] ?? '');
$liveOnly = (bool) ($filters['live_only'] ?? false);
$includeScheduled = (bool) ($filters['include_scheduled'] ?? false);
$category = (string) ($filters['category'] ?? ($data['audience_category'] ?? 'todos'));
$categoryOptions = audience_category_options();
$guestPreviewLocked = ! is_array($currentUser) || $currentUser === [];
$liveSectionTitle = $liveOnly ? 'Explorando lives' : 'Lives em destaque';
$liveSectionDescription = $includeScheduled
    ? 'Salas ao vivo primeiro, com as proximas transmissoes agendadas logo em seguida.'
    : 'Apenas as salas que ja estao ao vivo agora.';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= e(site_meta_title('Explorar')) ?></title>
    <meta name="description" content="<?= e(site_meta_description('Explore criadores, conteudos exclusivos e lives ao vivo na SexyLua.')) ?>"/>
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
<body>
<?php require BASE_PATH . '/templates/partials/public_topbar.php'; ?>

<main class="mx-auto max-w-7xl px-6 pb-20 pt-28 md:px-8">
    <section class="mb-10 rounded-[2rem] bg-[linear-gradient(135deg,#fff5f8_0%,#fbf9fb_100%)] p-8 shadow-sm">
        <div class="max-w-3xl">
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]">Explorar</p>
            <h1 class="headline mt-3 text-4xl font-extrabold">Descubra criadores, conteudos e lives no seu ritmo.</h1>
            <p class="mt-4 text-slate-600">Use os filtros para encontrar exatamente a categoria, o formato e as salas que combinam com o momento que voce quer viver agora.</p>
        </div>
        <form action="/explore" class="mt-8 grid grid-cols-1 gap-4 rounded-3xl bg-white p-5 shadow-sm md:grid-cols-2 xl:grid-cols-[minmax(0,1.2fr)_220px_220px_auto_auto]" method="get">
            <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="q" placeholder="Buscar por criador, live ou conteudo..." type="search" value="<?= e($query) ?>">
            <select class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="kind">
                <option value="">Todos os formatos</option>
                <option value="gallery" <?= $kind === 'gallery' ? 'selected' : '' ?>>Galeria</option>
                <option value="video" <?= $kind === 'video' ? 'selected' : '' ?>>Video</option>
                <option value="audio" <?= $kind === 'audio' ? 'selected' : '' ?>>Audio</option>
                <option value="article" <?= $kind === 'article' ? 'selected' : '' ?>>Artigo</option>
                <option value="live_teaser" <?= $kind === 'live_teaser' ? 'selected' : '' ?>>Teaser de live</option>
            </select>
            <select class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="category">
                <?php foreach ($categoryOptions as $categoryValue => $categoryLabel): ?>
                    <option value="<?= e($categoryValue) ?>" <?= $category === $categoryValue ? 'selected' : '' ?>><?= e($categoryLabel) ?></option>
                <?php endforeach; ?>
            </select>
            <label class="flex items-center justify-center gap-3 rounded-2xl bg-[#f5f3f5] px-5 py-4 text-sm font-semibold text-slate-700">
                <input <?= $liveOnly ? 'checked' : '' ?> name="live_only" type="checkbox" value="1">
                Foco em lives
            </label>
            <label class="flex items-center justify-center gap-3 rounded-2xl bg-[#f5f3f5] px-5 py-4 text-sm font-semibold text-slate-700">
                <input <?= $includeScheduled ? 'checked' : '' ?> name="include_scheduled" type="checkbox" value="1">
                Exibir agendadas
            </label>
            <div class="flex flex-wrap gap-3 xl:col-span-full">
                <button class="min-w-[120px] rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" type="submit">Filtrar</button>
                <a class="min-w-[110px] rounded-full bg-[#f5f3f5] px-5 py-4 text-center text-sm font-bold text-slate-600" href="<?= e(path_with_query('/explore', ['category' => $category])) ?>">Reset</a>
            </div>
        </form>
    </section>

    <section class="mb-14">
        <div class="mb-6 flex items-end justify-between gap-6">
            <div>
                <h2 class="headline text-3xl font-extrabold"><?= e($liveSectionTitle) ?></h2>
                <p class="mt-2 text-sm text-slate-500"><?= e($liveSectionDescription) ?></p>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            <?php foreach (array_slice($lives, 0, 8) as $live): ?>
                <?php $cover = media_url((string) ($live['cover_url'] ?? '')); ?>
                <?php $coverIsVideo = media_is_video($cover); ?>
                <a class="group overflow-hidden rounded-3xl bg-white shadow-sm transition-transform hover:-translate-y-1" href="<?= e(path_with_query('/live', ['id' => (int) ($live['id'] ?? 0)])) ?>">
                    <div class="relative aspect-[3/4] bg-slate-900">
                        <?php if ($cover !== ''): ?>
                            <?php if ($coverIsVideo): ?>
                                <video autoplay class="h-full w-full scale-105 object-cover transition-transform duration-500 group-hover:scale-[1.08] <?= $guestPreviewLocked ? 'scale-110 blur-[30px] brightness-70' : '' ?>" loop muted playsinline src="<?= e($cover) ?>"></video>
                            <?php else: ?>
                                <img alt="<?= e((string) ($live['title'] ?? 'Live')) ?>" class="h-full w-full scale-105 object-cover transition-transform duration-500 group-hover:scale-[1.08] <?= $guestPreviewLocked ? 'scale-110 blur-[30px] brightness-70' : '' ?>" src="<?= e($cover) ?>">
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="signature-glow flex h-full w-full items-center justify-center p-6 text-center text-white">
                                <span class="headline text-2xl font-extrabold"><?= e((string) ($live['title'] ?? 'Live')) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($guestPreviewLocked): ?>
                            <div class="absolute inset-0 bg-slate-950/45 backdrop-blur-[4px]"></div>
                        <?php endif; ?>
                        <div class="absolute left-4 top-4 rounded-full bg-black/45 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.25em] text-white"><?= e((string) (($live['status'] ?? '') === 'live' ? 'Ao vivo' : 'Agendada')) ?></div>
                        <div class="absolute right-4 top-4 rounded-full bg-white/90 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.25em] text-[#ab1155]"><?= e((string) ($live['category_label'] ?? 'Todos')) ?></div>
                        <?php if ($guestPreviewLocked): ?>
                            <div class="absolute inset-x-4 bottom-4 rounded-full bg-white/90 px-4 py-2 text-center text-[10px] font-bold uppercase tracking-[0.25em] text-[#ab1155]">
                                Entre para liberar
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-2 p-5">
                        <p class="headline truncate text-xl font-extrabold"><?= e((string) ($live['title'] ?? 'Live')) ?></p>
                        <p class="text-sm text-slate-500"><?= e(user_handle($live['creator'] ?? [], 'criador')) ?></p>
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">
                            <?= e((string) (($live['status'] ?? '') === 'live' ? number_format((int) ($live['viewer_count'] ?? 0), 0, ',', '.') . ' viewers' : format_datetime((string) ($live['scheduled_for'] ?? ''), 'd/m H:i'))) ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php if ($lives === []): ?>
                <div class="col-span-full rounded-3xl bg-white p-8 text-sm text-slate-500 shadow-sm">Nenhuma live encontrada com esse filtro agora.</div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (! $liveOnly): ?>
        <section class="mb-14">
            <div class="mb-6 flex items-end justify-between gap-6">
                <div>
                    <h2 class="headline text-3xl font-extrabold">Criadores</h2>
                    <p class="mt-2 text-sm text-slate-500">Perfis para conhecer melhor, seguir e visitar dentro da SexyLua.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
                <?php foreach (array_slice($creators, 0, 8) as $creator): ?>
                    <?php $avatar = media_url((string) ($creator['avatar_url'] ?? '')); ?>
                    <a class="rounded-3xl bg-white p-5 text-center shadow-sm transition-transform hover:-translate-y-1" href="<?= e(creator_public_url($creator)) ?>">
                        <div class="mx-auto mb-4 flex h-28 w-28 items-center justify-center overflow-hidden rounded-[1.75rem] bg-[#f7edf2]">
                            <?php if ($avatar !== ''): ?>
                                <img alt="<?= e(user_handle($creator, 'criador')) ?>" class="h-full w-full object-cover" src="<?= e($avatar) ?>">
                            <?php else: ?>
                                <span class="headline text-3xl font-extrabold text-[#ab1155]"><?= e(user_avatar_label($creator, 'CR')) ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="headline truncate text-xl font-extrabold"><?= e(user_handle($creator, 'criador')) ?></p>
                        <p class="mt-2 line-clamp-2 min-h-[2.75rem] text-sm text-slate-500"><?= e((string) ($creator['headline'] ?? 'Perfil criativo na SexyLua.')) ?></p>
                        <p class="mt-3 text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400"><?= e(number_format((int) ($creator['subscriber_count'] ?? 0), 0, ',', '.')) ?> assinantes</p>
                    </a>
                <?php endforeach; ?>
                <?php if ($creators === []): ?>
                    <div class="col-span-full rounded-3xl bg-white p-8 text-sm text-slate-500 shadow-sm">Nenhum criador encontrado com esse filtro.</div>
                <?php endif; ?>
            </div>
        </section>

        <section>
            <div class="mb-6 flex items-end justify-between gap-6">
                <div>
                    <h2 class="headline text-3xl font-extrabold">Conteudos publicados</h2>
                    <p class="mt-2 text-sm text-slate-500">Fotos, videos e publicacoes para voce explorar em um so lugar.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach (array_slice($content, 0, 9) as $item): ?>
                    <?php $thumbnail = media_url((string) ($item['thumbnail_url'] ?? $item['media_url'] ?? '')); ?>
                    <a class="overflow-hidden rounded-3xl bg-white shadow-sm transition-transform hover:-translate-y-1" href="<?= e(creator_public_url($item['creator'] ?? [])) ?>">
                        <div class="relative aspect-[4/3] bg-slate-900">
                            <?php if ($thumbnail !== ''): ?>
                                <img alt="<?= e((string) ($item['title'] ?? 'Conteudo')) ?>" class="h-full w-full object-cover <?= $guestPreviewLocked ? 'scale-105 blur-[22px] brightness-85' : '' ?>" src="<?= e($thumbnail) ?>">
                            <?php else: ?>
                                <div class="signature-glow flex h-full w-full items-center justify-center p-6 text-center text-white">
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
                                <span class="rounded-full bg-[#f8e8ef] px-3 py-1 text-[10px] font-bold uppercase tracking-[0.25em] text-[#ab1155]"><?= e((string) ($item['kind'] ?? 'conteudo')) ?></span>
                            </div>
                            <p class="line-clamp-2 min-h-[2.75rem] text-sm text-slate-500"><?= e(excerpt((string) ($item['excerpt'] ?? ''), 110)) ?></p>
                            <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">
                                <span><?= e(user_handle($item['creator'] ?? [], 'criador')) ?></span>
                                <span><?= luacoin_amount_html((int) ($item['price_tokens'] ?? 0), 'inline-flex items-center gap-1 whitespace-nowrap', '', 'h-3 w-3 shrink-0') ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
                <?php if ($content === []): ?>
                    <div class="col-span-full rounded-3xl bg-white p-8 text-sm text-slate-500 shadow-sm">Nenhum conteudo publicado encontrado com esse filtro.</div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<footer class="flex w-full flex-col items-center gap-6 bg-[#D81B60] px-10 py-12 text-white">
    <?= brand_logo_white('h-8 w-auto') ?>
    <div class="flex flex-wrap justify-center gap-8">
        <a class="text-xs uppercase tracking-widest text-white/70 transition-all duration-300 hover:text-white" href="/terms">Termos</a>
        <a class="text-xs uppercase tracking-widest text-white/70 transition-all duration-300 hover:text-white" href="/privacy">Privacidade</a>
        <a class="text-xs uppercase tracking-widest text-white/70 transition-all duration-300 hover:text-white" href="/help">Ajuda</a>
    </div>
    <p class="text-[10px] uppercase tracking-[0.2em] text-white/80">© 2026 SexyLua. Explore novos criadores, conteudos e experiencias.</p>
</footer>
</body>
</html>
