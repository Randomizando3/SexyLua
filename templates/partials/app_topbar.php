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

if (! defined('SEXYLUA_APP_TOPBAR_INCLUDED')) {
    define('SEXYLUA_APP_TOPBAR_INCLUDED', true);
}
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

    <div class="flex items-center gap-2 sm:gap-3">
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
                        <p class="text-sm font-extrabold text-slate-900">Notificações</p>
                        <p class="mt-1 text-xs text-slate-500">Alertas relevantes para sua área.</p>
                    </div>
                    <a class="rounded-full bg-[#f7f4f7] px-3 py-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#D81B60]" href="<?= e($appTopbarNotificationsHref) ?>">Abrir</a>
                </div>
                <div class="mt-3 max-h-[22rem] space-y-2 overflow-y-auto pr-1" data-feed-items>
                    <?php foreach ((array) ($appTopbarNotifications['items'] ?? []) as $item): ?>
                        <a class="flex items-start gap-3 rounded-2xl px-3 py-3 transition-colors hover:bg-slate-50" data-feed-item-marker="<?= e((string) ((int) ($item['marker'] ?? 0))) ?>" href="<?= e((string) ($item['href'] ?? $appTopbarNotificationsHref)) ?>">
                            <span class="material-symbols-outlined mt-0.5 text-lg text-[#D81B60]"><?= e((string) ($item['icon'] ?? 'notifications')) ?></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-bold text-slate-900"><?= e((string) ($item['title'] ?? 'Atualização')) ?></span>
                                <span class="mt-1 block text-sm text-slate-500"><?= e((string) ($item['body'] ?? '')) ?></span>
                                <span class="mt-2 block text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400"><?= e((string) ($item['time'] ?? 'Agora')) ?></span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                    <?php if (((array) ($appTopbarNotifications['items'] ?? [])) === []): ?>
                        <div class="rounded-2xl bg-[#f7f4f7] px-4 py-5 text-sm text-slate-500">Nenhuma notificação nova por aqui.</div>
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
</header>
<?php if (! defined('SEXYLUA_APP_TOPBAR_SCRIPT_INCLUDED')): ?>
    <?php define('SEXYLUA_APP_TOPBAR_SCRIPT_INCLUDED', true); ?>
    <script src="<?= e(asset('js/app-topbar.js')) ?>"></script>
<?php endif; ?>
