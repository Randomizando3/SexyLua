<?php

declare(strict_types=1);

$creatorShellCreator = $creatorShellCreator ?? ($creator ?? []);
$creatorShellCurrent = (string) ($creatorShellCurrent ?? 'dashboard');
$creatorShellCta = $creatorShellCta ?? null;
$creatorMenuItems = [
    ['key' => 'dashboard', 'href' => '/creator', 'icon' => 'insights', 'label' => 'Metricas Lunares'],
    ['key' => 'public_profile', 'href' => '/profile?id=' . (int) ($creatorShellCreator['id'] ?? 0), 'icon' => 'public', 'label' => 'Pagina Publica'],
    ['key' => 'content', 'href' => '/creator/content', 'icon' => 'movie', 'label' => 'Meu Conteudo'],
    ['key' => 'messages', 'href' => '/creator/messages', 'icon' => 'chat', 'label' => 'Mensagens'],
    ['key' => 'live', 'href' => '/creator/live', 'icon' => 'settings_input_antenna', 'label' => 'Configurar Live'],
    ['key' => 'memberships', 'href' => '/creator/memberships', 'icon' => 'star', 'label' => 'Minhas Assinaturas'],
    ['key' => 'favorites', 'href' => '/creator/favorites', 'icon' => 'favorite', 'label' => 'Favoritos'],
    ['key' => 'wallet', 'href' => '/creator/wallet', 'icon' => 'account_balance_wallet', 'label' => 'Carteira'],
    ['key' => 'settings', 'href' => '/creator/settings', 'icon' => 'settings', 'label' => 'Configuracoes'],
];
$creatorMenuItems = array_values(array_filter($creatorMenuItems, static fn (array $item): bool => $item['key'] !== 'public_profile' || (int) ($creatorShellCreator['id'] ?? 0) > 0));
?>
<div class="lg:hidden">
    <div class="fixed inset-0 z-[65] hidden bg-black/35 backdrop-blur-[1px]" data-mobile-sidebar-backdrop="creator"></div>
    <button class="fixed bottom-6 left-1/2 z-[75] flex h-14 w-14 -translate-x-1/2 items-center justify-center rounded-full bg-[#D81B60] text-white shadow-[0px_18px_35px_rgba(216,27,96,0.35)] transition-transform hover:scale-105" data-mobile-sidebar-toggle="creator" type="button">
        <span class="material-symbols-outlined" data-mobile-sidebar-icon="creator">menu</span>
    </button>
</div>

<aside class="fixed left-4 right-4 top-20 z-[70] hidden max-h-[calc(100vh-7rem)] flex-col overflow-y-auto rounded-[2rem] bg-zinc-50 px-4 pb-6 pt-6 font-['Plus_Jakarta_Sans'] font-medium shadow-[0px_20px_40px_rgba(27,28,29,0.16)] lg:bottom-0 lg:left-0 lg:right-auto lg:top-0 lg:flex lg:h-full lg:max-h-none lg:w-64 lg:rounded-none lg:rounded-r-[3rem] lg:pt-20 lg:shadow-xl" data-mobile-sidebar-panel="creator">
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
<script>
    (() => {
        if (window.__sexyLuaMobileSidebarInit?.includes('creator')) {
            return;
        }

        window.__sexyLuaMobileSidebarInit = Array.isArray(window.__sexyLuaMobileSidebarInit)
            ? window.__sexyLuaMobileSidebarInit
            : [];
        window.__sexyLuaMobileSidebarInit.push('creator');

        const toggle = document.querySelector('[data-mobile-sidebar-toggle="creator"]');
        const panel = document.querySelector('[data-mobile-sidebar-panel="creator"]');
        const backdrop = document.querySelector('[data-mobile-sidebar-backdrop="creator"]');
        const icon = document.querySelector('[data-mobile-sidebar-icon="creator"]');

        if (!toggle || !panel || !backdrop) {
            return;
        }

        const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;
        const syncState = (open) => {
            if (isDesktop()) {
                panel.classList.remove('hidden');
                backdrop.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                if (icon) {
                    icon.textContent = 'menu';
                }
                return;
            }

            panel.classList.toggle('hidden', !open);
            backdrop.classList.toggle('hidden', !open);
            document.body.classList.toggle('overflow-hidden', open);
            if (icon) {
                icon.textContent = open ? 'close' : 'menu';
            }
        };

        let open = false;
        syncState(open);

        toggle.addEventListener('click', () => {
            open = !open;
            syncState(open);
        });

        backdrop.addEventListener('click', () => {
            open = false;
            syncState(open);
        });

        window.addEventListener('resize', () => {
            if (isDesktop()) {
                open = false;
            }
            syncState(open);
        });

        panel.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                if (isDesktop()) {
                    return;
                }

                open = false;
                syncState(open);
            });
        });
    })();
</script>
