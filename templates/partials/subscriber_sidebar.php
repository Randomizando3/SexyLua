<?php

declare(strict_types=1);

$subscriberSidebarCurrent = (string) ($subscriberSidebarCurrent ?? 'dashboard');
$subscriberSidebarFooter = (string) ($subscriberSidebarFooter ?? '');
$subscriberMenuItems = [
    ['key' => 'dashboard', 'href' => '/subscriber', 'icon' => 'home', 'label' => 'Inicio'],
    ['key' => 'subscriptions', 'href' => '/subscriber/subscriptions', 'icon' => 'stars', 'label' => 'Minhas Assinaturas'],
    ['key' => 'favorites', 'href' => '/subscriber/favorites', 'icon' => 'favorite', 'label' => 'Favoritos'],
    ['key' => 'messages', 'href' => '/subscriber/messages', 'icon' => 'chat', 'label' => 'Mensagens'],
    ['key' => 'wallet', 'href' => '/subscriber/wallet', 'icon' => 'account_balance_wallet', 'label' => 'Carteira'],
    ['key' => 'settings', 'href' => '/subscriber/settings', 'icon' => 'settings', 'label' => 'Configuracoes'],
];
?>
<aside class="fixed left-0 top-16 z-40 hidden h-[calc(100vh-64px)] w-64 flex-col overflow-y-auto rounded-r-[3rem] bg-[#f5f3f5] p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.06)] lg:flex">
    <nav class="space-y-2">
        <?php foreach ($subscriberMenuItems as $item): ?>
            <?php $active = $subscriberSidebarCurrent === $item['key']; ?>
            <a class="flex items-center gap-4 rounded-full px-4 py-3 transition-colors <?= $active ? 'bg-white font-bold text-primary' : 'text-slate-500 hover:bg-white/60' ?>" href="<?= e((string) $item['href']) ?>">
                <span class="material-symbols-outlined"><?= e((string) $item['icon']) ?></span>
                <span><?= e((string) $item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <?php if ($subscriberSidebarFooter !== ''): ?>
        <div class="mt-auto"><?= $subscriberSidebarFooter ?></div>
    <?php endif; ?>
</aside>
