<?php

declare(strict_types=1);

$publicTopbarUser = is_array($publicTopbarUser ?? null) ? $publicTopbarUser : ($currentUser ?? $app->auth->user() ?? []);
$publicTopbarRole = (string) ($publicTopbarUser['role'] ?? '');
$publicTopbarLoggedIn = $publicTopbarRole !== '';
$publicTopbarPanelHref = $publicTopbarLoggedIn ? match ($publicTopbarRole) {
    'creator' => '/creator',
    'admin' => '/admin',
    default => '/subscriber',
} : '';
$publicTopbarMessagesHref = $publicTopbarLoggedIn ? match ($publicTopbarRole) {
    'creator' => '/creator/messages',
    'admin' => '/admin/messages',
    default => '/subscriber/messages',
} : '';
$publicTopbarNotificationsHref = $publicTopbarLoggedIn ? match ($publicTopbarRole) {
    'creator' => '/creator',
    'admin' => '/admin',
    default => '/subscriber',
} : '';
$publicTopbarFeeds = $publicTopbarLoggedIn ? $app->repository->topbarFeedDataForUser((int) ($publicTopbarUser['id'] ?? 0)) : ['messages' => ['items' => [], 'latest_marker' => 0], 'notifications' => ['items' => [], 'latest_marker' => 0]];
$publicTopbarMessages = is_array($publicTopbarFeeds['messages'] ?? null) ? $publicTopbarFeeds['messages'] : ['items' => [], 'latest_marker' => 0];
$publicTopbarNotifications = is_array($publicTopbarFeeds['notifications'] ?? null) ? $publicTopbarFeeds['notifications'] : ['items' => [], 'latest_marker' => 0];
$publicTopbarMenuLinks = [
    ['href' => '/', 'label' => 'Home'],
    ['href' => '/explore', 'label' => 'Explorar'],
];
?>
<nav class="fixed top-0 z-50 flex h-20 w-full items-center justify-between bg-[#D81B60] px-4 text-white shadow-[0px_20px_40px_rgba(27,28,29,0.06)] sm:px-6 lg:px-8">
    <div class="flex items-center gap-8">
        <a class="block" href="/"><?= brand_logo_white('h-8 w-auto') ?></a>
        <div class="hidden items-center gap-8 md:flex">
            <?php foreach ($publicTopbarMenuLinks as $item): ?>
                <a class="<?= current_path() === (string) ($item['href'] ?? '/') ? 'border-b-2 border-white pb-1 text-sm font-bold uppercase tracking-wide' : 'text-sm font-bold uppercase tracking-wide text-white/80 transition-colors hover:text-white' ?>" href="<?= e((string) ($item['href'] ?? '/')) ?>">
                    <?= e((string) ($item['label'] ?? 'Link')) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="hidden items-center gap-3 md:flex">
        <?php if ($publicTopbarLoggedIn): ?>
            <a class="relative flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white transition-colors hover:bg-white/15" data-public-feed-link data-feed-storage-key="<?= e('sexylua-feed:' . $publicTopbarRole . ':' . (int) ($publicTopbarUser['id'] ?? 0) . ':messages') ?>" data-feed-latest-marker="<?= e((string) ((int) ($publicTopbarMessages['latest_marker'] ?? 0))) ?>" data-feed-unread-count="<?= e((string) count((array) ($publicTopbarMessages['items'] ?? []))) ?>" href="<?= e($publicTopbarMessagesHref) ?>">
                <span class="material-symbols-outlined text-[20px]">chat</span>
                <span class="absolute -right-1 -top-1 hidden min-w-[1.25rem] rounded-full bg-[#1b1c1d] px-1.5 py-0.5 text-center text-[10px] font-extrabold leading-none text-white" data-public-feed-badge></span>
            </a>
            <a class="relative flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white transition-colors hover:bg-white/15" data-public-feed-link data-feed-storage-key="<?= e('sexylua-feed:' . $publicTopbarRole . ':' . (int) ($publicTopbarUser['id'] ?? 0) . ':notifications') ?>" data-feed-latest-marker="<?= e((string) ((int) ($publicTopbarNotifications['latest_marker'] ?? 0))) ?>" data-feed-unread-count="<?= e((string) count((array) ($publicTopbarNotifications['items'] ?? []))) ?>" href="<?= e($publicTopbarNotificationsHref) ?>">
                <span class="material-symbols-outlined text-[20px]">notifications</span>
                <span class="absolute -right-1 -top-1 hidden min-w-[1.25rem] rounded-full bg-[#1b1c1d] px-1.5 py-0.5 text-center text-[10px] font-extrabold leading-none text-white" data-public-feed-badge></span>
            </a>
            <a class="rounded-full border border-white/20 px-6 py-2 text-sm font-bold uppercase tracking-widest" href="<?= e($publicTopbarPanelHref) ?>">Painel</a>
            <form action="/logout" method="post">
                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                <button class="rounded-full bg-white/10 px-6 py-2 text-sm font-bold uppercase tracking-widest text-white" type="submit">Sair</button>
            </form>
        <?php else: ?>
            <a class="rounded-full px-6 py-2 text-sm font-bold uppercase tracking-widest text-white transition-transform hover:scale-105" href="/login">Login</a>
            <a class="rounded-full bg-white px-6 py-2 text-sm font-bold uppercase tracking-widest text-[#ab1155] shadow-lg transition-transform hover:scale-105" href="/register">Registro</a>
        <?php endif; ?>
    </div>

    <div class="flex items-center gap-2 md:hidden">
        <?php if ($publicTopbarLoggedIn): ?>
            <a class="relative flex h-11 w-11 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white" data-public-feed-link data-feed-storage-key="<?= e('sexylua-feed:' . $publicTopbarRole . ':' . (int) ($publicTopbarUser['id'] ?? 0) . ':messages') ?>" data-feed-latest-marker="<?= e((string) ((int) ($publicTopbarMessages['latest_marker'] ?? 0))) ?>" data-feed-unread-count="<?= e((string) count((array) ($publicTopbarMessages['items'] ?? []))) ?>" href="<?= e($publicTopbarMessagesHref) ?>">
                <span class="material-symbols-outlined text-[20px]">chat</span>
                <span class="absolute -right-1 -top-1 hidden min-w-[1.25rem] rounded-full bg-[#1b1c1d] px-1.5 py-0.5 text-center text-[10px] font-extrabold leading-none text-white" data-public-feed-badge></span>
            </a>
            <a class="relative flex h-11 w-11 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white" data-public-feed-link data-feed-storage-key="<?= e('sexylua-feed:' . $publicTopbarRole . ':' . (int) ($publicTopbarUser['id'] ?? 0) . ':notifications') ?>" data-feed-latest-marker="<?= e((string) ((int) ($publicTopbarNotifications['latest_marker'] ?? 0))) ?>" data-feed-unread-count="<?= e((string) count((array) ($publicTopbarNotifications['items'] ?? []))) ?>" href="<?= e($publicTopbarNotificationsHref) ?>">
                <span class="material-symbols-outlined text-[20px]">notifications</span>
                <span class="absolute -right-1 -top-1 hidden min-w-[1.25rem] rounded-full bg-[#1b1c1d] px-1.5 py-0.5 text-center text-[10px] font-extrabold leading-none text-white" data-public-feed-badge></span>
            </a>
        <?php endif; ?>

        <details class="relative">
            <summary class="flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-full border border-white/20 bg-white/10 text-white marker:content-none">
                <span class="material-symbols-outlined text-[22px]">menu</span>
            </summary>
            <div class="absolute right-0 top-[calc(100%+0.75rem)] z-[90] max-h-[calc(100vh-6rem)] w-80 max-w-[calc(100vw-2rem)] overflow-y-auto rounded-3xl bg-white p-4 text-slate-700 shadow-[0px_24px_48px_rgba(27,28,29,0.18)]">
                <div class="space-y-2">
                    <?php foreach ($publicTopbarMenuLinks as $item): ?>
                        <a class="block rounded-2xl px-4 py-3 text-sm font-bold <?= current_path() === (string) ($item['href'] ?? '/') ? 'bg-[#f7f4f7] text-[#D81B60]' : 'text-slate-700 hover:bg-[#f7f4f7]' ?>" href="<?= e((string) ($item['href'] ?? '/')) ?>">
                            <?= e((string) ($item['label'] ?? 'Link')) ?>
                        </a>
                    <?php endforeach; ?>

                    <?php if ($publicTopbarLoggedIn): ?>
                        <a class="block rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 hover:bg-[#f7f4f7]" href="<?= e($publicTopbarPanelHref) ?>">Painel</a>
                        <form action="/logout" method="post">
                            <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                            <button class="block w-full rounded-2xl px-4 py-3 text-left text-sm font-bold text-slate-700 hover:bg-[#f7f4f7]" type="submit">Sair</button>
                        </form>
                    <?php else: ?>
                        <a class="block rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 hover:bg-[#f7f4f7]" href="/login">Login</a>
                        <a class="block rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 hover:bg-[#f7f4f7]" href="/register">Registro</a>
                    <?php endif; ?>
                </div>
            </div>
        </details>
    </div>
