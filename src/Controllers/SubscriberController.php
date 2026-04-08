<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Services\SyncPayGateway;

final class SubscriberController extends Controller
{
    public function dashboard(Request $request): void
    {
        $this->app->auth->requireRole('subscriber');
        $dashboard = $this->app->repository->subscriberDashboardData((int) $this->user()['id']);

        $this->render('pages/subscriber/dashboard', [
            'title' => 'Area do Assinante',
            'data' => $dashboard,
            'sidebar_role' => 'subscriber',
            'prototype' => [
                'page' => 'subscriber.dashboard',
                'wallet_topup' => true,
                'wallet_topup_luacoins' => 100,
            ],
        ], null);
    }

    public function subscriptions(Request $request): void
    {
        $this->app->auth->requireRole('subscriber');
        $subscriptions = $this->app->repository->subscriberSubscriptionsData((int) $this->user()['id'], [
            'q' => (string) $request->query('q', ''),
            'status' => (string) $request->query('status', ''),
        ]);

        $this->render('pages/subscriber/subscriptions', [
            'title' => 'Minhas Assinaturas',
            'data' => $subscriptions,
            'sidebar_role' => 'subscriber',
            'prototype' => [
                'page' => 'subscriber.subscriptions',
            ],
        ], null);
    }

    public function favorites(Request $request): void
    {
        $this->app->auth->requireRole('subscriber');
        $favorites = $this->app->repository->favoritesData((int) $this->user()['id']);

        $this->render('pages/subscriber/favorites', [
            'title' => 'Favoritos e Salvos',
            'data' => $favorites,
            'sidebar_role' => 'subscriber',
            'prototype' => [
                'page' => 'subscriber.favorites',
            ],
        ], null);
    }

    public function messages(Request $request): void
    {
        $this->app->auth->requireRole('subscriber');
        $messages = $this->app->repository->conversationsData(
            (int) $this->user()['id'],
            $request->query('conversation') !== null ? (int) $request->query('conversation') : null,
            [
                'q' => (string) $request->query('q', ''),
                'announcement' => (int) $request->query('announcement', 0),
            ]
        );

        $this->render('pages/subscriber/messages', [
            'title' => 'Mensagens e Chat',
            'data' => $messages,
            'sidebar_role' => 'subscriber',
            'prototype' => [
                'page' => 'subscriber.messages',
                'subscriber_message' => [
                    'conversation_id' => $messages['selected_conversation']['id'] ?? null,
                ],
            ],
        ], null);
    }

    public function wallet(Request $request): void
    {
        $this->app->auth->requireRole('subscriber');
        $settings = $this->app->repository->settings();
        $topUpId = (int) $request->query('topup', 0);
        $shouldRefresh = (string) $request->query('refresh', '0') === '1';
        $gateway = new SyncPayGateway($settings);

        $selectedTopUp = $topUpId > 0
            ? $this->app->repository->findWalletTransactionForUser((int) $this->user()['id'], $topUpId)
            : $this->app->repository->latestPendingWalletTopUpForUser((int) $this->user()['id']);
        $syncCandidate = $selectedTopUp ?? $this->app->repository->latestSyncableWalletTopUpForUser((int) $this->user()['id']);

        if (
            $syncCandidate !== null
            && (string) ($syncCandidate['type'] ?? '') === 'top_up_pending'
            && $gateway->canFetchTransactionStatus()
            && ($shouldRefresh || strtolower((string) ($syncCandidate['status'] ?? 'pending')) === 'pending' || trim((string) ($syncCandidate['provider_status_raw'] ?? '')) !== '')
        ) {
            try {
                $statusPayload = $gateway->fetchTransactionStatus((string) ($syncCandidate['provider_payment_id'] ?? $syncCandidate['provider_checkout_id'] ?? ''));
                $this->app->repository->syncSyncPayWalletTopUp((int) ($syncCandidate['id'] ?? 0), $statusPayload);
            } catch (\Throwable) {
                // Mantem a tela utilizavel mesmo se a SyncPay nao responder ou nao estiver acessivel agora.
            }
        }

        $wallet = $this->app->repository->walletData((int) $this->user()['id'], [
            'q' => (string) $request->query('q', ''),
            'type' => (string) $request->query('type', ''),
        ]);
        $selectedTopUp = $topUpId > 0
            ? $this->app->repository->findWalletTransactionForUser((int) $this->user()['id'], $topUpId)
            : $this->app->repository->latestPendingWalletTopUpForUser((int) $this->user()['id']);

        $this->render('pages/subscriber/wallet', [
            'title' => 'Carteira e LuaCoins',
            'data' => $wallet + [
                'selected_topup' => $selectedTopUp,
                'syncpay_enabled' => $gateway->configured(),
            ],
            'sidebar_role' => 'subscriber',
            'prototype' => [
                'page' => 'subscriber.wallet',
                'wallet_topup' => true,
                'wallet_topup_luacoins' => 100,
            ],
        ], null);
    }

