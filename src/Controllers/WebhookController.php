<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Services\MercadoPagoGateway;

final class WebhookController extends Controller
{
    public function mercadoPago(Request $request): void
    {
        $settings = $this->app->repository->settings();
        $gateway = new MercadoPagoGateway($settings);

        if (! $gateway->configured()) {
            $this->json(['ok' => true, 'ignored' => 'mercadopago_not_configured'], 202);
        }

        $payload = json_decode((string) file_get_contents('php://input'), true);
        $payload = is_array($payload) ? $payload : [];
        $topic = strtolower(trim((string) (
            $payload['type']
            ?? $payload['topic']
            ?? $payload['action']
            ?? $request->query('type')
            ?? $request->query('topic')
            ?? $request->query('action')
            ?? ''
        )));
        $dataId = trim((string) (
            $payload['data']['id']
            ?? $payload['id']
            ?? $request->query('data_id')
            ?? $request->query('id')
            ?? ''
        ));

        if ($dataId === '' && isset($payload['resource']) && is_string($payload['resource'])) {
            $resourceParts = explode('/', trim($payload['resource'], '/'));
            $dataId = trim((string) end($resourceParts));
        }

        if ($dataId === '') {
            $this->json(['ok' => true, 'ignored' => 'missing_payment_id'], 202);
        }

        $requestId = trim((string) $request->server('HTTP_X_REQUEST_ID', ''));
        $signature = trim((string) $request->server('HTTP_X_SIGNATURE', ''));

        if (! $gateway->isValidWebhookSignature($signature, $requestId, $dataId)) {
            $this->json(['ok' => false, 'message' => 'Assinatura do webhook invalida.'], 401);
        }

        if ($topic !== '' && ! str_contains($topic, 'payment')) {
            $this->json(['ok' => true, 'ignored' => 'unsupported_topic'], 202);
        }

        $payment = $gateway->fetchPayment($dataId);
        $transactionId = (int) ($payment['metadata']['topup_transaction_id'] ?? 0);

        if ($transactionId <= 0) {
            $externalReference = (string) ($payment['external_reference'] ?? '');
            if (preg_match('/^topup-(\d+)-/i', $externalReference, $matches) === 1) {
                $transactionId = (int) $matches[1];
            }
        }

        if ($transactionId <= 0) {
            $this->json(['ok' => true, 'ignored' => 'missing_topup_reference'], 202);
        }

        $ok = $this->app->repository->syncMercadoPagoWalletTopUp($transactionId, $payment);

        $this->json([
            'ok' => $ok,
            'transaction_id' => $transactionId,
            'status' => (string) ($payment['status'] ?? 'pending'),
        ]);
    }
}
