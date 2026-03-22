<?php

declare(strict_types=1);

$creator = $data['creator'] ?? [];
$wallet = $data['wallet'] ?? [];
$platform = $data['platform'] ?? [];
$activeLive = $data['active_live'] ?? null;
$nextLive = $data['next_live'] ?? null;
$activeSubscribers = (int) ($data['active_subscribers'] ?? 0);
$security = $data['security'] ?? [];
$moods = ['Lua Nova', 'Lua Crescente', 'Lua Cheia', 'Lua Minguante', 'Aurora Rubi', 'Meia Noite', 'Eclipse Rosa'];
$coverStyles = ['rose-dawn', 'amber-night', 'violet-haze', 'solar-flare', 'midnight-ruby', 'rose-lounge', 'noir-silk'];
$payoutMethods = [
    'pix' => 'PIX',
    'bank_transfer' => 'Transferencia',
    'paypal' => 'PayPal',
];
$avatarUrl = media_url((string) ($creator['avatar_url'] ?? ''));
$coverUrl = media_url((string) ($creator['cover_url'] ?? ''));
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Configuracoes do Criador</title>
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
        <a class="mx-2 flex items-center gap-3 rounded-full px-4 py-3 text-zinc-600 transition-colors hover:bg-zinc-100" href="/creator/favorites">
            <span class="material-symbols-outlined">favorite</span>
            <span>Favoritos</span>
        </a>
        <a class="mx-2 flex items-center gap-3 rounded-full px-4 py-3 text-zinc-600 transition-colors hover:bg-zinc-100" href="/creator/wallet">
            <span class="material-symbols-outlined">account_balance_wallet</span>
            <span>Carteira</span>
        </a>
        <a class="mx-2 flex items-center gap-3 rounded-full bg-pink-50 px-4 py-3 text-pink-700" href="/creator/settings">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">settings</span>
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
                <p class="text-xs text-pink-600">Configuracao ativa</p>
            </div>
        </div>
    </div>
</aside>

<header class="sticky top-0 z-40 flex h-16 items-center bg-[#D81B60] shadow-lg shadow-[#D81B60]/20 lg:pl-64">
    <div class="mx-auto flex w-full max-w-screen-2xl items-center justify-between px-8 font-['Plus_Jakarta_Sans'] text-sm font-bold tracking-wide text-white">
        <div class="flex items-center gap-8">
            <h1 class="text-2xl font-black lg:hidden">SexyLua</h1>
            <nav class="hidden items-center gap-6 md:flex">
                <a class="rounded-full px-3 py-1 text-white/80 transition-colors hover:bg-white/10" href="/creator">Metricas Lunares</a>
                <a class="rounded-full px-3 py-1 text-white/80 transition-colors hover:bg-white/10" href="/creator/live">Configurar Live</a>
                <a class="border-b-2 border-white py-1 text-white" href="/creator/settings">Configuracoes</a>
            </nav>
        </div>
        <div class="flex items-center gap-4">
            <a class="rounded-full px-3 py-1 text-white/80 transition-colors hover:bg-white/10" href="/creator/wallet">Carteira</a>
            <a class="rounded-full px-3 py-1 text-white/80 transition-colors hover:bg-white/10" href="/creator/favorites">Favoritos</a>
        </div>
    </div>
</header>

