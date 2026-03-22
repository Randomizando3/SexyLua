<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;

final class LiveRtcController extends Controller
{
    public function join(Request $request): void
    {
        if (! $this->app->csrf->validate((string) $request->input('_token'))) {
            $this->json(['ok' => false, 'message' => 'Sessao expirada para iniciar a live.'], 419);
        }

        $result = $this->app->repository->joinLiveRtc(
            (int) $request->input('live_id', 0),
            (string) $request->input('role', 'viewer'),
            $this->app->auth->id(),
            session_id()
        );

        $this->json($result, (bool) ($result['ok'] ?? false) ? 200 : 422);
    }

    public function start(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        if (! $this->app->csrf->validate((string) $request->input('_token'))) {
            $this->json(['ok' => false, 'message' => 'Sessao expirada para iniciar a live.'], 419);
        }

        $result = $this->app->repository->startLiveBroadcast(
            (int) $this->user()['id'],
            (int) $request->input('live_id', 0),
            (string) $request->input('peer_id', ''),
            session_id(),
            [
                'max_bitrate_kbps' => (int) $request->input('max_bitrate_kbps', 1500),
                'video_width' => (int) $request->input('video_width', 960),
                'video_height' => (int) $request->input('video_height', 540),
                'video_fps' => (int) $request->input('video_fps', 24),
            ]
        );

        $this->json($result, (bool) ($result['ok'] ?? false) ? 200 : 422);
    }

    public function stop(Request $request): void
    {
        $this->app->auth->requireRole('creator');
        if (! $this->app->csrf->validate((string) $request->input('_token'))) {
            $this->json(['ok' => false, 'message' => 'Sessao expirada para encerrar a live.'], 419);
        }

        $result = $this->app->repository->stopLiveBroadcast(
            (int) $this->user()['id'],
            (int) $request->input('live_id', 0),
            (string) $request->input('peer_id', ''),
            session_id()
        );

        $this->json($result, (bool) ($result['ok'] ?? false) ? 200 : 422);
    }

    public function signal(Request $request): void
    {
        if (! $this->app->csrf->validate((string) $request->input('_token'))) {
            $this->json(['ok' => false, 'message' => 'Sessao expirada para sinalizar a live.'], 419);
        }

        $payload = json_decode((string) $request->input('payload', '{}'), true);
        $result = $this->app->repository->sendLiveRtcSignal(
            (int) $request->input('live_id', 0),
            (string) $request->input('from_peer_id', ''),
            (string) $request->input('to_peer_id', ''),
            (string) $request->input('kind', ''),
            is_array($payload) ? $payload : [],
            $this->app->auth->id(),
            session_id()
        );

        $this->json($result, (bool) ($result['ok'] ?? false) ? 200 : 422);
    }

    public function poll(Request $request): void
    {
        $result = $this->app->repository->pollLiveRtc(
            (int) $request->query('live_id', 0),
            (string) $request->query('peer_id', ''),
            $this->app->auth->id(),
            session_id(),
            (int) $request->query('after_id', 0)
        );

        $this->json($result, (bool) ($result['ok'] ?? false) ? 200 : 422);
    }

    public function heartbeat(Request $request): void
    {
        if (! $this->app->csrf->validate((string) $request->input('_token'))) {
            $this->json(['ok' => false, 'message' => 'Sessao expirada para manter a live.'], 419);
        }

        $result = $this->app->repository->heartbeatLiveRtc(
            (int) $request->input('live_id', 0),
            (string) $request->input('peer_id', ''),
            $this->app->auth->id(),
            session_id()
        );

        $this->json($result, (bool) ($result['ok'] ?? false) ? 200 : 422);
    }

    public function leave(Request $request): void
    {
        if (! $this->app->csrf->validate((string) $request->input('_token'))) {
            $this->json(['ok' => true], 200);
        }

        $result = $this->app->repository->leaveLiveRtc(
            (int) $request->input('live_id', 0),
            (string) $request->input('peer_id', ''),
            $this->app->auth->id(),
            session_id()
        );

        $this->json($result, 200);
    }
}
