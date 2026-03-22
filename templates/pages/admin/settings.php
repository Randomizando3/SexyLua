<?php

declare(strict_types=1);

$settings = $data ?? [];
$admin = $app->auth->user() ?? [];
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Configuracoes do Sistema</title>
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
                        "surface-container-high": "#e9e7e9",
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
    </style>
</head>
<body class="min-h-screen">
<header class="fixed top-0 z-50 flex h-16 w-full items-center justify-between bg-[#D81B60] px-6 font-['Plus_Jakarta_Sans'] font-bold tracking-wide text-white shadow-lg shadow-[#D81B60]/20">
    <div class="flex items-center gap-4">
        <h1 class="text-2xl font-black">SexyLua Admin</h1>
        <span class="hidden border-l border-white/20 pl-4 text-xs uppercase tracking-widest opacity-80 md:block">Control Room</span>
    </div>
    <div class="flex items-center gap-3">
        <a class="rounded-full border border-white/20 px-4 py-2 text-xs font-bold uppercase tracking-widest transition-colors hover:bg-white/10" href="/admin">Dashboard</a>
        <div class="flex h-9 w-9 items-center justify-center rounded-full border border-white/20 bg-white/10 font-bold"><?= e(avatar_initials((string) ($admin['name'] ?? 'Admin'))) ?></div>
    </div>
</header>

<aside class="fixed left-0 top-16 hidden h-[calc(100vh-64px)] w-64 flex-col bg-[#f5f3f5] p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] lg:flex">
    <nav class="space-y-2">
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin"><span class="material-symbols-outlined">dashboard</span><span>Painel</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/users"><span class="material-symbols-outlined">group</span><span>Usuarios</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/moderation"><span class="material-symbols-outlined">gavel</span><span>Moderacao</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/finance"><span class="material-symbols-outlined">payments</span><span>Financeiro</span></a>
        <a class="flex items-center gap-4 rounded-full bg-white px-4 py-3 font-bold text-primary" href="/admin/settings"><span class="material-symbols-outlined">settings</span><span>Configuracoes</span></a>
    </nav>
    <div class="mt-auto rounded-3xl bg-white p-5 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary">Token hoje</p>
        <h3 class="mt-3 text-3xl font-extrabold"><?= e(brl_amount((float) ($settings['token_price_brl'] ?? 0.35))) ?></h3>
        <p class="mt-2 text-sm text-on-surface-variant">Valor de referencia por token utilizado na plataforma.</p>
    </div>
