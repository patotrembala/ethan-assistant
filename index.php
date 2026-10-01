<?php
/**
 * Ethan Assistant - Front Controller & Visualizador de Telas
 * Permite navegação direta nas telas do frontend com dados simulados
 * enquanto os controllers do Codex são integrados.
 */

$sessionPath = __DIR__ . '/storage/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0775, true);
}
session_save_path($sessionPath);

session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax'
]);
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/validation.php';
require_once __DIR__ . '/src/Repositories/ClienteRepository.php';
require_once __DIR__ . '/src/Services/PasswordResetService.php';
require_once __DIR__ . '/src/Services/SmtpMailer.php';

// Roteamento amigável
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$baseUrl = '';

// Autenticação temporária da prévia publicada.
// As credenciais reais ficam em auth.local.php, arquivo ignorado pelo Git.
$authFile = __DIR__ . '/config/auth.local.php';
$authConfig = is_file($authFile) ? require $authFile : [];
$mailFile = __DIR__ . '/config/mail.local.php';
$mailConfig = is_file($mailFile) ? require $mailFile : [];
$pdo = database();
$userCount = (int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();

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

$csrfToken = csrfToken();

if ($requestUri === '/logout') {
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
        } elseif (strlen($senha) < 8) {
            $error = 'A senha deve possuir pelo menos 8 caracteres.';
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
        default => null
    };
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $email = strtolower(trim($_POST['email'] ?? ''));
        $senha = (string)($_POST['senha'] ?? '');

        $stmt = $pdo->prepare(
            'SELECT id, nome, email, senha_hash, perfil FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'nome' => (string)$user['nome'],
                'email' => (string)$user['email'],
                'perfil' => (string)$user['perfil']
            ];
            header('Location: /dashboard');
            exit;
        }

        $error = 'E-mail ou senha incorretos.';
    }

    require __DIR__ . '/views/auth/login.php';
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
        } elseif (
            trim((string)($mailConfig['host'] ?? '')) === ''
            || trim((string)($mailConfig['username'] ?? '')) === ''
            || (string)($mailConfig['password'] ?? '') === ''
        ) {
            $error = 'O envio de recuperação ainda não foi configurado. Procure o administrador do sistema.';
        } else {
            $resetService = new PasswordResetService($pdo);
            $user = $resetService->findActiveUserByEmail($email);

            if ($user && $resetService->canRequest((int)$user['id'])) {
                $token = $resetService->createToken((int)$user['id']);
                $appUrl = rtrim((string)($authConfig['app_url'] ?? ''), '/');

                if ($appUrl === '') {
                    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
                    $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
                    $appUrl = ($isHttps ? 'https://' : 'http://') . $host;
                }

                try {
                    $mailer = new SmtpMailer($mailConfig);
                    $mailer->sendPasswordReset(
                        (string)$user['email'],
                        (string)$user['nome'],
                        $appUrl . '/redefinir-senha?token=' . rawurlencode($token)
                    );
                } catch (Throwable $exception) {
                    $resetService->revokeToken($token);
                    error_log('Falha ao enviar recuperação de senha: ' . $exception->getMessage());
                }
            }

            if ($error === null) {
                $success = 'Se o e-mail estiver cadastrado, enviaremos um link válido por 30 minutos.';
            }
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
        } elseif (strlen($senha) < 8) {
            $error = 'A senha deve possuir pelo menos 8 caracteres.';
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

if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

$currentUser = $_SESSION['user'];
$isAdmin = ($currentUser['perfil'] ?? '') === 'admin';
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
        $clientes = $clienteRepository->all();
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
        $cliente = $clienteRepository->find((int)$m[1]);
        if (!$cliente) {
            http_response_code(404);
            exit('Cliente não encontrado.');
        }
        $historicoOS = $clienteRepository->serviceOrders((int)$m[1]);
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
