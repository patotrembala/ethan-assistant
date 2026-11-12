<?php

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$publicFile = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $path);
$blockedPrefixes = ['/config', '/database', '/docs', '/scripts', '/src', '/storage', '/tests'];

foreach ($blockedPrefixes as $prefix) {
    if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
        http_response_code(403);
        exit('Acesso negado.');
    }
}

if ($path !== '/' && is_file($publicFile)) {
    return false;
}

require __DIR__ . '/index.php';
