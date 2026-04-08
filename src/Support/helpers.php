<?php

declare(strict_types=1);

function base_path(string $path = ''): string
{
    return BASE_PATH . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function public_root_path(string $path = ''): string
{
    static $root = null;

    if ($root === null) {
        $candidates = [
            dirname(BASE_PATH, 2) . '/public_html',
            base_path('public'),
            dirname(BASE_PATH) . '/public',
        ];

        foreach ($candidates as $candidate) {
            if (is_dir($candidate)) {
                $root = $candidate;
                break;
            }
        }

        $root ??= base_path('public');
    }

    return $root . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function asset(string $path): string
{
    $relative = 'assets/' . ltrim($path, '/');
    $url = '/' . $relative;
    $absolute = public_root_path($relative);

    if (is_file($absolute)) {
        $version = @filemtime($absolute);
        if ($version !== false) {
            return $url . '?v=' . (string) $version;
        }
    }

    return $url;
}

function public_path(string $path = ''): string
{
    return public_root_path($path);
}

function private_path(string $path = ''): string
{
    return base_path('storage/private' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function site_settings(): array
{
    static $cache = null;
    static $loading = false;

    if (is_array($cache)) {
        return $cache;
    }

    if ($loading) {
        return [];
    }

    $loading = true;

    try {
        $app = $GLOBALS['app'] ?? null;
        if (is_object($app) && isset($app->repository) && method_exists($app->repository, 'settings')) {
            $cache = (array) $app->repository->settings();
        } else {
            $cache = [];
        }
    } catch (Throwable) {
        $cache = [];
    }

    $loading = false;

    return $cache;
}

function site_setting(string $key, mixed $default = ''): mixed
{
    $settings = site_settings();

    return array_key_exists($key, $settings) ? $settings[$key] : $default;
}

function site_brand_name(): string
{
    $name = trim((string) site_setting('seo_site_title', 'SexyLua'));

    return $name !== '' ? $name : 'SexyLua';
}

function site_meta_title(?string $pageTitle = null): string
{
    $baseTitle = trim((string) site_setting('seo_meta_title', site_brand_name()));
    $pageTitle = trim((string) $pageTitle);

    if ($pageTitle === '' || mb_strtolower($pageTitle) === mb_strtolower($baseTitle)) {
        return $baseTitle;
    }

    return $pageTitle . ' | ' . $baseTitle;
}

function site_meta_description(string $fallback = ''): string
{
    $description = trim((string) site_setting('seo_meta_description', ''));

    if ($description !== '') {
        return $description;
    }

    return $fallback !== '' ? $fallback : 'Plataforma de assinaturas, chats privados, lives e monetizacao com LuaCoins.';
}

function site_favicon_url(): string
{
    $customLogo = media_url((string) site_setting('seo_logo_color_url', ''));

    return $customLogo !== '' ? $customLogo : asset('img/luacoin.png');
}

function home_banner_default_image_url(): string
{
    return asset('img/home-banner-default.png');
}

function audience_category_options(): array
{
    return [
        'todos' => 'Todos',
        'homem' => 'Homem',
        'mulher' => 'Mulher',
        'trans' => 'Trans',
    ];
}

function normalize_audience_category(?string $value, string $default = 'todos'): string
{
    $normalized = mb_strtolower(trim((string) $value));
    $mapped = match ($normalized) {
        'todos', 'all', '' => 'todos',
        'homem', 'homens', 'male', 'man' => 'homem',
        'mulher', 'mulheres', 'female', 'woman' => 'mulher',
        'trans', 'transgenero', 'transgênero', 'transgender' => 'trans',
        default => $default,
    };

    $options = audience_category_options();

    return array_key_exists($mapped, $options) ? $mapped : $default;
}

function audience_category_label(?string $value): string
{
    $normalized = normalize_audience_category($value);
    $options = audience_category_options();

    return (string) ($options[$normalized] ?? $options['todos']);
}

function audience_category_matches_selection(?string $selection, ?string $itemCategory): bool
{
    $selected = normalize_audience_category($selection);
    $item = normalize_audience_category($itemCategory);

    if ($selected === 'todos') {
        return true;
    }

    return $item === 'todos' || $item === $selected;
}

function current_public_audience_category(?string $preferred = null): string
{
    $preferredValue = trim((string) $preferred);
    if ($preferredValue !== '') {
        return normalize_audience_category($preferredValue);
    }

    return normalize_audience_category((string) ($_COOKIE['sexylua_audience_category'] ?? 'todos'));
}

function public_age_gate_completed(): bool
{
    return (string) ($_COOKIE['sexylua_age_gate_verified'] ?? '0') === '1'
        && array_key_exists(
            normalize_audience_category((string) ($_COOKIE['sexylua_audience_category'] ?? '')),
            audience_category_options()
        );
}

function brand_logo_white(string $classes = 'h-8 w-auto', string $alt = 'SexyLua'): string
{
    $customLogo = media_url((string) site_setting('seo_logo_white_url', ''));
    $source = $customLogo !== '' ? $customLogo : asset('img/sexylualogobranco.png');

    return '<img alt="' . e($alt) . '" class="' . e($classes) . '" decoding="async" loading="eager" src="' . e($source) . '">';
}

function brand_logo_magenta(string $classes = 'h-8 w-auto', string $alt = 'SexyLua'): string
{
    $customLogo = media_url((string) site_setting('seo_logo_color_url', ''));
    $source = $customLogo !== '' ? $customLogo : asset('img/sexylualogomagenta.png');

    return '<img alt="' . e($alt) . '" class="' . e($classes) . '" decoding="async" loading="eager" src="' . e($source) . '">';
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

function luacoin_value(int|float $amount): string
{
    return number_format((float) $amount, 0, ',', '.');
}

function luacoin_icon(string $classes = 'h-5 w-5', string $alt = 'LuaCoin'): string
{
    return '<img alt="' . e($alt) . '" class="' . e($classes) . '" decoding="async" loading="lazy" src="' . e(asset('img/luacoin.png')) . '">';
}

function luacoin_amount_html(
    int|float $amount,
    string $wrapperClasses = 'inline-flex items-center gap-2 whitespace-nowrap',
    string $valueClasses = '',
    string $iconClasses = 'h-[1em] w-[1em] shrink-0'
): string {
    $valueClassAttr = trim($valueClasses) !== '' ? ' class="' . e($valueClasses) . '"' : '';

    return '<span class="' . e($wrapperClasses) . '"><span' . $valueClassAttr . '>' . e(luacoin_value($amount)) . '</span>' . luacoin_icon($iconClasses) . '<span class="sr-only">LuaCoins</span></span>';
}

function token_amount(int|float $amount): string
{
    return luacoins_amount($amount);
}

function brl_amount(int|float $amount): string
{
    return 'R$ ' . number_format((float) $amount, 2, ',', '.');
}

function luacoin_to_brl(int|float $amount, int|float $unitPriceBrl = 0.07): float
{
    return round(((float) $amount) * ((float) $unitPriceBrl), 2);
}

function luacoin_brl_pair_html(
    int|float $amount,
    int|float $unitPriceBrl = 0.07,
    string $wrapperClasses = 'space-y-1',
    string $coinClasses = 'inline-flex items-center gap-1.5 whitespace-nowrap',
    string $brlClasses = 'text-xs font-semibold text-slate-500'
): string {
    return '<span class="' . e($wrapperClasses) . '">'
        . '<span class="' . e($coinClasses) . '">' . luacoin_amount_html($amount, 'inline-flex items-center gap-1.5 whitespace-nowrap', '', 'h-[0.95em] w-[0.95em] shrink-0') . '</span>'
        . '<span class="' . e($brlClasses) . '">' . e(brl_amount(luacoin_to_brl($amount, $unitPriceBrl))) . '</span>'
        . '</span>';
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

function user_username(?array $user, string $fallback = 'usuario'): string
{
    $username = trim((string) ($user['username'] ?? ''));

    if ($username !== '') {
        return ltrim($username, '@');
    }

    $fallback = trim($fallback, '@ ');

    return $fallback !== '' ? $fallback : 'usuario';
}

function user_handle(?array $user, string $fallback = 'usuario'): string
{
    return '@' . user_username($user, $fallback);
}

function public_profile_reserved_usernames(): array
{
    return [
        'admin',
        'assets',
        'audience-gate',
        'auth',
        'creator',
        'explore',
        'help',
        'live',
        'login',
        'logout',
        'messages',
        'privacy',
        'profile',
        'register',
        'subscriber',
        'terms',
        'tip',
        'uploads',
        'webhook',
    ];
}

function creator_public_path(?array $creator): string
{
    if (! is_array($creator)) {
        return '/profile';
    }

    $username = user_username($creator, '');
    if ($username !== '' && ! in_array($username, public_profile_reserved_usernames(), true)) {
        return '/' . rawurlencode($username);
    }

    $slug = trim((string) ($creator['slug'] ?? ''));
    if ($slug !== '') {
        return path_with_query('/profile', ['slug' => $slug]);
    }

    $creatorId = (int) ($creator['id'] ?? 0);

    return $creatorId > 0
        ? path_with_query('/profile', ['id' => $creatorId])
        : '/profile';
}

function creator_public_url(?array $creator, array $query = []): string
{
    $base = creator_public_path($creator);
    $query = array_filter($query, static fn (mixed $value): bool => $value !== null && $value !== '');

    if ($query === []) {
        return $base;
    }

    return $base . (str_contains($base, '?') ? '&' : '?') . http_build_query($query);
}

function user_avatar_label(?array $user, string $fallback = 'SL'): string
{
    $username = str_replace(['.', '_', '-'], ' ', user_username($user, ''));
    if ($username !== '') {
        return avatar_initials($username);
    }

    $name = trim((string) ($user['name'] ?? ''));

    return avatar_initials($name !== '' ? $name : $fallback);
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

function media_file_extension(?string $value): string
{
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    $path = preg_match('#^https?://#i', $value) === 1
        ? (string) parse_url($value, PHP_URL_PATH)
        : $value;

    return strtolower(pathinfo($path, PATHINFO_EXTENSION));
}

function media_kind_from_value(?string $value): string
{
    return uploaded_asset_kind(media_file_extension($value));
}

function media_is_video(?string $value): bool
{
    return media_kind_from_value($value) === 'video';
}

function cover_media_allowed_extensions(): array
{
    return ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'mov', 'webm'];
}

function cover_media_accept_attribute(): string
{
    return '.jpg,.jpeg,.png,.webp,.gif,.mp4,.mov,.webm';
}

function cover_media_recommendation_text(): string
{
    return 'Recomendado: 1600 x 900 px para imagem, ou vídeo MP4/WebM/MOV de até 5s, sem áudio, em loop.';
}

function public_media_local_path(?string $value): ?string
{
    $value = trim((string) $value);

    if ($value === '' || preg_match('#^https?://#i', $value) === 1) {
        return null;
    }

    return public_path(ltrim($value, '/'));
}

function public_media_file_bytes(?string $value): int
{
    $path = public_media_local_path($value);

    if ($path === null || ! is_file($path)) {
        return 0;
    }

    $bytes = @filesize($path);

    return $bytes === false ? 0 : max(0, (int) $bytes);
}

function private_media_local_path(?string $value): ?string
{
    $value = trim((string) $value);

    if ($value === '' || preg_match('#^https?://#i', $value) === 1) {
        return null;
    }

    return private_path(ltrim($value, '/'));
}

function private_media_file_bytes(?string $value): int
{
    $path = private_media_local_path($value);

    if ($path === null || ! is_file($path)) {
        return 0;
    }

    $bytes = @filesize($path);

    return $bytes === false ? 0 : max(0, (int) $bytes);
}

function delete_public_media_file(?string $value): void
{
    $path = public_media_local_path($value);

    if ($path !== null && is_file($path)) {
        @unlink($path);
    }
}

function delete_private_media_file(?string $value): void
{
    $path = private_media_local_path($value);

    if ($path !== null && is_file($path)) {
        @unlink($path);
    }
}

function uploaded_asset_kind(string $extension, ?string $mimeType = null): string
{
    $extension = strtolower(trim($extension));
    $mimeType = strtolower(trim((string) $mimeType));

    if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg'], true) || str_starts_with($mimeType, 'image/')) {
        return 'image';
    }

    if (in_array($extension, ['mp4', 'mov', 'webm', 'm4v', 'mpeg', 'mpg'], true) || str_starts_with($mimeType, 'video/')) {
        return 'video';
    }

    return 'document';
}

function detect_uploaded_mime_type(string $path, string $fallback = 'application/octet-stream'): string
{
    if (is_file($path) && function_exists('finfo_open')) {
        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mime = @finfo_file($finfo, $path);
            @finfo_close($finfo);
            if (is_string($mime) && trim($mime) !== '') {
                return trim($mime);
            }
        }
    }

    return $fallback;
}

function human_file_size(int $bytes, int $precision = 1): string
{
    $bytes = max(0, $bytes);

    if ($bytes >= 1024 * 1024 * 1024) {
        return number_format($bytes / (1024 * 1024 * 1024), $precision, ',', '.') . ' GB';
    }

    if ($bytes >= 1024 * 1024) {
        return number_format($bytes / (1024 * 1024), $precision, ',', '.') . ' MB';
    }

    if ($bytes >= 1024) {
        return number_format($bytes / 1024, $precision, ',', '.') . ' KB';
    }

    return number_format($bytes, 0, ',', '.') . ' B';
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

function webhook_url(array $config = [], array $settings = [], string $path = '/webhook/syncpay'): string
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

function shell_command_path(string $command): ?string
{
    static $cache = [];

    if (array_key_exists($command, $cache)) {
        return $cache[$command];
    }

    $output = [];
    $exitCode = 1;

    if (DIRECTORY_SEPARATOR === '\\') {
        @exec('where ' . escapeshellarg($command), $output, $exitCode);
    } else {
        @exec('command -v ' . escapeshellarg($command) . ' 2>/dev/null', $output, $exitCode);
    }

    $path = $exitCode === 0 ? trim((string) ($output[0] ?? '')) : '';
    $cache[$command] = $path !== '' ? $path : null;

    return $cache[$command];
}

function uploaded_video_duration_seconds(string $path): ?float
{
    $probe = shell_command_path('ffprobe');
    if ($probe === null || ! is_file($path)) {
        return null;
    }

    $command = escapeshellarg($probe)
        . ' -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 '
        . escapeshellarg($path) . ' 2>&1';

    $output = [];
    $exitCode = 1;
    @exec($command, $output, $exitCode);

    if ($exitCode !== 0) {
        return null;
    }

    $duration = (float) trim((string) implode("\n", $output));

    return $duration > 0 ? $duration : null;
}

function compress_cover_video_file(string $sourcePath): ?array
{
    $ffmpeg = shell_command_path('ffmpeg');
    if ($ffmpeg === null || ! is_file($sourcePath)) {
        return null;
    }

    $targetPath = preg_replace('/\.[^.]+$/', '', $sourcePath) . '-cover.mp4';
    if (! is_string($targetPath) || trim($targetPath) === '') {
        return null;
    }

    $command = escapeshellarg($ffmpeg)
        . ' -y -i ' . escapeshellarg($sourcePath)
        . ' -an -vf fps=24 -c:v libx264 -preset veryfast -crf 30 -pix_fmt yuv420p -movflags +faststart '
        . escapeshellarg($targetPath) . ' 2>&1';

    $output = [];
    $exitCode = 1;
    @exec($command, $output, $exitCode);

    if ($exitCode !== 0 || ! is_file($targetPath)) {
        return null;
    }

    return [
        'path' => $targetPath,
        'extension' => 'mp4',
        'mime_type' => 'video/mp4',
        'size' => max(0, (int) (@filesize($targetPath) ?: 0)),
    ];
}

function store_cover_media_file(?array $file, string $folder, int $maxBytes = 26214400, int $maxDurationSeconds = 5): ?array
{
    if (! is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $originalName = (string) ($file['name'] ?? '');
    $size = (int) ($file['size'] ?? 0);

    if ($tmpName === '' || ! is_uploaded_file($tmpName) || $size <= 0 || $size > $maxBytes) {
        return [
            'ok' => false,
            'error' => 'A capa enviada e invalida ou ultrapassa o limite permitido.',
        ];
    }

    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (! in_array($extension, cover_media_allowed_extensions(), true)) {
        return [
            'ok' => false,
            'error' => 'Use apenas imagem ou video curto na capa.',
        ];
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
        return [
            'ok' => false,
            'error' => 'Nao foi possivel salvar a capa enviada.',
        ];
    }

    $mimeType = detect_uploaded_mime_type($targetPath, (string) ($file['type'] ?? 'application/octet-stream'));
    $kind = uploaded_asset_kind($extension, $mimeType);

    if ($kind === 'document') {
        @unlink($targetPath);

        return [
            'ok' => false,
            'error' => 'A capa aceita apenas imagem ou video curto.',
        ];
    }

    $relativePath = '/' . str_replace('\\', '/', $relativeDir . '/' . $filename);

    if ($kind === 'video') {
        $durationSeconds = uploaded_video_duration_seconds($targetPath);
        if ($durationSeconds !== null && $durationSeconds > ($maxDurationSeconds + 0.1)) {
            @unlink($targetPath);

            return [
                'ok' => false,
                'error' => 'Envie um video de capa com ate ' . $maxDurationSeconds . ' segundos.',
            ];
        }

        $compressed = compress_cover_video_file($targetPath);
        if (is_array($compressed) && is_file((string) ($compressed['path'] ?? ''))) {
            @unlink($targetPath);
            $targetPath = (string) $compressed['path'];
            $filename = basename($targetPath);
            $relativePath = '/' . str_replace('\\', '/', $relativeDir . '/' . $filename);
            $mimeType = (string) ($compressed['mime_type'] ?? 'video/mp4');
            $extension = (string) ($compressed['extension'] ?? 'mp4');
            $size = max(0, (int) ($compressed['size'] ?? $size));
        }
    }

    return [
        'ok' => true,
        'path' => $relativePath,
        'kind' => $kind,
        'mime_type' => $mimeType,
        'extension' => $extension,
        'size' => $size,
    ];
}

function store_private_uploaded_file(?array $file, string $folder, array $allowedExtensions = [], int $maxBytes = 52428800): ?array
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
    $targetDir = private_path($relativeDir);

    if (! is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(6)) . ($extension !== '' ? '.' . $extension : '');
    $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

    if (! move_uploaded_file($tmpName, $targetPath)) {
        return null;
    }

    $mimeType = detect_uploaded_mime_type($targetPath, (string) ($file['type'] ?? 'application/octet-stream'));

    return [
        'path' => str_replace('\\', '/', $relativeDir . '/' . $filename),
        'original_name' => $originalName !== '' ? $originalName : $filename,
        'mime_type' => $mimeType,
        'extension' => $extension,
        'size' => $size,
        'kind' => uploaded_asset_kind($extension, $mimeType),
    ];
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

    $page = (string) ($prototype['page'] ?? '');

    if ($page !== '' && str_starts_with($page, 'public.')) {
        return prototype_flash_stack_html(is_array($flashMessages) ? $flashMessages : []);
    }

    if (($prototype['runtime_scripts'] ?? false) !== true) {
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
        'profile' => creator_public_url(['id' => (int) ($prototype['profile']['creator_id'] ?? 2), 'username' => (string) ($prototype['profile']['username'] ?? '')]),
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

    return '<div id="prototype-flash-stack" class="fixed right-4 top-20 z-[9999] flex max-w-sm flex-col gap-3">' . implode('', $items) . '</div>' .
        '<script>(function(){var stack=document.getElementById("prototype-flash-stack");if(!stack){return;}stack.querySelectorAll("[data-prototype-flash-close]").forEach(function(button){button.addEventListener("click",function(){var item=button.closest("[data-prototype-flash]");if(item){item.remove();}});});setTimeout(function(){stack.querySelectorAll("[data-prototype-flash]").forEach(function(item){item.remove();});},5000);})();</script>';
}
