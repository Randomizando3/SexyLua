<?php

declare(strict_types=1);

$settings = $data ?? [];
$admin = $app->auth->user() ?? [];
$seoLogoWhitePreview = media_url((string) ($settings['seo_logo_white_url'] ?? '')) ?: asset('img/sexylualogobranco.png');
$seoLogoColorPreview = media_url((string) ($settings['seo_logo_color_url'] ?? '')) ?: asset('img/sexylualogomagenta.png');
$homeBannerPreview = media_url((string) ($settings['home_banner_background_url'] ?? '')) ?: home_banner_default_image_url();
$homeBannerMobilePreview = media_url((string) ($settings['home_banner_background_mobile_url'] ?? '')) ?: $homeBannerPreview;
$homeBannerIsVideo = media_is_video($homeBannerPreview);
$homeBannerMobileIsVideo = media_is_video($homeBannerMobilePreview);
$homeBannerCountdownTargetAt = trim((string) ($settings['home_banner_countdown_target_at'] ?? ''));
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SexyLua - SEO e Branding</title>
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
        .preview-tile {
            overflow: hidden;
            border-radius: 1.75rem;
            border: 1px solid rgba(148, 163, 184, 0.15);
            background: #ffffff;
            box-shadow: 0 14px 32px rgba(27, 28, 29, 0.06);
        }
    </style>
</head>
<body class="min-h-screen">
<?php
$adminTopbarUser = $admin;
require BASE_PATH . '/templates/partials/admin_topbar.php';
?>
<?php
$adminSidebarCurrent = 'seo';
$adminSidebarMetricTitle = 'Branding';
$adminSidebarMetricValue = 'Home + Metadados';
$adminSidebarMetricDescription = 'Titulo, logos e banner principal da home.';
require BASE_PATH . '/templates/partials/admin_sidebar.php';
?>

