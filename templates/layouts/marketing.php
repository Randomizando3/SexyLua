<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($title ?? 'SexyLua') . ' - ' . $app->config['app']['name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <script defer src="<?= e(asset('js/app.js')) ?>"></script>
</head>
<body class="marketing-body">
<?php include base_path('templates/partials/marketing_nav.php'); ?>
<main class="marketing-main">
    <?php include base_path('templates/partials/flash.php'); ?>
    <?= $content ?>
</main>
<footer class="marketing-footer">
    <div>
        <strong>SexyLua</strong>
        <p>PHP puro, dados locais e visual inspirado no prototipo aprovado.</p>
    </div>
    <div class="footer-meta">
        <span>Publico</span>
        <span>Criador</span>
        <span>Assinante</span>
        <span>Admin</span>
    </div>
</footer>
</body>
</html>
