<?php
$pageTitle = 'Auditoria - Ethan Assistant';
$currentRoute = 'auditoria';
$baseUrl = $baseUrl ?? '';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>
<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>
  <div class="app-content">
    <div class="page-header"><div class="page-title-wrap"><h1>Trilha de Auditoria</h1><p class="page-subtitle">Últimos eventos de segurança e alterações relevantes, sem armazenar endereços IP em texto aberto.</p></div></div>
    <div class="table-responsive"><table class="table-corporate">
      <thead><tr><th>Data</th><th>Usuário</th><th>Ação</th><th>Entidade</th><th>Registro</th></tr></thead>
      <tbody>
      <?php if (empty($auditLogs)): ?><tr class="empty-row"><td colspan="5" class="empty-state">Nenhum evento registrado.</td></tr><?php else: ?>
        <?php foreach ($auditLogs as $log): ?><tr>
          <td><?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($log['criado_em']))) ?></td>
          <td><?= htmlspecialchars($log['usuario_nome'] ?? 'Visitante/sistema') ?></td>
          <td><strong><?= htmlspecialchars($log['acao']) ?></strong></td>
          <td><?= htmlspecialchars($log['entidade']) ?></td>
          <td><?= $log['registro_id'] !== null ? '#' . (int)$log['registro_id'] : '—' ?></td>
        </tr><?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
