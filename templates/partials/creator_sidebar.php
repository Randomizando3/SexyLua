<?php

declare(strict_types=1);

$creatorShellCreator = $creatorShellCreator ?? ($creator ?? []);
$creatorShellCurrent = (string) ($creatorShellCurrent ?? 'dashboard');
$creatorShellCta = $creatorShellCta ?? null;
$creatorAvatarUrl = media_url((string) ($creatorShellCreator['avatar_url'] ?? ''));
$creatorPublicUrl = path_with_query('/profile', ['id' => (int) ($creatorShellCreator['id'] ?? 0)]);
$creatorMenuItems = [
    ['key' => 'dashboard', 'href' => '/creator', 'icon' => 'insights', 'label' => 'Metricas Lunares'],
    ['key' => 'content', 'href' => '/creator/content', 'icon' => 'movie', 'label' => 'Meu Conteudo'],
    ['key' => 'live', 'href' => '/creator/live', 'icon' => 'settings_input_antenna', 'label' => 'Configurar Live'],
    ['key' => 'memberships', 'href' => '/creator/memberships', 'icon' => 'star', 'label' => 'Minhas Assinaturas'],
    ['key' => 'favorites', 'href' => '/creator/favorites', 'icon' => 'favorite', 'label' => 'Favoritos'],
    ['key' => 'wallet', 'href' => '/creator/wallet', 'icon' => 'account_balance_wallet', 'label' => 'Carteira'],
    ['key' => 'settings', 'href' => '/creator/settings', 'icon' => 'settings', 'label' => 'Configuracoes'],
];
$creatorAdminItems = [
    ['href' => '/admin/users', 'icon' => 'group', 'label' => 'Gestao de Usuarios'],
    ['href' => '/admin/finance', 'icon' => 'payments', 'label' => 'Financeiro'],
    ['href' => '/admin/moderation', 'icon' => 'gavel', 'label' => 'Moderacao'],
];
?>
<aside class="fixed left-0 top-0 z-50 hidden h-full w-64 flex-col rounded-r-[3rem] bg-zinc-50 px-4 pb-6 pt-24 font-['Plus_Jakarta_Sans'] font-medium shadow-xl lg:flex">
    <div class="mb-10 px-6">
        <a class="block" href="/"><?= brand_logo_magenta('h-10 w-auto') ?></a>
    </div>

    <nav class="flex-1 space-y-1">
        <?php foreach ($creatorMenuItems as $item): ?>
            <?php $active = $creatorShellCurrent === $item['key']; ?>
            <a class="mx-2 flex items-center gap-3 rounded-full px-4 py-3 transition-colors <?= $active ? 'bg-pink-50 text-pink-700' : 'text-zinc-600 hover:bg-zinc-100' ?>" href="<?= e((string) $item['href']) ?>">
                <span class="material-symbols-outlined"<?= $active ? ' style="font-variation-settings: \'FILL\' 1;"' : '' ?>><?= e((string) $item['icon']) ?></span>
                <span><?= e((string) $item['label']) ?></span>
            </a>
        <?php endforeach; ?>

        <div class="px-8 pb-4 pt-8">
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-400">Administracao</p>
        </div>

        <?php foreach ($creatorAdminItems as $item): ?>
            <a class="mx-2 flex items-center gap-3 rounded-full px-4 py-3 text-zinc-600 transition-colors hover:bg-zinc-100" href="<?= e((string) $item['href']) ?>">
                <span class="material-symbols-outlined"><?= e((string) $item['icon']) ?></span>
                <span><?= e((string) $item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php if (is_array($creatorShellCta) && ! empty($creatorShellCta['label']) && ! empty($creatorShellCta['href'])): ?>
        <div class="px-4 pb-4 pt-2">
            <a class="flex w-full items-center justify-center gap-2 rounded-full bg-[#D81B60] px-5 py-4 text-sm font-bold text-white shadow-lg shadow-[#D81B60]/20" href="<?= e((string) $creatorShellCta['href']) ?>">
                <?php if (! empty($creatorShellCta['icon'])): ?><span class="material-symbols-outlined"><?= e((string) $creatorShellCta['icon']) ?></span><?php endif; ?>
                <?= e((string) $creatorShellCta['label']) ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="mt-auto px-4 py-4">
        <a class="flex items-center gap-3 rounded-2xl bg-white p-3 shadow-sm" href="<?= e($creatorPublicUrl) ?>">
            <?php if ($creatorAvatarUrl !== ''): ?>
                <img alt="Avatar do criador" class="h-11 w-11 rounded-full object-cover" src="<?= e($creatorAvatarUrl) ?>">
            <?php else: ?>
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-primary/10 font-bold text-primary"><?= e(avatar_initials((string) ($creatorShellCreator['name'] ?? 'Criador'))) ?></div>
            <?php endif; ?>
            <div class="min-w-0">
                <p class="truncate text-sm font-bold"><?= e((string) ($creatorShellCreator['name'] ?? 'Criador')) ?></p>
                <p class="truncate text-xs text-pink-600"><?= e((string) ($creatorShellCreator['headline'] ?? 'Creator Studio ativo')) ?></p>
            </div>
        </a>
    </div>
</aside>
