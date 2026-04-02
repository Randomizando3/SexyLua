<?php

declare(strict_types=1);

$appTopbarUser = is_array($appTopbarUser ?? null) ? $appTopbarUser : [];
$appTopbarRole = (string) ($appTopbarRole ?? ($appTopbarUser['role'] ?? 'subscriber'));
$appTopbarSearch = (string) ($appTopbarSearch ?? '');
$appTopbarAction = is_array($appTopbarAction ?? null) ? $appTopbarAction : null;
$appTopbarSettingsHref = (string) ($appTopbarSettingsHref ?? user_settings_route($appTopbarUser));
$appTopbarAccountLabel = (string) ($appTopbarAccountLabel ?? 'Perfil');
$appTopbarMessagesHref = (string) ($appTopbarMessagesHref ?? match ($appTopbarRole) {
    'admin' => '/admin/messages',
    'creator' => '/creator/messages',
    default => '/subscriber/messages',
});
$appTopbarNotificationsHref = (string) ($appTopbarNotificationsHref ?? match ($appTopbarRole) {
    'admin' => '/admin',
    'creator' => '/creator',
    default => '/subscriber',
});
$appTopbarFeeds = $app->repository->topbarFeedDataForUser((int) ($appTopbarUser['id'] ?? 0));
$appTopbarNotifications = is_array($appTopbarFeeds['notifications'] ?? null) ? $appTopbarFeeds['notifications'] : ['items' => [], 'latest_marker' => 0];
$appTopbarMessages = is_array($appTopbarFeeds['messages'] ?? null) ? $appTopbarFeeds['messages'] : ['items' => [], 'latest_marker' => 0];
$appTopbarNavItems = [
    ['href' => '/', 'label' => 'Home'],
    ['href' => '/explore', 'label' => 'Explorar'],
];
$appTopbarSidebarItems = is_array($appTopbarSidebarItems ?? null) ? $appTopbarSidebarItems : match ($appTopbarRole) {
    'admin' => [
        ['href' => '/admin', 'label' => 'Dashboard', 'icon' => 'dashboard'],
        ['href' => '/admin/users', 'label' => 'Usuarios', 'icon' => 'group'],
        ['href' => '/admin/moderation', 'label' => 'Moderacao', 'icon' => 'policy'],
        ['href' => '/admin/finance', 'label' => 'Financeiro', 'icon' => 'monitoring'],
        ['href' => '/admin/operations', 'label' => 'Operacoes', 'icon' => 'dataset'],
        ['href' => '/admin/messages', 'label' => 'Mensagens', 'icon' => 'chat'],
        ['href' => '/admin/settings#seo', 'label' => 'SEO', 'icon' => 'travel_explore'],
        ['href' => '/admin/settings#perfil', 'label' => 'Perfil', 'icon' => 'settings'],
    ],
    'creator' => [
        ['href' => '/creator', 'label' => 'Metricas', 'icon' => 'insights'],
        ['href' => '/profile?id=' . (int) ($appTopbarUser['id'] ?? 0), 'label' => 'Minha Pagina', 'icon' => 'public'],
        ['href' => '/creator/content', 'label' => 'Meu Conteudo', 'icon' => 'movie'],
        ['href' => '/creator/messages', 'label' => 'Mensagens', 'icon' => 'chat'],
        ['href' => '/creator/live', 'label' => 'Configurar Live', 'icon' => 'settings_input_antenna'],
        ['href' => '/creator/memberships', 'label' => 'Minhas Assinaturas', 'icon' => 'star'],
        ['href' => '/creator/favorites', 'label' => 'Favoritos', 'icon' => 'favorite'],
        ['href' => '/creator/wallet', 'label' => 'Carteira', 'icon' => 'account_balance_wallet'],
        ['href' => '/creator/settings', 'label' => 'Configuracoes', 'icon' => 'settings'],
    ],
    default => [
        ['href' => '/subscriber', 'label' => 'Inicio', 'icon' => 'home'],
        ['href' => '/subscriber/subscriptions', 'label' => 'Minhas Assinaturas', 'icon' => 'stars'],
        ['href' => '/subscriber/favorites', 'label' => 'Favoritos', 'icon' => 'favorite'],
        ['href' => '/subscriber/messages', 'label' => 'Mensagens', 'icon' => 'chat'],
        ['href' => '/subscriber/wallet', 'label' => 'Carteira', 'icon' => 'account_balance_wallet'],
        ['href' => '/subscriber/settings', 'label' => 'Configuracoes', 'icon' => 'settings'],
    ],
};

