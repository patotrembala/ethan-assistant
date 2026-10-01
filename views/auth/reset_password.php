<?php
$pageTitle = 'Redefinir senha - Ethan Assistant';
$baseUrl = $baseUrl ?? '';
$error = $error ?? null;
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
    <section class="auth-showcase" aria-label="Definição de nova senha">
      <div class="auth-showcase__glow auth-showcase__glow--one"></div>
      <div class="auth-showcase__glow auth-showcase__glow--two"></div>
      <div class="brand-lockup"><div class="brand-mark" aria-hidden="true"><span>EA</span></div><div><strong>Ethan Assistant</strong><span>Gestão técnica inteligente</span></div></div>
      <div class="showcase-copy">
        <span class="showcase-eyebrow"><span class="status-dot"></span>Nova credencial</span>
        <h1>Proteja sua conta.<br><span>Crie uma nova senha.</span></h1>
        <p>Use uma senha exclusiva, difícil de adivinhar e que você não utilize em outros serviços.</p>
      </div>
      <div class="showcase-footer"><span>Ethan Assistant</span><span>•</span><span>Uso interno corporativo</span></div>
    </section>

    <section class="auth-content">
      <div class="auth-content__inner">
        <div class="auth-mobile-brand"><div class="brand-mark" aria-hidden="true"><span>EA</span></div><strong>Ethan Assistant</strong></div>
        <div class="auth-heading"><span class="secure-label">Redefinir acesso</span><h2>Crie sua nova senha</h2><p>O link será invalidado assim que a alteração for concluída.</p></div>

        <?php if ($error): ?>
          <div class="auth-alert auth-alert--danger" role="alert"><span class="auth-alert__icon">!</span><span><?= htmlspecialchars($error) ?></span></div>
        <?php endif; ?>

        <?php if ($tokenIsValid): ?>
          <form action="<?= $baseUrl ?>/redefinir-senha" method="POST" class="auth-form" data-auth-form novalidate>
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="auth-field">
              <label for="senha">Nova senha</label>
              <div class="auth-input-wrap"><span class="auth-input-icon" aria-hidden="true">●</span><input type="password" id="senha" name="senha" placeholder="Mínimo de 8 caracteres" minlength="8" required autocomplete="new-password"></div>
              <span class="auth-field-error">Informe uma senha com pelo menos 8 caracteres.</span>
            </div>
            <div class="auth-field">
              <label for="confirmacao_senha">Confirmar nova senha</label>
              <div class="auth-input-wrap"><span class="auth-input-icon" aria-hidden="true">●</span><input type="password" id="confirmacao_senha" name="confirmacao_senha" placeholder="Digite a senha novamente" minlength="8" required autocomplete="new-password"></div>
              <span class="auth-field-error">Confirme sua nova senha.</span>
            </div>
            <button type="submit" class="auth-submit"><span class="auth-submit__label">Salvar nova senha</span><span class="auth-submit__arrow" aria-hidden="true">→</span><span class="auth-submit__loader" aria-hidden="true"></span></button>
          </form>
        <?php else: ?>
          <div class="auth-alert auth-alert--danger" role="alert"><span class="auth-alert__icon">!</span><span>Este link é inválido ou expirou.</span></div>
          <a class="auth-submit auth-submit--link" href="<?= $baseUrl ?>/esqueci-senha"><span>Solicitar novo link</span><span aria-hidden="true">→</span></a>
        <?php endif; ?>

        <div class="auth-help"><a class="auth-link" href="<?= $baseUrl ?>/login">← Voltar para o login</a></div>
      </div>
    </section>
  </main>
  <script src="<?= $baseUrl ?>/assets/js/auth.js"></script>
</body>
</html>
