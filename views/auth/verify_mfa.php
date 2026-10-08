<?php
$pageTitle = 'Verificar acesso - Ethan Assistant';
$baseUrl = $baseUrl ?? '';
$error = $error ?? null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/base.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/components.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/forms.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/auth.css">
</head>
<body class="auth-page">
  <main class="auth-shell">
    <section class="auth-showcase" aria-label="Verificação em duas etapas">
      <div class="brand-lockup"><div class="brand-mark" aria-hidden="true"><span>EA</span></div><div><strong>Ethan Assistant</strong><span>Gestão técnica inteligente</span></div></div>
      <div class="showcase-copy"><span class="showcase-eyebrow"><span class="status-dot"></span>Segunda etapa</span><h1>Confirme que<br><span>é você.</span></h1><p>Enviamos um código temporário para o e-mail do administrador.</p></div>
    </section>
    <section class="auth-content"><div class="auth-content__inner">
      <div class="auth-heading"><span class="secure-label">Acesso protegido</span><h2>Digite o código</h2><p>O código possui seis números e expira em 10 minutos.</p></div>
      <?php if ($error): ?><div class="auth-alert auth-alert--danger" role="alert"><span class="auth-alert__icon">!</span><span><?= htmlspecialchars($error) ?></span></div><?php endif; ?>
      <form method="POST" action="<?= $baseUrl ?>/verificar-acesso" class="auth-form">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <div class="auth-field"><label for="codigo">Código de segurança</label><div class="auth-input-wrap"><input type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" id="codigo" name="codigo" autocomplete="one-time-code" required autofocus></div></div>
        <button type="submit" class="auth-submit"><span>Confirmar acesso</span><span aria-hidden="true">→</span></button>
      </form>
    </div></section>
  </main>
</body>
</html>
