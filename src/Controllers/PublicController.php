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
        $category = current_public_audience_category((string) $request->query('category', ''));
        $home = $this->app->repository->homepageData([
            'category' => $category,
        ]);
        $this->render('pages/public/home', [
            'title' => 'SexyLua',
            'data' => $home + [
                'audience_category' => $category,
            ],
            'prototype' => [
                'page' => 'public.home',
            ],
        ], null);
    }

    public function explore(Request $request): void
    {
        $category = current_public_audience_category((string) $request->query('category', ''));
        $explore = $this->app->repository->exploreData([
            'q' => $request->query('q', ''),
            'kind' => $request->query('kind', ''),
            'live_only' => $request->query('live_only', '') === '1',
            'include_scheduled' => $request->query('include_scheduled', '') === '1',
            'category' => $category,
        ]);

        $this->render('pages/public/explore', [
            'title' => 'Explorar',
            'data' => $explore + [
                'audience_category' => $category,
            ],
            'prototype' => [
                'page' => 'public.explore',
            ],
        ], null);
    }

    public function profile(Request $request): void
    {
        $category = current_public_audience_category((string) $request->query('category', ''));
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
            'data' => $profileData = $this->app->repository->creatorProfileData((int) $creator['id'], $this->app->auth->id(), [
                'category' => $category,
            ]),
            'prototype' => [
                'page' => 'public.profile',
            ],
        ], null);
    }

    public function storeAudienceGate(Request $request): void
    {
        if (! $this->app->csrf->validate((string) $request->input('_token'))) {
            $this->redirect('/', 'Sessao expirada. Tente novamente.', 'error');
        }

        if ((string) $request->input('accepted', '0') !== '1') {
            setcookie('sexylua_age_gate_verified', '', time() - 3600, '/');
            setcookie('sexylua_audience_category', '', time() - 3600, '/');
            redirect_to('https://www.google.com');
        }

        $category = normalize_audience_category((string) $request->input('category', 'todos'));
        $secure = str_starts_with(app_base_url($this->app->config, $this->app->repository->settings()), 'https://');
        $expiresAt = time() + (60 * 60 * 24 * 30);

        setcookie('sexylua_age_gate_verified', '1', $expiresAt, '/', '', $secure, false);
        setcookie('sexylua_audience_category', $category, $expiresAt, '/', '', $secure, false);

        $this->redirect(path_with_query('/explore', ['category' => $category]));
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

    public function liveState(Request $request): void
    {
        $liveId = (int) $request->query('id', 0);
        $data = $this->app->repository->liveRoomData($liveId, $this->app->auth->id());

        if ($data === null) {
            $this->json(['ok' => false, 'message' => 'Live nao encontrada.'], 404);
        }

        $this->json([
            'ok' => true,
            'live' => $data['live'] ?? [],
            'stream' => $data['stream'] ?? [],
            'chat_messages' => $data['messages'] ?? [],
            'recent_tips' => $data['recent_tips'] ?? [],
            'top_supporters' => $data['top_supporters'] ?? [],
            'tip_total_amount' => (int) ($data['tip_total_amount'] ?? 0),
            'priority_alert' => $data['priority_alert'] ?? null,
            'can_watch' => (bool) ($data['can_watch'] ?? false),
            'can_chat' => (bool) ($data['can_chat'] ?? false),
            'can_tip' => (bool) ($data['can_tip'] ?? false),
            'requires_login' => (bool) ($data['requires_login'] ?? false),
            'requires_subscription' => (bool) ($data['requires_subscription'] ?? false),
            'requires_vip_unlock' => (bool) ($data['requires_vip_unlock'] ?? false),
            'vip_unlocked' => (bool) ($data['vip_unlocked'] ?? false),
            'vip_unlock_price' => (int) ($data['vip_unlock_price'] ?? 0),
            'darkroom_available' => (bool) ($data['darkroom_available'] ?? false),
            'darkroom_active' => (bool) ($data['darkroom_active'] ?? false),
            'requires_darkroom_wait' => (bool) ($data['requires_darkroom_wait'] ?? false),
            'darkroom_is_owner' => (bool) ($data['darkroom_is_owner'] ?? false),
            'darkroom_price_tokens' => (int) ($data['darkroom_price_tokens'] ?? 0),
            'darkroom_duration_minutes' => (int) ($data['darkroom_duration_minutes'] ?? 0),
            'darkroom_remaining_seconds' => (int) ($data['darkroom_remaining_seconds'] ?? 0),
            'darkroom_owner_name' => (string) ($data['darkroom_owner_name'] ?? ''),
            'darkroom_started_at' => (string) ($data['darkroom_started_at'] ?? ''),
            'darkroom_ends_at' => (string) ($data['darkroom_ends_at'] ?? ''),
            'access_message' => (string) ($data['access_message'] ?? ''),
            'active_darkroom' => is_array($data['active_darkroom'] ?? null) ? $data['active_darkroom'] : null,
            'darkroom_candidates' => is_array($data['darkroom_candidates'] ?? null) ? array_values($data['darkroom_candidates']) : [],
        ], 200);
    }

    public function messageAsset(Request $request): void
    {
        if ($this->app->auth->guest()) {
            $this->redirect('/login', 'Entre para acessar este anexo.', 'error');
        }

        $scope = (string) $request->query('scope', 'message');
        $asset = match ($scope) {
            'announcement' => $this->app->repository->findSecureAnnouncementAttachment((int) $request->query('id', 0), (int) ($this->user()['id'] ?? 0)),
            'identity' => $this->app->repository->findSecureIdentityAttachment((int) $request->query('id', 0), (int) ($this->user()['id'] ?? 0)),
            default => $this->app->repository->findSecureConversationMessageAttachment((int) $request->query('id', 0), (int) ($this->user()['id'] ?? 0)),
        };

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
        ], null);
    }

    public function terms(Request $request): void
    {
        $this->render('pages/public/terms', [
            'title' => 'Termos de Uso',
        ], null);
    }

    public function privacy(Request $request): void
    {
        $this->render('pages/public/privacy', [
            'title' => 'Politica de Privacidade',
        ], null);
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

    public function unlockLive(Request $request): void
    {
        if ($this->app->auth->guest()) {
            $this->redirect('/login', 'Entre para desbloquear esta live VIP.', 'error');
        }

        $redirect = (string) $request->input('redirect', path_with_query('/live', ['id' => (int) $request->input('live_id', 0)]));
        $this->validateCsrf($request, $redirect);
        $result = $this->app->repository->unlockLiveAccess((int) $request->input('live_id', 0), (int) ($this->user()['id'] ?? 0));

        if (! (bool) ($result['ok'] ?? false) && str_contains(mb_strtolower((string) ($result['message'] ?? '')), 'saldo insuficiente')) {
            $role = (string) ($this->user()['role'] ?? 'subscriber');
            $walletUrl = match ($role) {
                'creator' => '/creator/wallet',
                'admin' => '/admin/finance',
                default => '/subscriber/wallet',
            };
            $this->redirect($walletUrl, 'Voce nao tem LuaCoins suficientes para desbloquear esta live VIP. Recarregue sua carteira para continuar.', 'error');
        }

        $this->redirect($redirect, (string) ($result['message'] ?? 'Nao foi possivel desbloquear esta live VIP.'), (bool) ($result['ok'] ?? false) ? 'success' : 'error');
    }

    public function activateDarkroom(Request $request): void
    {
        if ($this->app->auth->guest()) {
            if ($this->expectsJson($request)) {
                $this->json(['ok' => false, 'message' => 'Entre para ativar o darkroom desta live.'], 401);
            }

            $this->redirect('/login', 'Entre para ativar o darkroom desta live.', 'error');
        }

        $redirect = (string) $request->input('redirect', path_with_query('/live', ['id' => (int) $request->input('live_id', 0)]));
        if (! $this->app->csrf->validate((string) $request->input('_token'))) {
            if ($this->expectsJson($request)) {
                $this->json(['ok' => false, 'message' => 'Sessao expirada. Envie o formulario novamente.'], 419);
            }

            $this->redirect($redirect, 'Sessao expirada. Envie o formulario novamente.', 'error');
        }

        $result = $this->app->repository->activateLiveDarkroom((int) $request->input('live_id', 0), (int) ($this->user()['id'] ?? 0));

        if (! (bool) ($result['ok'] ?? false) && str_contains(mb_strtolower((string) ($result['message'] ?? '')), 'saldo insuficiente')) {
            $role = (string) ($this->user()['role'] ?? 'subscriber');
            $walletUrl = match ($role) {
                'creator' => '/creator/wallet',
                'admin' => '/admin/finance',
                default => '/subscriber/wallet',
            };

            if ($this->expectsJson($request)) {
                $this->json([
                    'ok' => false,
                    'message' => 'Voce nao tem LuaCoins suficientes para ativar o darkroom. Recarregue sua carteira para continuar.',
                    'wallet_url' => $walletUrl,
                ], 422);
            }

            $this->redirect($walletUrl, 'Voce nao tem LuaCoins suficientes para ativar o darkroom. Recarregue sua carteira para continuar.', 'error');
        }

        if ($this->expectsJson($request)) {
            if (! (bool) ($result['ok'] ?? false)) {
                $this->json([
                    'ok' => false,
                    'message' => (string) ($result['message'] ?? 'Nao foi possivel ativar o darkroom.'),
                ], 422);
            }

            $room = $this->app->repository->liveRoomData((int) $request->input('live_id', 0), (int) ($this->user()['id'] ?? 0)) ?? [];

            $this->json([
                'ok' => true,
                'message' => (string) ($result['message'] ?? 'Darkroom ativado com sucesso.'),
            ] + $room);
        }

        $this->redirect($redirect, (string) ($result['message'] ?? 'Nao foi possivel ativar o darkroom.'), (bool) ($result['ok'] ?? false) ? 'success' : 'error');
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

        if (! $this->app->auth->hasRole('subscriber')) {
            if ($expectsJson) {
                $this->json(['ok' => false, 'message' => 'Apenas assinantes podem enviar gorjetas nesta sala.'], 403);
            }

            $this->redirect($this->app->auth->homeForRole((string) ($this->user()['role'] ?? 'subscriber')), 'Apenas assinantes podem enviar gorjetas nesta sala.', 'error');
        }

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

        if (! (bool) ($result['ok'] ?? false) && str_contains(mb_strtolower((string) ($result['message'] ?? '')), 'saldo insuficiente')) {
            $role = (string) ($this->user()['role'] ?? 'subscriber');
            $walletUrl = match ($role) {
                'creator' => '/creator/wallet',
                'admin' => '/admin/finance',
                default => '/subscriber/wallet',
            };

            if ($expectsJson) {
                $this->json([
                    'ok' => false,
                    'message' => 'Voce nao tem LuaCoins suficientes para enviar essa gorjeta. Recarregue sua carteira para continuar.',
                    'wallet_url' => $walletUrl,
                ], 422);
            }

            $this->redirect($walletUrl, 'Voce nao tem LuaCoins suficientes para enviar essa gorjeta. Recarregue sua carteira para continuar.', 'error');
        }

        if ($expectsJson) {
            $this->json($result, (bool) ($result['ok'] ?? false) ? 200 : 422);
        }

        $this->redirect($redirect, $result['message'], $result['ok'] ? 'success' : 'error');
    }
}
