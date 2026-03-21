<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;

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
                'wallet_topup_tokens' => 100,
            ],
        ], null);
    }

    public function subscriptions(Request $request): void
    {
        $this->app->auth->requireRole('subscriber');
        $subscriptions = $this->app->repository->subscriberSubscriptionsData((int) $this->user()['id']);

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
        $messages = $this->app->repository->conversationsData((int) $this->user()['id'], $request->query('conversation') !== null ? (int) $request->query('conversation') : null);

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
        $wallet = $this->app->repository->walletData((int) $this->user()['id']);

        $this->render('pages/subscriber/wallet', [
            'title' => 'Carteira e Tokens',
            'data' => $wallet,
            'sidebar_role' => 'subscriber',
            'prototype' => [
                'page' => 'subscriber.wallet',
                'wallet_topup' => true,
                'wallet_topup_tokens' => 100,
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
        $ok = $this->app->repository->addFunds((int) $this->user()['id'], (int) $request->input('tokens', 0));

        $this->redirect('/subscriber/wallet', $ok ? 'Recarga concluida.' : 'Informe um valor valido para a recarga.', $ok ? 'success' : 'error');
    }
}
