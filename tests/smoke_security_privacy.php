<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SecurityService.php';
require_once __DIR__ . '/../src/Services/PrivacyRequestService.php';

$pdo = database();
$security = new SecurityService($pdo, 'test-key');
$privacy = new PrivacyRequestService($pdo);
$email = 'privacidade-' . bin2hex(random_bytes(4)) . '@example.test';
$ip = '192.0.2.' . random_int(1, 200);
$adminId = (int)$pdo->query("SELECT id FROM usuarios WHERE perfil = 'admin' ORDER BY id LIMIT 1")->fetchColumn();
if ($adminId === 0) {
    throw new RuntimeException('Crie um administrador para executar este teste.');
}

try {
    assert(!$security->isLoginBlocked($email, $ip));
    for ($i = 0; $i < 5; $i++) {
        $security->recordLoginAttempt($email, $ip, false);
    }
    assert($security->isLoginBlocked($email, $ip));

    $security->audit($adminId, 'teste', 'seguranca');
    $logs = $security->recentAuditLogs(10);
    assert(count($logs) >= 1);

    assert($privacy->canSubmit($email));
    $protocol = $privacy->create([
        'nome' => 'Titular de Teste',
        'email' => $email,
        'tipo' => 'acesso',
        'descricao' => 'Solicitação automática usada pelo teste de aceitação.'
    ]);
    assert(str_starts_with($protocol, 'EA-'));
    $requestId = (int)$pdo->query("SELECT id FROM privacy_requests WHERE protocolo = " . $pdo->quote($protocol))->fetchColumn();
    assert($requestId > 0);
    assert($privacy->updateStatus($requestId, 'concluida', $adminId));
    echo "Segurança e solicitações de privacidade: OK\n";
} finally {
    $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE email_hash = :email_hash');
    $stmt->execute(['email_hash' => hash_hmac('sha256', strtolower($email), 'test-key')]);
    $stmt = $pdo->prepare('DELETE FROM privacy_requests WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $pdo->exec("DELETE FROM audit_logs WHERE acao = 'teste' AND entidade = 'seguranca'");
}
