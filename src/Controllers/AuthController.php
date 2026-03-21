<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;

final class AuthController extends Controller
{
    public function showLogin(Request $request): void
    {
        if ($this->app->auth->check()) {
            $this->redirect($this->app->auth->homeForRole($this->user()['role'] ?? 'subscriber'));
        }

        $this->render('pages/auth/login', [
            'title' => 'Entrar',
            'prototype' => [
                'page' => 'auth.login',
            ],
        ], null);
    }

    public function login(Request $request): void
    {
        $this->validateCsrf($request, '/login');

        $user = $this->app->auth->attempt(
            trim((string) $request->input('email')),
            (string) $request->input('password')
        );

        if (! $user) {
            $this->redirect('/login', 'Credenciais invalidas ou conta inativa.', 'error');
        }

        $this->redirect($this->app->auth->homeForRole($user['role']), 'Login realizado com sucesso.');
    }

    public function showRegister(Request $request): void
    {
        if ($this->app->auth->check()) {
            $this->redirect($this->app->auth->homeForRole($this->user()['role'] ?? 'subscriber'));
        }

        $this->render('pages/auth/register', [
            'title' => 'Criar conta',
            'prototype' => [
                'page' => 'auth.register',
            ],
        ], null);
    }

    public function register(Request $request): void
    {
        $this->validateCsrf($request, '/register');

        $name = trim((string) $request->input('name'));
        $email = trim((string) $request->input('email'));
        $password = (string) $request->input('password');
        $role = (string) $request->input('role', 'subscriber');

        if ($name === '' || $email === '' || $password === '') {
            $this->redirect('/register', 'Preencha nome, e-mail e senha.', 'error');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('/register', 'Informe um e-mail valido.', 'error');
        }

        if (strlen($password) < 6) {
            $this->redirect('/register', 'A senha precisa ter pelo menos 6 caracteres.', 'error');
        }

        if ($this->app->repository->findUserByEmail($email)) {
            $this->redirect('/register', 'Ja existe uma conta com este e-mail.', 'error');
        }

        $user = $this->app->repository->registerUser([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'city' => (string) $request->input('city', 'Brasil'),
        ]);

        $this->app->auth->login((int) $user['id']);

        $this->redirect($this->app->auth->homeForRole($user['role']), 'Conta criada com sucesso.');
    }

    public function logout(Request $request): void
    {
        $this->validateCsrf($request, '/');
        $this->app->auth->logout();

        $this->redirect('/', 'Sessao encerrada.');
    }
}
