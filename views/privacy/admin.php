<?php
$pageTitle = 'Solicitações LGPD - Ethan Assistant';
$currentRoute = 'solicitacoes-privacidade';
$baseUrl = $baseUrl ?? '';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>
<main class="app-main">
  <?php require __DIR__ . '/../layouts/topbar.php'; ?>
  <div class="app-content">
    <?php require __DIR__ . '/../layouts/flash.php'; ?>
    <div class="page-header"><div class="page-title-wrap"><h1>Solicitações LGPD</h1><p class="page-subtitle">Registre a análise, confirme a identidade do titular por canal seguro e documente a decisão.</p></div></div>
    <div class="table-responsive"><table class="table-corporate">
      <thead><tr><th>Protocolo</th><th>Titular</th><th>Tipo</th><th>Descrição</th><th>Status</th><th style="text-align:right;">Ação</th></tr></thead>
      <tbody>
      <?php if (empty($privacyRequests)): ?><tr class="empty-row"><td colspan="6" class="empty-state">Nenhuma solicitação registrada.</td></tr><?php else: ?>
        <?php foreach ($privacyRequests as $item): ?><tr>
          <td><strong><?= htmlspecialchars($item['protocolo']) ?></strong><br><small><?= htmlspecialchars(date('d/m/Y H:i', strtotime($item['solicitado_em']))) ?></small></td>
          <td><?= htmlspecialchars($item['nome']) ?><br><small><?= htmlspecialchars($item['email']) ?></small></td>
          <td><?= htmlspecialchars(ucfirst($item['tipo'])) ?></td>
          <td style="max-width:320px; white-space:normal;"><?= nl2br(htmlspecialchars($item['descricao'])) ?></td>
          <td><span class="badge badge-status-<?= htmlspecialchars(str_replace('_', '-', $item['status'])) ?>"><?= htmlspecialchars(str_replace('_', ' ', ucfirst($item['status']))) ?></span></td>
          <td><form method="POST" action="<?= $baseUrl ?>/solicitacoes-privacidade/<?= (int)$item['id'] ?>/status" style="display:flex; gap:6px; justify-content:flex-end;">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <select class="form-select" name="status" aria-label="Novo status"><option value="em_analise">Em análise</option><option value="concluida">Concluída</option><option value="recusada">Recusada</option></select>
            <button class="btn btn-primary btn-sm" type="submit">Salvar</button>
          </form></td>
        </tr><?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
