<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Services\MercadoPagoGateway;

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
                'wallet_topup_tokens' => 100,
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
        $wallet = $this->app->repository->walletData((int) $this->user()['id'], [
            'q' => (string) $request->query('q', ''),
            'type' => (string) $request->query('type', ''),
        ]);

        $this->render('pages/subscriber/wallet', [
            'title' => 'Carteira e LuaCoins',
            'data' => $wallet,
            'sidebar_role' => 'subscriber',
            'prototype' => [
                'page' => 'subscriber.wallet',
                'wallet_topup' => true,
                'wallet_topup_luacoins' => 100,
                'wallet_topup_tokens' => 100,
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
        $ok = $this->app->repository->sendConversationMessage($conversationId, (int) $this->user()['id'], (string) $request->input('body'));

        $this->redirect($redirect, $ok ? 'Mensagem enviada.' : 'Nao foi possivel enviar a mensagem.', $ok ? 'success' : 'error');
    }

    public function addFunds(Request $request): void
    {
        $this->app->auth->requireRole('subscriber');
        $this->validateCsrf($request, '/subscriber/wallet');
        $luacoins = (int) $request->input('luacoins', (int) $request->input('tokens', 0));
        $settings = $this->app->repository->settings();
        $gateway = new MercadoPagoGateway($settings);

        if ($gateway->configured()) {
            $baseUrl = app_base_url($this->app->config, $settings);

            if (! str_starts_with($baseUrl, 'https://')) {
                $this->redirect('/subscriber/wallet', 'Configure uma Site URL com HTTPS no admin para liberar o checkout do Mercado Pago.', 'error');
            }

            $topUp = $this->app->repository->createWalletTopUpRequest((int) $this->user()['id'], $luacoins);

            if (! is_array($topUp)) {
                $this->redirect('/subscriber/wallet', 'Informe uma quantidade valida de LuaCoins.', 'error');
            }

            try {
                $statementDescriptor = mb_strtoupper(preg_replace('/[^a-z0-9 ]+/i', '', (string) ($settings['mercadopago_statement_descriptor'] ?? 'SEXYLUA')) ?: 'SEXYLUA');
                $statementDescriptor = trim(mb_substr($statementDescriptor, 0, 13));
                $reference = (string) ($topUp['external_reference'] ?? '');
                $checkout = $gateway->createWalletTopUpPreference([
                    'external_reference' => $reference,
                    'notification_url' => webhook_url($this->app->config, $settings),
                    'back_urls' => [
                        'success' => path_with_query($baseUrl . '/subscriber/wallet', ['payment_status' => 'success', 'reference' => $reference]),
                        'failure' => path_with_query($baseUrl . '/subscriber/wallet', ['payment_status' => 'failure', 'reference' => $reference]),
                        'pending' => path_with_query($baseUrl . '/subscriber/wallet', ['payment_status' => 'pending', 'reference' => $reference]),
                    ],
                    'auto_return' => 'approved',
                    'binary_mode' => false,
                    'statement_descriptor' => $statementDescriptor !== '' ? $statementDescriptor : 'SEXYLUA',
                    'items' => [[
                        'id' => 'luacoins-' . (string) $luacoins,
                        'title' => $luacoins . ' LuaCoins - SexyLua',
                        'description' => 'Recarga de LuaCoins para assinaturas, gorjetas e desbloqueios.',
                        'quantity' => 1,
                        'currency_id' => 'BRL',
                        'unit_price' => (float) ($topUp['amount_brl_expected'] ?? 0),
                    ]],
                    'metadata' => [
                        'topup_transaction_id' => (int) ($topUp['id'] ?? 0),
                        'user_id' => (int) $this->user()['id'],
                        'luacoins' => $luacoins,
                    ],
                ]);

                $this->app->repository->attachWalletTopUpCheckout((int) ($topUp['id'] ?? 0), $checkout);
                $checkoutUrl = (string) ($checkout['init_point'] ?? '');

                if ($checkoutUrl === '') {
                    $this->redirect('/subscriber/wallet', 'O Mercado Pago nao retornou a URL de checkout.', 'error');
                }

                redirect_to($checkoutUrl);
            } catch (\Throwable $exception) {
                $this->redirect('/subscriber/wallet', 'Nao foi possivel iniciar o checkout do Mercado Pago: ' . $exception->getMessage(), 'error');
            }
        }

        $ok = $this->app->repository->addFunds((int) $this->user()['id'], $luacoins);

        $this->redirect('/subscriber/wallet', $ok ? 'Recarga de LuaCoins concluida.' : 'Informe um valor valido para a recarga.', $ok ? 'success' : 'error');
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
            $coverPath = store_uploaded_file($request->file('cover_file'), 'subscriber/profile/cover', ['jpg', 'jpeg', 'png', 'webp', 'gif']);
            if ($coverPath !== null) {
                $payload['cover_url'] = $coverPath;
            }
        }

        $ok = $this->app->repository->updateSubscriberSettings((int) $this->user()['id'], $payload);

        $this->redirect('/subscriber/settings', $ok ? 'Perfil atualizado com sucesso.' : 'Nao foi possivel salvar seu perfil.', $ok ? 'success' : 'error');
    }
}
