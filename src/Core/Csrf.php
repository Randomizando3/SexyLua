<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    private const TOKEN_KEY = 'sexylua_csrf_token';

    public function token(): string
    {
        if (! isset($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION[self::TOKEN_KEY];
    }

    public function validate(?string $token): bool
    {
        if (! isset($_SESSION[self::TOKEN_KEY]) || ! is_string($token) || $token === '') {
            return false;
        }

        return hash_equals((string) $_SESSION[self::TOKEN_KEY], $token);
    }
}
