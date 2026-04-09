<?php

declare(strict_types=1);

namespace App\Services;

final class SmtpMailer
{
    public function __construct(
        private readonly array $settings,
    ) {
    }

    public function sendSupportMessage(array $payload): array
    {
        $recipientEmail = trim((string) ($this->settings['support_recipient_email'] ?? ''));
        $recipientName = trim((string) ($this->settings['support_recipient_name'] ?? 'SexyLua'));
        $fromEmail = trim((string) ($this->settings['smtp_from_email'] ?? ''));
        $fromName = trim((string) ($this->settings['smtp_from_name'] ?? 'SexyLua'));
        $smtpHost = trim((string) ($this->settings['smtp_host'] ?? ''));

        if ($recipientEmail === '' || ! filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'ok' => false,
                'message' => 'Defina o e-mail de recebimento em Integracoes para ativar o formulario.',
            ];
        }

        if ($fromEmail === '' || ! filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            $fromEmail = $recipientEmail;
        }

        if ($fromName === '') {
            $fromName = 'SexyLua';
        }

        $name = trim((string) ($payload['name'] ?? 'Visitante'));
        $email = trim((string) ($payload['email'] ?? ''));
        $subject = trim((string) ($payload['subject'] ?? 'Contato pelo formulario de ajuda'));
        $category = trim((string) ($payload['category'] ?? 'geral'));
        $message = trim((string) ($payload['message'] ?? ''));
        $role = trim((string) ($payload['role'] ?? 'visitante'));

        $bodyLines = [
            'Novo contato enviado pela pagina de ajuda da SexyLua.',
            '',
            'Nome: ' . ($name !== '' ? $name : 'Visitante'),
            'Email: ' . ($email !== '' ? $email : 'Nao informado'),
            'Perfil: ' . ($role !== '' ? $role : 'visitante'),
            'Categoria: ' . ($category !== '' ? $category : 'geral'),
            '',
            'Mensagem:',
            $message !== '' ? $message : 'Sem mensagem.',
        ];

        if ($smtpHost === '') {
            return $this->sendWithMail(
                $recipientEmail,
                $recipientName,
                $fromEmail,
                $fromName,
                $email,
                $name,
                $subject,
                implode("\n", $bodyLines),
            );
        }

        return $this->sendWithSmtp(
            $recipientEmail,
            $recipientName,
            $fromEmail,
            $fromName,
            $email,
            $name,
            $subject,
            implode("\r\n", $bodyLines),
        );
    }

    private function sendWithMail(
        string $recipientEmail,
        string $recipientName,
        string $fromEmail,
        string $fromName,
        string $replyToEmail,
        string $replyToName,
        string $subject,
        string $body,
    ): array {
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'From: ' . $this->formatMailbox($fromEmail, $fromName);
        if ($replyToEmail !== '' && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . $this->formatMailbox($replyToEmail, $replyToName !== '' ? $replyToName : $replyToEmail);
        }

        $sent = @mail(
            $this->formatMailbox($recipientEmail, $recipientName),
            $this->encodeHeader($subject),
            $body,
            implode("\r\n", $headers),
        );

        return [
            'ok' => $sent,
            'message' => $sent ? 'Sua mensagem foi enviada com sucesso.' : 'Nao foi possivel enviar sua mensagem agora.',
        ];
    }

    private function sendWithSmtp(
        string $recipientEmail,
        string $recipientName,
        string $fromEmail,
        string $fromName,
        string $replyToEmail,
        string $replyToName,
        string $subject,
        string $body,
    ): array {
        $host = trim((string) ($this->settings['smtp_host'] ?? ''));
        $port = max(1, (int) ($this->settings['smtp_port'] ?? 587));
        $encryption = strtolower(trim((string) ($this->settings['smtp_encryption'] ?? 'tls')));
        $username = trim((string) ($this->settings['smtp_username'] ?? ''));
        $password = (string) ($this->settings['smtp_password'] ?? '');
        $timeout = max(5, (int) ($this->settings['smtp_timeout_seconds'] ?? 15));

        $transport = $encryption === 'ssl' ? 'ssl://' . $host : 'tcp://' . $host;
        $socket = @stream_socket_client($transport . ':' . $port, $errno, $errstr, $timeout);
        if (! is_resource($socket)) {
            return [
                'ok' => false,
                'message' => 'Nao foi possivel conectar ao servidor SMTP configurado.',
            ];
        }

        stream_set_timeout($socket, $timeout);

        try {
            $this->expectCode($socket, [220]);
            $serverName = parse_url((string) ($this->settings['site_base_url'] ?? ''), PHP_URL_HOST);
            $ehloHost = is_string($serverName) && $serverName !== '' ? $serverName : 'localhost';
            $this->command($socket, 'EHLO ' . $ehloHost, [250]);

            if ($encryption === 'tls') {
                $this->command($socket, 'STARTTLS', [220]);
                if (! @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException('Falha ao iniciar TLS.');
                }
                $this->command($socket, 'EHLO ' . $ehloHost, [250]);
            }

            if ($username !== '' || $password !== '') {
                $this->command($socket, 'AUTH LOGIN', [334]);
                $this->command($socket, base64_encode($username), [334]);
                $this->command($socket, base64_encode($password), [235]);
            }

            $this->command($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
            $this->command($socket, 'RCPT TO:<' . $recipientEmail . '>', [250, 251]);
            $this->command($socket, 'DATA', [354]);

            $headers = [
                'From: ' . $this->formatMailbox($fromEmail, $fromName),
                'To: ' . $this->formatMailbox($recipientEmail, $recipientName),
                'Subject: ' . $this->encodeHeader($subject),
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: base64',
            ];

            if ($replyToEmail !== '' && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
                $headers[] = 'Reply-To: ' . $this->formatMailbox($replyToEmail, $replyToName !== '' ? $replyToName : $replyToEmail);
            }

            $encodedBody = rtrim(chunk_split(base64_encode($body), 76, "\r\n"));
            fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n" . $encodedBody . "\r\n.\r\n");
            $this->expectCode($socket, [250]);
            $this->command($socket, 'QUIT', [221]);
        } catch (\Throwable) {
            @fwrite($socket, "QUIT\r\n");
            @fclose($socket);

            return [
                'ok' => false,
                'message' => 'Nao foi possivel enviar sua mensagem agora.',
            ];
        }

        @fclose($socket);

        return [
            'ok' => true,
            'message' => 'Sua mensagem foi enviada com sucesso.',
        ];
    }

    private function command($socket, string $command, array $expectedCodes): string
    {
        fwrite($socket, $command . "\r\n");

        return $this->expectCode($socket, $expectedCodes);
    }

    private function expectCode($socket, array $expectedCodes): string
    {
        $response = '';

        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (strlen($line) < 4 || $line[3] !== '-') {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);
        if (! in_array($code, $expectedCodes, true)) {
            throw new \RuntimeException('SMTP returned unexpected code: ' . $response);
        }

        return $response;
    }

    private function formatMailbox(string $email, string $name): string
    {
        $safeName = trim(str_replace(['"', "\r", "\n"], '', $name));
        if ($safeName === '') {
            return $email;
        }

        return $this->encodeHeader($safeName) . ' <' . $email . '>';
    }

    private function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }
}
