<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/PasswordResetService.php';

$pdo = database();
$email = 'teste-reset-' . bin2hex(random_bytes(4)) . '@example.test';
$userId = null;

try {
    $insert = $pdo->prepare(
        'INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo)
         VALUES (:nome, :email, :senha_hash, :perfil, 1)'
    );
    $insert->execute([
        'nome' => 'Teste Recuperação',
        'email' => $email,
        'senha_hash' => password_hash('SenhaAntiga123!', PASSWORD_DEFAULT),
        'perfil' => 'tecnico'
    ]);
    $userId = (int)$pdo->lastInsertId();

    $service = new PasswordResetService($pdo);
    $user = $service->findActiveUserByEmail($email);
    assert($user !== null && (int)$user['id'] === $userId);
    assert($service->canRequest($userId));

    $requestId = $service->createRequest($userId);
    assert($requestId > 0);
    assert(!$service->canRequest($userId));
    $pending = $service->findPendingRequest($requestId);
    assert($pending !== null && (int)$pending['usuario_id'] === $userId);

    $token = $service->createToken($userId);
    assert(strlen($token) === 64);
    assert($service->isValidToken($token));
    assert($service->approveRequest($requestId, $userId));
    assert($service->findPendingRequest($requestId) === null);
    assert($service->resetPassword($token, 'NovaSenha123!'));
    assert(!$service->isValidToken($token));
    assert(!$service->resetPassword($token, 'OutraSenha123!'));

    $check = $pdo->prepare('SELECT senha_hash FROM usuarios WHERE id = :id');
    $check->execute(['id' => $userId]);
    assert(password_verify('NovaSenha123!', (string)$check->fetchColumn()));

    echo "Smoke test de recuperação de senha concluído com sucesso.\n";
} finally {
    if ($userId !== null) {
        $delete = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
        $delete->execute(['id' => $userId]);
    }
}
