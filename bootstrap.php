<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\JsonStore;
use App\Core\PostgresStore;
use App\Core\StoreInterface;
use App\Core\View;
use App\Repositories\PlatformRepository;

define('BASE_PATH', __DIR__);

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = BASE_PATH . '/src/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($path)) {
        require $path;
    }
});

require BASE_PATH . '/src/Support/helpers.php';

load_env_file(BASE_PATH . '/.env');
load_env_file(BASE_PATH . '/.env.local');

ini_set('default_charset', 'UTF-8');

if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

if (function_exists('mb_http_output')) {
    mb_http_output('UTF-8');
}

if (PHP_SAPI !== 'cli' && ! headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$config = [
    'app' => require BASE_PATH . '/config/app.php',
    'database' => require BASE_PATH . '/config/database.php',
];

$timezone = (string) ($config['app']['timezone'] ?? 'America/Sao_Paulo');
date_default_timezone_set($timezone);

$store = build_store($config);
$config['app']['active_storage_driver'] = $store->driver();
$repository = new PlatformRepository($store, $config);
$flash = new Flash();
$csrf = new Csrf();
$view = new View(BASE_PATH . '/templates');
$auth = new Auth($repository, $flash);

$app = new App($config, $store, $repository, $view, $auth, $flash, $csrf);

$repository->seedIfMissing();
$view->share([
    'app' => $app,
    'flash_messages' => $flash->consume(),
]);

return $app;

function load_env_file(string $path): void
{
    if (! is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (str_starts_with($line, 'export ')) {
            $line = substr($line, 7);
        }

        $parts = explode('=', $line, 2);

        if (count($parts) !== 2) {
            continue;
        }

        $key = trim((string) $parts[0]);
        $value = trim((string) $parts[1]);

        if ($key === '') {
            continue;
        }

        $quote = $value[0] ?? '';
        if (($quote === '"' || $quote === '\'') && str_ends_with($value, $quote)) {
            $value = substr($value, 1, -1);
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

function build_store(array $config): StoreInterface
{
    $driver = strtolower((string) ($config['app']['storage_driver'] ?? $config['database']['driver'] ?? 'json'));
    $dataDir = BASE_PATH . '/storage/data';

    if (in_array($driver, ['postgresql', 'pgsql'], true)) {
        $pgsql = $config['database']['postgresql'] ?? [];
        $pdo = new PDO(
            (string) ($pgsql['dsn'] ?? ''),
            (string) ($pgsql['user'] ?? ''),
            (string) ($pgsql['password'] ?? ''),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return new PostgresStore(
            $pdo,
            (string) ($pgsql['schema'] ?? 'public'),
            (string) ($pgsql['collections_table'] ?? 'app_collections'),
        );
    }

    return new JsonStore($dataDir);
}