<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-7xl px-8 py-12">
        <header class="mb-10 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="mb-2 text-4xl font-extrabold tracking-tight">Configuracoes do Criador</h2>
                <p class="text-on-surface-variant">Edite sua apresentacao, dados de repasse, identidade do perfil e o acesso tecnico da sua conta.</p>
            </div>
            <div class="rounded-full bg-surface-container-lowest px-6 py-3 shadow-sm">
                <span class="text-sm font-bold text-primary"><?= e((string) $activeSubscribers) ?> assinantes ativos</span>
            </div>
        </header>

        <div class="grid grid-cols-1 gap-8 xl:grid-cols-[1.2fr_0.8fr]">
            <section class="rounded-2xl bg-surface-container-lowest p-8 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                <div class="mb-8">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Perfil publico</p>
                    <h3 class="mt-3 text-3xl font-extrabold">Dados principais</h3>
                </div>

                <form action="/creator/settings/update" class="space-y-8" method="post" enctype="multipart/form-data">
                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">

                    <div class="grid gap-6 md:grid-cols-2">
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Nome artistico</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="name" type="text" value="<?= e((string) ($creator['name'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Slug publico</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="slug" type="text" value="<?= e((string) ($creator['slug'] ?? '')) ?>">
                        </label>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Cidade</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="city" type="text" value="<?= e((string) ($creator['city'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Headline</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="headline" type="text" value="<?= e((string) ($creator['headline'] ?? '')) ?>">
                        </label>
                    </div>

                    <label class="block space-y-2">
                        <span class="text-sm font-semibold text-on-surface-variant">Bio</span>
                        <textarea class="min-h-[160px] w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="bio"><?= e((string) ($creator['bio'] ?? '')) ?></textarea>
                    </label>

                    <div class="grid gap-6 md:grid-cols-2">
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Fase do perfil</span>
                            <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="mood">
                                <?php foreach ($moods as $mood): ?>
                                    <option value="<?= e($mood) ?>" <?= (string) ($creator['mood'] ?? '') === $mood ? 'selected' : '' ?>><?= e($mood) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Estilo de capa</span>
                            <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="cover_style">
                                <?php foreach ($coverStyles as $coverStyle): ?>
                                    <option value="<?= e($coverStyle) ?>" <?= (string) ($creator['cover_style'] ?? '') === $coverStyle ? 'selected' : '' ?>><?= e(ucwords(str_replace('-', ' ', $coverStyle))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-4 rounded-3xl bg-surface-container-low p-5">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-on-surface-variant">Avatar atual</span>
                                <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-bold text-primary">Perfil</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <?php if ($avatarUrl !== ''): ?>
                                    <img alt="Avatar do criador" class="h-16 w-16 rounded-full object-cover" src="<?= e($avatarUrl) ?>">
                                <?php else: ?>
                                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-lg font-bold text-primary"><?= e(avatar_initials((string) ($creator['name'] ?? 'Criador'))) ?></div>
                                <?php endif; ?>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold"><?= e((string) ($creator['name'] ?? 'Criador')) ?></p>
                                    <p class="text-xs text-on-surface-variant">@<?= e((string) ($creator['slug'] ?? 'criador')) ?></p>
                                </div>
                            </div>
                            <label class="block space-y-2">
                                <span class="text-sm font-semibold text-on-surface-variant">URL do avatar</span>
                                <input class="w-full rounded-2xl border-none bg-white px-4 py-3 shadow-sm focus:ring-2 focus:ring-primary/20" name="avatar_url" type="text" value="<?= e((string) ($creator['avatar_url'] ?? '')) ?>">
                            </label>
                            <label class="block space-y-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Upload de avatar</span>
                                <input class="w-full rounded-2xl border-none bg-white px-4 py-3 shadow-sm focus:ring-2 focus:ring-primary/20" name="avatar_file" type="file" accept=".jpg,.jpeg,.png,.webp,.gif">
                            </label>
                        </div>

                        <div class="space-y-4 rounded-3xl bg-surface-container-low p-5">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-on-surface-variant">Capa atual</span>
                                <span class="rounded-full bg-zinc-900/5 px-3 py-1 text-xs font-bold text-zinc-700">Studio</span>
                            </div>
                            <?php if ($coverUrl !== ''): ?>
                                <img alt="Capa do criador" class="h-40 w-full rounded-3xl object-cover" src="<?= e($coverUrl) ?>">
                            <?php else: ?>
                                <div class="flex h-40 w-full items-center justify-center rounded-3xl bg-gradient-to-br from-pink-700 via-rose-600 to-orange-400 text-lg font-bold text-white">
                                    <?= e((string) ($creator['mood'] ?? 'Lua Nova')) ?>
                                </div>
                            <?php endif; ?>
                            <label class="block space-y-2">
                                <span class="text-sm font-semibold text-on-surface-variant">URL da capa</span>
                                <input class="w-full rounded-2xl border-none bg-white px-4 py-3 shadow-sm focus:ring-2 focus:ring-primary/20" name="cover_url" type="text" value="<?= e((string) ($creator['cover_url'] ?? '')) ?>">
                            </label>
                            <label class="block space-y-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Upload da capa</span>
                                <input class="w-full rounded-2xl border-none bg-white px-4 py-3 shadow-sm focus:ring-2 focus:ring-primary/20" name="cover_file" type="file" accept=".jpg,.jpeg,.png,.webp,.gif">
                            </label>
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Instagram</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="instagram" type="text" value="<?= e((string) ($creator['instagram'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-on-surface-variant">Telegram</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="telegram" type="text" value="<?= e((string) ($creator['telegram'] ?? '')) ?>">
                        </label>
                    </div>

                    <div class="rounded-3xl bg-surface-container-low p-6">
                        <div class="mb-5">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Financeiro</p>
                            <h4 class="mt-2 text-2xl font-extrabold">Recebimentos</h4>
                        </div>
                        <div class="grid gap-6 md:grid-cols-2">
                            <label class="block space-y-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Metodo de saque</span>
                                <select class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="payout_method">
                                    <?php foreach ($payoutMethods as $value => $label): ?>
                                        <option value="<?= e($value) ?>" <?= (string) ($creator['payout_method'] ?? 'pix') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label class="block space-y-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Chave de recebimento</span>
                                <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="payout_key" type="text" value="<?= e((string) ($creator['payout_key'] ?? '')) ?>">
                            </label>
                        </div>
                    </div>

                    <div class="rounded-3xl bg-surface-container-low p-6">
                        <div class="mb-5">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Seguranca</p>
                            <h4 class="mt-2 text-2xl font-extrabold">Acesso tecnico</h4>
                        </div>
                        <div class="grid gap-6 md:grid-cols-2">
                            <label class="block space-y-2 md:col-span-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Stream key</span>
                                <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="stream_key" type="text" value="<?= e((string) ($creator['stream_key'] ?? '')) ?>">
                            </label>
                            <label class="block space-y-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Nova senha</span>
                                <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="new_password" type="password">
                            </label>
                            <label class="block space-y-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Confirmar nova senha</span>
                                <input class="w-full rounded-2xl border-none bg-white px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="new_password_confirmation" type="password">
                            </label>
                        </div>
                    </div>

                    <button class="flex w-full items-center justify-center gap-2 rounded-full bg-primary px-8 py-4 text-lg font-bold text-white shadow-[0px_20px_40px_rgba(171,17,85,0.18)] transition-transform duration-200 hover:scale-[1.02]" type="submit" data-prototype-skip="1">
                        <span class="material-symbols-outlined">save</span>
                        Salvar Configuracoes
                    </button>
                </form>
            </section>

            <section class="space-y-8">
                <div class="rounded-2xl bg-primary p-8 text-white shadow-[0px_20px_40px_rgba(171,17,85,0.18)]">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-white/70">Resumo rapido</p>
                    <h3 class="mt-3 text-3xl font-extrabold"><?= e((string) ($creator['name'] ?? 'Criador')) ?></h3>
                    <p class="mt-3 text-sm leading-relaxed text-white/80"><?= e(excerpt((string) ($creator['headline'] ?? ''), 100)) ?></p>
                    <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-white/60">Saldo</p>
                            <p class="mt-1 text-xl font-bold"><?= e(token_amount((int) ($wallet['balance'] ?? 0))) ?></p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-white/60">Token em BRL</p>
                            <p class="mt-1 text-xl font-bold"><?= e(brl_amount((float) ($platform['token_price_brl'] ?? 0.35))) ?></p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-surface-container-lowest p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                    <h3 class="text-xl font-bold">Operacao da conta</h3>
                    <div class="mt-5 space-y-4 text-sm">
                        <div class="flex items-center justify-between rounded-2xl bg-surface-container-low p-4">
                            <span class="text-on-surface-variant">Saque minimo</span>
                            <strong><?= e(token_amount((int) ($platform['withdraw_min_tokens'] ?? 50))) ?></strong>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl bg-surface-container-low p-4">
                            <span class="text-on-surface-variant">Saque maximo</span>
                            <strong><?= e(token_amount((int) ($platform['withdraw_max_tokens'] ?? 25000))) ?></strong>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl bg-surface-container-low p-4">
                            <span class="text-on-surface-variant">Proxima live</span>
                            <strong><?= $nextLive ? e(format_datetime((string) ($nextLive['scheduled_for'] ?? ''), 'd/m H:i')) : 'Sem agenda' ?></strong>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-surface-container-lowest p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                    <h3 class="text-xl font-bold">Status atual</h3>
                    <div class="mt-5 space-y-4 text-sm">
                        <div class="rounded-2xl bg-surface-container-low p-4">
                            <p class="text-on-surface-variant">Live ativa</p>
                            <p class="mt-1 font-bold"><?= $activeLive ? e((string) ($activeLive['title'] ?? 'Em andamento')) : 'Nenhuma live ao vivo agora' ?></p>
                        </div>
                        <div class="rounded-2xl bg-surface-container-low p-4">
                            <p class="text-on-surface-variant">Assinantes ativos</p>
                            <p class="mt-1 font-bold"><?= e((string) $activeSubscribers) ?> membros pagantes</p>
                        </div>
                        <div class="rounded-2xl bg-surface-container-low p-4">
                            <p class="text-on-surface-variant">Mood atual</p>
                            <p class="mt-1 font-bold"><?= e((string) ($creator['mood'] ?? 'Lua Nova')) ?></p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-surface-container-lowest p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.05)]">
                    <h3 class="text-xl font-bold">Chaves salvas</h3>
                    <div class="mt-5 space-y-4 text-sm">
                        <div class="flex items-center justify-between rounded-2xl bg-surface-container-low p-4">
                            <span class="text-on-surface-variant">Stream key</span>
                            <strong><?= !empty($security['has_stream_key']) ? 'Configurada' : 'Pendente' ?></strong>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl bg-surface-container-low p-4">
                            <span class="text-on-surface-variant">Chave de saque</span>
                            <strong><?= !empty($security['has_payout_key']) ? 'Configurada' : 'Pendente' ?></strong>
                        </div>
                        <div class="rounded-2xl bg-surface-container-low p-4">
                            <p class="text-on-surface-variant">URL publica</p>
                            <p class="mt-1 break-all font-bold">/profile?slug=<?= e((string) ($creator['slug'] ?? 'criador')) ?></p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>
</body>
</html>
