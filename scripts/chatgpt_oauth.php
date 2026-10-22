<?php

declare(strict_types=1);

/**
 * Conector local "Entrar com ChatGPT" do Ethan Assistant.
 *
 * Execute apenas no computador do administrador:
 *   php -S 127.0.0.1:1455 scripts/chatgpt_oauth.php
 * Depois acesse http://127.0.0.1:1455/start.
 *
 * As credenciais são gravadas em config/chatgpt.oauth.local.json, que é
 * ignorado pelo Git e deve ser enviado ao servidor por um canal privado.
 */

const CHATGPT_ISSUER = 'https://auth.openai.com';
const CHATGPT_AUTHORIZE = 'https://auth.openai.com/api/accounts/authorize';
const CHATGPT_TOKEN = 'https://auth.openai.com/api/accounts/oauth/token';
const CHATGPT_JWKS = 'https://auth.openai.com/.well-known/jwks.json';
const CHATGPT_RESOURCE = 'https://api.openai.com/v1';
const CHATGPT_CALLBACK = 'http://127.0.0.1:1455/auth/callback';

$projectRoot = dirname(__DIR__);
$credentialFile = $projectRoot . '/config/chatgpt.oauth.local.json';
$transactionFile = $projectRoot . '/config/chatgpt.oauth.transaction.local.json';
$hostFile = $projectRoot . '/config/chatgpt.host.local.txt';
$registrationFile = $projectRoot . '/config/chatgpt.registration.local.json';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

try {
    if ($path === '/start') {
        startAuthorization($credentialFile, $transactionFile, $hostFile, $registrationFile);
    }
    if ($path === '/auth/callback') {
        finishAuthorization($credentialFile, $transactionFile, $hostFile, $registrationFile);
    }
    renderPage('Conectar o Ethan ao ChatGPT', 'Use o botão abaixo para autorizar sua conta. A autorização fica restrita ao administrador e não é salva no Git.', '<a class="button" href="/start">Continuar com ChatGPT</a>');
} catch (Throwable $exception) {
    http_response_code(400);
    renderPage('Não foi possível conectar', htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'), '<a class="button secondary" href="/start">Tentar novamente</a>');
}

function startAuthorization(string $credentialFile, string $transactionFile, string $hostFile, string $registrationFile): never
{
    $existing = readJsonFile($credentialFile);
    $registration = readJsonFile($registrationFile);
    $hostId = trim(is_file($hostFile) ? (string)file_get_contents($hostFile) : '');
    if ($hostId === '') {
        $hostId = 'urn:uuid:' . uuidV4();
        atomicWrite($hostFile, $hostId . PHP_EOL);
    }

    // Mantém o mesmo tamanho usado pelo DevKit oficial @siwc/local.
    $verifier = base64UrlEncode(random_bytes(32));
    $state = base64UrlEncode(random_bytes(32));
    $nonce = base64UrlEncode(random_bytes(32));
    $issuedClientId = trim((string)($existing['client_id'] ?? $registration['client_id'] ?? ''));
    $clientId = $issuedClientId !== '' ? $issuedClientId : 'dynamic_agent_client';

    atomicWriteJson($transactionFile, [
        'state' => $state,
        'nonce' => $nonce,
        'code_verifier' => $verifier,
        'client_id' => $clientId,
        'issued_client_id' => $issuedClientId,
        'created_at' => time(),
    ]);

    $params = [
        'client_id' => $clientId,
        'response_type' => 'code',
        'redirect_uri' => CHATGPT_CALLBACK,
        'scope' => 'openid profile email offline_access resource.invoke chatgpt.tokens.use.direct',
        'resource' => CHATGPT_RESOURCE,
        'state' => $state,
        'nonce' => $nonce,
        'code_challenge_method' => 'S256',
        'code_challenge' => base64UrlEncode(hash('sha256', $verifier, true)),
        'ext_agent_host_id' => $hostId,
    ];
    if ($issuedClientId === '') {
        $params['agent_name_hint'] = 'Ethan Assistant';
    } else {
        if (!empty($existing['id_token'])) {
            $params['id_token_hint'] = (string)$existing['id_token'];
        }
        if (!empty($existing['email'])) {
            $params['login_hint'] = (string)$existing['email'];
        }
    }

    header('Location: ' . CHATGPT_AUTHORIZE . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986));
    exit;
}

