<?php

declare(strict_types=1);

$creatorShellCreator = $creatorShellCreator ?? ($creator ?? []);
$creatorTopbarSearch = (string) ($creatorTopbarSearch ?? '');
$creatorTopbarAction = is_array($creatorTopbarAction ?? null) ? $creatorTopbarAction : null;
?>
<header class="fixed top-0 z-[60] flex h-16 w-full items-center justify-between bg-[#D81B60] px-6 font-['Plus_Jakarta_Sans'] font-bold tracking-wide text-white shadow-lg shadow-[#D81B60]/20 lg:pr-8">
    <div class="flex items-center">
        <a class="block" href="/"><?= brand_logo_white('h-8 w-auto') ?></a>
    </div>

    <?php if ($creatorTopbarSearch !== ''): ?>
        <div class="hidden flex-1 justify-center px-6 lg:flex">
            <div class="w-full max-w-xl"><?= $creatorTopbarSearch ?></div>
        </div>
    <?php else: ?>
        <div class="hidden flex-1 lg:block"></div>
    <?php endif; ?>

    <div class="flex items-center gap-3">
        <?php if ($creatorTopbarAction): ?>
            <a class="rounded-full border border-white/20 px-4 py-2 text-xs font-bold uppercase tracking-widest transition-colors hover:bg-white/10" href="<?= e((string) ($creatorTopbarAction['href'] ?? '/creator')) ?>">
                <?= e((string) ($creatorTopbarAction['label'] ?? 'Abrir')) ?>
            </a>
        <?php endif; ?>
        <?php
        $accountMenuUser = $creatorShellCreator;
        $accountMenuSettingsHref = '/creator/settings';
        $accountMenuLabel = 'Perfil do criador';
        require BASE_PATH . '/templates/partials/account_menu.php';
        ?>
    </div>
</header>
