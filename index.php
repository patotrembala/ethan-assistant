<?php
/**
 * Ethan Assistant - Front Controller & Visualizador de Telas
 * Permite navegação direta nas telas do frontend com dados simulados
 * enquanto os controllers do Codex são integrados.
 */

$sessionPath = dirname(__DIR__) . '/ethan-assistant-private/sessions';
if (!is_dir($sessionPath)) {
    @mkdir($sessionPath, 0700, true);
}
if (!is_dir($sessionPath) || !is_writable($sessionPath)) {
    $sessionPath = sys_get_temp_dir();
}
session_save_path($sessionPath);

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax'
]);
session_start();

header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/validation.php';
require_once __DIR__ . '/src/Repositories/ClienteRepository.php';
require_once __DIR__ . '/src/Services/PasswordResetService.php';
require_once __DIR__ . '/src/Services/SmtpMailer.php';
require_once __DIR__ . '/src/Services/SecurityService.php';
require_once __DIR__ . '/src/Services/PrivacyRequestService.php';

// Roteamento amigável
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$baseUrl = '';

// Autenticação temporária da prévia publicada.
// As credenciais reais ficam em auth.local.php, arquivo ignorado pelo Git.
$authFile = __DIR__ . '/config/auth.local.php';
$authConfig = is_file($authFile) ? require $authFile : [];
$mailFile = __DIR__ . '/config/mail.local.php';
$mailConfig = is_file($mailFile) ? require $mailFile : [];
$securityFile = __DIR__ . '/config/security.local.php';
$securityConfig = is_file($securityFile) ? require $securityFile : [];
$pdo = database();
$securityService = new SecurityService($pdo, (string)($securityConfig['key'] ?? $authConfig['admin_email'] ?? 'ethan-assistant'));
$privacyRequestService = new PrivacyRequestService($pdo);
$userCount = (int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();

if (isset($_SESSION['user'])) {
    $now = time();
    $lastActivity = (int)($_SESSION['last_activity'] ?? $now);
    $loginAt = (int)($_SESSION['login_at'] ?? $now);
    if (($now - $lastActivity) > 1800 || ($now - $loginAt) > 43200) {
        $_SESSION = [];
        session_regenerate_id(true);
        if (!in_array($requestUri, ['/login', '/esqueci-senha', '/redefinir-senha', '/privacidade', '/solicitacao-privacidade'], true)) {
            header('Location: /login?status=sessao-expirada');
            exit;
        }
    } else {
        $_SESSION['last_activity'] = $now;
    }
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void
{
    $sessionToken = (string)($_SESSION['csrf_token'] ?? '');
    $requestToken = (string)($_POST['_token'] ?? '');
    if ($sessionToken === '' || !hash_equals($sessionToken, $requestToken)) {
        http_response_code(419);
        exit('Sessão expirada. Atualize a página e tente novamente.');
    }
}

function passwordPolicyError(string $password): ?string
{
    if (strlen($password) < 12) {
        return 'A senha deve possuir pelo menos 12 caracteres.';
    }
    if (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password)
        || !preg_match('/\d/', $password) || !preg_match('/[^a-zA-Z0-9]/', $password)) {
        return 'Use letra maiúscula, minúscula, número e caractere especial.';
    }
    $normalized = strtolower(preg_replace('/\s+/', '', $password));
    if (in_array($normalized, ['123456789012', 'senha123456!', 'administrador1!', 'ethanassistant1!'], true)) {
        return 'Escolha uma senha menos previsível.';
    }

    return null;
}

$csrfToken = csrfToken();

if ($requestUri === '/logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if (!empty($_SESSION['user']['id'])) {
        $securityService->audit((int)$_SESSION['user']['id'], 'logout', 'sessao');
    }
    $_SESSION = [];
    session_destroy();
    header('Location: /login');
    exit;
}

