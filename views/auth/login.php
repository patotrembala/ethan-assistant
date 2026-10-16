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
<body class="auth-page auth-page--login">
  <main class="login-portal">
    <header class="login-portal__header">
      <a class="login-brand" href="<?= $baseUrl ?>/login" aria-label="Ethan Assistant - início">
        <span class="login-brand__symbol" aria-hidden="true"><span>E</span></span>
        <span><strong>Ethan Assistant</strong><small>Central técnica</small></span>
      </a>
      <span class="login-status"><i aria-hidden="true"></i>Sistema operacional</span>
    </header>

    <section class="login-workspace" aria-label="Acesso ao Ethan Assistant">
      <div class="login-access">
        <div class="login-access__intro">
          <span class="login-kicker">Acesso à plataforma</span>
          <h1>Entre no seu<br><em>espaço de trabalho.</em></h1>
          <p>Continue de onde parou e acompanhe toda a operação técnica em um só lugar.</p>
        </div>

        <div class="login-form-panel">
          <div class="login-form-panel__heading">
            <span class="login-form-panel__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M7 10V8a5 5 0 0 1 10 0v2"/><rect x="4" y="10" width="16" height="11" rx="3"/><path d="M12 15v2"/></svg></span>
            <span><strong>Identificação</strong><small>Use suas credenciais corporativas</small></span>
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
      </div>

      <aside class="login-operations" aria-label="Visão do fluxo operacional">
        <div class="login-operations__top">
          <span><i aria-hidden="true"></i>Fluxo de hoje</span>
          <span class="login-operations__date"><?= date('d/m') ?></span>
        </div>

        <div class="login-operations__hero">
          <span class="login-operations__eyebrow">Sua rotina, organizada</span>
          <h2>Do chamado à<br>solução técnica.</h2>
          <p>Registre, acompanhe e finalize cada atendimento com clareza.</p>
        </div>

        <ol class="operation-track">
          <li class="is-active"><span>01</span><div><strong>Entrada</strong><small>Chamados e ordens recebidas</small></div></li>
          <li><span>02</span><div><strong>Execução</strong><small>Diagnóstico e atendimento</small></div></li>
          <li><span>03</span><div><strong>Entrega</strong><small>Histórico e conclusão</small></div></li>
        </ol>

        <div class="login-operations__footer">
          <span><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>Acesso por perfil</span>
          <span>Uso interno</span>
        </div>
      </aside>
    </section>

    <footer class="login-portal__footer">
      <span>© <?= date('Y') ?> Ethan Assistant</span>
      <span>Gestão técnica inteligente</span>
    </footer>
  </main>
  <script src="<?= $baseUrl ?>/assets/js/auth.js"></script>
</body>
</html>
