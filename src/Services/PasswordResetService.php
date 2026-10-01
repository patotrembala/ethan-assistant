<?php

declare(strict_types=1);

final class PasswordResetService
{
    public const EXPIRATION_MINUTES = 30;
    private const REQUEST_INTERVAL_MINUTES = 5;

    public function __construct(private PDO $pdo)
    {
    }

    public function findActiveUserByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nome, email FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1'
        );
        $stmt->execute(['email' => strtolower(trim($email))]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function canRequest(int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM password_reset_tokens
             WHERE usuario_id = :usuario_id
               AND solicitado_em >= DATE_SUB(NOW(), INTERVAL ' . self::REQUEST_INTERVAL_MINUTES . ' MINUTE)'
        );
        $stmt->execute(['usuario_id' => $userId]);

        return (int)$stmt->fetchColumn() === 0;
    }

    public function createToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $this->pdo->beginTransaction();
        try {
            $invalidate = $this->pdo->prepare(
                'UPDATE password_reset_tokens
                 SET utilizado_em = NOW()
                 WHERE usuario_id = :usuario_id AND utilizado_em IS NULL'
            );
            $invalidate->execute(['usuario_id' => $userId]);

            $insert = $this->pdo->prepare(
                'INSERT INTO password_reset_tokens (usuario_id, token_hash, expira_em)
                 VALUES (:usuario_id, :token_hash, DATE_ADD(NOW(), INTERVAL ' . self::EXPIRATION_MINUTES . ' MINUTE))'
            );
            $insert->execute([
                'usuario_id' => $userId,
                'token_hash' => $tokenHash
            ]);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $token;
    }

    public function revokeToken(string $token): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE password_reset_tokens SET utilizado_em = NOW()
             WHERE token_hash = :token_hash AND utilizado_em IS NULL'
        );
        $stmt->execute(['token_hash' => hash('sha256', $token)]);
    }

    public function isValidToken(string $token): bool
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM password_reset_tokens pr
             INNER JOIN usuarios u ON u.id = pr.usuario_id
             WHERE pr.token_hash = :token_hash
               AND pr.utilizado_em IS NULL
               AND pr.expira_em > NOW()
               AND u.ativo = 1'
        );
        $stmt->execute(['token_hash' => hash('sha256', $token)]);

        return (int)$stmt->fetchColumn() === 1;
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return false;
        }

        $tokenHash = hash('sha256', $token);
        $this->pdo->beginTransaction();

        try {
            $select = $this->pdo->prepare(
                'SELECT pr.id, pr.usuario_id
                 FROM password_reset_tokens pr
                 INNER JOIN usuarios u ON u.id = pr.usuario_id
                 WHERE pr.token_hash = :token_hash
                   AND pr.utilizado_em IS NULL
                   AND pr.expira_em > NOW()
                   AND u.ativo = 1
                 LIMIT 1 FOR UPDATE'
            );
            $select->execute(['token_hash' => $tokenHash]);
            $reset = $select->fetch();

            if (!$reset) {
                $this->pdo->rollBack();
                return false;
            }

            $updateUser = $this->pdo->prepare(
                'UPDATE usuarios SET senha_hash = :senha_hash WHERE id = :id'
            );
            $updateUser->execute([
                'senha_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                'id' => (int)$reset['usuario_id']
            ]);

            $consume = $this->pdo->prepare(
                'UPDATE password_reset_tokens SET utilizado_em = NOW()
                 WHERE usuario_id = :usuario_id AND utilizado_em IS NULL'
            );
            $consume->execute(['usuario_id' => (int)$reset['usuario_id']]);
            $this->pdo->commit();

            return true;
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }
}
