<?php

declare(strict_types=1);

$currentUser = $app->auth->user();
$creator = $data['creator'] ?? [];
$plans = $data['plans'] ?? [];
$content = $data['content'] ?? [];
$upcomingLives = $data['upcoming_lives'] ?? [];
$relatedCreators = $data['related_creators'] ?? [];
$isFavorite = (bool) ($data['is_favorite'] ?? false);
$isSubscribed = (bool) ($data['is_subscribed'] ?? false);
$creatorId = (int) ($creator['id'] ?? 0);
$cover = media_url((string) ($creator['cover_url'] ?? ''));
$avatar = media_url((string) ($creator['avatar_url'] ?? ''));
$coverIsVideo = media_is_video($cover);
$profileUrl = creator_public_url($creator);
$primaryPlan = $plans[0] ?? null;
$canInteractAsSubscriber = ($currentUser['role'] ?? '') === 'subscriber';
$requestedContentId = isset($_GET['content']) ? (int) $_GET['content'] : 0;
$selectedContentPayload = null;
$selectedLockedPayload = null;
$viewerId = (int) ($currentUser['id'] ?? 0);
$viewerRole = (string) ($currentUser['role'] ?? '');
$guestPreviewLocked = ! is_array($currentUser) || $currentUser === [];
$creatorHandle = user_handle($creator, 'criador');
$creatorAvatarLabel = user_avatar_label($creator, 'CR');
$canAccessContent = static function (array $item) use ($creatorId, $viewerId, $viewerRole, $isSubscribed): bool {
    $visibility = (string) ($item['visibility'] ?? 'public');
    if ($visibility === 'public') {
        return true;
    }

    if ($viewerRole === 'admin') {
        return true;
    }

    if ($viewerRole === 'creator' && $viewerId === $creatorId) {
        return true;
    }

    return $isSubscribed;
};
$lockedMessageForContent = static function (array $item) use ($guestPreviewLocked): string {
    if ($guestPreviewLocked) {
        return 'Entre na sua conta para visualizar este conteudo.';
    }

    $visibility = (string) ($item['visibility'] ?? 'subscriber');
    return $visibility === 'premium'
        ? 'Este conteúdo exige um plano ativo para ser desbloqueado.'
        : 'Este conteúdo é exclusivo para assinantes.';
};

