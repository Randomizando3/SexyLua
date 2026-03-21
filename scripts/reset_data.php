<?php

declare(strict_types=1);

use App\Support\SeedFactory;

require dirname(__DIR__) . '/bootstrap.php';

$dataDir = dirname(__DIR__) . '/storage/data';

foreach (glob($dataDir . '/*.json') ?: [] as $file) {
    @unlink($file);
}

foreach (SeedFactory::build() as $collection => $payload) {
    file_put_contents(
        $dataDir . '/' . $collection . '.json',
        (string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );
}

echo "Dados resetados com sucesso.\n";