function finishAuthorization(string $credentialFile, string $transactionFile, string $hostFile, string $registrationFile): never
{
    $transaction = readJsonFile($transactionFile);
    if (!$transaction || time() - (int)($transaction['created_at'] ?? 0) > 600) {
        throw new RuntimeException('A tentativa expirou. Inicie uma nova autorização.');
    }
    $returnedState = (string)($_GET['state'] ?? '');
    if ($returnedState === '' || !hash_equals((string)$transaction['state'], $returnedState)) {
        throw new RuntimeException('A validação de segurança da autorização falhou.');
    }
    if (!empty($_GET['error'])) {
        throw new RuntimeException('A autorização foi cancelada ou não está disponível para esta conta.');
    }

    $code = (string)($_GET['code'] ?? '');
    $returnedClientId = trim((string)($_GET['client_id'] ?? ''));
    $savedIssuedClientId = trim((string)($transaction['issued_client_id'] ?? ''));
    if ($savedIssuedClientId !== '') {
        if ($returnedClientId !== '' && !hash_equals($savedIssuedClientId, $returnedClientId)) {
            throw new RuntimeException('O identificador retornado não corresponde à conta autorizada.');
        }
        $issuedClientId = $savedIssuedClientId;
    } else {
        $issuedClientId = $returnedClientId;
    }
    if ($code === '' || $issuedClientId === '' || $issuedClientId === 'dynamic_agent_client') {
        throw new RuntimeException('A autorização não retornou todos os dados necessários.');
    }

    // O registro precisa sobreviver mesmo se o código expirar ou a troca falhar.
    // Na tentativa seguinte, o fluxo reutiliza este client_id em vez de criar outro.
    atomicWriteJson($registrationFile, [
        'client_id' => $issuedClientId,
        'saved_at' => gmdate(DATE_ATOM),
    ]);

    $token = httpForm(CHATGPT_TOKEN, [
        'grant_type' => 'authorization_code',
        'client_id' => $issuedClientId,
        'code' => $code,
        'code_verifier' => (string)$transaction['code_verifier'],
        'redirect_uri' => CHATGPT_CALLBACK,
        'resource' => CHATGPT_RESOURCE,
    ]);
    foreach (['access_token', 'refresh_token', 'id_token'] as $required) {
        if (empty($token[$required])) {
            throw new RuntimeException('A OpenAI não retornou a credencial ' . $required . '.');
        }
    }

    $claims = validateIdToken((string)$token['id_token'], $issuedClientId, (string)$transaction['nonce']);
    $scopes = preg_split('/\s+/', trim((string)($token['scope'] ?? ''))) ?: [];
    if (!in_array('chatgpt.tokens.use.direct', $scopes, true)) {
        throw new RuntimeException('A conta não autorizou o uso do plano ChatGPT pelo Ethan.');
    }

    $now = time();
    $credentials = [
        'email' => (string)($claims['email'] ?? ''),
        'issuer' => CHATGPT_ISSUER,
        'subject' => (string)$claims['sub'],
        'client_id' => $issuedClientId,
        'ext_agent_host_id' => trim((string)file_get_contents($hostFile)),
        'id_token' => (string)$token['id_token'],
        'access_token' => (string)$token['access_token'],
        'refresh_token' => (string)$token['refresh_token'],
        'token_type' => (string)($token['token_type'] ?? 'Bearer'),
        'expires_in' => (int)($token['expires_in'] ?? 3600),
        'expires_at' => $now + (int)($token['expires_in'] ?? 3600),
        'scopes' => array_values($scopes),
        'model' => 'gpt-6.1-sol',
        'saved_at' => gmdate(DATE_ATOM, $now),
    ];
    // A autorização já está válida neste ponto. Salve-a antes de consultar o
    // catálogo, pois a prévia pode não disponibilizar /v1/models em toda conta.
    atomicWriteJson($credentialFile, $credentials);

    $catalogNotice = '';
    try {
        $modelsResponse = httpJson('https://api.openai.com/v1/models', null, (string)$token['access_token']);
        $models = [];
        foreach (($modelsResponse['models'] ?? $modelsResponse['data'] ?? []) as $availableModel) {
            $slug = trim((string)($availableModel['slug'] ?? $availableModel['id'] ?? ''));
            if ($slug !== '' && (($availableModel['visibility'] ?? 'list') === 'list')) {
                $models[] = $slug;
            }
        }
        $preferred = ['gpt-6.1-sol', 'gpt-6-sol', 'gpt-5.6-sol', 'gpt-5.6-luna'];
        if ($models) {
            $credentials['model'] = $models[0];
            foreach ($preferred as $candidate) {
                if (in_array($candidate, $models, true)) {
                    $credentials['model'] = $candidate;
                    break;
                }
            }
            atomicWriteJson($credentialFile, $credentials);
        }
    } catch (Throwable) {
        $catalogNotice = ' O catálogo não estava disponível; será usado o modelo oficial padrão da prévia.';
    }
    @unlink($transactionFile);

    renderPage('Ethan conectado ao ChatGPT', 'A autorização foi validada e guardada fora do Git. Modelo selecionado: ' . htmlspecialchars((string)$credentials['model'], ENT_QUOTES, 'UTF-8') . '.' . $catalogNotice, '<strong>Você já pode fechar esta aba.</strong>');
}