</aside>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Regras da plataforma</p>
        <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Configuracoes <span class="italic text-primary">Centrais</span></h2>
        <p class="mt-4 max-w-2xl text-on-surface-variant">Ajuste comissao, limites de saque, moderacao automatica, chat e comunicados globais sem sair do painel.</p>
    </section>

    <form action="/admin/settings/update" class="space-y-8" method="post">
        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">

        <div class="grid grid-cols-1 gap-8 2xl:grid-cols-[1fr_0.85fr]">
            <section class="space-y-8">
                <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                    <div class="mb-6">
                        <h3 class="text-2xl font-extrabold">Financeiro base</h3>
                        <p class="mt-2 text-sm text-on-surface-variant">Controle o fee da plataforma, valor do token e os limites de retirada.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Comissao da plataforma (%)</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" max="95" min="0" name="platform_fee_percent" step="1" type="number" value="<?= e((string) ($settings['platform_fee_percent'] ?? 20)) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Valor do token (BRL)</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="0.01" name="token_price_brl" step="0.01" type="number" value="<?= e(number_format((float) ($settings['token_price_brl'] ?? 0.35), 2, '.', '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Saque minimo (tokens)</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="1" name="withdraw_min_tokens" step="1" type="number" value="<?= e((string) ($settings['withdraw_min_tokens'] ?? 50)) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Saque maximo (tokens)</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="1" name="withdraw_max_tokens" step="1" type="number" value="<?= e((string) ($settings['withdraw_max_tokens'] ?? 25000)) ?>">
                        </label>
                    </div>
                </div>

                <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                    <div class="mb-6">
                        <h3 class="text-2xl font-extrabold">Moderacao e comunicacao</h3>
                        <p class="mt-2 text-sm text-on-surface-variant">Defina regras operacionais para o chat, banner de aviso e filtro visual automatico.</p>
                    </div>
                    <div class="space-y-5">
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Slow mode do chat (segundos)</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="0" name="slow_mode_seconds" step="1" type="number" value="<?= e((string) ($settings['slow_mode_seconds'] ?? 0)) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Anuncio global</span>
                            <textarea class="min-h-36 w-full rounded-3xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="announcement" placeholder="Mensagem exibida para criadores e assinantes."><?= e((string) ($settings['announcement'] ?? '')) ?></textarea>
                        </label>
                    </div>
                </div>
            </section>

            <section class="space-y-8">
                <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                    <div class="mb-6">
                        <h3 class="text-2xl font-extrabold">Chaves operacionais</h3>
                        <p class="mt-2 text-sm text-on-surface-variant">Ative ou desative recursos sensiveis da plataforma.</p>
                    </div>
                    <div class="space-y-4">
                        <label class="flex items-center justify-between gap-4 rounded-3xl bg-surface-container-low p-5">
                            <div>
                                <p class="font-bold">Modo manutencao</p>
                                <p class="mt-1 text-sm text-on-surface-variant">Pausa o uso publico enquanto o time faz ajustes.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <input name="maintenance_mode" type="hidden" value="0">
                                <input class="h-5 w-5 rounded border-none text-primary focus:ring-primary/20" name="maintenance_mode" type="checkbox" value="1" <?= !empty($settings['maintenance_mode']) ? 'checked' : '' ?>>
                            </div>
                        </label>
                        <label class="flex items-center justify-between gap-4 rounded-3xl bg-surface-container-low p-5">
                            <div>
                                <p class="font-bold">Moderacao automatica</p>
                                <p class="mt-1 text-sm text-on-surface-variant">Sinaliza conteudos antes da analise humana.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <input name="auto_moderation" type="hidden" value="0">
                                <input class="h-5 w-5 rounded border-none text-primary focus:ring-primary/20" name="auto_moderation" type="checkbox" value="1" <?= !empty($settings['auto_moderation']) ? 'checked' : '' ?>>
                            </div>
                        </label>
                        <label class="flex items-center justify-between gap-4 rounded-3xl bg-surface-container-low p-5">
                            <div>
                                <p class="font-bold">Blur de thumbnails sensiveis</p>
                                <p class="mt-1 text-sm text-on-surface-variant">Aplica camada de protecao nas miniaturas mais delicadas.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <input name="blur_sensitive_thumbs" type="hidden" value="0">
                                <input class="h-5 w-5 rounded border-none text-primary focus:ring-primary/20" name="blur_sensitive_thumbs" type="checkbox" value="1" <?= !empty($settings['blur_sensitive_thumbs']) ? 'checked' : '' ?>>
                            </div>
                        </label>
                        <label class="flex items-center justify-between gap-4 rounded-3xl bg-surface-container-low p-5">
                            <div>
                                <p class="font-bold">Chat nas lives</p>
                                <p class="mt-1 text-sm text-on-surface-variant">Libera envio de mensagens durante as transmissoes.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <input name="live_chat_enabled" type="hidden" value="0">
                                <input class="h-5 w-5 rounded border-none text-primary focus:ring-primary/20" name="live_chat_enabled" type="checkbox" value="1" <?= !empty($settings['live_chat_enabled']) ? 'checked' : '' ?>>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Resumo atual</p>
                    <div class="mt-6 space-y-4">
                        <div class="rounded-3xl bg-surface-container-low p-5">
                            <p class="text-sm text-on-surface-variant">Fee atual</p>
                            <p class="mt-2 text-2xl font-extrabold text-primary"><?= e((string) ($settings['platform_fee_percent'] ?? 20)) ?>%</p>
                        </div>
                        <div class="rounded-3xl bg-surface-container-low p-5">
                            <p class="text-sm text-on-surface-variant">Faixa de saque</p>
                            <p class="mt-2 text-2xl font-extrabold text-primary"><?= e(token_amount((int) ($settings['withdraw_min_tokens'] ?? 50))) ?> a <?= e(token_amount((int) ($settings['withdraw_max_tokens'] ?? 25000))) ?></p>
                        </div>
                        <div class="rounded-3xl bg-surface-container-low p-5">
                            <p class="text-sm text-on-surface-variant">Slow mode</p>
                            <p class="mt-2 text-2xl font-extrabold text-primary"><?= e((string) ($settings['slow_mode_seconds'] ?? 0)) ?>s</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <button class="rounded-full bg-primary px-8 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Salvar configuracoes</button>
            <a class="rounded-full bg-surface-container-low px-8 py-4 text-center text-sm font-bold text-on-surface-variant" href="/admin/settings">Recarregar painel</a>
        </div>
    </form>
</main>
</body>
</html>
