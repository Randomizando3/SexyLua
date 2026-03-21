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

$app = require dirname(__DIR__) . '/bootstrap.php';
$router = new Router($app);

require dirname(__DIR__) . '/routes/web.php';

$router->dispatch(Request::capture());
