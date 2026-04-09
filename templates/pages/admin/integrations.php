<?php

declare(strict_types=1);

$settings = $data ?? [];
$admin = $app->auth->user() ?? [];
$siteBaseUrl = (string) ($settings['site_base_url'] ?? app_base_url($app->config, $settings));
$syncPayWebhookUrl = (string) ($settings['syncpay_webhook_url'] ?? webhook_url($app->config, $settings, '/webhook/syncpay'));
$googleRedirectUrl = rtrim($siteBaseUrl !== '' ? $siteBaseUrl : app_base_url($app->config, $settings), '/') . '/auth/google/callback';
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - Integracoes</title>
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
        .settings-kicker {
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #ab1155;
        }
    </style>
</head>
<body class="min-h-screen">
<?php
$adminTopbarUser = $admin;
require BASE_PATH . '/templates/partials/admin_topbar.php';
?>
<?php
$adminSidebarCurrent = 'integrations';
$adminSidebarMetricTitle = 'Webhook principal';
$adminSidebarMetricValue = '/webhook/syncpay';
$adminSidebarMetricDescription = 'Central unica para SyncPay, Google e SMTP.';
require BASE_PATH . '/templates/partials/admin_sidebar.php';
?>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="settings-kicker">Integracoes</p>
            <h1 class="mt-2 text-5xl font-extrabold tracking-tight">Chaves, login social e entrega de e-mail</h1>
            <p class="mt-4 max-w-3xl text-on-surface-variant">Concentre aqui tudo o que depende de chave, webhook, OAuth e SMTP. Assim o painel tecnico fica separado das configuracoes operacionais.</p>
        </div>
        <form action="/admin/settings/update" method="post">
            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
            <input name="return_to" type="hidden" value="/admin/integrations">
            <button class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-bold text-white shadow-[0px_18px_36px_rgba(171,17,85,0.22)]" data-prototype-skip="1" type="submit">
                <span class="material-symbols-outlined text-base">save</span>
                Salvar integracoes
            </button>
        </form>
    </section>

    <form action="/admin/settings/update" class="space-y-8" method="post">
        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
        <input name="return_to" type="hidden" value="/admin/integrations">

        <section class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
            <div class="mb-6">
                <p class="settings-kicker">SyncPay</p>
                <h2 class="text-2xl font-extrabold">PIX e recarga de LuaCoins</h2>
                <p class="mt-2 text-sm text-on-surface-variant">Base da API, credenciais da Partner API e o webhook principal usado pela plataforma.</p>
            </div>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <label class="block space-y-2 md:col-span-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Site URL</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="site_base_url" type="url" value="<?= e($siteBaseUrl) ?>">
                </label>
                <label class="block space-y-2 md:col-span-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Webhook SyncPay</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-on-surface-variant shadow-sm" readonly type="text" value="<?= e($syncPayWebhookUrl) ?>">
                </label>
                <label class="block space-y-2 md:col-span-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">API Base URL</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="syncpay_api_base_url" type="url" value="<?= e((string) ($settings['syncpay_api_base_url'] ?? 'https://api.syncpayments.com.br')) ?>">
                </label>
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Client ID</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="syncpay_client_id" type="text" value="<?= e((string) ($settings['syncpay_client_id'] ?? '')) ?>">
                </label>
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Client Secret</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="syncpay_client_secret" type="text" value="<?= e((string) ($settings['syncpay_client_secret'] ?? '')) ?>">
                </label>
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">API Key (fallback/status)</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="syncpay_api_key" type="text" value="<?= e((string) ($settings['syncpay_api_key'] ?? '')) ?>">
                </label>
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Webhook Token</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="syncpay_webhook_token" type="text" value="<?= e((string) ($settings['syncpay_webhook_token'] ?? '')) ?>">
                </label>
                <label class="block space-y-2 md:col-span-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Validade do PIX (dias)</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="1" name="syncpay_pix_expires_in_days" step="1" type="number" value="<?= e((string) ($settings['syncpay_pix_expires_in_days'] ?? 2)) ?>">
                </label>
            </div>
        </section>

        <section class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
            <div class="mb-6">
                <p class="settings-kicker">Google OAuth</p>
                <h2 class="text-2xl font-extrabold">Login com Google</h2>
                <p class="mt-2 text-sm text-on-surface-variant">Use essas credenciais para o botao de login e cadastro pelo Google. Se o e-mail ja existir, a plataforma so autentica.</p>
            </div>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <label class="flex items-center gap-3 rounded-2xl bg-surface-container-low px-5 py-4 text-sm font-semibold text-on-surface md:col-span-2">
                    <input class="rounded border-none text-primary focus:ring-primary/20" name="google_oauth_enabled" type="checkbox" value="1" <?= !empty($settings['google_oauth_enabled']) ? 'checked' : '' ?>>
                    Habilitar login com Google
                </label>
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Client ID Google</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="google_client_id" type="text" value="<?= e((string) ($settings['google_client_id'] ?? '')) ?>">
                </label>
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Client Secret Google</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="google_client_secret" type="text" value="<?= e((string) ($settings['google_client_secret'] ?? '')) ?>">
                </label>
                <label class="block space-y-2 md:col-span-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Redirect URI</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 text-on-surface-variant shadow-sm" readonly type="text" value="<?= e($googleRedirectUrl) ?>">
                    <span class="block text-xs text-on-surface-variant">Cadastre exatamente esta URL no Google Cloud Console.</span>
                </label>
            </div>
        </section>

        <section class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
            <div class="mb-6">
                <p class="settings-kicker">SMTP e suporte</p>
                <h2 class="text-2xl font-extrabold">Entrega da pagina de ajuda</h2>
                <p class="mt-2 text-sm text-on-surface-variant">Defina o e-mail que recebe os formularios e a conta SMTP que faz a entrega.</p>
            </div>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Nome do destinatario</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="support_recipient_name" type="text" value="<?= e((string) ($settings['support_recipient_name'] ?? 'SexyLua')) ?>">
                </label>
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">E-mail de recebimento</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="support_recipient_email" type="email" value="<?= e((string) ($settings['support_recipient_email'] ?? '')) ?>">
                </label>
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">SMTP host</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="smtp_host" type="text" value="<?= e((string) ($settings['smtp_host'] ?? '')) ?>">
                </label>
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">SMTP porta</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="1" name="smtp_port" step="1" type="number" value="<?= e((string) ($settings['smtp_port'] ?? 587)) ?>">
                </label>
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Criptografia</span>
                    <select class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="smtp_encryption">
                        <?php foreach (['none' => 'Nenhuma', 'tls' => 'TLS', 'ssl' => 'SSL'] as $encryptionValue => $encryptionLabel): ?>
                            <option value="<?= e($encryptionValue) ?>" <?= (string) ($settings['smtp_encryption'] ?? 'tls') === $encryptionValue ? 'selected' : '' ?>><?= e($encryptionLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Timeout (segundos)</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="5" name="smtp_timeout_seconds" step="1" type="number" value="<?= e((string) ($settings['smtp_timeout_seconds'] ?? 15)) ?>">
                </label>
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Usuario SMTP</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="smtp_username" type="text" value="<?= e((string) ($settings['smtp_username'] ?? '')) ?>">
                </label>
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Senha SMTP</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="smtp_password" type="text" value="<?= e((string) ($settings['smtp_password'] ?? '')) ?>">
                </label>
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Nome remetente</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="smtp_from_name" type="text" value="<?= e((string) ($settings['smtp_from_name'] ?? 'SexyLua')) ?>">
                </label>
                <label class="block space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">E-mail remetente</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="smtp_from_email" type="email" value="<?= e((string) ($settings['smtp_from_email'] ?? '')) ?>">
                </label>
            </div>
        </section>

        <div class="flex justify-end">
            <button class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-bold text-white shadow-[0px_18px_36px_rgba(171,17,85,0.22)]" data-prototype-skip="1" type="submit">
                <span class="material-symbols-outlined text-base">save</span>
                Salvar integracoes
            </button>
        </div>
    </form>
</main>
</body>
</html>
