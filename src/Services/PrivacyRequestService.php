<?php

declare(strict_types=1);

final class PrivacyRequestService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function canSubmit(string $email): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM privacy_requests WHERE email = :email AND solicitado_em >= DATE_SUB(NOW(), INTERVAL 1 HOUR)'
        );
        $stmt->execute(['email' => strtolower(trim($email))]);
        return (int)$stmt->fetchColumn() < 3;
    }

    public function create(array $data): string
    {
        $protocol = 'EA-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $stmt = $this->pdo->prepare(
            'INSERT INTO privacy_requests (protocolo, nome, email, tipo, descricao)
             VALUES (:protocolo, :nome, :email, :tipo, :descricao)'
        );
        $stmt->execute([
            'protocolo' => $protocol,
            'nome' => trim((string)$data['nome']),
            'email' => strtolower(trim((string)$data['email'])),
            'tipo' => (string)$data['tipo'],
            'descricao' => trim((string)$data['descricao'])
        ]);
        return $protocol;
    }

    public function all(): array
    {
        return $this->pdo->query(
            'SELECT pr.*, u.nome AS responsavel_nome FROM privacy_requests pr
             LEFT JOIN usuarios u ON u.id = pr.responsavel_id
             ORDER BY FIELD(pr.status, \'recebida\', \'em_analise\', \'concluida\', \'recusada\'), pr.solicitado_em ASC'
        )->fetchAll();
    }

    public function updateStatus(int $id, string $status, int $adminId): bool
    {
        if (!in_array($status, ['em_analise', 'concluida', 'recusada'], true)) {
            return false;
        }
        $stmt = $this->pdo->prepare(
            'UPDATE privacy_requests SET status = :status, responsavel_id = :responsavel_id,
             concluido_em = IF(:status_conclusao IN (\'concluida\', \'recusada\'), NOW(), NULL)
             WHERE id = :id'
        );
        $stmt->execute([
            'status' => $status,
            'status_conclusao' => $status,
            'responsavel_id' => $adminId,
            'id' => $id
        ]);
        return $stmt->rowCount() === 1;
    }
}
