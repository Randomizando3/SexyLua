<?php

declare(strict_types=1);

namespace App\Core;

use App\Controllers\PublicController;

final class Router
{
    private array $routes = [];

    public function __construct(
        private readonly App $app,
    ) {
    }

    public function get(string $path, callable|array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function add(string $method, string $path, callable|array $handler): void
    {
        $this->routes[strtoupper($method)][self::normalizePath($path)] = $handler;
    }

    public function dispatch(Request $request): void
    {
        $handler = $this->routes[$request->method()][$request->path()] ?? null;

        if ($handler === null && $this->tryDispatchCreatorProfile($request)) {
            return;
        }

        if ($handler === null) {
            http_response_code(404);
            $this->app->view->render('pages/public/not-found', [
                'title' => 'Página não encontrada',
                'description' => 'A rota solicitada não existe nesta lua.',
            ], 'layouts/marketing');

            return;
        }

        if (is_array($handler) && isset($handler[0], $handler[1]) && is_string($handler[0])) {
            $controller = new $handler[0]($this->app);
            $method = $handler[1];
            $controller->{$method}($request);

            return;
        }

        $handler($request, $this->app);
    }

    private function tryDispatchCreatorProfile(Request $request): bool
    {
        if ($request->method() !== 'GET') {
            return false;
        }

        $path = trim($request->path(), '/');
        if ($path === '' || str_contains($path, '/')) {
            return false;
        }

        if (str_contains($path, '.')) {
            return false;
        }

        $normalized = mb_strtolower($path);
        if (in_array($normalized, \public_profile_reserved_usernames(), true)) {
            return false;
        }

        if ($this->app->repository->findCreatorBySlugOrId($path, null) === null) {
            return false;
        }

        $controller = new PublicController($this->app);
        $controller->profileByHandle($request, $path);

        return true;
    }

    private static function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '//' ? '/' : $path;
    }
}
