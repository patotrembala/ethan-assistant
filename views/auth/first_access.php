<?php
$pageTitle = 'Primeiro acesso - Ethan Assistant';
$baseUrl = $baseUrl ?? '';
$error = $error ?? null;
$adminEmail = (string)($authConfig['admin_email'] ?? '');
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
  <style>
    body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: var(--color-slate-100); padding: var(--spacing-4); }
    .first-access { width: 100%; max-width: 440px; }
    .first-access__brand { text-align: center; margin-bottom: var(--spacing-6); }
    .first-access__brand h1 { font-size: 1.5rem; color: var(--color-slate-900); }
    .first-access__brand p { color: var(--color-slate-500); font-size: var(--font-size-sm); margin-top: 4px; }
  </style>
</head>
<body>
  <main class="first-access">
    <div class="first-access__brand">
      <h1>Configurar administrador</h1>
      <p>Crie a senha do primeiro acesso ao Ethan Assistant</p>
    </div>

    <div class="card">
      <div class="card-body" style="padding: var(--spacing-6);">
        <?php if ($error): ?>
          <div class="alert alert-danger" style="margin-bottom: var(--spacing-4);">
            <span><?= htmlspecialchars($error) ?></span>
          </div>
        <?php endif; ?>

        <form action="<?= $baseUrl ?>/primeiro-acesso" method="POST" data-validate>
          <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
          <div class="form-group">
            <label for="email" class="form-label required">E-mail administrador</label>
            <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($adminEmail) ?>" required autocomplete="email" readonly>
          </div>

          <div class="form-group">
            <label for="senha" class="form-label required">Nova senha</label>
            <input type="password" id="senha" name="senha" class="form-control" minlength="8" required autocomplete="new-password">
            <div class="form-text">Utilize pelo menos 8 caracteres.</div>
          </div>

          <div class="form-group" style="margin-bottom: var(--spacing-6);">
            <label for="confirmacao_senha" class="form-label required">Confirmar senha</label>
            <input type="password" id="confirmacao_senha" name="confirmacao_senha" class="form-control" minlength="8" required autocomplete="new-password">
          </div>

          <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.625rem;">
            Criar acesso administrador
          </button>
        </form>
      </div>
    </div>
  </main>

  <script src="<?= $baseUrl ?>/assets/js/validation.js"></script>
</body>
</html>
