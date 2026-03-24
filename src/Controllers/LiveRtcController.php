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
                'segment_duration_seconds' => (int) $request->input('segment_duration_seconds', 6),
                'max_bitrate_kbps' => (int) $request->input('max_bitrate_kbps', 1200),
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

    public function segment(Request $request): void
    {
        $this->app->auth->requireRole('creator');

        if (! $this->app->csrf->validate((string) $request->input('_token'))) {
            $this->json(['ok' => false, 'message' => 'Sessao expirada para enviar o segmento.'], 419);
        }

        if (! $request->hasFile('segment_file')) {
            $this->json(['ok' => false, 'message' => 'Nenhum segmento foi enviado.'], 422);
        }

        $liveId = (int) $request->input('live_id', 0);
        $segmentPath = store_uploaded_file(
            $request->file('segment_file'),
            'live/segments/' . $liveId,
            ['webm', 'mp4', 'ogg'],
            1024 * 1024 * 25
        );

        if ($segmentPath === null) {
            $this->json(['ok' => false, 'message' => 'Nao foi possivel salvar o segmento da live.'], 422);
        }

        $file = $request->file('segment_file') ?? [];
        $result = $this->app->repository->appendLiveSegment(
            (int) $this->user()['id'],
            $liveId,
            (string) $request->input('peer_id', ''),
            session_id(),
            [
                'segment_sequence' => (int) $request->input('segment_sequence', 0),
                'segment_duration_ms' => (int) $request->input('segment_duration_ms', 6000),
                'segment_url' => $segmentPath,
                'segment_mime_type' => (string) ($file['type'] ?? $request->input('segment_mime_type', 'video/webm')),
                'segment_bytes' => (int) ($file['size'] ?? 0),
            ]
        );

        $this->json($result, (bool) ($result['ok'] ?? false) ? 200 : 422);
    }

    public function recording(Request $request): void
    {
        $this->app->auth->requireRole('creator');

        if (! $this->app->csrf->validate((string) $request->input('_token'))) {
            $this->json(['ok' => false, 'message' => 'Sessao expirada para enviar o replay.'], 419);
        }

        if (! $request->hasFile('recording_file')) {
            $this->json(['ok' => false, 'message' => 'Nenhum arquivo de replay foi enviado.'], 422);
        }

        $recordingPath = store_uploaded_file(
            $request->file('recording_file'),
            'creator/live/recordings',
            ['webm', 'mp4', 'ogg', 'mov'],
            1024 * 1024 * 700
        );

        if ($recordingPath === null) {
            $this->json(['ok' => false, 'message' => 'Nao foi possivel salvar o arquivo da gravacao.'], 422);
        }

        $file = $request->file('recording_file') ?? [];
        $result = $this->app->repository->saveLiveRecording((int) $this->user()['id'], (int) $request->input('live_id', 0), [
            'recording_url' => $recordingPath,
            'recording_mime_type' => (string) ($file['type'] ?? 'video/webm'),
            'recording_bytes' => (int) ($file['size'] ?? 0),
            'recording_duration_seconds' => max(0, (int) $request->input('recording_duration_seconds', 0)),
            'recording_label' => trim((string) $request->input('recording_label', 'Replay local')),
        ]);

        $this->json($result, (bool) ($result['ok'] ?? false) ? 200 : 422);
    }
}
