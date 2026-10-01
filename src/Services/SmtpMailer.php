<?php

declare(strict_types=1);

final class SmtpMailer
{
    public function __construct(private array $config)
    {
    }

    public function sendPasswordReset(string $recipientEmail, string $recipientName, string $resetUrl): void
    {
        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Destinatário inválido.');
        }

        $host = trim((string)($this->config['host'] ?? ''));
        $port = (int)($this->config['port'] ?? 587);
        $encryption = strtolower(trim((string)($this->config['encryption'] ?? 'tls')));
        $username = trim((string)($this->config['username'] ?? ''));
        $password = (string)($this->config['password'] ?? '');
        $fromEmail = trim((string)($this->config['from_email'] ?? $username));
        $fromName = $this->sanitizeHeader((string)($this->config['from_name'] ?? 'Ethan Assistant'));

        if ($host === '' || $username === '' || $password === '' || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('O envio de e-mail ainda não foi configurado.');
        }

        if (!in_array($encryption, ['tls', 'ssl', 'none'], true)) {
            throw new RuntimeException('Configuração de criptografia SMTP inválida.');
        }

        $transport = $encryption === 'ssl' ? 'ssl://' : 'tcp://';
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'peer_name' => $host
            ]
        ]);

        $socket = @stream_socket_client(
            $transport . $host . ':' . $port,
            $errorNumber,
            $errorMessage,
            15,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!is_resource($socket)) {
            throw new RuntimeException('Não foi possível conectar ao servidor de e-mail.');
        }

        stream_set_timeout($socket, 15);

        try {
            $this->expect($socket, [220]);
            $this->command($socket, 'EHLO ethan-assistant', [250]);

            if ($encryption === 'tls') {
                $this->command($socket, 'STARTTLS', [220]);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('Não foi possível proteger a conexão SMTP.');
                }
                $this->command($socket, 'EHLO ethan-assistant', [250]);
            }

            $this->command($socket, 'AUTH LOGIN', [334]);
            $this->command($socket, base64_encode($username), [334]);
            $this->command($socket, base64_encode($password), [235]);
            $this->command($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
            $this->command($socket, 'RCPT TO:<' . $recipientEmail . '>', [250, 251]);
            $this->command($socket, 'DATA', [354]);

            $safeName = trim(preg_replace('/[\r\n]+/', ' ', $recipientName)) ?: 'usuário';
            $subject = 'Redefinição de senha - Ethan Assistant';
            $body = "Olá, {$safeName}!\n\n"
                . "Recebemos uma solicitação para redefinir sua senha no Ethan Assistant.\n\n"
                . "Abra o link abaixo. Ele é válido por 30 minutos e pode ser usado uma única vez:\n"
                . $resetUrl . "\n\n"
                . "Se você não fez esta solicitação, ignore este e-mail. Sua senha continuará a mesma.\n";

            $headers = [
                'Date: ' . date(DATE_RFC2822),
                'From: ' . $fromName . ' <' . $fromEmail . '>',
                'To: <' . $recipientEmail . '>',
                'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: 8bit'
            ];

            $payload = implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n", "\r\n", $body);
            $payload = preg_replace('/^\./m', '..', $payload);
            fwrite($socket, $payload . "\r\n.\r\n");
            $this->expect($socket, [250]);
            $this->command($socket, 'QUIT', [221]);
        } finally {
            fclose($socket);
        }
    }

    private function command($socket, string $command, array $expectedCodes): void
    {
        if (fwrite($socket, $command . "\r\n") === false) {
            throw new RuntimeException('Falha de comunicação com o servidor de e-mail.');
        }
        $this->expect($socket, $expectedCodes);
    }

    private function expect($socket, array $expectedCodes): void
    {
        $response = '';
        do {
            $line = fgets($socket, 1024);
            if ($line === false) {
                throw new RuntimeException('O servidor de e-mail não respondeu.');
            }
            $response .= $line;
        } while (isset($line[3]) && $line[3] === '-');

        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $expectedCodes, true)) {
            throw new RuntimeException('O servidor de e-mail recusou a solicitação (código ' . $code . ').');
        }
    }

    private function sanitizeHeader(string $value): string
    {
        return trim((string)preg_replace('/[\r\n]+/', ' ', $value));
    }
}
