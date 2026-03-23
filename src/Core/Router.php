<?php

declare(strict_types=1);

namespace App\Core;

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

        if ($handler === null) {
            http_response_code(404);
            $this->app->view->render('pages/public/not-found', [
                'title' => 'P�gina n�o encontrada',
                'description' => 'A rota solicitada n�o existe nesta lua.',
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

    private static function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '//' ? '/' : $path;
    }
}
