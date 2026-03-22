<?php

declare(strict_types=1);

namespace App\Core;

use App\Repositories\PlatformRepository;

final class App
{
    public function __construct(
        public readonly array $config,
        public readonly StoreInterface $store,
        public readonly PlatformRepository $repository,
        public readonly View $view,
        public readonly Auth $auth,
        public readonly Flash $flash,
        public readonly Csrf $csrf,
    ) {
    }
}
