<?php
$pageTitle = 'Recuperações de Senha - Ethan Assistant';
$currentRoute = 'recuperacoes-senha';
$baseUrl = $baseUrl ?? '';
$currentUser = $currentUser ?? ($_SESSION['user'] ?? ['perfil' => 'admin']);

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>

  <div class="app-content">
    <?php require __DIR__ . '/../layouts/flash.php'; ?>

    <div class="page-header">
      <div class="page-title-wrap">
        <h1>Recuperações de Senha</h1>
        <p class="page-subtitle">Analise as solicitações antes de liberar um link de redefinição ao usuário.</p>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Solicitações pendentes</h2>
        <span class="badge badge-status-aguardando-aprovacao"><?= count($passwordResetRequests) ?> aguardando</span>
      </div>
      <div class="table-responsive">
        <table class="table-corporate">
          <thead>
            <tr>
              <th>Usuário</th>
              <th>E-mail</th>
              <th>Perfil</th>
              <th>Solicitado em</th>
              <th style="text-align: right;">Decisão</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($passwordResetRequests)): ?>
              <tr class="empty-row"><td colspan="5" class="empty-state">Nenhuma solicitação de recuperação pendente.</td></tr>
            <?php else: ?>
              <?php foreach ($passwordResetRequests as $request): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($request['nome']) ?></strong></td>
                  <td><?= htmlspecialchars($request['email']) ?></td>
                  <td><?= $request['perfil'] === 'admin' ? 'Administrador' : 'Técnico' ?></td>
                  <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($request['solicitado_em']))) ?></td>
                  <td>
                    <div class="table-actions" style="justify-content: flex-end;">
                      <form method="POST" action="<?= $baseUrl ?>/recuperacoes-senha/<?= (int)$request['id'] ?>/aprovar">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <button type="submit" class="btn btn-primary btn-sm">Aprovar e enviar link</button>
                      </form>
                      <form method="POST" action="<?= $baseUrl ?>/recuperacoes-senha/<?= (int)$request['id'] ?>/recusar" onsubmit="return confirm('Recusar esta solicitação de recuperação?');">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <button type="submit" class="btn btn-outline btn-sm">Recusar</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
