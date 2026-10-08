<?php
$pageTitle = 'Recuperar senha - Ethan Assistant';
$baseUrl = $baseUrl ?? '';
$error = $error ?? null;
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
    <section class="auth-showcase" aria-label="Recuperação segura de acesso">
      <div class="auth-showcase__glow auth-showcase__glow--one"></div>
      <div class="auth-showcase__glow auth-showcase__glow--two"></div>
      <div class="brand-lockup"><div class="brand-mark" aria-hidden="true"><span>EA</span></div><div><strong>Ethan Assistant</strong><span>Gestão técnica inteligente</span></div></div>
      <div class="showcase-copy">
        <span class="showcase-eyebrow"><span class="status-dot"></span>Recuperação protegida</span>
        <h1>Retome seu acesso.<br><span>Com segurança.</span></h1>
        <p>O administrador analisa a solicitação antes de liberar o link seguro de recuperação.</p>
      </div>
      <div class="showcase-footer"><span>Ethan Assistant</span><span>•</span><span>Uso interno corporativo</span></div>
    </section>

    <section class="auth-content">
      <div class="auth-content__inner">
        <div class="auth-mobile-brand"><div class="brand-mark" aria-hidden="true"><span>EA</span></div><strong>Ethan Assistant</strong></div>
        <div class="auth-heading">
          <span class="secure-label">Recuperar acesso</span>
          <h2>Esqueceu sua senha?</h2>
          <p>Informe o e-mail da sua conta. Após a aprovação do administrador, você receberá o link para criar uma nova senha.</p>
        </div>

        <?php if ($error): ?>
          <div class="auth-alert auth-alert--danger" role="alert"><span class="auth-alert__icon">!</span><span><?= htmlspecialchars($error) ?></span></div>
        <?php endif; ?>
        <?php if ($success): ?>
          <div class="auth-alert auth-alert--success" role="status"><span class="auth-alert__icon">✓</span><span><?= htmlspecialchars($success) ?></span></div>
        <?php endif; ?>

        <?php if (!$success): ?>
          <form action="<?= $baseUrl ?>/esqueci-senha" method="POST" class="auth-form" data-auth-form novalidate>
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="auth-field">
              <label for="email">E-mail</label>
              <div class="auth-input-wrap">
                <span class="auth-input-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
                <input type="email" id="email" name="email" placeholder="seu.email@empresa.com" required autocomplete="email" autofocus>
              </div>
              <span class="auth-field-error">Informe um e-mail válido.</span>
            </div>
            <button type="submit" class="auth-submit"><span class="auth-submit__label">Solicitar recuperação</span><span class="auth-submit__arrow" aria-hidden="true">→</span><span class="auth-submit__loader" aria-hidden="true"></span></button>
          </form>
        <?php endif; ?>

        <div class="auth-help"><a class="auth-link" href="<?= $baseUrl ?>/login">← Voltar para o login</a></div>
      </div>
    </section>
  </main>
  <script src="<?= $baseUrl ?>/assets/js/auth.js"></script>
</body>
</html>