<main class="min-h-screen px-6 pb-10 pt-24 lg:ml-64 lg:px-10">
    <section class="mb-8 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="settings-kicker">SEO e identidade</p>
            <h1 class="mt-2 text-5xl font-extrabold tracking-tight">Marca, metadados e banner da home</h1>
            <p class="mt-4 max-w-3xl text-on-surface-variant">Edite o nome do site, descricoes de busca, logos e a faixa principal da home em uma area separada das integracoes tecnicas.</p>
        </div>
        <form action="/admin/settings/update" method="post">
            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
            <input name="return_to" type="hidden" value="/admin/seo">
            <button class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-bold text-white shadow-[0px_18px_36px_rgba(171,17,85,0.22)]" data-prototype-skip="1" type="submit">
                <span class="material-symbols-outlined text-base">save</span>
                Salvar SEO
            </button>
        </form>
    </section>

    <form action="/admin/settings/update" class="space-y-8" enctype="multipart/form-data" method="post">
        <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
        <input name="return_to" type="hidden" value="/admin/seo">

        <section class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
            <div class="mb-6">
                <p class="settings-kicker">Metadados</p>
                <h2 class="text-2xl font-extrabold">Titulo e descricoes</h2>
                <p class="mt-2 text-sm text-on-surface-variant">Esses campos alimentam titulo da pagina, busca e compartilhamento.</p>
            </div>
            <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                <label class="block space-y-2 xl:col-span-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Titulo do site</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="seo_site_title" type="text" value="<?= e((string) ($settings['seo_site_title'] ?? 'SexyLua')) ?>">
                </label>
                <label class="block space-y-2 xl:col-span-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Meta title</span>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="seo_meta_title" type="text" value="<?= e((string) ($settings['seo_meta_title'] ?? 'SexyLua')) ?>">
                </label>
                <label class="block space-y-2 xl:col-span-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Meta description</span>
                    <textarea class="min-h-32 w-full rounded-3xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="seo_meta_description"><?= e((string) ($settings['seo_meta_description'] ?? '')) ?></textarea>
                </label>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-8 xl:grid-cols-2">
            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <div class="mb-6">
                    <p class="settings-kicker">Logo branco</p>
                    <h2 class="text-2xl font-extrabold">Fundos escuros</h2>
                </div>
                <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_220px] xl:items-start">
                    <div class="space-y-4">
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">URL do logo branco</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="seo_logo_white_url" type="text" value="<?= e((string) ($settings['seo_logo_white_url'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Upload do logo branco</span>
                            <input accept=".png,.jpg,.jpeg,.webp,.svg,.gif" class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="seo_logo_white_file" type="file">
                        </label>
                    </div>
                    <div class="preview-tile p-5">
                        <div class="flex min-h-[160px] items-center justify-center rounded-[1.5rem] bg-[#1b1c1d] p-6">
                            <img alt="Preview do logo branco" class="max-h-24 w-auto object-contain" src="<?= e($seoLogoWhitePreview) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
                <div class="mb-6">
                    <p class="settings-kicker">Logo colorido</p>
                    <h2 class="text-2xl font-extrabold">Fundos claros</h2>
                </div>
                <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_220px] xl:items-start">
                    <div class="space-y-4">
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">URL do logo colorido</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="seo_logo_color_url" type="text" value="<?= e((string) ($settings['seo_logo_color_url'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Upload do logo colorido</span>
                            <input accept=".png,.jpg,.jpeg,.webp,.svg,.gif" class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="seo_logo_color_file" type="file">
                        </label>
                    </div>
                    <div class="preview-tile p-5">
                        <div class="flex min-h-[160px] items-center justify-center rounded-[1.5rem] bg-[#fbf9fb] p-6">
                            <img alt="Preview do logo colorido" class="max-h-24 w-auto object-contain" src="<?= e($seoLogoColorPreview) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl bg-surface-container-lowest p-8 shadow-sm">
            <div class="mb-6">
                <p class="settings-kicker">Banner principal</p>
                <h2 class="text-2xl font-extrabold">Campanha da home</h2>
                <p class="mt-2 text-sm text-on-surface-variant">Controle chamada, botoes, contador e fundo do primeiro banner da home.</p>
            </div>
            <div class="grid grid-cols-1 gap-8 xl:grid-cols-[1.05fr_0.95fr]">
                <div class="space-y-5">
                    <label class="flex items-center gap-3 rounded-2xl bg-surface-container-low px-5 py-4 text-sm font-semibold text-on-surface">
                        <input class="rounded border-none text-primary focus:ring-primary/20" name="home_banner_enabled" type="checkbox" value="1" <?= !empty($settings['home_banner_enabled']) ? 'checked' : '' ?>>
                        Exibir o banner principal da home
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Titulo do banner</span>
                        <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="home_banner_title" type="text" value="<?= e((string) ($settings['home_banner_title'] ?? '')) ?>">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Subtitulo do banner</span>
                        <textarea class="min-h-32 w-full rounded-3xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="home_banner_subtitle"><?= e((string) ($settings['home_banner_subtitle'] ?? '')) ?></textarea>
                    </label>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Botao principal</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="home_banner_primary_text" type="text" value="<?= e((string) ($settings['home_banner_primary_text'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Link principal</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="home_banner_primary_link" type="text" value="<?= e((string) ($settings['home_banner_primary_link'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Botao secundario</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="home_banner_secondary_text" type="text" value="<?= e((string) ($settings['home_banner_secondary_text'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Link secundario</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="home_banner_secondary_link" type="text" value="<?= e((string) ($settings['home_banner_secondary_link'] ?? '')) ?>">
                        </label>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label class="flex items-center gap-3 rounded-2xl bg-surface-container-low px-5 py-4 text-sm font-semibold text-on-surface">
                            <input class="rounded border-none text-primary focus:ring-primary/20" name="home_banner_countdown_enabled" type="checkbox" value="1" <?= !empty($settings['home_banner_countdown_enabled']) ? 'checked' : '' ?>>
                            Exibir contador
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Duracao do contador (segundos)</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" min="0" name="home_banner_countdown_seconds" step="1" type="number" value="<?= e((string) ($settings['home_banner_countdown_seconds'] ?? 172800)) ?>">
                            <?php if ($homeBannerCountdownTargetAt !== ''): ?>
                                <span class="block text-xs text-on-surface-variant">Termina em <?= e(format_datetime($homeBannerCountdownTargetAt, 'd/m/Y H:i')) ?></span>
                            <?php endif; ?>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Imagem desktop URL</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="home_banner_background_url" type="text" value="<?= e((string) ($settings['home_banner_background_url'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Upload desktop</span>
                            <input accept="<?= e(cover_media_accept_attribute()) ?>" class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" data-cover-media-input name="home_banner_background_file" type="file">
                            <span class="block text-xs text-on-surface-variant" data-cover-media-feedback><?= e(cover_media_recommendation_text()) ?></span>
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Imagem mobile URL</span>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" name="home_banner_background_mobile_url" type="text" value="<?= e((string) ($settings['home_banner_background_mobile_url'] ?? '')) ?>">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Upload mobile</span>
                            <input accept="<?= e(cover_media_accept_attribute()) ?>" class="w-full rounded-2xl border-none bg-surface-container-low px-5 py-4 shadow-sm focus:ring-2 focus:ring-primary/20" data-cover-media-input name="home_banner_background_mobile_file" type="file">
                            <span class="block text-xs text-on-surface-variant" data-cover-media-feedback><?= e(cover_media_recommendation_text()) ?></span>
                        </label>
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="preview-tile overflow-hidden">
                        <?php if ($homeBannerIsVideo): ?>
                            <video autoplay class="h-[240px] w-full object-cover" loop muted playsinline src="<?= e($homeBannerPreview) ?>"></video>
                        <?php else: ?>
                            <img alt="Preview desktop do banner" class="h-[240px] w-full object-cover" src="<?= e($homeBannerPreview) ?>">
                        <?php endif; ?>
                        <div class="space-y-4 p-5">
                            <div class="flex flex-wrap gap-3 text-xs font-bold uppercase tracking-[0.22em] text-slate-400">
                                <span class="rounded-full bg-[#f5f3f5] px-3 py-1">Preview desktop</span>
                            </div>
                            <p class="text-2xl font-extrabold text-on-surface"><?= e((string) ($settings['home_banner_title'] ?? 'SexyLua')) ?></p>
                            <p class="text-sm leading-6 text-on-surface-variant"><?= e((string) ($settings['home_banner_subtitle'] ?? 'Configure titulo, subtitulo e botoes para destacar a campanha principal da home.')) ?></p>
                        </div>
                    </div>

                    <div class="preview-tile overflow-hidden">
                        <div class="mx-auto flex max-w-[260px] justify-center bg-[#f5f3f5] p-4">
                            <?php if ($homeBannerMobileIsVideo): ?>
                                <video autoplay class="h-[360px] w-full rounded-[2rem] object-cover" loop muted playsinline src="<?= e($homeBannerMobilePreview) ?>"></video>
                            <?php else: ?>
                                <img alt="Preview mobile do banner" class="h-[360px] w-full rounded-[2rem] object-cover" src="<?= e($homeBannerMobilePreview) ?>">
                            <?php endif; ?>
                        </div>
                        <div class="space-y-3 p-5">
                            <div class="flex flex-wrap gap-3 text-xs font-bold uppercase tracking-[0.22em] text-slate-400">
                                <span class="rounded-full bg-[#f5f3f5] px-3 py-1">Preview mobile</span>
                                <?php if ((string) ($settings['home_banner_background_mobile_url'] ?? '') === ''): ?>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-500">Usando fundo desktop</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm leading-6 text-on-surface-variant">Use uma arte mais vertical para o celular. Se nada for enviado aqui, a home continua usando o banner desktop como fallback.</p>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary">Recomendacao: 1080 x 1440 px</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="flex justify-end">
            <button class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-bold text-white shadow-[0px_18px_36px_rgba(171,17,85,0.22)]" data-prototype-skip="1" type="submit">
                <span class="material-symbols-outlined text-base">save</span>
                Salvar SEO
            </button>
        </div>
    </form>
</main>
</body>
</html>
