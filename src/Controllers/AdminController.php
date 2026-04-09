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
        $finance = $this->app->repository->financeData();
        $filters = [
            'tab' => $this->financeTabValue((string) $request->query('tab', 'payouts')),
            'topups' => [
                'q' => (string) $request->query('topup_q', ''),
                'status' => (string) $request->query('topup_status', ''),
                'page' => (int) $request->query('topup_page', 1),
            ],
            'payouts' => [
                'q' => (string) $request->query('payout_q', ''),
                'status' => (string) $request->query('payout_status', ''),
                'page' => (int) $request->query('payout_page', 1),
            ],
            'transactions' => [
                'q' => (string) $request->query('transaction_q', ''),
                'type' => (string) $request->query('transaction_type', ''),
                'status' => (string) $request->query('transaction_status', ''),
                'page' => (int) $request->query('transaction_page', 1),
            ],
            'adjustments' => [
                'q' => (string) $request->query('adjustment_q', ''),
                'page' => (int) $request->query('adjustment_page', 1),
            ],
        ];

        $this->render('pages/admin/finance', [
            'title' => 'Relatorios Financeiros',
            'data' => array_merge($finance, ['filters' => $filters]),
            'sidebar_role' => 'admin',
            'prototype' => [
                'page' => 'admin.finance',
            ],
        ], null);
    }

    public function exportFinanceCsv(Request $request): void
    {
        $this->app->auth->requireRole('admin');
        $finance = $this->app->repository->financeData();
        $transactions = is_array($finance['transactions'] ?? null) ? $finance['transactions'] : [];
        $luacoinPriceBrl = (float) ($finance['luacoin_price_brl'] ?? 0.07);
        $days = $this->financeReportDaysValue((int) $request->query('days', 7));
        $item = $this->financeReportItemValue((string) $request->query('item', 'all'));
        $filteredTransactions = $this->financeFilterTransactionsByDays($transactions, $days);
        $sheets = $this->financeReportSheets($filteredTransactions, $luacoinPriceBrl, $item);

        if (! class_exists(\ZipArchive::class)) {
            http_response_code(500);
            echo 'A extensao ZIP do PHP nao esta disponivel para gerar o relatorio XLSX.';
            exit;
        }

        $xlsxPath = $this->buildFinanceWorkbookXlsx($sheets);
        $fileSuffix = $item === 'all' ? 'geral' : $item;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="sexylua-relatorio-' . $fileSuffix . '-' . $days . 'd-' . date('Ymd-His') . '.xlsx"');
        header('Content-Length: ' . (string) filesize($xlsxPath));

        readfile($xlsxPath);
        @unlink($xlsxPath);
        exit;
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

    public function integrations(Request $request): void
    {
        $this->app->auth->requireRole('admin');

        $this->render('pages/admin/integrations', [
            'title' => 'Integracoes do Sistema',
            'data' => $this->app->repository->settings(),
            'sidebar_role' => 'admin',
            'prototype' => [
                'page' => 'admin.integrations',
            ],
        ], null);
    }

    public function seo(Request $request): void
    {
        $this->app->auth->requireRole('admin');

        $this->render('pages/admin/seo', [
            'title' => 'SEO e Branding',
            'data' => $this->app->repository->settings(),
            'sidebar_role' => 'admin',
            'prototype' => [
                'page' => 'admin.seo',
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
        $returnTo = (string) $request->input('return_to', '/admin/settings');
        $this->validateCsrf($request, $returnTo);
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
                $this->redirect($returnTo, (string) $bannerUpload['error'], 'error');
            }
        }

        if ($request->hasFile('home_banner_background_mobile_file')) {
            $bannerMobileUpload = store_cover_media_file($request->file('home_banner_background_mobile_file'), 'admin/branding');
            if (is_array($bannerMobileUpload) && (bool) ($bannerMobileUpload['ok'] ?? false)) {
                $payload['home_banner_background_mobile_url'] = (string) ($bannerMobileUpload['path'] ?? '');
            } elseif (is_array($bannerMobileUpload) && trim((string) ($bannerMobileUpload['error'] ?? '')) !== '') {
                $this->redirect($returnTo, (string) $bannerMobileUpload['error'], 'error');
            }
        }

        $this->app->repository->updateSettings($payload);

        $this->redirect($returnTo, 'Configuracoes salvas.');
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

        $this->redirect($this->adminFinanceRedirectUrl((string) $request->input('tab', 'payouts')), $ok ? 'Saque atualizado.' : 'Nao foi possivel atualizar o saque.', $ok ? 'success' : 'error');
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

        $this->redirect($this->adminFinanceRedirectUrl((string) $request->input('tab', 'adjustments')), $ok ? 'Carteira atualizada.' : 'Nao foi possivel ajustar a carteira.', $ok ? 'success' : 'error');
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

        $this->redirect($this->adminFinanceRedirectUrl((string) $request->input('tab', 'topups')), $ok ? 'Recarga atualizada.' : 'Nao foi possivel revisar a recarga.', $ok ? 'success' : 'error');
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

    private function financeTabValue(string $tab): string
    {
        return in_array($tab, ['topups', 'payouts', 'transactions', 'adjustments'], true) ? $tab : 'payouts';
    }

    private function financeReportDaysValue(int $days): int
    {
        return in_array($days, [1, 7, 30], true) ? $days : 7;
    }

    private function financeReportItemValue(string $item): string
    {
        return in_array($item, ['all', 'topups', 'payouts', 'transactions', 'adjustments'], true) ? $item : 'all';
    }

    private function adminFinanceRedirectUrl(string $tab): string
    {
        return path_with_query('/admin/finance', ['tab' => $this->financeTabValue($tab)]);
    }

    private function financeFilterTransactionsByDays(array $transactions, int $days): array
    {
        $cutoff = (new \DateTimeImmutable())->modify('-' . max(1, $days) . ' days');

        return array_values(array_filter($transactions, static function (array $transaction) use ($cutoff): bool {
            $createdAt = trim((string) ($transaction['created_at'] ?? ''));
            if ($createdAt === '') {
                return false;
            }

            try {
                return new \DateTimeImmutable($createdAt) >= $cutoff;
            } catch (\Throwable) {
                return false;
            }
        }));
    }

    private function financeReportSheets(array $transactions, float $luacoinPriceBrl, string $item): array
    {
        $groups = match ($item) {
            'topups' => ['topups'],
            'payouts' => ['payouts'],
            'adjustments' => ['adjustments'],
            'transactions' => ['transactions'],
            default => ['topups', 'payouts', 'transactions', 'adjustments'],
        };

        $sheets = [];

        foreach ($groups as $group) {
            $sheets[] = match ($group) {
                'topups' => $this->financeTopupsSheet($transactions, $luacoinPriceBrl),
                'payouts' => $this->financePayoutsSheet($transactions, $luacoinPriceBrl),
                'adjustments' => $this->financeAdjustmentsSheet($transactions, $luacoinPriceBrl),
                default => $this->financeTransactionsSheet($transactions, $luacoinPriceBrl),
            };
        }

        return $sheets;
    }

    private function financeTopupsSheet(array $transactions, float $luacoinPriceBrl): array
    {
        $rows = [];

        foreach ($transactions as $transaction) {
            $type = (string) ($transaction['type'] ?? '');
            if (! in_array($type, ['top_up_pending', 'top_up'], true)) {
                continue;
            }

            $user = is_array($transaction['user'] ?? null) ? $transaction['user'] : [];
            $rows[] = [
                (string) ((int) ($transaction['id'] ?? 0)),
                (string) ($transaction['created_at'] ?? ''),
                user_handle($user, 'usuario'),
                (string) ($user['email'] ?? ''),
                (string) ($transaction['status'] ?? ($type === 'top_up_pending' ? 'pending' : 'approved')),
                (string) ($transaction['provider'] ?? 'syncpay'),
                (string) ((int) ($transaction['amount'] ?? 0)),
                brl_amount(luacoin_to_brl((int) ($transaction['amount'] ?? 0), $luacoinPriceBrl)),
                (string) ($transaction['external_reference'] ?? ''),
                (string) ($transaction['note'] ?? ''),
                (string) ($transaction['admin_note'] ?? ''),
            ];
        }

        return [
            'title' => 'Recargas',
            'headers' => ['ID', 'Data', 'Usuario', 'E-mail', 'Status', 'Provedor', 'LuaCoins', 'Valor BRL', 'Referencia', 'Nota', 'Nota admin'],
            'rows' => $rows,
        ];
    }

    private function financePayoutsSheet(array $transactions, float $luacoinPriceBrl): array
    {
        $rows = [];

        foreach ($transactions as $transaction) {
            if ((string) ($transaction['type'] ?? '') !== 'payout_request') {
                continue;
            }

            $user = is_array($transaction['user'] ?? null) ? $transaction['user'] : [];
            $rows[] = [
                (string) ((int) ($transaction['id'] ?? 0)),
                (string) ($transaction['created_at'] ?? ''),
                user_handle($user, 'usuario'),
                (string) ($user['email'] ?? ''),
                (string) ($transaction['status'] ?? 'pending'),
                (string) ($transaction['payout_method'] ?? 'pix'),
                (string) ($transaction['payout_key'] ?? ''),
                (string) ((int) ($transaction['amount'] ?? 0)),
                brl_amount(luacoin_to_brl((int) ($transaction['amount'] ?? 0), $luacoinPriceBrl)),
                (string) ($transaction['note'] ?? ''),
                (string) ($transaction['admin_note'] ?? ''),
            ];
        }

        return [
            'title' => 'Saques',
            'headers' => ['ID', 'Data', 'Usuario', 'E-mail', 'Status', 'Metodo', 'Chave PIX', 'LuaCoins', 'Valor BRL', 'Nota', 'Nota admin'],
            'rows' => $rows,
        ];
    }

    private function financeTransactionsSheet(array $transactions, float $luacoinPriceBrl): array
    {
        $rows = [];

        foreach ($transactions as $transaction) {
            $user = is_array($transaction['user'] ?? null) ? $transaction['user'] : [];
            $creator = is_array($transaction['creator'] ?? null) ? $transaction['creator'] : [];

            $rows[] = [
                (string) ((int) ($transaction['id'] ?? 0)),
                (string) ($transaction['created_at'] ?? ''),
                (string) ($transaction['type'] ?? ''),
                (string) ($transaction['status'] ?? 'completed'),
                (string) ($transaction['direction'] ?? 'in'),
                user_handle($user, 'usuario'),
                (string) ($user['email'] ?? ''),
                user_handle($creator, ''),
                (string) ((int) ($transaction['amount'] ?? 0)),
                brl_amount(luacoin_to_brl((int) ($transaction['amount'] ?? 0), $luacoinPriceBrl)),
                (string) ($transaction['external_reference'] ?? ''),
                (string) ($transaction['note'] ?? ''),
                (string) ($transaction['admin_note'] ?? ''),
            ];
        }

        return [
            'title' => 'Transacoes',
            'headers' => ['ID', 'Data', 'Tipo', 'Status', 'Direcao', 'Usuario', 'E-mail', 'Criador', 'LuaCoins', 'Valor BRL', 'Referencia', 'Nota', 'Nota admin'],
            'rows' => $rows,
        ];
    }

    private function financeAdjustmentsSheet(array $transactions, float $luacoinPriceBrl): array
    {
        $rows = [];

        foreach ($transactions as $transaction) {
            if (! in_array((string) ($transaction['type'] ?? ''), ['admin_credit', 'admin_debit'], true)) {
                continue;
            }

            $user = is_array($transaction['user'] ?? null) ? $transaction['user'] : [];
            $rows[] = [
                (string) ((int) ($transaction['id'] ?? 0)),
                (string) ($transaction['created_at'] ?? ''),
                (string) ($transaction['type'] ?? ''),
                (string) ($transaction['direction'] ?? 'in'),
                user_handle($user, 'usuario'),
                (string) ($user['email'] ?? ''),
                (string) ((int) ($transaction['amount'] ?? 0)),
                brl_amount(luacoin_to_brl((int) ($transaction['amount'] ?? 0), $luacoinPriceBrl)),
                (string) ($transaction['note'] ?? ''),
                (string) ($transaction['admin_note'] ?? ''),
            ];
        }

        return [
            'title' => 'Ajustes',
            'headers' => ['ID', 'Data', 'Tipo', 'Direcao', 'Usuario', 'E-mail', 'LuaCoins', 'Valor BRL', 'Nota', 'Nota admin'],
            'rows' => $rows,
        ];
    }

    private function buildFinanceWorkbookXlsx(array $sheets): string
    {
        $path = tempnam(sys_get_temp_dir(), 'sexylua-finance-');
        if ($path === false) {
            throw new \RuntimeException('Nao foi possivel criar o arquivo temporario do relatorio.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($path, \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Nao foi possivel abrir o arquivo XLSX para escrita.');
        }

        $sheetCount = count($sheets);
        $sheetNames = [];
        foreach ($sheets as $index => $sheet) {
            $sheetNames[] = $this->sanitizeWorksheetTitle((string) ($sheet['title'] ?? ('Planilha ' . ($index + 1))));
            $zip->addFromString('xl/worksheets/sheet' . ($index + 1) . '.xml', $this->financeWorksheetXml($sheet['headers'] ?? [], $sheet['rows'] ?? []));
        }

        $zip->addFromString('[Content_Types].xml', $this->financeWorkbookContentTypesXml($sheetCount));
        $zip->addFromString('_rels/.rels', $this->financeWorkbookRootRelsXml());
        $zip->addFromString('docProps/app.xml', $this->financeWorkbookAppPropsXml($sheetNames));
        $zip->addFromString('docProps/core.xml', $this->financeWorkbookCorePropsXml());
        $zip->addFromString('xl/workbook.xml', $this->financeWorkbookXml($sheetNames));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->financeWorkbookRelsXml($sheetCount));
        $zip->addFromString('xl/styles.xml', $this->financeWorkbookStylesXml());
        $zip->close();

        return $path;
    }

    private function financeWorksheetXml(array $headers, array $rows): string
    {
        $rowCount = max(1, count($rows) + 1);
        $columnCount = max(1, count($headers));
        $lastCell = $this->xlsxColumnName($columnCount) . $rowCount;
        $widths = [];

        for ($index = 0; $index < $columnCount; $index++) {
            $maxLength = mb_strlen((string) ($headers[$index] ?? ''), 'UTF-8');
            foreach ($rows as $row) {
                $maxLength = max($maxLength, mb_strlen((string) ($row[$index] ?? ''), 'UTF-8'));
            }
            $widths[] = min(42, max(12, $maxLength + 2));
        }

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<dimension ref="A1:' . $lastCell . '"/>'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="20"/>'
            . '<cols>';

        foreach ($widths as $index => $width) {
            $column = $index + 1;
            $xml .= '<col min="' . $column . '" max="' . $column . '" width="' . number_format((float) $width, 2, '.', '') . '" customWidth="1"/>';
        }

        $xml .= '</cols><sheetData>';

        $allRows = array_merge([$headers], $rows);
        foreach ($allRows as $rowIndex => $row) {
            $xml .= '<row r="' . ($rowIndex + 1) . '">';
            for ($columnIndex = 0; $columnIndex < $columnCount; $columnIndex++) {
                $cellRef = $this->xlsxColumnName($columnIndex + 1) . ($rowIndex + 1);
                $cellValue = (string) ($row[$columnIndex] ?? '');
                $style = $rowIndex === 0 ? ' s="1"' : '';
                $xml .= '<c r="' . $cellRef . '" t="inlineStr"' . $style . '><is><t xml:space="preserve">' . $this->xlsxXml($cellValue) . '</t></is></c>';
            }
            $xml .= '</row>';
        }

        $xml .= '</sheetData><autoFilter ref="A1:' . $lastCell . '"/>'
            . '<pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>'
            . '</worksheet>';

        return $xml;
    }

    private function financeWorkbookContentTypesXml(int $sheetCount): string
    {
        $overrides = '';
        for ($sheet = 1; $sheet <= $sheetCount; $sheet++) {
            $overrides .= '<Override PartName="/xl/worksheets/sheet' . $sheet . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . $overrides
            . '</Types>';
    }

    private function financeWorkbookRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function financeWorkbookAppPropsXml(array $sheetNames): string
    {
        $parts = '';
        foreach ($sheetNames as $sheetName) {
            $parts .= '<vt:lpstr>' . $this->xlsxXml($sheetName) . '</vt:lpstr>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>SexyLua</Application>'
            . '<DocSecurity>0</DocSecurity>'
            . '<ScaleCrop>false</ScaleCrop>'
            . '<HeadingPairs><vt:vector size="2" baseType="variant"><vt:variant><vt:lpstr>Worksheets</vt:lpstr></vt:variant><vt:variant><vt:i4>' . count($sheetNames) . '</vt:i4></vt:variant></vt:vector></HeadingPairs>'
            . '<TitlesOfParts><vt:vector size="' . count($sheetNames) . '" baseType="lpstr">' . $parts . '</vt:vector></TitlesOfParts>'
            . '<Company>SexyLua</Company>'
            . '<LinksUpToDate>false</LinksUpToDate><SharedDoc>false</SharedDoc><HyperlinksChanged>false</HyperlinksChanged><AppVersion>1.0</AppVersion>'
            . '</Properties>';
    }

    private function financeWorkbookCorePropsXml(): string
    {
        $created = date('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>Relatorio financeiro SexyLua</dc:title>'
            . '<dc:creator>SexyLua</dc:creator>'
            . '<cp:lastModifiedBy>SexyLua</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $created . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $created . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    private function financeWorkbookXml(array $sheetNames): string
    {
        $sheetsXml = '';
        foreach ($sheetNames as $index => $sheetName) {
            $sheetId = $index + 1;
            $sheetsXml .= '<sheet name="' . $this->xlsxXml($sheetName) . '" sheetId="' . $sheetId . '" r:id="rId' . $sheetId . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<bookViews><workbookView xWindow="0" yWindow="0" windowWidth="28800" windowHeight="16020"/></bookViews>'
            . '<sheets>' . $sheetsXml . '</sheets>'
            . '</workbook>';
    }

    private function financeWorkbookRelsXml(int $sheetCount): string
    {
        $relationships = '';
        for ($sheet = 1; $sheet <= $sheetCount; $sheet++) {
            $relationships .= '<Relationship Id="rId' . $sheet . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $sheet . '.xml"/>';
        }

        $relationships .= '<Relationship Id="rId' . ($sheetCount + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $relationships
            . '</Relationships>';
    }

    private function financeWorkbookStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="11"/><name val="Aptos"/></font><font><b/><sz val="11"/><name val="Aptos"/></font></fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function sanitizeWorksheetTitle(string $title): string
    {
        $title = preg_replace('/[\\\\\\/*?:\\[\\]]+/', '-', trim($title)) ?? 'Planilha';
        $title = trim($title);
        if ($title === '') {
            $title = 'Planilha';
        }

        return mb_substr($title, 0, 31, 'UTF-8');
    }

    private function xlsxColumnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }

        return $name !== '' ? $name : 'A';
    }

    private function xlsxXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
