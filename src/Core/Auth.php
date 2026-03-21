<?php

declare(strict_types=1);

namespace App\Core;

use App\Repositories\PlatformRepository;

final class Auth
{
    private const SESSION_KEY = 'sexylua_auth_user_id';

    private ?array $cachedUser = null;

    public function __construct(
        private readonly PlatformRepository $repository,
        private readonly Flash $flash,
    ) {
    }

    public function attempt(string $email, string $password): ?array
    {
        $user = $this->repository->findUserByEmail($email);

        if (! $user) {
            return null;
        }

        if (($user['status'] ?? 'inactive') !== 'active') {
            return null;
        }

        if (! password_verify($password, (string) ($user['password'] ?? ''))) {
            return null;
        }

        $this->login((int) $user['id']);

        return $this->user();
    }

    public function login(int $userId): void
    {
        $_SESSION[self::SESSION_KEY] = $userId;
        session_regenerate_id(true);
        $this->cachedUser = $this->repository->findUserById($userId);
    }

    public function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        session_regenerate_id(true);
        $this->cachedUser = null;
    }

    public function user(): ?array
    {
        if ($this->cachedUser !== null) {
            return $this->cachedUser;
        }

        $userId = $_SESSION[self::SESSION_KEY] ?? null;

        if (! is_int($userId) && ! ctype_digit((string) $userId)) {
            return null;
        }

        $this->cachedUser = $this->repository->findUserById((int) $userId);

        return $this->cachedUser;
    }

    public function id(): ?int
    {
        return $this->user()['id'] ?? null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function hasRole(string|array $roles): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        return in_array($user['role'], (array) $roles, true);
    }

    public function requireRole(string|array $roles, string $redirect = '/login'): void
    {
        if ($this->guest()) {
            $this->flash->add('error', 'Faça login para acessar esta área.');
            redirect_to($redirect);
        }

        if (! $this->hasRole($roles)) {
            $this->flash->add('error', 'Seu perfil não tem permissão para esta área.');
            redirect_to($this->homeForRole($this->user()['role'] ?? 'subscriber'));
        }
    }

    public function homeForRole(string $role): string
    {
        return match ($role) {
            'admin' => '/admin',
            'creator' => '/creator',
            'subscriber' => '/subscriber',
            default => '/',
        };
    }
}