$appTopbarSidebarItems = array_values(array_filter(
    $appTopbarSidebarItems,
    static fn (array $item): bool => (string) ($item['href'] ?? '') !== '/profile?id=0'
));
$appTopbarMobileSidebarItems = $appTopbarSidebarItems;

if ($appTopbarRole === 'subscriber') {
    $appTopbarMobileSidebarItems = array_values(array_filter(
        $appTopbarMobileSidebarItems,
        static fn (array $item): bool => ! in_array((string) ($item['href'] ?? ''), ['/subscriber/wallet'], true)
    ));
}

if (! defined('SEXYLUA_APP_TOPBAR_INCLUDED')) {
    define('SEXYLUA_APP_TOPBAR_INCLUDED', true);
}
$appTopbarVerificationStatus = trim((string) ($appTopbarUser['verification_status'] ?? ''));
$appTopbarShowVerificationNotice = in_array($appTopbarRole, ['creator', 'subscriber'], true)
    && $appTopbarVerificationStatus !== ''
    && $appTopbarVerificationStatus !== 'approved';
$appTopbarVerificationMessage = $appTopbarVerificationStatus === 'rejected'
    ? 'Usuario nao verificado. Reenvie sua documentacao no perfil. A aprovacao pode durar ate 48h.'
    : 'Usuario nao verificado. A aprovacao pode durar ate 48h.';
