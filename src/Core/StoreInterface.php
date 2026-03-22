<?php

declare(strict_types=1);

namespace App\Core;

interface StoreInterface
{
    public function exists(string $collection): bool;

    public function read(string $collection, array $fallback = []): array;

    public function write(string $collection, array $data): void;

    public function nextId(array $records): int;

    public function driver(): string;
}