if ($requestUri === '/primeiro-acesso') {
    if ($userCount > 0) {
        header('Location: /login');
        exit;
    }

    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $email = strtolower(trim($_POST['email'] ?? ''));
        $senha = (string)($_POST['senha'] ?? '');
        $confirmacao = (string)($_POST['confirmacao_senha'] ?? '');
        $adminEmail = strtolower((string)($authConfig['admin_email'] ?? ''));

        if ($adminEmail === '' || !hash_equals($adminEmail, $email)) {
            $error = 'Use o e-mail administrador autorizado.';
        } elseif (($passwordError = passwordPolicyError($senha)) !== null) {
            $error = $passwordError;
        } elseif (!hash_equals($senha, $confirmacao)) {
            $error = 'A confirmação da senha não corresponde.';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) VALUES (:nome, :email, :senha_hash, :perfil, 1)'
            );
            $stmt->execute([
                'nome' => (string)($authConfig['admin_name'] ?? 'Administrador'),
                'email' => $adminEmail,
                'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
                'perfil' => 'admin'
            ]);

            header('Location: /login?cadastro=sucesso');
            exit;
        }
    }

    require __DIR__ . '/views/auth/first_access.php';
    exit;
}

if ($requestUri === '/login') {
    if (isset($_SESSION['user']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /dashboard');
        exit;
    }

    if ($userCount === 0) {
        header('Location: /primeiro-acesso');
        exit;
    }

    $error = null;
    $success = match ($_GET['status'] ?? ($_GET['cadastro'] ?? '')) {
        'sucesso' => 'Administrador criado. Entre com sua senha.',
        'senha-alterada' => 'Senha alterada com sucesso. Entre com sua nova senha.',
        'sessao-expirada' => 'Sua sessão expirou por segurança. Entre novamente.',
        default => null
    };
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $email = strtolower(trim($_POST['email'] ?? ''));
        $senha = (string)($_POST['senha'] ?? '');
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

        if ($securityService->isLoginBlocked($email, $ip)) {
            $error = 'Muitas tentativas de acesso. Aguarde 15 minutos e tente novamente.';
            require __DIR__ . '/views/auth/login.php';
            exit;
        }

        $stmt = $pdo->prepare(
            'SELECT id, nome, email, senha_hash, perfil FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha_hash'])) {
            $securityService->recordLoginAttempt($email, $ip, true);
            if ((string)$user['perfil'] === 'admin' && $securityService->mailIsConfigured($mailConfig)) {
                $code = (string)random_int(100000, 999999);
                $_SESSION['mfa_pending'] = [
                    'user' => [
                        'id' => (int)$user['id'],
                        'nome' => (string)$user['nome'],
                        'email' => (string)$user['email'],
                        'perfil' => (string)$user['perfil']
                    ],
                    'code_hash' => password_hash($code, PASSWORD_DEFAULT),
                    'expires_at' => time() + 600,
                    'attempts' => 0
                ];
                try {
                    (new SmtpMailer($mailConfig))->sendLoginCode((string)$user['email'], (string)$user['nome'], $code);
                    header('Location: /verificar-acesso');
                    exit;
                } catch (Throwable $exception) {
                    unset($_SESSION['mfa_pending']);
                    error_log('Falha ao enviar MFA: ' . $exception->getMessage());
                    $error = 'Não foi possível enviar o código de segurança. Tente novamente.';
                    require __DIR__ . '/views/auth/login.php';
                    exit;
                }
            }
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'nome' => (string)$user['nome'],
                'email' => (string)$user['email'],
                'perfil' => (string)$user['perfil']
            ];
            $_SESSION['login_at'] = time();
            $_SESSION['last_activity'] = time();
            $securityService->audit((int)$user['id'], 'login', 'sessao');
            header('Location: /dashboard');
            exit;
        }

        $securityService->recordLoginAttempt($email, $ip, false);
        $error = 'E-mail ou senha incorretos.';
    }

    require __DIR__ . '/views/auth/login.php';
    exit;
}

