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
<div class="lg:hidden">
    <div class="fixed inset-0 z-[65] hidden bg-black/35 backdrop-blur-[1px]" data-mobile-sidebar-backdrop="subscriber"></div>
    <button class="fixed bottom-6 left-1/2 z-[75] flex h-14 w-14 -translate-x-1/2 items-center justify-center rounded-full bg-[#D81B60] text-white shadow-[0px_18px_35px_rgba(216,27,96,0.35)] transition-transform hover:scale-105" data-mobile-sidebar-toggle="subscriber" type="button">
        <span class="material-symbols-outlined" data-mobile-sidebar-icon="subscriber">menu</span>
    </button>
</div>

<aside class="fixed left-4 right-4 top-20 z-[70] hidden max-h-[calc(100vh-7rem)] flex-col overflow-y-auto rounded-[2rem] bg-[#f5f3f5] p-6 shadow-[0px_20px_40px_rgba(27,28,29,0.16)] lg:bottom-auto lg:left-0 lg:right-auto lg:top-16 lg:flex lg:h-[calc(100vh-64px)] lg:max-h-none lg:w-64 lg:rounded-none lg:rounded-r-[3rem] lg:shadow-[0px_20px_40px_rgba(27,28,29,0.06)]" data-mobile-sidebar-panel="subscriber">
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

<script>
    (() => {
        if (window.__sexyLuaMobileSidebarInit?.includes('subscriber')) {
            return;
        }

        window.__sexyLuaMobileSidebarInit = Array.isArray(window.__sexyLuaMobileSidebarInit)
            ? window.__sexyLuaMobileSidebarInit
            : [];
        window.__sexyLuaMobileSidebarInit.push('subscriber');

        const toggle = document.querySelector('[data-mobile-sidebar-toggle="subscriber"]');
        const panel = document.querySelector('[data-mobile-sidebar-panel="subscriber"]');
        const backdrop = document.querySelector('[data-mobile-sidebar-backdrop="subscriber"]');
        const icon = document.querySelector('[data-mobile-sidebar-icon="subscriber"]');

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
