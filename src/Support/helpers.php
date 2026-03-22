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

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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

function token_amount(int|float $amount): string
{
    return number_format((float) $amount, 0, ',', '.') . ' LUA';
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

        foreach ($value as $key => $item) {
            if (is_string($key) && in_array($key, ['password'], true)) {
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

    $runtime = [
        'page' => $prototype['page'] ?? '',
        'auth' => $user !== null,
        'role' => $user['role'] ?? null,
        'currentUserName' => $user['name'] ?? null,
        'currentUrl' => $_SERVER['REQUEST_URI'] ?? current_path(),
        'settings' => is_array($settings) ? $settings : [],
        'data' => prototype_sanitize_runtime_data($payload['data'] ?? null),
        'routes' => [
            'home' => '/',
            'explore' => '/explore',
            'login' => '/login',
            'register' => '/register',
            'subscriber' => '/subscriber',
            'subscriberSubscriptions' => '/subscriber/subscriptions',
            'subscriberFavorites' => '/subscriber/favorites',
            'subscriberMessages' => '/subscriber/messages',
            'subscriberWallet' => '/subscriber/wallet',
            'creator' => '/creator',
            'creatorMetrics' => '/creator',
            'creatorContent' => '/creator/content',
            'creatorFavorites' => '/creator/favorites',
            'creatorMemberships' => '/creator/memberships',
            'creatorLive' => '/creator/live',
            'creatorWallet' => '/creator/wallet',
            'creatorSettings' => '/creator/settings',
            'admin' => '/admin',
            'adminUsers' => '/admin/users',
            'adminModeration' => '/admin/moderation',
            'adminFinance' => '/admin/finance',
            'adminSettings' => '/admin/settings',
            'live' => '/live?id=' . (string) ($prototype['live']['id'] ?? 1),
            'profile' => '/profile?id=' . (string) ($prototype['profile']['creator_id'] ?? 2),
        ],
        'actions' => [
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
            'adminSettings' => ! empty($prototype['admin_settings']) ? '/admin/settings/update' : null,
            'adminReview' => $moderationIds !== [] ? '/admin/moderation/review' : null,
            'logout' => $user ? '/logout' : null,
        ],
        'profile' => $prototype['profile'] ?? null,
        'live' => $prototype['live'] ?? null,
        'moderation' => [
            'contentIds' => array_values(array_map(static fn (mixed $id): int => (int) $id, is_array($moderationIds) ? $moderationIds : [])),
        ],
        'subscriberMessage' => [
            'conversationId' => $conversationId !== null ? (int) $conversationId : null,
        ],
        'walletTopupTokens' => $prototype['wallet_topup_tokens'] ?? 100,
        'payoutTokens' => $prototype['payout_tokens'] ?? 100,
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
        $forms[] = '<form id="prototype-topup-form" method="post" action="/subscriber/wallet/add-funds" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"><input type="hidden" name="tokens" value="' . e((string) ($prototype['wallet_topup_tokens'] ?? 100)) . '"></form>';
    }

    if (! empty($prototype['wallet_payout'])) {
        $forms[] = '<form id="prototype-payout-form" method="post" action="/creator/wallet/payout" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"><input type="hidden" name="tokens" value="' . e((string) ($prototype['payout_tokens'] ?? 100)) . '"></form>';
    }

    if (! empty($prototype['creator_content_create'])) {
        $forms[] = '<form id="prototype-creator-content-form" method="post" action="/creator/content/create" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"><input type="hidden" name="title" value=""><input type="hidden" name="excerpt" value=""><input type="hidden" name="body" value=""><input type="hidden" name="kind" value="gallery"><input type="hidden" name="visibility" value="subscriber"><input type="hidden" name="status" value="pending"></form>';
    }

    if (! empty($prototype['creator_live_quick'])) {
        $forms[] = '<form id="prototype-creator-live-form" method="post" action="/creator/live/save" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"><input type="hidden" name="title" value=""><input type="hidden" name="description" value="Live criada a partir do layout original"><input type="hidden" name="scheduled_for" value=""><input type="hidden" name="price_tokens" value="0"><input type="hidden" name="status" value="scheduled"><input type="hidden" name="chat_enabled" value="1"></form>';
    }

    if (! empty($prototype['admin_settings'])) {
        $forms[] = '<form id="prototype-admin-settings-form" method="post" action="/admin/settings/update" style="display:none"><input type="hidden" name="_token" value="' . e($runtime['csrf']) . '"><input type="hidden" name="platform_fee_percent" value=""><input type="hidden" name="withdraw_min_tokens" value=""><input type="hidden" name="withdraw_max_tokens" value=""><input type="hidden" name="slow_mode_seconds" value=""><input type="hidden" name="maintenance_mode" value=""><input type="hidden" name="auto_moderation" value=""><input type="hidden" name="live_chat_enabled" value=""></form>';
    }

    if ($moderationIds !== []) {
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
