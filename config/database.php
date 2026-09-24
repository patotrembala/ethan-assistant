<?php

declare(strict_types=1);

/**
 * Cria uma conexao PDO reutilizavel com o MySQL.
 * As credenciais ficam em database.local.php, que nao e enviado ao Git.
 */
function database(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $localConfigFile = __DIR__ . '/database.local.php';
    $config = is_file($localConfigFile) ? require $localConfigFile : [];

    $host = (string)($config['host'] ?? '127.0.0.1');
    $port = (int)($config['port'] ?? 3306);
    $database = (string)($config['database'] ?? 'ethan_assistant');
    $username = (string)($config['username'] ?? 'root');
    $password = (string)($config['password'] ?? '');

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $host,
        $port,
        $database
    );

    $connection = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    return $connection;
}
