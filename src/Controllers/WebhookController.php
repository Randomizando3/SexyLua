<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Services\SyncPayGateway;

final class WebhookController extends Controller
{
    public function syncPay(Request $request): void
    {
        $settings = $this->app->repository->settings();
        $gateway = new SyncPayGateway($settings);

        if (! $gateway->configured()) {
            $this->json(['ok' => true, 'ignored' => 'syncpay_not_configured'], 202);
        }

        $payload = json_decode((string) file_get_contents('php://input'), true);
        $payload = is_array($payload) ? $payload : [];
        $authorization = trim((string) $request->server('HTTP_AUTHORIZATION', ''));

        if (! $gateway->isValidWebhookAuthorization($authorization)) {
            $this->json(['ok' => false, 'message' => 'Authorization do webhook SyncPay invalido.'], 401);
        }

        $transactionId = (int) (
            $payload['metadata']['topup_transaction_id']
            ?? $payload['data']['metadata']['topup_transaction_id']
            ?? 0
        );

        if ($transactionId <= 0) {
            $externalReference = (string) (
                $payload['data']['externalreference']
                ?? $payload['externalreference']
                ?? $payload['data']['external_reference']
                ?? $payload['external_reference']
                ?? ''
            );

            if (preg_match('/^topup-(\d+)-/i', $externalReference, $matches) === 1) {
                $transactionId = (int) $matches[1];
            }
        }

        if ($transactionId <= 0) {
            $this->json(['ok' => true, 'ignored' => 'missing_topup_reference'], 202);
        }

        $ok = $this->app->repository->syncSyncPayWalletTopUp($transactionId, $payload);
        $rawStatus = (string) (
            $payload['data']['status']
            ?? $payload['status_transaction']
            ?? $payload['status']
            ?? ''
        );

        $this->json([
            'ok' => $ok,
            'transaction_id' => $transactionId,
            'status' => $rawStatus !== '' ? $rawStatus : 'pending',
        ]);
    }
}
