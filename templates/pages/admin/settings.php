<?php

declare(strict_types=1);

$settings = $data ?? [];
$admin = $app->auth->user() ?? [];
$adminAvatarUrl = media_url((string) ($admin['avatar_url'] ?? ''));
$adminCoverUrl = media_url((string) ($admin['cover_url'] ?? ''));
$siteBaseUrl = (string) ($settings['site_base_url'] ?? app_base_url($app->config, $settings));
$syncPayWebhookUrl = (string) ($settings['syncpay_webhook_url'] ?? webhook_url($app->config, $settings, '/webhook/syncpay'));
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
                        "on-surface": "#1b1c1d",
                        "on-surface-variant": "#5a4044",
                    },
                    fontFamily: { headline: ["Plus Jakarta Sans"], body: ["Manrope"] },
                    borderRadius: { DEFAULT: "1rem", lg: "2rem", xl: "3rem", full: "9999px" },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
        body { background: #fbf9fb; color: #1b1c1d; font-family: "Manrope", sans-serif; }
        h1, h2, h3, h4 { font-family: "Plus Jakarta Sans", sans-serif; }
        @media (max-width: 768px) {
            .mobile-stack { background: transparent !important; padding: 0 !important; box-shadow: none !important; }
            .mobile-card { border-radius: 1.75rem; background: #ffffff !important; padding: 1.15rem !important; box-shadow: 0 12px 32px rgba(27, 28, 29, 0.08); }
        }
    </style>
</head>
<body class="min-h-screen">
<?php
$adminTopbarUser = $admin;
require BASE_PATH . '/templates/partials/admin_topbar.php';
?>
<aside class="fixed left-0 top-16 hidden h-[calc(100vh-64px)] w-64 flex-col bg-[#f5f3f5] p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] lg:flex">
    <nav class="space-y-2">
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin"><span class="material-symbols-outlined">dashboard</span><span>Painel</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/users"><span class="material-symbols-outlined">group</span><span>Usuarios</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/moderation"><span class="material-symbols-outlined">gavel</span><span>Moderacao</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/finance"><span class="material-symbols-outlined">payments</span><span>Financeiro</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/operations"><span class="material-symbols-outlined">manufacturing</span><span>Operacoes</span></a>
        <a class="flex items-center gap-4 rounded-full bg-white px-4 py-3 font-bold text-primary" href="/admin/settings"><span class="material-symbols-outlined">settings</span><span>Configuracoes</span></a>
        <a class="flex items-center gap-4 rounded-full px-4 py-3 text-slate-500 transition-colors hover:bg-white/60" href="/admin/settings#seo"><span class="material-symbols-outlined">travel_explore</span><span>SEO</span></a>
    </nav>
    <div class="mt-auto rounded-3xl bg-white p-5 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary">LuaCoin hoje</p>
        <h3 class="mt-3 text-3xl font-extrabold"><?= e(brl_amount((float) ($settings['luacoin_price_brl'] ?? 0.07))) ?></h3>
        <p class="mt-2 text-sm text-on-surface-variant">Valor de referencia usado para leitura rapida no financeiro do admin.</p>
    </div>
</aside>
<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.3em] text-primary">Regras da plataforma</p>
        <h2 class="mt-2 text-5xl font-extrabold tracking-tight">Configuracoes <span class="italic text-primary">Centrais</span></h2>
        <p class="mt-4 max-w-2xl text-on-surface-variant">Ajuste financeiro, SyncPay, limites operacionais, SEO e identidade da plataforma a partir de um unico painel.</p>
    </section>

    <section class="mb-8 rounded-3xl bg-surface-container-lowest p-8 shadow-sm" id="perfil">
        <div class="mb-6 flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary">Conta administrativa</p>
                <h3 class="mt-3 text-3xl font-extrabold">Perfil do admin</h3>
                <p class="mt-3 max-w-2xl text-sm text-on-surface-variant">Atualize a conta que opera o painel central sem sair do admin.</p>
            </div>
            <div class="rounded-3xl bg-surface-container-low p-5 text-sm">
                <p class="font-bold"><?= e((string) ($admin['name'] ?? 'Admin')) ?></p>
                <p class="mt-1 text-on-surface-variant"><?= e((string) ($admin['email'] ?? '')) ?></p>
                <p class="mt-3 text-xs font-bold uppercase tracking-[0.25em] text-primary"><?= e((string) role_label((string) ($admin['role'] ?? 'admin'))) ?></p>
            </div>
        </div>
        <form action="/admin/profile/update" class="grid grid-cols-1 gap-8 xl:grid-cols-[1.1fr_0.9fr]" enctype="multipart/form-data" method="post">
            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
            <div class="space-y-6">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Nome</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="name" type="text" value="<?= e((string) ($admin['name'] ?? '')) ?>"></label>
                    <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Cidade</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="city" type="text" value="<?= e((string) ($admin['city'] ?? '')) ?>"></label>
                    <label class="block space-y-2 md:col-span-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">E-mail</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-on-surface-variant shadow-sm" readonly type="email" value="<?= e((string) ($admin['email'] ?? '')) ?>"></label>
                    <label class="block space-y-2 md:col-span-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Headline</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="headline" type="text" value="<?= e((string) ($admin['headline'] ?? '')) ?>"></label>
                </div>
                <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Bio</span><textarea class="min-h-36 w-full rounded-3xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="bio"><?= e((string) ($admin['bio'] ?? '')) ?></textarea></label>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">URL do avatar</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="avatar_url" type="text" value="<?= e((string) ($admin['avatar_url'] ?? '')) ?>"></label>
                    <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Upload do avatar</span><input accept=".jpg,.jpeg,.png,.webp,.gif" class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="avatar_file" type="file"></label>
                    <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">URL da capa</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="cover_url" type="text" value="<?= e((string) ($admin['cover_url'] ?? '')) ?>"></label>
                    <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Upload da capa</span><input accept=".jpg,.jpeg,.png,.webp,.gif" class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="cover_file" type="file"></label>
                    <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Nova senha</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="new_password" type="password"></label>
                    <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Confirmar senha</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="new_password_confirmation" type="password"></label>
                </div>
                <button class="rounded-full bg-slate-900 px-8 py-4 text-sm font-bold text-white" data-prototype-skip="1" type="submit">Salvar perfil do admin</button>
            </div>
            <div class="space-y-6">
                <div class="overflow-hidden rounded-3xl bg-surface-container-low">
                    <?php if ($adminCoverUrl !== ''): ?><img alt="Capa do admin" class="h-44 w-full object-cover" src="<?= e($adminCoverUrl) ?>"><?php else: ?><div class="flex h-44 w-full items-center justify-center bg-gradient-to-br from-[#ab1155] via-[#D81B60] to-[#f57c91] text-lg font-bold text-white">Control Room</div><?php endif; ?>
                </div>
                <div class="rounded-3xl bg-surface-container-low p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        <?php if ($adminAvatarUrl !== ''): ?><img alt="Avatar do admin" class="h-20 w-20 rounded-full object-cover" src="<?= e($adminAvatarUrl) ?>"><?php else: ?><div class="flex h-20 w-20 items-center justify-center rounded-full bg-white text-2xl font-bold text-primary"><?= e(avatar_initials((string) ($admin['name'] ?? 'Admin'))) ?></div><?php endif; ?>
                        <div><p class="text-lg font-bold"><?= e((string) ($admin['name'] ?? 'Admin')) ?></p><p class="mt-1 text-sm text-on-surface-variant"><?= e((string) ($admin['headline'] ?? '')) ?></p></div>
                    </div>
                    <p class="mt-5 text-sm leading-relaxed text-on-surface-variant"><?= e(excerpt((string) ($admin['bio'] ?? 'Perfil administrativo da plataforma.'), 180)) ?></p>
                </div>
            </div>
        </form>
    </section>

    <form action="/admin/settings/update" class="space-y-8" enctype="multipart/form-data" method="post">
        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
        <section class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
            <article class="rounded-3xl bg-surface-container-lowest p-5 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">LuaCoin</p><p class="mt-2 text-2xl font-extrabold text-primary"><?= e(brl_amount((float) ($settings['luacoin_price_brl'] ?? 0.07))) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Deposito minimo</p><p class="mt-2 text-2xl font-extrabold text-primary"><?= e(luacoins_amount((int) ($settings['deposit_min_luacoins'] ?? 100))) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Saque minimo</p><p class="mt-2 text-2xl font-extrabold text-primary"><?= e(luacoins_amount((int) ($settings['withdraw_min_luacoins'] ?? 50))) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Saque maximo</p><p class="mt-2 text-2xl font-extrabold text-primary"><?= e(luacoins_amount((int) ($settings['withdraw_max_luacoins'] ?? 25000))) ?></p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Armazenamento</p><p class="mt-2 text-2xl font-extrabold text-primary"><?= e((string) ((int) ($settings['creator_content_storage_limit_mb'] ?? 50))) ?> MB</p></article>
            <article class="rounded-3xl bg-surface-container-lowest p-5 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Alerta live</p><p class="mt-2 text-2xl font-extrabold text-primary"><?= e((string) ((int) ($settings['live_priority_alert_duration_ms'] ?? 8000) / 1000)) ?> s</p></article>
        </section>
        <div class="grid grid-cols-1 gap-8 2xl:grid-cols-[1fr_0.85fr]">
            <section class="space-y-8">
                <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                    <div class="mb-6">
                        <h3 class="text-2xl font-extrabold">Financeiro base</h3>
                        <p class="mt-2 text-sm text-on-surface-variant">Controle fee, LuaCoin e limites operacionais de dinheiro e conteudo.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Comissao da plataforma (%)</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" max="95" min="0" name="platform_fee_percent" step="1" type="number" value="<?= e((string) ($settings['platform_fee_percent'] ?? 20)) ?>"></label>
                        <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Valor da LuaCoin (BRL)</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="0.01" name="luacoin_price_brl" step="0.01" type="number" value="<?= e(number_format((float) ($settings['luacoin_price_brl'] ?? 0.07), 2, '.', '')) ?>"></label>
                        <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Deposito minimo (LuaCoins)</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="1" name="deposit_min_luacoins" step="1" type="number" value="<?= e((string) ($settings['deposit_min_luacoins'] ?? 100)) ?>"></label>
                        <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Saque minimo (LuaCoins)</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="1" name="withdraw_min_luacoins" step="1" type="number" value="<?= e((string) ($settings['withdraw_min_luacoins'] ?? 50)) ?>"></label>
                        <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Saque maximo (LuaCoins)</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="1" name="withdraw_max_luacoins" step="1" type="number" value="<?= e((string) ($settings['withdraw_max_luacoins'] ?? 25000)) ?>"></label>
                        <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Armazenamento padrao do criador (MB)</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="1" name="creator_content_storage_limit_mb" step="1" type="number" value="<?= e((string) ($settings['creator_content_storage_limit_mb'] ?? 50)) ?>"></label>
                        <label class="flex items-center gap-3 rounded-2xl bg-surface-container-low px-5 py-4 text-sm font-semibold text-on-surface"><input class="rounded border-none text-primary focus:ring-primary/20" name="subscriber_signup_bonus_enabled" type="checkbox" value="1" <?= !empty($settings['subscriber_signup_bonus_enabled']) ? 'checked' : '' ?>>Bonus de entrada no cadastro do assinante</label>
                        <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Bonus no cadastro (LuaCoins)</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="0" name="subscriber_signup_bonus_luacoins" step="1" type="number" value="<?= e((string) ($settings['subscriber_signup_bonus_luacoins'] ?? 120)) ?>"></label>
                        <label class="block space-y-2 md:col-span-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Bonus por compra de LuaCoins (%)</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" max="100" min="0" name="topup_bonus_percent" step="1" type="number" value="<?= e((string) ($settings['topup_bonus_percent'] ?? 0)) ?>"></label>
                    </div>
                </div>

                <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                    <div class="mb-6">
                        <h3 class="text-2xl font-extrabold">Lives e moderacao</h3>
                        <p class="mt-2 text-sm text-on-surface-variant">Defina limites, velocidade de chat e o comportamento visual padrao da plataforma.</p>
                    </div>
                    <div class="mobile-stack rounded-3xl bg-surface-container-low p-6">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <label class="mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Tempo maximo da live (min)</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="5" name="live_max_duration_minutes" step="1" type="number" value="<?= e((string) ($settings['live_max_duration_minutes'] ?? 30)) ?>"></label>
                            <label class="mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Duracao do alerta da live (ms)</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="2000" name="live_priority_alert_duration_ms" step="500" type="number" value="<?= e((string) ($settings['live_priority_alert_duration_ms'] ?? 8000)) ?>"></label>
                            <label class="mobile-card flex items-center gap-3 rounded-3xl bg-white px-5 py-4 text-sm font-semibold text-on-surface md:col-span-2"><input class="rounded border-none text-primary focus:ring-primary/20" name="live_chat_enabled" type="checkbox" value="1" <?= !empty($settings['live_chat_enabled']) ? 'checked' : '' ?>>Chat habilitado por padrao</label>
                            <label class="mobile-card flex items-center gap-3 rounded-3xl bg-white px-5 py-4 text-sm font-semibold text-on-surface"><input class="rounded border-none text-primary focus:ring-primary/20" name="maintenance_mode" type="checkbox" value="1" <?= !empty($settings['maintenance_mode']) ? 'checked' : '' ?>>Modo manutencao</label>
                            <label class="mobile-card flex items-center gap-3 rounded-3xl bg-white px-5 py-4 text-sm font-semibold text-on-surface"><input class="rounded border-none text-primary focus:ring-primary/20" name="auto_moderation" type="checkbox" value="1" <?= !empty($settings['auto_moderation']) ? 'checked' : '' ?>>Moderacao automatica</label>
                            <label class="mobile-card flex items-center gap-3 rounded-3xl bg-white px-5 py-4 text-sm font-semibold text-on-surface md:col-span-2"><input class="rounded border-none text-primary focus:ring-primary/20" name="blur_sensitive_thumbs" type="checkbox" value="1" <?= !empty($settings['blur_sensitive_thumbs']) ? 'checked' : '' ?>>Manter blur nas thumbs sensiveis e cards de live publicos</label>
                            <label class="mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm md:col-span-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Slow mode (segundos)</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="0" name="slow_mode_seconds" step="1" type="number" value="<?= e((string) ($settings['slow_mode_seconds'] ?? 3)) ?>"></label>
                        </div>
                    </div>
                    <div class="mt-5 rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
                        O replay automatico esta desabilitado para reduzir uso de disco na VPS. O armazenamento acima vale para conteudos enviados pelo criador.
                    </div>
                </div>

                <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                    <div class="mb-6">
                        <h3 class="text-2xl font-extrabold">SyncPay PIX</h3>
                        <p class="mt-2 text-sm text-on-surface-variant">Configure a SyncPay para recarga de LuaCoins. Saques continuam manuais pelo admin.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <label class="block space-y-2 md:col-span-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Site URL</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="site_base_url" type="url" value="<?= e($siteBaseUrl) ?>"></label>
                        <label class="block space-y-2 md:col-span-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Webhook SyncPay</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-on-surface-variant shadow-sm" readonly type="text" value="<?= e($syncPayWebhookUrl) ?>"></label>
                        <label class="block space-y-2 md:col-span-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">API Base URL</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="syncpay_api_base_url" type="url" value="<?= e((string) ($settings['syncpay_api_base_url'] ?? 'https://api.syncpayments.com.br')) ?>"></label>
                        <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Client ID</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="syncpay_client_id" type="text" value="<?= e((string) ($settings['syncpay_client_id'] ?? '')) ?>"></label>
                        <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Client Secret</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="syncpay_client_secret" type="text" value="<?= e((string) ($settings['syncpay_client_secret'] ?? '')) ?>"></label>
                        <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">API Key (fallback/status)</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="syncpay_api_key" type="text" value="<?= e((string) ($settings['syncpay_api_key'] ?? '')) ?>"></label>
                        <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Webhook Token</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="syncpay_webhook_token" type="text" value="<?= e((string) ($settings['syncpay_webhook_token'] ?? '')) ?>"></label>
                        <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Validade do PIX (dias)</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="1" name="syncpay_pix_expires_in_days" step="1" type="number" value="<?= e((string) ($settings['syncpay_pix_expires_in_days'] ?? 2)) ?>"></label>
                    </div>
                </div>
            </section>

            <section class="space-y-8">
                <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                    <div class="mb-6">
                        <h3 class="text-2xl font-extrabold">Comunicacao</h3>
                        <p class="mt-2 text-sm text-on-surface-variant">Mensagens operacionais e ajustes de leitura para os times internos.</p>
                    </div>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Announcement</span>
                        <textarea class="min-h-40 w-full rounded-3xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="announcement"><?= e((string) ($settings['announcement'] ?? '')) ?></textarea>
                    </label>
                </div>
                <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm" id="seo">
                    <div class="mb-6">
                        <h3 class="text-2xl font-extrabold">SEO e branding</h3>
                        <p class="mt-2 text-sm text-on-surface-variant">Defina titulos, meta description e substitua os logos geral branco e colorido.</p>
                    </div>
                    <div class="space-y-5">
                        <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Titulo do site</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="seo_site_title" type="text" value="<?= e((string) ($settings['seo_site_title'] ?? 'SexyLua')) ?>"></label>
                        <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Meta title</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="seo_meta_title" type="text" value="<?= e((string) ($settings['seo_meta_title'] ?? 'SexyLua')) ?>"></label>
                        <label class="block space-y-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Meta description</span><textarea class="min-h-32 w-full rounded-3xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="seo_meta_description"><?= e((string) ($settings['seo_meta_description'] ?? '')) ?></textarea></label>
                        <div class="mobile-stack rounded-3xl bg-surface-container-low p-6">
                            <div class="grid grid-cols-1 gap-5">
                                <label class="mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Logo branco URL</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="seo_logo_white_url" type="text" value="<?= e((string) ($settings['seo_logo_white_url'] ?? '')) ?>"></label>
                                <label class="mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Upload do logo branco</span><input accept=".png,.jpg,.jpeg,.webp,.svg,.gif" class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="seo_logo_white_file" type="file"></label>
                                <label class="mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Logo colorido URL</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="seo_logo_color_url" type="text" value="<?= e((string) ($settings['seo_logo_color_url'] ?? '')) ?>"></label>
                                <label class="mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Upload do logo colorido</span><input accept=".png,.jpg,.jpeg,.webp,.svg,.gif" class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="seo_logo_color_file" type="file"></label>
                            </div>
                        </div>
                        <div class="mobile-stack rounded-3xl bg-surface-container-low p-6">
                            <div class="grid grid-cols-1 gap-5">
                                <label class="mobile-card flex items-center gap-3 rounded-3xl bg-white px-5 py-4 text-sm font-semibold text-on-surface"><input class="rounded border-none text-primary focus:ring-primary/20" name="home_banner_enabled" type="checkbox" value="1" <?= !empty($settings['home_banner_enabled']) ? 'checked' : '' ?>>Exibir banner principal na home</label>
                                <label class="mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Titulo do banner</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="home_banner_title" type="text" value="<?= e((string) ($settings['home_banner_title'] ?? '')) ?>"></label>
                                <label class="mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Subtitulo do banner</span><textarea class="min-h-28 w-full rounded-3xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="home_banner_subtitle"><?= e((string) ($settings['home_banner_subtitle'] ?? '')) ?></textarea></label>
                                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    <label class="mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Botao principal</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="home_banner_primary_text" type="text" value="<?= e((string) ($settings['home_banner_primary_text'] ?? '')) ?>"></label>
                                    <label class="mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Link principal</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="home_banner_primary_link" type="text" value="<?= e((string) ($settings['home_banner_primary_link'] ?? '')) ?>"></label>
                                    <label class="mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Botao secundario</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="home_banner_secondary_text" type="text" value="<?= e((string) ($settings['home_banner_secondary_text'] ?? '')) ?>"></label>
                                    <label class="mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Link secundario</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="home_banner_secondary_link" type="text" value="<?= e((string) ($settings['home_banner_secondary_link'] ?? '')) ?>"></label>
                                </div>
                                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    <label class="mobile-card flex items-center gap-3 rounded-3xl bg-white px-5 py-4 text-sm font-semibold text-on-surface"><input class="rounded border-none text-primary focus:ring-primary/20" name="home_banner_countdown_enabled" type="checkbox" value="1" <?= !empty($settings['home_banner_countdown_enabled']) ? 'checked' : '' ?>>Exibir contador regressivo</label>
                                    <label class="mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Contador (segundos)</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="0" name="home_banner_countdown_seconds" step="1" type="number" value="<?= e((string) ($settings['home_banner_countdown_seconds'] ?? 172800)) ?>"></label>
                                    <label class="mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm md:col-span-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Imagem do banner URL</span><input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="home_banner_background_url" type="text" value="<?= e((string) ($settings['home_banner_background_url'] ?? '')) ?>"></label>
                                    <label class="mobile-card block space-y-2 rounded-3xl bg-white p-5 shadow-sm md:col-span-2"><span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Upload da imagem do banner</span><input accept=".png,.jpg,.jpeg,.webp,.gif" class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="home_banner_background_file" type="file"></label>
                                </div>
                                <div class="mobile-card overflow-hidden rounded-3xl bg-white p-0 shadow-sm">
                                    <img alt="Preview do banner da home" class="h-40 w-full object-cover" src="<?= e(media_url((string) ($settings['home_banner_background_url'] ?? '')) ?: home_banner_default_image_url()) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="rounded-3xl bg-surface-container-low p-5">
                            <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Preview rapido</p>
                            <div class="mt-4 flex flex-wrap items-center gap-4">
                                <div class="rounded-2xl bg-primary px-5 py-4"><?= brand_logo_white('h-6 w-auto') ?></div>
                                <div class="rounded-2xl bg-white px-5 py-4 shadow-sm"><?= brand_logo_magenta('h-6 w-auto') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="rounded-3xl bg-primary p-8 text-white shadow-[0px_20px_40px_rgba(171,17,85,0.18)]">
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/70">Checklist</p>
                    <h3 class="mt-3 text-3xl font-extrabold">Publicacao segura</h3>
                    <ul class="mt-4 space-y-3 text-sm text-white/85">
                        <li>1. Defina o deposito minimo e os limites de saque.</li>
                        <li>2. Confirme Site URL, webhook e credenciais SyncPay.</li>
                        <li>3. Ajuste o titulo/meta para o SEO publico.</li>
                        <li>4. Mantenha o armazenamento enxuto enquanto o replay segue desabilitado.</li>
                    </ul>
                    <button class="mt-6 inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-bold text-primary" data-prototype-skip="1" type="submit">
                        <span class="material-symbols-outlined text-base">save</span>
                        Salvar configuracoes
                    </button>
                </div>
            </section>
        </div>
    </form>
</main>
</body>
</html>
