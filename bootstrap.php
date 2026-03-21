<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\JsonStore;
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

date_default_timezone_set('America/Sao_Paulo');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$config = [
    'app' => require BASE_PATH . '/config/app.php',
    'database' => require BASE_PATH . '/config/database.php',
];

$store = new JsonStore(BASE_PATH . '/storage/data');
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