    public function settings(Request $request): void
    {
        $this->app->auth->requireRole('subscriber');

        $this->render('pages/subscriber/settings', [
            'title' => 'Configuracoes do Assinante',
            'data' => $this->app->repository->subscriberSettingsData((int) $this->user()['id']),
            'sidebar_role' => 'subscriber',
            'prototype' => [
                'page' => 'subscriber.settings',
            ],
        ], null);
    }

    public function subscribe(Request $request): void
    {
        $this->app->auth->requireRole('subscriber');
        $this->validateCsrf($request, '/subscriber/subscriptions');
        $result = $this->app->repository->subscribeToPlan((int) $this->user()['id'], (int) $request->input('plan_id', 0));

        $this->redirect('/subscriber/subscriptions', $result['message'], $result['ok'] ? 'success' : 'error');
    }

    public function cancelSubscription(Request $request): void
    {
        $this->app->auth->requireRole('subscriber');
        $this->validateCsrf($request, '/subscriber/subscriptions');
        $ok = $this->app->repository->cancelSubscription((int) $this->user()['id'], (int) $request->input('subscription_id', 0));

        $this->redirect('/subscriber/subscriptions', $ok ? 'Assinatura cancelada.' : 'Nao foi possivel cancelar a assinatura.', $ok ? 'success' : 'error');
    }

    public function toggleFavorite(Request $request): void
    {
        $this->app->auth->requireRole('subscriber');
        $redirect = (string) $request->input('redirect', '/subscriber/favorites');
        $this->validateCsrf($request, $redirect);
        $active = $this->app->repository->toggleFavoriteCreator((int) $this->user()['id'], (int) $request->input('creator_id', 0));

        $this->redirect($redirect, $active ? 'Criador adicionado aos favoritos.' : 'Criador removido dos favoritos.');
    }

    public function toggleSaved(Request $request): void
    {
        $this->app->auth->requireRole('subscriber');
        $redirect = (string) $request->input('redirect', '/subscriber/favorites');
        $this->validateCsrf($request, $redirect);
        $active = $this->app->repository->toggleSavedContent((int) $this->user()['id'], (int) $request->input('content_id', 0));

        $this->redirect($redirect, $active ? 'Conteudo salvo com sucesso.' : 'Conteudo removido dos salvos.');
    }

    public function sendMessage(Request $request): void
    {
        $this->app->auth->requireRole('subscriber');
        $conversationId = (int) $request->input('conversation_id', 0);
        $redirect = path_with_query('/subscriber/messages', ['conversation' => $conversationId]);
        $this->validateCsrf($request, $redirect);
        $options = [];

        if ($request->hasFile('attachment_file')) {
            $attachment = \store_private_uploaded_file(
                $request->file('attachment_file'),
                'messages/subscriber',
                ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'mov', 'webm', 'pdf', 'doc', 'docx', 'txt', 'zip', 'rar', '7z'],
                52428800
            );
            if ($attachment !== null) {
                $options['attachment'] = $attachment;
            }
        }

        $ok = $this->app->repository->sendConversationMessage($conversationId, (int) $this->user()['id'], (string) $request->input('body'), $options);

