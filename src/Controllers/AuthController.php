<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Services\GoogleOAuthService;

final class AuthController extends Controller
{
    private const GOOGLE_SESSION_KEY = 'sexylua_google_oauth';

    public function showLogin(Request $request): void
    {
        if ($this->app->auth->check()) {
            $this->redirect($this->app->auth->homeForRole($this->user()['role'] ?? 'subscriber'));
        }

        $settings = $this->app->repository->settings();

        $this->render('pages/auth/login', [
            'title' => 'Entrar',
            'google_auth_enabled' => (bool) ($settings['google_oauth_enabled'] ?? true),
            'google_auth_url' => '/auth/google?intent=login',
            'prototype' => [
                'page' => 'auth.login',
            ],
        ], null);
    }

    public function login(Request $request): void
    {
        $this->validateCsrf($request, '/login');

        $user = $this->app->auth->attempt(
            trim((string) $request->input('login', (string) $request->input('email'))),
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

        $settings = $this->app->repository->settings();

        $this->render('pages/auth/register', [
            'title' => 'Criar conta',
            'google_auth_enabled' => (bool) ($settings['google_oauth_enabled'] ?? true),
            'google_auth_url' => '/auth/google?intent=register&role=subscriber',
            'prototype' => [
                'page' => 'auth.register',
            ],
        ], null);
    }

    public function register(Request $request): void
    {
        $this->validateCsrf($request, '/register');

        $name = trim((string) $request->input('name'));
        $username = $this->app->repository->normalizeUsername((string) $request->input('username'));
        $email = trim((string) $request->input('email'));
        $password = (string) $request->input('password');
        $role = (string) $request->input('role', 'subscriber');
        $age = (int) $request->input('age', 0);

        if ($name === '' || $username === '' || $email === '' || $password === '') {
            $this->redirect('/register', 'Preencha nome, usuario, e-mail e senha.', 'error');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('/register', 'Informe um e-mail valido.', 'error');
        }

        if (strlen($password) < 6) {
            $this->redirect('/register', 'A senha precisa ter pelo menos 6 caracteres.', 'error');
        }

        if ($age < 18) {
            $this->redirect('/register', 'O cadastro na plataforma e permitido apenas para maiores de 18 anos.', 'error');
        }

        if ((string) $request->input('terms_accepted', '0') !== '1') {
            $this->redirect('/register', 'Voce precisa aceitar os termos para criar a conta.', 'error');
        }

        if (! $request->hasFile('identity_document_file')) {
            $this->redirect('/register', 'Envie um documento de identidade para concluir o cadastro.', 'error');
        }

        $identityDocument = \store_private_uploaded_file(
            $request->file('identity_document_file'),
            'users/identity',
            ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
            10485760
        );

        if ($identityDocument === null) {
            $this->redirect('/register', 'Nao foi possivel processar o documento enviado.', 'error');
        }

        if ($this->app->repository->findUserByEmail($email)) {
            $this->redirect('/register', 'Ja existe uma conta com este e-mail.', 'error');
        }

        if ($this->app->repository->findUserByUsername($username)) {
            $this->redirect('/register', 'Este usuario ja esta em uso.', 'error');
        }

        $user = $this->app->repository->registerUser([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'city' => (string) $request->input('city', 'Brasil'),
            'age' => $age,
            'terms_accepted_at' => date('Y-m-d H:i:s'),
            'terms_version' => '2026-04',
            'identity_document' => $identityDocument,
        ]);

        $this->app->auth->login((int) $user['id']);

        $this->redirect($this->app->auth->homeForRole($user['role']), 'Conta criada com sucesso.');
    }

    public function checkUsername(Request $request): void
    {
        $rawUsername = trim((string) $request->query('username', (string) $request->input('username', '')));
        $normalized = $this->app->repository->normalizeUsername($rawUsername);
        $authUser = $this->user();
        $excludeId = 0;

        if (is_array($authUser) && (int) ($authUser['id'] ?? 0) > 0) {
            $excludeId = (int) ($authUser['id'] ?? 0);
        }

        if (($authUser['role'] ?? '') === 'admin') {
            $excludeId = max($excludeId, (int) $request->query('exclude_id', $excludeId));
        }

        $formatChanged = ltrim(mb_strtolower($rawUsername), '@') !== $normalized;

        if ($rawUsername === '') {
            $this->json([
                'ok' => false,
                'available' => false,
                'normalized' => '',
                'state' => 'empty',
                'message' => 'Digite um @usuario para verificar.',
            ]);
        }

        if ($normalized === '' || mb_strlen($normalized) < 3) {
            $this->json([
                'ok' => false,
                'available' => false,
                'normalized' => $normalized,
                'state' => 'invalid',
                'message' => 'Use pelo menos 3 caracteres com letras, numeros, ponto, underline ou hifen.',
            ]);
        }

        $existing = $this->app->repository->findUserByUsername($normalized);
        $available = ! is_array($existing) || (int) ($existing['id'] ?? 0) === $excludeId;

        $message = $available
            ? ($formatChanged ? 'Disponivel. Sera salvo como @' . $normalized . '.' : 'Disponivel para uso.')
            : 'Esse @usuario ja esta em uso.';

        $this->json([
            'ok' => true,
            'available' => $available,
            'normalized' => $normalized,
            'state' => $available ? 'available' : 'taken',
            'message' => $message,
        ]);
    }

    public function googleRedirect(Request $request): void
    {
        if ($this->app->auth->check()) {
            $this->redirect($this->app->auth->homeForRole($this->user()['role'] ?? 'subscriber'));
        }

        $settings = $this->app->repository->settings();
        $service = new GoogleOAuthService($this->app->config, $settings);

        if (! $service->configured()) {
            $intent = (string) $request->query('intent', 'login');
            $this->redirect($intent === 'register' ? '/register' : '/login', 'Login com Google ainda nao foi configurado.', 'error');
        }

        $intent = in_array((string) $request->query('intent', 'login'), ['login', 'register'], true)
            ? (string) $request->query('intent', 'login')
            : 'login';
        $role = in_array((string) $request->query('role', 'subscriber'), ['subscriber', 'creator'], true)
            ? (string) $request->query('role', 'subscriber')
            : 'subscriber';
        $state = bin2hex(random_bytes(24));

        $_SESSION[self::GOOGLE_SESSION_KEY] = [
            'state' => $state,
            'intent' => $intent,
            'role' => $role,
            'created_at' => time(),
        ];

        redirect_to($service->authorizationUrl($state));
    }

    public function googleCallback(Request $request): void
    {
        if ($this->app->auth->check()) {
            $this->redirect($this->app->auth->homeForRole($this->user()['role'] ?? 'subscriber'));
        }

        $settings = $this->app->repository->settings();
        $service = new GoogleOAuthService($this->app->config, $settings);
        $sessionState = is_array($_SESSION[self::GOOGLE_SESSION_KEY] ?? null) ? $_SESSION[self::GOOGLE_SESSION_KEY] : [];
        $intent = in_array((string) ($sessionState['intent'] ?? 'login'), ['login', 'register'], true)
            ? (string) ($sessionState['intent'] ?? 'login')
            : 'login';
        $fallbackRoute = $intent === 'register' ? '/register' : '/login';

        if (! $service->configured()) {
            unset($_SESSION[self::GOOGLE_SESSION_KEY]);
            $this->redirect($fallbackRoute, 'Login com Google ainda nao foi configurado.', 'error');
        }

        if ((string) $request->query('error', '') !== '') {
            unset($_SESSION[self::GOOGLE_SESSION_KEY]);
            $this->redirect($fallbackRoute, 'O Google nao autorizou a entrada: ' . (string) $request->query('error'), 'error');
        }

        $returnedState = trim((string) $request->query('state', ''));
        $expectedState = trim((string) ($sessionState['state'] ?? ''));
        $code = trim((string) $request->query('code', ''));

        if ($returnedState === '' || $expectedState === '' || ! hash_equals($expectedState, $returnedState) || $code === '') {
            unset($_SESSION[self::GOOGLE_SESSION_KEY]);
            $this->redirect($fallbackRoute, 'Nao foi possivel validar o retorno do Google.', 'error');
        }

        try {
            $token = $service->fetchAccessToken($code);
            $profile = $service->fetchUserInfo((string) ($token['access_token'] ?? ''));
        } catch (\Throwable $exception) {
            unset($_SESSION[self::GOOGLE_SESSION_KEY]);
            $this->redirect($fallbackRoute, 'Falha ao autenticar com Google: ' . $exception->getMessage(), 'error');
        }

        unset($_SESSION[self::GOOGLE_SESSION_KEY]);

        $googleId = trim((string) ($profile['sub'] ?? ''));
        $email = mb_strtolower(trim((string) ($profile['email'] ?? '')));
        $emailVerified = (bool) ($profile['email_verified'] ?? false);

        if ($googleId === '' || $email === '' || ! $emailVerified) {
            $this->redirect($fallbackRoute, 'A conta Google precisa fornecer um e-mail verificado para entrar.', 'error');
        }

        $user = $this->app->repository->findUserByGoogleId($googleId);
        if ($user === null) {
            $user = $this->app->repository->findUserByEmail($email);
            if ($user !== null) {
                $linked = $this->app->repository->linkGoogleIdentity((int) ($user['id'] ?? 0), $googleId, (string) ($profile['picture'] ?? ''));
                if ($linked !== null) {
                    $user = $linked;
                }
            }
        }

        if ($user !== null) {
            if ((string) ($user['status'] ?? 'inactive') !== 'active') {
                $this->redirect($fallbackRoute, 'Sua conta esta inativa no momento.', 'error');
            }

            $this->app->auth->login((int) ($user['id'] ?? 0));
            $this->redirect($this->app->auth->homeForRole((string) ($user['role'] ?? 'subscriber')), 'Login com Google realizado com sucesso.');
        }

        $role = in_array((string) ($sessionState['role'] ?? 'subscriber'), ['subscriber', 'creator'], true)
            ? (string) ($sessionState['role'] ?? 'subscriber')
            : 'subscriber';

        $newUser = $this->app->repository->registerGoogleUser([
            'name' => (string) ($profile['name'] ?? ''),
            'given_name' => (string) ($profile['given_name'] ?? ''),
            'email' => $email,
            'role' => $role,
            'username' => (string) ($profile['preferred_username'] ?? ''),
            'avatar_url' => (string) ($profile['picture'] ?? ''),
            'google_sub' => $googleId,
            'terms_accepted_at' => date('Y-m-d H:i:s'),
            'terms_version' => '2026-04',
        ]);

        $this->app->auth->login((int) ($newUser['id'] ?? 0));

        $this->redirect(user_settings_route($newUser), 'Conta criada com Google. Complete seu perfil para finalizar o cadastro.');
    }

    public function logout(Request $request): void
    {
        $this->validateCsrf($request, '/');
        $this->app->auth->logout();

        $this->redirect('/', 'Sessao encerrada.');
    }
}
