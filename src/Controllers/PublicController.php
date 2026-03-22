<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;

final class PublicController extends Controller
{
    public function home(Request $request): void
    {
        $home = $this->app->repository->homepageData();
        $this->render('pages/public/home', [
            'title' => 'SexyLua',
            'data' => $home,
            'prototype' => [
                'page' => 'public.home',
                'live' => ['id' => $home['live_now'][0]['id'] ?? 1],
                'profile' => ['creator_id' => $home['featured_creators'][0]['id'] ?? 2],
            ],
        ], null);
    }

    public function explore(Request $request): void
    {
        $explore = $this->app->repository->exploreData([
            'q' => $request->query('q', ''),
            'kind' => $request->query('kind', ''),
            'live_only' => $request->query('live_only', '') === '1',
        ]);

        $this->render('pages/public/explore', [
            'title' => 'Explorar',
            'data' => $explore,
            'prototype' => [
                'page' => 'public.explore',
                'live' => ['id' => $explore['lives'][0]['id'] ?? 1],
                'profile' => ['creator_id' => $explore['creators'][0]['id'] ?? 2],
            ],
        ], null);
    }

    public function profile(Request $request): void
    {
        $creator = $this->app->repository->findCreatorBySlugOrId(
            $request->query('slug'),
            $request->query('id') !== null ? (int) $request->query('id') : null
        );

        if (! $creator) {
            http_response_code(404);
            $this->render('pages/public/not-found', [
                'title' => 'Perfil nao encontrado',
                'description' => 'Este criador nao foi encontrado.',
            ], 'layouts/marketing');

            return;
        }

        $this->render('pages/public/profile', [
            'title' => $creator['name'],
            'data' => $profileData = $this->app->repository->creatorProfileData((int) $creator['id'], $this->app->auth->id()),
            'prototype' => [
                'page' => 'public.profile',
                'profile' => [
                    'creator_id' => (int) $creator['id'],
                    'plan_id' => $profileData['plans'][0]['id'] ?? null,
                    'redirect' => path_with_query('/profile', ['id' => (int) $creator['id']]),
                ],
            ],
        ], null);
    }

    public function live(Request $request): void
    {
        $liveId = (int) $request->query('id', 0);
        $data = $this->app->repository->liveRoomData($liveId, $this->app->auth->id());

        if (! $data) {
            http_response_code(404);
            $this->render('pages/public/not-found', [
                'title' => 'Live nao encontrada',
                'description' => 'Esta sala nao esta disponivel.',
            ], 'layouts/marketing');

            return;
        }

        $this->render('pages/public/live', [
            'title' => $data['live']['title'],
            'data' => $data,
            'prototype' => [
                'page' => 'public.live-room',
                'live' => [
                    'id' => (int) $data['live']['id'],
                    'creator_id' => (int) $data['live']['creator_id'],
                    'redirect' => path_with_query('/live', ['id' => (int) $data['live']['id']]),
                ],
            ],
        ], null);
    }

    public function postLiveMessage(Request $request): void
    {
        $liveId = (int) $request->input('live_id', 0);

        if ($this->app->auth->guest()) {
            $this->redirect('/login', 'Entre para participar do chat ao vivo.', 'error');
        }

        $this->validateCsrf($request, path_with_query('/live', ['id' => $liveId]));
        $ok = $this->app->repository->postLiveMessage($liveId, (int) $this->user()['id'], (string) $request->input('body'));

        $this->redirect(path_with_query('/live', ['id' => $liveId]), $ok ? 'Mensagem enviada.' : 'Nao foi possivel enviar sua mensagem.', $ok ? 'success' : 'error');
    }

    public function postSubscribe(Request $request): void
    {
        if ($this->app->auth->guest()) {
            $this->redirect('/login', 'Entre como assinante para contratar um plano.', 'error');
        }

        $this->app->auth->requireRole('subscriber');
        $this->validateCsrf($request, '/subscriber/subscriptions');
        $result = $this->app->repository->subscribeToPlan((int) $this->user()['id'], (int) $request->input('plan_id', 0));

        $this->redirect('/subscriber/subscriptions', $result['message'], $result['ok'] ? 'success' : 'error');
    }

    public function postStartConversation(Request $request): void
    {
        if ($this->app->auth->guest()) {
            $this->redirect('/login', 'Entre para conversar com o criador.', 'error');
        }

        $this->app->auth->requireRole('subscriber');
        $creatorId = (int) $request->input('creator_id', 0);
        $this->validateCsrf($request, path_with_query('/profile', ['id' => $creatorId]));
        $conversationId = $this->app->repository->startConversation((int) $this->user()['id'], $creatorId, (string) $request->input('body', 'Oi! Gostaria de conversar sobre seus conteudos.'));

        $this->redirect(path_with_query('/subscriber/messages', ['conversation' => $conversationId]), 'Conversa iniciada.');
    }

    public function postTip(Request $request): void
    {
        if ($this->app->auth->guest()) {
            $this->redirect('/login', 'Entre para enviar gorjetas.', 'error');
        }

        $this->app->auth->requireRole('subscriber');
        $creatorId = (int) $request->input('creator_id', 0);
        $liveId = (int) $request->input('live_id', 0);
        $amount = (int) $request->input('amount', 0);
        $redirect = $liveId > 0 ? path_with_query('/live', ['id' => $liveId]) : path_with_query('/profile', ['id' => $creatorId]);
        $this->validateCsrf($request, $redirect);
        $result = $this->app->repository->tipCreator((int) $this->user()['id'], $creatorId, $amount, 'Gorjeta enviada ao criador');

        $this->redirect($redirect, $result['message'], $result['ok'] ? 'success' : 'error');
    }
}
