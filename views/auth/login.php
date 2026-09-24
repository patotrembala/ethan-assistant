<?php
/**
 * Ethan Assistant - Tela de Login
 * @var string $error Mensagem de erro de autenticação (opcional)
 */
$pageTitle = 'Login - Ethan Assistant';
$baseUrl = $baseUrl ?? '';
$error = $error ?? ($_SESSION['login_error'] ?? null);
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
    body {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      background-color: var(--color-slate-100);
      padding: var(--spacing-4);
    }
    .login-container {
      width: 100%;
      max-width: 400px;
    }
    .login-brand {
      text-align: center;
      margin-bottom: var(--spacing-6);
    }
    .login-brand h1 {
      font-size: 1.5rem;
      color: var(--color-slate-900);
      letter-spacing: -0.025em;
    }
    .login-brand p {
      color: var(--color-slate-500);
      font-size: var(--font-size-sm);
      margin-top: 4px;
    }
  </style>
</head>
<body>

<div class="login-container">
  <div class="login-brand">
    <h1>Ethan Assistant</h1>
    <p>Gestão e Operação Técnica em Informática</p>
  </div>

  <div class="card">
    <div class="card-body" style="padding: var(--spacing-6);">
      <?php if ($error): ?>
        <div class="alert alert-danger" style="margin-bottom: var(--spacing-4);">
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <form action="<?= $baseUrl ?>/login" method="POST" data-validate>
        <div class="form-group">
          <label for="email" class="form-label required">E-mail corporativo</label>
          <input 
            type="email" 
            id="email" 
            name="email" 
            class="form-control" 
            placeholder="seu.email@empresa.com" 
            required 
            autocomplete="email"
            autofocus
          >
        </div>

        <div class="form-group" style="margin-bottom: var(--spacing-6);">
          <label for="senha" class="form-label required">Senha</label>
          <input 
            type="password" 
            id="senha" 
            name="senha" 
            class="form-control" 
            placeholder="••••••••" 
            required 
            autocomplete="current-password"
          >
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.625rem;">
          Acessar Sistema
        </button>
      </form>
    </div>
    <div class="card-footer" style="justify-content: center; font-size: var(--font-size-xs); color: var(--color-slate-500);">
      Acesso restrito a técnicos e administradores credenciados
    </div>
  </div>
</div>

<script src="<?= $baseUrl ?>/assets/js/validation.js"></script>
</body>
</html>
