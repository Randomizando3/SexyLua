<?php

declare(strict_types=1);

function base_path(string $path = ''): string
{
    return BASE_PATH . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

function public_path(string $path = ''): string
{
    return base_path('public' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function brand_logo_white(string $classes = 'h-8 w-auto', string $alt = 'SexyLua'): string
{
    return '<img alt="' . e($alt) . '" class="' . e($classes) . '" decoding="async" loading="eager" src="' . e(asset('img/sexylualogobranco.png')) . '">';
}

function brand_logo_magenta(string $classes = 'h-8 w-auto', string $alt = 'SexyLua'): string
{
    return '<img alt="' . e($alt) . '" class="' . e($classes) . '" decoding="async" loading="eager" src="' . e(asset('img/sexylualogomagenta.png')) . '">';
}

function redirect_to(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function path_with_query(string $path, array $query = []): string
{
    $query = array_filter($query, static fn ($value): bool => $value !== null && $value !== '');

    return $query === [] ? $path : $path . '?' . http_build_query($query);
}

function format_datetime(?string $value, string $format = 'd/m/Y H:i'): string
{
    if (! $value) {
        return '-';
    }

    try {
        return (new DateTimeImmutable($value))->format($format);
    } catch (Throwable) {
        return $value;
    }
}

function luacoins_amount(int|float $amount): string
{
    return number_format((float) $amount, 0, ',', '.') . ' LuaCoins';
}

function token_amount(int|float $amount): string
{
    return luacoins_amount($amount);
}

function brl_amount(int|float $amount): string
{
    return 'R$ ' . number_format((float) $amount, 2, ',', '.');
}

function avatar_initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $initials = '';

    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }

    return $initials !== '' ? $initials : 'SL';
}

function excerpt(string $text, int $limit = 140): string
{
    $text = trim($text);

    if (mb_strlen($text) <= $limit) {
        return $text;
    }

    return rtrim(mb_substr($text, 0, max(1, $limit - 3))) . '...';
}

function current_path(): string
{
    return strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
}

function is_active_path(string $path): bool
{
    return current_path() === $path;
}

function media_url(?string $value): string
{
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $value) === 1) {
        return $value;
    }

    if ($value[0] === '/') {
        return $value;
    }

    return '/' . ltrim($value, '/');
}

function app_base_url(array $config = [], array $settings = []): string
{
    $baseUrl = trim((string) ($settings['site_base_url'] ?? $config['app']['base_url'] ?? ''));

    if ($baseUrl !== '') {
        return rtrim($baseUrl, '/');
    }

    $https = false;
    $httpsValue = strtolower((string) ($_SERVER['HTTPS'] ?? ''));
    if ($httpsValue !== '' && $httpsValue !== 'off') {
        $https = true;
    }

    $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    if ($forwardedProto !== '') {
        $https = $forwardedProto === 'https';
    }

    $scheme = $https ? 'https' : 'http';
    $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));

    if ($host === '') {
        $port = (int) ($config['app']['demo_port'] ?? 8088);
        $host = '127.0.0.1' . ($port > 0 ? ':' . $port : '');
    }

    return $scheme . '://' . $host;
}

function webhook_url(array $config = [], array $settings = [], string $path = '/webhook/mp'): string
{
    return rtrim(app_base_url($config, $settings), '/') . '/' . ltrim($path, '/');
}

function role_label(?string $role): string
{
    return match ((string) $role) {
        'admin' => 'Admin',
        'creator' => 'Criador',
        'subscriber' => 'Assinante',
        default => 'Conta',
    };
}

function user_settings_route(?array $user): string
{
    if (! is_array($user)) {
        return '/login';
    }

    return match ((string) ($user['role'] ?? '')) {
        'admin' => '/admin/settings#perfil',
        'creator' => '/creator/settings',
        'subscriber' => '/subscriber/settings',
        default => '/login',
    };
}

