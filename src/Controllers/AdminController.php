<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;

final class AdminController extends Controller
{
    public function dashboard(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $dashboard = $this->app->repository->adminDashboardData();

        $this->render('pages/admin/dashboard', [
            'title' => 'Dashboard Admin',
            'data' => $dashboard,
            'sidebar_role' => 'admin',
            'prototype' => [
                'page' => 'admin.dashboard',
            ],
        ], null);
    }

    public function users(Request $request): void
    {
        $this->app->auth->requireRole('admin');

        $this->render('pages/admin/users', [
            'title' => 'Gestao de Usuarios',
            'data' => [
                'users' => $this->app->repository->adminUsersData((string) $request->query('q', '')),
                'query' => (string) $request->query('q', ''),
            ],
            'sidebar_role' => 'admin',
            'prototype' => [
                'page' => 'admin.users',
            ],
        ], null);
    }

    public function moderation(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $data = $this->app->repository->moderationData();

        $this->render('pages/admin/moderation', [
            'title' => 'Moderacao de Conteudo',
            'data' => $data,
            'sidebar_role' => 'admin',
            'prototype' => [
                'page' => 'admin.moderation',
                'moderation' => [
                    'content_ids' => array_map(
                        static fn (array $item): int => (int) $item['id'],
                        array_slice($data['pending'] ?? [], 0, 8)
                    ),
                ],
            ],
        ], null);
    }

    public function finance(Request $request): void
    {
        $this->app->auth->requireRole('admin');

        $this->render('pages/admin/finance', [
            'title' => 'Relatorios Financeiros',
            'data' => $this->app->repository->financeData(),
            'sidebar_role' => 'admin',
            'prototype' => [
                'page' => 'admin.finance',
            ],
        ], null);
    }

    public function settings(Request $request): void
    {
        $this->app->auth->requireRole('admin');

        $this->render('pages/admin/settings', [
            'title' => 'Configuracoes do Sistema',
            'data' => $this->app->repository->settings(),
            'sidebar_role' => 'admin',
            'prototype' => [
                'page' => 'admin.settings',
                'admin_settings' => true,
            ],
        ], null);
    }

    public function updateUser(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $this->validateCsrf($request, '/admin/users');
        $ok = $this->app->repository->updateUser((int) $request->input('user_id', 0), $request->all());

        $this->redirect('/admin/users', $ok ? 'Usuario atualizado.' : 'Nao foi possivel atualizar o usuario.', $ok ? 'success' : 'error');
    }

    public function reviewContent(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $this->validateCsrf($request, '/admin/moderation');
        $ok = $this->app->repository->reviewContent((int) $this->user()['id'], (int) $request->input('content_id', 0), (string) $request->input('decision', 'rejected'), (string) $request->input('moderation_feedback', ''));

        $this->redirect('/admin/moderation', $ok ? 'Conteudo revisado com sucesso.' : 'Nao foi possivel revisar o conteudo.', $ok ? 'success' : 'error');
    }

    public function updateSettings(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $this->validateCsrf($request, '/admin/settings');
        $this->app->repository->updateSettings($request->all());

        $this->redirect('/admin/settings', 'Configuracoes salvas.');
    }
}