        $this->redirect($redirect, $ok ? 'Mensagem enviada.' : 'Nao foi possivel enviar a mensagem.', $ok ? 'success' : 'error');
    }

    public function addFunds(Request $request): void
    {
        $this->app->auth->requireRole('subscriber');
        $this->validateCsrf($request, '/subscriber/wallet');
        $luacoins = (int) $request->input('luacoins', (int) $request->input('tokens', 0));
        $document = preg_replace('/\D+/', '', (string) $request->input('cpf', (string) $request->input('document', '')));
        $settings = $this->app->repository->settings();
        $gateway = new SyncPayGateway($settings);
        $minimumDeposit = max(1, (int) ($settings['deposit_min_luacoins'] ?? 100));

        if (! $gateway->configured()) {
            $this->redirect('/subscriber/wallet', 'Configure a SyncPay no admin antes de gerar recargas PIX.', 'error');
        }

        if ($luacoins < $minimumDeposit) {
            $this->redirect('/subscriber/wallet', 'A recarga minima configurada hoje e de ' . $minimumDeposit . ' LuaCoins.', 'error');
        }

        if ($document === '' || ! in_array(strlen($document), [11, 14], true)) {
            $this->redirect('/subscriber/wallet', 'Informe um CPF ou CNPJ valido para gerar o PIX.', 'error');
        }

        $topUp = $this->app->repository->createWalletTopUpRequest((int) $this->user()['id'], $luacoins, 'syncpay');

        if (! is_array($topUp)) {
            $this->redirect('/subscriber/wallet', 'Informe uma quantidade valida de LuaCoins.', 'error');
        }

        try {
            $checkout = $gateway->createWalletTopUpCharge([
                'amount' => (float) ($topUp['amount_brl_expected'] ?? 0),
                'document' => $document,
                'name' => (string) ($this->user()['name'] ?? 'Assinante SexyLua'),
                'email' => (string) ($this->user()['email'] ?? ''),
                'description' => 'Recarga de ' . $luacoins . ' LuaCoins',
                'ip' => (string) $request->server('REMOTE_ADDR', ''),
                'webhook_url' => path_with_query(webhook_url($this->app->config, $settings, '/webhook/syncpay'), [
                    'topup' => (int) ($topUp['id'] ?? 0),
                ]),
                'pix_expires_in_days' => (int) ($settings['syncpay_pix_expires_in_days'] ?? 2),
            ]);

            $this->app->repository->attachWalletTopUpCheckout((int) ($topUp['id'] ?? 0), $checkout);
            $this->redirect(path_with_query('/subscriber/wallet', [
                'topup' => (int) ($topUp['id'] ?? 0),
                'payment_status' => 'pending',
            ]), 'PIX gerado com sucesso. Copie o codigo e conclua o pagamento na sua carteira.', 'success');
        } catch (\Throwable $exception) {
            $this->app->repository->discardWalletTopUpRequest((int) $this->user()['id'], (int) ($topUp['id'] ?? 0));

            $this->redirect('/subscriber/wallet', 'Nao foi possivel gerar o PIX na SyncPay: ' . $exception->getMessage(), 'error');
        }
    }

    public function updateSettings(Request $request): void
    {
        $this->app->auth->requireRole('subscriber');
        $this->validateCsrf($request, '/subscriber/settings');
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
            $this->redirect('/subscriber/settings', 'Confirme a nova senha corretamente.', 'error');
        }

        if ($request->hasFile('avatar_file')) {
            $avatarPath = store_uploaded_file($request->file('avatar_file'), 'subscriber/profile/avatar', ['jpg', 'jpeg', 'png', 'webp', 'gif']);
            if ($avatarPath !== null) {
                $payload['avatar_url'] = $avatarPath;
            }
        }

        if ($request->hasFile('cover_file')) {
            $coverUpload = store_cover_media_file($request->file('cover_file'), 'subscriber/profile/cover');
            if (is_array($coverUpload) && (bool) ($coverUpload['ok'] ?? false)) {
                $payload['cover_url'] = (string) ($coverUpload['path'] ?? '');
            } elseif (is_array($coverUpload) && trim((string) ($coverUpload['error'] ?? '')) !== '') {
                $this->redirect('/subscriber/settings', (string) $coverUpload['error'], 'error');
            }
        }

        if ($request->hasFile('identity_document_file')) {
            $identityDocument = store_private_uploaded_file(
                $request->file('identity_document_file'),
                'users/identity',
                ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
                10485760
            );

            if ($identityDocument !== null) {
                $payload['identity_document'] = $identityDocument;
            }
        }

        $ok = $this->app->repository->updateSubscriberSettings((int) $this->user()['id'], $payload);

        $this->redirect('/subscriber/settings', $ok ? 'Perfil atualizado com sucesso.' : 'Nao foi possivel salvar seu perfil.', $ok ? 'success' : 'error');
    }
}