function store_uploaded_file(?array $file, string $folder, array $allowedExtensions = [], int $maxBytes = 52428800): ?string
{
    if (! is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $originalName = (string) ($file['name'] ?? '');
    $size = (int) ($file['size'] ?? 0);

    if ($tmpName === '' || ! is_uploaded_file($tmpName) || $size <= 0 || $size > $maxBytes) {
        return null;
    }

    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if ($allowedExtensions !== [] && ! in_array($extension, $allowedExtensions, true)) {
        return null;
    }

    $folder = trim(preg_replace('/[^a-z0-9\\/_-]+/i', '-', $folder) ?? 'uploads', '/');
    $relativeDir = 'uploads/' . ($folder !== '' ? $folder : 'misc');
    $targetDir = public_path($relativeDir);

    if (! is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(6)) . ($extension !== '' ? '.' . $extension : '');
    $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

    if (! move_uploaded_file($tmpName, $targetPath)) {
        return null;
    }

    return '/' . str_replace('\\', '/', $relativeDir . '/' . $filename);
}

function prototype_apply(string $html, array $payload): string
{
    $prototype = $payload['prototype'] ?? null;

    if (! is_array($prototype)) {
        return $html;
    }

    if (isset($prototype['replace']) && is_array($prototype['replace'])) {
        $html = strtr($html, $prototype['replace']);
    }

    if (isset($prototype['replace_once']) && is_array($prototype['replace_once'])) {
        foreach ($prototype['replace_once'] as $pair) {
            if (! is_array($pair) || ! isset($pair['search'], $pair['replace'])) {
                continue;
            }

            $html = preg_replace('/' . preg_quote((string) $pair['search'], '/') . '/', (string) $pair['replace'], $html, 1) ?? $html;
        }
    }

    $runtime = prototype_runtime_html($payload);

    if ($runtime !== '') {
        if (str_contains($html, '</body>')) {
            $html = str_replace('</body>', $runtime . "\n</body>", $html);
        } else {
            $html .= $runtime;
        }
    }

    return $html;
}

function prototype_sanitize_runtime_data(mixed $value): mixed
{
    if (is_array($value)) {
        $sanitized = [];
        $hiddenKeys = ['password', 'stream_mode', 'segment_duration_seconds', 'latest_sequence', 'segments', 'broadcaster_peer_id'];

        foreach ($value as $key => $item) {
            if (is_string($key) && in_array($key, $hiddenKeys, true)) {
                continue;
            }

            $sanitized[$key] = prototype_sanitize_runtime_data($item);
        }

        return $sanitized;
    }

    if (is_object($value)) {
        return prototype_sanitize_runtime_data((array) $value);
    }

    if (is_scalar($value) || $value === null) {
        return $value;
    }

    return null;
}

function prototype_runtime_html(array $payload): string
{
    $app = $payload['app'] ?? null;
    $prototype = $payload['prototype'] ?? null;
    $flashMessages = $payload['flash_messages'] ?? [];

    if (! is_array($prototype)) {
        return '';
    }

    if (! $app || ! isset($app->auth, $app->csrf)) {
        return prototype_flash_stack_html(is_array($flashMessages) ? $flashMessages : []);
    }

    $user = $app->auth->user();
    $currentUser = null;
    if (is_array($user)) {
        $currentUser = $user;

        if ((string) ($user['role'] ?? '') === 'creator') {
            $currentUser = array_merge(
                $currentUser,
                $app->repository->findCreatorBySlugOrId(null, (int) ($user['id'] ?? 0)) ?? []
            );
        }

        if (array_key_exists('avatar_url', $currentUser)) {
            $currentUser['avatar_url'] = media_url((string) ($currentUser['avatar_url'] ?? ''));
        }

        if (array_key_exists('cover_url', $currentUser)) {
            $currentUser['cover_url'] = media_url((string) ($currentUser['cover_url'] ?? ''));
        }

        $currentUser['settings_route'] = user_settings_route($user);
        $currentUser['role_label'] = role_label((string) ($user['role'] ?? ''));
    }

    $favoriteCreatorId = $prototype['favorite_creator_id']
        ?? $prototype['profile']['creator_id']
        ?? $prototype['live']['creator_id']
        ?? null;
    $favoriteRedirect = $prototype['favorite_redirect']
        ?? $prototype['profile']['redirect']
        ?? $prototype['live']['redirect']
        ?? ($_SERVER['REQUEST_URI'] ?? current_path());
    $conversationId = $prototype['subscriber_message']['conversation_id'] ?? null;
    $moderationIds = $prototype['moderation']['content_ids'] ?? [];
    $settings = prototype_sanitize_runtime_data($app->repository->settings());
    $isAdmin = is_array($user) && (string) ($user['role'] ?? '') === 'admin';

    $routes = [
        'home' => '/',
        'explore' => '/explore',
        'login' => '/login',
        'register' => '/register',
        'subscriber' => '/subscriber',
        'subscriberSubscriptions' => '/subscriber/subscriptions',
        'subscriberFavorites' => '/subscriber/favorites',
        'subscriberMessages' => '/subscriber/messages',
        'subscriberWallet' => '/subscriber/wallet',
        'subscriberSettings' => '/subscriber/settings',
        'creator' => '/creator',
        'creatorMetrics' => '/creator',
        'creatorContent' => '/creator/content',
        'creatorFavorites' => '/creator/favorites',
        'creatorMemberships' => '/creator/memberships',
        'creatorLive' => '/creator/live',
        'creatorWallet' => '/creator/wallet',
        'creatorSettings' => '/creator/settings',
        'live' => '/live?id=' . (string) ($prototype['live']['id'] ?? 1),
        'profile' => '/profile?id=' . (string) ($prototype['profile']['creator_id'] ?? 2),
    ];

    if ($isAdmin) {
        $routes = array_merge($routes, [
            'admin' => '/admin',
            'adminUsers' => '/admin/users',
            'adminModeration' => '/admin/moderation',
            'adminFinance' => '/admin/finance',
            'adminSettings' => '/admin/settings',
        ]);
    }

    $actions = [
        'favorite' => $favoriteCreatorId ? '/subscriber/favorites/toggle' : null,
        'subscribe' => isset($prototype['profile']['plan_id']) ? '/profile/subscribe' : null,
        'message' => isset($prototype['profile']['creator_id']) ? '/profile/message' : null,
        'subscriberMessage' => $conversationId ? '/subscriber/messages/send' : null,
        'tip' => isset($prototype['live']['creator_id']) ? '/tip' : null,
        'chat' => isset($prototype['live']['id']) ? '/live/chat' : null,
        'topup' => ! empty($prototype['wallet_topup']) ? '/subscriber/wallet/add-funds' : null,
        'payout' => ! empty($prototype['wallet_payout']) ? '/creator/wallet/payout' : null,
        'contentCreate' => ! empty($prototype['creator_content_create']) ? '/creator/content/create' : null,
        'creatorQuickLive' => ! empty($prototype['creator_live_quick']) ? '/creator/live/save' : null,
        'creatorSettingsUpdate' => ! empty($prototype['creator_settings']) ? '/creator/settings/update' : null,
        'logout' => $user ? '/logout' : null,
    ];

    if ($isAdmin) {
        $actions = array_merge($actions, [
            'adminSettings' => ! empty($prototype['admin_settings']) ? '/admin/settings/update' : null,
            'adminReview' => $moderationIds !== [] ? '/admin/moderation/review' : null,
        ]);
    }

    $runtime = [
        'page' => $prototype['page'] ?? '',
        'auth' => $user !== null,
        'role' => $user['role'] ?? null,
        'currentUserName' => $currentUser['name'] ?? null,
        'currentUrl' => $_SERVER['REQUEST_URI'] ?? current_path(),
        'currentUser' => prototype_sanitize_runtime_data($currentUser),
        'settings' => is_array($settings) ? $settings : [],
        'data' => prototype_sanitize_runtime_data($payload['data'] ?? null),
        'routes' => $routes,
        'actions' => $actions,
        'profile' => $prototype['profile'] ?? null,
        'live' => $prototype['live'] ?? null,
        'moderation' => [
            'contentIds' => array_values(array_map(static fn (mixed $id): int => (int) $id, is_array($moderationIds) ? $moderationIds : [])),
        ],
        'subscriberMessage' => [
            'conversationId' => $conversationId !== null ? (int) $conversationId : null,
        ],
        'walletTopupLuaCoins' => $prototype['wallet_topup_luacoins'] ?? $prototype['wallet_topup_tokens'] ?? 100,
        'walletTopupTokens' => $prototype['wallet_topup_luacoins'] ?? $prototype['wallet_topup_tokens'] ?? 100,
        'payoutLuaCoins' => $prototype['payout_luacoins'] ?? $prototype['payout_tokens'] ?? 100,
        'payoutTokens' => $prototype['payout_luacoins'] ?? $prototype['payout_tokens'] ?? 100,
        'flashMessages' => is_array($flashMessages) ? $flashMessages : [],
        'csrf' => $app->csrf->token(),
    ];

    $forms = [];

    if ($user) {
        $forms[] = '<form id="prototype-logout-form" method="post" action="/logout" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"></form>';
    }

    if ($favoriteCreatorId) {
        $forms[] = '<form id="prototype-favorite-form" method="post" action="/subscriber/favorites/toggle" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"><input type="hidden" name="creator_id" value="' . e((string) $favoriteCreatorId) . '"><input type="hidden" name="redirect" value="' . e((string) $favoriteRedirect) . '"></form>';
    }

    if (isset($prototype['profile']['plan_id'])) {
        $forms[] = '<form id="prototype-subscribe-form" method="post" action="/profile/subscribe" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"><input type="hidden" name="plan_id" value="' . e((string) $prototype['profile']['plan_id']) . '"></form>';
    }

    if (isset($prototype['profile']['creator_id'])) {
        $forms[] = '<form id="prototype-message-form" method="post" action="/profile/message" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"><input type="hidden" name="creator_id" value="' . e((string) $prototype['profile']['creator_id']) . '"><input type="hidden" name="body" value="Oi! Quero conversar com voce."></form>';
    }

    if ($conversationId !== null) {
        $forms[] = '<form id="prototype-subscriber-message-form" method="post" action="/subscriber/messages/send" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"><input type="hidden" name="conversation_id" value="' . e((string) $conversationId) . '"><input type="hidden" name="body" value=""></form>';
    }

    if (isset($prototype['live']['creator_id'], $prototype['live']['id'])) {
        $forms[] = '<form id="prototype-tip-form" method="post" action="/tip" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"><input type="hidden" name="creator_id" value="' . e((string) $prototype['live']['creator_id']) . '"><input type="hidden" name="live_id" value="' . e((string) $prototype['live']['id']) . '"><input type="hidden" name="amount" value="25"></form>';
        $forms[] = '<form id="prototype-live-chat-form" method="post" action="/live/chat" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"><input type="hidden" name="live_id" value="' . e((string) $prototype['live']['id']) . '"><input type="hidden" name="body" value=""></form>';
    }

    if (! empty($prototype['wallet_topup'])) {
        $forms[] = '<form id="prototype-topup-form" method="post" action="/subscriber/wallet/add-funds" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"><input type="hidden" name="luacoins" value="' . e((string) ($prototype['wallet_topup_luacoins'] ?? $prototype['wallet_topup_tokens'] ?? 100)) . '"></form>';
    }

    if (! empty($prototype['wallet_payout'])) {
        $forms[] = '<form id="prototype-payout-form" method="post" action="/creator/wallet/payout" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"><input type="hidden" name="luacoins" value="' . e((string) ($prototype['payout_luacoins'] ?? $prototype['payout_tokens'] ?? 100)) . '"></form>';
    }

    if (! empty($prototype['creator_content_create'])) {
        $forms[] = '<form id="prototype-creator-content-form" method="post" action="/creator/content/create" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"><input type="hidden" name="title" value=""><input type="hidden" name="excerpt" value=""><input type="hidden" name="body" value=""><input type="hidden" name="kind" value="gallery"><input type="hidden" name="visibility" value="subscriber"><input type="hidden" name="status" value="pending"></form>';
    }

    if (! empty($prototype['creator_live_quick'])) {
        $forms[] = '<form id="prototype-creator-live-form" method="post" action="/creator/live/save" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"><input type="hidden" name="title" value=""><input type="hidden" name="description" value="Live criada a partir do layout original"><input type="hidden" name="scheduled_for" value=""><input type="hidden" name="price_luacoins" value="0"><input type="hidden" name="status" value="scheduled"><input type="hidden" name="chat_enabled" value="1"></form>';
    }

    if ($isAdmin && ! empty($prototype['admin_settings'])) {
        $forms[] = '<form id="prototype-admin-settings-form" method="post" action="/admin/settings/update" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"><input type="hidden" name="platform_fee_percent" value=""><input type="hidden" name="withdraw_min_luacoins" value=""><input type="hidden" name="withdraw_max_luacoins" value=""><input type="hidden" name="slow_mode_seconds" value=""><input type="hidden" name="maintenance_mode" value=""><input type="hidden" name="auto_moderation" value=""><input type="hidden" name="live_chat_enabled" value=""></form>';
    }

    if ($isAdmin && $moderationIds !== []) {
        $forms[] = '<form id="prototype-admin-review-form" method="post" action="/admin/moderation/review" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"><input type="hidden" name="content_id" value=""><input type="hidden" name="decision" value=""><input type="hidden" name="moderation_feedback" value=""></form>';
    }

    return prototype_flash_stack_html(is_array($flashMessages) ? $flashMessages : []) . "\n" .
        implode("\n", $forms) .
        "\n<script>window.SexyLuaPrototype = " . json_encode($runtime, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ";</script>\n" .
        '<script src="' . e(asset('js/prototype-data.js')) . '"></script>' . "\n" .
        '<script src="' . e(asset('js/prototype-actions.js')) . '"></script>';
}

function prototype_flash_stack_html(array $messages): string
{
    if ($messages === []) {
        return '';
    }

    $items = [];

    foreach ($messages as $flash) {
        $type = (string) ($flash['type'] ?? 'success');
        $message = (string) ($flash['message'] ?? '');
        $classes = match ($type) {
            'error' => 'border-red-200 bg-red-50 text-red-900',
            'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
            default => 'border-pink-200 bg-white text-slate-900',
        };

        $items[] = '<div data-prototype-flash class="rounded-2xl border px-4 py-3 shadow-2xl backdrop-blur ' . $classes . '">' .
            '<div class="flex items-start gap-3">' .
            '<div class="mt-0.5 h-2.5 w-2.5 flex-none rounded-full bg-current opacity-70"></div>' .
            '<div class="min-w-0 flex-1 text-sm font-semibold leading-5">' . e($message) . '</div>' .
            '<button type="button" data-prototype-flash-close class="text-xs font-bold uppercase tracking-wider opacity-60 transition-opacity hover:opacity-100">Fechar</button>' .
            '</div>' .
            '</div>';
    }

    return '<div id="prototype-flash-stack" class="fixed right-4 top-20 z-[9999] flex max-w-sm flex-col gap-3">' . implode('', $items) . '</div>';
}
