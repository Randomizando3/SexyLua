<?php

declare(strict_types=1);

/** @var \App\Core\App $app */
$app = require dirname(__DIR__) . '/bootstrap.php';

echo "Driver ativo: " . $app->store->driver() . PHP_EOL;
echo "Usuarios carregados: " . count($app->store->read('users', [])) . PHP_EOL;