if ($requestedContentId > 0) {
    foreach ($content as $candidate) {
        if ((int) ($candidate['id'] ?? 0) !== $requestedContentId) {
            continue;
        }

        $candidatePlan = is_array($candidate['plan'] ?? null) ? $candidate['plan'] : null;
        if ($canAccessContent($candidate) && ! $guestPreviewLocked) {
            $selectedContentPayload = [
                'id' => (int) ($candidate['id'] ?? 0),
                'title' => (string) ($candidate['title'] ?? 'Conteúdo'),
                'kind' => (string) ($candidate['kind'] ?? 'gallery'),
                'thumbnail_url' => media_url((string) ($candidate['thumbnail_url'] ?? $candidate['media_url'] ?? '')),
                'media_url' => media_url((string) ($candidate['media_url'] ?? '')),
                'excerpt' => (string) ($candidate['excerpt'] ?? ''),
                'body' => (string) ($candidate['body'] ?? ''),
                'duration' => (string) ($candidate['duration'] ?? ''),
                'visibility' => (string) ($candidate['visibility'] ?? 'public'),
                'plan_name' => (string) ($candidatePlan['name'] ?? ''),
                'pack_items' => array_values((array) ($candidate['pack_items'] ?? [])),
            ];
        } else {
            $selectedLockedPayload = [
                'id' => (int) ($candidate['id'] ?? 0),
                'title' => (string) ($candidate['title'] ?? 'Conteúdo exclusivo'),
                'visibility' => (string) ($candidate['visibility'] ?? 'subscriber'),
                'plan_name' => (string) ($candidatePlan['name'] ?? ''),
                'message' => $lockedMessageForContent($candidate),
            ];
        }
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= e($creatorHandle) ?> - SexyLua</title>
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

<main class="pt-20">
    <div class="relative z-0 h-[210px] overflow-hidden bg-surface-container-high md:h-[240px] lg:h-[260px]">
        <?php if ($cover !== ''): ?>
            <?php if ($coverIsVideo): ?>
                <video autoplay class="h-full w-full object-cover" loop muted playsinline src="<?= e($cover) ?>"></video>
            <?php else: ?>
                <img alt="<?= e($creatorHandle) ?>" class="h-full w-full object-cover" src="<?= e($cover) ?>">
            <?php endif; ?>
        <?php else: ?>
            <div class="signature-glow flex h-full w-full items-center justify-center">
                <span class="headline text-5xl font-extrabold text-white"><?= e($creatorHandle) ?></span>
            </div>
        <?php endif; ?>
        <div class="absolute inset-0 z-10 bg-gradient-to-t from-background via-background/20 to-transparent"></div>
    </div>

    <div class="relative z-20 mx-auto max-w-7xl px-8">
        <div class="-mt-4 mb-16 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex flex-col items-center gap-5 text-center md:flex-row md:items-center md:text-left">
                <div class="relative z-30 rounded-full bg-white p-1 shadow-xl">
                    <?php if ($avatar !== ''): ?>
                        <img alt="<?= e($creatorHandle) ?>" class="h-44 w-44 rounded-full object-cover" src="<?= e($avatar) ?>">
                    <?php else: ?>
                        <div class="signature-glow flex h-44 w-44 items-center justify-center rounded-full text-4xl font-extrabold text-white"><?= e($creatorAvatarLabel) ?></div>
                    <?php endif; ?>
                </div>
                <div class="space-y-1 pt-1 md:pt-0">
                    <h1 class="headline text-5xl font-extrabold tracking-tight"><?= e($creatorHandle) ?></h1>
                    <p class="text-lg text-slate-500">Perfil publico do criador</p>
                    <p class="max-w-2xl text-slate-700"><?= e((string) ($creator['headline'] ?? 'Perfil criativo na SexyLua.')) ?></p>
                </div>
            </div>

            <div class="flex flex-wrap justify-center gap-3 pb-4 lg:justify-end">
                <?php if ($canInteractAsSubscriber): ?>
                    <form action="/subscriber/favorites/toggle" method="post">
                        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                        <input name="creator_id" type="hidden" value="<?= e((string) $creatorId) ?>">
                        <input name="redirect" type="hidden" value="<?= e($profileUrl) ?>">
                        <button class="inline-flex h-12 w-12 items-center justify-center rounded-full border <?= $isFavorite ? 'border-amber-300 bg-amber-100 text-amber-500' : 'border-slate-200 bg-white text-slate-400' ?> shadow-sm transition hover:scale-105" title="<?= e($isFavorite ? 'Remover favorito' : 'Favoritar') ?>" type="submit">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $isFavorite ? '1' : '0' ?>, 'wght' 500, 'GRAD' 0, 'opsz' 24;">star</span>
                        </button>
                    </form>
                    <form action="/profile/message" method="post">
                        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                        <input name="creator_id" type="hidden" value="<?= e((string) $creatorId) ?>">
                        <input name="body" type="hidden" value="Oi! Gostaria de conversar com voce.">
                        <button class="rounded-full bg-slate-900 px-6 py-3 text-sm font-bold text-white" type="submit">Enviar mensagem</button>
                    </form>
                    <?php if ($primaryPlan && ! $isSubscribed): ?>
                        <form action="/profile/subscribe" method="post">
                            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                            <input name="plan_id" type="hidden" value="<?= e((string) ((int) ($primaryPlan['id'] ?? 0))) ?>">
                            <button class="signature-glow rounded-full px-8 py-3 text-sm font-bold text-white shadow-lg" type="submit">Assinar agora</button>
                        </form>
                    <?php elseif ($isSubscribed): ?>
                        <span class="rounded-full bg-emerald-50 px-6 py-3 text-sm font-bold text-emerald-700">Assinatura ativa</span>
                    <?php endif; ?>
                <?php elseif (! $currentUser): ?>
                    <a class="signature-glow rounded-full px-8 py-3 text-sm font-bold text-white shadow-lg" href="/login">Entrar para assinar</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-16 grid grid-cols-1 gap-8 lg:grid-cols-12">
            <section class="space-y-8 lg:col-span-8">
                <div class="rounded-[2rem] bg-white p-8 shadow-sm">
                    <div class="mb-6 flex items-center gap-3">
                        <span class="material-symbols-outlined text-[#ab1155]">nightlight</span>
                        <h2 class="headline text-2xl font-extrabold">Sobre este perfil</h2>
                    </div>
                    <p class="text-lg leading-relaxed text-slate-600"><?= e((string) ($creator['bio'] ?? $creator['headline'] ?? 'Este criador ainda nao publicou uma bio completa.')) ?></p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <span class="rounded-full bg-[#f5f3f5] px-4 py-2 text-sm font-semibold text-slate-600">Mood: <?= e((string) ($creator['mood'] ?? 'Lunar')) ?></span>
                        <span class="rounded-full bg-[#f5f3f5] px-4 py-2 text-sm font-semibold text-slate-600"><?= e(number_format((int) ($creator['content_count'] ?? 0), 0, ',', '.')) ?> conteudos</span>
                        <span class="rounded-full bg-[#f5f3f5] px-4 py-2 text-sm font-semibold text-slate-600"><?= e(number_format((int) ($creator['subscriber_count'] ?? 0), 0, ',', '.')) ?> assinantes</span>
                    </div>
                </div>

                <div class="rounded-[2rem] bg-white p-8 shadow-sm">
                    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-[#ab1155]">event_upcoming</span>
                                <h2 class="headline text-2xl font-extrabold">Agenda de Lives</h2>
                            </div>
                            <p class="mt-2 text-sm text-slate-500">Acompanhe as próximas lives deste perfil e se programe para entrar na sala na hora certa.</p>
                        </div>
                        <a class="text-sm font-bold text-[#ab1155] underline" href="/explore">Ver mais lives</a>
                    </div>

                    <?php if ($upcomingLives !== []): ?>
                        <div class="-mx-2 flex snap-x gap-4 overflow-x-auto px-2 pb-2">
                            <?php foreach ($upcomingLives as $live): ?>
                                <?php
                                $liveCover = media_url((string) ($live['cover_url'] ?? ''));
                                $liveCoverIsVideo = media_is_video($liveCover);
                                $liveUrl = path_with_query('/live', ['id' => (int) ($live['id'] ?? 0)]);
                                ?>
                                <a class="min-w-[260px] max-w-[260px] snap-start overflow-hidden rounded-3xl bg-[#fbf9fb] ring-1 ring-[#f0e8ee] transition-transform hover:-translate-y-1" href="<?= e($liveUrl) ?>">
                                    <div class="relative aspect-[4/3] bg-slate-900">
                                        <?php if ($liveCover !== ''): ?>
                                            <?php if ($liveCoverIsVideo): ?>
                                                <video autoplay class="h-full w-full scale-105 object-cover <?= $guestPreviewLocked ? 'scale-110 blur-[22px] brightness-75' : '' ?>" loop muted playsinline src="<?= e($liveCover) ?>"></video>
                                            <?php else: ?>
                                                <img alt="<?= e((string) ($live['title'] ?? 'Live agendada')) ?>" class="h-full w-full scale-105 object-cover <?= $guestPreviewLocked ? 'scale-110 blur-[22px] brightness-75' : '' ?>" src="<?= e($liveCover) ?>">
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="signature-glow flex h-full w-full items-center justify-center text-white">
                                                <span class="headline px-6 text-center text-xl font-extrabold"><?= e((string) ($live['title'] ?? 'Live')) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($guestPreviewLocked): ?>
                                            <div class="absolute inset-0 bg-slate-950/35 backdrop-blur-[2px]"></div>
                                            <div class="absolute inset-x-4 top-4 rounded-full bg-white/90 px-4 py-2 text-center text-[10px] font-bold uppercase tracking-[0.25em] text-[#ab1155]">
                                                Entre para liberar
                                            </div>
                                        <?php endif; ?>
                                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-4 text-white">
                                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-white/75">Próxima live</p>
                                            <p class="mt-2 text-sm font-bold"><?= e(format_datetime((string) ($live['scheduled_for'] ?? ''), 'd/m H:i')) ?></p>
                                        </div>
                                    </div>
                                    <div class="space-y-2 p-4">
                                        <p class="headline text-lg font-extrabold"><?= e((string) ($live['title'] ?? 'Live agendada')) ?></p>
                                        <p class="text-sm text-slate-500"><?= e(excerpt((string) ($live['description'] ?? ''), 90)) ?></p>
                                        <div class="flex items-center justify-between gap-3 text-[11px] font-bold uppercase tracking-[0.22em] text-slate-400">
                                            <span><?= e((string) ($live['category_label'] ?? 'Todos')) ?></span>
                                            <span><?= e(match ((string) ($live['access_mode'] ?? 'public')) {
                                                'subscriber' => 'Assinantes',
                                                'vip' => 'Live VIP',
                                                default => 'Público',
                                            }) ?></span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="rounded-3xl bg-[#fbf9fb] p-6 text-sm text-slate-500 ring-1 ring-[#f0e8ee]">Nenhuma live agendada no momento.</div>
                    <?php endif; ?>
                </div>

                <div class="rounded-[2rem] bg-white p-8 shadow-sm">
                    <div class="mb-8 flex items-end justify-between gap-6">
                        <div>
                            <h2 class="headline text-2xl font-extrabold">Conteudos publicados</h2>
                            <p class="mt-2 text-sm text-slate-500">Escolha entre os conteúdos publicados neste perfil e abra o que mais combinar com você.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <?php foreach (array_slice($content, 0, 6) as $item): ?>
                            <?php
                            $itemId = (int) ($item['id'] ?? 0);
                            $kind = (string) ($item['kind'] ?? 'gallery');
                            $itemPlan = is_array($item['plan'] ?? null) ? $item['plan'] : null;
                            $itemAccessible = $canAccessContent($item) && ! $guestPreviewLocked;
                            $contentPayload = null;
                            $lockedPayload = null;

                            if ($itemAccessible) {
                                $thumbnail = media_url((string) ($item['thumbnail_url'] ?? $item['media_url'] ?? ''));
                                $media = media_url((string) ($item['media_url'] ?? ''));
                                $contentPayload = [
                                    'id' => $itemId,
                                    'title' => (string) ($item['title'] ?? 'Conteúdo'),
                                    'kind' => $kind,
                                    'thumbnail_url' => $thumbnail,
                                    'media_url' => $media,
                                    'excerpt' => (string) ($item['excerpt'] ?? ''),
                                    'body' => (string) ($item['body'] ?? ''),
                                    'duration' => (string) ($item['duration'] ?? ''),
                                    'visibility' => (string) ($item['visibility'] ?? 'public'),
                                    'plan_name' => (string) ($itemPlan['name'] ?? ''),
                                    'pack_items' => array_values((array) ($item['pack_items'] ?? [])),
                                ];
                            } else {
                                $lockedPayload = [
                                    'id' => $itemId,
                                    'title' => (string) ($item['title'] ?? 'Conteúdo exclusivo'),
                                    'visibility' => (string) ($item['visibility'] ?? 'subscriber'),
                                    'plan_name' => (string) ($itemPlan['name'] ?? ''),
                                    'message' => $lockedMessageForContent($item),
                                ];
                            }
                            ?>
                            <button
                                class="overflow-hidden rounded-3xl bg-[#fbf9fb] text-left shadow-sm ring-1 ring-[#f0e8ee] transition-transform hover:-translate-y-1"
                                <?php if ($itemAccessible && $contentPayload !== null): ?>
                                    data-profile-content="<?= e((string) json_encode($contentPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>"
                                <?php elseif ($lockedPayload !== null): ?>
                                    data-profile-locked="<?= e((string) json_encode($lockedPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>"
                                <?php endif; ?>
                                id="content-<?= e((string) $itemId) ?>"
                                type="button"
                            >
                                <div class="relative aspect-[4/3] bg-slate-900">
                                    <?php if ($itemAccessible && !empty($thumbnail ?? '')): ?>
                                        <img alt="<?= e((string) ($item['title'] ?? 'Conteúdo')) ?>" class="h-full w-full object-cover" src="<?= e($thumbnail) ?>">
                                    <?php elseif (!empty($thumbnail ?? '')): ?>
                                        <img alt="<?= e((string) ($item['title'] ?? 'Conteúdo')) ?>" class="h-full w-full object-cover scale-105 blur-[16px] brightness-90" src="<?= e($thumbnail) ?>">
                                    <?php else: ?>
                                        <div class="signature-glow flex h-full w-full items-center justify-center p-6 text-center text-white">
                                            <div class="<?= $itemAccessible ? '' : 'blur-md' ?>">
                                                <span class="headline text-2xl font-extrabold"><?= e((string) strtoupper((string) ($item['kind'] ?? 'conteúdo'))) ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($itemAccessible && ($kind === 'video' || $kind === 'live_teaser')): ?>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-black/55 text-white shadow-lg">
                                                <span class="material-symbols-outlined text-4xl">play_arrow</span>
                                            </span>
                                        </div>
                                    <?php elseif (! $itemAccessible): ?>
                                        <div class="absolute inset-0">
                                            <div class="absolute inset-4 rounded-[1.75rem] bg-white/15 blur-xl"></div>
                                            <div class="absolute inset-0 bg-slate-950/25 backdrop-blur-sm"></div>
                                            <div class="absolute inset-0 flex flex-col justify-between p-5 text-white">
                                                <div class="flex items-start justify-between gap-3">
                                                    <span class="rounded-full bg-white/15 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.25em]"><?= e((string) ($item['visibility'] ?? 'subscriber')) ?></span>
                                                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-white/15">
                                                        <span class="material-symbols-outlined">lock</span>
                                                    </span>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold uppercase tracking-[0.25em] text-white/80">Conteúdo bloqueado</p>
                                                    <p class="mt-2 text-sm text-white/85"><?= e($guestPreviewLocked ? 'Entre para visualizar este material.' : 'Assine para liberar este material.') ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="space-y-3 p-5">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="headline truncate text-xl font-extrabold"><?= e((string) ($item['title'] ?? 'Conteúdo')) ?></p>
                                        <span class="rounded-full bg-[#f8e8ef] px-3 py-1 text-[10px] font-bold uppercase tracking-[0.25em] text-[#ab1155]"><?= e((string) ($item['visibility'] ?? 'public')) ?></span>
                                    </div>
                                    <p class="line-clamp-3 min-h-[4rem] text-sm text-slate-500">
                                        <?= e($itemAccessible ? excerpt((string) ($item['excerpt'] ?? ''), 140) : $lockedMessageForContent($item)) ?>
                                    </p>
                                    <div class="flex flex-wrap items-center justify-between gap-2 text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">
                                        <span><?= e((string) ($item['kind'] ?? 'conteúdo')) ?></span>
                                        <?php if ($itemPlan): ?>
                                            <span class="rounded-full bg-white px-3 py-1 text-[10px] text-[#ab1155]"><?= e((string) ($itemPlan['name'] ?? 'Plano')) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </button>
                        <?php endforeach; ?>
                        <?php if ($content === []): ?>
                            <div class="col-span-full rounded-3xl bg-[#fbf9fb] p-8 text-sm text-slate-500 shadow-sm ring-1 ring-[#f0e8ee]">
                                Este criador ainda nao publicou conteudos visiveis no catalogo publico.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <aside class="space-y-8 lg:col-span-4">
                <section class="rounded-[2rem] bg-white p-8 shadow-sm">
                    <h2 class="headline text-2xl font-extrabold">Assinaturas VIP</h2>
                    <div class="mt-6 space-y-4">
                        <?php foreach ($plans as $plan): ?>
                            <?php $perks = array_values(array_filter(array_map('strval', (array) ($plan['perks'] ?? [])))); ?>
                            <div class="rounded-3xl bg-[#fbf9fb] p-5 shadow-sm ring-1 ring-[#f0e8ee]">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="headline text-xl font-extrabold"><?= e((string) ($plan['name'] ?? 'Plano')) ?></p>
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.25em] text-emerald-700">Ativo</span>
                                </div>
                                <div class="mt-3 text-sm font-semibold text-slate-600"><?= luacoin_amount_html((int) ($plan['price_tokens'] ?? 0), 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-4 w-4 shrink-0') ?></div>
                                <p class="mt-3 text-sm text-slate-500"><?= e((string) ($plan['description'] ?? 'Acesso recorrente ao universo deste criador.')) ?></p>
                                <?php if ($perks !== []): ?>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <?php foreach ($perks as $perk): ?>
                                            <span class="rounded-full bg-white px-3 py-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500"><?= e($perk) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($plans === []): ?>
                            <div class="rounded-3xl bg-[#fbf9fb] p-6 text-sm text-slate-500 shadow-sm ring-1 ring-[#f0e8ee]">
                                Este criador ainda nao tem planos ativos para assinatura publica.
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="rounded-[2rem] bg-[#ab1155] p-8 text-white shadow-xl">
                    <h2 class="headline text-2xl font-extrabold">Resumo lunar</h2>
                    <p class="mt-3 text-white/80">Um resumo rapido do momento atual do perfil.</p>
                    <div class="mt-6 grid grid-cols-2 gap-4">
                        <div class="rounded-3xl bg-white/10 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-white/70">Assinantes</p>
                            <p class="mt-2 text-2xl font-extrabold"><?= e(number_format((int) ($creator['subscriber_count'] ?? 0), 0, ',', '.')) ?></p>
                        </div>
                        <div class="rounded-3xl bg-white/10 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-white/70">Conteudos</p>
                            <p class="mt-2 text-2xl font-extrabold"><?= e(number_format((int) ($creator['content_count'] ?? 0), 0, ',', '.')) ?></p>
                        </div>
                    </div>
                </section>

                <section class="rounded-[2rem] bg-white p-8 shadow-sm">
                    <h2 class="headline text-2xl font-extrabold">Criadores relacionados</h2>
                    <div class="mt-6 space-y-4">
                        <?php foreach (array_slice($relatedCreators, 0, 4) as $related): ?>
                            <?php $relatedAvatar = media_url((string) ($related['avatar_url'] ?? '')); ?>
                                <a class="flex items-center gap-4 rounded-3xl bg-[#fbf9fb] p-4 shadow-sm ring-1 ring-[#f0e8ee]" href="<?= e(creator_public_url($related)) ?>">
                                <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-full bg-[#f7edf2]">
                                    <?php if ($relatedAvatar !== ''): ?>
                                        <img alt="<?= e(user_handle($related, 'criador')) ?>" class="h-full w-full object-cover" src="<?= e($relatedAvatar) ?>">
                                    <?php else: ?>
                                        <span class="headline text-lg font-extrabold text-[#ab1155]"><?= e(user_avatar_label($related, 'CR')) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="min-w-0">
                                    <p class="headline truncate text-lg font-extrabold"><?= e(user_handle($related, 'criador')) ?></p>
                                    <p class="truncate text-sm text-slate-500"><?= e((string) ($related['headline'] ?? 'Perfil criativo na SexyLua.')) ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                        <?php if ($relatedCreators === []): ?>
                            <div class="rounded-3xl bg-[#fbf9fb] p-6 text-sm text-slate-500 shadow-sm ring-1 ring-[#f0e8ee]">
                                Ainda nao ha outros criadores para recomendar.
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</main>

<div class="hidden fixed inset-0 z-[80] flex items-center justify-center bg-black/70 px-4 py-8" data-profile-content-modal>
    <div class="w-full max-w-5xl overflow-hidden rounded-[2rem] bg-white shadow-[0px_30px_80px_rgba(27,28,29,0.24)]">
        <div class="flex items-center justify-between border-b border-[#f0e8ee] px-6 py-5">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]" data-profile-modal-kind>Conteudo</p>
                <h3 class="headline mt-2 truncate text-2xl font-extrabold" data-profile-modal-title>Conteudo</h3>
            </div>
            <button class="flex h-11 w-11 items-center justify-center rounded-full bg-[#f5f3f5] text-slate-500" data-profile-content-close type="button">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="grid grid-cols-1 gap-0 lg:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">
            <div class="bg-slate-950">
                <div class="hidden aspect-video h-full w-full" data-profile-modal-video-wrap>
                    <video class="h-full w-full bg-black object-contain" controls data-profile-modal-video playsinline></video>
                </div>
                <div class="hidden aspect-video h-full w-full" data-profile-modal-audio-wrap>
                    <div class="flex h-full min-h-[320px] items-center justify-center p-8">
                        <audio class="w-full" controls data-profile-modal-audio></audio>
                    </div>
                </div>
                <div class="hidden aspect-video h-full w-full" data-profile-modal-image-wrap>
                    <img alt="" class="h-full w-full object-contain" data-profile-modal-image src="">
                </div>
                <div class="hidden h-full min-h-[320px] w-full bg-slate-950 p-4" data-profile-modal-pack-wrap>
                    <div class="grid h-full grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_220px]">
                        <div class="flex min-h-[320px] items-center justify-center rounded-[1.75rem] bg-black/35 p-4" data-profile-pack-stage></div>
                        <div class="overflow-hidden rounded-[1.75rem] bg-white/5">
                            <div class="border-b border-white/10 px-4 py-3 text-xs font-bold uppercase tracking-[0.25em] text-white/70">Itens do pack</div>
                            <div class="max-h-[420px] space-y-2 overflow-y-auto p-3" data-profile-pack-index></div>
                        </div>
                    </div>
                </div>
                <div class="hidden flex h-full min-h-[320px] items-start justify-center bg-white p-8" data-profile-modal-article-wrap>
                    <article class="prose max-w-none text-slate-700">
                        <p data-profile-modal-article></p>
                    </article>
                </div>
            </div>
            <div class="space-y-5 p-6">
                <div class="rounded-3xl bg-[#fbf9fb] p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Resumo</p>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600" data-profile-modal-excerpt></p>
                </div>
                <div class="rounded-3xl bg-[#fbf9fb] p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Detalhes</p>
                    <div class="mt-4 grid grid-cols-1 gap-3">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-slate-500">Tipo</span>
                            <span class="font-bold text-slate-700" data-profile-modal-kind-label></span>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-slate-500">Duracao</span>
                            <span class="font-bold text-slate-700" data-profile-modal-duration></span>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-slate-500">Acesso</span>
                            <span class="font-bold text-slate-700" data-profile-modal-visibility></span>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-slate-500">Plano</span>
                            <span class="font-bold text-slate-700" data-profile-modal-plan></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="hidden fixed inset-0 z-[85] flex items-center justify-center bg-black/70 px-4 py-8" data-profile-locked-modal>
    <div class="w-full max-w-xl overflow-hidden rounded-[2rem] bg-white shadow-[0px_30px_80px_rgba(27,28,29,0.24)]">
        <div class="flex items-center justify-between border-b border-[#f0e8ee] px-6 py-5">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#D81B60]">Conteúdo exclusivo</p>
                <h3 class="headline mt-2 truncate text-2xl font-extrabold" data-profile-locked-title>Conteúdo bloqueado</h3>
            </div>
            <button class="flex h-11 w-11 items-center justify-center rounded-full bg-[#f5f3f5] text-slate-500" data-profile-locked-close type="button">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="space-y-6 p-6">
            <div class="rounded-[1.75rem] bg-[#fbf9fb] p-6">
                <div class="flex items-start gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[#f8e8ef] text-[#ab1155]">
                        <span class="material-symbols-outlined text-3xl">workspace_premium</span>
                    </span>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.22em] text-slate-400" data-profile-locked-visibility>Assinantes</p>
                        <p class="mt-2 text-base leading-relaxed text-slate-600" data-profile-locked-message>Assine para liberar este conteúdo.</p>
                        <p class="mt-3 text-sm font-semibold text-[#ab1155]" data-profile-locked-plan></p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <?php if (! $currentUser): ?>
                    <a class="rounded-full bg-slate-900 px-6 py-4 text-center text-sm font-bold text-white" href="/login">Entrar para assinar</a>
                <?php elseif ($canInteractAsSubscriber && $primaryPlan && ! $isSubscribed): ?>
                    <form action="/profile/subscribe" method="post">
                        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                        <input name="plan_id" type="hidden" value="<?= e((string) ((int) ($primaryPlan['id'] ?? 0))) ?>">
                        <button class="signature-glow rounded-full px-6 py-4 text-sm font-bold text-white" type="submit">Adquirir plano</button>
                    </form>
                <?php elseif ($isSubscribed): ?>
                    <span class="rounded-full bg-emerald-50 px-6 py-4 text-center text-sm font-bold text-emerald-700">Seu plano já está ativo</span>
                <?php else: ?>
                    <span class="rounded-full bg-slate-900 px-6 py-4 text-center text-sm font-bold text-white">Acesse com uma conta de assinante</span>
                <?php endif; ?>
                <button class="rounded-full bg-[#f5f3f5] px-6 py-4 text-sm font-bold text-slate-600" data-profile-locked-close type="button">Fechar</button>
            </div>
        </div>
    </div>
</div>

<footer class="flex w-full flex-col items-center gap-6 bg-[#D81B60] px-10 py-12 text-white">
    <?= brand_logo_white('h-8 w-auto') ?>
    <div class="flex flex-wrap justify-center gap-8">
        <a class="text-xs uppercase tracking-widest text-white/70 transition-all duration-300 hover:text-white" href="/terms">Termos</a>
        <a class="text-xs uppercase tracking-widest text-white/70 transition-all duration-300 hover:text-white" href="/privacy">Privacidade</a>
        <a class="text-xs uppercase tracking-widest text-white/70 transition-all duration-300 hover:text-white" href="/help">Ajuda</a>
        <a class="text-xs uppercase tracking-widest text-white/70 transition-all duration-300 hover:text-white" href="/terms">Termos</a>
    </div>
    <p class="text-[10px] uppercase tracking-[0.2em] text-white/80">&copy; 2026 SexyLua. Perfis, conteúdos e experiências exclusivas em um só lugar.</p>
</footer>

<?php if ($selectedContentPayload !== null): ?>
    <script id="profile-selected-content" type="application/json"><?= e((string) json_encode($selectedContentPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></script>
<?php endif; ?>
<?php if ($selectedLockedPayload !== null): ?>
    <script id="profile-selected-locked-content" type="application/json"><?= e((string) json_encode($selectedLockedPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></script>
<?php endif; ?>
<script>
    (() => {
        const modal = document.querySelector('[data-profile-content-modal]');
        const lockedModal = document.querySelector('[data-profile-locked-modal]');
        if (!modal || !lockedModal) return;

        const titleNode = modal.querySelector('[data-profile-modal-title]');
        const kindNode = modal.querySelector('[data-profile-modal-kind]');
        const kindLabelNode = modal.querySelector('[data-profile-modal-kind-label]');
        const excerptNode = modal.querySelector('[data-profile-modal-excerpt]');
        const durationNode = modal.querySelector('[data-profile-modal-duration]');
        const visibilityNode = modal.querySelector('[data-profile-modal-visibility]');
        const planNode = modal.querySelector('[data-profile-modal-plan]');
        const videoWrap = modal.querySelector('[data-profile-modal-video-wrap]');
        const video = modal.querySelector('[data-profile-modal-video]');
        const audioWrap = modal.querySelector('[data-profile-modal-audio-wrap]');
        const audio = modal.querySelector('[data-profile-modal-audio]');
        const imageWrap = modal.querySelector('[data-profile-modal-image-wrap]');
        const image = modal.querySelector('[data-profile-modal-image]');
        const packWrap = modal.querySelector('[data-profile-modal-pack-wrap]');
        const packStage = modal.querySelector('[data-profile-pack-stage]');
        const packIndex = modal.querySelector('[data-profile-pack-index]');
        const articleWrap = modal.querySelector('[data-profile-modal-article-wrap]');
        const article = modal.querySelector('[data-profile-modal-article]');
        const lockedTitleNode = lockedModal.querySelector('[data-profile-locked-title]');
        const lockedVisibilityNode = lockedModal.querySelector('[data-profile-locked-visibility]');
        const lockedMessageNode = lockedModal.querySelector('[data-profile-locked-message]');
        const lockedPlanNode = lockedModal.querySelector('[data-profile-locked-plan]');
        const labels = {
            gallery: 'Galeria',
            video: 'Video',
            audio: 'Audio',
            article: 'Artigo',
            live_teaser: 'Live',
            pack: 'Pack',
        };
        const visibilityLabels = {
            public: 'Publico',
            subscriber: 'Assinantes',
            premium: 'Plano vinculado',
        };

        const resetMedia = () => {
            [videoWrap, audioWrap, imageWrap, packWrap, articleWrap].forEach((node) => node.classList.add('hidden'));
            video.pause();
            video.removeAttribute('src');
            video.load();
            audio.pause();
            audio.removeAttribute('src');
            audio.load();
            image.setAttribute('src', '');
            if (packStage) {
                packStage.innerHTML = '';
            }
            if (packIndex) {
                packIndex.innerHTML = '';
            }
            article.textContent = '';
        };

        const renderPackStage = (item) => {
            if (!packStage) return;
            packStage.innerHTML = '';
            if (!item) return;

            if (item.kind === 'video') {
                const videoNode = document.createElement('video');
                videoNode.className = 'h-full max-h-[520px] w-full rounded-[1.5rem] bg-black object-contain';
                videoNode.controls = true;
                videoNode.playsInline = true;
                videoNode.src = item.url || item.thumbnail_url || '';
                packStage.appendChild(videoNode);
                videoNode.play().catch(() => {});
                return;
            }

            const imageNode = document.createElement('img');
            imageNode.className = 'h-full max-h-[520px] w-full rounded-[1.5rem] object-contain';
            imageNode.alt = item.title || 'Item do pack';
            imageNode.src = item.url || item.thumbnail_url || '';
            packStage.appendChild(imageNode);
        };

        const renderPack = (items) => {
            if (!packWrap || !packStage || !packIndex) return;
            const normalizedItems = Array.isArray(items) ? items.filter(Boolean) : [];
            packWrap.classList.remove('hidden');
            packIndex.innerHTML = '';
            renderPackStage(normalizedItems[0] || null);

            normalizedItems.forEach((item, index) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'flex w-full items-center gap-3 rounded-2xl bg-white/8 px-3 py-3 text-left text-white transition hover:bg-white/12';

                const thumbWrap = document.createElement('div');
                thumbWrap.className = 'flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white/10';

                if (item.kind === 'video') {
                    const icon = document.createElement('span');
                    icon.className = 'material-symbols-outlined text-2xl';
                    icon.textContent = 'play_circle';
                    thumbWrap.appendChild(icon);
                } else if (item.thumbnail_url || item.url) {
                    const img = document.createElement('img');
                    img.className = 'h-full w-full object-cover';
                    img.alt = item.title || `Item ${index + 1}`;
                    img.src = item.thumbnail_url || item.url || '';
                    thumbWrap.appendChild(img);
                }

                const textWrap = document.createElement('div');
                textWrap.className = 'min-w-0';
                const title = document.createElement('p');
                title.className = 'truncate text-sm font-bold';
                title.textContent = item.title || `Item ${index + 1}`;
                const meta = document.createElement('p');
                meta.className = 'mt-1 text-[10px] font-bold uppercase tracking-[0.2em] text-white/60';
                meta.textContent = item.kind === 'video' ? 'Video' : 'Imagem';
                textWrap.appendChild(title);
                textWrap.appendChild(meta);

                button.appendChild(thumbWrap);
                button.appendChild(textWrap);
                button.addEventListener('click', () => renderPackStage(item));
                packIndex.appendChild(button);
            });
        };

        const closeLockedModal = () => {
            lockedModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            const nextUrl = new URL(window.location.href);
            nextUrl.searchParams.delete('content');
            window.history.replaceState({}, '', nextUrl.toString());
        };

        const openModal = (payload) => {
            closeLockedModal();
            resetMedia();
            titleNode.textContent = payload.title || 'Conteudo';
            kindNode.textContent = labels[payload.kind] || 'Conteudo';
            kindLabelNode.textContent = labels[payload.kind] || 'Conteudo';
            excerptNode.textContent = payload.excerpt || payload.body || 'Sem descricao adicional.';
            durationNode.textContent = payload.duration || 'Sem duracao';
            visibilityNode.textContent = visibilityLabels[payload.visibility] || 'Publico';
            planNode.textContent = payload.plan_name || 'Sem plano';

            if (payload.kind === 'video' || payload.kind === 'live_teaser') {
                videoWrap.classList.remove('hidden');
                video.src = payload.media_url || payload.thumbnail_url || '';
                video.play().catch(() => {});
            } else if (payload.kind === 'pack') {
                renderPack(payload.pack_items || []);
            } else if (payload.kind === 'audio') {
                audioWrap.classList.remove('hidden');
                audio.src = payload.media_url || '';
                audio.play().catch(() => {});
            } else if (payload.kind === 'article') {
                articleWrap.classList.remove('hidden');
                article.textContent = payload.body || payload.excerpt || 'Sem texto para este artigo.';
            } else {
                imageWrap.classList.remove('hidden');
                image.src = payload.media_url || payload.thumbnail_url || '';
            }

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            const nextUrl = new URL(window.location.href);
            nextUrl.searchParams.set('content', String(payload.id || ''));
            window.history.replaceState({}, '', nextUrl.toString());
        };

        const closeModal = () => {
            resetMedia();
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            const nextUrl = new URL(window.location.href);
            nextUrl.searchParams.delete('content');
            window.history.replaceState({}, '', nextUrl.toString());
        };

        const openLockedModal = (payload) => {
            closeModal();
            lockedTitleNode.textContent = payload.title || 'Conteúdo exclusivo';
            lockedVisibilityNode.textContent = visibilityLabels[payload.visibility] || 'Assinantes';
            lockedMessageNode.textContent = payload.message || 'Adquira um plano para visualizar este conteúdo.';
            lockedPlanNode.textContent = payload.plan_name ? `Plano sugerido: ${payload.plan_name}` : '';
            lockedModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            const nextUrl = new URL(window.location.href);
            nextUrl.searchParams.set('content', String(payload.id || ''));
            window.history.replaceState({}, '', nextUrl.toString());
        };

        document.querySelectorAll('[data-profile-content]').forEach((button) => {
            button.addEventListener('click', () => {
                try {
                    openModal(JSON.parse(button.getAttribute('data-profile-content') || '{}'));
                } catch (error) {
                }
            });
        });

        document.querySelectorAll('[data-profile-locked]').forEach((button) => {
            button.addEventListener('click', () => {
                try {
                    openLockedModal(JSON.parse(button.getAttribute('data-profile-locked') || '{}'));
                } catch (error) {
                }
            });
        });

        modal.querySelectorAll('[data-profile-content-close]').forEach((button) => button.addEventListener('click', closeModal));
        lockedModal.querySelectorAll('[data-profile-locked-close]').forEach((button) => button.addEventListener('click', closeLockedModal));
        modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });
        lockedModal.addEventListener('click', (event) => { if (event.target === lockedModal) closeLockedModal(); });
        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            if (!modal.classList.contains('hidden')) {
                closeModal();
            }

            if (!lockedModal.classList.contains('hidden')) {
                closeLockedModal();
            }
        });

        const selectedNode = document.getElementById('profile-selected-content');
        if (selectedNode) {
            try {
                openModal(JSON.parse(selectedNode.textContent || '{}'));
            } catch (error) {
            }
        }

        const selectedLockedNode = document.getElementById('profile-selected-locked-content');
        if (selectedLockedNode) {
            try {
                openLockedModal(JSON.parse(selectedLockedNode.textContent || '{}'));
            } catch (error) {
            }
        }
    })();
</script>
</body>
</html>
