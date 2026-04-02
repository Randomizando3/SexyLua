<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Router;

if (PHP_SAPI === 'cli-server') {
    $requestedPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $publicPath = __DIR__ . $requestedPath;

    if ($requestedPath !== '/' && is_file($publicPath)) {
        return false;
    }
}

$basePath = dirname(__DIR__);

if (is_dir($basePath . '/private/app')) {
    $basePath .= '/private/app';
}

$app = require $basePath . '/bootstrap.php';
$router = new Router($app);

require $basePath . '/routes/web.php';

$router->dispatch(Request::capture());
