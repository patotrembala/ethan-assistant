<?php

declare(strict_types=1);

final class ClienteRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(?array $user = null): array
    {
        $sql = 'SELECT c.id, c.razao_social, c.cnpj, c.endereco, c.email, c.telefone, c.ativo, c.criado_em, c.atualizado_em
                FROM clientes c';
        $params = [];
        if (($user['perfil'] ?? 'admin') !== 'admin') {
            $sql .= ' WHERE EXISTS (SELECT 1 FROM ordens_servico os WHERE os.cliente_id = c.id AND os.tecnico_id = :tecnico_id)
                      OR EXISTS (SELECT 1 FROM chamados_online ch WHERE ch.cliente_id = c.id AND ch.tecnico_id = :tecnico_id_chamado)';
            $params = ['tecnico_id' => (int)$user['id'], 'tecnico_id_chamado' => (int)$user['id']];
        }
        $sql .= ' ORDER BY c.razao_social';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return array_map([$this, 'format'], $stmt->fetchAll());
    }

    public function find(int $id, ?array $user = null): ?array
    {
        $sql = 'SELECT c.id, c.razao_social, c.cnpj, c.endereco, c.email, c.telefone, c.ativo, c.criado_em, c.atualizado_em
                FROM clientes c WHERE c.id = :id';
        $params = ['id' => $id];
        if (($user['perfil'] ?? 'admin') !== 'admin') {
            $sql .= ' AND (EXISTS (SELECT 1 FROM ordens_servico os WHERE os.cliente_id = c.id AND os.tecnico_id = :tecnico_id)
                       OR EXISTS (SELECT 1 FROM chamados_online ch WHERE ch.cliente_id = c.id AND ch.tecnico_id = :tecnico_id_chamado))';
            $params['tecnico_id'] = (int)$user['id'];
            $params['tecnico_id_chamado'] = (int)$user['id'];
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $cliente = $stmt->fetch();

        return $cliente ? $this->format($cliente) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO clientes (razao_social, cnpj, endereco, email, telefone, ativo)
             VALUES (:razao_social, :cnpj, :endereco, :email, :telefone, :ativo)'
        );
        $stmt->execute($data);

        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare(
            'UPDATE clientes
             SET razao_social = :razao_social,
                 cnpj = :cnpj,
                 endereco = :endereco,
                 email = :email,
                 telefone = :telefone,
                 ativo = :ativo
             WHERE id = :id'
        );
        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM clientes WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function serviceOrders(int $clienteId, ?array $user = null): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT os.id,
                    os.equipamento_tipo,
                    os.equipamento_marca,
                    os.equipamento_modelo,
                    os.status,
                    DATE_FORMAT(os.abertura_em, '%d/%m/%Y %H:%i') AS abertura_em_formatada,
                    DATE_FORMAT(os.prazo_previsto, '%d/%m/%Y %H:%i') AS prazo_formatado,
                    u.nome AS tecnico_nome
             FROM ordens_servico os
             LEFT JOIN usuarios u ON u.id = os.tecnico_id
             WHERE os.cliente_id = :cliente_id" . (($user['perfil'] ?? 'admin') !== 'admin' ? ' AND os.tecnico_id = :tecnico_id' : '') . "
             ORDER BY os.abertura_em DESC"
        );
        $params = ['cliente_id' => $clienteId];
        if (($user['perfil'] ?? 'admin') !== 'admin') {
            $params['tecnico_id'] = (int)$user['id'];
        }
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    private function format(array $cliente): array
    {
        $cnpj = preg_replace('/\D/', '', (string)$cliente['cnpj']);
        if (strlen($cnpj) === 14) {
            $cliente['cnpj_formatado'] = substr($cnpj, 0, 2) . '.'
                . substr($cnpj, 2, 3) . '.'
                . substr($cnpj, 5, 3) . '/'
                . substr($cnpj, 8, 4) . '-'
                . substr($cnpj, 12, 2);
        }

        return $cliente;
    }
}
