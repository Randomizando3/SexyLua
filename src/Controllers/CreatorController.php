<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;

final class CreatorController extends Controller
{
    public function dashboard(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $dashboard = $this->app->repository->creatorDashboardData((int) $this->user()['id']);

        $this->render('pages/creator/dashboard', [
            'title' => 'Dashboard do Criador',
            'data' => $dashboard,
            'sidebar_role' => 'creator',
            'prototype' => [
                'page' => 'creator.dashboard',
                'creator_live_quick' => true,
            ],
        ], null);
    }

    public function content(Request $request): void
    {
        $this->app->auth->requireRole('creator');

        $this->render('pages/creator/content', [
            'title' => 'Gestao de Conteudo',
            'data' => $this->app->repository->creatorContentData((int) $this->user()['id']),
            'sidebar_role' => 'creator',
            'prototype' => [
                'page' => 'creator.content',
                'creator_content_create' => true,
            ],
        ], null);
    }

    public function favorites(Request $request): void
    {
        $this->app->auth->requireRole('creator');

        $this->render('pages/creator/favorites', [
            'title' => 'Favoritos do Criador',
            'data' => $this->app->repository->creatorFavoritesData((int) $this->user()['id']),
            'sidebar_role' => 'creator',
            'prototype' => [
                'page' => 'creator.favorites',
            ],
        ], null);
    }

    public function memberships(Request $request): void
    {
        $this->app->auth->requireRole('creator');

        $this->render('pages/creator/memberships', [
            'title' => 'Gestao de Assinaturas',
            'data' => $this->app->repository->creatorPlansData((int) $this->user()['id']),
            'sidebar_role' => 'creator',
            'prototype' => [
                'page' => 'creator.memberships',
            ],
        ], null);
    }

    public function live(Request $request): void
    {
        $this->app->auth->requireRole('creator');

        $this->render('pages/creator/live', [
            'title' => 'Configuracao de Live',
            'data' => $this->app->repository->creatorLiveData((int) $this->user()['id']),
            'sidebar_role' => 'creator',
            'prototype' => [
                'page' => 'creator.live',
                'creator_live_quick' => true,
            ],
        ], null);
    }

    public function wallet(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $wallet = $this->app->repository->creatorWalletData((int) $this->user()['id']);

        $this->render('pages/creator/wallet', [
            'title' => 'Carteira Lunar',
            'data' => $wallet,
            'sidebar_role' => 'creator',
            'prototype' => [
                'page' => 'creator.wallet',
                'wallet_payout' => true,
                'payout_tokens' => max(50, (int) ($wallet['min_withdrawal'] ?? 50)),
            ],
        ], null);
    }

    public function settings(Request $request): void
    {
        $this->app->auth->requireRole('creator');

        $this->render('pages/creator/settings', [
            'title' => 'Configuracoes do Criador',
            'data' => $this->app->repository->creatorSettingsData((int) $this->user()['id']),
            'sidebar_role' => 'creator',
            'prototype' => [
                'page' => 'creator.settings',
                'creator_settings' => true,
            ],
        ], null);
    }

    public function createContent(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $this->validateCsrf($request, '/creator/content');
        $this->app->repository->createContent((int) $this->user()['id'], $request->all());

        $this->redirect('/creator/content', 'Conteudo criado com sucesso.');
    }

    public function updateContentStatus(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $this->validateCsrf($request, '/creator/content');
        $ok = $this->app->repository->updateContentStatus((int) $this->user()['id'], (int) $request->input('content_id', 0), (string) $request->input('status', 'draft'));

        $this->redirect('/creator/content', $ok ? 'Status do conteudo atualizado.' : 'Nao foi possivel atualizar o conteudo.', $ok ? 'success' : 'error');
    }

    public function deleteContent(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $this->validateCsrf($request, '/creator/content');
        $ok = $this->app->repository->deleteContent((int) $this->user()['id'], (int) $request->input('content_id', 0));

        $this->redirect('/creator/content', $ok ? 'Conteudo removido.' : 'Nao foi possivel remover o conteudo.', $ok ? 'success' : 'error');
    }

    public function savePlan(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $this->validateCsrf($request, '/creator/memberships');
        $this->app->repository->savePlan((int) $this->user()['id'], $request->all());

        $this->redirect('/creator/memberships', 'Plano salvo com sucesso.');
    }

    public function saveLive(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $this->validateCsrf($request, '/creator/live');
        $this->app->repository->saveLive((int) $this->user()['id'], $request->all());

        $this->redirect('/creator/live', 'Live salva com sucesso.');
    }

    public function updateLiveStatus(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $this->validateCsrf($request, '/creator/live');
        $ok = $this->app->repository->updateLiveStatus((int) $this->user()['id'], (int) $request->input('live_id', 0), (string) $request->input('status', 'scheduled'));

        $this->redirect('/creator/live', $ok ? 'Status da live atualizado.' : 'Nao foi possivel atualizar a live.', $ok ? 'success' : 'error');
    }

    public function requestPayout(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $this->validateCsrf($request, '/creator/wallet');
        $result = $this->app->repository->requestPayout((int) $this->user()['id'], (int) $request->input('tokens', 0));

        $this->redirect('/creator/wallet', $result['message'], $result['ok'] ? 'success' : 'error');
    }

    public function updateSettings(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        $this->validateCsrf($request, '/creator/settings');
        $ok = $this->app->repository->updateCreatorSettings((int) $this->user()['id'], $request->all());

        $this->redirect('/creator/settings', $ok ? 'Configuracoes atualizadas com sucesso.' : 'Nao foi possivel salvar as configuracoes.', $ok ? 'success' : 'error');
    }
}
