<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class GoogleOAuthService
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';

    public function __construct(
        private readonly array $config,
        private readonly array $settings,
    ) {
    }

    public function enabled(): bool
    {
        return (bool) ($this->settings['google_oauth_enabled'] ?? true);
    }

    public function configured(): bool
    {
        return $this->enabled() && $this->clientId() !== '' && $this->clientSecret() !== '';
    }

    public function redirectUri(): string
    {
        return rtrim(\app_base_url($this->config, $this->settings), '/') . '/auth/google/callback';
    }

    public function authorizationUrl(string $state): string
    {
        return self::AUTH_URL . '?' . http_build_query([
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'online',
            'include_granted_scopes' => 'true',
            'prompt' => 'select_account',
            'state' => $state,
        ]);
    }

    public function fetchAccessToken(string $code): array
    {
        $payload = http_build_query([
            'code' => $code,
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        $response = $this->request('POST', self::TOKEN_URL, [
            'Content-Type: application/x-www-form-urlencoded',
        ], $payload);

        if (($response['status'] ?? 500) < 200 || ($response['status'] ?? 500) >= 300) {
            $message = (string) (($response['json']['error_description'] ?? '') ?: ($response['json']['error'] ?? '') ?: 'Nao foi possivel autenticar com o Google.');
            throw new RuntimeException($message);
        }

        return is_array($response['json'] ?? null) ? $response['json'] : [];
    }

    public function fetchUserInfo(string $accessToken): array
    {
        $response = $this->request('GET', self::USERINFO_URL, [
            'Accept: application/json',
            'Authorization: Bearer ' . $accessToken,
        ]);

        if (($response['status'] ?? 500) < 200 || ($response['status'] ?? 500) >= 300) {
            $message = (string) (($response['json']['error_description'] ?? '') ?: ($response['json']['error'] ?? '') ?: 'Nao foi possivel obter os dados do Google.');
            throw new RuntimeException($message);
        }

        return is_array($response['json'] ?? null) ? $response['json'] : [];
    }

    private function clientId(): string
    {
        return trim((string) ($this->settings['google_client_id'] ?? ''));
    }

    private function clientSecret(): string
    {
        return trim((string) ($this->settings['google_client_secret'] ?? ''));
    }

    private function request(string $method, string $url, array $headers = [], ?string $body = null): array
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                throw new RuntimeException('Nao foi possivel iniciar a conexao com o Google.');
            }

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_HTTPHEADER => $headers,
            ]);

            if ($body !== null && $method !== 'GET') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            $raw = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if (! is_string($raw)) {
                throw new RuntimeException($error !== '' ? $error : 'O Google nao respondeu como esperado.');
            }

            $json = json_decode($raw, true);

            return [
                'status' => $status,
                'body' => $raw,
                'json' => is_array($json) ? $json : [],
            ];
        }

        $headerString = implode("\r\n", $headers);
        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'timeout' => 20,
                'ignore_errors' => true,
                'header' => $headerString . ($headerString !== '' ? "\r\n" : ''),
                'content' => $body ?? '',
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if (! is_string($raw)) {
            throw new RuntimeException('O Google nao respondeu como esperado.');
        }

        $status = 0;
        foreach ((array) ($http_response_header ?? []) as $header) {
            if (preg_match('/^HTTP\/\S+\s+(\d+)/', (string) $header, $matches) === 1) {
                $status = (int) ($matches[1] ?? 0);
                break;
            }
        }

        $json = json_decode($raw, true);

        return [
            'status' => $status,
            'body' => $raw,
            'json' => is_array($json) ? $json : [],
        ];
    }
}
