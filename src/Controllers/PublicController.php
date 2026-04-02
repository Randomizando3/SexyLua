<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;

final class PublicController extends Controller
{
    private function expectsJson(Request $request): bool
    {
        $requestedWith = strtolower((string) $request->server('HTTP_X_REQUESTED_WITH', ''));
        $accept = strtolower((string) $request->server('HTTP_ACCEPT', ''));

        return $requestedWith === 'xmlhttprequest' || str_contains($accept, 'application/json');
    }

    public function home(Request $request): void
    {
        $home = $this->app->repository->homepageData();
        $this->render('pages/public/home', [
            'title' => 'SexyLua',
            'data' => $home,
            'prototype' => [
                'page' => 'public.home',
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
                'page' => 'public.live',
            ],
        ], null);
    }

    public function messageAsset(Request $request): void
    {
        if ($this->app->auth->guest()) {
            $this->redirect('/login', 'Entre para acessar este anexo.', 'error');
        }

        $scope = (string) $request->query('scope', 'message');
        $asset = $scope === 'announcement'
            ? $this->app->repository->findSecureAnnouncementAttachment((int) $request->query('id', 0), (int) ($this->user()['id'] ?? 0))
            : $this->app->repository->findSecureConversationMessageAttachment((int) $request->query('id', 0), (int) ($this->user()['id'] ?? 0));

        $path = private_media_local_path((string) ($asset['path'] ?? ''));
        if ($asset === null || $path === null || ! is_file($path)) {
            http_response_code(404);
            echo 'Anexo nao encontrado.';
            exit;
        }

        $mimeType = (string) ($asset['mime_type'] ?? 'application/octet-stream');
        $displayName = str_replace(["\r", "\n"], '', (string) ($asset['display_name'] ?? $asset['original_name'] ?? basename($path)));
        $disposition = (string) ($asset['kind'] ?? 'document') === 'document' ? 'attachment' : 'inline';

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . (string) filesize($path));
        header('Content-Disposition: ' . $disposition . '; filename="' . addslashes($displayName) . '"');
        readfile($path);
        exit;
    }

    public function help(Request $request): void
    {
        $this->render('pages/public/help', [
            'title' => 'Ajuda',
        ], 'layouts/marketing');
    }

    public function terms(Request $request): void
    {
        $this->render('pages/public/terms', [
            'title' => 'Termos de Uso',
        ], 'layouts/marketing');
    }

    public function privacy(Request $request): void
    {
        $this->render('pages/public/privacy', [
            'title' => 'Politica de Privacidade',
        ], 'layouts/marketing');
    }

    public function postLiveMessage(Request $request): void
    {
        $liveId = (int) $request->input('live_id', 0);
        $expectsJson = $this->expectsJson($request);

        if ($this->app->auth->guest()) {
            if ($expectsJson) {
                $this->json(['ok' => false, 'message' => 'Entre para participar do chat ao vivo.'], 401);
            }

            $this->redirect('/login', 'Entre para participar do chat ao vivo.', 'error');
        }

        if (! $this->app->csrf->validate((string) $request->input('_token'))) {
            if ($expectsJson) {
                $this->json(['ok' => false, 'message' => 'Sessao expirada. Envie o formulario novamente.'], 419);
            }

            $this->redirect(path_with_query('/live', ['id' => $liveId]), 'Sessao expirada. Envie o formulario novamente.', 'error');
        }

        $ok = $this->app->repository->postLiveMessage($liveId, (int) $this->user()['id'], (string) $request->input('body'));

        if ($expectsJson) {
            $this->json([
                'ok' => $ok,
                'message' => $ok ? 'Mensagem enviada.' : 'Nao foi possivel enviar sua mensagem.',
            ], $ok ? 200 : 422);
        }

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

    public function unlockMessage(Request $request): void
    {
        if ($this->app->auth->guest()) {
            $this->redirect('/login', 'Entre para desbloquear este conteudo.', 'error');
        }

        $this->app->auth->requireRole('subscriber');
        $redirect = (string) $request->input('redirect', '/subscriber/messages');
        $this->validateCsrf($request, $redirect);
        $result = $this->app->repository->unlockConversationMessage((int) $request->input('message_id', 0), (int) ($this->user()['id'] ?? 0));

        if (! (bool) ($result['ok'] ?? false) && str_contains(mb_strtolower((string) ($result['message'] ?? '')), 'saldo insuficiente')) {
            $this->redirect('/subscriber/wallet', 'Voce nao tem LuaCoins suficientes para desbloquear este conteudo. Recarregue sua carteira para continuar.', 'error');
        }

        $this->redirect($redirect, (string) ($result['message'] ?? 'Nao foi possivel desbloquear este conteudo.'), (bool) ($result['ok'] ?? false) ? 'success' : 'error');
    }

    public function postTip(Request $request): void
    {
        $expectsJson = $this->expectsJson($request);

        if ($this->app->auth->guest()) {
            if ($expectsJson) {
                $this->json(['ok' => false, 'message' => 'Entre para enviar gorjetas.'], 401);
            }

            $this->redirect('/login', 'Entre para enviar gorjetas.', 'error');
        }

        $this->app->auth->requireRole('subscriber');
        $creatorId = (int) $request->input('creator_id', 0);
        $liveId = (int) $request->input('live_id', 0);
        $amount = (int) $request->input('amount', 0);
        $message = trim((string) $request->input('message', ''));
        $redirect = $liveId > 0 ? path_with_query('/live', ['id' => $liveId]) : path_with_query('/profile', ['id' => $creatorId]);

        if (! $this->app->csrf->validate((string) $request->input('_token'))) {
            if ($expectsJson) {
                $this->json(['ok' => false, 'message' => 'Sessao expirada. Envie o formulario novamente.'], 419);
            }

            $this->redirect($redirect, 'Sessao expirada. Envie o formulario novamente.', 'error');
        }

        $result = $this->app->repository->tipCreator((int) $this->user()['id'], $creatorId, $amount, 'Gorjeta enviada ao criador', $liveId, $message);

        if ($expectsJson) {
            $this->json($result, (bool) ($result['ok'] ?? false) ? 200 : 422);
        }

        $this->redirect($redirect, $result['message'], $result['ok'] ? 'success' : 'error');
    }
}
