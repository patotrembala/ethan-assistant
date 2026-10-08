<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/database.php';
$keyMaterial = getenv('ETHAN_BACKUP_KEY') ?: '';
if (strlen($keyMaterial) < 20) {
    fwrite(STDERR, "Defina ETHAN_BACKUP_KEY com uma frase secreta forte.\n");
    exit(1);
}

$pdo = database();
$tables = ['usuarios', 'clientes', 'tipos_servico', 'ordens_servico', 'chamados_online', 'pendencias', 'exclusoes_pendentes', 'password_reset_tokens', 'password_reset_requests', 'login_attempts', 'audit_logs', 'privacy_requests'];
$export = ['created_at' => date(DATE_ATOM), 'tables' => []];
foreach ($tables as $table) {
    $export['tables'][$table] = $pdo->query('SELECT * FROM `' . $table . '`')->fetchAll(PDO::FETCH_ASSOC);
}

$plaintext = json_encode($export, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
$salt = random_bytes(16);
$iv = random_bytes(12);
$key = hash_pbkdf2('sha256', $keyMaterial, $salt, 200000, 32, true);
$tag = '';
$ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
if ($ciphertext === false) {
    throw new RuntimeException('Falha ao criptografar o backup.');
}

$backupDir = dirname(__DIR__, 2) . '/ethan-assistant-private/backups';
if (!is_dir($backupDir) && !mkdir($backupDir, 0700, true) && !is_dir($backupDir)) {
    throw new RuntimeException('Não foi possível criar a pasta privada de backups.');
}
$payload = "ETHANBACKUP1" . $salt . $iv . $tag . $ciphertext;
$file = $backupDir . '/ethan-' . date('Ymd-His') . '.backup';
file_put_contents($file, $payload, LOCK_EX);
chmod($file, 0600);
echo "Backup criptografado criado em {$file}\n";
