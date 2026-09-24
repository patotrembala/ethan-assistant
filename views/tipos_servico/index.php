<?php
/**
 * Ethan Assistant - Catálogo de Tipos de Serviço (RF11)
 * @var array $tiposServico Catálogo de serviços configuráveis
 */
$pageTitle = 'Tipos de Serviço - Ethan Assistant';
$currentRoute = 'tipos-servico';
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
        <h1>Tipos de Serviço</h1>
        <p class="page-subtitle">Configuração do catálogo de serviços técnicos prestados pela assistência</p>
      </div>
      <div class="page-actions">
        <a href="<?= $baseUrl ?>/tipos-servico/novo" class="btn btn-primary">
          <span>+ Novo Tipo de Serviço</span>
        </a>
      </div>
    </div>

    <!-- Tabela Corporativa -->
    <div class="table-responsive">
      <table class="table-corporate">
        <thead>
          <tr>
            <th>Nome do Serviço</th>
            <th>Descrição do Escopo</th>
            <th>Status</th>
            <th style="text-align: right;">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($tiposServico)): ?>
            <tr class="empty-row">
              <td colspan="4" class="empty-state">Nenhum tipo de serviço configurado.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($tiposServico as $ts): ?>
              <tr>
                <td><strong><?= htmlspecialchars($ts['nome']) ?></strong></td>
                <td><?= htmlspecialchars($ts['descricao'] ?? '-') ?></td>
                <td>
                  <?php if (!empty($ts['ativo'])): ?>
                    <span class="badge" style="background-color: var(--color-success-bg); color: var(--color-success); border: 1px solid var(--color-success-border);">
                      Ativo
                    </span>
                  <?php else: ?>
                    <span class="badge" style="background-color: var(--color-slate-200); color: var(--color-slate-600); border: 1px solid var(--color-slate-300);">
                      Inativo
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="table-actions" style="justify-content: flex-end;">
                    <a href="<?= $baseUrl ?>/tipos-servico/<?= (int)$ts['id'] ?>/editar" class="btn btn-secondary btn-sm">
                      Editar
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
