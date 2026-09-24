<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/validation.php';
require_once __DIR__ . '/../src/Repositories/ClienteRepository.php';

$pdo = database();
$repository = new ClienteRepository($pdo);

[$data, $errors] = validateCliente([
    'razao_social' => 'Cliente de Teste Automatizado',
    'cnpj' => '11.222.333/0001-81',
    'endereco' => 'Rua de Teste, 123, Centro',
    'email' => 'teste@example.com',
    'telefone' => '(11) 99999-9999',
    'ativo' => '1'
]);

if ($errors) {
    throw new RuntimeException('A massa de teste foi rejeitada: ' . json_encode($errors));
}

$pdo->beginTransaction();

try {
    $id = $repository->create($data);
    $created = $repository->find($id);
    if (!$created || $created['razao_social'] !== $data['razao_social']) {
        throw new RuntimeException('Falha ao criar ou consultar o cliente.');
    }

    $data['razao_social'] = 'Cliente de Teste Atualizado';
    $repository->update($id, $data);
    $updated = $repository->find($id);
    if (!$updated || $updated['razao_social'] !== $data['razao_social']) {
        throw new RuntimeException('Falha ao atualizar o cliente.');
    }

    $repository->delete($id);
    if ($repository->find($id) !== null) {
        throw new RuntimeException('Falha ao excluir o cliente.');
    }

    echo "CRUD de clientes: OK\n";
} finally {
    $pdo->rollBack();
}
