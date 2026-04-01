<?php

declare(strict_types=1);

$creatorShellCreator = $creatorShellCreator ?? ($creator ?? []);
$creatorShellCurrent = (string) ($creatorShellCurrent ?? 'dashboard');
$creatorShellCta = $creatorShellCta ?? null;
$creatorMenuItems = [
    ['key' => 'dashboard', 'href' => '/creator', 'icon' => 'insights', 'label' => 'Metricas Lunares'],
    ['key' => 'public_profile', 'href' => '/profile?id=' . (int) ($creatorShellCreator['id'] ?? 0), 'icon' => 'public', 'label' => 'Pagina Publica'],
    ['key' => 'content', 'href' => '/creator/content', 'icon' => 'movie', 'label' => 'Meu Conteudo'],
    ['key' => 'live', 'href' => '/creator/live', 'icon' => 'settings_input_antenna', 'label' => 'Configurar Live'],
    ['key' => 'memberships', 'href' => '/creator/memberships', 'icon' => 'star', 'label' => 'Minhas Assinaturas'],
    ['key' => 'favorites', 'href' => '/creator/favorites', 'icon' => 'favorite', 'label' => 'Favoritos'],
    ['key' => 'wallet', 'href' => '/creator/wallet', 'icon' => 'account_balance_wallet', 'label' => 'Carteira'],
    ['key' => 'settings', 'href' => '/creator/settings', 'icon' => 'settings', 'label' => 'Configuracoes'],
];
$creatorMenuItems = array_values(array_filter($creatorMenuItems, static fn (array $item): bool => $item['key'] !== 'public_profile' || (int) ($creatorShellCreator['id'] ?? 0) > 0));
?>
<aside class="fixed left-0 top-0 z-50 hidden h-full w-64 flex-col rounded-r-[3rem] bg-zinc-50 px-4 pb-6 pt-20 font-['Plus_Jakarta_Sans'] font-medium shadow-xl lg:flex">
    <nav class="flex-1 space-y-1">
        <?php foreach ($creatorMenuItems as $item): ?>
            <?php $active = $creatorShellCurrent === $item['key']; ?>
            <a class="mx-2 flex items-center gap-3 rounded-full px-4 py-3 transition-colors <?= $active ? 'bg-pink-50 text-pink-700' : 'text-zinc-600 hover:bg-zinc-100' ?>" href="<?= e((string) $item['href']) ?>">
                <span class="material-symbols-outlined"<?= $active ? ' style="font-variation-settings: \'FILL\' 1;"' : '' ?>><?= e((string) $item['icon']) ?></span>
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
        <form action="/logout" class="mx-2" method="post">
            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
            <button class="flex w-full items-center gap-3 rounded-full bg-white px-4 py-3 text-sm font-bold text-zinc-700 shadow-sm transition-colors hover:bg-zinc-100" data-prototype-skip="1" type="submit">
                <span class="material-symbols-outlined">logout</span>
                Sair
            </button>
        </form>
    </div>
</aside>
