<?php

declare(strict_types=1);

final class SecurityService
{
    public function __construct(private PDO $pdo, private string $key)
    {
    }

    public function isLoginBlocked(string $email, string $ip): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE sucesso = 0 AND tentativa_em >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
               AND (email_hash = :email_hash OR ip_hash = :ip_hash)'
        );
        $stmt->execute([
            'email_hash' => $this->fingerprint(strtolower(trim($email))),
            'ip_hash' => $this->fingerprint($ip)
        ]);

        return (int)$stmt->fetchColumn() >= 5;
    }

    public function recordLoginAttempt(string $email, string $ip, bool $success): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO login_attempts (email_hash, ip_hash, sucesso) VALUES (:email_hash, :ip_hash, :sucesso)'
        );
        $stmt->execute([
            'email_hash' => $this->fingerprint(strtolower(trim($email))),
            'ip_hash' => $this->fingerprint($ip),
            'sucesso' => $success ? 1 : 0
        ]);
    }

    public function audit(?int $userId, string $action, string $entity, ?int $recordId = null, array $details = []): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO audit_logs (usuario_id, acao, entidade, registro_id, detalhes_json, ip_hash)
             VALUES (:usuario_id, :acao, :entidade, :registro_id, :detalhes_json, :ip_hash)'
        );
        $stmt->execute([
            'usuario_id' => $userId,
            'acao' => mb_substr($action, 0, 80),
            'entidade' => mb_substr($entity, 0, 80),
            'registro_id' => $recordId,
            'detalhes_json' => $details ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'ip_hash' => $this->fingerprint((string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'))
        ]);
    }

    public function recentAuditLogs(int $limit = 200): array
    {
        $limit = max(1, min($limit, 500));
        return $this->pdo->query(
            'SELECT a.*, u.nome AS usuario_nome FROM audit_logs a
             LEFT JOIN usuarios u ON u.id = a.usuario_id
             ORDER BY a.criado_em DESC LIMIT ' . $limit
        )->fetchAll();
    }

    public function mailIsConfigured(array $mailConfig): bool
    {
        return trim((string)($mailConfig['host'] ?? '')) !== ''
            && trim((string)($mailConfig['username'] ?? '')) !== ''
            && (string)($mailConfig['password'] ?? '') !== '';
    }

    private function fingerprint(string $value): string
    {
        return hash_hmac('sha256', $value, $this->key);
    }
}
