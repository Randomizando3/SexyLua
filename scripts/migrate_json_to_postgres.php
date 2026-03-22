<?php

declare(strict_types=1);

use App\Core\JsonStore;
/** @var \App\Core\App $app */
$app = require dirname(__DIR__) . '/bootstrap.php';

$jsonStore = new JsonStore(dirname(__DIR__) . '/storage/data');

if (! $app->store instanceof \App\Core\PostgresStore) {
    throw new RuntimeException('O driver ativo precisa ser PostgreSQL para rodar esta migracao.');
}

$collections = [];

foreach (glob(dirname(__DIR__) . '/storage/data/*.json') ?: [] as $file) {
    $collection = pathinfo($file, PATHINFO_FILENAME);
    $collections[$collection] = $jsonStore->read($collection, []);
}

$app->store->importCollections($collections);

echo "Migracao concluida: " . count($collections) . " colecoes enviadas ao PostgreSQL.\n";
