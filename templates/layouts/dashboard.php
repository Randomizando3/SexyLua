<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($title ?? 'Painel') . ' - ' . $app->config['app']['name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <script defer src="<?= e(asset('js/app.js')) ?>"></script>
</head>
<body class="dashboard-body">
<div class="dashboard-shell">
    <?php include base_path('templates/partials/dashboard_sidebar.php'); ?>
    <div class="dashboard-content">
        <header class="dashboard-topbar">
            <div>
                <p class="eyebrow"><?= e(ucfirst((string) ($sidebar_role ?? 'painel'))) ?></p>
                <h1><?= e($title ?? 'Painel') ?></h1>
            </div>
            <div class="topbar-actions">
                <a class="button button-ghost" href="/">Ver site</a>
                <div class="user-chip">
                    <span class="avatar"><?= e(avatar_initials($app->auth->user()['name'] ?? 'SL')) ?></span>
                    <div>
                        <strong><?= e($app->auth->user()['name'] ?? 'Usuario') ?></strong>
                        <span><?= e($app->auth->user()['role'] ?? '') ?></span>
                    </div>
                </div>
            </div>
        </header>
        <main class="dashboard-main">
            <?php include base_path('templates/partials/flash.php'); ?>
            <?= $content ?>
        </main>
    </div>
</div>
</body>
</html>