?>
<header class="fixed top-0 z-[60] flex h-16 w-full items-center justify-between bg-[#D81B60] px-4 font-['Plus_Jakarta_Sans'] font-bold tracking-wide text-white shadow-lg shadow-[#D81B60]/20 sm:px-6 lg:pr-8">
    <div class="flex min-w-0 items-center gap-4 lg:gap-8">
        <a class="shrink-0" href="/"><?= brand_logo_white('h-8 w-auto') ?></a>
        <nav class="hidden items-center gap-5 text-[11px] uppercase tracking-[0.22em] md:flex">
            <?php foreach ($appTopbarNavItems as $item): ?>
                <?php $active = current_path() === (string) ($item['href'] ?? '/'); ?>
                <a class="<?= $active ? 'text-white' : 'text-white/75 transition-opacity hover:text-white' ?>" href="<?= e((string) ($item['href'] ?? '/')) ?>">
                    <?= e((string) ($item['label'] ?? 'Link')) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <?php if ($appTopbarSearch !== ''): ?>
        <div class="hidden min-w-0 flex-1 px-6 xl:flex">
            <div class="w-full max-w-xl"><?= $appTopbarSearch ?></div>
        </div>
    <?php else: ?>
        <div class="hidden flex-1 xl:block"></div>
    <?php endif; ?>

    <div class="hidden items-center gap-2 sm:gap-3 md:flex">
        <?php if ($appTopbarAction !== null): ?>
            <a class="hidden rounded-full border border-white/20 px-4 py-2 text-xs font-bold uppercase tracking-widest transition-colors hover:bg-white/10 sm:inline-flex" href="<?= e((string) ($appTopbarAction['href'] ?? '#')) ?>">
                <?= e((string) ($appTopbarAction['label'] ?? 'Abrir')) ?>
            </a>
        <?php endif; ?>

        <details class="relative" data-feed-menu data-feed-kind="messages" data-feed-storage-key="<?= e('sexylua-feed:' . $appTopbarRole . ':' . (int) ($appTopbarUser['id'] ?? 0) . ':messages') ?>" data-feed-latest-marker="<?= e((string) ((int) ($appTopbarMessages['latest_marker'] ?? 0))) ?>">
            <summary class="relative flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-full border border-white/20 bg-white/10 text-white transition-colors hover:bg-white/15 marker:content-none">
                <span class="material-symbols-outlined text-[20px]">chat</span>
                <span class="absolute -right-1 -top-1 hidden min-w-[1.25rem] rounded-full bg-[#1b1c1d] px-1.5 py-0.5 text-center text-[10px] font-extrabold leading-none text-white" data-feed-badge></span>
            </summary>
            <div class="absolute right-0 top-[calc(100%+0.75rem)] z-[90] w-[22rem] max-w-[calc(100vw-2rem)] rounded-3xl border border-white/10 bg-white p-4 text-slate-700 shadow-[0px_24px_48px_rgba(27,28,29,0.18)]">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-1 pb-3">
                    <div>
                        <p class="text-sm font-extrabold text-slate-900">Mensagens</p>
                        <p class="mt-1 text-xs text-slate-500"><?= e($appTopbarRole === 'admin' ? 'Comunicados e envios recentes.' : 'Conversas recentes da sua conta.') ?></p>
                    </div>
                    <a class="rounded-full bg-[#f7f4f7] px-3 py-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#D81B60]" href="<?= e($appTopbarMessagesHref) ?>">Abrir</a>
                </div>
                <div class="mt-3 max-h-[22rem] space-y-2 overflow-y-auto pr-1" data-feed-items>
                    <?php foreach ((array) ($appTopbarMessages['items'] ?? []) as $item): ?>
                        <a class="flex items-start gap-3 rounded-2xl px-3 py-3 transition-colors hover:bg-slate-50" data-feed-item-marker="<?= e((string) ((int) ($item['marker'] ?? 0))) ?>" href="<?= e((string) ($item['href'] ?? $appTopbarMessagesHref)) ?>">
                            <span class="material-symbols-outlined mt-0.5 text-lg text-[#D81B60]"><?= e((string) ($item['icon'] ?? 'chat')) ?></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-bold text-slate-900"><?= e((string) ($item['title'] ?? 'Mensagem')) ?></span>
                                <span class="mt-1 block text-sm text-slate-500"><?= e((string) ($item['body'] ?? '')) ?></span>
                                <span class="mt-2 block text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400"><?= e((string) ($item['time'] ?? 'Agora')) ?></span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                    <?php if (((array) ($appTopbarMessages['items'] ?? [])) === []): ?>
                        <div class="rounded-2xl bg-[#f7f4f7] px-4 py-5 text-sm text-slate-500">Nenhuma mensagem nova por aqui.</div>
                    <?php endif; ?>
                </div>
            </div>
        </details>

        <details class="relative" data-feed-menu data-feed-kind="notifications" data-feed-storage-key="<?= e('sexylua-feed:' . $appTopbarRole . ':' . (int) ($appTopbarUser['id'] ?? 0) . ':notifications') ?>" data-feed-latest-marker="<?= e((string) ((int) ($appTopbarNotifications['latest_marker'] ?? 0))) ?>">
            <summary class="relative flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-full border border-white/20 bg-white/10 text-white transition-colors hover:bg-white/15 marker:content-none">
                <span class="material-symbols-outlined text-[20px]">notifications</span>
                <span class="absolute -right-1 -top-1 hidden min-w-[1.25rem] rounded-full bg-[#1b1c1d] px-1.5 py-0.5 text-center text-[10px] font-extrabold leading-none text-white" data-feed-badge></span>
            </summary>
            <div class="absolute right-0 top-[calc(100%+0.75rem)] z-[90] w-[22rem] max-w-[calc(100vw-2rem)] rounded-3xl border border-white/10 bg-white p-4 text-slate-700 shadow-[0px_24px_48px_rgba(27,28,29,0.18)]">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-1 pb-3">
                    <div>
                        <p class="text-sm font-extrabold text-slate-900">Notificacoes</p>
                        <p class="mt-1 text-xs text-slate-500">Alertas relevantes para sua area.</p>
                    </div>
                    <a class="rounded-full bg-[#f7f4f7] px-3 py-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#D81B60]" href="<?= e($appTopbarNotificationsHref) ?>">Abrir</a>
                </div>
                <div class="mt-3 max-h-[22rem] space-y-2 overflow-y-auto pr-1" data-feed-items>
                    <?php foreach ((array) ($appTopbarNotifications['items'] ?? []) as $item): ?>
                        <a class="flex items-start gap-3 rounded-2xl px-3 py-3 transition-colors hover:bg-slate-50" data-feed-item-marker="<?= e((string) ((int) ($item['marker'] ?? 0))) ?>" href="<?= e((string) ($item['href'] ?? $appTopbarNotificationsHref)) ?>">
                            <span class="material-symbols-outlined mt-0.5 text-lg text-[#D81B60]"><?= e((string) ($item['icon'] ?? 'notifications')) ?></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-bold text-slate-900"><?= e((string) ($item['title'] ?? 'Atualizacao')) ?></span>
                                <span class="mt-1 block text-sm text-slate-500"><?= e((string) ($item['body'] ?? '')) ?></span>
                                <span class="mt-2 block text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400"><?= e((string) ($item['time'] ?? 'Agora')) ?></span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                    <?php if (((array) ($appTopbarNotifications['items'] ?? [])) === []): ?>
                        <div class="rounded-2xl bg-[#f7f4f7] px-4 py-5 text-sm text-slate-500">Nenhuma notificacao nova por aqui.</div>
                    <?php endif; ?>
                </div>
            </div>
        </details>

        <?php
        $accountMenuUser = $appTopbarUser;
        $accountMenuSettingsHref = $appTopbarSettingsHref;
        $accountMenuLabel = $appTopbarAccountLabel;
        require BASE_PATH . '/templates/partials/account_menu.php';
        ?>
    </div>

    <div class="flex items-center gap-2 md:hidden">
        <details class="relative" data-feed-menu data-feed-kind="messages" data-feed-storage-key="<?= e('sexylua-feed:' . $appTopbarRole . ':' . (int) ($appTopbarUser['id'] ?? 0) . ':messages') ?>" data-feed-latest-marker="<?= e((string) ((int) ($appTopbarMessages['latest_marker'] ?? 0))) ?>">
            <summary class="relative flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-full border border-white/20 bg-white/10 text-white marker:content-none">
                <span class="material-symbols-outlined text-[20px]">chat</span>
                <span class="absolute -right-1 -top-1 hidden min-w-[1.25rem] rounded-full bg-[#1b1c1d] px-1.5 py-0.5 text-center text-[10px] font-extrabold leading-none text-white" data-feed-badge></span>
            </summary>
            <div class="absolute right-0 top-[calc(100%+0.75rem)] z-[90] w-[20rem] max-w-[calc(100vw-2rem)] rounded-3xl border border-white/10 bg-white p-4 text-slate-700 shadow-[0px_24px_48px_rgba(27,28,29,0.18)]">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-1 pb-3">
                    <div>
                        <p class="text-sm font-extrabold text-slate-900">Mensagens</p>
                        <p class="mt-1 text-xs text-slate-500"><?= e($appTopbarRole === 'admin' ? 'Comunicados e envios recentes.' : 'Conversas recentes da sua conta.') ?></p>
                    </div>
                    <a class="rounded-full bg-[#f7f4f7] px-3 py-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#D81B60]" href="<?= e($appTopbarMessagesHref) ?>">Abrir</a>
                </div>
                <div class="mt-3 max-h-[22rem] space-y-2 overflow-y-auto pr-1" data-feed-items>
                    <?php foreach ((array) ($appTopbarMessages['items'] ?? []) as $item): ?>
                        <a class="flex items-start gap-3 rounded-2xl px-3 py-3 transition-colors hover:bg-slate-50" data-feed-item-marker="<?= e((string) ((int) ($item['marker'] ?? 0))) ?>" href="<?= e((string) ($item['href'] ?? $appTopbarMessagesHref)) ?>">
                            <span class="material-symbols-outlined mt-0.5 text-lg text-[#D81B60]"><?= e((string) ($item['icon'] ?? 'chat')) ?></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-bold text-slate-900"><?= e((string) ($item['title'] ?? 'Mensagem')) ?></span>
                                <span class="mt-1 block text-sm text-slate-500"><?= e((string) ($item['body'] ?? '')) ?></span>
                                <span class="mt-2 block text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400"><?= e((string) ($item['time'] ?? 'Agora')) ?></span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                    <?php if (((array) ($appTopbarMessages['items'] ?? [])) === []): ?>
                        <div class="rounded-2xl bg-[#f7f4f7] px-4 py-5 text-sm text-slate-500">Nenhuma mensagem nova por aqui.</div>
                    <?php endif; ?>
                </div>
            </div>
        </details>

        <details class="relative" data-feed-menu data-feed-kind="notifications" data-feed-storage-key="<?= e('sexylua-feed:' . $appTopbarRole . ':' . (int) ($appTopbarUser['id'] ?? 0) . ':notifications') ?>" data-feed-latest-marker="<?= e((string) ((int) ($appTopbarNotifications['latest_marker'] ?? 0))) ?>">
            <summary class="relative flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-full border border-white/20 bg-white/10 text-white marker:content-none">
                <span class="material-symbols-outlined text-[20px]">notifications</span>
                <span class="absolute -right-1 -top-1 hidden min-w-[1.25rem] rounded-full bg-[#1b1c1d] px-1.5 py-0.5 text-center text-[10px] font-extrabold leading-none text-white" data-feed-badge></span>
            </summary>
            <div class="absolute right-0 top-[calc(100%+0.75rem)] z-[90] w-[20rem] max-w-[calc(100vw-2rem)] rounded-3xl border border-white/10 bg-white p-4 text-slate-700 shadow-[0px_24px_48px_rgba(27,28,29,0.18)]">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-1 pb-3">
                    <div>
                        <p class="text-sm font-extrabold text-slate-900">Notificacoes</p>
                        <p class="mt-1 text-xs text-slate-500">Alertas relevantes para sua area.</p>
                    </div>
                    <a class="rounded-full bg-[#f7f4f7] px-3 py-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#D81B60]" href="<?= e($appTopbarNotificationsHref) ?>">Abrir</a>
                </div>
                <div class="mt-3 max-h-[22rem] space-y-2 overflow-y-auto pr-1" data-feed-items>
                    <?php foreach ((array) ($appTopbarNotifications['items'] ?? []) as $item): ?>
                        <a class="flex items-start gap-3 rounded-2xl px-3 py-3 transition-colors hover:bg-slate-50" data-feed-item-marker="<?= e((string) ((int) ($item['marker'] ?? 0))) ?>" href="<?= e((string) ($item['href'] ?? $appTopbarNotificationsHref)) ?>">
                            <span class="material-symbols-outlined mt-0.5 text-lg text-[#D81B60]"><?= e((string) ($item['icon'] ?? 'notifications')) ?></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-bold text-slate-900"><?= e((string) ($item['title'] ?? 'Atualizacao')) ?></span>
                                <span class="mt-1 block text-sm text-slate-500"><?= e((string) ($item['body'] ?? '')) ?></span>
                                <span class="mt-2 block text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400"><?= e((string) ($item['time'] ?? 'Agora')) ?></span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                    <?php if (((array) ($appTopbarNotifications['items'] ?? [])) === []): ?>
                        <div class="rounded-2xl bg-[#f7f4f7] px-4 py-5 text-sm text-slate-500">Nenhuma notificacao nova por aqui.</div>
                    <?php endif; ?>
                </div>
            </div>
        </details>

        <details class="relative" data-mobile-nav>
            <summary class="flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-full border border-white/20 bg-white/10 text-white marker:content-none">
                <span class="material-symbols-outlined text-[22px]" data-mobile-nav-icon>menu</span>
            </summary>
            <div class="absolute right-0 top-[calc(100%+0.75rem)] z-[90] max-h-[calc(100dvh-6rem)] w-80 max-w-[calc(100vw-2rem)] overflow-y-auto overscroll-contain rounded-3xl bg-white p-4 pb-6 text-slate-700 shadow-[0px_24px_48px_rgba(27,28,29,0.18)]">
            <div class="space-y-2">
                <?php foreach (array_filter($appTopbarNavItems, static fn (array $item): bool => (string) ($item['href'] ?? '') !== '/') as $item): ?>
                    <a class="block rounded-2xl px-4 py-3 text-sm font-bold <?= current_path() === (string) ($item['href'] ?? '/') ? 'bg-[#f7f4f7] text-[#D81B60]' : 'text-slate-700 hover:bg-[#f7f4f7]' ?>" href="<?= e((string) ($item['href'] ?? '/')) ?>">
                        <?= e((string) ($item['label'] ?? 'Link')) ?>
                    </a>
                <?php endforeach; ?>

                <?php if ($appTopbarAction !== null): ?>
                    <a class="block rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 hover:bg-[#f7f4f7]" href="<?= e((string) ($appTopbarAction['href'] ?? '#')) ?>">
                        <?= e((string) ($appTopbarAction['label'] ?? 'Abrir')) ?>
                    </a>
                <?php endif; ?>

                <?php foreach ($appTopbarMobileSidebarItems as $item): ?>
                    <a class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold <?= current_path() === (string) ($item['href'] ?? '') ? 'bg-[#f7f4f7] text-[#D81B60]' : 'text-slate-700 hover:bg-[#f7f4f7]' ?>" href="<?= e((string) ($item['href'] ?? '#')) ?>">
                        <span class="material-symbols-outlined text-[20px]"><?= e((string) ($item['icon'] ?? 'chevron_right')) ?></span>
                        <span><?= e((string) ($item['label'] ?? 'Atalho')) ?></span>
                    </a>
                <?php endforeach; ?>

                <form action="/logout" method="post">
                    <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                    <button class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-left text-sm font-bold text-slate-700 hover:bg-[#f7f4f7]" data-prototype-skip="1" type="submit">
                        <span class="material-symbols-outlined text-[20px]">logout</span>
                        <span>Sair</span>
                    </button>
                </form>
            </div>
            </div>
        </details>
    </div>
</header>
<?php if ($appTopbarShowVerificationNotice): ?>
    <div class="fixed inset-x-0 bottom-0 z-[58] border-t border-amber-300 bg-amber-100/95 px-4 py-2 text-center text-[11px] font-bold tracking-[0.15em] text-amber-900 backdrop-blur-sm sm:text-xs">
        <a class="inline-flex items-center justify-center gap-2 hover:underline" href="<?= e($appTopbarSettingsHref) ?>">
            <span class="material-symbols-outlined text-[16px]">verified_user</span>
            <span><?= e($appTopbarVerificationMessage) ?></span>
        </a>
    </div>
<?php endif; ?>
<script>
    (() => {
        const href = <?= json_encode(site_favicon_url(), JSON_UNESCAPED_SLASHES) ?>;
        if (!href) {
            return;
        }

        let icon = document.querySelector('link[rel="icon"]');
        if (!icon) {
            icon = document.createElement('link');
            icon.rel = 'icon';
            document.head.appendChild(icon);
        }

        icon.href = href;
    })();
</script>
<?php if (! defined('SEXYLUA_APP_TOPBAR_SCRIPT_INCLUDED')): ?>
    <?php define('SEXYLUA_APP_TOPBAR_SCRIPT_INCLUDED', true); ?>
    <script src="<?= e(asset('js/app-topbar.js')) ?>"></script>
<?php endif; ?>
