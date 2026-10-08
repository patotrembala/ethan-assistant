<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/database.php';
$pdo = database();
$pdo->beginTransaction();
try {
    $pdo->exec("DELETE FROM login_attempts WHERE tentativa_em < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $pdo->exec("DELETE FROM password_reset_tokens WHERE solicitado_em < DATE_SUB(NOW(), INTERVAL 30 DAY) AND (utilizado_em IS NOT NULL OR expira_em < NOW())");
    $pdo->exec("DELETE FROM audit_logs WHERE criado_em < DATE_SUB(NOW(), INTERVAL 24 MONTH)");
    $pdo->exec("DELETE FROM privacy_requests WHERE concluido_em < DATE_SUB(NOW(), INTERVAL 5 YEAR)");
    $pdo->commit();
    echo "Manutenção de retenção concluída.\n";
} catch (Throwable $exception) {
    $pdo->rollBack();
    fwrite(STDERR, "Falha na manutenção: {$exception->getMessage()}\n");
    exit(1);
}