if ($requestUri === '/verificar-acesso') {
    $pendingMfa = $_SESSION['mfa_pending'] ?? null;
    if (!$pendingMfa || (int)($pendingMfa['expires_at'] ?? 0) < time()) {
        unset($_SESSION['mfa_pending']);
        header('Location: /login');
        exit;
    }

    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $code = preg_replace('/\D/', '', (string)($_POST['codigo'] ?? ''));
        $_SESSION['mfa_pending']['attempts'] = (int)($_SESSION['mfa_pending']['attempts'] ?? 0) + 1;

        if ($_SESSION['mfa_pending']['attempts'] > 5) {
            unset($_SESSION['mfa_pending']);
            header('Location: /login');
            exit;
        }

        if (strlen($code) === 6 && password_verify($code, (string)$pendingMfa['code_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = $pendingMfa['user'];
            $_SESSION['login_at'] = time();
            $_SESSION['last_activity'] = time();
            unset($_SESSION['mfa_pending']);
            $securityService->audit((int)$_SESSION['user']['id'], 'login_mfa', 'sessao');
            header('Location: /dashboard');
            exit;
        }
        $error = 'Código inválido ou expirado.';
    }

    require __DIR__ . '/views/auth/verify_mfa.php';
    exit;
}

if ($requestUri === '/esqueci-senha') {
    if ($userCount === 0) {
        header('Location: /primeiro-acesso');
        exit;
    }

    $error = null;
    $success = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $email = strtolower(trim($_POST['email'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Informe um e-mail válido.';
        } else {
            $resetService = new PasswordResetService($pdo);
            $user = $resetService->findActiveUserByEmail($email);

            if ($user && $resetService->canRequest((int)$user['id'])) {
                $requestId = $resetService->createRequest((int)$user['id']);
                $securityService->audit(null, 'solicitar_recuperacao', 'password_reset_request', $requestId);
            }

            $success = 'Se o e-mail estiver cadastrado, a solicitação foi enviada para aprovação do administrador.';
        }
    }

    require __DIR__ . '/views/auth/forgot_password.php';
    exit;
}

if ($requestUri === '/redefinir-senha') {
    $resetService = new PasswordResetService($pdo);
    $token = strtolower(trim((string)($_POST['token'] ?? $_GET['token'] ?? '')));
    $tokenIsValid = $resetService->isValidToken($token);
    $error = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $senha = (string)($_POST['senha'] ?? '');
        $confirmacao = (string)($_POST['confirmacao_senha'] ?? '');

        if (!$tokenIsValid) {
            $error = 'Este link é inválido ou expirou. Solicite um novo.';
        } elseif (($passwordError = passwordPolicyError($senha)) !== null) {
            $error = $passwordError;
        } elseif (!hash_equals($senha, $confirmacao)) {
            $error = 'A confirmação da senha não corresponde.';
        } elseif ($resetService->resetPassword($token, $senha)) {
            $_SESSION = [];
            session_regenerate_id(true);
            header('Location: /login?status=senha-alterada');
            exit;
        } else {
            $error = 'Este link é inválido ou expirou. Solicite um novo.';
            $tokenIsValid = false;
        }
    }

    require __DIR__ . '/views/auth/reset_password.php';
    exit;
}

if ($requestUri === '/privacidade') {
    $privacyController = (string)($authConfig['privacy_controller'] ?? 'Responsável pelo Ethan Assistant');
    $privacyEmail = (string)($authConfig['privacy_email'] ?? $authConfig['admin_email'] ?? '');
    require __DIR__ . '/views/privacy/notice.php';
    exit;
}

if ($requestUri === '/solicitacao-privacidade') {
    $error = null;
    $success = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $requestData = [
            'nome' => trim((string)($_POST['nome'] ?? '')),
            'email' => strtolower(trim((string)($_POST['email'] ?? ''))),
            'tipo' => (string)($_POST['tipo'] ?? ''),
            'descricao' => trim((string)($_POST['descricao'] ?? ''))
        ];
        $allowedTypes = ['acesso', 'correcao', 'eliminacao', 'bloqueio', 'portabilidade', 'informacao', 'outro'];
        if (mb_strlen($requestData['nome']) < 3 || mb_strlen($requestData['nome']) > 120) {
            $error = 'Informe seu nome completo.';
        } elseif (!filter_var($requestData['email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'Informe um e-mail válido.';
        } elseif (!in_array($requestData['tipo'], $allowedTypes, true)) {
            $error = 'Selecione o tipo da solicitação.';
        } elseif (mb_strlen($requestData['descricao']) < 10 || mb_strlen($requestData['descricao']) > 2000) {
            $error = 'Descreva a solicitação entre 10 e 2.000 caracteres.';
        } elseif (!$privacyRequestService->canSubmit($requestData['email'])) {
            $error = 'Limite temporário atingido. Tente novamente mais tarde.';
        } else {
            $protocol = $privacyRequestService->create($requestData);
            $securityService->audit(null, 'criar', 'privacy_request', null, ['protocolo' => $protocol]);
            $success = 'Solicitação registrada. Anote o protocolo: ' . $protocol;
        }
    }
    require __DIR__ . '/views/privacy/request.php';
    exit;
}

if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

$currentUser = $_SESSION['user'];
$isAdmin = ($currentUser['perfil'] ?? '') === 'admin';
$passwordResetService = new PasswordResetService($pdo);
$pendingPasswordResetCount = $isAdmin ? $passwordResetService->pendingCount() : 0;
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$clienteRepository = new ClienteRepository($pdo);

function requireAdmin(bool $isAdmin): void
{
    if (!$isAdmin) {
        http_response_code(403);
        exit('Acesso restrito ao administrador.');
    }
}

function redirectWithFlash(string $location, string $type, string $message): never
{
    $_SESSION['flash'] = ['tipo' => $type, 'mensagem' => $message];
    header('Location: ' . $location);
    exit;
}

// Dados mock para visualização das telas
$mockClientes = [
    [
        'id' => 1,
        'razao_social' => 'Acme Corporação de Tecnologia',
        'cnpj' => '12345678000195',
        'cnpj_formatado' => '12.345.678/0001-95',
        'telefone' => '(11) 3214-5500',
        'email' => 'suporte@acme.com.br',
        'endereco' => 'Av. Paulista, 1000 - 8º andar, Bela Vista - São Paulo/SP',
        'ativo' => 1
    ],
    [
        'id' => 2,
        'razao_social' => 'Logística Expressa Global S/A',
        'cnpj' => '98765432000188',
        'cnpj_formatado' => '98.765.432/0001-88',
        'telefone' => '(11) 4567-8900',
        'email' => 'ti@expressaglobal.com',
        'endereco' => 'Rodovia Anhanguera, km 25, Galpão 3 - São Paulo/SP',
        'ativo' => 1
    ],
    [
        'id' => 3,
        'razao_social' => 'Consultoria Financeira Aliança',
        'cnpj' => '45678901000123',
        'cnpj_formatado' => '45.678.901/0001-23',
        'telefone' => '(11) 2233-4455',
        'email' => 'contato@aliancafin.com.br',
        'endereco' => 'Rua Funchal, 418 - Vila Olímpia - São Paulo/SP',
        'ativo' => 0
    ]
];

$mockTiposServico = [
    ['id' => 1, 'nome' => 'Manutenção Preventiva e Limpeza Física', 'descricao' => 'Desmontagem, limpeza de coolers, troca de pasta térmica e testes térmicos.', 'ativo' => 1],
    ['id' => 2, 'nome' => 'Formatação e Instalação de SO', 'descricao' => 'Instalação limpa do Windows/Linux, drivers atualizados e pacote básico de softwares.', 'ativo' => 1],
    ['id' => 3, 'nome' => 'Reparo em Placa-Mãe / Solda BGA', 'descricao' => 'Diagnóstico eletrônico em nível de componentes, substituição de capacitores e mosfets.', 'ativo' => 1],
    ['id' => 4, 'nome' => 'Upgrade de Hardware (SSD / RAM)', 'descricao' => 'Instalação de módulos de memória, SSD NVMe e clonagem de dados.', 'ativo' => 1]
];

$mockTecnicos = [
    ['id' => 1, 'nome' => 'Cauã Meira', 'email' => 'caua@ethan.local', 'perfil' => 'admin', 'ativo' => 1],
    ['id' => 2, 'nome' => 'Lucas Oliveira', 'email' => 'lucas@ethan.local', 'perfil' => 'tecnico', 'ativo' => 1],
    ['id' => 3, 'nome' => 'Beatriz Santos', 'email' => 'beatriz@ethan.local', 'perfil' => 'tecnico', 'ativo' => 1]
];

$mockOrdens = [
    [
        'id' => 101,
        'cliente_id' => 1,
        'cliente_nome' => 'Acme Corporação de Tecnologia',
        'cliente_cnpj' => '12.345.678/0001-95',
        'cliente_telefone' => '(11) 3214-5500',
        'cliente_email' => 'suporte@acme.com.br',
        'cliente_endereco' => 'Av. Paulista, 1000 - 8º andar, Bela Vista - São Paulo/SP',
        'tipo_servico_id' => 1,
        'servico_nome' => 'Manutenção Preventiva e Limpeza Física',
        'tecnico_id' => 2,
        'tecnico_nome' => 'Lucas Oliveira',
        'equipamento_tipo' => 'Notebook',
        'equipamento_marca' => 'Dell',
        'equipamento_modelo' => 'Latitude 5420',
        'defeito' => 'Superaquecimento constante após 20 minutos de uso, cooler operando em rotação máxima.',
        'diagnostico' => 'Obstrução severa por poeira nas aletas do dissipador térmico e pasta ressecada.',
        'observacoes' => 'Entregue com fonte de alimentação original e mochila.',
        'prioridade' => 'alta',
        'status' => 'Em andamento',
        'abertura_em' => '2026-09-22 09:30:00',
        'abertura_em_formatada' => '22/09/2026 09:30',
        'prazo_previsto' => '2026-09-23 18:00:00',
        'prazo_formatado' => '23/09/2026 18:00',
        'prazo_violado' => true // RN11: Prazo violado
    ],
    [
        'id' => 102,
        'cliente_id' => 2,
        'cliente_nome' => 'Logística Expressa Global S/A',
        'cliente_cnpj' => '98.765.432/0001-88',
        'cliente_telefone' => '(11) 4567-8900',
        'cliente_email' => 'ti@expressaglobal.com',
        'cliente_endereco' => 'Rodovia Anhanguera, km 25, Galpão 3 - São Paulo/SP',
        'tipo_servico_id' => 2,
        'servico_nome' => 'Formatação e Instalação de SO',
        'tecnico_id' => null,
        'tecnico_nome' => null,
        'equipamento_tipo' => 'Desktop',
        'equipamento_marca' => 'Lenovo',
        'equipamento_modelo' => 'ThinkCentre M70q',
        'defeito' => 'Sistema operacional corrompido após queda de energia elétrica.',
        'diagnostico' => null,
        'observacoes' => 'Equipamento sem cabos adicionais.',
        'prioridade' => 'media',
        'status' => 'Aberta',
        'abertura_em' => '2026-09-24 08:00:00',
        'abertura_em_formatada' => '24/09/2026 08:00',
        'prazo_previsto' => '2026-09-25 17:00:00',
        'prazo_formatado' => '25/09/2026 17:00',
        'prazo_violado' => false
    ]
];

$mockChamados = [
    [
        'id' => 201,
        'cliente_id' => 1,
        'cliente_nome' => 'Acme Corporação de Tecnologia',
        'tecnico_id' => 2,
        'tecnico_nome' => 'Lucas Oliveira',
        'descricao' => 'Usuário relata lentidão excessiva ao abrir o software de faturamento em estação remota.',
        'status' => 'Em atendimento',
        'solucao' => 'Conexão via AnyDesk para limpeza de cache e reinicialização de serviço local.',
        'aberto_em' => '2026-09-24 09:15:00',
        'aberto_em_formatada' => '24/09/2026 09:15',
        'concluido_em' => null
    ]
];

$mockPendencias = [
    [
        'id' => 301,
        'titulo' => 'Calibração da estação de solda da bancada 2',
        'descricao' => 'Verificar aferição térmica com termômetro digital antes dos reparos BGA.',
        'responsavel_id' => 2,
        'responsavel_nome' => 'Lucas Oliveira',
        'prioridade' => 'media',
        'status' => 'Pendente',
        'prazo' => '2026-09-23 12:00:00',
        'prazo_formatado' => '23/09/2026 12:00',
        'prazo_violado' => true // Prazo violado
    ]
];

// Roteador simples
switch (true) {
    case $requestUri === '/' || $requestUri === '/dashboard':
        $metricas = [
            'servicos_concluidos' => 18,
            'chamados_resolvidos' => 34,
            'pendencias_concluidas' => 12,
            'tarefas_atrasadas' => 2,
            'tempo_medio' => '3h 40min',
            'conclusao_no_prazo' => '94.5%'
        ];
        $tecnicos = $mockTecnicos;
        $atividadesRecentes = [
            [
                'id' => 101,
                'tipo_registro' => 'Ordem de Serviço',
                'titulo_ou_cliente' => 'Acme Corporação (Notebook Dell)',
                'tecnico_nome' => 'Lucas Oliveira',
                'prioridade' => 'alta',
                'status' => 'Em andamento',
                'prazo_formatado' => '23/09/2026 18:00',
                'prazo_violado' => true,
                'url_modulo' => 'ordens'
            ],
            [
                'id' => 301,
                'tipo_registro' => 'Pendência Interna',
                'titulo_ou_cliente' => 'Calibração da estação de solda',
                'tecnico_nome' => 'Lucas Oliveira',
                'prioridade' => 'media',
                'status' => 'Pendente',
                'prazo_formatado' => '23/09/2026 12:00',
                'prazo_violado' => true,
                'url_modulo' => 'pendencias'
            ]
        ];
        require __DIR__ . '/views/dashboard/index.php';
        break;

    case $requestUri === '/clientes':
        $clientes = $clienteRepository->all($currentUser);
        $securityService->audit((int)$currentUser['id'], 'listar', 'cliente');
        require __DIR__ . '/views/clientes/index.php';
        break;

    case $requestUri === '/clientes/novo':
        requireAdmin($isAdmin);
        $cliente = null;
        $errors = [];
        require __DIR__ . '/views/clientes/form.php';
        break;

    case $requestUri === '/clientes/salvar' && $_SERVER['REQUEST_METHOD'] === 'POST':
        requireAdmin($isAdmin);
        verifyCsrf();
        [$cliente, $errors] = validateCliente($_POST);
        if ($errors) {
            require __DIR__ . '/views/clientes/form.php';
            break;
        }
        try {
            $clienteRepository->create($cliente);
            $securityService->audit((int)$currentUser['id'], 'criar', 'cliente', (int)$pdo->lastInsertId());
            redirectWithFlash('/clientes', 'success', 'Cliente cadastrado com sucesso.');
        } catch (PDOException $exception) {
            if ((string)$exception->getCode() === '23000') {
                $errors['cnpj'] = 'Este CNPJ já está cadastrado.';
                require __DIR__ . '/views/clientes/form.php';
                break;
            }
            throw $exception;
        }

    case preg_match('#^/clientes/(\d+)$#', $requestUri, $m):
        $cliente = $clienteRepository->find((int)$m[1], $currentUser);
        if (!$cliente) {
            http_response_code(404);
            exit('Cliente não encontrado.');
        }
        $historicoOS = $clienteRepository->serviceOrders((int)$m[1], $currentUser);
        $securityService->audit((int)$currentUser['id'], 'visualizar', 'cliente', (int)$m[1]);
        require __DIR__ . '/views/clientes/show.php';
        break;

    case preg_match('#^/clientes/(\d+)/editar$#', $requestUri, $m):
        requireAdmin($isAdmin);
        $cliente = $clienteRepository->find((int)$m[1]);
        if (!$cliente) {
            http_response_code(404);
            exit('Cliente não encontrado.');
        }
        $errors = [];
        require __DIR__ . '/views/clientes/form.php';
        break;

    case preg_match('#^/clientes/(\d+)/atualizar$#', $requestUri, $m) && $_SERVER['REQUEST_METHOD'] === 'POST':
        requireAdmin($isAdmin);
        verifyCsrf();
        $id = (int)$m[1];
        if (!$clienteRepository->find($id)) {
            http_response_code(404);
            exit('Cliente não encontrado.');
        }
        [$cliente, $errors] = validateCliente($_POST);
        $cliente['id'] = $id;
        if ($errors) {
            require __DIR__ . '/views/clientes/form.php';
            break;
        }
        try {
            $clienteRepository->update($id, $cliente);
            $securityService->audit((int)$currentUser['id'], 'atualizar', 'cliente', $id);
            redirectWithFlash('/clientes/' . $id, 'success', 'Cliente atualizado com sucesso.');
        } catch (PDOException $exception) {
            if ((string)$exception->getCode() === '23000') {
                $errors['cnpj'] = 'Este CNPJ já está cadastrado.';
                require __DIR__ . '/views/clientes/form.php';
                break;
            }
            throw $exception;
        }

    case preg_match('#^/clientes/(\d+)/excluir$#', $requestUri, $m) && $_SERVER['REQUEST_METHOD'] === 'POST':
        requireAdmin($isAdmin);
        verifyCsrf();
        try {
            $clienteRepository->delete((int)$m[1]);
            $securityService->audit((int)$currentUser['id'], 'excluir', 'cliente', (int)$m[1]);
            redirectWithFlash('/clientes', 'success', 'Cliente excluído com sucesso.');
        } catch (PDOException $exception) {
            if ((string)$exception->getCode() === '23000') {
                redirectWithFlash('/clientes', 'danger', 'O cliente possui atendimentos vinculados e não pode ser excluído.');
            }
            throw $exception;
        }

    case $requestUri === '/ordens':
        $ordens = $mockOrdens;
        $tecnicos = $mockTecnicos;
        require __DIR__ . '/views/ordens/index.php';
        break;

    case $requestUri === '/ordens/nova':
        $ordem = null;
        $clientes = $mockClientes;
        $tiposServico = $mockTiposServico;
        $tecnicos = $mockTecnicos;
        require __DIR__ . '/views/ordens/form.php';
        break;

    case preg_match('#^/ordens/(\d+)$#', $requestUri, $m):
        $ordem = $mockOrdens[0];
        require __DIR__ . '/views/ordens/show.php';
        break;

    case preg_match('#^/ordens/(\d+)/editar$#', $requestUri, $m):
        $ordem = $mockOrdens[0];
        $clientes = $mockClientes;
        $tiposServico = $mockTiposServico;
        $tecnicos = $mockTecnicos;
        require __DIR__ . '/views/ordens/form.php';
        break;

    case preg_match('#^/ordens/(\d+)/imprimir$#', $requestUri, $m):
        $ordem = $mockOrdens[0];
        require __DIR__ . '/views/ordens/print.php';
        break;

    case $requestUri === '/chamados':
        $chamados = $mockChamados;
        require __DIR__ . '/views/chamados/index.php';
        break;

    case $requestUri === '/chamados/novo':
        $chamado = null;
        $clientes = $mockClientes;
        $tecnicos = $mockTecnicos;
        require __DIR__ . '/views/chamados/form.php';
        break;

    case preg_match('#^/chamados/(\d+)$#', $requestUri, $m):
    case preg_match('#^/chamados/(\d+)/editar$#', $requestUri, $m):
        $chamado = $mockChamados[0];
        $clientes = $mockClientes;
        $tecnicos = $mockTecnicos;
        require __DIR__ . '/views/chamados/form.php';
        break;

    case $requestUri === '/pendencias':
        $pendencias = $mockPendencias;
        require __DIR__ . '/views/pendencias/index.php';
        break;

    case $requestUri === '/pendencias/nova':
        $pendencia = null;
        $usuarios = $mockTecnicos;
        require __DIR__ . '/views/pendencias/form.php';
        break;

    case preg_match('#^/pendencias/(\d+)$#', $requestUri, $m):
        $pendencia = $mockPendencias[0];
        require __DIR__ . '/views/pendencias/show.php';
        break;

    case preg_match('#^/pendencias/(\d+)/editar$#', $requestUri, $m):
        $pendencia = $mockPendencias[0];
        $usuarios = $mockTecnicos;
        require __DIR__ . '/views/pendencias/form.php';
        break;

    case $requestUri === '/tipos-servico':
        $tiposServico = $mockTiposServico;
        require __DIR__ . '/views/tipos_servico/index.php';
        break;

    case $requestUri === '/tipos-servico/novo':
        $tipoServico = null;
        require __DIR__ . '/views/tipos_servico/form.php';
        break;

    case preg_match('#^/tipos-servico/(\d+)/editar$#', $requestUri, $m):
        $tipoServico = $mockTiposServico[0];
        require __DIR__ . '/views/tipos_servico/form.php';
        break;

    case $requestUri === '/usuarios':
        $usuarios = $mockTecnicos;
        require __DIR__ . '/views/usuarios/index.php';
        break;

    case $requestUri === '/recuperacoes-senha':
        requireAdmin($isAdmin);
        $passwordResetRequests = $passwordResetService->pendingRequests();
        require __DIR__ . '/views/password_resets/index.php';
        break;

    case $requestUri === '/auditoria':
        requireAdmin($isAdmin);
        $auditLogs = $securityService->recentAuditLogs();
        require __DIR__ . '/views/audit/index.php';
        break;

    case $requestUri === '/solicitacoes-privacidade':
        requireAdmin($isAdmin);
        $privacyRequests = $privacyRequestService->all();
        require __DIR__ . '/views/privacy/admin.php';
        break;

    case preg_match('#^/solicitacoes-privacidade/(\d+)/status$#', $requestUri, $m) && $_SERVER['REQUEST_METHOD'] === 'POST':
        requireAdmin($isAdmin);
        verifyCsrf();
        $status = (string)($_POST['status'] ?? '');
        if ($privacyRequestService->updateStatus((int)$m[1], $status, (int)$currentUser['id'])) {
            $securityService->audit((int)$currentUser['id'], 'atualizar_status', 'privacy_request', (int)$m[1], ['status' => $status]);
            redirectWithFlash('/solicitacoes-privacidade', 'success', 'Solicitação atualizada.');
        }
        redirectWithFlash('/solicitacoes-privacidade', 'danger', 'Não foi possível atualizar a solicitação.');

    case preg_match('#^/recuperacoes-senha/(\d+)/aprovar$#', $requestUri, $m) && $_SERVER['REQUEST_METHOD'] === 'POST':
        requireAdmin($isAdmin);
        verifyCsrf();

        if (
            trim((string)($mailConfig['host'] ?? '')) === ''
            || trim((string)($mailConfig['username'] ?? '')) === ''
            || (string)($mailConfig['password'] ?? '') === ''
        ) {
            redirectWithFlash('/recuperacoes-senha', 'danger', 'Configure o SMTP antes de aprovar solicitações.');
        }

        $resetRequest = $passwordResetService->findPendingRequest((int)$m[1]);
        if (!$resetRequest) {
            redirectWithFlash('/recuperacoes-senha', 'danger', 'A solicitação não existe ou já foi analisada.');
        }

        $token = $passwordResetService->createToken((int)$resetRequest['usuario_id']);
        $appUrl = rtrim((string)($authConfig['app_url'] ?? ''), '/');
        if ($appUrl === '') {
            $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
            $appUrl = ($isHttps ? 'https://' : 'http://') . $host;
        }

        try {
            (new SmtpMailer($mailConfig))->sendPasswordReset(
                (string)$resetRequest['email'],
                (string)$resetRequest['nome'],
                $appUrl . '/redefinir-senha?token=' . rawurlencode($token)
            );

            if (!$passwordResetService->approveRequest((int)$m[1], (int)$currentUser['id'])) {
                $passwordResetService->revokeToken($token);
                redirectWithFlash('/recuperacoes-senha', 'danger', 'A solicitação já foi analisada por outro administrador.');
            }
            $securityService->audit((int)$currentUser['id'], 'aprovar', 'password_reset_request', (int)$m[1]);
        } catch (Throwable $exception) {
            $passwordResetService->revokeToken($token);
            error_log('Falha ao aprovar recuperação de senha: ' . $exception->getMessage());
            redirectWithFlash('/recuperacoes-senha', 'danger', 'Não foi possível enviar o e-mail. A solicitação continua pendente.');
        }

        redirectWithFlash('/recuperacoes-senha', 'success', 'Solicitação aprovada e link de redefinição enviado ao usuário.');

    case preg_match('#^/recuperacoes-senha/(\d+)/recusar$#', $requestUri, $m) && $_SERVER['REQUEST_METHOD'] === 'POST':
        requireAdmin($isAdmin);
        verifyCsrf();
        if ($passwordResetService->rejectRequest((int)$m[1], (int)$currentUser['id'])) {
            $securityService->audit((int)$currentUser['id'], 'recusar', 'password_reset_request', (int)$m[1]);
            redirectWithFlash('/recuperacoes-senha', 'success', 'Solicitação recusada.');
        }
        redirectWithFlash('/recuperacoes-senha', 'danger', 'A solicitação não existe ou já foi analisada.');

    case $requestUri === '/usuarios/novo':
        $usuario = null;
        require __DIR__ . '/views/usuarios/form.php';
        break;

    case preg_match('#^/usuarios/(\d+)/editar$#', $requestUri, $m):
        $usuario = $mockTecnicos[1];
        require __DIR__ . '/views/usuarios/form.php';
        break;

    default:
        http_response_code(404);
        echo "<h1 style='font-family: sans-serif; text-align: center; margin-top: 50px;'>404 - Página não encontrada</h1>";
        echo "<p style='font-family: sans-serif; text-align: center;'><a href='/dashboard'>Voltar ao Dashboard</a></p>";
        break;
}
