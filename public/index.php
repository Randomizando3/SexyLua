<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Router;

$app = require dirname(__DIR__) . '/bootstrap.php';
$router = new Router($app);

require dirname(__DIR__) . '/routes/web.php';

$router->dispatch(Request::capture());
