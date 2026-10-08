<?php
$pageTitle = 'Login - Ethan Assistant';
$baseUrl = $baseUrl ?? '';
$error = $error ?? ($_SESSION['login_error'] ?? null);
$success = $success ?? null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#0f172a">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/base.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/components.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/forms.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/auth.css">
</head>
<body class="auth-page">
  <main class="auth-shell">
    <section class="auth-showcase" aria-label="Apresentação do Ethan Assistant">
      <div class="auth-showcase__glow auth-showcase__glow--one"></div>
      <div class="auth-showcase__glow auth-showcase__glow--two"></div>

      <div class="brand-lockup">
        <div class="brand-mark" aria-hidden="true"><span>EA</span></div>
        <div>
          <strong>Ethan Assistant</strong>
          <span>Gestão técnica inteligente</span>
        </div>
      </div>

      <div class="showcase-copy">
        <span class="showcase-eyebrow"><span class="status-dot"></span>Central de operações</span>
        <h1>Organize atendimentos.<br><span>Entregue resultados.</span></h1>
        <p>Clientes, ordens de serviço e desempenho técnico reunidos em um ambiente simples e confiável.</p>

        <div class="showcase-features" aria-label="Recursos do sistema">
          <div class="showcase-feature">
            <span class="feature-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></span>
            <span><strong>Fluxo centralizado</strong>Ordens e chamados sob controle</span>
          </div>
          <div class="showcase-feature">
            <span class="feature-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></span>
            <span><strong>Operação prática</strong>Menos etapas, mais produtividade</span>
          </div>
          <div class="showcase-feature">
            <span class="feature-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg></span>
            <span><strong>Acesso protegido</strong>Permissões por perfil de usuário</span>
          </div>
        </div>
      </div>

      <div class="showcase-footer"><span>Ethan Assistant</span><span>•</span><span>Uso interno corporativo</span></div>
    </section>

    <section class="auth-content">
      <div class="auth-content__inner">
        <div class="auth-mobile-brand">
          <div class="brand-mark" aria-hidden="true"><span>EA</span></div>
          <strong>Ethan Assistant</strong>
        </div>

        <div class="auth-heading">
          <span class="secure-label">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
            Ambiente seguro
          </span>
          <h2>Bem-vindo de volta</h2>
          <p>Entre com suas credenciais para acessar o painel operacional.</p>
        </div>

        <?php if ($error): ?>
          <div class="auth-alert auth-alert--danger" role="alert"><span class="auth-alert__icon">!</span><span><?= htmlspecialchars($error) ?></span></div>
        <?php endif; ?>
        <?php if ($success): ?>
          <div class="auth-alert auth-alert--success" role="status"><span class="auth-alert__icon">✓</span><span><?= htmlspecialchars($success) ?></span></div>
        <?php endif; ?>

        <form action="<?= $baseUrl ?>/login" method="POST" class="auth-form" data-auth-form novalidate>
          <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

          <div class="auth-field">
            <label for="email">E-mail</label>
            <div class="auth-input-wrap">
              <span class="auth-input-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
              <input type="email" id="email" name="email" placeholder="seu.email@empresa.com" required autocomplete="email" autofocus>
            </div>
            <span class="auth-field-error">Informe um e-mail válido.</span>
          </div>

          <div class="auth-field">
            <div class="auth-label-row"><label for="senha">Senha</label><a class="auth-link" href="<?= $baseUrl ?>/esqueci-senha">Esqueci minha senha</a></div>
            <div class="auth-input-wrap">
              <span class="auth-input-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span>
              <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required autocomplete="current-password">
              <button type="button" class="password-toggle" data-password-toggle aria-label="Mostrar senha" aria-pressed="false">
                <svg class="eye-open" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg class="eye-closed" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m3 3 18 18"/><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 4.2A9.7 9.7 0 0 1 12 4c6.5 0 10 8 10 8a18 18 0 0 1-2.1 3.2"/><path d="M6.6 6.6C3.7 8.5 2 12 2 12s3.5 8 10 8a9.8 9.8 0 0 0 4.3-1"/></svg>
              </button>
            </div>
            <span class="auth-field-error">Informe sua senha.</span>
          </div>

          <button type="submit" class="auth-submit">
            <span class="auth-submit__label">Acessar sistema</span>
            <span class="auth-submit__arrow" aria-hidden="true">→</span>
            <span class="auth-submit__loader" aria-hidden="true"></span>
          </button>
        </form>

        <div class="auth-help"><span>Problemas para acessar?</span><span>Procure o administrador do sistema.</span><a class="auth-link" href="<?= $baseUrl ?>/privacidade">Privacidade e proteção de dados</a></div>
      </div>
    </section>
  </main>
  <script src="<?= $baseUrl ?>/assets/js/auth.js"></script>
</body>
</html>