</nav>
<?php if (! defined('SEXYLUA_PUBLIC_TOPBAR_SCRIPT_INCLUDED')): ?>
    <?php define('SEXYLUA_PUBLIC_TOPBAR_SCRIPT_INCLUDED', true); ?>
    <script>
        (() => {
            const links = Array.from(document.querySelectorAll('[data-public-feed-link]'));
            const toNumber = (value) => {
                const parsed = Number.parseInt(String(value || '0'), 10);
                return Number.isFinite(parsed) ? parsed : 0;
            };

            links.forEach((link) => {
                const storageKey = link.getAttribute('data-feed-storage-key') || '';
                const latestMarker = toNumber(link.getAttribute('data-feed-latest-marker'));
                const unreadCount = toNumber(link.getAttribute('data-feed-unread-count'));
                const seenMarker = storageKey !== '' ? toNumber(window.localStorage.getItem(storageKey)) : 0;
                const badge = link.querySelector('[data-public-feed-badge]');
                if (badge) {
                    if (latestMarker > seenMarker && unreadCount > 0) {
                        badge.textContent = String(Math.min(unreadCount, 99));
                        badge.classList.remove('hidden');
                    } else {
                        badge.textContent = '';
                        badge.classList.add('hidden');
                    }
                }

                link.addEventListener('click', () => {
                    if (storageKey !== '' && latestMarker > 0) {
                        window.localStorage.setItem(storageKey, String(latestMarker));
                    }
                });
            });
        })();
    </script>
<?php endif; ?>
