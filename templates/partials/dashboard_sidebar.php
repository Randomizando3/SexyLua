<?php
$role = (string) ($sidebar_role ?? 'subscriber');
$menu = match ($role) {
    'admin' => [
        ['/admin', 'Dashboard Admin'],
        ['/admin/users', 'Gestao de Usuarios'],
        ['/admin/moderation', 'Moderacao de Conteudo'],
        ['/admin/finance', 'Relatorios Financeiros'],
        ['/admin/settings', 'Configuracoes do Sistema'],
    ],
    'creator' => [
        ['/creator', 'Dashboard do Criador'],
        ['/creator/content', 'Gestao de Conteudo'],
        ['/creator/memberships', 'Gestao de Assinaturas'],
        ['/creator/live', 'Configuracao de Live'],
        ['/creator/wallet', 'Carteira Lunar'],
    ],
    default => [
        ['/subscriber', 'Area do Assinante'],
        ['/subscriber/subscriptions', 'Minhas Assinaturas'],
        ['/subscriber/favorites', 'Favoritos e Salvos'],
        ['/subscriber/messages', 'Mensagens e Chat'],
        ['/subscriber/wallet', 'Carteira e LuaCoins'],
    ],
};
?>
<aside class="dashboard-sidebar">
    <div class="sidebar-brand">
        <a href="/" class="brand-mark">SexyLua</a>
        <p><?= e(ucfirst($role)) ?></p>
    </div>
    <nav class="sidebar-nav">
        <?php foreach ($menu as [$href, $label]) : ?>
            <a href="<?= e($href) ?>" class="<?= is_active_path($href) ? 'active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>
</aside>
