<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query,
        private readonly array $input,
        private readonly array $files,
        private readonly array $server,
    ) {
    }

    public static function capture(): self
    {
        $uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            self::normalizePath($uri ?: '/'),
            $_GET,
            $_POST,
            $_FILES,
            $_SERVER,
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->input[$key] ?? $default;
    }

    public function integer(string $key, int $default = 0): int
    {
        return (int) ($this->input[$key] ?? $this->query[$key] ?? $default);
    }

    public function boolean(string $key): bool
    {
        $value = $this->input[$key] ?? $this->query[$key] ?? false;

        return in_array($value, [true, '1', 1, 'on', 'yes'], true);
    }

    public function all(): array
    {
        return $this->input;
    }

    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;

        return is_array($file) ? $file : null;
    }

    public function hasFile(string $key): bool
    {
        $file = $this->file($key);

        return is_array($file)
            && isset($file['error'], $file['tmp_name'])
            && (int) $file['error'] === UPLOAD_ERR_OK
            && is_string($file['tmp_name'])
            && $file['tmp_name'] !== '';
    }

    public function queryParams(): array
    {
        return $this->query;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    private static function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '//' ? '/' : $path;
    }
}
