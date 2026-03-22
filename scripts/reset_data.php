<?php

declare(strict_types=1);

use App\Support\SeedFactory;

/** @var \App\Core\App $app */
$app = require dirname(__DIR__) . '/bootstrap.php';

$dataDir = dirname(__DIR__) . '/storage/data';
$seed = SeedFactory::build();

if ($app->store->driver() === 'json') {
    foreach (glob($dataDir . '/*.json') ?: [] as $file) {
        @unlink($file);
    }

    foreach ($seed as $collection => $payload) {
        file_put_contents(
            $dataDir . '/' . $collection . '.json',
            (string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
} else {
    foreach ($seed as $collection => $payload) {
        $app->store->write($collection, $payload);
    }
}

echo "Dados resetados com sucesso no driver " . $app->store->driver() . ".\n";
