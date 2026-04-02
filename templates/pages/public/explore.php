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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Explorar</title>
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
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]">Exploração real</p>
            <h1 class="headline mt-3 text-4xl font-extrabold">Descubra criadores, conteúdos e lives reais.</h1>
            <p class="mt-4 text-slate-600">Nada aqui vem de mock ou demo: esta vitrine já responde ao que foi cadastrado de verdade na plataforma.</p>
        </div>
        <form action="/explore" class="mt-8 grid grid-cols-1 gap-4 rounded-3xl bg-white p-5 shadow-sm md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_220px_auto_auto]" method="get">
            <input class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="q" placeholder="Buscar por criador, live ou conteúdo..." type="search" value="<?= e($query) ?>">
            <select class="rounded-2xl border-none bg-[#f5f3f5] px-5 py-4" name="kind">
                <option value="">Todos os tipos</option>
                <option value="gallery" <?= $kind === 'gallery' ? 'selected' : '' ?>>Galeria</option>
                <option value="video" <?= $kind === 'video' ? 'selected' : '' ?>>Vídeo</option>
                <option value="audio" <?= $kind === 'audio' ? 'selected' : '' ?>>Áudio</option>
                <option value="article" <?= $kind === 'article' ? 'selected' : '' ?>>Artigo</option>
                <option value="live_teaser" <?= $kind === 'live_teaser' ? 'selected' : '' ?>>Teaser de live</option>
            </select>
            <label class="flex items-center justify-center gap-3 rounded-2xl bg-[#f5f3f5] px-5 py-4 text-sm font-semibold text-slate-700">
                <input <?= $liveOnly ? 'checked' : '' ?> name="live_only" type="checkbox" value="1">
                Mostrar foco em lives
            </label>
            <div class="flex flex-wrap gap-3">
                <button class="min-w-[120px] rounded-full bg-slate-900 px-6 py-4 text-sm font-bold text-white" type="submit">Filtrar</button>
                <a class="min-w-[110px] rounded-full bg-[#f5f3f5] px-5 py-4 text-center text-sm font-bold text-slate-600" href="/explore">Reset</a>
            </div>
        </form>
    </section>

    <section class="mb-14">
        <div class="mb-6 flex items-end justify-between gap-6">
            <div>
                <h2 class="headline text-3xl font-extrabold">Lives e sessões</h2>
                <p class="mt-2 text-sm text-slate-500">Transmissões que já estão no ar ou já foram agendadas pelos criadores.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            <?php foreach (array_slice($lives, 0, 8) as $live): ?>
                <?php $cover = media_url((string) ($live['cover_url'] ?? '')); ?>
                <a class="group overflow-hidden rounded-3xl bg-white shadow-sm transition-transform hover:-translate-y-1" href="<?= e(path_with_query('/live', ['id' => (int) ($live['id'] ?? 0)])) ?>">
                    <div class="relative aspect-[3/4] bg-slate-900">
                        <?php if ($cover !== ''): ?>
                            <img alt="<?= e((string) ($live['title'] ?? 'Live')) ?>" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" src="<?= e($cover) ?>">
                        <?php else: ?>
                            <div class="signature-glow flex h-full w-full items-center justify-center p-6 text-center text-white">
                                <span class="headline text-2xl font-extrabold"><?= e((string) ($live['title'] ?? 'Live')) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="absolute left-4 top-4 rounded-full bg-black/45 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.25em] text-white"><?= e((string) (($live['status'] ?? '') === 'live' ? 'Ao vivo' : 'Agendada')) ?></div>
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
            <?php if ($lives === []): ?>
                <div class="col-span-full rounded-3xl bg-white p-8 text-sm text-slate-500 shadow-sm">Nenhuma live encontrada com esse filtro.</div>
            <?php endif; ?>
        </div>
    </section>

    <section class="mb-14">
        <div class="mb-6 flex items-end justify-between gap-6">
            <div>
                <h2 class="headline text-3xl font-extrabold">Criadores</h2>
                <p class="mt-2 text-sm text-slate-500">Perfis públicos conectados ao banco atual.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            <?php foreach (array_slice($creators, 0, 8) as $creator): ?>
                <?php $avatar = media_url((string) ($creator['avatar_url'] ?? '')); ?>
                <a class="rounded-3xl bg-white p-5 shadow-sm transition-transform hover:-translate-y-1" href="<?= e(path_with_query('/profile', ['id' => (int) ($creator['id'] ?? 0)])) ?>">
                    <div class="mb-4 flex h-28 items-center justify-center overflow-hidden rounded-3xl bg-[#f7edf2]">
                        <?php if ($avatar !== ''): ?>
                            <img alt="<?= e((string) ($creator['name'] ?? 'Criador')) ?>" class="h-full w-full object-cover" src="<?= e($avatar) ?>">
                        <?php else: ?>
                            <span class="headline text-3xl font-extrabold text-[#ab1155]"><?= e(avatar_initials((string) ($creator['name'] ?? 'Criador'))) ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="headline truncate text-xl font-extrabold"><?= e((string) ($creator['name'] ?? 'Criador')) ?></p>
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
                <h2 class="headline text-3xl font-extrabold">Conteúdos publicados</h2>
                <p class="mt-2 text-sm text-slate-500">Itens aprovados e disponíveis no estado atual da plataforma.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach (array_slice($content, 0, 9) as $item): ?>
                <?php $thumbnail = media_url((string) ($item['thumbnail_url'] ?? $item['media_url'] ?? '')); ?>
                <a class="overflow-hidden rounded-3xl bg-white shadow-sm transition-transform hover:-translate-y-1" href="<?= e(path_with_query('/profile', ['id' => (int) ($item['creator']['id'] ?? 0)])) ?>">
                    <div class="aspect-[4/3] bg-slate-900">
                        <?php if ($thumbnail !== ''): ?>
                            <img alt="<?= e((string) ($item['title'] ?? 'Conteúdo')) ?>" class="h-full w-full object-cover" src="<?= e($thumbnail) ?>">
                        <?php else: ?>
                            <div class="signature-glow flex h-full w-full items-center justify-center p-6 text-center text-white">
                                <span class="headline text-2xl font-extrabold"><?= e((string) strtoupper((string) ($item['kind'] ?? 'conteúdo'))) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-2 p-5">
                        <div class="flex items-center justify-between gap-3">
                            <p class="headline truncate text-xl font-extrabold"><?= e((string) ($item['title'] ?? 'Conteúdo')) ?></p>
                            <span class="rounded-full bg-[#f8e8ef] px-3 py-1 text-[10px] font-bold uppercase tracking-[0.25em] text-[#ab1155]"><?= e((string) ($item['kind'] ?? 'conteúdo')) ?></span>
                        </div>
                        <p class="line-clamp-2 min-h-[2.75rem] text-sm text-slate-500"><?= e(excerpt((string) ($item['excerpt'] ?? ''), 110)) ?></p>
                        <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">
                            <span><?= e((string) ($item['creator']['name'] ?? 'Criador')) ?></span>
                            <span><?= luacoin_amount_html((int) ($item['price_tokens'] ?? 0), 'inline-flex items-center gap-1 whitespace-nowrap', '', 'h-3 w-3 shrink-0') ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php if ($content === []): ?>
                <div class="col-span-full rounded-3xl bg-white p-8 text-sm text-slate-500 shadow-sm">Nenhum conteúdo publicado encontrado com esse filtro.</div>
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
    <p class="text-[10px] uppercase tracking-[0.2em] text-white/80">© 2026 SexyLua. Busca pública conectada aos dados reais.</p>
</footer>
</body>
</html>