function validateIdToken(string $jwt, string $clientId, string $nonce): array
{
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        throw new RuntimeException('O token de identidade recebido é inválido.');
    }
    $header = json_decode(base64UrlDecode($parts[0]), true, 512, JSON_THROW_ON_ERROR);
    $claims = json_decode(base64UrlDecode($parts[1]), true, 512, JSON_THROW_ON_ERROR);
    if (($header['alg'] ?? '') !== 'RS256' || empty($header['kid'])) {
        throw new RuntimeException('O token usa uma assinatura não suportada.');
    }
    $jwks = httpJson(CHATGPT_JWKS);
    $jwk = null;
    foreach (($jwks['keys'] ?? []) as $candidate) {
        if (($candidate['kid'] ?? '') === $header['kid']) {
            $jwk = $candidate;
            break;
        }
    }
    if (!$jwk || empty($jwk['n']) || empty($jwk['e'])) {
        throw new RuntimeException('Não foi possível localizar a chave pública da OpenAI.');
    }
    $publicKey = jwkToPem((string)$jwk['n'], (string)$jwk['e']);
    $verified = openssl_verify($parts[0] . '.' . $parts[1], base64UrlDecode($parts[2]), $publicKey, OPENSSL_ALGO_SHA256);
    if ($verified !== 1) {
        throw new RuntimeException('A assinatura da OpenAI não pôde ser validada.');
    }

    $audience = $claims['aud'] ?? null;
    $audienceOk = is_array($audience) ? in_array($clientId, $audience, true) : hash_equals($clientId, (string)$audience);
    if (($claims['iss'] ?? '') !== CHATGPT_ISSUER || !$audienceOk || (int)($claims['exp'] ?? 0) <= time() || !hash_equals($nonce, (string)($claims['nonce'] ?? '')) || empty($claims['sub'])) {
        throw new RuntimeException('Os dados de identidade retornados não passaram na validação.');
    }
    return $claims;
}

function jwkToPem(string $modulus, string $exponent): string
{
    $modulus = base64UrlDecode($modulus);
    $exponent = base64UrlDecode($exponent);
    $rsa = derSequence(derInteger($modulus) . derInteger($exponent));
    $algorithm = derSequence("\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00");
    $subjectPublicKey = derSequence($algorithm . "\x03" . derLength(strlen($rsa) + 1) . "\x00" . $rsa);
    return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($subjectPublicKey), 64, "\n") . "-----END PUBLIC KEY-----\n";
}

