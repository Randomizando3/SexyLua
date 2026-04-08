<?php

declare(strict_types=1);

$accountMenuUser = is_array($accountMenuUser ?? null) ? $accountMenuUser : [];
$accountMenuSettingsHref = (string) ($accountMenuSettingsHref ?? user_settings_route($accountMenuUser));
$accountMenuLabel = (string) ($accountMenuLabel ?? 'Perfil');
$accountMenuAvatarUrl = media_url((string) ($accountMenuUser['avatar_url'] ?? ''));
$accountMenuHandle = user_handle($accountMenuUser, 'usuario');
$accountMenuAvatarLabel = user_avatar_label($accountMenuUser, 'CT');
?>
<details class="relative" data-account-menu>
    <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center overflow-hidden rounded-full border border-white/20 bg-white/10 font-bold text-white marker:content-none">
        <?php if ($accountMenuAvatarUrl !== ''): ?>
            <img alt="<?= e($accountMenuLabel) ?>" class="h-full w-full object-cover" src="<?= e($accountMenuAvatarUrl) ?>">
        <?php else: ?>
            <?= e($accountMenuAvatarLabel) ?>
        <?php endif; ?>
    </summary>
    <div class="absolute right-0 top-[calc(100%+0.75rem)] z-[80] w-56 rounded-3xl border border-white/10 bg-white p-3 text-slate-700 shadow-[0px_24px_48px_rgba(27,28,29,0.18)]">
        <div class="border-b border-slate-100 px-3 pb-3">
            <p class="text-sm font-bold text-slate-900"><?= e($accountMenuHandle) ?></p>
            <?php if (($accountMenuUser['email'] ?? '') !== ''): ?>
                <p class="mt-1 truncate text-xs text-slate-500"><?= e((string) ($accountMenuUser['email'] ?? '')) ?></p>
            <?php endif; ?>
        </div>
        <div class="mt-2 space-y-1">
            <a class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50" href="<?= e($accountMenuSettingsHref) ?>">
                <span class="material-symbols-outlined text-base">person</span>
                <span>Editar perfil</span>
            </a>
            <form action="/logout" method="post">
                <input name="_token" type="hidden" value="<?= e($app->csrf->token()) ?>">
                <button class="flex w-full items-center gap-3 rounded-2xl px-3 py-3 text-left text-sm font-semibold text-rose-700 transition-colors hover:bg-rose-50" type="submit">
                    <span class="material-symbols-outlined text-base">logout</span>
                    <span>Sair</span>
                </button>
            </form>
        </div>
    </div>
</details>
