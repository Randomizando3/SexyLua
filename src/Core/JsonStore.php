<?php

declare(strict_types=1);

namespace App\Core;

final class JsonStore
{
    public function __construct(
        private readonly string $dataDir,
    ) {
        if (! is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0777, true);
        }
    }

    public function exists(string $collection): bool
    {
        return is_file($this->path($collection));
    }

    public function read(string $collection, array $fallback = []): array
    {
        $path = $this->path($collection);

        if (! is_file($path)) {
            return $fallback;
        }

        $contents = file_get_contents($path);

        if ($contents === false || $contents === '') {
            return $fallback;
        }

        $decoded = json_decode($contents, true);

        return is_array($decoded) ? $decoded : $fallback;
    }

    public function write(string $collection, array $data): void
    {
        file_put_contents(
            $this->path($collection),
            (string) json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
    }

    public function nextId(array $records): int
    {
        $max = 0;

        foreach ($records as $record) {
            $max = max($max, (int) ($record['id'] ?? 0));
        }

        return $max + 1;
    }

    private function path(string $collection): string
    {
        return $this->dataDir . '/' . $collection . '.json';
    }
}
