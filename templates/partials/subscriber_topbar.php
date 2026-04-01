<?php

declare(strict_types=1);

$subscriberTopbarUser = is_array($subscriberTopbarUser ?? null) ? $subscriberTopbarUser : [];
$subscriberTopbarAction = is_array($subscriberTopbarAction ?? null) ? $subscriberTopbarAction : null;
$subscriberTopbarNav = is_array($subscriberTopbarNav ?? null) ? $subscriberTopbarNav : [];
?>
<header class="fixed top-0 z-50 flex h-16 w-full items-center justify-between bg-[#D81B60] px-6 font-['Plus_Jakarta_Sans'] font-bold tracking-wide text-white shadow-lg shadow-[#D81B60]/20">
    <div class="flex items-center gap-4">
        <?= brand_logo_white('h-8 w-auto') ?>
        <span class="hidden border-l border-white/20 pl-4 text-xs uppercase tracking-widest opacity-80 md:block">Subscriber Club</span>
    </div>
    <?php if ($subscriberTopbarNav !== []): ?>
        <nav class="hidden items-center gap-6 text-sm md:flex">
            <?php foreach ($subscriberTopbarNav as $item): ?>
                <?php $isActive = !empty($item['active']); ?>
                <a class="<?= $isActive ? 'border-b-2 border-white py-1' : 'opacity-80 transition-opacity hover:opacity-100' ?>" href="<?= e((string) ($item['href'] ?? '/subscriber')) ?>">
                    <?= e((string) ($item['label'] ?? 'Link')) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    <?php else: ?>
        <div class="hidden flex-1 md:block"></div>
    <?php endif; ?>
    <div class="flex items-center gap-3">
        <?php if ($subscriberTopbarAction !== null): ?>
            <a class="rounded-full border border-white/20 px-4 py-2 text-xs font-bold uppercase tracking-widest transition-colors hover:bg-white/10" href="<?= e((string) ($subscriberTopbarAction['href'] ?? '/subscriber')) ?>">
                <?= e((string) ($subscriberTopbarAction['label'] ?? 'Abrir')) ?>
            </a>
        <?php endif; ?>
        <?php
        $accountMenuUser = $subscriberTopbarUser;
        $accountMenuSettingsHref = '/subscriber/settings';
        $accountMenuLabel = 'Perfil do assinante';
        require BASE_PATH . '/templates/partials/account_menu.php';
        ?>
    </div>
</header>
