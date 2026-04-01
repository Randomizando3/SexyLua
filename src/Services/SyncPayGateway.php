<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class SyncPayGateway
{
    private const DEFAULT_API_BASE = 'https://api.syncpayments.com.br';

    public function __construct(
        private readonly array $settings,
    ) {
    }

    public function configured(): bool
    {
        $hasOauth = $this->clientId() !== '' && $this->clientSecret() !== '';
        $hasApiKey = $this->apiKey() !== '';

        return $hasOauth || $hasApiKey;
    }

    public function canFetchTransactionStatus(): bool
    {
        return ($this->clientId() !== '' && $this->clientSecret() !== '') || $this->apiKey() !== '';
    }

    public function createWalletTopUpCharge(array $payload): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('SyncPay nao configurado.');
        }

        $document = preg_replace('/\D+/', '', (string) ($payload['document'] ?? $payload['cpf'] ?? ''));
        if ($document === '') {
            throw new RuntimeException('Informe um CPF ou CNPJ valido para gerar o PIX.');
        }

        $amount = round((float) ($payload['amount'] ?? 0), 2);
        if ($amount <= 0) {
            throw new RuntimeException('Informe um valor valido para gerar o PIX.');
        }

        $body = array_filter([
            'amount' => $amount,
            'description' => trim((string) ($payload['description'] ?? 'Recarga de LuaCoins SexyLua')),
            'webhook_url' => trim((string) ($payload['webhook_url'] ?? $payload['postback_url'] ?? '')),
            'client' => array_filter([
                'name' => trim((string) ($payload['name'] ?? 'SexyLua User')),
                'cpf' => $document,
                'email' => trim((string) ($payload['email'] ?? '')),
                'phone' => preg_replace('/\D+/', '', (string) ($payload['phone'] ?? '')),
            ], static fn (mixed $value): bool => $value !== '' && $value !== null),
        ], static fn (mixed $value): bool => $value !== '' && $value !== null && $value !== []);

        $response = $this->request('POST', '/api/partner/v1/cash-in', $body);

        return is_array($response) ? $response : [];
    }

    public function fetchTransactionStatus(string $transactionId): array
    {
        $transactionId = trim($transactionId);
        if ($transactionId === '') {
            throw new RuntimeException('Transacao SyncPay invalida.');
        }

        if ($this->clientId() !== '' && $this->clientSecret() !== '') {
            return $this->request('GET', '/api/partner/v1/transaction/' . rawurlencode($transactionId));
        }

        if ($this->apiKey() === '') {
            throw new RuntimeException('Consulta de status exige credenciais validas da SyncPay.');
        }

        $url = $this->baseUrl() . '/s1/getTransaction/api/getTransactionStatus.php?id_transaction=' . rawurlencode($transactionId);

        return $this->requestAbsolute('GET', $url, [], [
            'Authorization: Basic ' . $this->apiKey(),
            'Accept: application/json',
            'Content-Type: application/json',
        ]);
    }

    public function isValidWebhookAuthorization(string $authorizationHeader): bool
    {
        $expected = trim((string) ($this->settings['syncpay_webhook_token'] ?? ''));

        if ($expected === '') {
            return true;
        }

        $authorizationHeader = trim($authorizationHeader);
        if ($authorizationHeader === '') {
            return false;
        }

        if (stripos($authorizationHeader, 'Bearer ') === 0) {
            $authorizationHeader = trim(substr($authorizationHeader, 7));
        }

        return hash_equals($expected, $authorizationHeader);
    }

    private function request(string $method, string $path, array $payload = []): array
    {
        return $this->requestAbsolute($method, $this->baseUrl() . $path, $payload, $this->authorizationHeaders());
    }

    private function requestAbsolute(string $method, string $url, array $payload, array $headers): array
    {
        $headers = array_merge($headers, [
            'Accept: application/json',
            'Content-Type: application/json',
        ]);

        if (function_exists('curl_init')) {
            $curl = curl_init($url);

            if ($curl === false) {
                throw new RuntimeException('Nao foi possivel iniciar a conexao com a SyncPay.');
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
                throw new RuntimeException($error !== '' ? $error : 'Resposta invalida da SyncPay.');
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
                throw new RuntimeException('Falha ao consultar a SyncPay.');
            }
        }

        $decoded = json_decode($response, true);
        $data = is_array($decoded) ? $decoded : [];

        if ($status >= 400) {
            $message = (string) (
                $data['message']
                ?? $data['error']
                ?? $data['errors'][0]['message']
                ?? 'Erro ao consultar a SyncPay.'
            );

            throw new RuntimeException($message);
        }

        return $data;
    }

    private function authorizationHeaders(): array
    {
        if ($this->clientId() !== '' && $this->clientSecret() !== '') {
            return ['Authorization: Bearer ' . $this->issueBearerToken()];
        }

        if ($this->apiKey() !== '') {
            return ['Authorization: Basic ' . $this->apiKey()];
        }

        throw new RuntimeException('SyncPay nao configurado.');
    }

    private function issueBearerToken(): string
    {
        $response = $this->requestAbsolute(
            'POST',
            $this->baseUrl() . '/api/partner/v1/auth-token',
            [
                'client_id' => $this->clientId(),
                'client_secret' => $this->clientSecret(),
            ],
            []
        );

        $token = trim((string) ($response['access_token'] ?? ''));
        if ($token === '') {
            throw new RuntimeException('A SyncPay nao retornou um access_token valido.');
        }

        return $token;
    }

    private function baseUrl(): string
    {
        $baseUrl = trim((string) ($this->settings['syncpay_api_base_url'] ?? ''));
        if ($baseUrl === '') {
            $baseUrl = self::DEFAULT_API_BASE;
        }

        return rtrim($baseUrl, '/');
    }

    private function clientId(): string
    {
        return trim((string) ($this->settings['syncpay_client_id'] ?? ''));
    }

    private function clientSecret(): string
    {
        return trim((string) ($this->settings['syncpay_client_secret'] ?? ''));
    }

    private function apiKey(): string
    {
        return trim((string) ($this->settings['syncpay_api_key'] ?? ''));
    }

    private function pixExpiresInDays(): int
    {
        return max(1, (int) ($this->settings['syncpay_pix_expires_in_days'] ?? 2));
    }
}
