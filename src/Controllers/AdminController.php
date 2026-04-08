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
        $filters = [
            'q' => (string) $request->query('q', ''),
            'role' => (string) $request->query('role', ''),
            'status' => (string) $request->query('status', ''),
            'verification' => (string) $request->query('verification', ''),
        ];

        $this->render('pages/admin/users', [
            'title' => 'Gestao de Usuarios',
            'data' => $this->app->repository->adminUsersData($filters),
            'sidebar_role' => 'admin',
            'prototype' => [
                'page' => 'admin.users',
            ],
        ], null);
    }

    public function moderation(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $data = $this->app->repository->moderationData([
            'q' => (string) $request->query('q', ''),
            'status' => (string) $request->query('status', ''),
        ]);

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

    public function messages(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $filters = [
            'q' => (string) $request->query('q', ''),
            'audience' => (string) $request->query('audience', ''),
        ];

        $this->render('pages/admin/messages', [
            'title' => 'Comunicados Gerais',
            'data' => $this->app->repository->adminMessagesData($filters),
            'sidebar_role' => 'admin',
            'prototype' => [
                'page' => 'admin.messages',
            ],
        ], null);
    }

    public function finance(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $filters = [
            'q' => (string) $request->query('q', ''),
            'type' => (string) $request->query('type', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $this->render('pages/admin/finance', [
            'title' => 'Relatorios Financeiros',
            'data' => $this->app->repository->financeData($filters),
            'sidebar_role' => 'admin',
            'prototype' => [
                'page' => 'admin.finance',
            ],
        ], null);
    }

    public function operations(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $filters = [
            'creator_id' => (int) $request->query('creator_id', 0),
            'content_q' => (string) $request->query('content_q', ''),
            'content_status' => (string) $request->query('content_status', ''),
            'content_page' => (int) $request->query('content_page', 1),
            'plan_q' => (string) $request->query('plan_q', ''),
            'plan_status' => (string) $request->query('plan_status', ''),
            'plan_page' => (int) $request->query('plan_page', 1),
            'micro_q' => (string) $request->query('micro_q', ''),
            'micro_status' => (string) $request->query('micro_status', ''),
            'micro_page' => (int) $request->query('micro_page', 1),
            'live_q' => (string) $request->query('live_q', ''),
            'live_status' => (string) $request->query('live_status', ''),
            'live_page' => (int) $request->query('live_page', 1),
        ];

        $this->render('pages/admin/operations', [
            'title' => 'Operacoes de Conteudo',
            'data' => $this->app->repository->adminOperationsData($filters),
            'sidebar_role' => 'admin',
            'prototype' => [
                'page' => 'admin.operations',
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

    public function createUser(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $this->validateCsrf($request, '/admin/users');
        $ok = $this->app->repository->createAdminManagedUser($request->all());

        $this->redirect('/admin/users', $ok ? 'Usuario criado com sucesso.' : 'Nao foi possivel criar o usuario.', $ok ? 'success' : 'error');
    }

    public function sendAnnouncement(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $this->validateCsrf($request, '/admin/messages');
        $payload = $request->all();

        if ($request->hasFile('attachment_file')) {
            $attachment = store_private_uploaded_file(
                $request->file('attachment_file'),
                'messages/admin',
                ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'mov', 'webm', 'pdf', 'doc', 'docx', 'txt', 'zip', 'rar', '7z'],
                52428800
            );
            if ($attachment !== null) {
                $payload['attachment'] = $attachment;
            }
        }

        $result = $this->app->repository->sendAdminAnnouncement((int) ($this->user()['id'] ?? 0), $payload);

        $this->redirect('/admin/messages', (string) ($result['message'] ?? 'Nao foi possivel enviar o comunicado.'), (bool) ($result['ok'] ?? false) ? 'success' : 'error');
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
        $payload = $request->all();

        if ($request->hasFile('seo_logo_white_file')) {
            $whiteLogoPath = store_uploaded_file($request->file('seo_logo_white_file'), 'admin/branding', ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif']);
            if ($whiteLogoPath !== null) {
                $payload['seo_logo_white_url'] = $whiteLogoPath;
            }
        }

        if ($request->hasFile('seo_logo_color_file')) {
            $colorLogoPath = store_uploaded_file($request->file('seo_logo_color_file'), 'admin/branding', ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif']);
            if ($colorLogoPath !== null) {
                $payload['seo_logo_color_url'] = $colorLogoPath;
            }
        }

        if ($request->hasFile('home_banner_background_file')) {
            $bannerUpload = store_cover_media_file($request->file('home_banner_background_file'), 'admin/branding');
            if (is_array($bannerUpload) && (bool) ($bannerUpload['ok'] ?? false)) {
                $payload['home_banner_background_url'] = (string) ($bannerUpload['path'] ?? '');
            } elseif (is_array($bannerUpload) && trim((string) ($bannerUpload['error'] ?? '')) !== '') {
                $this->redirect('/admin/settings#seo', (string) $bannerUpload['error'], 'error');
            }
        }

        if ($request->hasFile('home_banner_background_mobile_file')) {
            $bannerMobileUpload = store_cover_media_file($request->file('home_banner_background_mobile_file'), 'admin/branding');
            if (is_array($bannerMobileUpload) && (bool) ($bannerMobileUpload['ok'] ?? false)) {
                $payload['home_banner_background_mobile_url'] = (string) ($bannerMobileUpload['path'] ?? '');
            } elseif (is_array($bannerMobileUpload) && trim((string) ($bannerMobileUpload['error'] ?? '')) !== '') {
                $this->redirect('/admin/settings#seo', (string) $bannerMobileUpload['error'], 'error');
            }
        }

        $this->app->repository->updateSettings($payload);

        $this->redirect('/admin/settings', 'Configuracoes salvas.');
    }

    public function updateProfile(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $this->validateCsrf($request, '/admin/settings');
        $payload = $request->all();

        foreach (['avatar_url', 'cover_url'] as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = trim((string) ($payload[$field] ?? ''));
                if ($payload[$field] !== '') {
                    $payload[$field] = media_url((string) $payload[$field]);
                }
            }
        }

        if ((string) ($payload['new_password'] ?? '') !== '' && (string) ($payload['new_password'] ?? '') !== (string) ($payload['new_password_confirmation'] ?? '')) {
            $this->redirect('/admin/settings#perfil', 'Confirme a nova senha corretamente.', 'error');
        }

        if ($request->hasFile('avatar_file')) {
            $avatarPath = store_uploaded_file($request->file('avatar_file'), 'admin/profile/avatar', ['jpg', 'jpeg', 'png', 'webp', 'gif']);
            if ($avatarPath !== null) {
                $payload['avatar_url'] = $avatarPath;
            }
        }

        if ($request->hasFile('cover_file')) {
            $coverUpload = store_cover_media_file($request->file('cover_file'), 'admin/profile/cover');
            if (is_array($coverUpload) && (bool) ($coverUpload['ok'] ?? false)) {
                $payload['cover_url'] = (string) ($coverUpload['path'] ?? '');
            } elseif (is_array($coverUpload) && trim((string) ($coverUpload['error'] ?? '')) !== '') {
                $this->redirect('/admin/settings#perfil', (string) $coverUpload['error'], 'error');
            }
        }

        $ok = $this->app->repository->updateAdminProfile((int) $this->user()['id'], $payload);

        $this->redirect('/admin/settings#perfil', $ok ? 'Perfil do admin atualizado.' : 'Nao foi possivel salvar o perfil do admin.', $ok ? 'success' : 'error');
    }

    public function reviewPayout(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $this->validateCsrf($request, '/admin/finance');
        $ok = $this->app->repository->reviewPayoutRequest(
            (int) $request->input('transaction_id', 0),
            (string) $request->input('status', 'processing'),
            (string) $request->input('admin_note', '')
        );

        $this->redirect('/admin/finance', $ok ? 'Saque atualizado.' : 'Nao foi possivel atualizar o saque.', $ok ? 'success' : 'error');
    }

    public function adjustWallet(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $this->validateCsrf($request, '/admin/finance');
        $ok = $this->app->repository->adminAdjustWalletBalance(
            (int) ($this->user()['id'] ?? 0),
            (int) $request->input('user_id', 0),
            (int) $request->input('luacoins', 0),
            (string) $request->input('direction', 'credit'),
            (string) $request->input('note', '')
        );

        $this->redirect('/admin/finance', $ok ? 'Carteira atualizada.' : 'Nao foi possivel ajustar a carteira.', $ok ? 'success' : 'error');
    }

    public function reviewTopUp(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $this->validateCsrf($request, '/admin/finance');
        $ok = $this->app->repository->reviewTopUpRequest(
            (int) $request->input('transaction_id', 0),
            (string) $request->input('status', 'approved'),
            (string) $request->input('admin_note', '')
        );

        $this->redirect('/admin/finance', $ok ? 'Recarga atualizada.' : 'Nao foi possivel revisar a recarga.', $ok ? 'success' : 'error');
    }

    public function saveManagedContent(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $this->validateCsrf($request, '/admin/operations');
        $ok = $this->app->repository->adminSaveContent((int) $request->input('content_id', 0), $request->all());

        $this->redirect('/admin/operations', $ok ? 'Conteudo atualizado.' : 'Nao foi possivel salvar o conteudo.', $ok ? 'success' : 'error');
    }

    public function deleteManagedContent(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $this->validateCsrf($request, '/admin/operations');
        $ok = $this->app->repository->adminDeleteContent((int) $request->input('content_id', 0));

        $this->redirect('/admin/operations', $ok ? 'Conteudo removido.' : 'Nao foi possivel remover o conteudo.', $ok ? 'success' : 'error');
    }

    public function saveManagedPlan(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $this->validateCsrf($request, '/admin/operations');
        $ok = $this->app->repository->adminSavePlan((int) $request->input('plan_id', 0), $request->all());

        $this->redirect('/admin/operations', $ok ? 'Plano atualizado.' : 'Nao foi possivel salvar o plano.', $ok ? 'success' : 'error');
    }

    public function deleteManagedPlan(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $this->validateCsrf($request, '/admin/operations');
        $result = $this->app->repository->adminDeletePlan((int) $request->input('plan_id', 0));

        $this->redirect('/admin/operations', (string) ($result['message'] ?? 'Plano atualizado.'), ($result['ok'] ?? false) ? 'success' : 'error');
    }

    public function saveManagedLive(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $this->validateCsrf($request, '/admin/operations');
        $ok = $this->app->repository->adminSaveLive((int) $request->input('live_id', 0), $request->all());

        $this->redirect('/admin/operations', $ok ? 'Live atualizada.' : 'Nao foi possivel salvar a live.', $ok ? 'success' : 'error');
    }

    public function deleteManagedLive(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $this->validateCsrf($request, '/admin/operations');
        $ok = $this->app->repository->adminDeleteLive((int) $request->input('live_id', 0));

        $this->redirect('/admin/operations', $ok ? 'Live removida.' : 'Nao foi possivel remover a live.', $ok ? 'success' : 'error');
    }
}
