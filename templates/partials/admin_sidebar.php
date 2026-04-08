<?php

declare(strict_types=1);

$adminSidebarCurrent = (string) ($adminSidebarCurrent ?? 'dashboard');
$adminSidebarSettings = $app->repository->settings();
$adminSidebarMetricTitle = (string) ($adminSidebarMetricTitle ?? 'LuaCoin hoje');
$adminSidebarMetricValue = (string) ($adminSidebarMetricValue ?? brl_amount((float) ($adminSidebarSettings['luacoin_price_brl'] ?? 0.07)));
$adminSidebarMetricDescription = (string) ($adminSidebarMetricDescription ?? 'Referencia rapida para operacao e leitura do painel.');
$adminSidebarLinks = [
    'dashboard' => ['href' => '/admin', 'icon' => 'dashboard', 'label' => 'Painel'],
    'users' => ['href' => '/admin/users', 'icon' => 'group', 'label' => 'Usuarios'],
    'messages' => ['href' => '/admin/messages', 'icon' => 'campaign', 'label' => 'Comunicados'],
    'moderation' => ['href' => '/admin/moderation', 'icon' => 'gavel', 'label' => 'Moderacao'],
    'finance' => ['href' => '/admin/finance', 'icon' => 'payments', 'label' => 'Financeiro'],
    'operations' => ['href' => '/admin/operations', 'icon' => 'manufacturing', 'label' => 'Operacoes'],
    'settings' => ['href' => '/admin/settings', 'icon' => 'settings', 'label' => 'Configuracoes'],
    'integrations' => ['href' => '/admin/integrations', 'icon' => 'hub', 'label' => 'Integracoes'],
    'seo' => ['href' => '/admin/seo', 'icon' => 'travel_explore', 'label' => 'SEO'],
];
?>
<aside class="fixed left-0 top-16 hidden h-[calc(100vh-64px)] w-64 flex-col bg-[#f5f3f5] p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] lg:flex">
    <nav class="space-y-2">
        <?php foreach ($adminSidebarLinks as $sidebarKey => $sidebarLink): ?>
            <?php $isActive = $adminSidebarCurrent === $sidebarKey; ?>
            <a class="flex items-center gap-4 rounded-full px-4 py-3 transition-colors <?= $isActive ? 'bg-white font-bold text-primary' : 'text-slate-500 hover:bg-white/60' ?>" href="<?= e((string) $sidebarLink['href']) ?>">
                <span class="material-symbols-outlined"><?= e((string) $sidebarLink['icon']) ?></span>
                <span><?= e((string) $sidebarLink['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="mt-auto rounded-3xl bg-white p-5 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary"><?= e($adminSidebarMetricTitle) ?></p>
        <h3 class="mt-3 text-3xl font-extrabold"><?= e($adminSidebarMetricValue) ?></h3>
        <p class="mt-2 text-sm text-on-surface-variant"><?= e($adminSidebarMetricDescription) ?></p>
    </div>
</aside>
