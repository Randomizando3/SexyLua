<?php $currentUser = $app->auth->user(); ?>
<header class="marketing-header">
    <div class="brand-block">
        <a href="/" class="brand-mark">SexyLua</a>
        <button class="nav-toggle" type="button" data-toggle-nav>Menu</button>
    </div>
    <nav class="marketing-nav" data-nav-menu>
        <a class="<?= is_active_path('/') ? 'active' : '' ?>" href="/">Home</a>
        <a class="<?= is_active_path('/explore') ? 'active' : '' ?>" href="/explore">Explorar</a>
        <?php if ($currentUser && $currentUser['role'] === 'subscriber') : ?>
            <a href="/subscriber">Assinante</a>
        <?php endif; ?>
        <?php if ($currentUser && $currentUser['role'] === 'creator') : ?>
            <a href="/creator">Criador</a>
        <?php endif; ?>
        <?php if ($currentUser && $currentUser['role'] === 'admin') : ?>
            <a href="/admin">Admin</a>
        <?php endif; ?>
    </nav>
    <div class="marketing-actions">
        <?php if ($currentUser) : ?>
            <span class="user-chip compact">
                <span class="avatar"><?= e(avatar_initials($currentUser['name'])) ?></span>
                <span><?= e($currentUser['name']) ?></span>
            </span>
            <form action="/logout" method="post">
                <input type="hidden" name="_token" value="<?= e($app->csrf->token()) ?>">
                <button class="button button-secondary" type="submit">Sair</button>
            </form>
        <?php else : ?>
            <a class="button button-ghost" href="/login">Login</a>
            <a class="button button-primary" href="/register">Registro</a>
        <?php endif; ?>
    </div>
</header>