function derInteger(string $value): string
{
    $value = ltrim($value, "\x00");
    if ($value === '' || (ord($value[0]) & 0x80)) {
        $value = "\x00" . $value;
    }
    return "\x02" . derLength(strlen($value)) . $value;
}

function derSequence(string $value): string
{
    return "\x30" . derLength(strlen($value)) . $value;
}

function derLength(int $length): string
{
    if ($length < 128) {
        return chr($length);
    }
    $bytes = '';
    while ($length > 0) {
        $bytes = chr($length & 0xff) . $bytes;
        $length >>= 8;
    }
    return chr(0x80 | strlen($bytes)) . $bytes;
}

function httpForm(string $url, array $form): array
{
    return httpJson($url, http_build_query($form, '', '&', PHP_QUERY_RFC3986), null, 'application/x-www-form-urlencoded');
}

function httpJson(string $url, ?string $body = null, ?string $bearer = null, string $contentType = 'application/json'): array
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException('A extensão cURL do PHP não está ativa.');
    }
    $headers = ['Accept: application/json'];
    if ($body !== null) {
        $headers[] = 'Content-Type: ' . $contentType;
    }
    if ($bearer !== null) {
        $headers[] = 'Authorization: Bearer ' . $bearer;
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => $body !== null,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 45,
    ]);
    $raw = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $responseType = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $error = curl_error($ch);
    curl_close($ch);
    if ($raw === false || $error !== '') {
        throw new RuntimeException('Falha ao conectar ao serviço de autorização.');
    }
    $data = json_decode((string)$raw, true);
    if (!is_array($data) || $status < 200 || $status >= 300) {
        $safeError = (string)($data['error_description'] ?? $data['error']['message'] ?? $data['error'] ?? 'resposta inválida');
        throw new RuntimeException(
            'A autorização não foi concluída (HTTP ' . $status . ', ' . ($responseType ?: 'tipo desconhecido') . '): '
            . mb_substr($safeError, 0, 240)
        );
    }
    return $data;
}

function readJsonFile(string $path): array
{
    if (!is_file($path)) {
        return [];
    }
    $decoded = json_decode((string)file_get_contents($path), true);
    return is_array($decoded) ? $decoded : [];
}

function atomicWriteJson(string $path, array $data): void
{
    atomicWrite($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
}

function atomicWrite(string $path, string $contents): void
{
    $temporary = $path . '.tmp-' . bin2hex(random_bytes(6));
    if (file_put_contents($temporary, $contents, LOCK_EX) === false || !rename($temporary, $path)) {
        @unlink($temporary);
        throw new RuntimeException('Não foi possível salvar a autorização localmente.');
    }
    @chmod($path, 0600);
}

function base64UrlEncode(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function base64UrlDecode(string $value): string
{
    $padding = strlen($value) % 4;
    if ($padding) {
        $value .= str_repeat('=', 4 - $padding);
    }
    $decoded = base64_decode(strtr($value, '-_', '+/'), true);
    if ($decoded === false) {
        throw new RuntimeException('Token codificado em formato inválido.');
    }
    return $decoded;
}

function uuidV4(): string
{
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
    $hex = bin2hex($bytes);
    return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
}

function renderPage(string $title, string $message, string $action): never
{
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html lang="pt-BR"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title><style>body{margin:0;font-family:system-ui;background:#071525;color:#eef7ff;display:grid;place-items:center;min-height:100vh}.card{width:min(520px,calc(100% - 48px));padding:42px;border:1px solid #22425f;border-radius:24px;background:#0b2034;box-shadow:0 24px 70px #0007}h1{margin:0 0 12px;font-size:30px}p{color:#acc5d9;line-height:1.6}.button{display:inline-block;margin-top:18px;padding:13px 20px;border-radius:12px;background:#2f7df4;color:white;text-decoration:none;font-weight:700}.secondary{background:#173b5a}</style>';
    echo '<body><main class="card"><div style="color:#59c9ff;font-weight:800;margin-bottom:18px">ETHAN ASSISTANT</div><h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1><p>' . $message . '</p>' . $action . '</main></body></html>';
    exit;
}
