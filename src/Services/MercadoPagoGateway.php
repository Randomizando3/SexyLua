<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class MercadoPagoGateway
{
    private const API_BASE = 'https://api.mercadopago.com';

    public function __construct(
        private readonly array $settings,
    ) {
    }

    public function configured(): bool
    {
        return trim((string) ($this->settings['mercadopago_access_token'] ?? '')) !== '';
    }

    public function createWalletTopUpPreference(array $payload): array
    {
        return $this->request('POST', '/checkout/preferences', $payload);
    }

    public function fetchPayment(string|int $paymentId): array
    {
        return $this->request('GET', '/v1/payments/' . rawurlencode((string) $paymentId));
    }

    public function isValidWebhookSignature(string $signatureHeader, string $requestId, string $dataId): bool
    {
        $secret = trim((string) ($this->settings['mercadopago_webhook_secret'] ?? ''));

        if ($secret === '') {
            return true;
        }

        if ($signatureHeader === '' || $dataId === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $piece) {
            [$key, $value] = array_pad(explode('=', trim($piece), 2), 2, '');
            $parts[strtolower(trim($key))] = trim($value);
        }

        $timestamp = (string) ($parts['ts'] ?? '');
        $hash = strtolower((string) ($parts['v1'] ?? ''));

        if ($timestamp === '' || $hash === '') {
            return false;
        }

        $manifest = sprintf('id:%s;request-id:%s;ts:%s;', $dataId, $requestId, $timestamp);
        $expected = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expected, $hash);
    }

    private function request(string $method, string $path, array $payload = []): array
    {
        $accessToken = trim((string) ($this->settings['mercadopago_access_token'] ?? ''));

        if ($accessToken === '') {
            throw new RuntimeException('Mercado Pago nao configurado.');
        }

        $url = self::API_BASE . $path;
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json',
            'Content-Type: application/json',
        ];

        if (function_exists('curl_init')) {
            $curl = curl_init($url);

            if ($curl === false) {
                throw new RuntimeException('Nao foi possivel iniciar a conexao com o Mercado Pago.');
            }

            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => strtoupper($method),
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 30,
            ]);

            if (strtoupper($method) !== 'GET') {
                curl_setopt($curl, CURLOPT_POSTFIELDS, (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }

            $response = curl_exec($curl);
            $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            $error = curl_error($curl);
            curl_close($curl);

            if (! is_string($response)) {
                throw new RuntimeException($error !== '' ? $error : 'Resposta invalida do Mercado Pago.');
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => strtoupper($method),
                    'header' => implode("\r\n", $headers),
                    'content' => strtoupper($method) === 'GET' ? '' : (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'timeout' => 30,
                    'ignore_errors' => true,
                ],
            ]);

            $response = @file_get_contents($url, false, $context);
            $status = 0;

            $responseHeaders = function_exists('http_get_last_response_headers') ? http_get_last_response_headers() : ($http_response_header ?? []);

            if (isset($responseHeaders[0]) && preg_match('/\s(\d{3})\s/', (string) $responseHeaders[0], $matches) === 1) {
                $status = (int) $matches[1];
            }

            if (! is_string($response)) {
                throw new RuntimeException('Falha ao consultar o Mercado Pago.');
            }
        }

        $decoded = json_decode($response, true);
        $data = is_array($decoded) ? $decoded : [];

        if ($status >= 400) {
            $message = (string) ($data['message'] ?? $data['error'] ?? 'Erro ao consultar o Mercado Pago.');
            throw new RuntimeException($message);
        }

        return $data;
    }
}
